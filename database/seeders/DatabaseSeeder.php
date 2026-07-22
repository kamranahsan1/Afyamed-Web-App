<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\WebAdmin;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::query()->firstOrCreate(
            ['name' => 'super_admin'],
            ['label' => 'Super Admin'],
        );

        Role::query()->firstOrCreate(
            ['name' => 'support'],
            ['label' => 'Support'],
        );

        $email = trim((string) config('afyamed.admin.email'));
        $password = (string) config('afyamed.admin.password');

        if ($email === '' || $password === '') {
            $this->command?->warn('ADMIN_EMAIL or ADMIN_PASSWORD is missing; admin account was not seeded.');

            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('ADMIN_EMAIL must be a valid email address.');
        }

        if (app()->isProduction() && mb_strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD must be at least 12 characters in production.');
        }

        $admin = WebAdmin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) config('afyamed.admin.name', 'AfyaMed Admin'),
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        $admin->roles()->syncWithoutDetaching([$superAdmin->id]);
    }
}
