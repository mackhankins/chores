<?php

namespace App\Filament\Widgets;

use App\Models\Chore;
use App\Services\ChoreService;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodaysChoresOverview extends TableWidget
{
    protected static ?string $heading = "Today's Chores";

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $choreService = app(ChoreService::class);
        $todaysChores = $choreService->getTodaysChores();

        $choreIds = $todaysChores->pluck('chore.id')->unique()->toArray();

        return $table
            ->query(
                fn (): Builder => Chore::query()
                    ->whereIn('id', $choreIds)
                    ->with(['room', 'completions'])
            )
            ->columns([
                TextColumn::make('room.name')
                    ->label('Room')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Chore')
                    ->sortable(),
                TextColumn::make('assigned_to')
                    ->label('Assigned To')
                    ->state(function (Chore $record) use ($todaysChores): string {
                        $item = $todaysChores->firstWhere('chore.id', $record->id);

                        return $item['child']?->name ?? '—';
                    }),
                IconColumn::make('completed')
                    ->label('Done')
                    ->state(function (Chore $record) use ($todaysChores): bool {
                        $item = $todaysChores->firstWhere('chore.id', $record->id);
                        $childId = $item['child']?->id;

                        if (! $childId) {
                            return false;
                        }

                        return $record->completions
                            ->where('child_id', $childId)
                            ->where('completed_date', today()->toDateString())
                            ->isNotEmpty();
                    })
                    ->boolean(),
            ]);
    }
}
