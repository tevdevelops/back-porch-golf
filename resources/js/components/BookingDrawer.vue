<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import {
    addMinutes,
    clock,
    hours,
    isoMinute,
    money,
    quote,
} from '@/lib/studio';
import type { Bay } from '@/types';

const props = defineProps<{
    bay: Bay;
    startsAt: Date;
    dayLabel: string;
    /** Minutes free on this bay from the start (next booking or ∞). */
    free: number;
}>();

const minutes = defineModel<number>('minutes', { required: true });
const emit = defineEmits<{ close: [] }>();

const studio = usePage().props.studio;

const lengths = computed(() => {
    const max = Math.min(studio.max_minutes, props.free);
    const list: number[] = [];
    for (let m = studio.min_minutes; m <= max; m += studio.slot_minutes) {
        list.push(m);
    }
    return list;
});

const form = useForm({
    bay_id: props.bay.id,
    starts_at: isoMinute(props.startsAt),
    minutes: minutes.value,
    players: 2,
    name: '',
    email: '',
    phone: '',
});

// Keep the form in step when the customer picks another slot or length.
watch(
    () => [props.bay.id, props.startsAt.getTime()],
    () => {
        form.bay_id = props.bay.id;
        form.starts_at = isoMinute(props.startsAt);
        form.clearErrors('starts_at');
    },
);
watch(
    lengths,
    (list) => {
        if (!list.includes(minutes.value)) minutes.value = list.at(-1) ?? 60;
    },
    { immediate: true },
);
watch(minutes, (m) => (form.minutes = m), { immediate: true });

const price = computed(() => quote(studio, minutes.value, form.players));
const ends = computed(() => addMinutes(props.startsAt, minutes.value));

function submit() {
    form.post('/bookings', { preserveScroll: true });
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') emit('close');
}
onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));

const input =
    'w-full rounded-xl border border-line bg-white px-4 py-3 outline-none focus:border-forest';
</script>

<template>
    <aside
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col border-l border-line bg-linen shadow-2xl"
        role="dialog"
        aria-modal="false"
        aria-labelledby="drawer-title"
    >
        <div class="flex items-start justify-between border-b border-line p-6">
            <div>
                <p class="text-sm text-wood">{{ bay.name }} · {{ dayLabel }}</p>
                <h2
                    id="drawer-title"
                    class="mt-1 font-serif text-2xl font-semibold"
                >
                    {{ clock(startsAt, studio.timezone) }} –
                    {{ clock(ends, studio.timezone) }}
                </h2>
            </div>
            <button
                type="button"
                class="rounded-full p-2 text-xl leading-none hover:bg-black/5"
                aria-label="Close"
                @click="emit('close')"
            >
                ×
            </button>
        </div>

        <form
            id="booking-form"
            class="flex-1 space-y-6 overflow-y-auto p-6"
            @submit.prevent="submit"
        >
            <fieldset>
                <legend class="mb-2 text-sm font-medium">How long?</legend>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="m in lengths"
                        :key="m"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-sm"
                        :class="
                            minutes === m
                                ? 'border-forest bg-forest text-white'
                                : 'border-line hover:border-forest/40'
                        "
                        :aria-pressed="minutes === m"
                        @click="minutes = m"
                    >
                        {{ m / 60 }}h
                    </button>
                </div>
                <p v-if="form.errors.minutes" class="mt-2 text-sm text-rust">
                    {{ form.errors.minutes }}
                </p>
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-sm font-medium">
                    Players, including you
                </legend>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="p in studio.max_players"
                        :key="p"
                        type="button"
                        class="rounded-xl border py-2 font-serif text-lg"
                        :class="
                            form.players === p
                                ? 'border-forest bg-forest text-white'
                                : 'border-line hover:border-forest/40'
                        "
                        :aria-pressed="form.players === p"
                        @click="form.players = p"
                    >
                        {{ p }}
                    </button>
                </div>
                <p class="mt-2 text-xs text-ink-faint">
                    Everyone who plays must be declared now.
                </p>
            </fieldset>

            <div class="space-y-3">
                <div>
                    <label class="sr-only" for="name">Full name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        autocomplete="name"
                        placeholder="Full name"
                        required
                        :class="input"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-rust">
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label class="sr-only" for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="Email"
                        required
                        :class="input"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-rust">
                        {{ form.errors.email }}
                    </p>
                </div>
                <div>
                    <label class="sr-only" for="phone">Phone</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        autocomplete="tel"
                        placeholder="Phone (for your door code)"
                        required
                        :class="input"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-rust">
                        {{ form.errors.phone }}
                    </p>
                </div>
            </div>
        </form>

        <div class="border-t border-line bg-sand p-6">
            <p
                v-if="form.errors.starts_at || form.errors.players"
                class="mb-4 rounded-xl bg-rust/10 p-3 text-sm text-rust"
                role="alert"
            >
                {{ form.errors.starts_at ?? form.errors.players }}
            </p>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <dt>{{ hours(minutes) }} × {{ money(price.hourly) }}/hr</dt>
                    <dd>{{ money(price.subtotal) }}</dd>
                </div>
                <div class="flex justify-between text-ink-faint">
                    <dt>Amusement tax ({{ studio.tax_rate * 100 }}%)</dt>
                    <dd>{{ money(price.tax) }}</dd>
                </div>
                <div
                    class="flex justify-between pt-2 font-serif text-xl font-semibold"
                >
                    <dt>Total</dt>
                    <dd>{{ money(price.total) }}</dd>
                </div>
            </dl>
            <button
                type="submit"
                form="booking-form"
                :disabled="form.processing"
                class="mt-4 w-full rounded-full bg-forest py-3 font-medium text-cream hover:bg-forest-dark disabled:opacity-60"
            >
                {{
                    form.processing
                        ? 'Holding your slot…'
                        : 'Continue to payment'
                }}
            </button>
            <p class="mt-3 text-center text-xs text-ink-faint">
                We hold this slot for 30 minutes while you pay.
            </p>
        </div>
    </aside>
</template>
