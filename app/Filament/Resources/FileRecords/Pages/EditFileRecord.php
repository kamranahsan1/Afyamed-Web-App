<?php

namespace App\Filament\Resources\FileRecords\Pages;

use App\Filament\Resources\FileRecords\FileRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFileRecord extends EditRecord
{
    protected static string $resource = FileRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
