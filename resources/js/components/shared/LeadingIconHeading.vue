<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    iconClass?: HTMLAttributes['class'];
    contentClass?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        data-slot="leading-icon-heading"
        :class="cn('flex min-w-0 flex-nowrap items-start gap-3', props.class)"
    >
        <div
            data-slot="leading-icon-heading-icon"
            :class="cn('shrink-0', props.iconClass)"
        >
            <slot name="icon" />
        </div>

        <div
            data-slot="leading-icon-heading-content"
            :class="cn('grid min-w-0 flex-1 gap-1.5', props.contentClass)"
        >
            <slot />
        </div>
    </div>
</template>
