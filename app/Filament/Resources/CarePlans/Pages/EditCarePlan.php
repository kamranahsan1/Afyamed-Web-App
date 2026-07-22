<?php

namespace App\Filament\Resources\CarePlans\Pages;

use App\Filament\Resources\CarePlans\CarePlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarePlan extends EditRecord
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
