<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FileRecord extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'ulid',
        'disk',
        'category',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'owner_firebase_uid',
        'owner_role',
        'related_type',
        'related_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'size_bytes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FileRecord $file): void {
            if (empty($file->ulid)) {
                $file->ulid = (string) Str::ulid();
            }
            if (empty($file->disk)) {
                $file->disk = 'medical';
            }
        });
    }
}
