<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const notice = computed(() => page.props.flash.notice);
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <header
            class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-5 sm:px-6"
        >
            <Link href="/" class="flex items-center gap-2">
                <span
                    class="grid size-9 place-items-center rounded-full bg-forest text-lg text-amber"
                    aria-hidden="true"
                    >☀</span
                >
                <span class="font-serif text-lg font-semibold sm:text-xl">
                    Back Porch Golf Co.
                </span>
            </Link>
            <nav class="hidden items-center gap-8 text-sm md:flex">
                <Link href="/#how" class="hover:text-forest">How it works</Link>
                <Link href="/#pricing" class="hover:text-forest">Pricing</Link>
                <Link href="/#faq" class="hover:text-forest">FAQ</Link>
            </nav>
            <Link
                href="/book"
                class="rounded-full bg-forest px-5 py-2.5 text-sm font-medium whitespace-nowrap text-cream hover:bg-forest-dark"
            >
                Book a bay
            </Link>
        </header>

        <div
            v-if="notice"
            class="mx-auto mb-2 w-full max-w-6xl px-4 sm:px-6"
            role="status"
        >
            <p
                class="rounded-xl border border-amber-dark/40 bg-amber/25 px-4 py-3 text-sm text-wood-dark"
            >
                {{ notice }}
            </p>
        </div>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-line">
            <div
                class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-8 text-sm text-ink-faint sm:flex-row sm:items-center sm:justify-between sm:px-6"
            >
                <p>
                    Back Porch Golf Co. is a made-up studio for a portfolio
                    demo. Payments run in Stripe test mode.
                </p>
                <nav class="flex gap-6">
                    <Link href="/book" class="hover:text-forest">Book</Link>
                    <Link href="/door" class="hover:text-forest"
                        >Door keypad demo</Link
                    >
                </nav>
            </div>
        </footer>
    </div>
</template>
