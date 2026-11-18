<?php

use App\Actions\CancelBooking;
use App\Actions\ConfirmBooking;
use App\Actions\ExpireBooking;
use App\Enums\BookingStatus;
use App\Mail\BookingConfirmed;
use App\Models\Bay;
use App\Models\Booking;
use App\Support\DoorCodes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->travelTo(studioTime('2026-11-17 20:08'));
    $this->payments = fakePayments();
    Mail::fake();
    $this->bay = Bay::factory()->create();
});

it('confirms a held booking, issues a door code and emails it', function () {
    $booking = Booking::factory()->for($this->bay)->held()->at(studioTime('2026-11-18 18:00'))->create();

    app(ConfirmBooking::class)->handle($booking, 'pi_123');

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->door_code)->toMatch('/^\d{6}$/')
        ->and($booking->stripe_payment_intent_id)->toBe('pi_123')
        ->and($booking->hold_expires_at)->toBeNull();
    Mail::assertSent(BookingConfirmed::class, fn ($mail) => $mail->hasTo($booking->customer_email));
});

it('ignores a repeated webhook for the same payment', function () {
    $booking = Booking::factory()->for($this->bay)->held()->at(studioTime('2026-11-18 18:00'))->create();

    app(ConfirmBooking::class)->handle($booking, 'pi_123');
    $code = $booking->refresh()->door_code;
    app(ConfirmBooking::class)->handle($booking, 'pi_123');

    expect($booking->refresh()->door_code)->toBe($code);
    Mail::assertSentCount(1);
    expect($this->payments->refunds)->toBe([]);
});

it('still confirms a late payment if nobody took the slot', function () {
    $booking = Booking::factory()->for($this->bay)->expired()->at(studioTime('2026-11-18 18:00'))->create();

    app(ConfirmBooking::class)->handle($booking, 'pi_late');

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed);
});

it('refunds a late payment when the slot was taken in the meantime', function () {
    $booking = Booking::factory()->for($this->bay)->expired()->at(studioTime('2026-11-18 18:00'))->create();
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:30'))->create();

    app(ConfirmBooking::class)->handle($booking, 'pi_late');

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Expired)
        ->and($booking->refunded_at)->not->toBeNull()
        ->and($this->payments->refunds)->toBe(['pi_late']);
    Mail::assertNothingSent();
});

it('expires a held booking and closes its checkout when the customer backs out', function () {
    $booking = Booking::factory()->for($this->bay)->held()->create(['stripe_checkout_session_id' => 'cs_abc']);

    app(ExpireBooking::class)->handle($booking, closeCheckout: true);

    expect($booking->refresh()->status)->toBe(BookingStatus::Expired)
        ->and($this->payments->expired)->toBe(['cs_abc']);
});

it('cancels with a full refund 24 or more hours ahead', function () {
    $booking = Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 20:08'))->create();

    app(CancelBooking::class)->handle($booking);

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($this->payments->refunds)->toBe([$booking->stripe_payment_intent_id]);
});

it('refuses to cancel inside 24 hours', function () {
    $booking = Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 20:00'))->create();

    expect(fn () => app(CancelBooking::class)->handle($booking))->toThrow(ValidationException::class);
    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($this->payments->refunds)->toBe([]);
});

it('opens the door only during the access window', function () {
    $booking = Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:00'), 120)->create(['door_code' => '482913']);
    $codes = app(DoorCodes::class);

    expect($codes->bookingFor('482913', studioTime('2026-11-18 17:49')))->toBeNull()
        ->and($codes->bookingFor('482913', studioTime('2026-11-18 17:50'))?->is($booking))->toBeTrue()
        ->and($codes->bookingFor('482913', studioTime('2026-11-18 19:59'))?->is($booking))->toBeTrue()
        ->and($codes->bookingFor('482913', studioTime('2026-11-18 20:00')))->toBeNull()
        ->and($codes->bookingFor('000000', studioTime('2026-11-18 18:30')))->toBeNull();
});
