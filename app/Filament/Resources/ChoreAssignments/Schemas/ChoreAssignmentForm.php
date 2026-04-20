<?php

namespace App\Filament\Resources\ChoreAssignments\Schemas;

use App\Filament\Resources\ChoreAssignments\Pages\CreateChoreAssignment;
use App\Models\Chore;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

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
                    ->label(fn ($livewire) => $livewire instanceof CreateChoreAssignment ? 'Chores' : 'Chore')
                    ->options(fn () => Chore::query()
                        ->with('room')
                        ->where('is_active', true)
                        ->get()
                        ->mapWithKeys(fn (Chore $chore) => [$chore->id => "{$chore->name} ({$chore->room->name})"]))
                    ->searchable()
                    ->required()
                    ->multiple(fn ($livewire) => $livewire instanceof CreateChoreAssignment)
                    ->visible(fn ($get) => $get('scope') === 'chore'),
                Select::make('room_id')
                    ->relationship('room', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn ($get) => $get('scope') === 'room'),
                Radio::make('target_type')
                    ->label('Assign to')
                    ->options([
                        'child' => 'A specific kid',
                        'rotation_group' => 'A rotation group',
                    ])
                    ->default('child')
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $record) {
                        $component->state($record?->rotation_group_id ? 'rotation_group' : 'child');
                    })
                    ->columnSpanFull(),
                Select::make('child_id')
                    ->label('Kid')
                    ->relationship('child', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn ($get) => $get('target_type') === 'child')
                    ->visible(fn ($get) => $get('target_type') === 'child')
                    ->dehydrateStateUsing(fn ($state, $get) => $get('target_type') === 'child' ? $state : null),
                Select::make('rotation_group_id')
                    ->label('Rotation group')
                    ->relationship('rotationGroup', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn ($get) => $get('target_type') === 'rotation_group')
                    ->visible(fn ($get) => $get('target_type') === 'rotation_group')
                    ->dehydrateStateUsing(fn ($state, $get) => $get('target_type') === 'rotation_group' ? $state : null),
            ]);
    }
}
