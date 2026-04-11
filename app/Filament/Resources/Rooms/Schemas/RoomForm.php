<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->maxLength(255)
                    ->helperText(new HtmlString('Paste an emoji (e.g. 🍳 🛏️ 🧹). <a href="https://emojipedia.org" target="_blank" class="underline text-primary-600 hover:text-primary-500">Browse emojis</a>'))
                    ->suffixAction(
                        Action::make('browseEmojis')
                            ->icon(Heroicon::ArrowTopRightOnSquare)
                            ->url('https://emojipedia.org', shouldOpenInNewTab: true)
                            ->tooltip('Browse emojis on Emojipedia'),
                    ),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
