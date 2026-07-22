<?php

namespace App\Filament\Resources\FileRecords\Pages;

use App\Filament\Resources\FileRecords\FileRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFileRecord extends ViewRecord
{
    protected static string $resource = FileRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
