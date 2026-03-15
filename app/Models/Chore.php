<?php

namespace App\Models;

use App\RotationPeriod;
use Carbon\Carbon;
use Database\Factories\ChoreFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chore extends Model
{
    /** @use HasFactory<ChoreFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'room_id',
        'days_of_week',
        'frequency',
        'frequency_start_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'frequency' => RotationPeriod::class,
            'frequency_start_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function isScheduledForToday(): bool
    {
        if (! $this->isScheduledForDate()) {
            return false;
        }

        if ($this->days_of_week === null) {
            return true;
        }

        return in_array(strtolower(now()->format('l')), $this->days_of_week);
    }

    /**
     * Check if this chore falls on an active period based on its frequency.
     * A null frequency means every occurrence (no skipping).
     */
    public function isScheduledForDate(?Carbon $date = null): bool
    {
        if ($this->frequency === null) {
            return true;
        }

        $date ??= today();
        $startDate = $this->frequency_start_date;

        if (! $startDate || $date->lt($startDate)) {
            return false;
        }

        return match ($this->frequency) {
            RotationPeriod::Daily => true,
            RotationPeriod::Weekly => $date->dayOfWeek === $startDate->dayOfWeek,
            RotationPeriod::Biweekly => $date->dayOfWeek === $startDate->dayOfWeek
                && (int) $startDate->diffInWeeks($date) % 2 === 0,
            RotationPeriod::Monthly => $date->day === $startDate->day,
        };
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ChoreCompletion::class);
    }
}
