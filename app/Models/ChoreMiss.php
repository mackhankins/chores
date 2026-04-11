<?php

namespace App\Models;

use Database\Factories\ChoreMissFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreMiss extends Model
{
    /** @use HasFactory<ChoreMissFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'chore_id',
        'child_id',
        'missed_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'missed_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function isOutstanding(): bool
    {
        return $this->completed_at === null;
    }
}
