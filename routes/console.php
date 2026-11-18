<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\Schedule;

// Held bookings stop blocking their slot as soon as the Hold runs out (see
// Booking::scopeBlocking). This just tidies their status afterwards.
Schedule::call(function () {
    Booking::query()
        ->where('status', BookingStatus::Held)
        ->where('hold_expires_at', '<=', now())
        ->update(['status' => BookingStatus::Expired, 'hold_expires_at' => null]);
})->name('expire-holds')->everyFiveMinutes();

// Keep the live demo's grid full: refresh the fake bookings every Monday.
Schedule::command('db:seed', ['--force' => true])
    ->weeklyOn(1, '4:00')
    ->timezone(config('studio.timezone'));
