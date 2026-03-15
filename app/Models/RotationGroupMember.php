<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RotationGroupMember extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'rotation_group_members';

    protected $fillable = [
        'rotation_group_id',
        'child_id',
        'position',
    ];

    public function rotationGroup(): BelongsTo
    {
        return $this->belongsTo(RotationGroup::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
