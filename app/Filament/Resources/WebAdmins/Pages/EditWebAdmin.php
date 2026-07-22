<?php

namespace App\Filament\Resources\WebAdmins\Pages;

use App\Filament\Resources\WebAdmins\WebAdminResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebAdmin extends EditRecord
{
    protected static string $resource = WebAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
