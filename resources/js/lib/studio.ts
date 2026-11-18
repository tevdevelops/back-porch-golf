import type { Studio } from '@/types';

export function money(cents: number): string {
    return `$${(cents / 100).toFixed(2)}`;
}

export function hours(minutes: number): string {
    const h = minutes / 60;
    return `${h} ${h === 1 ? 'hour' : 'hours'}`;
}

export function plural(n: number, word: string): string {
    return `${n} ${n === 1 ? word : `${word}s`}`;
}

/** "7:30 PM" in the studio's timezone. */
export function clock(at: Date, timezone: string): string {
    return new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        hour: 'numeric',
        minute: '2-digit',
    }).format(at);
}

/** "7pm" in the studio's timezone. */
export function hourLabel(at: Date, timezone: string): string {
    return new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        hour: 'numeric',
    })
        .format(at)
        .replace(' ', '')
        .toLowerCase();
}

export function addMinutes(at: Date | string, minutes: number): Date {
    return new Date(new Date(at).getTime() + minutes * 60_000);
}

/** ISO string without milliseconds, as the server expects. */
export function isoMinute(at: Date): string {
    return at.toISOString().replace(/\.\d{3}Z$/, 'Z');
}

/** Same formula as App\Support\BookingPrice. Preview only. */
export function quote(studio: Studio, minutes: number, players: number) {
    const hourly =
        studio.rate_cents + studio.extra_player_cents * (players - 1);
    const subtotal = Math.round((hourly * minutes) / 60);
    const tax = Math.round(subtotal * studio.tax_rate);

    return { hourly, subtotal, tax, total: subtotal + tax };
}
