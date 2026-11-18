<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Payments\PaymentFailed;
use App\Payments\PaymentGateway;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Cancels a Confirmed booking with a full refund, as long as it is at least
 * studio.cancel_cutoff_hours before the start.
 */
final class CancelBooking
{
    public function __construct(private readonly PaymentGateway $payments) {}

    /**
     * @throws ValidationException
     * @throws PaymentFailed
     */
    public function handle(Booking $booking, ?CarbonImmutable $now = null): Booking
    {
        $now ??= now();

        if (! $booking->canBeCancelled($now)) {
            throw ValidationException::withMessages([
                'booking' => $booking->status === BookingStatus::Confirmed
                    ? 'Bookings can only be cancelled '.config('studio.cancel_cutoff_hours').' or more hours before they start.'
                    : 'This booking is not active, so there is nothing to cancel.',
            ]);
        }

        // Refund first: if Stripe fails, the booking stays Confirmed.
        $this->payments->refund($booking->stripe_payment_intent_id);

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => $now,
            'refunded_at' => $now,
        ]);

        return $booking;
    }
}
