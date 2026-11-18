<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;

/**
 * Door codes are six digits and only open the door during a booking's
 * Access window (from a few minutes before the start until the end).
 */
final class DoorCodes
{
    /** A code no other Confirmed booking can use at the same time. */
    public function issueFor(Booking $booking): string
    {
        $lead = config('studio.door_code_lead_minutes');

        do {
            $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

            $clash = Booking::query()
                ->where('status', BookingStatus::Confirmed)
                ->where('door_code', $code)
                ->whereKeyNot($booking->getKey())
                // Access windows [start - lead, end) overlap.
                ->where('starts_at', '<', $booking->ends_at->addMinutes($lead))
                ->where('ends_at', '>', $booking->accessStartsAt())
                ->exists();
        } while ($clash);

        return $code;
    }

    /** The Confirmed booking this code opens the door for at $at, if any. */
    public function bookingFor(string $code, CarbonImmutable $at): ?Booking
    {
        return Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->where('door_code', $code)
            ->where('starts_at', '<=', $at->addMinutes(config('studio.door_code_lead_minutes')))
            ->where('ends_at', '>', $at)
            ->first();
    }
}
