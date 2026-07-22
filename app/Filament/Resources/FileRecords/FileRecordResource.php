<?php

namespace App\Filament\Resources\FileRecords;

use App\Filament\Resources\FileRecords\Pages\CreateFileRecord;
use App\Filament\Resources\FileRecords\Pages\EditFileRecord;
use App\Filament\Resources\FileRecords\Pages\ListFileRecords;
use App\Filament\Resources\FileRecords\Pages\ViewFileRecord;
use App\Filament\Resources\FileRecords\Schemas\FileRecordForm;
use App\Filament\Resources\FileRecords\Schemas\FileRecordInfolist;
use App\Filament\Resources\FileRecords\Tables\FileRecordsTable;
use App\Models\FileRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FileRecordResource extends Resource
{
    protected static ?string $model = FileRecord::class;

    protected static ?string $recordTitleAttribute = 'original_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Files';

    protected static ?string $modelLabel = 'File';

    protected static ?string $pluralModelLabel = 'Files';

    public static function form(Schema $schema): Schema
    {
        return FileRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FileRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FileRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFileRecords::route('/'),
            'create' => CreateFileRecord::route('/create'),
            'view' => ViewFileRecord::route('/{record}'),
            'edit' => EditFileRecord::route('/{record}/edit'),
        ];
    }
}
