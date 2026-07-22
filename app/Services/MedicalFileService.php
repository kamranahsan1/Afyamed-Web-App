<?php

namespace App\Services;

use App\Models\FileRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MedicalFileService
{
    public const CATEGORIES = [
        'prescriptions',
        'insurance',
        'medical_reports',
        'doctor_documents',
        'pharmacy_documents',
    ];

    public function store(
        UploadedFile $file,
        string $category,
        ?string $ownerFirebaseUid = null,
        ?string $ownerRole = null,
        ?string $relatedType = null,
        ?string $relatedId = null,
    ): FileRecord {
        if (! in_array($category, self::CATEGORIES, true)) {
            throw ValidationException::withMessages([
                'category' => ['Invalid medical file category.'],
            ]);
        }

        $path = $file->store($category, 'medical');

        return FileRecord::query()->create([
            'disk' => 'medical',
            'category' => $category,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize() ?: null,
            'owner_firebase_uid' => $ownerFirebaseUid,
            'owner_role' => $ownerRole,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }

    public function absolutePath(FileRecord $record): string
    {
        return Storage::disk($record->disk)->path($record->path);
    }

    public function delete(FileRecord $record): void
    {
        Storage::disk($record->disk)->delete($record->path);
        $record->delete();
    }
}
