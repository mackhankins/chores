<?php

namespace App\Filament\Resources\RotationGroups\Pages;

use App\Filament\Resources\RotationGroups\RotationGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRotationGroups extends ListRecords
{
    protected static string $resource = RotationGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
