<?php

namespace App\Filament\Resources\CarePlans\Pages;

use App\Filament\Resources\CarePlans\CarePlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarePlans extends ListRecords
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
