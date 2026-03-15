<?php

namespace App\Filament\Resources\RotationGroups\Schemas;

use App\RotationPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RotationGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('period')
                    ->options(RotationPeriod::class)
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                Repeater::make('rotationGroupMembers')
                    ->relationship()
                    ->schema([
                        Select::make('child_id')
                            ->relationship('child', 'name')
                            ->required()
                            ->distinct()
                            ->searchable()
                            ->preload(),
                    ])
                    ->orderColumn('position')
                    ->reorderable()
                    ->columnSpanFull()
                    ->label('Members')
                    ->addActionLabel('Add member'),
            ]);
    }
}
