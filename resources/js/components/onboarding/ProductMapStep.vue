<script setup lang="ts">
import {
    Activity,
    Bell,
    CalendarDays,
    FolderKanban,
    LayoutDashboard,
    ListChecks,
} from '@lucide/vue';
import type { OnboardingCopy } from '@/components/onboarding/onboarding-types';
import IconTile from '@/components/shared/IconTile.vue';

defineProps<{ copy: OnboardingCopy['product_map'] }>();

const destinations = [
    {
        key: 'dashboard',
        icon: LayoutDashboard,
    },
    {
        key: 'tasks',
        icon: ListChecks,
    },
    {
        key: 'projects',
        icon: FolderKanban,
    },
    {
        key: 'calendar',
        icon: CalendarDays,
    },
    {
        key: 'activity',
        icon: Activity,
    },
    {
        key: 'notifications',
        icon: Bell,
    },
] as const;
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2">
        <article
            v-for="destination in destinations"
            :key="destination.key"
            class="flex gap-4 rounded-2xl border border-border/80 bg-muted/20 p-4"
        >
            <IconTile tone="cobalt" size="sm">
                <component :is="destination.icon" />
            </IconTile>
            <div class="min-w-0">
                <h2 class="font-semibold">
                    {{ copy[`${destination.key}_title`] }}
                </h2>
                <p class="mt-1 text-base leading-7 text-muted-foreground">
                    {{ copy[`${destination.key}_description`] }}
                </p>
            </div>
        </article>
    </div>
</template>
