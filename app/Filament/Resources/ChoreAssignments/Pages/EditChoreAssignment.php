<?php

namespace App\Filament\Resources\ChoreAssignments\Pages;

use App\Filament\Resources\ChoreAssignments\ChoreAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChoreAssignment extends EditRecord
{
    protected static string $resource = ChoreAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
