<?php

namespace App\Filament\Resources\CarePlans\Pages;

use App\Filament\Resources\CarePlans\CarePlanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCarePlan extends CreateRecord
{
    protected static string $resource = CarePlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::guard('web_admin')->id();

        return $data;
    }
}
