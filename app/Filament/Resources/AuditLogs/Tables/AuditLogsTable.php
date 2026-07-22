<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('action')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('actor_type')
                    ->toggleable(),
                TextColumn::make('actor_id')
                    ->limit(16)
                    ->toggleable(),
                TextColumn::make('subject_type')
                    ->toggleable(),
                TextColumn::make('subject_id')
                    ->limit(16)
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('actor_type')
                    ->options([
                        'web_admin' => 'Web admin',
                        'firebase_user' => 'App user',
                        'system' => 'System',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
