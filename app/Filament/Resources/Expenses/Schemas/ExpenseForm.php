<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('child_id')
                    ->relationship(
                        'child',
                        'name',
                        fn (Builder $query) => $query->whereNotNull('monthly_expenses'),
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step('0.01'),
                DatePicker::make('paid_date')
                    ->required()
                    ->default(today()),
                TextInput::make('note')
                    ->maxLength(255)
                    ->placeholder('e.g. Bi-weekly payment'),
            ]);
    }
}
