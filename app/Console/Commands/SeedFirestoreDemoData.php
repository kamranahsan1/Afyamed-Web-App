<?php

namespace App\Console\Commands;

use App\Services\Doctors\DoctorDirectoryService;
use App\Services\Firebase\FirestoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedFirestoreDemoData extends Command
{
    protected $signature = 'afyamed:seed-firestore {--patient= : Optional patient uid for sample inbox}';

    protected $description = 'Seed specialities, demo doctors, availability, and optional notifications into Firestore';

    public function handle(FirestoreService $firestore, DoctorDirectoryService $directory): int
    {
        if (! $firestore->configured()) {
            $this->error('Firestore is not configured (missing service account or google/cloud-firestore).');

            return self::FAILURE;
        }

        $specialities = [
            ['id' => 'general-practice', 'name' => 'General Practice', 'slug' => 'general-practice', 'sort_order' => 1],
            ['id' => 'cardiology', 'name' => 'Cardiology', 'slug' => 'cardiology', 'sort_order' => 2],
            ['id' => 'dermatology', 'name' => 'Dermatology', 'slug' => 'dermatology', 'sort_order' => 3],
            ['id' => 'pediatrics', 'name' => 'Pediatrics', 'slug' => 'pediatrics', 'sort_order' => 4],
            ['id' => 'orthopedics', 'name' => 'Orthopedics', 'slug' => 'orthopedics', 'sort_order' => 5],
            ['id' => 'ent', 'name' => 'ENT', 'slug' => 'ent', 'sort_order' => 6],
            ['id' => 'gynecology', 'name' => 'Gynecology', 'slug' => 'gynecology', 'sort_order' => 7],
            ['id' => 'psychiatry', 'name' => 'Psychiatry', 'slug' => 'psychiatry', 'sort_order' => 8],
        ];

        foreach ($specialities as $spec) {
            $firestore->setDocument('specialities/'.$spec['id'], $spec, true);
        }
        $this->info('Seeded '.count($specialities).' specialities.');

        $doctors = [
            [
                'uid' => 'demo_doctor_sara',
                'name' => 'Dr. Sara Al Maktoum',
                'email' => 'sara.doctor@afyamed.demo',
                'speciality_ids' => ['cardiology'],
                'city' => 'Dubai',
                'fee' => 350,
                'bio' => 'Consultant cardiologist with 12 years of experience.',
                'weekdays' => [0, 1, 2, 3, 4],
            ],
            [
                'uid' => 'demo_doctor_james',
                'name' => 'Dr. James Okello',
                'email' => 'james.doctor@afyamed.demo',
                'speciality_ids' => ['general-practice', 'pediatrics'],
                'city' => 'Abu Dhabi',
                'fee' => 220,
                'bio' => 'Family physician focused on everyday care and wellness.',
                'weekdays' => [1, 2, 3, 4, 5],
            ],
            [
                'uid' => 'demo_doctor_amina',
                'name' => 'Dr. Amina Hassan',
                'email' => 'amina.doctor@afyamed.demo',
                'speciality_ids' => ['dermatology'],
                'city' => 'Dubai',
                'fee' => 280,
                'bio' => 'Board-certified dermatologist for adults and teens.',
                'weekdays' => [0, 2, 4, 6],
            ],
            [
                'uid' => 'demo_doctor_ravi',
                'name' => 'Dr. Ravi Menon',
                'email' => 'ravi.doctor@afyamed.demo',
                'speciality_ids' => ['orthopedics'],
                'city' => 'Sharjah',
                'fee' => 300,
                'bio' => 'Sports injury and joint specialist.',
                'weekdays' => [1, 3, 5],
            ],
        ];

        foreach ($doctors as $doc) {
            $uid = $doc['uid'];
            $firestore->setDocument("users/{$uid}", [
                'ulid' => (string) Str::ulid(),
                'name' => $doc['name'],
                'email' => $doc['email'],
                'role' => 'doctor',
                'status' => 'active',
                'verified' => true,
                'verification_status' => 'approved',
                'profile' => [
                    'type' => 'doctor',
                    'verification_status' => 'approved',
                    'speciality_ids' => $doc['speciality_ids'],
                    'consultation_fee' => $doc['fee'],
                    'bio' => $doc['bio'],
                    'city' => $doc['city'],
                    'rating' => 4.8,
                ],
                'updated_at' => now()->toIso8601String(),
                'created_at' => now()->toIso8601String(),
            ], true);

            $directory->syncPublicCard($uid, [
                'name' => $doc['name'],
                'status' => 'active',
                'verification_status' => 'approved',
                'profile' => [
                    'verification_status' => 'approved',
                    'speciality_ids' => $doc['speciality_ids'],
                    'consultation_fee' => $doc['fee'],
                    'bio' => $doc['bio'],
                    'city' => $doc['city'],
                    'rating' => 4.8,
                ],
            ]);

            // Clear and rewrite weekly availability
            foreach ($firestore->listDocuments("doctor_availability/{$uid}/weekly", 50) as $existing) {
                $firestore->deleteDocument("doctor_availability/{$uid}/weekly/{$existing['id']}");
            }
            foreach ($doc['weekdays'] as $weekday) {
                $id = (string) Str::ulid();
                $firestore->setDocument("doctor_availability/{$uid}/weekly/{$id}", [
                    'weekday' => $weekday,
                    'start' => '09:00',
                    'end' => '17:00',
                    'slot_minutes' => 30,
                    'timezone' => 'Asia/Dubai',
                    'updated_at' => now()->toIso8601String(),
                ], false);
            }
        }
        $this->info('Seeded '.count($doctors).' demo doctors with weekly availability.');

        $patientUid = $this->option('patient');
        if (is_string($patientUid) && $patientUid !== '') {
            $samples = [
                [
                    'title' => 'Welcome to AfyaMed',
                    'body' => 'Your health inbox is ready. Find a doctor whenever you need care.',
                    'type' => 'system',
                ],
                [
                    'title' => 'Complete your profile',
                    'body' => 'Add emergency contacts and family members for faster booking.',
                    'type' => 'reminders',
                    'data' => ['route' => '/profile'],
                ],
            ];
            foreach ($samples as $sample) {
                $id = (string) Str::ulid();
                $firestore->setDocument("users/{$patientUid}/notifications/{$id}", [
                    'ulid' => $id,
                    'title' => $sample['title'],
                    'body' => $sample['body'],
                    'type' => $sample['type'],
                    'data' => $sample['data'] ?? [],
                    'read_at' => null,
                    'sent_at' => now()->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                ], false);
            }
            $this->info("Seeded sample notifications for patient {$patientUid}.");
        }

        $this->info('Firestore demo seed complete.');

        return self::SUCCESS;
    }
}
