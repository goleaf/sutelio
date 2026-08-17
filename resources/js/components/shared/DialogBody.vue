<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        data-slot="dialog-body"
        :class="cn('space-y-6 px-4 py-5 sm:px-8 sm:py-6', props.class)"
    >
        <slot />
    </div>
</template>
