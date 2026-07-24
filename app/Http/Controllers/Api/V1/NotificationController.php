<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Firebase\FirestoreService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function __construct(private readonly FirestoreService $firestore) {}

    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'onesignal_player_id' => ['required', 'string', 'max:191'],
            'platform' => ['required', Rule::in(['android', 'ios', 'web', 'unknown'])],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $deviceUlid = (string) Str::ulid();
        $payload = [
            'ulid' => $deviceUlid,
            'onesignal_player_id' => $validated['onesignal_player_id'],
            'platform' => $validated['platform'],
            'app_version' => $validated['app_version'] ?? null,
            'updated_at' => now()->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];

        // Upsert by player id when possible
        $existing = $this->firestore->listDocuments("users/{$uid}/devices", 50);
        foreach ($existing as $row) {
            if (($row['data']['onesignal_player_id'] ?? null) === $validated['onesignal_player_id']) {
                $deviceUlid = $row['id'];
                $payload['ulid'] = $deviceUlid;
                $payload['created_at'] = $row['data']['created_at'] ?? $payload['created_at'];
                break;
            }
        }

        $this->firestore->setDocument("users/{$uid}/devices/{$deviceUlid}", $payload, false);

        return ApiResponse::success([
            'device' => $payload,
        ], 'Device registered.');
    }

    public function unregisterDevice(Request $request, string $id): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $path = "users/{$uid}/devices/{$id}";
        $doc = $this->firestore->getDocument($path);

        if ($doc === null) {
            $devices = $this->firestore->listDocuments("users/{$uid}/devices", 100);
            foreach ($devices as $row) {
                if (($row['data']['onesignal_player_id'] ?? null) === $id) {
                    $this->firestore->deleteDocument("users/{$uid}/devices/{$row['id']}");

                    return ApiResponse::success(null, 'Device removed.');
                }
            }

            return ApiResponse::error('Device not found.', 404);
        }

        $this->firestore->deleteDocument($path);

        return ApiResponse::success(null, 'Device removed.');
    }

    public function index(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 30)));

        $rows = $this->firestore->listDocuments("users/{$uid}/notifications", 200);
        usort($rows, function (array $a, array $b): int {
            return strcmp(
                (string) ($b['data']['created_at'] ?? ''),
                (string) ($a['data']['created_at'] ?? ''),
            );
        });

        $unread = 0;
        $items = [];
        foreach ($rows as $row) {
            $data = $row['data'];
            $item = [
                'ulid' => $data['ulid'] ?? $row['id'],
                'title' => $data['title'] ?? '',
                'body' => $data['body'] ?? '',
                'type' => $data['type'] ?? 'general',
                'data' => is_array($data['data'] ?? null) ? $data['data'] : [],
                'read_at' => $data['read_at'] ?? null,
                'sent_at' => $data['sent_at'] ?? null,
                'created_at' => $data['created_at'] ?? null,
            ];
            if (empty($item['read_at'])) {
                $unread++;
            }
            $items[] = $item;
        }

        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);

        return ApiResponse::success([
            'data' => $pageItems,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => count($items),
                'unread_count' => $unread,
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $rows = $this->firestore->listDocuments("users/{$uid}/notifications", 200);
        $unread = 0;
        foreach ($rows as $row) {
            if (empty($row['data']['read_at'])) {
                $unread++;
            }
        }

        return ApiResponse::success(['unread_count' => $unread]);
    }

    public function markRead(Request $request, string $ulid): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $path = "users/{$uid}/notifications/{$ulid}";
        $doc = $this->firestore->getDocument($path);
        if ($doc === null) {
            return ApiResponse::error('Notification not found.', 404);
        }

        $this->firestore->setDocument($path, [
            'read_at' => now()->toIso8601String(),
        ], true);

        return ApiResponse::success(null, 'Marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $rows = $this->firestore->listDocuments("users/{$uid}/notifications", 500);
        $now = now()->toIso8601String();
        foreach ($rows as $row) {
            if (! empty($row['data']['read_at'])) {
                continue;
            }
            $this->firestore->setDocument("users/{$uid}/notifications/{$row['id']}", [
                'read_at' => $now,
            ], true);
        }

        return ApiResponse::success(null, 'All notifications marked as read.');
    }

    public function preferences(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $doc = $this->firestore->getDocument("users/{$uid}/notification_preferences/default");

        return ApiResponse::success($this->defaultPreferences($doc));
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_enabled' => ['sometimes', 'boolean'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['boolean'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $current = $this->defaultPreferences(
            $this->firestore->getDocument("users/{$uid}/notification_preferences/default")
        );

        if (array_key_exists('push_enabled', $validated)) {
            $current['push_enabled'] = (bool) $validated['push_enabled'];
        }
        if (isset($validated['categories'])) {
            $current['categories'] = array_merge(
                $current['categories'],
                array_map(fn ($v) => (bool) $v, $validated['categories']),
            );
        }
        $current['updated_at'] = now()->toIso8601String();

        $this->firestore->setDocument(
            "users/{$uid}/notification_preferences/default",
            $current,
            false,
        );

        return ApiResponse::success($current, 'Preferences updated.');
    }

    /**
     * @param  array<string, mixed>|null  $doc
     * @return array{push_enabled: bool, categories: array<string, bool>}
     */
    private function defaultPreferences(?array $doc): array
    {
        $categories = [
            'appointments' => true,
            'reminders' => true,
            'promotions' => false,
            'system' => true,
        ];

        if (is_array($doc['categories'] ?? null)) {
            foreach ($doc['categories'] as $key => $value) {
                $categories[(string) $key] = (bool) $value;
            }
        }

        return [
            'push_enabled' => (bool) ($doc['push_enabled'] ?? true),
            'categories' => $categories,
        ];
    }
}
