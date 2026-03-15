<?php

namespace App\Filament\Resources\ChoreAssignments\Tables;

use App\Models\ChoreAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChoreAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scope')
                    ->label('Scope')
                    ->state(fn (ChoreAssignment $record): string => $record->room_id ? 'Room' : 'Chore')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Room' ? 'info' : 'gray'),
                TextColumn::make('target')
                    ->label('Target')
                    ->state(fn (ChoreAssignment $record): string => $record->room_id
                        ? $record->room->name
                        : $record->chore->name
                    ),
                TextColumn::make('chore.room.name')
                    ->label('Room')
                    ->visible(fn (Table $table): bool => false)
                    ->placeholder('—'),
                TextColumn::make('child.name')
                    ->label('Assigned Child')
                    ->placeholder('—'),
                TextColumn::make('rotationGroup.name')
                    ->label('Rotation Group')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('room')
                    ->relationship('room', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
