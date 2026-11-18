<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Mail\BookingConfirmed;
use App\Models\Bay;
use App\Models\Booking;
use App\Payments\PaymentGateway;
use App\Support\DoorCodes;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Runs when Stripe reports a completed checkout. Confirms the booking and
 * issues a Door code, or refunds the payment if the slot was lost in the
 * meantime. Safe to run more than once for the same payment.
 */
final class ConfirmBooking
{
    public function __construct(
        private readonly DoorCodes $doorCodes,
        private readonly PaymentGateway $payments,
    ) {}

    public function handle(Booking $booking, string $paymentIntentId, ?CarbonImmutable $now = null): Booking
    {
        $now ??= now();

        $confirmed = DB::transaction(function () use ($booking, $paymentIntentId, $now) {
            Bay::query()->whereKey($booking->bay_id)->lockForUpdate()->first();
            $booking->refresh();

            if ($booking->status === BookingStatus::Confirmed || $booking->refunded_at !== null) {
                return false;
            }

            $slotLost = $booking->status === BookingStatus::Cancelled
                || Booking::query()
                    ->where('bay_id', $booking->bay_id)
                    ->whereKeyNot($booking->getKey())
                    ->blocking($now)
                    ->overlapping($booking->starts_at, $booking->ends_at)
                    ->exists();

            if ($slotLost) {
                $this->payments->refund($paymentIntentId);

                $booking->update([
                    'status' => BookingStatus::Expired,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'refunded_at' => $now,
                ]);

                return false;
            }

            $booking->update([
                'status' => BookingStatus::Confirmed,
                'stripe_payment_intent_id' => $paymentIntentId,
                'door_code' => $this->doorCodes->issueFor($booking),
                'hold_expires_at' => null,
                'confirmed_at' => $now,
            ]);

            return true;
        });

        if ($confirmed) {
            Mail::to($booking->customer_email)->send(new BookingConfirmed($booking));
        }

        return $booking;
    }
}
