<?php

namespace App\Filament\Resources\WebAdmins;

use App\Filament\Resources\WebAdmins\Pages\CreateWebAdmin;
use App\Filament\Resources\WebAdmins\Pages\EditWebAdmin;
use App\Filament\Resources\WebAdmins\Pages\ListWebAdmins;
use App\Filament\Resources\WebAdmins\Schemas\WebAdminForm;
use App\Filament\Resources\WebAdmins\Tables\WebAdminsTable;
use App\Models\WebAdmin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WebAdminResource extends Resource
{
    protected static ?string $model = WebAdmin::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Access';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Admins';

    protected static ?string $modelLabel = 'Admin';

    protected static ?string $pluralModelLabel = 'Admins';

    public static function form(Schema $schema): Schema
    {
        return WebAdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebAdminsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebAdmins::route('/'),
            'create' => CreateWebAdmin::route('/create'),
            'edit' => EditWebAdmin::route('/{record}/edit'),
        ];
    }
}
