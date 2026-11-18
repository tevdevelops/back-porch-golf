<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Studio-local calendar helpers. Bookings are stored in UTC; a "studio day"
 * runs from local midnight to the next local midnight (23–25 hours around
 * DST changes).
 */
final class StudioClock
{
    public static function timezone(): string
    {
        return config('studio.timezone');
    }

    public static function local(CarbonImmutable $moment): CarbonImmutable
    {
        return $moment->setTimezone(self::timezone());
    }

    /** Local midnight that starts the given Y-m-d date, as a UTC instant. */
    public static function dayStart(string $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, self::timezone())->utc();
    }

    public static function dayEnd(string $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, self::timezone())
            ->addDay()
            ->utc();
    }

    public static function today(?CarbonImmutable $now = null): string
    {
        return self::local($now ?? now())->toDateString();
    }

    /**
     * The local dates inside the Booking window, starting today.
     *
     * @return list<string>
     */
    public static function bookableDates(?CarbonImmutable $now = null): array
    {
        $today = self::local($now ?? now())->startOfDay();

        return collect(range(0, config('studio.booking_window_days') - 1))
            ->map(fn (int $i) => $today->addDays($i)->toDateString())
            ->all();
    }

    /** First instant after the Booking window. */
    public static function windowEnd(?CarbonImmutable $now = null): CarbonImmutable
    {
        return self::local($now ?? now())
            ->startOfDay()
            ->addDays(config('studio.booking_window_days'))
            ->utc();
    }
}
