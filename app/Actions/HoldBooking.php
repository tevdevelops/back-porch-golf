<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Bay;
use App\Models\Booking;
use App\Support\BookingPrice;
use App\Support\StudioClock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates a Held booking if the slot is open and every studio rule passes.
 * The Hold keeps the slot while the customer pays on Stripe.
 */
final class HoldBooking
{
    /**
     * @param  array{name: string, email: string, phone: string}  $customer
     *
     * @throws ValidationException
     */
    public function handle(
        Bay $bay,
        CarbonImmutable $start,
        int $minutes,
        int $players,
        array $customer,
        ?CarbonImmutable $now = null,
    ): Booking {
        $now ??= now();
        $start = $start->utc();
        $end = $start->addMinutes($minutes);

        $this->checkRules($start, $minutes, $players, $now);

        return DB::transaction(function () use ($bay, $start, $end, $minutes, $players, $customer, $now) {
            // Serialise bookings per bay so two customers can't both pass the
            // overlap check for the same slot.
            Bay::query()->whereKey($bay->getKey())->lockForUpdate()->first();

            $taken = Booking::query()
                ->where('bay_id', $bay->getKey())
                ->blocking($now)
                ->overlapping($start, $end)
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Someone just booked that time. Please pick another slot.',
                ]);
            }

            $price = BookingPrice::for($minutes, $players);

            return Booking::create([
                'bay_id' => $bay->getKey(),
                'starts_at' => $start,
                'ends_at' => $end,
                'players' => $players,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'status' => BookingStatus::Held,
                // A little longer than the Stripe session, so the slot never
                // frees up while the customer can still pay.
                'hold_expires_at' => $now->addMinutes(config('studio.hold_minutes') + 2),
                'subtotal_cents' => $price->subtotalCents,
                'tax_cents' => $price->taxCents,
                'total_cents' => $price->totalCents,
            ]);
        });
    }

    private function checkRules(CarbonImmutable $start, int $minutes, int $players, CarbonImmutable $now): void
    {
        $slot = config('studio.slot_minutes');
        $local = StudioClock::local($start);
        $errors = [];

        if ($local->second !== 0 || $local->minute % $slot !== 0) {
            $errors['starts_at'] = 'Bookings start on the hour or half hour.';
        } elseif ($start->lt($now)) {
            $errors['starts_at'] = 'That time has already passed.';
        } elseif ($start->gte(StudioClock::windowEnd($now))) {
            $errors['starts_at'] = 'You can book up to '.config('studio.booking_window_days').' days ahead.';
        }

        if ($minutes < config('studio.min_minutes')
            || $minutes > config('studio.max_minutes')
            || $minutes % $slot !== 0) {
            $errors['minutes'] = 'Bookings run 1 to 4 hours, in half-hour steps.';
        }

        if ($players < 1 || $players > config('studio.max_players')) {
            $errors['players'] = 'A bay holds 1 to '.config('studio.max_players').' players.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
