<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { money, plural } from '@/lib/studio';

type Booking = {
    id: string;
    status: 'held' | 'confirmed' | 'cancelled' | 'expired';
    bay: string;
    date: string;
    time: string;
    hours: number;
    players: number;
    name: string;
    email: string;
    subtotal_cents: number;
    tax_cents: number;
    total_cents: number;
    door_code: string | null;
    door_opens: string;
    refunded: boolean;
    can_cancel: boolean;
    cancel_url: string;
    book_again_url: string;
};

const props = defineProps<{ booking: Booking; paid: boolean }>();

const studio = usePage().props.studio;
const cancelForm = useForm({});
const cancelError = computed(
    () => (cancelForm.errors as Record<string, string>).booking,
);

// Stripe sends the customer back before (or just after) its webhook lands.
// Poll briefly until the webhook has confirmed the booking.
const waiting = computed(() => props.booking.status === 'held' && props.paid);
const gaveUp = ref(false);
let timer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    if (!waiting.value) return;
    let tries = 0;
    timer = setInterval(() => {
        if (!waiting.value || ++tries > 20) {
            gaveUp.value = waiting.value;
            clearInterval(timer);
            return;
        }
        router.reload({ only: ['booking'] });
    }, 2000);
});
onBeforeUnmount(() => clearInterval(timer));

function cancel() {
    if (
        window.confirm(
            'Cancel this booking? You will get a full refund to your card.',
        )
    ) {
        cancelForm.post(props.booking.cancel_url, { preserveScroll: true });
    }
}

const heading = computed(
    () =>
        ({
            held: waiting.value
                ? 'Finishing your booking…'
                : 'Waiting for payment',
            confirmed: "You're booked!",
            cancelled: 'Booking cancelled',
            expired: 'This booking was not completed',
        })[props.booking.status],
);
</script>

<template>
    <Head :title="heading" />

    <SiteLayout>
        <section class="mx-auto max-w-2xl px-4 pt-6 pb-20 sm:px-6">
            <h1 class="font-serif text-4xl font-semibold text-forest-deep">
                {{ heading }}
            </h1>

            <p v-if="booking.status === 'held'" class="mt-3 text-ink-soft">
                <template v-if="waiting && !gaveUp">
                    Payment received. We're confirming it with the bank; this
                    takes a few seconds.
                </template>
                <template v-else-if="gaveUp">
                    This is taking longer than usual. Your confirmation email
                    will arrive as soon as it's done, or refresh this page in a
                    minute.
                </template>
                <template v-else>
                    This slot is held while you pay. If you closed the payment
                    page, start again from the booking grid.
                </template>
            </p>
            <p v-if="booking.status === 'cancelled'" class="mt-3 text-ink-soft">
                Your full refund of {{ money(booking.total_cents) }} is on its
                way back to your card.
            </p>
            <p v-if="booking.status === 'expired'" class="mt-3 text-ink-soft">
                <template v-if="booking.refunded">
                    Your payment came through after someone else took this slot,
                    so we refunded it in full.
                </template>
                <template v-else>
                    The payment wasn't finished, so the slot was released.
                    Nothing was charged.
                </template>
            </p>

            <!-- Door code -->
            <div
                v-if="booking.door_code"
                class="mt-8 rounded-3xl bg-forest-deep p-8 text-center text-cream"
            >
                <p class="text-sm tracking-wide text-amber uppercase">
                    Your door code
                </p>
                <p
                    class="mt-2 font-serif text-6xl font-semibold tracking-[0.2em]"
                >
                    {{ booking.door_code }}
                </p>
                <p class="mt-3 text-sm text-cream/70">
                    Works from {{ booking.door_opens }} until your booking ends.
                    We also emailed it to {{ booking.email }}.
                </p>
            </div>

            <!-- Details -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-line bg-linen"
            >
                <div class="border-b border-line p-6">
                    <p class="text-sm text-wood">{{ booking.bay }}</p>
                    <p class="mt-1 font-serif text-2xl font-semibold">
                        {{ booking.date }}
                    </p>
                    <p class="text-ink-soft">
                        {{ booking.time }} ·
                        {{ plural(booking.players, 'player') }}
                    </p>
                </div>
                <dl class="space-y-1 bg-sand p-6 text-sm">
                    <div class="flex justify-between">
                        <dt>Bay time ({{ booking.hours }} h)</dt>
                        <dd>{{ money(booking.subtotal_cents) }}</dd>
                    </div>
                    <div class="flex justify-between text-ink-faint">
                        <dt>Amusement tax</dt>
                        <dd>{{ money(booking.tax_cents) }}</dd>
                    </div>
                    <div
                        class="flex justify-between pt-2 font-serif text-lg font-semibold"
                    >
                        <dt>
                            {{
                                booking.status === 'confirmed'
                                    ? 'Paid'
                                    : 'Total'
                            }}
                        </dt>
                        <dd>{{ money(booking.total_cents) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div v-if="booking.status === 'confirmed'" class="mt-8">
                <button
                    v-if="booking.can_cancel"
                    type="button"
                    :disabled="cancelForm.processing"
                    class="rounded-full border border-rust/40 px-5 py-2.5 text-sm font-medium text-rust hover:bg-rust/5 disabled:opacity-60"
                    @click="cancel"
                >
                    Cancel for a full refund
                </button>
                <p v-else class="text-sm text-ink-faint">
                    It's less than {{ studio.cancel_cutoff_hours }} hours until
                    your booking, so it can no longer be cancelled.
                </p>
                <p v-if="cancelError" class="mt-2 text-sm text-rust">
                    {{ cancelError }}
                </p>
            </div>

            <div class="mt-10 flex flex-wrap gap-3">
                <Link
                    :href="booking.book_again_url"
                    class="rounded-full bg-forest px-5 py-2.5 text-sm font-medium text-cream hover:bg-forest-dark"
                >
                    {{
                        booking.status === 'confirmed'
                            ? 'Book another time'
                            : 'Back to the booking grid'
                    }}
                </Link>
                <Link
                    v-if="booking.door_code"
                    href="/door"
                    class="rounded-full border border-forest/30 px-5 py-2.5 text-sm font-medium text-forest hover:bg-forest/5"
                >
                    Try your code on the demo keypad
                </Link>
            </div>
        </section>
    </SiteLayout>
</template>
