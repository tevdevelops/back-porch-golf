<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Payments\PaymentGateway;

/**
 * Releases a Held booking's slot: when Stripe's session expires, or when the
 * customer backs out of the payment page.
 */
final class ExpireBooking
{
    public function __construct(private readonly PaymentGateway $payments) {}

    public function handle(Booking $booking, bool $closeCheckout = false): Booking
    {
        if ($booking->status !== BookingStatus::Held) {
            return $booking;
        }

        if ($closeCheckout && $booking->stripe_checkout_session_id) {
            $this->payments->expireCheckout($booking->stripe_checkout_session_id);
        }

        $booking->update([
            'status' => BookingStatus::Expired,
            'hold_expires_at' => null,
        ]);

        return $booking;
    }
}
