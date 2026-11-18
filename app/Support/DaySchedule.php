<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\CarbonImmutable;

/**
 * Everything the public grid needs for one studio day: its length and the
 * blocks that take up time on each bay. No customer details leave here.
 */
final class DaySchedule
{
    /**
     * @return array{
     *     date: string,
     *     starts_at: string,
     *     minutes: int,
     *     blocks: list<array{bay_id: int, offset: int, minutes: int, starts_at: string, ends_at: string, status: string}>
     * }
     */
    public static function for(string $date, ?CarbonImmutable $now = null): array
    {
        $now ??= now();
        $dayStart = StudioClock::dayStart($date);
        $dayEnd = StudioClock::dayEnd($date);

        // Look a few hours past midnight too, so the grid knows how long a
        // late-night booking can run. Those blocks have offset >= minutes.
        $blocks = array_values(Booking::query()
            ->blocking($now)
            ->overlapping($dayStart, $dayEnd->addMinutes(config('studio.max_minutes')))
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Booking $b) => [
                'bay_id' => $b->bay_id,
                // Minutes from local midnight; negative if it began last night.
                'offset' => (int) $dayStart->diffInMinutes($b->starts_at, false),
                'minutes' => $b->minutes(),
                'starts_at' => $b->starts_at->toIso8601ZuluString(),
                'ends_at' => $b->ends_at->toIso8601ZuluString(),
                'status' => self::gridStatus($b, $now),
            ])
            ->all());

        return [
            'date' => $date,
            'starts_at' => $dayStart->toIso8601ZuluString(),
            'minutes' => (int) $dayStart->diffInMinutes($dayEnd),
            'blocks' => $blocks,
        ];
    }

    public static function gridStatus(Booking $booking, CarbonImmutable $now): string
    {
        return match (true) {
            $booking->ends_at->lte($now) => 'Complete',
            $booking->starts_at->lte($now) => 'In Progress',
            default => 'Booked',
        };
    }
}
