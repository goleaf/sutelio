<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    label: string;
    summary: string;
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <nav
        data-slot="pagination-bar"
        :aria-label="props.label"
        :class="
            cn(
                'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between',
                props.class,
            )
        "
    >
        <p class="text-sm text-muted-foreground tabular-nums">
            {{ props.summary }}
        </p>
        <div
            data-slot="pagination-actions"
            class="grid grid-cols-2 gap-2 sm:flex"
        >
            <slot />
        </div>
    </nav>
</template>
