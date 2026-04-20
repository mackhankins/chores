<?php

namespace App\Filament\Resources\RotationGroups\Schemas;

use App\Models\RotationGroup;
use App\RotationPeriod;
use Filament\Forms\Components\DatePicker;
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
                Select::make('members')
                    ->relationship('members', 'name')
                    ->multiple()
                    ->reorderable()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull()
                    ->helperText('Rotation runs in the order you pick them — drag to reorder.')
                    ->saveRelationshipsUsing(function (RotationGroup $record, $state): void {
                        $sync = [];
                        foreach (array_values((array) $state) as $index => $childId) {
                            $sync[$childId] = ['position' => $index];
                        }
                        $record->members()->sync($sync);
                    }),
            ]);
    }
}
