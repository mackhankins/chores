<?php

namespace App\Filament\Resources\RotationGroups\Pages;

use App\Filament\Resources\RotationGroups\RotationGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRotationGroup extends EditRecord
{
    protected static string $resource = RotationGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
