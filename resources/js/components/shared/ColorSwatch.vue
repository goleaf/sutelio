<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        color?: string | null;
        fallback?: string;
        label?: string;
        size?: 'xs' | 'sm' | 'md' | 'lg';
        emphasized?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        color: undefined,
        fallback: '#64748b',
        label: undefined,
        size: 'sm',
        emphasized: false,
    },
);

const sizeClass = computed(
    () =>
        ({
            xs: 'size-1.5',
            sm: 'size-2',
            md: 'size-2.5',
            lg: 'size-4',
        })[props.size],
);
</script>

<template>
    <span
        data-slot="color-swatch"
        :role="props.label ? 'img' : undefined"
        :aria-label="props.label"
        :aria-hidden="props.label ? undefined : 'true'"
        :style="{
            backgroundColor: safeDefinitionColor(props.color, props.fallback),
        }"
        :class="
            cn(
                'shrink-0 rounded-full forced-colors:border forced-colors:border-[CanvasText]',
                sizeClass,
                props.emphasized && 'ring-4 ring-muted',
                props.class,
            )
        "
    />
</template>
