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

        $childId = $data['child_id'] ?? null;
        $rotationGroupId = $data['rotation_group_id'] ?? null;

        if (! empty($data['room_id'])) {
            return ChoreAssignment::updateOrCreate(
                [
                    'room_id' => $data['room_id'],
                    'child_id' => $childId,
                    'rotation_group_id' => $rotationGroupId,
                ],
                $data,
            );
        }

        if (! is_array($choreIds)) {
            $choreIds = [$choreIds];
        }

        $lastRecord = null;

        foreach ($choreIds as $choreId) {
            $lastRecord = ChoreAssignment::updateOrCreate(
                [
                    'chore_id' => $choreId,
                    'child_id' => $childId,
                    'rotation_group_id' => $rotationGroupId,
                ],
                $data + ['chore_id' => $choreId],
            );
        }

        return $lastRecord;
    }
}
