<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ulid'),
                TextEntry::make('action')->badge(),
                TextEntry::make('actor_type'),
                TextEntry::make('actor_id'),
                TextEntry::make('subject_type'),
                TextEntry::make('subject_id'),
                TextEntry::make('reason')->columnSpanFull(),
                TextEntry::make('meta')
                    ->formatStateUsing(fn ($state): string => is_array($state)
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                        : (string) $state)
                    ->columnSpanFull(),
                TextEntry::make('ip_address'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
