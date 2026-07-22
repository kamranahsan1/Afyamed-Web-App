<?php

namespace App\Filament\Resources\FileRecords\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FileRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ulid'),
                TextEntry::make('disk'),
                TextEntry::make('category'),
                TextEntry::make('path'),
                TextEntry::make('original_name'),
                TextEntry::make('mime_type'),
                TextEntry::make('size_bytes')
                    ->numeric(),
                TextEntry::make('owner_firebase_uid'),
                TextEntry::make('owner_role'),
                TextEntry::make('related_type'),
                TextEntry::make('related_id'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
