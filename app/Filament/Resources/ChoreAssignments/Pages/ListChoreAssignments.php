<?php

namespace App\Filament\Resources\ChoreAssignments\Pages;

use App\Filament\Resources\ChoreAssignments\ChoreAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChoreAssignments extends ListRecords
{
    protected static string $resource = ChoreAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
