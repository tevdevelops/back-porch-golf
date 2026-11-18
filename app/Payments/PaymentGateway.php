<?php

namespace App\Payments;

use App\Models\Booking;

/**
 * The few payment operations the studio needs. Stripe in the app, a fake in
 * tests.
 */
interface PaymentGateway
{
    /**
     * Starts a hosted checkout for a Held booking.
     *
     * @throws PaymentFailed
     */
    public function createCheckout(Booking $booking, string $successUrl, string $cancelUrl): CheckoutSession;

    /** Closes an unpaid checkout so it can no longer be paid. */
    public function expireCheckout(string $sessionId): void;

    /**
     * Refunds a payment in full.
     *
     * @throws PaymentFailed
     */
    public function refund(string $paymentIntentId): void;
}
