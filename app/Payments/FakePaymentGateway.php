<?php

namespace App\Payments;

use App\Models\Booking;
use Illuminate\Support\Str;

/** In-memory gateway for tests. Records every call. */
final class FakePaymentGateway implements PaymentGateway
{
    /** @var list<string> */
    public array $checkouts = [];

    /** @var list<string> */
    public array $expired = [];

    /** @var list<string> */
    public array $refunds = [];

    /** Make every call fail, as if Stripe were down. */
    public bool $down = false;

    public function createCheckout(Booking $booking, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $this->failIfDown();
        $id = 'cs_test_'.Str::random(24);
        $this->checkouts[] = $booking->id;

        return new CheckoutSession($id, "https://checkout.stripe.test/{$id}");
    }

    public function expireCheckout(string $sessionId): void
    {
        $this->expired[] = $sessionId;
    }

    public function refund(string $paymentIntentId): void
    {
        $this->failIfDown();
        $this->refunds[] = $paymentIntentId;
    }

    private function failIfDown(): void
    {
        if ($this->down) {
            throw new PaymentFailed('Stripe is down (fake).');
        }
    }
}
