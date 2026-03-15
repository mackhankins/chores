<?php

namespace App\Filament\Resources\Chores\Schemas;

use App\RotationPeriod;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('room_id')
                    ->relationship('room', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Textarea::make('description')
                    ->columnSpanFull(),
                CheckboxList::make('days_of_week')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->columns(4)
                    ->helperText('Leave empty for every day.')
                    ->columnSpanFull(),
                Select::make('frequency')
                    ->options(RotationPeriod::class)
                    ->helperText('Leave empty if the chore happens every time its scheduled day comes around.')
                    ->live(),
                DatePicker::make('frequency_start_date')
                    ->label('Starting from')
                    ->required(fn ($get) => $get('frequency') !== null)
                    ->visible(fn ($get) => $get('frequency') !== null)
                    ->helperText('Reference date for calculating which weeks/months this chore is active.'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
