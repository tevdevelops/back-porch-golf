<script setup lang="ts">
// PROTOTYPE — three design directions for Back Porch Golf Co., switchable
// via ?variant=A|B|C on /prototype/design. Throwaway: the winner gets
// rewritten properly; the rest lives only on a prototype branch.
import { ref } from 'vue';
import PrototypeSwitcher from '@/components/PrototypeSwitcher.vue';
import VariantA from '@/prototype/design/VariantA.vue';
import VariantB from '@/prototype/design/VariantB.vue';
import VariantC from '@/prototype/design/VariantC.vue';

const variants = [
    { key: 'A', name: 'Warm porch' },
    { key: 'B', name: 'Retro clubhouse' },
    { key: 'C', name: 'Clean modern' },
];

const fromUrl = new URLSearchParams(window.location.search).get('variant');
const variant = ref(
    variants.some((v) => v.key === fromUrl) ? (fromUrl as string) : 'A',
);

function change(key: string) {
    variant.value = key;
    const url = new URL(window.location.href);
    url.searchParams.set('variant', key);
    window.history.replaceState(window.history.state, '', url);
    window.scrollTo({ top: 0 });
}
</script>

<template>
    <VariantA v-if="variant === 'A'" />
    <VariantB v-else-if="variant === 'B'" />
    <VariantC v-else />
    <PrototypeSwitcher
        :variants="variants"
        :current="variant"
        @change="change"
    />
</template>
