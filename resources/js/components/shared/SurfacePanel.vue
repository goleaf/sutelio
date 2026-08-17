<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: 'div' | 'section' | 'aside' | 'article' | 'nav';
        padding?: 'none' | 'sm' | 'md';
        overflowHidden?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        as: 'div',
        padding: 'none',
        overflowHidden: true,
    },
);

const paddingClass = computed(
    () =>
        ({
            none: undefined,
            sm: 'p-4',
            md: 'p-5 sm:p-6',
        })[props.padding],
);
</script>

<template>
    <component
        :is="props.as"
        data-slot="surface-panel"
        :class="
            cn(
                'rounded-panel border border-border/80 bg-card shadow-panel',
                props.overflowHidden && 'overflow-hidden',
                paddingClass,
                props.class,
            )
        "
    >
        <slot />
    </component>
</template>
