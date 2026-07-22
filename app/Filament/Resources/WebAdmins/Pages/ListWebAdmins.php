<?php

namespace App\Filament\Resources\WebAdmins\Pages;

use App\Filament\Resources\WebAdmins\WebAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebAdmins extends ListRecords
{
    protected static string $resource = WebAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
