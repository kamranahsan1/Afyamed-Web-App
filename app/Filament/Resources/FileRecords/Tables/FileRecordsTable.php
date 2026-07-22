<?php

namespace App\Filament\Resources\FileRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FileRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('original_name')
                    ->label('File')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('mime_type')
                    ->toggleable(),
                TextColumn::make('size_bytes')
                    ->label('Size')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (?int $state): string => $state === null
                        ? '—'
                        : number_format($state / 1024, 1).' KB'),
                TextColumn::make('owner_firebase_uid')
                    ->label('Owner UID')
                    ->limit(12)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('owner_role')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'prescriptions' => 'Prescriptions',
                        'insurance' => 'Insurance',
                        'medical_reports' => 'Medical reports',
                        'doctor_documents' => 'Doctor documents',
                        'pharmacy_documents' => 'Pharmacy documents',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
