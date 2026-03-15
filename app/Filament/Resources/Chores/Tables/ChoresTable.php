<?php

namespace App\Filament\Resources\Chores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ChoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room.name')
                    ->sortable(),
                TextColumn::make('schedule')
                    ->label('Schedule')
                    ->state(function ($record): string {
                        $parts = [];

                        if ($record->frequency) {
                            $parts[] = ucfirst($record->frequency->value);
                        }

                        if (! empty($record->days_of_week)) {
                            $parts[] = collect($record->days_of_week)
                                ->map(fn (string $day) => substr(ucfirst($day), 0, 3))
                                ->implode(', ');
                        }

                        return empty($parts) ? 'Daily' : implode(' · ', $parts);
                    }),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('room')
                    ->relationship('room', 'name'),
                TernaryFilter::make('is_active'),
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
