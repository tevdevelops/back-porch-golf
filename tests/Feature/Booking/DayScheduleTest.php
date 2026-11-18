<?php

use App\Models\Bay;
use App\Models\Booking;
use App\Support\DaySchedule;

beforeEach(function () {
    $this->travelTo(studioTime('2026-11-17 20:08'));
    $this->bay = Bay::factory()->create();
});

it('labels blocks Complete, In Progress or Booked from the clock', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-17 14:00'))->create();
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-17 19:30'), 90)->create();
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-17 22:00'))->create();

    $statuses = collect(DaySchedule::for('2026-11-17')['blocks'])->pluck('status')->all();

    expect($statuses)->toBe(['Complete', 'In Progress', 'Booked']);
});

it('shows bookings that spill over from last night with a negative offset', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-17 23:00'), 120)->create();

    $next = DaySchedule::for('2026-11-18');

    expect($next['blocks'])->toHaveCount(1)
        ->and($next['blocks'][0]['offset'])->toBe(-60)
        ->and($next['blocks'][0]['minutes'])->toBe(120);
});

it('hides bookings that no longer hold a slot and never exposes customers', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 10:00'))->expired()->create();
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 12:00'))->cancelled()->create();
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 14:00'))->held()->create();

    $blocks = DaySchedule::for('2026-11-18')['blocks'];

    expect($blocks)->toHaveCount(1)
        ->and(array_keys($blocks[0]))->toBe(['bay_id', 'offset', 'minutes', 'starts_at', 'ends_at', 'status']);
});

it('knows studio days around DST are 23 or 25 hours long', function () {
    expect(DaySchedule::for('2026-11-17')['minutes'])->toBe(1440)
        ->and(DaySchedule::for('2027-03-14')['minutes'])->toBe(1380)
        ->and(DaySchedule::for('2027-11-07')['minutes'])->toBe(1500);
});
