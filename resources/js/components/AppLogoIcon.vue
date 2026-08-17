<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/favicon.svg"
        alt=""
        :class="className"
        aria-hidden="true"
        draggable="false"
        v-bind="$attrs"
    />
</template>
