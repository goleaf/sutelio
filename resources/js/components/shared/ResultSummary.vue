<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        summary: string;
        detail?: string;
        pending?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        detail: undefined,
        pending: false,
    },
);
</script>

<template>
    <div
        data-slot="result-summary"
        aria-live="polite"
        aria-atomic="true"
        :aria-busy="props.pending ? 'true' : undefined"
        :class="cn('min-w-0', props.class)"
    >
        <p class="text-sm font-semibold tabular-nums">
            {{ props.summary }}
        </p>
        <p
            v-if="props.detail"
            class="mt-0.5 text-xs text-muted-foreground tabular-nums"
        >
            {{ props.detail }}
        </p>
    </div>
</template>
