<?php

namespace App\Filament\Resources\Chores\Pages;

use App\Filament\Resources\Chores\ChoreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChore extends EditRecord
{
    protected static string $resource = ChoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
