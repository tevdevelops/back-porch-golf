<script setup lang="ts">
// PROTOTYPE — Variant A "Warm porch": classic scrolling landing page,
// vertical day grid, slide-in drawer for the booking.
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

const ROW = 28;
const d = useDraft();
const scroller = ref<HTMLElement | null>(null);

onMounted(async () => {
    await nextTick();
    if (scroller.value) scroller.value.scrollTop = ROW * 2 * 14;
});

const top = (min: number) => (Math.max(min, 0) / 30) * ROW;
const height = (s: number, e: number) =>
    ((Math.min(e, 1440) - Math.max(s, 0)) / 30) * ROW;

const selectedDay = computed(() => days[d.dayIndex.value]);

const statusStyle: Record<string, string> = {
    Complete: 'bg-[#E9E1D0] text-[#8A7F6A] border-[#D8CCB4]',
    'In Progress': 'bg-[#E7B65A] text-[#3E2A10] border-[#C9952F]',
    Booked: 'bg-[#2F4A3A] text-[#F6F0E4] border-[#203528]',
};
</script>

<template>
    <Head title="Prototype A · Warm porch">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600"
        />
    </Head>

    <div
        class="min-h-screen bg-[#F6F0E4] font-['Inter'] text-[#2B2620] selection:bg-[#E7B65A]/50"
    >
        <!-- Nav -->
        <header
            class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6"
        >
            <a href="#" class="flex items-center gap-2">
                <span
                    class="grid size-9 place-items-center rounded-full bg-[#2F4A3A] text-lg text-[#E7B65A]"
                    >☀</span
                >
                <span class="font-['Fraunces'] text-xl font-semibold">
                    Back Porch Golf Co.
                </span>
            </a>
            <nav class="hidden items-center gap-8 text-sm md:flex">
                <a href="#how" class="hover:text-[#2F4A3A]">How it works</a>
                <a href="#pricing" class="hover:text-[#2F4A3A]">Pricing</a>
                <a href="#book" class="hover:text-[#2F4A3A]">Book</a>
            </nav>
            <a
                href="#book"
                class="rounded-full bg-[#2F4A3A] px-5 py-2.5 text-sm font-medium text-[#F6F0E4] hover:bg-[#203528]"
                >Book a bay</a
            >
        </header>

        <!-- Hero -->
        <section
            class="mx-auto grid max-w-6xl items-center gap-12 px-4 pt-10 pb-20 sm:px-6 md:grid-cols-[1.2fr_1fr] md:pt-16"
        >
            <div>
                <p
                    class="mb-4 text-sm font-medium tracking-wide text-[#8A5A2B] uppercase"
                >
                    Springfield, PA · Open 24/7
                </p>
                <h1
                    class="font-['Fraunces'] text-5xl leading-[1.05] font-semibold text-[#1F3328] sm:text-6xl"
                >
                    Pull up a chair.<br />Tee off any hour.
                </h1>
                <p class="mt-6 max-w-md text-lg leading-relaxed text-[#5A5246]">
                    Two private simulator bays, no front desk and no rush. Book
                    online, get a door code, and play the world's courses on
                    your schedule.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="#book"
                        class="rounded-full bg-[#2F4A3A] px-6 py-3 font-medium text-[#F6F0E4] hover:bg-[#203528]"
                        >Book a bay</a
                    >
                    <a
                        href="#how"
                        class="rounded-full border border-[#2F4A3A]/30 px-6 py-3 font-medium text-[#2F4A3A] hover:bg-[#2F4A3A]/5"
                        >How it works</a
                    >
                </div>
            </div>

            <!-- Wooden sign -->
            <div class="relative mx-auto w-full max-w-sm">
                <div
                    class="absolute -top-10 left-1/2 h-10 w-px -translate-x-1/2 bg-[#6B4A2B]"
                />
                <div
                    class="rotate-[-2deg] rounded-xl border-4 border-[#5B3D22] p-8 text-center text-[#FBF4E4] shadow-[0_20px_40px_-15px_rgba(60,40,20,0.5)]"
                    style="
                        background: repeating-linear-gradient(
                            0deg,
                            #8a5a2b 0 22px,
                            #7a4f25 22px 24px
                        );
                    "
                >
                    <p class="font-['Fraunces'] text-3xl font-semibold">
                        Open all night
                    </p>
                    <div
                        class="my-5 h-px bg-[#FBF4E4]/40"
                        role="presentation"
                    />
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <dt class="opacity-80">Bays</dt>
                            <dd class="font-['Fraunces'] text-2xl">2</dd>
                        </div>
                        <div>
                            <dt class="opacity-80">Per hour</dt>
                            <dd class="font-['Fraunces'] text-2xl">$35</dd>
                        </div>
                        <div>
                            <dt class="opacity-80">Staff</dt>
                            <dd class="font-['Fraunces'] text-2xl">0</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how" class="border-y border-[#E3D8C2] bg-[#FBF7EE]">
            <div
                class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-3"
            >
                <div
                    v-for="(s, i) in [
                        [
                            'Pick your bay',
                            'See both bays side by side and grab any open half hour, day or night.',
                        ],
                        [
                            'Get your door code',
                            'Pay online and we text you a code that opens the door 10 minutes before you start.',
                        ],
                        [
                            'Play like a regular',
                            'Clubs, launch monitor and 100+ courses are waiting. Bring friends, up to four per bay.',
                        ],
                    ]"
                    :key="i"
                >
                    <p class="font-['Fraunces'] text-4xl text-[#C9952F]">
                        0{{ i + 1 }}
                    </p>
                    <h3 class="mt-2 font-['Fraunces'] text-xl font-semibold">
                        {{ s[0] }}
                    </h3>
                    <p class="mt-2 leading-relaxed text-[#5A5246]">
                        {{ s[1] }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Booking -->
        <section id="book" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2
                        class="font-['Fraunces'] text-4xl font-semibold text-[#1F3328]"
                    >
                        Book a bay
                    </h2>
                    <p class="mt-2 text-[#5A5246]">
                        Tap an open half hour to start. {{ selectedDay.long }}.
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs text-[#5A5246]">
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-[#2F4A3A]" />
                        Booked</span
                    >
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-[#E7B65A]" /> In
                        progress</span
                    >
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-[#E9E1D0]" />
                        Complete</span
                    >
                </div>
            </div>

            <!-- Day pills -->
            <div class="-mx-4 mt-6 flex gap-2 overflow-x-auto px-4 pb-2">
                <button
                    v-for="day in days"
                    :key="day.index"
                    type="button"
                    class="flex min-w-16 flex-col items-center rounded-2xl border px-3 py-2 transition"
                    :class="
                        d.dayIndex.value === day.index
                            ? 'border-[#2F4A3A] bg-[#2F4A3A] text-[#F6F0E4]'
                            : 'border-[#E3D8C2] bg-[#FBF7EE] hover:border-[#2F4A3A]/40'
                    "
                    @click="d.dayIndex.value = day.index"
                >
                    <span class="text-xs opacity-80">{{
                        day.index === 0 ? 'Today' : day.weekday
                    }}</span>
                    <span class="font-['Fraunces'] text-xl">{{ day.day }}</span>
                </button>
            </div>

            <!-- Grid -->
            <div
                class="mt-4 overflow-hidden rounded-2xl border border-[#E3D8C2] bg-[#FBF7EE] shadow-sm"
            >
                <div
                    class="grid grid-cols-[56px_1fr_1fr] border-b border-[#E3D8C2] bg-[#F1E8D6] text-center"
                >
                    <div />
                    <div
                        v-for="b in bays"
                        :key="b.id"
                        class="py-3 font-['Fraunces'] font-semibold"
                    >
                        {{ b.name }}
                    </div>
                </div>
                <div ref="scroller" class="max-h-[520px] overflow-y-auto">
                    <div class="relative grid grid-cols-[56px_1fr_1fr]">
                        <!-- time labels -->
                        <div>
                            <div
                                v-for="s in slots"
                                :key="s"
                                class="relative pr-2 text-right text-[11px] text-[#8A7F6A]"
                                :style="{ height: ROW + 'px' }"
                            >
                                <span
                                    v-if="s % 60 === 0"
                                    class="absolute -top-1.5 right-2"
                                >
                                    {{ hourLabel(s) }}
                                </span>
                            </div>
                        </div>
                        <!-- bays -->
                        <div
                            v-for="b in bays"
                            :key="b.id"
                            class="relative border-l border-[#E3D8C2]"
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
                                class="block w-full border-b text-[11px] text-transparent transition disabled:cursor-default"
                                :class="[
                                    s % 60 === 30
                                        ? 'border-[#E3D8C2]'
                                        : 'border-dashed border-[#EFE6D3]',
                                    s < d.now.value
                                        ? 'bg-[#F1EADB]'
                                        : 'hover:bg-[#2F4A3A]/10 hover:text-[#2F4A3A]',
                                ]"
                                :style="{ height: ROW + 'px' }"
                                @click="d.pick(b.id, s)"
                            >
                                {{ clock(s) }}
                            </button>

                            <div
                                v-for="(blk, i) in d.blocks.value.filter(
                                    (x) => x.bay === b.id,
                                )"
                                :key="i"
                                class="pointer-events-none absolute inset-x-1.5 overflow-hidden rounded-lg border px-2 py-1 text-[11px] leading-tight"
                                :class="statusStyle[statusOf(blk, d.now.value)]"
                                :style="{
                                    top: top(blk.start) + 1 + 'px',
                                    height:
                                        height(blk.start, blk.end) - 2 + 'px',
                                }"
                            >
                                <span class="font-semibold">{{
                                    statusOf(blk, d.now.value)
                                }}</span>
                                <span class="ml-1 opacity-80"
                                    >{{ clock(blk.start) }} ·
                                    {{ hours(blk.end - blk.start) }}</span
                                >
                                <span
                                    v-if="blk.start < 0"
                                    class="block opacity-70"
                                    >from last night</span
                                >
                                <span
                                    v-if="blk.end > 1440"
                                    class="block opacity-70"
                                    >continues past midnight</span
                                >
                            </div>

                            <div
                                v-if="
                                    d.bay.value === b.id &&
                                    d.start.value !== null
                                "
                                class="pointer-events-none absolute inset-x-1.5 rounded-lg border-2 border-dashed border-[#C9952F] bg-[#E7B65A]/30 px-2 py-1 text-[11px] font-semibold text-[#5B3D22]"
                                :style="{
                                    top: top(d.start.value) + 'px',
                                    height:
                                        height(
                                            d.start.value,
                                            d.start.value + d.duration.value,
                                        ) + 'px',
                                }"
                            >
                                Your booking
                            </div>
                        </div>

                        <!-- now line -->
                        <div
                            v-if="d.now.value > 0"
                            class="pointer-events-none absolute right-0 left-12 flex items-center"
                            :style="{ top: (d.now.value / 30) * ROW + 'px' }"
                        >
                            <span
                                class="rounded-full bg-[#B5502B] px-1.5 text-[10px] font-semibold text-white"
                                >{{ clock(d.now.value) }}</span
                            >
                            <span class="h-px flex-1 bg-[#B5502B]" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="pricing" class="bg-[#1F3328] text-[#F6F0E4]">
            <div
                class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-[1fr_1.4fr]"
            >
                <h2 class="font-['Fraunces'] text-4xl font-semibold">
                    Simple porch prices
                </h2>
                <dl class="divide-y divide-[#F6F0E4]/15">
                    <div class="flex justify-between py-3">
                        <dt>Bay, first player</dt>
                        <dd class="font-['Fraunces'] text-xl">$35 / hr</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt>Each extra player (up to 4)</dt>
                        <dd class="font-['Fraunces'] text-xl">$8 / hr</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt>Cancel 24+ hours ahead</dt>
                        <dd class="font-['Fraunces'] text-xl">Full refund</dd>
                    </div>
                </dl>
            </div>
        </section>

        <footer class="px-4 py-10 text-center text-sm text-[#8A7F6A]">
            Back Porch Golf Co. · A made-up studio for a demo
        </footer>

        <!-- Drawer -->
        <transition
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="d.start.value !== null"
                class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col border-l border-[#E3D8C2] bg-[#FBF7EE] shadow-2xl transition-transform duration-300"
            >
                <div
                    class="flex items-start justify-between border-b border-[#E3D8C2] p-6"
                >
                    <div>
                        <p class="text-sm text-[#8A5A2B]">
                            Bay {{ d.bay.value }} · {{ selectedDay.long }}
                        </p>
                        <h3
                            class="mt-1 font-['Fraunces'] text-2xl font-semibold"
                        >
                            {{ clock(d.start.value) }} –
                            {{ clock(d.start.value + d.duration.value) }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-2 text-xl leading-none hover:bg-black/5"
                        aria-label="Close"
                        @click="d.clear()"
                    >
                        ×
                    </button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto p-6">
                    <div>
                        <p class="mb-2 text-sm font-medium">How long?</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="m in d.durations.value"
                                :key="m"
                                type="button"
                                class="rounded-full border px-3 py-1.5 text-sm"
                                :class="
                                    d.duration.value === m
                                        ? 'border-[#2F4A3A] bg-[#2F4A3A] text-white'
                                        : 'border-[#E3D8C2] hover:border-[#2F4A3A]/40'
                                "
                                @click="d.duration.value = m"
                            >
                                {{ m / 60 }}h
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium">Players</p>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="p in 4"
                                :key="p"
                                type="button"
                                class="rounded-xl border py-2 font-['Fraunces'] text-lg"
                                :class="
                                    d.players.value === p
                                        ? 'border-[#2F4A3A] bg-[#2F4A3A] text-white'
                                        : 'border-[#E3D8C2] hover:border-[#2F4A3A]/40'
                                "
                                @click="d.players.value = p"
                            >
                                {{ p }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <input
                            placeholder="Full name"
                            class="w-full rounded-xl border border-[#E3D8C2] bg-white px-4 py-3 outline-none focus:border-[#2F4A3A]"
                        />
                        <input
                            placeholder="Email"
                            class="w-full rounded-xl border border-[#E3D8C2] bg-white px-4 py-3 outline-none focus:border-[#2F4A3A]"
                        />
                        <input
                            placeholder="Phone"
                            class="w-full rounded-xl border border-[#E3D8C2] bg-white px-4 py-3 outline-none focus:border-[#2F4A3A]"
                        />
                    </div>
                </div>

                <div class="border-t border-[#E3D8C2] bg-[#F1E8D6] p-6">
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt>
                                {{ hours(d.duration.value) }} ×
                                {{ money(d.quote.value.hourly) }}/hr
                            </dt>
                            <dd>{{ money(d.quote.value.subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between text-[#8A7F6A]">
                            <dt>Amusement tax (5%)</dt>
                            <dd>{{ money(d.quote.value.tax) }}</dd>
                        </div>
                        <div
                            class="flex justify-between pt-2 font-['Fraunces'] text-xl font-semibold"
                        >
                            <dt>Total</dt>
                            <dd>{{ money(d.quote.value.total) }}</dd>
                        </div>
                    </dl>
                    <button
                        type="button"
                        class="mt-4 w-full rounded-full bg-[#2F4A3A] py-3 font-medium text-[#F6F0E4] hover:bg-[#203528]"
                    >
                        Continue to payment
                    </button>
                </div>
            </aside>
        </transition>
    </div>
</template>
