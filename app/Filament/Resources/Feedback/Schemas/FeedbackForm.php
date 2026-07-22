<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firebase_uid')
                    ->label('Firebase UID')
                    ->disabledOn(Operation::Edit)
                    ->maxLength(255),
                Select::make('role')
                    ->options([
                        'patient' => 'Patient',
                        'doctor' => 'Doctor',
                        'pharmacy' => 'Pharmacy',
                    ])
                    ->disabledOn(Operation::Edit),
                TextInput::make('category')
                    ->required()
                    ->default('general')
                    ->maxLength(100),
                TextInput::make('rating')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->disabledOn(Operation::Edit),
                Textarea::make('message')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull()
                    ->disabledOn(Operation::Edit),
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'reviewed' => 'Reviewed',
                        'closed' => 'Closed',
                    ])
                    ->required()
                    ->default('open'),
                Textarea::make('admin_note')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
