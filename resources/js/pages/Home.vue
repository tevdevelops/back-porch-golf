<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { money } from '@/lib/studio';

const studio = usePage().props.studio;

const steps = [
    [
        'Pick your bay',
        'See both bays side by side and grab any open half hour, day or night.',
    ],
    [
        'Get your door code',
        `Pay online and we send a code that opens the door ${studio.door_code_lead_minutes} minutes before you start.`,
    ],
    [
        'Play like a regular',
        `Launch monitor and 100+ courses are waiting. Bring friends, up to ${studio.max_players} per bay.`,
    ],
];

const faqs = [
    [
        'Is anyone there?',
        'No staff, ever. Cameras and an alarm look after the place, and your door code only works during your booking.',
    ],
    [
        'Can I play at 3 a.m.?',
        'Yes. We are open 24 hours a day, 365 days a year, and bookings can run past midnight.',
    ],
    [
        'Who counts as a player?',
        'Everyone who swings, including you. Declare them all when you book.',
    ],
    [
        'What if plans change?',
        `Cancel ${studio.cancel_cutoff_hours} hours or more before you start for a full refund, using the link in your confirmation email.`,
    ],
];
</script>

<template>
    <Head title="Golf simulator bays, open 24/7" />

    <SiteLayout>
        <!-- Hero -->
        <section
            class="mx-auto grid max-w-6xl items-center gap-12 px-4 pt-10 pb-20 sm:px-6 md:grid-cols-[1.2fr_1fr] md:pt-16"
        >
            <div>
                <p
                    class="mb-4 text-sm font-medium tracking-wide text-wood uppercase"
                >
                    Springfield, PA · Open 24/7
                </p>
                <h1
                    class="font-serif text-5xl leading-[1.05] font-semibold text-forest-deep sm:text-6xl"
                >
                    Pull up a chair.<br />Tee off any hour.
                </h1>
                <p class="mt-6 max-w-md text-lg leading-relaxed text-ink-soft">
                    Two private simulator bays, no front desk and no rush. Book
                    online, get a door code, and play the world's courses on
                    your schedule.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/book"
                        class="rounded-full bg-forest px-6 py-3 font-medium text-cream hover:bg-forest-dark"
                        >Book a bay</Link
                    >
                    <a
                        href="#how"
                        class="rounded-full border border-forest/30 px-6 py-3 font-medium text-forest hover:bg-forest/5"
                        >How it works</a
                    >
                </div>
            </div>

            <!-- Hanging wooden sign -->
            <div class="relative mx-auto w-full max-w-sm" aria-hidden="true">
                <div
                    class="absolute -top-10 left-1/2 h-10 w-px -translate-x-1/2 bg-wood-dark"
                />
                <div
                    class="rotate-[-2deg] rounded-xl border-4 border-wood-dark bg-[repeating-linear-gradient(0deg,#8a5a2b_0_22px,#7a4f25_22px_24px)] p-8 text-center text-[#fbf4e4] shadow-[0_20px_40px_-15px_rgba(60,40,20,0.5)]"
                >
                    <p class="font-serif text-3xl font-semibold">
                        Open all night
                    </p>
                    <div class="my-5 h-px bg-[#fbf4e4]/40" />
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <dt class="opacity-80">Bays</dt>
                            <dd class="font-serif text-2xl">2</dd>
                        </div>
                        <div>
                            <dt class="opacity-80">Per hour</dt>
                            <dd class="font-serif text-2xl">
                                ${{ studio.rate_cents / 100 }}
                            </dd>
                        </div>
                        <div>
                            <dt class="opacity-80">Staff</dt>
                            <dd class="font-serif text-2xl">0</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how" class="border-y border-line bg-linen">
            <div
                class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-3"
            >
                <div v-for="(step, i) in steps" :key="i">
                    <p class="font-serif text-4xl text-amber-dark">
                        0{{ i + 1 }}
                    </p>
                    <h2 class="mt-2 font-serif text-xl font-semibold">
                        {{ step[0] }}
                    </h2>
                    <p class="mt-2 leading-relaxed text-ink-soft">
                        {{ step[1] }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="pricing" class="bg-forest-deep text-cream">
            <div
                class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-[1fr_1.4fr]"
            >
                <div>
                    <h2 class="font-serif text-4xl font-semibold">
                        Simple porch prices
                    </h2>
                    <p class="mt-3 text-cream/70">
                        Pay for the bay, not the seat. Prices before
                        {{ studio.tax_rate * 100 }}% amusement tax.
                    </p>
                </div>
                <dl class="divide-y divide-cream/15">
                    <div class="flex justify-between gap-4 py-3">
                        <dt>Bay, first player</dt>
                        <dd class="font-serif text-xl whitespace-nowrap">
                            {{ money(studio.rate_cents) }} / hr
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt>
                            Each extra player (up to {{ studio.max_players }})
                        </dt>
                        <dd class="font-serif text-xl whitespace-nowrap">
                            {{ money(studio.extra_player_cents) }} / hr
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt>Booking length</dt>
                        <dd class="font-serif text-xl whitespace-nowrap">
                            1–4 hours
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt>
                            Cancel {{ studio.cancel_cutoff_hours }}+ hours ahead
                        </dt>
                        <dd class="font-serif text-xl whitespace-nowrap">
                            Full refund
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="font-serif text-4xl font-semibold text-forest-deep">
                Questions from the porch
            </h2>
            <dl class="mt-8 grid gap-x-12 gap-y-8 md:grid-cols-2">
                <div v-for="faq in faqs" :key="faq[0]">
                    <dt class="font-serif text-lg font-semibold">
                        {{ faq[0] }}
                    </dt>
                    <dd class="mt-2 leading-relaxed text-ink-soft">
                        {{ faq[1] }}
                    </dd>
                </div>
            </dl>
            <div
                class="mt-14 flex flex-col items-start justify-between gap-6 rounded-3xl bg-sand p-8 sm:flex-row sm:items-center"
            >
                <p class="font-serif text-2xl font-semibold">
                    The porch light is always on.
                </p>
                <Link
                    href="/book"
                    class="rounded-full bg-forest px-6 py-3 font-medium text-cream hover:bg-forest-dark"
                    >See open times</Link
                >
            </div>
        </section>
    </SiteLayout>
</template>
