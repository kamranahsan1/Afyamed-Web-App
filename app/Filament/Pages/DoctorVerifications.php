<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Services\Doctors\DoctorDirectoryService;
use App\Services\Firebase\FirestoreService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Throwable;
use UnitEnum;

class DoctorVerifications extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?string $navigationLabel = 'Doctor verifications';

    protected static string|UnitEnum|null $navigationGroup = 'App users';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Doctor & pharmacy verifications';

    protected string $view = 'filament.pages.doctor-verifications';

    /** @var list<array<string, mixed>> */
    public array $pending = [];

    public function mount(): void
    {
        $this->refreshPending();
    }

    public function refreshPending(): void
    {
        $this->pending = [];
        $firestore = app(FirestoreService::class);
        if (! $firestore->configured()) {
            return;
        }

        try {
            $users = $firestore->listDocuments('users', 300);
        } catch (Throwable) {
            return;
        }

        foreach ($users as $row) {
            $data = $row['data'];
            $role = $data['role'] ?? null;
            if (! in_array($role, ['doctor', 'pharmacy'], true)) {
                continue;
            }
            $profile = is_array($data['profile'] ?? null) ? $data['profile'] : [];
            $status = $profile['verification_status']
                ?? $data['verification_status']
                ?? 'unsubmitted';
            if (! in_array($status, ['pending', 'unsubmitted'], true)) {
                continue;
            }
            $this->pending[] = [
                'uid' => $row['id'],
                'name' => $data['name'] ?? $row['id'],
                'email' => $data['email'] ?? null,
                'role' => $role,
                'verification_status' => $status,
                'documents' => is_array($profile['documents'] ?? null) ? $profile['documents'] : [],
            ];
        }
    }

    public function approve(string $uid): void
    {
        $this->setStatus($uid, 'approved', 'Approved via Filament');
    }

    public function reject(string $uid): void
    {
        $this->setStatus($uid, 'rejected', 'Rejected via Filament');
    }

    private function setStatus(string $uid, string $status, string $reason): void
    {
        $firestore = app(FirestoreService::class);
        $directory = app(DoctorDirectoryService::class);

        try {
            $user = $firestore->getUser($uid);
            if ($user === null) {
                Notification::make()->title('User not found')->danger()->send();

                return;
            }

            $firestore->setDocument("users/{$uid}", [
                'verification_status' => $status,
                'profile' => [
                    'verification_status' => $status,
                    'verification_note' => $reason,
                ],
                'updated_at' => now()->toIso8601String(),
            ], true);

            if (($user['role'] ?? null) === 'doctor') {
                $directory->syncPublicCard($uid, $user);
                if ($status !== 'approved') {
                    $firestore->setDocument("doctors/{$uid}", [
                        'verification_status' => $status,
                        'status' => $user['status'] ?? 'active',
                    ], true);
                }
            }

            AuditLog::query()->create([
                'actor_type' => 'web_admin',
                'actor_id' => Auth::guard('web_admin')->id(),
                'action' => "verification.{$status}",
                'subject_type' => 'firestore_user',
                'subject_id' => $uid,
                'reason' => $reason,
                'meta' => [
                    'role' => $user['role'] ?? null,
                ],
            ]);

            Notification::make()
                ->title(ucfirst($status))
                ->body("User {$uid} marked as {$status}.")
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()->title('Update failed')->body($e->getMessage())->danger()->send();
        }

        $this->refreshPending();
    }
}
