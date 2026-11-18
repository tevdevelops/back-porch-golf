<?php

namespace App\Support;

/**
 * The price of a booking, in cents. The server's copy is the only one that
 * counts; the browser shows a preview with the same formula.
 */
final readonly class BookingPrice
{
    public function __construct(
        public int $hourlyCents,
        public int $subtotalCents,
        public int $taxCents,
        public int $totalCents,
    ) {}

    public static function for(int $minutes, int $players): self
    {
        $hourly = config('studio.rate_cents')
            + config('studio.extra_player_cents') * ($players - 1);

        $subtotal = (int) round($hourly * $minutes / 60);
        $tax = (int) round($subtotal * config('studio.tax_rate'));

        return new self($hourly, $subtotal, $tax, $subtotal + $tax);
    }
}
