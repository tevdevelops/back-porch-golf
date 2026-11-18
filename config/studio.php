<?php

/*
|--------------------------------------------------------------------------
| Studio rules
|--------------------------------------------------------------------------
|
| Prices, booking rules and timing for Back Porch Golf Co. These mirror the
| real two-bay studio plan in ~/code/golf-sim-studio. Money is in cents.
| Times are stored in UTC and shown in the studio's timezone.
|
*/

return [

    'name' => 'Back Porch Golf Co.',

    'timezone' => 'America/New_York',

    // Price per bay-hour, including the first player.
    'rate_cents' => 3500,

    // Added per hour for each player after the first.
    'extra_player_cents' => 800,

    // Amusement tax on the bay-time subtotal.
    'tax_rate' => 0.05,

    'max_players' => 4,

    // Bookings start on these boundaries and grow in these steps.
    'slot_minutes' => 30,

    'min_minutes' => 60,

    'max_minutes' => 240,

    // A booking must start within this many days, counting today.
    'booking_window_days' => 14,

    // How long a Held booking blocks its slot while the customer pays.
    // Stripe Checkout sessions cannot expire sooner than 30 minutes.
    'hold_minutes' => 30,

    // Cancelling at least this many hours before the start is refunded.
    'cancel_cutoff_hours' => 24,

    // A Door code starts working this many minutes before the booking.
    'door_code_lead_minutes' => 10,

];
