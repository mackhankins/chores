<?php

namespace App\Filament\Resources\ChoreAssignments\Schemas;

use App\Filament\Resources\ChoreAssignments\Pages\CreateChoreAssignment;
use App\Models\Chore;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ChoreAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('scope')
                    ->label('Assign')
                    ->options([
                        'chore' => 'Individual chores',
                        'room' => 'Entire room (all active chores)',
                    ])
                    ->default('chore')
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $record) {
                        if ($record?->room_id) {
                            $component->state('room');
                        } else {
                            $component->state('chore');
                        }
                    })
                    ->columnSpanFull(),
                Select::make('chore_id')
                    ->label('Chores')
                    ->relationship(
                        'chore',
                        'name',
                        fn (Builder $query) => $query->with('room'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Chore $record) => "{$record->name} ({$record->room->name})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->multiple(fn ($livewire) => $livewire instanceof CreateChoreAssignment)
                    ->visible(fn ($get) => $get('scope') === 'chore'),
                Select::make('room_id')
                    ->relationship('room', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn ($get) => $get('scope') === 'room'),
                Select::make('child_id')
                    ->relationship('child', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Set for fixed assignments. Leave blank if using a rotation group.'),
                Select::make('rotation_group_id')
                    ->relationship('rotationGroup', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Set for rotating assignments. Leave blank if assigned to a specific child.'),
            ]);
    }
}
