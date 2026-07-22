<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'ulid',
        'firebase_uid',
        'role',
        'category',
        'rating',
        'message',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Feedback $feedback): void {
            if (empty($feedback->ulid)) {
                $feedback->ulid = (string) Str::ulid();
            }
        });
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(WebAdmin::class, 'reviewed_by');
    }
}
