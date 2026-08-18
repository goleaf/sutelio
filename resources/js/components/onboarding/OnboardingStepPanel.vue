<script setup lang="ts">
import { AlertTriangle, CircleAlert, RotateCcw } from '@lucide/vue';
import type { Component } from 'vue';
import { computed } from 'vue';
import OnboardingIcon from '@/components/onboarding/OnboardingIcon.vue';
import IconTile from '@/components/shared/IconTile.vue';
import StatusNotice from '@/components/shared/StatusNotice.vue';

const props = defineProps<{
    icon: Component;
    eyebrow: string;
    title: string;
    description: string;
    errors: Record<string, string>;
    errorLabels: Record<string, string>;
    validationTitle: string;
    validationDescription: string;
    recoveryMessage?: string | null;
    replayBadge?: string | null;
}>();

const validationErrors = computed(() =>
    Object.entries(props.errors).map(([field, message]) => ({
        field,
        label: props.errorLabels[field] ?? field,
        message,
    })),
);
</script>

<template>
    <section aria-labelledby="onboarding-step-heading">
        <header class="border-b border-border/70 px-5 py-6 sm:px-7 sm:py-7">
            <div class="flex flex-wrap items-center gap-2">
                <p
                    class="text-[0.9375rem] leading-5 font-semibold wrap-anywhere text-orange-700"
                >
                    {{ eyebrow }}
                </p>
                <span
                    v-if="replayBadge"
                    class="inline-flex items-center gap-1.5 rounded-full border border-status-information-border bg-status-information-surface px-2.5 py-1 text-[0.9375rem] leading-5 font-semibold wrap-anywhere text-status-information-text"
                >
                    <OnboardingIcon
                        :icon="RotateCcw"
                        size="sm"
                        class="text-current"
                    />
                    {{ replayBadge }}
                </span>
            </div>
            <div class="mt-3 flex min-w-0 items-center gap-3 sm:gap-4">
                <IconTile tone="brand" size="lg">
                    <component :is="icon" />
                </IconTile>
                <h1
                    id="onboarding-step-heading"
                    tabindex="-1"
                    class="max-w-3xl min-w-0 text-3xl leading-tight font-semibold tracking-tight wrap-anywhere outline-none sm:text-4xl"
                >
                    {{ title }}
                </h1>
            </div>
            <p class="mt-3 max-w-3xl text-base leading-7 text-muted-foreground">
                {{ description }}
            </p>
        </header>

        <div class="space-y-5 p-5 sm:p-7">
            <StatusNotice
                v-if="recoveryMessage"
                :message="recoveryMessage"
                status="information"
                class="text-base leading-6"
            />

            <div
                v-if="validationErrors.length"
                id="validation-summary"
                tabindex="-1"
                role="alert"
                class="rounded-2xl border border-destructive/25 bg-destructive/[0.06] p-4 outline-none focus-visible:ring-2 focus-visible:ring-destructive"
            >
                <p
                    class="flex items-center gap-2 font-semibold text-destructive"
                >
                    <OnboardingIcon
                        :icon="AlertTriangle"
                        class="text-current"
                    />
                    <span>{{ validationTitle }}</span>
                </p>
                <p class="mt-1 text-base leading-6 text-muted-foreground">
                    {{ validationDescription }}
                </p>
                <ul class="mt-3 space-y-1.5 text-[0.9375rem] leading-6">
                    <li v-for="error in validationErrors" :key="error.field">
                        <a
                            :href="`#${error.field}`"
                            class="inline-flex min-h-12 items-center font-medium wrap-anywhere text-destructive underline decoration-destructive/40 underline-offset-4 focus-visible:ring-2 focus-visible:ring-destructive focus-visible:outline-none pointer-coarse:min-h-13"
                        >
                            <OnboardingIcon
                                :icon="CircleAlert"
                                size="sm"
                                class="mr-2 text-current"
                            />
                            {{ error.label }}: {{ error.message }}
                        </a>
                    </li>
                </ul>
            </div>

            <slot />
        </div>
    </section>
</template>
