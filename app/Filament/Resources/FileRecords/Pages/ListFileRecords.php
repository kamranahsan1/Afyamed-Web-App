<?php

namespace App\Filament\Resources\FileRecords\Pages;

use App\Filament\Resources\FileRecords\FileRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFileRecords extends ListRecords
{
    protected static string $resource = FileRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
