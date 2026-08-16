<script setup lang="ts">
import { Check, Circle, Route } from '@lucide/vue';
import { computed, ref } from 'vue';
import type {
    OnboardingCopy,
    OnboardingProgress,
} from '@/components/onboarding/onboarding-types';
import { orderedOnboardingSteps } from '@/components/onboarding/onboarding-types';
import WorkspaceConfirmDialog from '@/components/shared/WorkspaceConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    copy: OnboardingCopy;
    progress: OnboardingProgress;
    saveStatus: 'idle' | 'saving' | 'saved' | 'error' | 'resumed';
    canBack: boolean;
    primaryLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    back: [];
    skip: [];
}>();

const skipOpen = ref(false);
const stepLabel = computed(() =>
    props.copy.status.step_count
        .replace(':current', String(props.progress.position))
        .replace(':total', String(props.progress.total)),
);
const percentLabel = computed(() =>
    props.copy.status.percent_complete.replace(
        ':percent',
        String(props.progress.percent),
    ),
);
const statusMessage = computed(() => props.copy.status[props.saveStatus]);
</script>

<template>
    <div class="relative">
        <header
            class="sticky top-0 z-20 -mx-4 mb-4 border-b border-border/80 bg-background/95 px-4 py-3 backdrop-blur-sm lg:hidden"
        >
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p
                        class="text-xs font-semibold text-orange-700 dark:text-orange-300"
                    >
                        {{ stepLabel }}
                    </p>
                    <p class="truncate text-sm font-semibold">
                        {{ copy.steps[progress.step].title }}
                    </p>
                </div>
                <span
                    class="shrink-0 text-xs font-medium text-muted-foreground tabular-nums"
                >
                    {{ percentLabel }}
                </span>
            </div>
            <div
                class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted forced-colors:border forced-colors:border-[CanvasText]"
                role="progressbar"
                :aria-label="percentLabel"
                aria-valuemin="0"
                aria-valuemax="100"
                :aria-valuenow="progress.percent"
            >
                <div
                    class="h-full rounded-full bg-orange-500 transition-[width] duration-300 motion-reduce:transition-none forced-colors:bg-[Highlight]"
                    :style="{ width: `${progress.percent}%` }"
                />
            </div>
        </header>

        <div
            class="grid min-w-0 gap-5 xl:grid-cols-[minmax(15rem,0.34fr)_minmax(0,1fr)] xl:gap-7"
        >
            <aside
                class="hidden rounded-panel border border-border/80 bg-card p-5 shadow-panel xl:block"
                :aria-label="copy.meta.eyebrow"
            >
                <div
                    class="flex items-center gap-3 border-b border-border/70 pb-5"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-700 ring-1 ring-orange-500/15 dark:text-orange-300"
                    >
                        <Route class="size-5" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                        >
                            {{ copy.meta.eyebrow }}
                        </p>
                        <p class="mt-1 text-sm font-semibold">
                            {{ percentLabel }}
                        </p>
                    </div>
                </div>

                <ol class="mt-5 space-y-1.5">
                    <li
                        v-for="(step, index) in orderedOnboardingSteps"
                        :key="step"
                        class="relative flex gap-3 rounded-xl px-2.5 py-2.5"
                        :class="
                            step === progress.step
                                ? 'bg-orange-500/[0.07] text-foreground ring-1 ring-orange-500/15'
                                : 'text-muted-foreground'
                        "
                        :aria-current="
                            step === progress.step ? 'step' : undefined
                        "
                    >
                        <span
                            class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full border forced-colors:border-[CanvasText]"
                            :class="
                                index + 1 < progress.position
                                    ? 'border-emerald-600 bg-emerald-600 text-white dark:border-emerald-400 dark:bg-emerald-400 dark:text-slate-950'
                                    : step === progress.step
                                      ? 'border-orange-500 bg-orange-500 text-white'
                                      : 'border-border bg-background'
                            "
                        >
                            <Check
                                v-if="index + 1 < progress.position"
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            <Circle
                                v-else
                                class="size-2.5 fill-current"
                                aria-hidden="true"
                            />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold">
                                {{ copy.steps[step].title }}
                            </span>
                            <span
                                v-if="step === progress.step"
                                class="mt-0.5 block text-xs leading-5 text-muted-foreground"
                            >
                                {{ copy.steps[step].description }}
                            </span>
                        </span>
                    </li>
                </ol>
            </aside>

            <div class="min-w-0 space-y-3">
                <div
                    class="rounded-panel border border-border/80 bg-card shadow-panel"
                >
                    <slot />
                </div>

                <p
                    class="min-h-5 px-2 text-sm text-muted-foreground"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    {{ statusMessage }}
                </p>
            </div>
        </div>

        <div
            class="sticky bottom-0 z-20 -mx-4 mt-5 border-t border-border/80 bg-background/95 px-4 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] backdrop-blur-sm sm:-mx-6 sm:px-6 xl:static xl:mx-0 xl:border-0 xl:bg-transparent xl:px-0 xl:pt-4 xl:pb-0 xl:backdrop-blur-none"
        >
            <div
                class="mx-auto flex max-w-app flex-wrap items-center gap-2 xl:justify-end"
            >
                <Button
                    type="button"
                    variant="ghost"
                    size="lg"
                    class="min-h-11"
                    :disabled="processing"
                    @click="skipOpen = true"
                >
                    {{
                        progress.is_replay
                            ? copy.actions.exit_replay
                            : copy.actions.skip
                    }}
                </Button>
                <span class="min-w-0 flex-1" aria-hidden="true" />
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-11"
                    :disabled="!canBack || processing"
                    @click="emit('back')"
                >
                    {{ copy.actions.back }}
                </Button>
                <Button
                    type="submit"
                    form="onboarding-step-form"
                    size="lg"
                    class="min-h-11 min-w-32"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    {{ primaryLabel }}
                </Button>
            </div>
        </div>

        <WorkspaceConfirmDialog
            v-model:open="skipOpen"
            :title="copy.actions.skip_title"
            :description="copy.actions.skip_description"
            :confirm-label="copy.actions.skip_confirm"
            :cancel-label="copy.actions.cancel"
            :processing="processing"
            :destructive="false"
            @confirm="emit('skip')"
        />
    </div>
</template>
