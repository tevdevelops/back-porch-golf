<script setup lang="ts">
// PROTOTYPE — floating variant switcher for throwaway design prototypes.
// Hidden in production builds.
import { computed, onBeforeUnmount, onMounted } from 'vue';

const props = defineProps<{
    variants: { key: string; name: string }[];
    current: string;
}>();

const emit = defineEmits<{ change: [key: string] }>();

const show = !import.meta.env.PROD;

const label = computed(() => {
    const v = props.variants.find((x) => x.key === props.current);
    return v ? `${v.key} (${v.name})` : props.current;
});

function step(by: number) {
    const n = props.variants.length;
    const i = props.variants.findIndex((v) => v.key === props.current);
    emit('change', props.variants[(i + by + n) % n].key);
}

function onKey(e: KeyboardEvent) {
    const el = e.target as HTMLElement | null;
    if (el && (el.closest('input, textarea, select') || el.isContentEditable)) {
        return;
    }
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
}

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <div
        v-if="show"
        class="fixed bottom-4 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-1 rounded-full bg-black/90 p-1 font-mono text-xs text-white shadow-2xl ring-1 ring-white/20"
    >
        <button
            type="button"
            class="rounded-full px-3 py-2 hover:bg-white/15"
            aria-label="Previous variant"
            @click="step(-1)"
        >
            ←
        </button>
        <span class="min-w-44 px-2 text-center"> PROTOTYPE · {{ label }} </span>
        <button
            type="button"
            class="rounded-full px-3 py-2 hover:bg-white/15"
            aria-label="Next variant"
            @click="step(1)"
        >
            →
        </button>
    </div>
</template>
