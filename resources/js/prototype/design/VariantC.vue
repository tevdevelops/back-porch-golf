<script setup lang="ts">
// PROTOTYPE — Variant C "Clean modern": booking-first app layout. Players
// and length are chosen first; the grid then highlights every start time
// that fits. A sticky summary card sits beside the grid.
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import {
    bays,
    clock,
    days,
    hourLabel,
    hours,
    isOpen,
    maxDuration,
    money,
    slots,
    statusOf,
    useDraft,
} from './sample';

const ROW = 26;
const d = useDraft();
const scroller = ref<HTMLElement | null>(null);

onMounted(async () => {
    await nextTick();
    if (scroller.value) scroller.value.scrollTop = ROW * 2 * 16;
});

const top = (min: number) => (Math.max(min, 0) / 30) * ROW;
const height = (s: number, e: number) =>
    ((Math.min(e, 1440) - Math.max(s, 0)) / 30) * ROW;

const selectedDay = computed(() => days[d.dayIndex.value]);
const lengths = [60, 90, 120, 150, 180, 210, 240];

function fits(bay: number, s: number) {
    return (
        isOpen(d.blocks.value, bay, s, d.now.value) &&
        maxDuration(d.blocks.value, bay, s) >= d.duration.value
    );
}

const openCount = computed(
    () => bays.flatMap((b) => slots.filter((s) => fits(b.id, s))).length,
);

const statusStyle: Record<string, string> = {
    Complete: 'bg-zinc-100 text-zinc-400',
    'In Progress': 'bg-emerald-600 text-white',
    Booked: 'bg-zinc-800 text-zinc-100',
};
</script>

<template>
    <Head title="Prototype C · Clean modern">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700"
        />
    </Head>

    <div class="min-h-screen bg-white font-['Inter_Tight'] text-zinc-900">
        <!-- Nav -->
        <header class="border-b border-zinc-200">
            <div
                class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6"
            >
                <span class="flex items-center gap-2 font-semibold">
                    <span
                        class="grid size-6 place-items-center rounded-md bg-emerald-600 text-[11px] font-bold text-white"
                        >BP</span
                    >
                    Back Porch Golf Co.
                </span>
                <nav class="flex items-center gap-6 text-sm text-zinc-500">
                    <a
                        href="#pricing"
                        class="hidden hover:text-zinc-900 sm:inline"
                        >Pricing</a
                    >
                    <a href="#faq" class="hidden hover:text-zinc-900 sm:inline"
                        >FAQ</a
                    >
                    <span
                        class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                    >
                        <i class="size-1.5 rounded-full bg-emerald-500" /> Open
                        now
                    </span>
                </nav>
            </div>
        </header>

        <!-- Compact hero -->
        <section class="mx-auto max-w-7xl px-4 pt-10 pb-8 sm:px-6">
            <h1
                class="max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl"
            >
                Book a bay in under a minute.
                <span class="text-zinc-400">Play any hour, day or night.</span>
            </h1>
            <div
                class="mt-6 flex flex-wrap gap-x-8 gap-y-2 text-sm text-zinc-500"
            >
                <span><b class="text-zinc-900">2</b> private bays</span>
                <span><b class="text-zinc-900">24/7</b> door-code entry</span>
                <span
                    ><b class="text-zinc-900">$35</b>/hr + $8 per extra
                    player</span
                >
            </div>
        </section>

        <!-- App -->
        <section
            class="mx-auto grid max-w-7xl gap-6 px-4 pb-20 sm:px-6 lg:grid-cols-[260px_1fr_300px]"
        >
            <!-- Left: find a time -->
            <aside class="space-y-6">
                <div>
                    <p class="mb-2 text-xs font-medium text-zinc-500 uppercase">
                        Date
                    </p>
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <span
                            v-for="(w, i) in [
                                'T',
                                'W',
                                'T',
                                'F',
                                'S',
                                'S',
                                'M',
                            ]"
                            :key="i"
                            class="text-[11px] text-zinc-400"
                            >{{ w }}</span
                        >
                        <button
                            v-for="day in days"
                            :key="day.index"
                            type="button"
                            class="aspect-square rounded-lg text-sm transition"
                            :class="
                                d.dayIndex.value === day.index
                                    ? 'bg-zinc-900 font-semibold text-white'
                                    : 'hover:bg-zinc-100'
                            "
                            @click="d.dayIndex.value = day.index"
                        >
                            {{ day.day }}
                        </button>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-xs font-medium text-zinc-500 uppercase">
                        Players
                    </p>
                    <div
                        class="grid grid-cols-4 rounded-lg border border-zinc-200 p-0.5"
                    >
                        <button
                            v-for="p in 4"
                            :key="p"
                            type="button"
                            class="rounded-md py-1.5 text-sm"
                            :class="
                                d.players.value === p
                                    ? 'bg-zinc-900 text-white'
                                    : 'text-zinc-600 hover:bg-zinc-100'
                            "
                            @click="d.players.value = p"
                        >
                            {{ p }}
                        </button>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-xs font-medium text-zinc-500 uppercase">
                        Length
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="m in lengths"
                            :key="m"
                            type="button"
                            class="rounded-md border px-2.5 py-1 text-sm"
                            :class="
                                d.duration.value === m
                                    ? 'border-zinc-900 bg-zinc-900 text-white'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-400'
                            "
                            @click="
                                d.clear();
                                d.duration.value = m;
                            "
                        >
                            {{ m / 60 }}h
                        </button>
                    </div>
                </div>

                <p
                    class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800"
                >
                    <b>{{ openCount }}</b> start times fit {{ d.players.value }}
                    {{ d.players.value === 1 ? 'player' : 'players' }} for
                    {{ hours(d.duration.value) }} on {{ selectedDay.weekday }}.
                </p>
            </aside>

            <!-- Middle: grid -->
            <div class="overflow-hidden rounded-xl border border-zinc-200">
                <div
                    class="grid grid-cols-[52px_1fr_1fr] border-b border-zinc-200 bg-zinc-50 text-sm"
                >
                    <div />
                    <div
                        v-for="b in bays"
                        :key="b.id"
                        class="border-l border-zinc-200 px-3 py-2.5 font-medium"
                    >
                        {{ b.name }}
                    </div>
                </div>
                <div ref="scroller" class="max-h-[560px] overflow-y-auto">
                    <div class="relative grid grid-cols-[52px_1fr_1fr]">
                        <div>
                            <div
                                v-for="s in slots"
                                :key="s"
                                class="relative pr-2 text-right text-[11px] text-zinc-400"
                                :style="{ height: ROW + 'px' }"
                            >
                                <span
                                    v-if="s % 60 === 0"
                                    class="absolute -top-1.5 right-2"
                                    >{{ hourLabel(s) }}</span
                                >
                            </div>
                        </div>
                        <div
                            v-for="b in bays"
                            :key="b.id"
                            class="relative border-l border-zinc-200"
                        >
                            <button
                                v-for="s in slots"
                                :key="s"
                                type="button"
                                :disabled="!fits(b.id, s)"
                                class="group block w-full border-b border-zinc-100 px-2 text-left text-[11px] disabled:cursor-default"
                                :style="{ height: ROW + 'px' }"
                                @click="d.pick(b.id, s)"
                            >
                                <span
                                    v-if="fits(b.id, s)"
                                    class="inline-block rounded px-1.5 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white"
                                    >{{ clock(s) }}</span
                                >
                            </button>

                            <div
                                v-for="(blk, i) in d.blocks.value.filter(
                                    (x) => x.bay === b.id,
                                )"
                                :key="i"
                                class="pointer-events-none absolute inset-x-1 flex items-start justify-between overflow-hidden rounded-md px-2 py-1 text-[11px]"
                                :class="statusStyle[statusOf(blk, d.now.value)]"
                                :style="{
                                    top: top(blk.start) + 1 + 'px',
                                    height:
                                        height(blk.start, blk.end) - 2 + 'px',
                                }"
                            >
                                <span class="font-medium">{{
                                    statusOf(blk, d.now.value)
                                }}</span>
                                <span class="opacity-70">{{
                                    hours(blk.end - blk.start)
                                }}</span>
                            </div>

                            <div
                                v-if="
                                    d.bay.value === b.id &&
                                    d.start.value !== null
                                "
                                class="pointer-events-none absolute inset-x-1 rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-medium text-white ring-4 ring-emerald-600/20"
                                :style="{
                                    top: top(d.start.value) + 'px',
                                    height:
                                        height(
                                            d.start.value,
                                            d.start.value + d.duration.value,
                                        ) + 'px',
                                }"
                            >
                                {{ clock(d.start.value) }} –
                                {{ clock(d.start.value + d.duration.value) }}
                            </div>
                        </div>
                        <div
                            v-if="d.now.value > 0"
                            class="pointer-events-none absolute right-0 left-[52px] h-0.5 bg-red-500"
                            :style="{ top: (d.now.value / 30) * ROW + 'px' }"
                        >
                            <span
                                class="absolute -top-1 -left-1 size-2.5 rounded-full bg-red-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: summary -->
            <aside>
                <div
                    class="sticky top-6 rounded-xl border border-zinc-200 p-5 shadow-sm"
                >
                    <p class="text-xs font-medium text-zinc-500 uppercase">
                        Your booking
                    </p>
                    <template v-if="d.start.value !== null">
                        <p class="mt-2 text-lg font-semibold">
                            Bay {{ d.bay.value }} · {{ clock(d.start.value) }}
                        </p>
                        <p class="text-sm text-zinc-500">
                            {{ selectedDay.long }} ·
                            {{ hours(d.duration.value) }} ·
                            {{ d.players.value }}
                            {{ d.players.value === 1 ? 'player' : 'players' }}
                        </p>
                        <div class="mt-4 space-y-2">
                            <input
                                placeholder="Full name"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-900"
                            />
                            <input
                                placeholder="Email"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-900"
                            />
                            <input
                                placeholder="Phone"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-900"
                            />
                        </div>
                    </template>
                    <p v-else class="mt-2 text-sm text-zinc-500">
                        Pick a green start time on either bay.
                    </p>

                    <dl
                        class="mt-5 space-y-1.5 border-t border-zinc-100 pt-4 text-sm"
                    >
                        <div class="flex justify-between text-zinc-500">
                            <dt>
                                {{ money(d.quote.value.hourly) }}/hr ×
                                {{ d.duration.value / 60 }}
                            </dt>
                            <dd>{{ money(d.quote.value.subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <dt>Amusement tax</dt>
                            <dd>{{ money(d.quote.value.tax) }}</dd>
                        </div>
                        <div
                            class="flex justify-between pt-1 text-base font-semibold"
                        >
                            <dt>Total</dt>
                            <dd>{{ money(d.quote.value.total) }}</dd>
                        </div>
                    </dl>
                    <button
                        type="button"
                        :disabled="d.start.value === null"
                        class="mt-4 w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:bg-zinc-200 disabled:text-zinc-400"
                    >
                        Pay {{ money(d.quote.value.total) }}
                    </button>
                    <p class="mt-3 text-center text-xs text-zinc-400">
                        Free cancellation up to 24 hours before.
                    </p>
                </div>
            </aside>
        </section>

        <!-- Pricing + FAQ -->
        <section id="pricing" class="border-t border-zinc-200 bg-zinc-50">
            <div
                class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-3"
            >
                <div
                    v-for="f in [
                        [
                            'Pricing',
                            '$35 per bay-hour with one player, $8/hr for each extra player, up to four. 5% amusement tax.',
                        ],
                        [
                            'Getting in',
                            'Your door code arrives by text and email after payment. It works from 10 minutes before your start until you finish.',
                        ],
                        [
                            'Changing plans',
                            'Cancel 24 hours or more ahead for a full refund, straight back to your card.',
                        ],
                    ]"
                    :id="f[0] === 'Getting in' ? 'faq' : undefined"
                    :key="f[0]"
                >
                    <h3 class="font-semibold">{{ f[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">
                        {{ f[1] }}
                    </p>
                </div>
            </div>
        </section>

        <footer class="py-8 text-center text-xs text-zinc-400">
            Back Porch Golf Co. · A made-up studio for a demo
        </footer>
    </div>
</template>
