<?php

namespace App\Filament\Resources\Chores\Pages;

use App\Filament\Resources\Chores\ChoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChores extends ListRecords
{
    protected static string $resource = ChoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
