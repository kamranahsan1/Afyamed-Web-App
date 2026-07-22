<?php

namespace App\Filament\Resources\WebAdmins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Validation\Rules\Password;

class WebAdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->rule(Password::defaults())
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->hiddenOn(Operation::View),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ])
                    ->required()
                    ->default('active'),
                Select::make('roles')
                    ->relationship('roles', 'label')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
