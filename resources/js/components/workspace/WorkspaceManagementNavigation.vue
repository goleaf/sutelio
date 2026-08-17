<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import ResponsiveSectionNavigation from '@/components/shared/ResponsiveSectionNavigation.vue';
import type { WorkspaceManagementSection } from '@/types/models';
import type { RouteDefinition } from '@/wayfinder';

export interface WorkspaceManagementNavigationItem {
    section: WorkspaceManagementSection;
    label: string;
    icon: LucideIcon;
    href: string | RouteDefinition<'get'>;
}

const props = defineProps<{
    activeSection: WorkspaceManagementSection;
    currentLabel: string;
    items: WorkspaceManagementNavigationItem[];
    label: string;
    openLabel: string;
}>();

const navigationItems = computed(() =>
    props.items.map((item) => ({
        ...item,
        active: item.section === props.activeSection,
        key: item.section,
        tone:
            item.section === 'danger'
                ? ('danger' as const)
                : ('default' as const),
    })),
);
</script>

<template>
    <ResponsiveSectionNavigation
        :items="navigationItems"
        :label="label"
        :current-label="currentLabel"
        :open-label="openLabel"
        desktop-mode="segmented"
        prefetch="click"
    />
</template>
