<script setup lang="ts">
import { computed } from 'vue';
import { resolveProjectIcon } from '@/components/project/project-icons';

const props = defineProps<{
    value?: string | null;
}>();

const icon = computed(() => resolveProjectIcon(props.value));
</script>

<template>
    <component :is="icon" aria-hidden="true" />
</template>
