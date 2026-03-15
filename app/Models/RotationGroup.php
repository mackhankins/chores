<?php

namespace App\Models;

use App\RotationPeriod;
use Database\Factories\RotationGroupFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RotationGroup extends Model
{
    /** @use HasFactory<RotationGroupFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'period',
        'start_date',
    ];

    protected function casts(): array
    {
        return [
            'period' => RotationPeriod::class,
            'start_date' => 'date',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Child::class, 'rotation_group_members')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function rotationGroupMembers(): HasMany
    {
        return $this->hasMany(RotationGroupMember::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }
}
