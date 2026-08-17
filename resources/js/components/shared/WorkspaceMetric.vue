<script setup lang="ts">
import type { Component } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import type { IconTileTone } from '@/components/shared/IconTile.vue';

withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        icon: Component;
        tone?: 'orange' | 'blue' | 'emerald' | 'violet' | 'slate';
    }>(),
    {
        tone: 'slate',
    },
);

const tileTones: Record<
    'orange' | 'blue' | 'emerald' | 'violet' | 'slate',
    IconTileTone
> = {
    orange: 'brand',
    blue: 'information',
    emerald: 'success',
    violet: 'cobalt',
    slate: 'muted',
};
</script>

<template>
    <div class="flex min-w-0 items-center gap-3 px-5 py-4">
        <IconTile :tone="tileTones[tone]" size="md">
            <component :is="icon" />
        </IconTile>
        <div class="min-w-0">
            <p class="truncate text-xs font-medium text-muted-foreground">
                {{ label }}
            </p>
            <p class="mt-0.5 text-xl font-semibold tracking-tight">
                {{ value }}
            </p>
        </div>
    </div>
</template>
