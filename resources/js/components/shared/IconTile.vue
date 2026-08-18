<script lang="ts">
export type IconTileTone =
    | 'brand'
    | 'cobalt'
    | 'muted'
    | 'success'
    | 'warning'
    | 'destructive'
    | 'information';
</script>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        tone?: IconTileTone;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { tone: 'brand', size: 'md' },
);

const toneClass = computed(
    () =>
        ({
            brand: 'border-orange-500/15 bg-orange-500/10 text-orange-800',
            cobalt: 'border-brand-cobalt/15 bg-brand-cobalt text-brand-ivory',
            muted: 'border-border/80 bg-muted text-muted-foreground',
            success:
                'border-status-success-border bg-status-success-surface text-status-success-icon',
            warning:
                'border-status-warning-border bg-status-warning-surface text-status-warning-icon',
            destructive:
                'border-status-destructive-border bg-status-destructive-surface text-status-destructive-icon',
            information:
                'border-status-information-border bg-status-information-surface text-status-information-icon',
        })[props.tone],
);

const sizeClass = computed(
    () =>
        ({
            sm: 'size-9 rounded-xl [&_svg]:size-4',
            md: 'size-11 rounded-2xl [&_svg]:size-5',
            lg: 'size-12 rounded-2xl [&_svg]:size-6',
        })[props.size],
);
</script>

<template>
    <span
        data-slot="icon-tile"
        aria-hidden="true"
        :class="
            cn(
                'ui-icon-response flex shrink-0 items-center justify-center border shadow-sm',
                toneClass,
                sizeClass,
                props.class,
            )
        "
    >
        <slot />
    </span>
</template>
