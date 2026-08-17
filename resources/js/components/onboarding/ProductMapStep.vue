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

defineProps<{ copy: OnboardingCopy['product_map'] }>();

const destinations = [
    {
        key: 'dashboard',
        icon: LayoutDashboard,
        tone: 'text-orange-600',
    },
    {
        key: 'tasks',
        icon: ListChecks,
        tone: 'text-orange-600',
    },
    {
        key: 'projects',
        icon: FolderKanban,
        tone: 'text-blue-600',
    },
    {
        key: 'calendar',
        icon: CalendarDays,
        tone: 'text-blue-600',
    },
    {
        key: 'activity',
        icon: Activity,
        tone: 'text-slate-600',
    },
    {
        key: 'notifications',
        icon: Bell,
        tone: 'text-slate-600',
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
            <span
                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-background ring-1 ring-border"
            >
                <component
                    :is="destination.icon"
                    class="size-5"
                    :class="destination.tone"
                    aria-hidden="true"
                />
            </span>
            <div class="min-w-0">
                <h2 class="font-semibold">
                    {{ copy[`${destination.key}_title`] }}
                </h2>
                <p class="mt-1 text-sm leading-6 text-muted-foreground">
                    {{ copy[`${destination.key}_description`] }}
                </p>
            </div>
        </article>
    </div>
</template>
