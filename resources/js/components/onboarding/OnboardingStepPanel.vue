<script setup lang="ts">
import { AlertCircle } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
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
                    class="text-xs font-semibold tracking-[0.14em] text-orange-700 uppercase"
                >
                    {{ eyebrow }}
                </p>
                <span
                    v-if="replayBadge"
                    class="rounded-full border border-blue-500/20 bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-700"
                >
                    {{ replayBadge }}
                </span>
            </div>
            <h1
                id="onboarding-step-heading"
                tabindex="-1"
                class="mt-3 max-w-3xl text-3xl font-semibold tracking-tight outline-none sm:text-4xl"
            >
                {{ title }}
            </h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-muted-foreground">
                {{ description }}
            </p>
        </header>

        <div class="space-y-5 p-5 sm:p-7">
            <div
                v-if="recoveryMessage"
                class="flex gap-3 rounded-2xl border border-blue-500/20 bg-blue-500/[0.07] p-4 text-sm leading-6 text-blue-950"
            >
                <AlertCircle
                    class="mt-0.5 size-5 shrink-0"
                    aria-hidden="true"
                />
                <p>{{ recoveryMessage }}</p>
            </div>

            <div
                v-if="validationErrors.length"
                id="validation-summary"
                tabindex="-1"
                role="alert"
                class="rounded-2xl border border-destructive/25 bg-destructive/[0.06] p-4 outline-none focus-visible:ring-2 focus-visible:ring-destructive"
            >
                <p class="font-semibold text-destructive">
                    {{ validationTitle }}
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ validationDescription }}
                </p>
                <ul class="mt-3 space-y-1.5 text-sm">
                    <li v-for="error in validationErrors" :key="error.field">
                        <a
                            :href="`#${error.field}`"
                            class="font-medium text-destructive underline decoration-destructive/40 underline-offset-4 focus-visible:ring-2 focus-visible:ring-destructive focus-visible:outline-none"
                        >
                            {{ error.label }}: {{ error.message }}
                        </a>
                    </li>
                </ul>
            </div>

            <slot />
        </div>
    </section>
</template>
