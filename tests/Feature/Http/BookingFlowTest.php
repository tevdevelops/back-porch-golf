<?php

use App\Enums\BookingStatus;
use App\Models\Bay;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(studioTime('2026-11-17 20:08'));
    $this->payments = fakePayments();
    Mail::fake();
    $this->bay = Bay::factory()->create(['name' => 'Bay 1']);
});

function webhook(string $type, array $session): TestResponse
{
    config(['services.stripe.webhook_secret' => 'whsec_test']);
    $payload = json_encode([
        'id' => 'evt_test',
        'object' => 'event',
        'type' => $type,
        'data' => ['object' => ['object' => 'checkout.session', ...$session]],
    ]);
    $time = time();
    $signature = hash_hmac('sha256', "{$time}.{$payload}", 'whsec_test');

    return test()->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $payload);
}

it('shows the landing page and the booking grid', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-17 19:30'), 90)->create();

    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Home'));

    $this->get('/book')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Book')
        ->where('date', '2026-11-17')
        ->has('days', 14)
        ->has('bays', 1)
        ->where('schedule.blocks.0.status', 'In Progress'));
});

it('falls back to today for a date outside the booking window', function () {
    $this->get('/book?date=2027-01-01')
        ->assertInertia(fn (Assert $page) => $page->where('date', '2026-11-17'));
});

it('holds the slot and sends the customer to Stripe', function () {
    $response = $this->post('/bookings', [
        'bay_id' => $this->bay->id,
        'starts_at' => studioTime('2026-11-18 18:00')->format('Y-m-d\TH:i:s\Z'),
        'minutes' => 120,
        'players' => 3,
        'name' => 'Pat Doe',
        'email' => 'pat@example.com',
        'phone' => '(610) 555-0100',
    ], ['X-Inertia' => 'true']);

    $booking = Booking::sole();
    $response->assertStatus(409)->assertHeader('X-Inertia-Location', "https://checkout.stripe.test/{$booking->stripe_checkout_session_id}");
    expect($booking->status)->toBe(BookingStatus::Held)
        ->and($booking->total_cents)->toBe(10710);
});

it('sends the customer back with errors when the slot is taken', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:00'))->create();

    $this->from('/book')->post('/bookings', [
        'bay_id' => $this->bay->id,
        'starts_at' => studioTime('2026-11-18 18:00')->format('Y-m-d\TH:i:s\Z'),
        'minutes' => 60,
        'players' => 2,
        'name' => 'Pat Doe',
        'email' => 'pat@example.com',
        'phone' => '610-555-0100',
    ])->assertRedirect('/book')->assertSessionHasErrors('starts_at');

    expect($this->payments->checkouts)->toBe([]);
});

it('releases the slot and explains when Stripe cannot start a checkout', function () {
    $this->payments->down = true;

    $this->from('/book')->post('/bookings', [
        'bay_id' => $this->bay->id,
        'starts_at' => studioTime('2026-11-18 18:00')->format('Y-m-d\TH:i:s\Z'),
        'minutes' => 60,
        'players' => 2,
        'name' => 'Pat Doe',
        'email' => 'pat@example.com',
        'phone' => '610-555-0100',
    ])->assertRedirect('/book')->assertSessionHasErrors('starts_at');

    expect(Booking::sole()->status)->toBe(BookingStatus::Expired);
});

it('keeps the booking when a refund fails', function () {
    $booking = Booking::factory()->for($this->bay)->at(studioTime('2026-11-20 18:00'))->create();
    $this->payments->down = true;

    $this->post(URL::signedRoute('bookings.cancel', $booking))->assertSessionHasErrors('booking');

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed);
});

it('confirms the booking when Stripe reports payment', function () {
    $booking = Booking::factory()->for($this->bay)->held()->create(['stripe_checkout_session_id' => 'cs_1']);

    webhook('checkout.session.completed', [
        'id' => 'cs_1',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_1',
        'metadata' => ['booking_id' => $booking->id],
    ])->assertOk();

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->door_code)->not->toBeNull();
});

it('releases the slot when the Stripe session expires', function () {
    $booking = Booking::factory()->for($this->bay)->held()->create();

    webhook('checkout.session.expired', [
        'id' => 'cs_1',
        'payment_status' => 'unpaid',
        'payment_intent' => null,
        'metadata' => ['booking_id' => $booking->id],
    ])->assertOk();

    expect($booking->refresh()->status)->toBe(BookingStatus::Expired);
});

it('rejects webhooks without a valid signature', function () {
    config(['services.stripe.webhook_secret' => 'whsec_test']);

    $this->postJson('/stripe/webhook', ['type' => 'checkout.session.completed'], ['Stripe-Signature' => 't=1,v1=nope'])
        ->assertStatus(400);
});

it('only opens booking pages from signed links', function () {
    $booking = Booking::factory()->for($this->bay)->create();

    $this->get("/bookings/{$booking->id}")->assertForbidden();

    $this->get($booking->manageUrl())->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Bookings/Show')
        ->where('booking.status', 'confirmed')
        ->where('booking.door_code', $booking->door_code)
        ->missing('booking.customer_phone'));
});

it('releases the hold when the customer backs out of Stripe', function () {
    $booking = Booking::factory()->for($this->bay)->held()->at(studioTime('2026-11-18 18:00'))->create(['stripe_checkout_session_id' => 'cs_back']);

    $this->get(URL::signedRoute('bookings.abandon', $booking))
        ->assertRedirect(route('book', ['date' => '2026-11-18']));

    expect($booking->refresh()->status)->toBe(BookingStatus::Expired)
        ->and($this->payments->expired)->toBe(['cs_back']);
});

it('cancels from the signed link', function () {
    $booking = Booking::factory()->for($this->bay)->at(studioTime('2026-11-20 18:00'))->create();

    $this->post(URL::signedRoute('bookings.cancel', $booking))->assertRedirect($booking->manageUrl());

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
});

it('unlocks the demo keypad only inside the access window', function () {
    Booking::factory()->for($this->bay)->at(studioTime('2026-11-18 18:00'))->create(['door_code' => '135790']);

    $this->from('/door')->post('/door', ['code' => '135790', 'at' => '2026-11-18T17:55'])
        ->assertSessionHas('door.unlocked', true);

    $this->from('/door')->post('/door', ['code' => '135790', 'at' => '2026-11-18T19:00'])
        ->assertSessionHas('door.unlocked', false);
});
