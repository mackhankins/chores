<?php

namespace App\Filament\Resources\ChoreAssignments\Pages;

use App\Filament\Resources\ChoreAssignments\ChoreAssignmentResource;
use App\Models\ChoreAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateChoreAssignment extends CreateRecord
{
    protected static string $resource = ChoreAssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $choreIds = $data['chore_id'] ?? [];
        unset($data['chore_id']);

        // Room-level assignment — single record, no chore_id
        if (! empty($data['room_id'])) {
            return ChoreAssignment::create($data);
        }

        // Normalize to array (edit page sends a single ID)
        if (! is_array($choreIds)) {
            $choreIds = [$choreIds];
        }

        $lastRecord = null;

        foreach ($choreIds as $choreId) {
            $lastRecord = ChoreAssignment::firstOrCreate([
                'chore_id' => $choreId,
                'child_id' => $data['child_id'] ?? null,
                'rotation_group_id' => $data['rotation_group_id'] ?? null,
            ], $data + ['chore_id' => $choreId]);
        }

        return $lastRecord;
    }
}
