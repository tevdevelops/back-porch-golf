<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BookingDrawer from '@/components/BookingDrawer.vue';
import BookingGrid from '@/components/BookingGrid.vue';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { addMinutes } from '@/lib/studio';
import type { Bay, Day, Schedule } from '@/types';

const props = defineProps<{
    date: string;
    days: Day[];
    bays: Bay[];
    schedule: Schedule;
    now: string;
}>();

const studio = usePage().props.studio;

const selection = ref<{ bayId: number; offset: number } | null>(null);
const minutes = ref(60);

const selectedDay = computed(
    () => props.days.find((d) => d.date === props.date) ?? props.days[0],
);
const selectedBay = computed(() =>
    props.bays.find((b) => b.id === selection.value?.bayId),
);
const startsAt = computed(() =>
    selection.value
        ? addMinutes(props.schedule.starts_at, selection.value.offset)
        : null,
);

/** Minutes free on the bay from the selected slot to the next booking. */
const free = computed(() => {
    if (!selection.value) return 0;
    const { bayId, offset } = selection.value;
    const next = props.schedule.blocks
        .filter((b) => b.bay_id === bayId && b.offset + b.minutes > offset)
        .reduce((min, b) => Math.min(min, b.offset), Infinity);
    return next - offset;
});

function pick(bayId: number, offset: number) {
    selection.value = { bayId, offset };
}

function showDay(date: string) {
    selection.value = null;
    router.get(
        '/book',
        { date },
        {
            only: ['date', 'schedule', 'now'],
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <Head title="Book a bay" />

    <SiteLayout>
        <section class="mx-auto max-w-6xl px-4 pt-6 pb-20 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1
                        class="font-serif text-4xl font-semibold text-forest-deep"
                    >
                        Book a bay
                    </h1>
                    <p class="mt-2 text-ink-soft">
                        Tap an open half hour to start · {{ selectedDay.long }}
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs text-ink-soft">
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-forest" /> Booked</span
                    >
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-amber" /> In
                        progress</span
                    >
                    <span class="flex items-center gap-1.5"
                        ><i class="size-3 rounded-sm bg-sand-dark" />
                        Complete</span
                    >
                </div>
            </div>

            <nav
                class="-mx-4 mt-6 flex gap-2 overflow-x-auto px-4 pb-2"
                aria-label="Choose a day"
            >
                <button
                    v-for="day in days"
                    :key="day.date"
                    type="button"
                    class="flex min-w-16 flex-col items-center rounded-2xl border px-3 py-2 transition"
                    :class="
                        date === day.date
                            ? 'border-forest bg-forest text-cream'
                            : 'border-line bg-linen hover:border-forest/40'
                    "
                    :aria-current="date === day.date ? 'date' : undefined"
                    @click="showDay(day.date)"
                >
                    <span class="text-xs opacity-80">{{ day.weekday }}</span>
                    <span class="font-serif text-xl">{{ day.day }}</span>
                </button>
            </nav>

            <BookingGrid
                class="mt-4"
                :bays="bays"
                :schedule="schedule"
                :now="now"
                :timezone="studio.timezone"
                :slot-minutes="studio.slot_minutes"
                :min-minutes="studio.min_minutes"
                :selection="selection ? { ...selection, minutes } : null"
                @pick="pick"
            />

            <p class="mt-4 text-sm text-ink-faint">
                Open 24/7. Bookings run 1–4 hours and can go past midnight.
                Cancel {{ studio.cancel_cutoff_hours }}+ hours ahead for a full
                refund.
            </p>
        </section>

        <Transition
            enter-active-class="transition-transform duration-300"
            leave-active-class="transition-transform duration-200"
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <BookingDrawer
                v-if="selection && selectedBay && startsAt"
                v-model:minutes="minutes"
                :bay="selectedBay"
                :starts-at="startsAt"
                :day-label="selectedDay.long"
                :free="free"
                @close="selection = null"
            />
        </Transition>
    </SiteLayout>
</template>
