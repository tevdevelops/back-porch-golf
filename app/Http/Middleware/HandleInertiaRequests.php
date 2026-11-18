<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'studio' => [
                'timezone' => config('studio.timezone'),
                'rate_cents' => config('studio.rate_cents'),
                'extra_player_cents' => config('studio.extra_player_cents'),
                'tax_rate' => config('studio.tax_rate'),
                'max_players' => config('studio.max_players'),
                'slot_minutes' => config('studio.slot_minutes'),
                'min_minutes' => config('studio.min_minutes'),
                'max_minutes' => config('studio.max_minutes'),
                'cancel_cutoff_hours' => config('studio.cancel_cutoff_hours'),
                'door_code_lead_minutes' => config('studio.door_code_lead_minutes'),
            ],
            'flash' => [
                'notice' => fn () => $request->session()->get('notice'),
                'door' => fn () => $request->session()->get('door'),
            ],
        ];
    }
}
