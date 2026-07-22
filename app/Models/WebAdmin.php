<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class WebAdmin extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'web_admins';

    protected $fillable = [
        'ulid',
        'name',
        'email',
        'password',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WebAdmin $admin): void {
            if (empty($admin->ulid)) {
                $admin->ulid = (string) Str::ulid();
            }
            if (empty($admin->status)) {
                $admin->status = 'active';
            }
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_web_admin');
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive();
    }
}
