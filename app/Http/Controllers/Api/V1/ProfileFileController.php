<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FileRecord;
use App\Services\Firebase\FirestoreService;
use App\Services\MedicalFileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProfileFileController extends Controller
{
    public function __construct(
        private readonly MedicalFileService $files,
        private readonly FirestoreService $firestore,
    ) {}

    public function uploadAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $appUser = $request->attributes->get('app_user') ?? [];
        $role = is_array($appUser) ? ($appUser['role'] ?? 'patient') : 'patient';

        $record = $this->files->store(
            $validated['avatar'],
            'avatars',
            $uid,
            $role,
            'user_avatar',
            $uid,
        );

        $url = $this->temporaryUrl($record);

        if ($this->firestore->configured()) {
            try {
                $this->firestore->setDocument("users/{$uid}", [
                    'profile' => [
                        'avatar_url' => $url,
                        'avatar_file_ulid' => $record->ulid,
                    ],
                    'updated_at' => now()->toIso8601String(),
                ], true);
            } catch (Throwable) {
                // File is stored; profile sync can be retried.
            }
        }

        $profile = is_array($appUser) && is_array($appUser['profile'] ?? null)
            ? $appUser['profile']
            : ['type' => $role];
        $profile['avatar_url'] = $url;
        $profile['avatar_file_ulid'] = $record->ulid;
        $profile['type'] = $profile['type'] ?? $role;

        return ApiResponse::success([
            'file' => [
                'ulid' => $record->ulid,
                'original_name' => $record->original_name,
                'mime_type' => $record->mime_type,
                'size_bytes' => $record->size_bytes,
                'download_url' => $url,
            ],
            'profile' => $profile,
        ], 'Avatar uploaded.');
    }

    public function uploadVerificationDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['license', 'id', 'pharmacy_license'])],
            'document' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $appUser = $request->attributes->get('app_user') ?? [];
        $role = is_array($appUser) ? ($appUser['role'] ?? 'patient') : 'patient';

        $category = $role === 'pharmacy' ? 'pharmacy_documents' : 'doctor_documents';
        if ($role === 'patient') {
            $category = 'medical_reports';
        }

        $record = $this->files->store(
            $validated['document'],
            $category,
            $uid,
            $role,
            'verification_document',
            $validated['type'],
        );

        $docMeta = [
            'ulid' => $record->ulid,
            'type' => $validated['type'],
            'status' => 'pending',
            'original_name' => $record->original_name,
            'category' => $category,
            'uploaded_at' => now()->toIso8601String(),
        ];

        if ($this->firestore->configured()) {
            try {
                $user = $this->firestore->getUser($uid) ?? [];
                $profile = is_array($user['profile'] ?? null) ? $user['profile'] : [];
                $documents = is_array($profile['documents'] ?? null) ? $profile['documents'] : [];
                $documents = array_values(array_filter(
                    $documents,
                    fn ($row) => ! is_array($row) || ($row['type'] ?? null) !== $validated['type'],
                ));
                $documents[] = $docMeta;

                $this->firestore->setDocument("users/{$uid}", [
                    'profile' => [
                        'documents' => $documents,
                        'verification_status' => 'pending',
                    ],
                    'verification_status' => 'pending',
                    'updated_at' => now()->toIso8601String(),
                ], true);
            } catch (Throwable) {
                // File stored; Firestore sync optional retry.
            }
        }

        return ApiResponse::success([
            'document' => array_merge($docMeta, [
                'download_url' => $this->temporaryUrl($record),
            ]),
        ], 'Verification document uploaded.');
    }

    public function temporaryDocumentUrl(Request $request, string $ulid): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        $record = FileRecord::query()->where('ulid', $ulid)->first();

        if ($record === null) {
            return ApiResponse::error('Document not found.', 404);
        }

        if ($record->owner_firebase_uid !== $uid) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success([
            'ulid' => $record->ulid,
            'url' => $this->temporaryUrl($record),
            'expires_in' => 900,
        ]);
    }

    public function stream(Request $request, string $ulid): BinaryFileResponse|JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return ApiResponse::error('Invalid or expired link.', 403);
        }

        $record = FileRecord::query()->where('ulid', $ulid)->first();
        if ($record === null) {
            return ApiResponse::error('Document not found.', 404);
        }

        $path = $this->files->absolutePath($record);
        if (! is_file($path)) {
            return ApiResponse::error('File missing on disk.', 404);
        }

        return response()->file($path, [
            'Content-Type' => $record->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($record->original_name ?: $ulid).'"',
        ]);
    }

    private function temporaryUrl(FileRecord $record): string
    {
        return URL::temporarySignedRoute(
            'api.v1.documents.stream',
            now()->addMinutes(15),
            ['ulid' => $record->ulid],
        );
    }
}
