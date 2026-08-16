<script setup lang="ts">
import {
    Check,
    FolderKanban,
    ListChecks,
    Settings2,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import type {
    OnboardingCopy,
    OnboardingPreferences,
    OnboardingProject,
    OnboardingTask,
    OnboardingWorkspace,
} from '@/components/onboarding/onboarding-types';
import { onboardingPluralForm } from '@/components/onboarding/onboarding-types';
import { resolveIntlLocale } from '@/lib/formatters';

const props = defineProps<{
    copy: OnboardingCopy['results'];
    preferences: OnboardingPreferences;
    workspace?: OnboardingWorkspace;
    project?: OnboardingProject;
    task?: OnboardingTask;
}>();

const rows = computed(
    () =>
        [
            {
                key: 'preferences',
                icon: Settings2,
                value: `${props.preferences.language.toUpperCase()} · ${props.preferences.timezone}`,
            },
            {
                key: 'workspace',
                icon: UsersRound,
                value: props.workspace?.name ?? '—',
            },
            {
                key: 'project',
                icon: FolderKanban,
                value: props.project?.name ?? '—',
            },
            { key: 'task', icon: ListChecks, value: props.task?.title ?? '—' },
        ] as const,
);
const readyCount = computed(
    () => rows.value.filter((row) => row.value !== '—').length,
);
const countMessage = computed(() => {
    const form = onboardingPluralForm(
        readyCount.value,
        resolveIntlLocale(props.preferences.language),
    );
    const template =
        props.copy[`entity_count_${form}`] ?? props.copy.entity_count_other;

    return template.replace(':count', String(readyCount.value));
});
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.55fr)]">
        <section
            class="rounded-2xl border border-border/80 bg-muted/20 p-4 sm:p-5"
        >
            <div class="flex items-center gap-3">
                <span
                    class="flex size-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300"
                >
                    <Check class="size-5" aria-hidden="true" />
                </span>
                <div>
                    <h2 class="font-semibold">{{ copy.ready_title }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ countMessage }}
                    </p>
                </div>
            </div>
            <dl class="mt-5 divide-y divide-border/70">
                <div
                    v-for="row in rows"
                    :key="row.key"
                    class="flex items-start gap-3 py-3 first:pt-0 last:pb-0"
                >
                    <component
                        :is="row.icon"
                        class="mt-0.5 size-4 shrink-0 text-orange-600 dark:text-orange-300"
                        aria-hidden="true"
                    />
                    <div class="min-w-0">
                        <dt
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ copy[row.key] }}
                        </dt>
                        <dd class="mt-1 text-sm font-medium break-words">
                            {{ row.value }}
                        </dd>
                    </div>
                </div>
            </dl>
        </section>
        <aside
            class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.07] p-5 text-blue-950 dark:text-blue-100"
        >
            <h2 class="font-semibold">{{ copy.next_title }}</h2>
            <p class="mt-2 text-sm leading-6">{{ copy.next_description }}</p>
        </aside>
    </div>
</template>
