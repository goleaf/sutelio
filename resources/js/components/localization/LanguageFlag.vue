<script setup lang="ts">
defineProps<{
    src: string;
}>();
</script>

<template>
    <span
        class="relative inline-flex h-5 w-7 shrink-0 overflow-hidden rounded-[0.3rem] border border-black/10 bg-muted shadow-sm"
        aria-hidden="true"
    >
        <img class="size-full object-cover" :src="src" alt="" />
        <span
            class="pointer-events-none absolute inset-0 ring-1 ring-white/20 ring-inset"
        />
    </span>
</template>
