<?php

namespace App\Http\Controllers;

use App\Models\Bay;
use App\Support\DaySchedule;
use App\Support\StudioClock;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingCalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $dates = StudioClock::bookableDates();
        $date = in_array($request->query('date'), $dates, true) ? $request->query('date') : $dates[0];

        return Inertia::render('Book', [
            'date' => $date,
            'days' => collect($dates)->map(function (string $d) use ($dates) {
                $day = CarbonImmutable::createFromFormat('!Y-m-d', $d);

                return [
                    'date' => $d,
                    'weekday' => $d === $dates[0] ? 'Today' : $day->format('D'),
                    'day' => (int) $day->format('j'),
                    'long' => $day->format('l, F j'),
                ];
            }),
            'bays' => Bay::query()->orderBy('position')->get(['id', 'name']),
            'schedule' => DaySchedule::for($date),
            'now' => now()->toIso8601ZuluString(),
        ]);
    }
}
