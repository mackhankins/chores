<?php

namespace App\Models;

use Database\Factories\ChoreCompletionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreCompletion extends Model
{
    /** @use HasFactory<ChoreCompletionFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'chore_id',
        'child_id',
        'completed_date',
    ];

    protected function casts(): array
    {
        return [
            'completed_date' => 'date',
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
}
