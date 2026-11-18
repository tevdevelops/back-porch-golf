<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Bay;
use App\Models\Booking;
use App\Support\BookingPrice;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $start = CarbonImmutable::now()->addDay()->startOfHour();
        $price = BookingPrice::for(60, 2);

        return [
            'bay_id' => Bay::factory(),
            'starts_at' => $start,
            'ends_at' => $start->addHour(),
            'players' => 2,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('(610) ###-####'),
            'status' => BookingStatus::Confirmed,
            'hold_expires_at' => null,
            'subtotal_cents' => $price->subtotalCents,
            'tax_cents' => $price->taxCents,
            'total_cents' => $price->totalCents,
            'stripe_checkout_session_id' => 'cs_test_'.Str::random(24),
            'stripe_payment_intent_id' => 'pi_test_'.Str::random(24),
            'door_code' => fake()->numerify('######'),
            'confirmed_at' => now(),
        ];
    }

    /** Place the booking at a time, for a length, keeping the price right. */
    public function at(CarbonImmutable $start, int $minutes = 60, int $players = 2): static
    {
        $price = BookingPrice::for($minutes, $players);

        return $this->state([
            'starts_at' => $start,
            'ends_at' => $start->addMinutes($minutes),
            'players' => $players,
            'subtotal_cents' => $price->subtotalCents,
            'tax_cents' => $price->taxCents,
            'total_cents' => $price->totalCents,
        ]);
    }

    public function held(?CarbonImmutable $until = null): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Held,
            'hold_expires_at' => $until ?? now()->addMinutes(30),
            'stripe_payment_intent_id' => null,
            'door_code' => null,
            'confirmed_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->held()->state(['status' => BookingStatus::Expired, 'hold_expires_at' => null]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'refunded_at' => now(),
        ]);
    }
}
