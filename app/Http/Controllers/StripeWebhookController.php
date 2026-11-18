<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmBooking;
use App\Actions\ExpireBooking;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
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

        $session = $event->data->object;
        $booking = Booking::query()->find($session->metadata->booking_id ?? null);

        if ($booking === null) {
            Log::warning('Stripe webhook for unknown booking', ['event' => $event->id, 'type' => $event->type]);

            return response('Ignored', 200);
        }

        match ($event->type) {
            'checkout.session.completed' => $session->payment_status === 'paid'
                ? $confirm->handle($booking, (string) $session->payment_intent)
                : null,
            'checkout.session.expired' => $expire->handle($booking),
            default => null,
        };

        return response('OK', 200);
    }
}
