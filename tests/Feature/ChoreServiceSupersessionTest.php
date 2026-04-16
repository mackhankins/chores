<?php

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Services\ChoreService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Pin "today" to a Wednesday so day-of-week scheduling is deterministic.
    Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('drops an outstanding miss when the chore is scheduled again today', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => ['wednesday']]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $miss = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subWeek(),
        'completed_at' => null,
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$miss]),
        $child,
    );

    expect($filtered)->toBeEmpty();
});

it('drops an older miss when a newer miss exists for the same chore', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => ['monday']]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $older = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(8),
        'completed_at' => null,
    ]);

    ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(1),
        'completed_at' => null,
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$older]),
        $child,
    );

    expect($filtered)->toBeEmpty();
});

it('drops a miss when a newer completion exists for the same chore', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => ['monday']]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $miss = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(8),
        'completed_at' => null,
    ]);

    ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'completed_date' => today()->subDays(1),
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$miss]),
        $child,
    );

    expect($filtered)->toBeEmpty();
});

it('keeps an already-resolved miss so the UI can render the checkmark', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => ['wednesday']]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $miss = ChoreMiss::factory()->completed()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subWeek(),
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$miss]),
        $child,
    );

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($miss->id);
});

it('does not let one child\'s schedule supersede another child\'s miss', function () {
    $childA = Child::factory()->create();
    $childB = Child::factory()->create();

    $chore = Chore::factory()->create(['days_of_week' => ['wednesday']]);
    ChoreAssignment::factory()->for($chore)->for($childB)->create();

    $miss = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $childA->id,
        'missed_date' => today()->subWeek(),
        'completed_at' => null,
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$miss]),
        $childA,
    );

    expect($filtered)->toHaveCount(1);
});

it('passes through when there is nothing to supersede', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => ['monday']]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $miss = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(2),
        'completed_at' => null,
    ]);

    $filtered = app(ChoreService::class)->filterSupersededMisses(
        collect([$miss]),
        $child,
    );

    expect($filtered)->toHaveCount(1);
});
