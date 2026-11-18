<?php

use App\Actions\HoldBooking;
use App\Enums\BookingStatus;
use App\Models\Bay;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->travelTo(studioTime('2026-11-17 20:08'));
    $this->bay = Bay::factory()->create();
    $this->customer = ['name' => 'Pat Doe', 'email' => 'pat@example.com', 'phone' => '610-555-0100'];
    $this->hold = fn (string $local, int $minutes = 60, int $players = 2, ?Bay $bay = null) => app(HoldBooking::class)
        ->handle($bay ?? $this->bay, studioTime($local), $minutes, $players, $this->customer);
});

it('holds an open slot and prices it on the server', function () {
    $booking = ($this->hold)('2026-11-18 18:00', 120, 3);

    expect($booking->status)->toBe(BookingStatus::Held)
        ->and($booking->starts_at->equalTo(studioTime('2026-11-18 18:00')))->toBeTrue()
        ->and($booking->ends_at->equalTo(studioTime('2026-11-18 20:00')))->toBeTrue()
        ->and($booking->total_cents)->toBe(10710)
        ->and($booking->hold_expires_at->greaterThan(now()->addMinutes(30)))->toBeTrue();
});

it('refuses a slot that overlaps a confirmed booking', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:30'))->create();

    ($this->hold)('2026-11-18 18:00', 90);
})->throws(ValidationException::class, 'Someone just booked that time');

it('refuses a slot another customer is still paying for', function () {
    Booking::factory()->for($this->bay)->held()->at(studioTime('2026-11-18 18:00'))->create();

    ($this->hold)('2026-11-18 18:00');
})->throws(ValidationException::class);

it('frees the slot once a hold runs out or the booking ends', function (string $state) {
    $factory = Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:00'));
    match ($state) {
        'hold ran out' => $factory->held(now()->subMinute())->create(),
        'expired' => $factory->expired()->create(),
        'cancelled' => $factory->cancelled()->create(),
    };

    expect(($this->hold)('2026-11-18 18:00')->status)->toBe(BookingStatus::Held);
})->with(['hold ran out', 'expired', 'cancelled']);

it('allows back-to-back bookings and the other bay at the same time', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:00'))->create();

    ($this->hold)('2026-11-18 19:00');
    ($this->hold)('2026-11-18 18:00', bay: Bay::factory()->create());

    expect(Booking::count())->toBe(3);
});

it('allows a booking that runs past midnight', function () {
    $booking = ($this->hold)('2026-11-17 23:00', 180);

    expect($booking->localEnd()->format('Y-m-d H:i'))->toBe('2026-11-18 02:00');
});

it('enforces the studio rules', function (string $local, int $minutes, int $players, string $field) {
    try {
        ($this->hold)($local, $minutes, $players);
        $this->fail('Expected a validation error.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey($field);
    }
})->with([
    'in the past' => ['2026-11-17 19:30', 60, 2, 'starts_at'],
    'not on a half hour' => ['2026-11-18 18:15', 60, 2, 'starts_at'],
    'past the 14-day window' => ['2026-12-01 09:00', 60, 2, 'starts_at'],
    'shorter than an hour' => ['2026-11-18 18:00', 30, 2, 'minutes'],
    'longer than four hours' => ['2026-11-18 18:00', 270, 2, 'minutes'],
    'odd length' => ['2026-11-18 18:00', 75, 2, 'minutes'],
    'no players' => ['2026-11-18 18:00', 60, 0, 'players'],
    'too many players' => ['2026-11-18 18:00', 60, 5, 'players'],
]);

it('accepts the last day of the window', function () {
    expect(($this->hold)('2026-11-30 23:00')->status)->toBe(BookingStatus::Held);
});
