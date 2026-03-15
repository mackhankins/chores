<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'icon',
        'sort_order',
    ];

    public function chores(): HasMany
    {
        return $this->hasMany(Chore::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }
}
