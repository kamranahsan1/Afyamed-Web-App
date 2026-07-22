<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'ulid',
        'actor_type',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'reason',
        'meta',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log): void {
            if (empty($log->ulid)) {
                $log->ulid = (string) Str::ulid();
            }
        });
    }
}
