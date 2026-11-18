<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SiteLayout from '@/layouts/SiteLayout.vue';

const props = defineProps<{ now: string; lead_minutes: number }>();

const page = usePage();
const result = computed(() => page.props.flash.door);

const form = useForm({ code: '', at: props.now });

function press(digit: string) {
    if (form.code.length < 6) form.code += digit;
}

function clear() {
    form.code = '';
}

// The code stays on the display so the same code can be retried at
// another pretend time.
function submit() {
    form.post('/door', { preserveScroll: true });
}

const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];
</script>

<template>
    <Head title="Door keypad demo" />

    <SiteLayout>
        <section
            class="mx-auto grid max-w-5xl items-start gap-12 px-4 pt-6 pb-20 sm:px-6 md:grid-cols-2"
        >
            <div>
                <h1 class="font-serif text-4xl font-semibold text-forest-deep">
                    Door keypad demo
                </h1>
                <p class="mt-4 leading-relaxed text-ink-soft">
                    The studio has no staff, so the door opens with the code
                    from your booking. A code works from
                    {{ lead_minutes }} minutes before your start until your
                    booking ends, and at no other time.
                </p>
                <p class="mt-4 leading-relaxed text-ink-soft">
                    Enter a code from a confirmed booking. To see the time
                    window in action, change the pretend time below and try the
                    same code again.
                </p>

                <label class="mt-8 block">
                    <span class="text-sm font-medium">Pretend it's…</span>
                    <input
                        v-model="form.at"
                        type="datetime-local"
                        class="mt-1 w-full rounded-xl border border-line bg-white px-4 py-3 outline-none focus:border-forest"
                    />
                </label>
                <p v-if="form.errors.at" class="mt-1 text-sm text-rust">
                    {{ form.errors.at }}
                </p>
            </div>

            <!-- Keypad -->
            <form
                class="mx-auto w-full max-w-xs rounded-[2rem] border-4 border-wood-dark bg-ink p-6 shadow-[0_20px_40px_-15px_rgba(60,40,20,0.6)]"
                @submit.prevent="submit"
            >
                <div
                    class="flex items-center justify-between rounded-xl px-4 py-3"
                    :class="
                        result
                            ? result.unlocked
                                ? 'bg-forest'
                                : 'bg-rust'
                            : 'bg-black/40'
                    "
                    role="status"
                    aria-live="polite"
                >
                    <span
                        class="font-mono text-2xl tracking-[0.4em] text-amber"
                        :aria-label="`${form.code.length} digits entered`"
                    >
                        {{ form.code.padEnd(6, '·') }}
                    </span>
                    <span class="text-xl" aria-hidden="true">{{
                        result ? (result.unlocked ? '🔓' : '🔒') : '🔒'
                    }}</span>
                </div>
                <p
                    v-if="result"
                    class="mt-3 text-center text-sm"
                    :class="
                        result.unlocked ? 'text-[#9fd3b0]' : 'text-[#f0a58a]'
                    "
                >
                    {{ result.message }}<br />
                    <span class="text-xs opacity-70">at {{ result.at }}</span>
                </p>
                <p
                    v-else-if="form.errors.code"
                    class="mt-3 text-center text-sm text-[#f0a58a]"
                >
                    {{ form.errors.code }}
                </p>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <button
                        v-for="key in keys"
                        :key="key"
                        type="button"
                        class="aspect-square rounded-full bg-white/10 font-serif text-2xl text-cream hover:bg-white/20 active:bg-white/30"
                        @click="press(key)"
                    >
                        {{ key }}
                    </button>
                    <button
                        type="button"
                        class="aspect-square rounded-full bg-white/5 text-sm text-cream/70 hover:bg-white/15"
                        @click="clear"
                    >
                        Clear
                    </button>
                    <button
                        type="button"
                        class="aspect-square rounded-full bg-white/10 font-serif text-2xl text-cream hover:bg-white/20 active:bg-white/30"
                        @click="press('0')"
                    >
                        0
                    </button>
                    <button
                        type="submit"
                        :disabled="form.code.length !== 6 || form.processing"
                        class="aspect-square rounded-full bg-forest text-sm font-semibold text-cream hover:bg-forest-dark disabled:opacity-40"
                    >
                        Enter
                    </button>
                </div>
            </form>
        </section>
    </SiteLayout>
</template>
