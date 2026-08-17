<script setup lang="ts">
import { DatabaseBackup, ShieldCheck, UsersRound } from '@lucide/vue';
import type { OnboardingCopy } from '@/components/onboarding/onboarding-types';

defineProps<{
    copy: OnboardingCopy['safety'];
    canManageWorkspace: boolean;
}>();

const topics = [
    { key: 'team', icon: UsersRound },
    { key: 'security', icon: ShieldCheck },
    { key: 'backup', icon: DatabaseBackup },
] as const;
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-3 md:grid-cols-3">
            <article
                v-for="topic in topics"
                :key="topic.key"
                class="rounded-2xl border border-border/80 bg-muted/20 p-4"
            >
                <component
                    :is="topic.icon"
                    class="size-5 text-orange-600"
                    aria-hidden="true"
                />
                <h2 class="mt-4 font-semibold">
                    {{ copy[`${topic.key}_title`] }}
                </h2>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{ copy[`${topic.key}_description`] }}
                </p>
            </article>
        </div>
        <p
            class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.07] p-4 text-sm leading-6 text-blue-950"
        >
            {{ canManageWorkspace ? copy.manager_note : copy.member_note }}
        </p>
    </div>
</template>
