// PROTOTYPE — throwaway. Fake data and booking math shared by the three
// design variants on /prototype/design. Not production code.
import { computed, ref, watch } from 'vue';

export type Status = 'Booked' | 'In Progress' | 'Complete';

/** A booking block on the shown day, in minutes from that day's midnight.
 *  start < 0 means it began last night; end > 1440 means it runs past midnight. */
export interface Block {
    bay: number;
    start: number;
    end: number;
}

export const bays = [
    { id: 1, name: 'Bay 1' },
    { id: 2, name: 'Bay 2' },
];

/** Pretend "now": 8:08 PM on the first day. */
export const NOW = 20 * 60 + 8;

export const SLOT = 30;
export const slots = Array.from({ length: 48 }, (_, i) => i * SLOT);

const firstDay = new Date(2026, 10, 17);
export const days = Array.from({ length: 14 }, (_, i) => {
    const d = new Date(firstDay);
    d.setDate(d.getDate() + i);
    return {
        index: i,
        weekday: d.toLocaleDateString('en-US', { weekday: 'short' }),
        day: d.getDate(),
        month: d.toLocaleDateString('en-US', { month: 'short' }),
        long: d.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
        }),
    };
});

const todayBlocks: Block[] = [
    { bay: 1, start: -60, end: 60 },
    { bay: 1, start: 7 * 60, end: 8 * 60 },
    { bay: 1, start: 14 * 60 + 30, end: 16 * 60 },
    { bay: 1, start: 17 * 60 + 30, end: 19 * 60 + 30 },
    { bay: 1, start: 19 * 60 + 30, end: 21 * 60 },
    { bay: 1, start: 23 * 60, end: 25 * 60 },
    { bay: 2, start: 6 * 60, end: 7 * 60 + 30 },
    { bay: 2, start: 10 * 60, end: 11 * 60 },
    { bay: 2, start: 12 * 60 + 30, end: 14 * 60 + 30 },
    { bay: 2, start: 16 * 60 + 30, end: 18 * 60 + 30 },
    { bay: 2, start: 21 * 60, end: 23 * 60 },
];

const laterBlocks: Block[] = [
    { bay: 1, start: -60, end: 60 },
    { bay: 1, start: 9 * 60, end: 10 * 60 + 30 },
    { bay: 1, start: 18 * 60, end: 20 * 60 },
    { bay: 2, start: 12 * 60, end: 13 * 60 },
    { bay: 2, start: 19 * 60, end: 21 * 60 + 30 },
];

export function blocksFor(dayIndex: number): Block[] {
    if (dayIndex === 0) return todayBlocks;
    return dayIndex % 2 === 0 ? laterBlocks : laterBlocks.slice(1);
}

export function nowFor(dayIndex: number): number {
    return dayIndex === 0 ? NOW : -Infinity;
}

export function statusOf(b: Block, now: number): Status {
    if (b.end <= now) return 'Complete';
    if (b.start <= now) return 'In Progress';
    return 'Booked';
}

export function clock(min: number): string {
    const m = ((min % 1440) + 1440) % 1440;
    const h24 = Math.floor(m / 60);
    const mm = m % 60;
    const h12 = h24 % 12 === 0 ? 12 : h24 % 12;
    return `${h12}:${String(mm).padStart(2, '0')} ${h24 < 12 ? 'AM' : 'PM'}`;
}

export function hourLabel(min: number): string {
    const h24 = Math.floor(min / 60) % 24;
    if (h24 === 0) return '12am';
    if (h24 === 12) return '12pm';
    return h24 < 12 ? `${h24}am` : `${h24 - 12}pm`;
}

export function hours(min: number): string {
    const h = min / 60;
    return `${h} ${h === 1 ? 'hour' : 'hours'}`;
}

export function money(cents: number): string {
    return `$${(cents / 100).toFixed(2)}`;
}

export function isTaken(blocks: Block[], bay: number, start: number) {
    return blocks.some(
        (b) => b.bay === bay && b.start < start + SLOT && b.end > start,
    );
}

export function isOpen(
    blocks: Block[],
    bay: number,
    start: number,
    now: number,
) {
    return start >= now && !isTaken(blocks, bay, start);
}

/** Longest booking (≤ 4 hrs) that fits before the next block on the bay. */
export function maxDuration(blocks: Block[], bay: number, start: number) {
    const next = blocks
        .filter((b) => b.bay === bay && b.start >= start + SLOT)
        .reduce((min, b) => Math.min(min, b.start), Infinity);
    return Math.min(240, next - start);
}

export const RATE = 3500;
export const EXTRA_PLAYER = 800;
export const TAX = 0.05;

export function price(durationMin: number, players: number) {
    const hourly = RATE + EXTRA_PLAYER * (players - 1);
    const subtotal = Math.round((hourly * durationMin) / 60);
    const tax = Math.round(subtotal * TAX);
    return { hourly, subtotal, tax, total: subtotal + tax };
}

/** The in-progress booking a customer is building. */
export function useDraft() {
    const dayIndex = ref(0);
    const bay = ref<number | null>(null);
    const start = ref<number | null>(null);
    const duration = ref(60);
    const players = ref(2);

    const blocks = computed(() => blocksFor(dayIndex.value));
    const now = computed(() => nowFor(dayIndex.value));

    const durations = computed(() => {
        const max =
            bay.value === null || start.value === null
                ? 240
                : maxDuration(blocks.value, bay.value, start.value);
        const list: number[] = [];
        for (let d = 60; d <= max; d += 30) list.push(d);
        if (list.length === 0) list.push(30);
        return list;
    });

    watch(durations, (list) => {
        if (!list.includes(duration.value))
            duration.value = list[list.length - 1];
    });

    watch(dayIndex, () => clear());

    const quote = computed(() => price(duration.value, players.value));

    function pick(b: number, s: number) {
        bay.value = b;
        start.value = s;
    }

    function clear() {
        bay.value = null;
        start.value = null;
    }

    return {
        dayIndex,
        bay,
        start,
        duration,
        players,
        blocks,
        now,
        durations,
        quote,
        pick,
        clear,
    };
}
