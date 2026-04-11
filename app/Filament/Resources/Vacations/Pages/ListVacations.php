<?php

namespace App\Filament\Resources\Vacations\Pages;

use App\Filament\Resources\Vacations\VacationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacations extends ListRecords
{
    protected static string $resource = VacationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
