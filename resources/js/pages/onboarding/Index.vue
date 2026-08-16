<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    progress: {
        step: string;
        position: number;
        total: number;
        percent: number;
        is_replay: boolean;
    };
    state: Record<string, unknown>;
    copy: {
        eyebrow: string;
        title: string;
        description: string;
    };
}>();
</script>

<template>
    <AppLayout>
        <Head :title="copy.title" />

        <div class="min-h-full bg-muted/20 px-4 py-6 sm:p-8">
            <section
                class="mx-auto max-w-3xl rounded-panel border border-border/80 bg-card p-6 shadow-panel sm:p-10"
                aria-labelledby="onboarding-title"
            >
                <p
                    class="text-sm font-semibold tracking-wide text-primary uppercase"
                >
                    {{ copy.eyebrow }}
                </p>
                <h1
                    id="onboarding-title"
                    class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    {{ copy.title }}
                </h1>
                <p
                    class="mt-4 max-w-2xl text-base leading-7 text-muted-foreground"
                >
                    {{ copy.description }}
                </p>
            </section>
        </div>
    </AppLayout>
</template>
