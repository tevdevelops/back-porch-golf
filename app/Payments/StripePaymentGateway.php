<?php

namespace App\Payments;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

final class StripePaymentGateway implements PaymentGateway
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function createCheckout(Booking $booking, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $start = $booking->localStart();
        $hours = $booking->minutes() / 60;

        try {
            $session = $this->createSession($booking, $start, $hours, $successUrl, $cancelUrl);
        } catch (ApiErrorException $e) {
            throw new PaymentFailed('Stripe could not start a checkout: '.$e->getMessage(), previous: $e);
        }

        return new CheckoutSession($session->id, $session->url);
    }

    private function createSession(Booking $booking, CarbonImmutable $start, float $hours, string $successUrl, string $cancelUrl): Session
    {
        return $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $booking->customer_email,
            'client_reference_id' => $booking->id,
            'metadata' => ['booking_id' => $booking->id],
            'payment_intent_data' => ['metadata' => ['booking_id' => $booking->id]],
            // Stripe's minimum is 30 minutes; the Hold lasts slightly longer.
            'expires_at' => now()->addMinutes(config('studio.hold_minutes'))->addMinute()->timestamp,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $booking->subtotal_cents,
                        'product_data' => [
                            'name' => "{$booking->bay->name} · {$start->format('D M j, g:i A')}",
                            'description' => sprintf(
                                '%s %s, %d %s',
                                rtrim(rtrim(number_format($hours, 1), '0'), '.'),
                                $hours === 1.0 ? 'hour' : 'hours',
                                $booking->players,
                                $booking->players === 1 ? 'player' : 'players',
                            ),
                        ],
                    ],
                ],
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $booking->tax_cents,
                        'product_data' => ['name' => 'Amusement tax (5%)'],
                    ],
                ],
            ],
        ]);
    }

    public function expireCheckout(string $sessionId): void
    {
        try {
            $this->stripe->checkout->sessions->expire($sessionId);
        } catch (InvalidRequestException) {
            // Already paid or expired; nothing to close.
        }
    }

    public function refund(string $paymentIntentId): void
    {
        try {
            $this->stripe->refunds->create(
                ['payment_intent' => $paymentIntentId],
                ['idempotency_key' => "refund-{$paymentIntentId}"],
            );
        } catch (ApiErrorException $e) {
            throw new PaymentFailed('Stripe could not refund: '.$e->getMessage(), previous: $e);
        }
    }
}
