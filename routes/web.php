<?php

use App\Http\Controllers\BookingCalendarController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DoorController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::get('/book', BookingCalendarController::class)->name('book');
Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');

// No customer accounts: these links are signed and sent to the customer.
Route::middleware('signed')->group(function () {
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/abandon', [BookingController::class, 'abandon'])->name('bookings.abandon');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

Route::get('/door', [DoorController::class, 'show'])->name('door');
Route::post('/door', [DoorController::class, 'check'])
    ->middleware('throttle:20,1')
    ->name('door.check');

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// PROTOTYPE — throwaway design variants. Local only.
if (app()->environment('local')) {
    Route::inertia('/prototype/design', 'prototype/Design');
}
