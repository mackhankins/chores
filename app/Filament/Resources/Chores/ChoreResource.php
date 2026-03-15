<?php

namespace App\Filament\Resources\Chores;

use App\Filament\Resources\Chores\Pages\CreateChore;
use App\Filament\Resources\Chores\Pages\EditChore;
use App\Filament\Resources\Chores\Pages\ListChores;
use App\Filament\Resources\Chores\Schemas\ChoreForm;
use App\Filament\Resources\Chores\Tables\ChoresTable;
use App\Models\Chore;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChoreResource extends Resource
{
    protected static ?string $model = Chore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Chores';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ChoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChoresTable::configure($table);
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
            'index' => ListChores::route('/'),
            'create' => CreateChore::route('/create'),
            'edit' => EditChore::route('/{record}/edit'),
        ];
    }
}
