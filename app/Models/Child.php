<?php

namespace App\Models;

use Database\Factories\ChildFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    /** @use HasFactory<ChildFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'phone',
        'pin',
        'avatar_color',
        'notify_morning_at',
        'notify_reminder_at',
    ];

    public function choreAssignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }

    public function choreCompletions(): HasMany
    {
        return $this->hasMany(ChoreCompletion::class);
    }

    public function rotationGroups(): BelongsToMany
    {
        return $this->belongsToMany(RotationGroup::class, 'rotation_group_members')
            ->withPivot('position');
    }
}
