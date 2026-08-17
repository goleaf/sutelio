<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import ResponsiveSectionNavigation from '@/components/shared/ResponsiveSectionNavigation.vue';

type SettingsSectionItem = {
    active: boolean;
    href: string;
    icon: LucideIcon;
    label: string;
};

const props = defineProps<{
    currentLabel: string;
    items: SettingsSectionItem[];
    label: string;
    openLabel: string;
}>();

const navigationItems = computed(() =>
    props.items.map((item) => ({
        ...item,
        key: item.href,
    })),
);
</script>

<template>
    <ResponsiveSectionNavigation
        :items="navigationItems"
        :label="label"
        :current-label="currentLabel"
        :open-label="openLabel"
    />
</template>
