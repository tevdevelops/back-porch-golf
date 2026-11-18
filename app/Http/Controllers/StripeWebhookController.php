<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmBooking;
use App\Actions\ExpireBooking;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Stripe tells us here when a checkout is paid or runs out. This, not the
 * customer's browser, is what confirms a booking.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, ConfirmBooking $confirm, ExpireBooking $expire): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response('Invalid signature', 400);
        }

        // We only subscribe to checkout session events.
        $session = $event->data->object;
        if (! $session instanceof Session) {
            return response('Ignored', 200);
        }

        $bookingId = $session->metadata['booking_id'] ?? null;
        $booking = is_string($bookingId) ? Booking::query()->find($bookingId) : null;

        if ($booking === null) {
            Log::warning('Stripe webhook for unknown booking', ['event' => $event->id, 'type' => $event->type]);

            return response('Ignored', 200);
        }

        $paymentIntentId = $session->payment_intent instanceof PaymentIntent
            ? $session->payment_intent->id
            : $session->payment_intent;

        match ($event->type) {
            'checkout.session.completed' => $session->payment_status === Session::PAYMENT_STATUS_PAID && $paymentIntentId !== null
                ? $confirm->handle($booking, $paymentIntentId)
                : null,
            'checkout.session.expired' => $expire->handle($booking),
            default => null,
        };

        return response('OK', 200);
    }
}
