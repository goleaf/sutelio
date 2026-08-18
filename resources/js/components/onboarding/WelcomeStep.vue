<script setup lang="ts">
import { ListChecks, ShieldCheck, Sparkles, UsersRound } from '@lucide/vue';
import type { OnboardingCopy } from '@/components/onboarding/onboarding-types';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';

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
                <LeadingIconHeading
                    tile
                    tile-tone="brand"
                    tile-size="sm"
                    content-class="gap-2"
                >
                    <template #icon>
                        <component :is="feature.icon" />
                    </template>
                    <h2 class="text-base font-semibold">
                        {{ copy[`${feature.key}_title`] }}
                    </h2>
                    <p class="text-base leading-7 text-muted-foreground">
                        {{ copy[`${feature.key}_description`] }}
                    </p>
                </LeadingIconHeading>
            </article>
        </div>
        <LeadingIconHeading
            tile
            tile-tone="success"
            tile-size="sm"
            class="rounded-2xl border border-status-success-border bg-status-success-surface p-4 text-status-success-text"
        >
            <template #icon>
                <ShieldCheck />
            </template>
            <p class="text-base leading-7">{{ copy.privacy }}</p>
        </LeadingIconHeading>
    </div>
</template>
