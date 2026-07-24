<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Doctors\DoctorDirectoryService;
use App\Services\Firebase\FirestoreService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function __construct(
        private readonly FirestoreService $firestore,
        private readonly DoctorDirectoryService $directory,
    ) {}

    public function specialities(): JsonResponse
    {
        if (! $this->firestore->configured()) {
            return ApiResponse::success(['specialities' => []]);
        }

        try {
            $rows = $this->firestore->listDocuments('specialities', 100);
        } catch (\Throwable $e) {
            return ApiResponse::error('Unable to load specialities.', 503);
        }

        usort($rows, fn ($a, $b) => ((int) ($a['data']['sort_order'] ?? 0)) <=> ((int) ($b['data']['sort_order'] ?? 0)));

        $items = array_map(fn ($row) => [
            'id' => $row['id'],
            'name' => $row['data']['name'] ?? $row['id'],
            'slug' => $row['data']['slug'] ?? $row['id'],
            'sort_order' => (int) ($row['data']['sort_order'] ?? 0),
        ], $rows);

        return ApiResponse::success(['specialities' => $items]);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $this->firestore->configured()) {
            return ApiResponse::success(['doctors' => []]);
        }

        $q = trim((string) $request->query('q', ''));
        $speciality = trim((string) $request->query('speciality', ''));
        $city = trim((string) $request->query('city', ''));

        try {
            $doctors = $this->directory->listApproved($q, $speciality, $city);
        } catch (\Throwable) {
            return ApiResponse::error('Unable to load doctors.', 503);
        }

        return ApiResponse::success(['doctors' => $doctors]);
    }

    public function show(string $id): JsonResponse
    {
        if (! $this->firestore->configured()) {
            return ApiResponse::error('Doctor not found.', 404);
        }

        try {
            $doctor = $this->directory->findApproved($id);
        } catch (\Throwable) {
            return ApiResponse::error('Unable to load doctor.', 503);
        }

        if ($doctor === null) {
            return ApiResponse::error('Doctor not found.', 404);
        }

        return ApiResponse::success(['doctor' => $doctor]);
    }

    public function slots(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        if (! $this->firestore->configured()) {
            return ApiResponse::error('Doctor not found.', 404);
        }

        try {
            $doctor = $this->directory->findApproved($id);
            if ($doctor === null) {
                return ApiResponse::error('Doctor not found.', 404);
            }
            $slots = $this->directory->generateSlots($id, $validated['date']);
        } catch (\Throwable) {
            return ApiResponse::error('Unable to load slots.', 503);
        }

        return ApiResponse::success([
            'doctor_id' => $id,
            'date' => $validated['date'],
            'slots' => $slots,
        ]);
    }

    public function listAvailability(Request $request): JsonResponse
    {
        $uid = (string) $request->attributes->get('firebase_uid');
        if (! $this->assertDoctor($request)) {
            return ApiResponse::error('Doctor role required.', 403);
        }

        return ApiResponse::success($this->directory->availabilityBundle($uid));
    }

    public function storeAvailability(Request $request): JsonResponse
    {
        if (! $this->assertDoctor($request)) {
            return ApiResponse::error('Doctor role required.', 403);
        }

        $validated = $request->validate([
            'weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i', 'after:start'],
            'slot_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $id = (string) Str::ulid();
        $payload = [
            'weekday' => (int) $validated['weekday'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'slot_minutes' => (int) $validated['slot_minutes'],
            'timezone' => $validated['timezone'] ?? 'Asia/Dubai',
            'updated_at' => now()->toIso8601String(),
        ];
        $this->firestore->setDocument("doctor_availability/{$uid}/weekly/{$id}", $payload, false);

        return ApiResponse::success(['id' => $id, ...$payload], 'Weekly availability added.', 201);
    }

    public function updateAvailability(Request $request, string $id): JsonResponse
    {
        if (! $this->assertDoctor($request)) {
            return ApiResponse::error('Doctor role required.', 403);
        }

        $validated = $request->validate([
            'weekday' => ['sometimes', 'integer', 'min:0', 'max:6'],
            'start' => ['sometimes', 'date_format:H:i'],
            'end' => ['sometimes', 'date_format:H:i'],
            'slot_minutes' => ['sometimes', 'integer', 'min:5', 'max:240'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'type' => ['sometimes', Rule::in(['leave', 'custom'])],
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'hours' => ['sometimes', 'array'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $weeklyPath = "doctor_availability/{$uid}/weekly/{$id}";
        $exceptionPath = "doctor_availability/{$uid}/exceptions/{$id}";

        if ($this->firestore->getDocument($weeklyPath) !== null) {
            $this->firestore->setDocument($weeklyPath, array_merge($validated, [
                'updated_at' => now()->toIso8601String(),
            ]), true);

            return ApiResponse::success(['id' => $id, ...$validated], 'Availability updated.');
        }

        if ($this->firestore->getDocument($exceptionPath) !== null) {
            $this->firestore->setDocument($exceptionPath, array_merge($validated, [
                'updated_at' => now()->toIso8601String(),
            ]), true);

            return ApiResponse::success(['id' => $id, ...$validated], 'Exception updated.');
        }

        // Create exception when type provided
        if (isset($validated['type'], $validated['date'])) {
            $this->firestore->setDocument($exceptionPath, array_merge($validated, [
                'updated_at' => now()->toIso8601String(),
            ]), false);

            return ApiResponse::success(['id' => $id, ...$validated], 'Exception saved.', 201);
        }

        return ApiResponse::error('Availability entry not found.', 404);
    }

    public function destroyAvailability(Request $request, string $id): JsonResponse
    {
        if (! $this->assertDoctor($request)) {
            return ApiResponse::error('Doctor role required.', 403);
        }

        $uid = (string) $request->attributes->get('firebase_uid');
        $weeklyPath = "doctor_availability/{$uid}/weekly/{$id}";
        $exceptionPath = "doctor_availability/{$uid}/exceptions/{$id}";

        if ($this->firestore->getDocument($weeklyPath) !== null) {
            $this->firestore->deleteDocument($weeklyPath);

            return ApiResponse::success(null, 'Availability deleted.');
        }
        if ($this->firestore->getDocument($exceptionPath) !== null) {
            $this->firestore->deleteDocument($exceptionPath);

            return ApiResponse::success(null, 'Exception deleted.');
        }

        return ApiResponse::error('Availability entry not found.', 404);
    }

    public function updatePublicProfile(Request $request): JsonResponse
    {
        if (! $this->assertDoctor($request)) {
            return ApiResponse::error('Doctor role required.', 403);
        }

        $validated = $request->validate([
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'speciality_ids' => ['sometimes', 'array'],
            'speciality_ids.*' => ['string'],
            'clinic_name' => ['sometimes', 'nullable', 'string', 'max:190'],
        ]);

        $uid = (string) $request->attributes->get('firebase_uid');
        $appUser = $request->attributes->get('app_user') ?? [];
        $profilePatch = [];
        foreach (['consultation_fee', 'bio', 'city', 'clinic_name'] as $key) {
            if (array_key_exists($key, $validated)) {
                $profilePatch[$key] = $validated[$key];
            }
        }
        if (isset($validated['speciality_ids'])) {
            $profilePatch['speciality_ids'] = array_values($validated['speciality_ids']);
        }

        if ($profilePatch !== []) {
            $this->firestore->setDocument("users/{$uid}", [
                'profile' => $profilePatch,
                'updated_at' => now()->toIso8601String(),
            ], true);
        }

        $card = $this->directory->syncPublicCard($uid, is_array($appUser) ? $appUser : []);

        return ApiResponse::success(['doctor' => $card], 'Public profile updated.');
    }

    private function assertDoctor(Request $request): bool
    {
        $appUser = $request->attributes->get('app_user');

        return is_array($appUser) && ($appUser['role'] ?? null) === 'doctor';
    }
}
