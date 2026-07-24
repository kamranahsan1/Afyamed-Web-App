<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use Illuminate\Support\Str;
use RuntimeException;

final class FirestoreService
{
    private ?FirestoreClient $client = null;

    public function __construct(private readonly FirebaseFactory $firebaseFactory) {}

    public function configured(): bool
    {
        return app(FirebaseAuthService::class)->configured()
            && class_exists(FirestoreClient::class);
    }

    public function client(): FirestoreClient
    {
        if ($this->client instanceof FirestoreClient) {
            return $this->client;
        }

        if (! $this->configured()) {
            throw new RuntimeException('Firestore is not configured.');
        }

        $firestore = $this->firebaseFactory->factory()->createFirestore();
        $this->client = $firestore->database();

        return $this->client;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDocument(string $path): ?array
    {
        $snap = $this->client()->document($path)->snapshot();
        if (! $snap->exists()) {
            return null;
        }

        return $this->normalize($snap->data() ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setDocument(string $path, array $data, bool $merge = true): void
    {
        $this->client()->document($path)->set($data, ['merge' => $merge]);
    }

    /**
     * @param  array<string, mixed>  $data  Dot-path keys supported (e.g. profile.avatar_url)
     */
    public function updateDocument(string $path, array $data): void
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = ['path' => (string) $key, 'value' => $value];
        }
        $this->client()->document($path)->update($fields);
    }

    public function deleteDocument(string $path): void
    {
        $this->client()->document($path)->delete();
    }

    /**
     * @param  array<int, array{fieldPath: string, op: string, value: mixed}>  $filters
     * @return list<array{id: string, path: string, data: array<string, mixed>}>
     */
    public function queryCollection(
        string $collection,
        array $filters = [],
        ?string $orderBy = null,
        string $orderDirection = 'ASCENDING',
        ?int $limit = null,
    ): array {
        $query = $this->client()->collection($collection);

        foreach ($filters as $filter) {
            $query = $query->where($filter['fieldPath'], $filter['op'], $filter['value']);
        }

        if ($orderBy !== null) {
            $query = $query->orderBy($orderBy, $orderDirection);
        }

        if ($limit !== null) {
            $query = $query->limit($limit);
        }

        $rows = [];
        foreach ($query->documents() as $doc) {
            if (! $doc->exists()) {
                continue;
            }
            $rows[] = [
                'id' => $doc->id(),
                'path' => $doc->reference()->path(),
                'data' => $this->normalize($doc->data() ?? []),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{id: string, path: string, data: array<string, mixed>}>
     */
    public function listDocuments(string $collectionPath, ?int $limit = null): array
    {
        $query = $this->client()->collection($collectionPath);
        if ($limit !== null) {
            $query = $query->limit($limit);
        }

        $rows = [];
        foreach ($query->documents() as $doc) {
            if (! $doc->exists()) {
                continue;
            }
            $rows[] = [
                'id' => $doc->id(),
                'path' => $doc->reference()->path(),
                'data' => $this->normalize($doc->data() ?? []),
            ];
        }

        return $rows;
    }

    public function addDocument(string $collectionPath, array $data, ?string $id = null): string
    {
        $id ??= (string) Str::ulid();
        $this->client()->collection($collectionPath)->document($id)->set($data);

        return $id;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUser(string $uid): ?array
    {
        $data = $this->getDocument("users/{$uid}");
        if ($data === null) {
            return null;
        }

        return array_merge($data, ['id' => $uid, 'firebase_uid' => $uid]);
    }

    /**
     * Compact app_user payload attached to authenticated requests.
     *
     * @return array<string, mixed>|null
     */
    public function appUserSummary(string $uid): ?array
    {
        $user = $this->getUser($uid);
        if ($user === null) {
            return null;
        }

        $profile = is_array($user['profile'] ?? null) ? $user['profile'] : [];

        return [
            'firebase_uid' => $uid,
            'ulid' => $user['ulid'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'phone' => $user['phone'] ?? null,
            'role' => $user['role'] ?? 'patient',
            'status' => $user['status'] ?? 'active',
            'verified' => (bool) ($user['verified'] ?? false),
            'verification_status' => $profile['verification_status']
                ?? $user['verification_status']
                ?? null,
            'profile' => $profile,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[$key] = $this->normalizeValue($value);
        }

        return $out;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Timestamp) {
            return $value->get()->format(DATE_ATOM);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_array($value)) {
            $isList = array_is_list($value);
            $mapped = [];
            foreach ($value as $k => $v) {
                $mapped[$k] = $this->normalizeValue($v);
            }

            return $isList ? array_values($mapped) : $mapped;
        }

        return $value;
    }
}
