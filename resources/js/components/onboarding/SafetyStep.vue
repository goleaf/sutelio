<script setup lang="ts">
import { DatabaseBackup, ShieldCheck, UsersRound } from '@lucide/vue';
import type { OnboardingCopy } from '@/components/onboarding/onboarding-types';
import IconTile from '@/components/shared/IconTile.vue';

defineProps<{
    copy: OnboardingCopy['safety'];
    canManageWorkspace: boolean;
}>();

const topics = [
    { key: 'team', icon: UsersRound, tone: 'information' },
    { key: 'security', icon: ShieldCheck, tone: 'success' },
    { key: 'backup', icon: DatabaseBackup, tone: 'warning' },
] as const;
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-3 lg:grid-cols-3">
            <article
                v-for="topic in topics"
                :key="topic.key"
                class="rounded-2xl border border-border/80 bg-muted/20 p-4"
            >
                <IconTile :tone="topic.tone" size="sm">
                    <component :is="topic.icon" />
                </IconTile>
                <h2 class="mt-4 font-semibold">
                    {{ copy[`${topic.key}_title`] }}
                </h2>
                <p class="mt-2 text-base leading-7 text-muted-foreground">
                    {{ copy[`${topic.key}_description`] }}
                </p>
            </article>
        </div>
        <p
            class="rounded-2xl border border-status-information-border bg-status-information-surface p-4 text-base leading-7 text-status-information-text"
        >
            {{ canManageWorkspace ? copy.manager_note : copy.member_note }}
        </p>
    </div>
</template>
