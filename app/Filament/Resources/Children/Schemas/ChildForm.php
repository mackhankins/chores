<?php

namespace App\Filament\Resources\Children\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->mask('(999) 999-9999')
                    ->prefix('+1')
                    ->placeholder('(601) 555-1234')
                    ->dehydrateStateUsing(function (?string $state): ?string {
                        if (! $state) {
                            return null;
                        }

                        $digits = preg_replace('/\D/', '', $state);

                        return '+1'.substr($digits, -10);
                    })
                    ->formatStateUsing(function (?string $state): ?string {
                        if (! $state) {
                            return null;
                        }

                        $digits = preg_replace('/\D/', '', $state);
                        $digits = substr($digits, -10);

                        return sprintf('(%s) %s-%s',
                            substr($digits, 0, 3),
                            substr($digits, 3, 3),
                            substr($digits, 6, 4),
                        );
                    }),
                TextInput::make('pin')
                    ->required()
                    ->length(4)
                    ->numeric(),
                ColorPicker::make('avatar_color')
                    ->required(),
                Section::make('SMS Notifications')
                    ->description('Requires a phone number. Leave times empty to disable.')
                    ->schema([
                        TimePicker::make('notify_morning_at')
                            ->label('Morning chore list')
                            ->seconds(false),
                        TimePicker::make('notify_reminder_at')
                            ->label('Evening reminder')
                            ->seconds(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
