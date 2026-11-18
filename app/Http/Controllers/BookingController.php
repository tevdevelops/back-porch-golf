<?php

namespace App\Http\Controllers;

use App\Actions\CancelBooking;
use App\Actions\ExpireBooking;
use App\Actions\HoldBooking;
use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Bay;
use App\Models\Booking;
use App\Payments\PaymentFailed;
use App\Payments\PaymentGateway;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BookingController extends Controller
{
    /** Hold the slot, then send the customer to Stripe Checkout. */
    public function store(StoreBookingRequest $request, HoldBooking $hold, PaymentGateway $payments, ExpireBooking $expire): HttpResponse
    {
        $booking = $hold->handle(
            Bay::findOrFail($request->integer('bay_id')),
            CarbonImmutable::parse($request->string('starts_at')),
            $request->integer('minutes'),
            $request->integer('players'),
            $request->only('name', 'email', 'phone'),
        );

        try {
            $checkout = $payments->createCheckout(
                $booking,
                successUrl: URL::signedRoute('bookings.show', ['booking' => $booking, 'paid' => 1]),
                cancelUrl: URL::signedRoute('bookings.abandon', $booking),
            );
        } catch (PaymentFailed $e) {
            report($e);
            $expire->handle($booking);

            throw ValidationException::withMessages([
                'starts_at' => "We couldn't start the payment just now. Please try again in a minute.",
            ]);
        }

        $booking->update(['stripe_checkout_session_id' => $checkout->id]);

        return Inertia::location($checkout->url);
    }

    public function show(Booking $booking): Response
    {
        $start = $booking->localStart();

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status->value,
                'bay' => $booking->bay->name,
                'date' => $start->format('l, F j'),
                'time' => $start->format('g:i A').' – '.$booking->localEnd()->format('g:i A'),
                'hours' => $booking->minutes() / 60,
                'players' => $booking->players,
                'name' => $booking->customer_name,
                'email' => $booking->customer_email,
                'subtotal_cents' => $booking->subtotal_cents,
                'tax_cents' => $booking->tax_cents,
                'total_cents' => $booking->total_cents,
                'door_code' => $booking->status === BookingStatus::Confirmed ? $booking->door_code : null,
                'door_opens' => $booking->accessStartsAt()->setTimezone(config('studio.timezone'))->format('g:i A'),
                'refunded' => $booking->refunded_at !== null,
                'can_cancel' => $booking->canBeCancelled(),
                'cancel_url' => URL::signedRoute('bookings.cancel', $booking),
                'book_again_url' => route('book', ['date' => $start->toDateString()]),
            ],
            'paid' => request()->boolean('paid'),
        ]);
    }

    /** Stripe's "back" link: release the slot straight away. */
    public function abandon(Booking $booking, ExpireBooking $expire): RedirectResponse
    {
        $expire->handle($booking, closeCheckout: true);

        return redirect()
            ->route('book', ['date' => $booking->localStart()->toDateString()])
            ->with('notice', 'Payment cancelled. Your slot has been released.');
    }

    public function cancel(Booking $booking, CancelBooking $cancel): RedirectResponse
    {
        try {
            $cancel->handle($booking);
        } catch (PaymentFailed $e) {
            report($e);

            throw ValidationException::withMessages([
                'booking' => "We couldn't process the refund just now, so your booking is still active. Please try again in a minute.",
            ]);
        }

        return redirect()->to($booking->manageUrl())
            ->with('notice', 'Your booking is cancelled and your refund is on its way.');
    }
}
