<?php

namespace App\Filament\Resources\Feedback\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('message')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'reviewed' => 'info',
                        'closed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'reviewed' => 'Reviewed',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('role')
                    ->options([
                        'patient' => 'Patient',
                        'doctor' => 'Doctor',
                        'pharmacy' => 'Pharmacy',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
