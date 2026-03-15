<?php

namespace App\Filament\Resources\Chores\Pages;

use App\Filament\Resources\Chores\ChoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChore extends CreateRecord
{
    protected static string $resource = ChoreResource::class;
}
