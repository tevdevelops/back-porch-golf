<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Bay;
use App\Models\Booking;
use App\Support\StudioClock;
use Illuminate\Database\Seeder;

/**
 * Two bays and a believable two weeks of Confirmed bookings, so the grid
 * shows Complete, In Progress and Booked blocks.
 *
 * Safe to rerun (the live demo reseeds weekly): it replaces only the fake
 * bookings, which all use @example.* addresses.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(crc32(StudioClock::today()));

        $bays = collect(['Bay 1', 'Bay 2'])->map(
            fn (string $name, int $i) => Bay::firstOrCreate(['name' => $name], ['position' => $i + 1]),
        );

        Booking::query()->where('customer_email', 'like', '%@example.%')->delete();

        $firstDay = StudioClock::dayStart(StudioClock::today())->subDay();

        foreach ($bays as $bay) {
            for ($day = 0; $day <= config('studio.booking_window_days'); $day++) {
                $dayStart = $firstDay->addDays($day);
                // Later days are quieter: fewer people book far ahead.
                $busy = $day <= 3 ? 0.75 : 0.45;
                $cursor = $dayStart->addHours(6);

                while ($cursor->lt($dayStart->addHours(23))) {
                    $cursor = $cursor->addMinutes(30 * mt_rand(1, 6));
                    $minutes = 30 * mt_rand(2, 6);

                    if (mt_rand() / mt_getrandmax() > $busy) {
                        continue;
                    }

                    Booking::factory()
                        ->for($bay)
                        ->at($cursor, $minutes, mt_rand(1, 4))
                        ->create([
                            'status' => BookingStatus::Confirmed,
                            'confirmed_at' => $cursor->subDays(mt_rand(1, 6)),
                        ]);

                    $cursor = $cursor->addMinutes($minutes);
                }
            }
        }
    }
}
