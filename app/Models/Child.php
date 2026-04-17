<?php

namespace App\Models;

use App\Enums\Carrier;
use Database\Factories\ChildFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Child extends Model
{
    /** @use HasFactory<ChildFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'phone',
        'carrier',
        'pin',
        'avatar_color',
        'notify_morning_at',
        'notify_reminder_at',
        'monthly_expenses',
    ];

    protected function casts(): array
    {
        return [
            'carrier' => Carrier::class,
            'monthly_expenses' => 'decimal:2',
        ];
    }

    public function choreAssignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }

    public function choreCompletions(): HasMany
    {
        return $this->hasMany(ChoreCompletion::class);
    }

    public function choreMisses(): HasMany
    {
        return $this->hasMany(ChoreMiss::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function rotationGroups(): BelongsToMany
    {
        return $this->belongsToMany(RotationGroup::class, 'rotation_group_members')
            ->withPivot('position');
    }

    public function vacations(): BelongsToMany
    {
        return $this->belongsToMany(Vacation::class);
    }

    public function isOnVacation(?Carbon $date = null): bool
    {
        $date = $date ?? today();

        return $this->vacations()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }
}
