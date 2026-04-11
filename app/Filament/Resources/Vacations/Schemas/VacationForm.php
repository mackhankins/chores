<?php

namespace App\Filament\Resources\Vacations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('e.g. Weekend at Grandma\'s')
                    ->maxLength(255),
                Select::make('children')
                    ->relationship('children', 'name')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->searchable(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
            ]);
    }
}
