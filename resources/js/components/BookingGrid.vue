<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { addMinutes, clock, hourLabel, hours } from '@/lib/studio';
import type { Bay, Block, GridStatus, Schedule } from '@/types';

const props = defineProps<{
    bays: Bay[];
    schedule: Schedule;
    now: string;
    timezone: string;
    slotMinutes: number;
    minMinutes: number;
    selection: { bayId: number; offset: number; minutes: number } | null;
}>();

const emit = defineEmits<{ pick: [bayId: number, offset: number] }>();

const ROW = 28;
const scroller = ref<HTMLElement | null>(null);

const dayStart = computed(() => new Date(props.schedule.starts_at));
const nowMs = computed(() => new Date(props.now).getTime());

/** Offsets (minutes from local midnight) of every slot in the day. */
const slots = computed(() =>
    Array.from(
        { length: props.schedule.minutes / props.slotMinutes },
        (_, i) => i * props.slotMinutes,
    ),
);

const nowOffset = computed(
    () => (nowMs.value - dayStart.value.getTime()) / 60_000,
);

const at = (offset: number) => addMinutes(dayStart.value, offset);

function blocksOn(bayId: number): Block[] {
    return props.schedule.blocks.filter((b) => b.bay_id === bayId);
}

/** Minutes free from this slot until the next booking on the bay. */
function freeFrom(bayId: number, offset: number): number {
    const next = blocksOn(bayId)
        .filter((b) => b.offset + b.minutes > offset)
        .reduce((min, b) => Math.min(min, b.offset), Infinity);

    return next - offset;
}

function isOpen(bayId: number, offset: number): boolean {
    return (
        offset >= nowOffset.value && freeFrom(bayId, offset) >= props.minMinutes
    );
}

function visible(blocks: Block[]): Block[] {
    return blocks.filter(
        (b) => b.offset < props.schedule.minutes && b.offset + b.minutes > 0,
    );
}

const top = (offset: number) => (Math.max(offset, 0) / props.slotMinutes) * ROW;
const height = (offset: number, minutes: number) =>
    ((Math.min(offset + minutes, props.schedule.minutes) -
        Math.max(offset, 0)) /
        props.slotMinutes) *
    ROW;

const statusStyle: Record<GridStatus, string> = {
    Complete: 'border-line bg-sand-dark text-ink-faint',
    'In Progress': 'border-amber-dark bg-amber text-wood-dark',
    Booked: 'border-forest-dark bg-forest text-cream',
};

async function scrollToStart() {
    await nextTick();
    if (!scroller.value) return;
    // Today: just above "now". Other days: 2pm, when evenings get busy.
    const target = nowOffset.value > 0 ? nowOffset.value - 120 : 14 * 60;
    scroller.value.scrollTop = Math.max(0, (target / props.slotMinutes) * ROW);
}

onMounted(scrollToStart);
watch(() => props.schedule.date, scrollToStart);
</script>

<template>
    <div
        v-if="bays.length === 0"
        class="rounded-2xl border border-dashed border-line bg-linen p-10 text-center text-ink-soft"
    >
        No bays are open for booking yet. Check back soon.
    </div>
    <div
        v-else
        class="overflow-hidden rounded-2xl border border-line bg-linen shadow-sm"
    >
        <div
            class="grid border-b border-line bg-sand text-center"
            :style="{
                gridTemplateColumns: `56px repeat(${bays.length}, minmax(0, 1fr))`,
            }"
        >
            <div />
            <div
                v-for="bay in bays"
                :key="bay.id"
                class="py-3 font-serif font-semibold"
            >
                {{ bay.name }}
            </div>
        </div>

        <div ref="scroller" class="max-h-[560px] overflow-y-auto">
            <div
                class="relative grid"
                :style="{
                    gridTemplateColumns: `56px repeat(${bays.length}, minmax(0, 1fr))`,
                }"
            >
                <!-- Hour labels -->
                <div aria-hidden="true">
                    <div
                        v-for="offset in slots"
                        :key="offset"
                        class="relative pr-2 text-right text-[11px] text-ink-faint"
                        :style="{ height: ROW + 'px' }"
                    >
                        <span
                            v-if="offset % 60 === 0 && offset > 0"
                            class="absolute top-0 right-2 -translate-y-1/2 leading-none"
                            >{{ hourLabel(at(offset), timezone) }}</span
                        >
                    </div>
                </div>

                <!-- Bays -->
                <div
                    v-for="bay in bays"
                    :key="bay.id"
                    class="relative border-l border-line"
                >
                    <button
                        v-for="offset in slots"
                        :key="offset"
                        type="button"
                        :disabled="!isOpen(bay.id, offset)"
                        :aria-label="`${bay.name}, ${clock(at(offset), timezone)}`"
                        class="block w-full border-b text-[11px] text-transparent transition focus-visible:text-forest focus-visible:outline-2 focus-visible:outline-forest disabled:cursor-default"
                        :class="[
                            offset % 60 === 30
                                ? 'border-line'
                                : 'border-dashed border-line-soft',
                            offset < nowOffset
                                ? 'bg-sand/70'
                                : 'hover:bg-forest/10 hover:text-forest',
                        ]"
                        :style="{ height: ROW + 'px' }"
                        @click="emit('pick', bay.id, offset)"
                    >
                        {{ clock(at(offset), timezone) }}
                    </button>

                    <div
                        v-for="block in visible(blocksOn(bay.id))"
                        :key="block.starts_at"
                        class="pointer-events-none absolute inset-x-1.5 overflow-hidden rounded-lg border px-2 py-1 text-[11px] leading-tight"
                        :class="statusStyle[block.status]"
                        :style="{
                            top: top(block.offset) + 1 + 'px',
                            height:
                                height(block.offset, block.minutes) - 2 + 'px',
                        }"
                    >
                        <span class="font-semibold">{{ block.status }}</span>
                        <span class="ml-1 opacity-80"
                            >{{ clock(new Date(block.starts_at), timezone) }} ·
                            {{ hours(block.minutes) }}</span
                        >
                        <span v-if="block.offset < 0" class="block opacity-70"
                            >from last night</span
                        >
                        <span
                            v-if="
                                block.offset + block.minutes > schedule.minutes
                            "
                            class="block opacity-70"
                            >runs past midnight</span
                        >
                    </div>

                    <div
                        v-if="selection && selection.bayId === bay.id"
                        class="pointer-events-none absolute inset-x-1.5 rounded-lg border-2 border-dashed border-amber-dark bg-amber/30 px-2 py-1 text-[11px] font-semibold text-wood-dark"
                        :style="{
                            top: top(selection.offset) + 'px',
                            height:
                                height(selection.offset, selection.minutes) +
                                'px',
                        }"
                    >
                        Your booking
                    </div>
                </div>

                <!-- Now line -->
                <div
                    v-if="nowOffset > 0 && nowOffset < schedule.minutes"
                    class="pointer-events-none absolute right-0 left-12 flex -translate-y-1/2 items-center"
                    :style="{
                        top: (nowOffset / slotMinutes) * ROW + 'px',
                    }"
                >
                    <span
                        class="rounded-full bg-rust px-1.5 text-[10px] font-semibold text-white"
                        >{{ clock(new Date(now), timezone) }}</span
                    >
                    <span class="h-px flex-1 bg-rust" />
                </div>
            </div>
        </div>
    </div>
</template>
