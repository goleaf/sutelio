<script setup lang="ts">
import { ListChecks, Sparkles, UsersRound } from '@lucide/vue';
import type { OnboardingCopy } from '@/components/onboarding/onboarding-types';
import IconTile from '@/components/shared/IconTile.vue';

defineProps<{ copy: OnboardingCopy['welcome'] }>();

const features = [
    { key: 'capture', icon: ListChecks },
    { key: 'plan', icon: Sparkles },
    { key: 'collaborate', icon: UsersRound },
] as const;
</script>

<template>
    <div class="space-y-6">
        <p class="max-w-3xl text-base leading-7 text-foreground/85">
            {{ copy.intro }}
        </p>
        <div class="grid gap-3 lg:grid-cols-3">
            <article
                v-for="feature in features"
                :key="feature.key"
                class="rounded-2xl border border-border/80 bg-muted/25 p-4"
            >
                <IconTile tone="brand" size="sm">
                    <component :is="feature.icon" />
                </IconTile>
                <h2 class="mt-4 text-base font-semibold">
                    {{ copy[`${feature.key}_title`] }}
                </h2>
                <p class="mt-2 text-base leading-7 text-muted-foreground">
                    {{ copy[`${feature.key}_description`] }}
                </p>
            </article>
        </div>
        <p
            class="rounded-2xl border border-status-success-border bg-status-success-surface p-4 text-base leading-7 text-status-success-text"
        >
            {{ copy.privacy }}
        </p>
    </div>
</template>
