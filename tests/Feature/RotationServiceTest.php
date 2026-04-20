<?php

use App\Models\Child;
use App\Models\RotationGroup;
use App\Models\RotationGroupMember;
use App\Services\RotationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

function makeRotation(string $period, Carbon $startDate, int $memberCount = 3): array
{
    $group = RotationGroup::create([
        'name' => ucfirst($period).' Rotation',
        'period' => $period,
        'start_date' => $startDate,
    ]);

    $children = collect(range(0, $memberCount - 1))->map(function (int $i) use ($group) {
        $child = Child::factory()->create(['name' => "Kid {$i}"]);
        RotationGroupMember::create([
            'rotation_group_id' => $group->id,
            'child_id' => $child->id,
            'position' => $i,
        ]);

        return $child;
    });

    return [$group->fresh(), $children];
}

it('rotates daily through each member', function () {
    [$group, $children] = makeRotation('daily', Carbon::parse('2026-04-20'));

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-20'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-21'))->id)->toBe($children[1]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-22'))->id)->toBe($children[2]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-23'))->id)->toBe($children[0]->id);
});

it('rotates weekly through each member on successive Mondays', function () {
    // 2026-04-20 is a Monday
    [$group, $children] = makeRotation('weekly', Carbon::parse('2026-04-20'));

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-20'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-27'))->id)->toBe($children[1]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-04'))->id)->toBe($children[2]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-11'))->id)->toBe($children[0]->id);
});

it('keeps the same child for every day within a weekly period', function () {
    [$group, $children] = makeRotation('weekly', Carbon::parse('2026-04-20'));

    $service = app(RotationService::class);

    foreach (range(0, 6) as $offset) {
        $date = Carbon::parse('2026-04-20')->addDays($offset);
        expect($service->getCurrentChild($group, $date)->id)->toBe($children[0]->id);
    }
});

it('rotates biweekly every two weeks', function () {
    [$group, $children] = makeRotation('biweekly', Carbon::parse('2026-04-20'));

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-20'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-27'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-04'))->id)->toBe($children[1]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-18'))->id)->toBe($children[2]->id);
});

it('rotates monthly', function () {
    [$group, $children] = makeRotation('monthly', Carbon::parse('2026-04-01'));

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-15'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-01'))->id)->toBe($children[1]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-06-01'))->id)->toBe($children[2]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-07-01'))->id)->toBe($children[0]->id);
});

it('pins to position 0 for dates before the start date', function () {
    [$group, $children] = makeRotation('weekly', Carbon::parse('2026-04-20'));

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-13'))->id)->toBe($children[0]->id);
});

it('returns null when the group has no members', function () {
    $group = RotationGroup::create([
        'name' => 'Empty',
        'period' => 'weekly',
        'start_date' => Carbon::parse('2026-04-20'),
    ]);

    expect(app(RotationService::class)->getCurrentChild($group))->toBeNull();
});

it('resolves correctly when positions start at 1 instead of 0', function () {
    // Legacy data may have 1-indexed positions from the old Repeater UI.
    $group = RotationGroup::create([
        'name' => 'Legacy',
        'period' => 'weekly',
        'start_date' => Carbon::parse('2026-04-20'),
    ]);

    $children = collect(range(1, 3))->map(function (int $i) use ($group) {
        $child = Child::factory()->create(['name' => "Kid {$i}"]);
        RotationGroupMember::create([
            'rotation_group_id' => $group->id,
            'child_id' => $child->id,
            'position' => $i,
        ]);

        return $child;
    });

    $service = app(RotationService::class);

    expect($service->getCurrentChild($group, Carbon::parse('2026-04-20'))->id)->toBe($children[0]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-04-27'))->id)->toBe($children[1]->id)
        ->and($service->getCurrentChild($group, Carbon::parse('2026-05-04'))->id)->toBe($children[2]->id);
});

it('defaults to today when no date is passed', function () {
    Carbon::setTestNow('2026-04-27 09:00:00');

    [$group, $children] = makeRotation('weekly', Carbon::parse('2026-04-20'));

    expect(app(RotationService::class)->getCurrentChild($group)->id)->toBe($children[1]->id);
});
