<?php

namespace App\Services\Doctors;

use App\Services\Firebase\FirestoreService;
use Carbon\Carbon;
use DateTimeZone;

final class DoctorDirectoryService
{
    public function __construct(private readonly FirestoreService $firestore) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listApproved(string $q = '', string $speciality = '', string $city = ''): array
    {
        $rows = $this->firestore->listDocuments('doctors', 200);
        $items = [];
        foreach ($rows as $row) {
            $data = $row['data'];
            if (($data['verification_status'] ?? null) !== 'approved') {
                continue;
            }
            if (($data['status'] ?? 'active') !== 'active') {
                continue;
            }

            $card = $this->mapCard($row['id'], $data);
            if ($q !== '') {
                $hay = strtolower(($card['name'] ?? '').' '.($card['bio'] ?? '').' '.($card['city'] ?? ''));
                if (! str_contains($hay, strtolower($q))) {
                    continue;
                }
            }
            if ($speciality !== '') {
                $ids = $card['speciality_ids'] ?? [];
                $names = $card['speciality_names'] ?? [];
                $match = in_array($speciality, $ids, true)
                    || in_array($speciality, $names, true)
                    || collect($names)->contains(fn ($n) => strcasecmp((string) $n, $speciality) === 0);
                if (! $match) {
                    continue;
                }
            }
            if ($city !== '' && strcasecmp((string) ($card['city'] ?? ''), $city) !== 0) {
                continue;
            }
            $items[] = $card;
        }

        usort($items, fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        return $items;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findApproved(string $id): ?array
    {
        $doc = $this->firestore->getDocument("doctors/{$id}");
        if ($doc === null) {
            return null;
        }
        if (($doc['verification_status'] ?? null) !== 'approved') {
            return null;
        }
        if (($doc['status'] ?? 'active') !== 'active') {
            return null;
        }

        return $this->mapCard($id, $doc);
    }

    /**
     * @return list<array{start: string, end: string, available: bool}>
     */
    public function generateSlots(string $doctorUid, string $dateYmd): array
    {
        $weekly = $this->firestore->listDocuments("doctor_availability/{$doctorUid}/weekly", 50);
        $exceptions = $this->firestore->listDocuments("doctor_availability/{$doctorUid}/exceptions", 50);

        $timezone = 'Asia/Dubai';
        foreach ($weekly as $row) {
            if (! empty($row['data']['timezone'])) {
                $timezone = (string) $row['data']['timezone'];
                break;
            }
        }

        $day = Carbon::createFromFormat('Y-m-d', $dateYmd, new DateTimeZone($timezone))->startOfDay();
        $weekday = (int) $day->dayOfWeek; // 0=Sunday

        foreach ($exceptions as $row) {
            $data = $row['data'];
            if (($data['date'] ?? null) !== $dateYmd) {
                continue;
            }
            if (($data['type'] ?? null) === 'leave') {
                return [];
            }
            if (($data['type'] ?? null) === 'custom' && is_array($data['hours'] ?? null)) {
                return $this->expandHours($day, $data['hours'], (int) ($data['slot_minutes'] ?? 30), $timezone);
            }
        }

        $slots = [];
        foreach ($weekly as $row) {
            $data = $row['data'];
            if ((int) ($data['weekday'] ?? -1) !== $weekday) {
                continue;
            }
            $slots = array_merge(
                $slots,
                $this->expandRange(
                    $day,
                    (string) ($data['start'] ?? '09:00'),
                    (string) ($data['end'] ?? '17:00'),
                    (int) ($data['slot_minutes'] ?? 30),
                    $timezone,
                ),
            );
        }

        usort($slots, fn ($a, $b) => strcmp($a['start'], $b['start']));

        return $slots;
    }

    /**
     * @return array{weekly: list<array<string, mixed>>, exceptions: list<array<string, mixed>>}
     */
    public function availabilityBundle(string $uid): array
    {
        $weekly = [];
        foreach ($this->firestore->listDocuments("doctor_availability/{$uid}/weekly", 100) as $row) {
            $weekly[] = ['id' => $row['id'], ...$row['data']];
        }
        $exceptions = [];
        foreach ($this->firestore->listDocuments("doctor_availability/{$uid}/exceptions", 100) as $row) {
            $exceptions[] = ['id' => $row['id'], ...$row['data']];
        }

        return ['weekly' => $weekly, 'exceptions' => $exceptions];
    }

    /**
     * @param  array<string, mixed>  $appUser
     * @return array<string, mixed>
     */
    public function syncPublicCard(string $uid, array $appUser = []): array
    {
        $user = $this->firestore->getUser($uid) ?? $appUser;
        $profile = is_array($user['profile'] ?? null) ? $user['profile'] : [];
        $specialityIds = array_values(array_filter(array_map(
            'strval',
            is_array($profile['speciality_ids'] ?? null) ? $profile['speciality_ids'] : [],
        )));

        if ($specialityIds === [] && ! empty($profile['speciality'])) {
            $specialityIds = [(string) $profile['speciality']];
        }

        $specialityNames = [];
        foreach ($specialityIds as $sid) {
            $spec = $this->firestore->getDocument("specialities/{$sid}");
            if ($spec !== null) {
                $specialityNames[] = $spec['name'] ?? $sid;
            } else {
                $specialityNames[] = $sid;
            }
        }

        $card = [
            'uid' => $uid,
            'name' => $user['name'] ?? 'Doctor',
            'speciality_ids' => $specialityIds,
            'speciality_names' => $specialityNames,
            'consultation_fee' => isset($profile['consultation_fee']) ? (float) $profile['consultation_fee'] : null,
            'bio' => $profile['bio'] ?? null,
            'city' => $profile['city'] ?? null,
            'clinic_name' => $profile['clinic_name'] ?? null,
            'avatar_url' => $profile['avatar_url'] ?? null,
            'rating' => isset($profile['rating']) ? (float) $profile['rating'] : 4.8,
            'verification_status' => $profile['verification_status']
                ?? $user['verification_status']
                ?? 'pending',
            'status' => $user['status'] ?? 'active',
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore->setDocument("doctors/{$uid}", $card, true);

        return $this->mapCard($uid, $card);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapCard(string $id, array $data): array
    {
        return [
            'id' => $id,
            'uid' => $data['uid'] ?? $id,
            'name' => $data['name'] ?? 'Doctor',
            'speciality_ids' => array_values($data['speciality_ids'] ?? []),
            'speciality_names' => array_values($data['speciality_names'] ?? []),
            'consultation_fee' => isset($data['consultation_fee']) ? (float) $data['consultation_fee'] : null,
            'bio' => $data['bio'] ?? null,
            'city' => $data['city'] ?? null,
            'clinic_name' => $data['clinic_name'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? null,
            'rating' => isset($data['rating']) ? (float) $data['rating'] : null,
            'verification_status' => $data['verification_status'] ?? null,
            'status' => $data['status'] ?? 'active',
        ];
    }

    /**
     * @param  list<array{start?: string, end?: string}>  $hours
     * @return list<array{start: string, end: string, available: bool}>
     */
    private function expandHours(Carbon $day, array $hours, int $slotMinutes, string $timezone): array
    {
        $slots = [];
        foreach ($hours as $block) {
            if (! is_array($block)) {
                continue;
            }
            $slots = array_merge(
                $slots,
                $this->expandRange(
                    $day,
                    (string) ($block['start'] ?? '09:00'),
                    (string) ($block['end'] ?? '12:00'),
                    $slotMinutes,
                    $timezone,
                ),
            );
        }

        return $slots;
    }

    /**
     * @return list<array{start: string, end: string, available: bool}>
     */
    private function expandRange(Carbon $day, string $start, string $end, int $slotMinutes, string $timezone): array
    {
        $tz = new DateTimeZone($timezone);
        $cursor = Carbon::createFromFormat('Y-m-d H:i', $day->format('Y-m-d').' '.$start, $tz);
        $limit = Carbon::createFromFormat('Y-m-d H:i', $day->format('Y-m-d').' '.$end, $tz);
        if ($cursor === false || $limit === false || $slotMinutes < 5) {
            return [];
        }

        $slots = [];
        while ($cursor->copy()->addMinutes($slotMinutes)->lte($limit)) {
            $slotEnd = $cursor->copy()->addMinutes($slotMinutes);
            $slots[] = [
                'start' => $cursor->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'available' => true,
            ];
            $cursor = $slotEnd;
        }

        return $slots;
    }
}
