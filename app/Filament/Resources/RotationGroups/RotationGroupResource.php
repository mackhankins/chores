<?php

namespace App\Filament\Resources\RotationGroups;

use App\Filament\Resources\RotationGroups\Pages\CreateRotationGroup;
use App\Filament\Resources\RotationGroups\Pages\EditRotationGroup;
use App\Filament\Resources\RotationGroups\Pages\ListRotationGroups;
use App\Filament\Resources\RotationGroups\Schemas\RotationGroupForm;
use App\Filament\Resources\RotationGroups\Tables\RotationGroupsTable;
use App\Models\RotationGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RotationGroupResource extends Resource
{
    protected static ?string $model = RotationGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Chores';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return RotationGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RotationGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRotationGroups::route('/'),
            'create' => CreateRotationGroup::route('/create'),
            'edit' => EditRotationGroup::route('/{record}/edit'),
        ];
    }
}
