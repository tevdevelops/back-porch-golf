<script setup lang="ts">
// PROTOTYPE — Variant B "Retro clubhouse": 1970s poster landing,
// horizontal "tee sheet" timeline (bays are rows, time runs left to right),
// booking summary as a ticket stub under the sheet.
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import {
    bays,
    clock,
    days,
    hourLabel,
    hours,
    isOpen,
    money,
    slots,
    statusOf,
    useDraft,
} from './sample';

const COL = 44;
const d = useDraft();
const sheet = ref<HTMLElement | null>(null);

onMounted(async () => {
    await nextTick();
    if (sheet.value) sheet.value.scrollLeft = COL * 2 * 15;
});

const left = (min: number) => (Math.max(min, 0) / 30) * COL;
const width = (s: number, e: number) =>
    ((Math.min(e, 1440) - Math.max(s, 0)) / 30) * COL;

const selectedDay = computed(() => days[d.dayIndex.value]);

const statusStyle: Record<string, string> = {
    Complete: 'bg-[#D9C9A3] text-[#7A6A4A]',
    'In Progress': 'bg-[#B5502B] text-[#F3E6C8]',
    Booked: 'bg-[#5D6B2F] text-[#F3E6C8]',
};
</script>

<template>
    <Head title="Prototype B · Retro clubhouse">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css?family=shrikhand:400|dm-sans:400,500,700"
        />
    </Head>

    <div class="min-h-screen bg-[#F3E6C8] font-['DM_Sans'] text-[#3B2A1A]">
        <!-- Poster hero -->
        <section
            class="relative overflow-hidden border-b-8 border-[#3B2A1A] px-4 pt-6 pb-24 sm:px-6"
        >
            <div
                class="absolute inset-0 opacity-90"
                style="
                    background: repeating-conic-gradient(
                        from 0deg at 50% 110%,
                        #e3a72f 0deg 6deg,
                        #f3e6c8 6deg 12deg
                    );
                "
            />
            <div
                class="absolute inset-x-0 bottom-0 h-28 bg-[#5D6B2F]"
                style="border-radius: 50% 50% 0 0 / 100% 100% 0 0"
            />

            <header
                class="relative mx-auto flex max-w-6xl items-center justify-between"
            >
                <span
                    class="rounded-full border-2 border-[#3B2A1A] bg-[#F3E6C8] px-4 py-1.5 text-sm font-bold tracking-widest uppercase"
                    >Est. 2026 · Springfield PA</span
                >
                <a
                    href="#sheet"
                    class="rounded-full border-2 border-[#3B2A1A] bg-[#B5502B] px-5 py-2 font-bold text-[#F3E6C8] shadow-[3px_3px_0_#3B2A1A] hover:translate-y-px hover:shadow-[2px_2px_0_#3B2A1A]"
                    >Book a bay</a
                >
            </header>

            <div class="relative mx-auto mt-14 max-w-4xl text-center">
                <h1
                    class="font-['Shrikhand'] text-6xl leading-[0.95] text-[#B5502B] [text-shadow:4px_4px_0_#3B2A1A] sm:text-8xl"
                >
                    Back Porch<br />Golf Co.
                </h1>
                <p
                    class="mx-auto mt-6 inline-block -rotate-2 rounded-md bg-[#3B2A1A] px-5 py-2 text-lg font-bold tracking-wide text-[#E3A72F] uppercase"
                >
                    Swing all night · Two bays · No staff
                </p>
            </div>

            <div
                class="relative mx-auto mt-12 flex max-w-xl flex-wrap justify-center gap-5"
            >
                <div
                    v-for="badge in [
                        ['24/7', 'always open'],
                        ['$35', 'per hour'],
                        ['4', 'players a bay'],
                    ]"
                    :key="badge[0]"
                    class="grid size-28 place-items-center rounded-full border-4 border-[#3B2A1A] bg-[#F3E6C8] text-center shadow-[4px_4px_0_#3B2A1A]"
                >
                    <div>
                        <p class="font-['Shrikhand'] text-3xl text-[#5D6B2F]">
                            {{ badge[0] }}
                        </p>
                        <p class="text-[11px] font-bold uppercase">
                            {{ badge[1] }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tee sheet -->
        <section id="sheet" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="font-['Shrikhand'] text-4xl text-[#5D6B2F] sm:text-5xl">
                Today's bay sheet
            </h2>
            <p class="mt-2 font-medium">
                Pick a day, then tap any open square on a bay.
            </p>

            <!-- folder tabs -->
            <div class="mt-8 flex gap-1 overflow-x-auto">
                <button
                    v-for="day in days.slice(0, 7)"
                    :key="day.index"
                    type="button"
                    class="shrink-0 rounded-t-xl border-2 border-b-0 border-[#3B2A1A] px-4 pt-2 pb-1.5 text-sm font-bold"
                    :class="
                        d.dayIndex.value === day.index
                            ? 'bg-[#E3A72F]'
                            : 'bg-[#E9D9B4] hover:bg-[#EFD38F]'
                    "
                    @click="d.dayIndex.value = day.index"
                >
                    {{ day.index === 0 ? 'Today' : day.weekday }}
                    <span class="font-['Shrikhand'] font-normal">{{
                        day.day
                    }}</span>
                </button>
                <button
                    type="button"
                    class="shrink-0 rounded-t-xl border-2 border-b-0 border-dashed border-[#3B2A1A]/50 px-4 pt-2 pb-1.5 text-sm font-bold opacity-70"
                >
                    More dates ▾
                </button>
            </div>

            <div
                class="rounded-tr-xl rounded-b-xl border-2 border-[#3B2A1A] bg-[#FBF3DF] shadow-[6px_6px_0_#3B2A1A]"
            >
                <div class="flex">
                    <!-- bay labels -->
                    <div class="w-24 shrink-0 border-r-2 border-[#3B2A1A]">
                        <div class="h-9 border-b-2 border-[#3B2A1A]" />
                        <div
                            v-for="b in bays"
                            :key="b.id"
                            class="flex h-20 items-center justify-center border-b-2 border-[#3B2A1A] font-['Shrikhand'] text-xl text-[#B5502B] last:border-b-0"
                        >
                            {{ b.name }}
                        </div>
                    </div>

                    <div ref="sheet" class="overflow-x-auto">
                        <div
                            class="relative"
                            :style="{ width: COL * slots.length + 'px' }"
                        >
                            <!-- hour header -->
                            <div class="flex h-9 border-b-2 border-[#3B2A1A]">
                                <div
                                    v-for="s in slots"
                                    :key="s"
                                    class="shrink-0 pt-2 pl-1 text-xs font-bold"
                                    :class="
                                        s % 60 === 0
                                            ? 'border-l border-[#3B2A1A]/40'
                                            : ''
                                    "
                                    :style="{ width: COL + 'px' }"
                                >
                                    <span v-if="s % 60 === 0">{{
                                        hourLabel(s)
                                    }}</span>
                                </div>
                            </div>

                            <div
                                v-for="b in bays"
                                :key="b.id"
                                class="relative flex h-20 border-b-2 border-[#3B2A1A] last:border-b-0"
                            >
                                <button
                                    v-for="s in slots"
                                    :key="s"
                                    type="button"
                                    :disabled="
                                        !isOpen(
                                            d.blocks.value,
                                            b.id,
                                            s,
                                            d.now.value,
                                        )
                                    "
                                    class="shrink-0 border-l border-dotted border-[#3B2A1A]/25 disabled:cursor-default"
                                    :class="
                                        s < d.now.value
                                            ? 'bg-[repeating-linear-gradient(45deg,transparent_0_4px,rgba(59,42,26,0.08)_4px_6px)]'
                                            : 'hover:bg-[#E3A72F]/40'
                                    "
                                    :style="{ width: COL + 'px' }"
                                    :title="clock(s)"
                                    @click="d.pick(b.id, s)"
                                />

                                <div
                                    v-for="(blk, i) in d.blocks.value.filter(
                                        (x) => x.bay === b.id,
                                    )"
                                    :key="i"
                                    class="pointer-events-none absolute inset-y-2.5 overflow-hidden rounded-full border-2 border-[#3B2A1A] px-3 py-1 text-xs leading-tight"
                                    :class="
                                        statusStyle[statusOf(blk, d.now.value)]
                                    "
                                    :style="{
                                        left: left(blk.start) + 2 + 'px',
                                        width:
                                            width(blk.start, blk.end) -
                                            4 +
                                            'px',
                                    }"
                                >
                                    <p class="truncate font-bold uppercase">
                                        {{ statusOf(blk, d.now.value) }}
                                    </p>
                                    <p class="truncate">
                                        {{ clock(blk.start) }} ·
                                        {{ hours(blk.end - blk.start) }}
                                    </p>
                                </div>

                                <div
                                    v-if="
                                        d.bay.value === b.id &&
                                        d.start.value !== null
                                    "
                                    class="pointer-events-none absolute inset-y-1.5 rounded-full border-4 border-dashed border-[#B5502B] bg-[#E3A72F]/50"
                                    :style="{
                                        left: left(d.start.value) + 'px',
                                        width:
                                            width(
                                                d.start.value,
                                                d.start.value +
                                                    d.duration.value,
                                            ) + 'px',
                                    }"
                                />
                            </div>

                            <div
                                v-if="d.now.value > 0"
                                class="pointer-events-none absolute top-0 bottom-0 w-1 bg-[#B5502B]"
                                :style="{
                                    left: (d.now.value / 30) * COL + 'px',
                                }"
                            >
                                <span
                                    class="absolute -top-1 left-1 rounded bg-[#B5502B] px-1 text-[10px] font-bold whitespace-nowrap text-[#F3E6C8]"
                                    >NOW</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="mt-3 text-sm font-medium opacity-70">
                ← Scroll the sheet for earlier and later times →
            </p>

            <!-- Ticket stub -->
            <div
                v-if="d.start.value !== null"
                class="mt-10 flex flex-col overflow-hidden rounded-2xl border-2 border-[#3B2A1A] bg-[#FBF3DF] shadow-[6px_6px_0_#3B2A1A] md:flex-row"
            >
                <div class="flex-1 p-6 sm:p-8">
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-[#B5502B] uppercase"
                    >
                        Admit {{ d.players.value }} · Bay {{ d.bay.value }}
                    </p>
                    <h3 class="mt-1 font-['Shrikhand'] text-3xl text-[#5D6B2F]">
                        {{ selectedDay.weekday }} {{ selectedDay.month }}
                        {{ selectedDay.day }}, {{ clock(d.start.value) }}
                    </h3>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold uppercase"
                                >Length</span
                            >
                            <select
                                v-model="d.duration.value"
                                class="mt-1 w-full rounded-lg border-2 border-[#3B2A1A] bg-white px-3 py-2 font-bold"
                            >
                                <option
                                    v-for="m in d.durations.value"
                                    :key="m"
                                    :value="m"
                                >
                                    {{ hours(m) }} (until
                                    {{ clock(d.start.value + m) }})
                                </option>
                            </select>
                        </label>
                        <div>
                            <span class="text-sm font-bold uppercase"
                                >Players</span
                            >
                            <div
                                class="mt-1 flex items-center justify-between rounded-lg border-2 border-[#3B2A1A] bg-white"
                            >
                                <button
                                    type="button"
                                    class="px-4 py-2 text-xl font-bold"
                                    @click="
                                        d.players.value = Math.max(
                                            1,
                                            d.players.value - 1,
                                        )
                                    "
                                >
                                    −
                                </button>
                                <span class="font-['Shrikhand'] text-2xl">{{
                                    d.players.value
                                }}</span>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-xl font-bold"
                                    @click="
                                        d.players.value = Math.min(
                                            4,
                                            d.players.value + 1,
                                        )
                                    "
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        <input
                            placeholder="Name"
                            class="rounded-lg border-2 border-[#3B2A1A] bg-white px-3 py-2"
                        />
                        <input
                            placeholder="Phone"
                            class="rounded-lg border-2 border-[#3B2A1A] bg-white px-3 py-2"
                        />
                        <input
                            placeholder="Email"
                            class="rounded-lg border-2 border-[#3B2A1A] bg-white px-3 py-2 sm:col-span-2"
                        />
                    </div>
                </div>

                <!-- stub -->
                <div
                    class="relative flex w-full flex-col justify-between border-t-2 border-dashed border-[#3B2A1A] bg-[#E3A72F] p-6 sm:p-8 md:w-72 md:border-t-0 md:border-l-2"
                >
                    <span
                        class="absolute -top-3 -left-3 hidden size-6 rounded-full border-2 border-[#3B2A1A] bg-[#F3E6C8] md:block"
                    />
                    <span
                        class="absolute -bottom-3 -left-3 hidden size-6 rounded-full border-2 border-[#3B2A1A] bg-[#F3E6C8] md:block"
                    />
                    <dl class="space-y-1 text-sm font-medium">
                        <div class="flex justify-between">
                            <dt>Bay time</dt>
                            <dd>{{ money(d.quote.value.subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Tax 5%</dt>
                            <dd>{{ money(d.quote.value.tax) }}</dd>
                        </div>
                    </dl>
                    <div class="my-4">
                        <p class="text-xs font-bold uppercase">Total</p>
                        <p class="font-['Shrikhand'] text-5xl">
                            {{ money(d.quote.value.total) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border-2 border-[#3B2A1A] bg-[#B5502B] py-3 font-bold text-[#F3E6C8] shadow-[3px_3px_0_#3B2A1A] hover:translate-y-px"
                    >
                        Punch my ticket →
                    </button>
                </div>
            </div>
            <div
                v-else
                class="mt-10 rounded-2xl border-2 border-dashed border-[#3B2A1A]/40 p-8 text-center font-bold opacity-70"
            >
                Your ticket shows up here once you pick a time.
            </div>
        </section>

        <!-- Rules strip -->
        <section
            class="border-y-8 border-[#3B2A1A] bg-[#5D6B2F] text-[#F3E6C8]"
        >
            <div
                class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3"
            >
                <div
                    v-for="rule in [
                        [
                            '$35 / hr',
                            'Includes one player. Add friends at $8/hr each.',
                        ],
                        [
                            'Door code',
                            'Texted after you pay. Works 10 min before your start.',
                        ],
                        ['24h rule', 'Cancel a day ahead for a full refund.'],
                    ]"
                    :key="rule[0]"
                >
                    <p class="font-['Shrikhand'] text-3xl text-[#E3A72F]">
                        {{ rule[0] }}
                    </p>
                    <p class="mt-2 font-medium">{{ rule[1] }}</p>
                </div>
            </div>
        </section>

        <footer class="py-10 text-center text-sm font-bold uppercase">
            Back Porch Golf Co. · A made-up studio for a demo
        </footer>
    </div>
</template>
