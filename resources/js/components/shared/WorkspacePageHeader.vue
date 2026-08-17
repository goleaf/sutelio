<script setup lang="ts">
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';

defineProps<{
    eyebrow: string;
    title: string;
    description: string;
}>();
</script>

<template>
    <header
        class="relative overflow-hidden rounded-feature border border-border/80 bg-card shadow-[0_24px_70px_-44px_rgba(15,23,42,0.45)]"
    >
        <div
            class="absolute inset-y-0 left-0 w-1.5 bg-orange-500"
            aria-hidden="true"
        />
        <div
            class="absolute -top-24 -right-24 size-64 rounded-full border-[52px] border-orange-500/5"
            aria-hidden="true"
        />

        <div
            class="relative flex flex-col gap-6 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between lg:px-10 lg:py-9"
        >
            <div class="max-w-3xl min-w-0 flex-1">
                <div v-if="$slots.back" class="mb-3">
                    <slot name="back" />
                </div>
                <LeadingIconHeading tile tile-tone="cobalt" tile-size="lg">
                    <template #icon><slot name="icon" /></template>
                    <p
                        class="text-[0.7rem] font-semibold tracking-[0.22em] text-orange-700 uppercase"
                    >
                        {{ eyebrow }}
                    </p>
                    <div v-if="$slots.badges" class="flex flex-wrap gap-2">
                        <slot name="badges" />
                    </div>
                    <h1
                        class="text-3xl font-semibold tracking-[-0.04em] text-balance wrap-anywhere text-brand-deep-cobalt sm:text-4xl"
                    >
                        {{ title }}
                    </h1>
                    <p
                        class="max-w-2xl text-sm leading-6 wrap-anywhere text-muted-foreground sm:text-base"
                    >
                        {{ description }}
                    </p>
                </LeadingIconHeading>
            </div>

            <div
                v-if="$slots.actions"
                class="grid w-full min-w-0 grid-cols-1 gap-2 sm:flex sm:w-auto sm:flex-wrap lg:shrink-0"
            >
                <slot name="actions" />
            </div>
        </div>

        <div
            v-if="$slots.metrics"
            class="relative grid divide-y border-t border-border/70 bg-muted/25 sm:auto-cols-fr sm:grid-flow-col sm:divide-x sm:divide-y-0"
        >
            <slot name="metrics" />
        </div>
    </header>
</template>
