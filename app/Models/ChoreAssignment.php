<?php

namespace App\Models;

use Database\Factories\ChoreAssignmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreAssignment extends Model
{
    /** @use HasFactory<ChoreAssignmentFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'chore_id',
        'room_id',
        'child_id',
        'rotation_group_id',
    ];

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function rotationGroup(): BelongsTo
    {
        return $this->belongsTo(RotationGroup::class);
    }
}
