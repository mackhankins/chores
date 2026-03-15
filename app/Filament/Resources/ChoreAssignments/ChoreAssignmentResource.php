<?php

namespace App\Filament\Resources\ChoreAssignments;

use App\Filament\Resources\ChoreAssignments\Pages\CreateChoreAssignment;
use App\Filament\Resources\ChoreAssignments\Pages\EditChoreAssignment;
use App\Filament\Resources\ChoreAssignments\Pages\ListChoreAssignments;
use App\Filament\Resources\ChoreAssignments\Schemas\ChoreAssignmentForm;
use App\Filament\Resources\ChoreAssignments\Tables\ChoreAssignmentsTable;
use App\Models\ChoreAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChoreAssignmentResource extends Resource
{
    protected static ?string $model = ChoreAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Chores';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Assignments';

    public static function form(Schema $schema): Schema
    {
        return ChoreAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChoreAssignmentsTable::configure($table);
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
            'index' => ListChoreAssignments::route('/'),
            'create' => CreateChoreAssignment::route('/create'),
            'edit' => EditChoreAssignment::route('/{record}/edit'),
        ];
    }
}
