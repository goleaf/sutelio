<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        icon?: Component;
        size?: 'sm' | 'md';
        class?: HTMLAttributes['class'];
    }>(),
    {
        icon: undefined,
        size: 'md',
    },
);

const sizeClass = computed(() =>
    props.size === 'sm'
        ? '[&_svg]:size-3.5 [&_img]:size-3.5'
        : '[&_svg]:size-4 [&_img]:size-4',
);
</script>

<template>
    <span
        data-slot="onboarding-icon"
        aria-hidden="true"
        :class="
            cn(
                'inline-flex shrink-0 items-center justify-center text-orange-700 forced-colors:text-[CanvasText]',
                sizeClass,
                props.class,
            )
        "
    >
        <component :is="icon" v-if="icon" />
        <slot v-else />
    </span>
</template>
