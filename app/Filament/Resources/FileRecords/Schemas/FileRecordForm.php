<?php

namespace App\Filament\Resources\FileRecords\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FileRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->options([
                        'prescriptions' => 'Prescriptions',
                        'insurance' => 'Insurance',
                        'medical_reports' => 'Medical reports',
                        'doctor_documents' => 'Doctor documents',
                        'pharmacy_documents' => 'Pharmacy documents',
                    ])
                    ->required(),
                TextInput::make('disk')
                    ->default('medical')
                    ->required()
                    ->maxLength(50),
                TextInput::make('path')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                TextInput::make('original_name')
                    ->maxLength(255),
                TextInput::make('mime_type')
                    ->maxLength(100),
                TextInput::make('size_bytes')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('owner_firebase_uid')
                    ->label('Owner Firebase UID')
                    ->maxLength(255),
                Select::make('owner_role')
                    ->options([
                        'patient' => 'Patient',
                        'doctor' => 'Doctor',
                        'pharmacy' => 'Pharmacy',
                        'admin' => 'Admin',
                    ]),
                TextInput::make('related_type')
                    ->maxLength(100),
                TextInput::make('related_id')
                    ->maxLength(100),
                KeyValue::make('meta')
                    ->columnSpanFull(),
            ]);
    }
}
