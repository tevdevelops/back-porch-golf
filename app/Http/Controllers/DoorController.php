<?php

namespace App\Http\Controllers;

use App\Support\DoorCodes;
use App\Support\StudioClock;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Demo of the studio's door keypad. The optional "pretend time" shows how a
 * code only works during its booking's Access window.
 */
class DoorController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Door', [
            'now' => StudioClock::local(now())->format('Y-m-d\TH:i'),
            'lead_minutes' => config('studio.door_code_lead_minutes'),
        ]);
    }

    public function check(Request $request, DoorCodes $codes): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);

        $at = isset($data['at'])
            ? CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['at'], StudioClock::timezone())->utc()
            : now();

        $booking = $codes->bookingFor($data['code'], $at);

        return back()->with('door', [
            'unlocked' => $booking !== null,
            'message' => $booking
                ? "Unlocked. Enjoy {$booking->bay->name}!"
                : 'Denied. That code is not active at this time.',
            'at' => StudioClock::local($at)->format('D M j, g:i A'),
        ]);
    }
}
