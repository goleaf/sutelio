<script setup lang="ts">
import { Check, Circle, Route } from '@lucide/vue';
import { computed } from 'vue';
import type {
    OnboardingCopy,
    OnboardingProgress,
} from '@/components/onboarding/onboarding-types';
import { orderedOnboardingSteps } from '@/components/onboarding/onboarding-types';
import IconTile from '@/components/shared/IconTile.vue';
import StatusNotice from '@/components/shared/StatusNotice.vue';
import type { StatusNoticeStatus } from '@/components/shared/StatusNotice.vue';
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
    exitReplay: [];
}>();

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
const saveNoticeStatus = computed<StatusNoticeStatus>(() => {
    if (props.saveStatus === 'saving') {
        return 'loading';
    }

    if (props.saveStatus === 'saved') {
        return 'success';
    }

    if (props.saveStatus === 'error') {
        return 'error';
    }

    return 'information';
});
const progressScale = computed(() =>
    Math.min(1, Math.max(0, props.progress.percent / 100)),
);
</script>

<template>
    <div class="relative pb-40 sm:pb-24 lg:pb-0">
        <header
            data-slot="onboarding-mobile-progress"
            class="sticky top-0 z-20 -mx-4 mb-4 border-b border-border/80 bg-background/95 px-4 py-3 backdrop-blur-sm xl:hidden"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p
                        class="text-[0.9375rem] leading-5 font-semibold text-orange-700"
                    >
                        {{ stepLabel }}
                    </p>
                    <p class="text-base leading-6 font-semibold wrap-anywhere">
                        {{ copy.steps[progress.step].title }}
                    </p>
                </div>
                <span
                    class="max-w-32 shrink-0 text-right text-[0.9375rem] leading-5 font-medium wrap-anywhere text-muted-foreground tabular-nums"
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
                    class="h-full w-full origin-left scale-x-[var(--progress)] rounded-full bg-orange-500 transition-transform duration-[var(--motion-state)] ease-[var(--ease-standard)] motion-reduce:transition-none forced-colors:bg-[Highlight]"
                    :style="{ '--progress': progressScale }"
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
                    <IconTile tone="brand">
                        <Route />
                    </IconTile>
                    <div class="min-w-0">
                        <p
                            class="text-[0.9375rem] leading-5 font-semibold wrap-anywhere text-muted-foreground"
                        >
                            {{ copy.meta.eyebrow }}
                        </p>
                        <p class="mt-1 text-base font-semibold wrap-anywhere">
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
                                    ? 'border-status-success-icon bg-status-success-icon text-white'
                                    : step === progress.step
                                      ? 'border-orange-600 bg-orange-600 text-white'
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
                            <span
                                class="block text-base font-semibold wrap-anywhere"
                            >
                                {{ copy.steps[step].title }}
                            </span>
                            <span
                                v-if="step === progress.step"
                                class="mt-1 block text-[0.9375rem] leading-6 wrap-anywhere text-foreground/75"
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

                <StatusNotice
                    :message="statusMessage"
                    :status="saveNoticeStatus"
                    class="text-[0.9375rem] leading-6"
                />
            </div>
        </div>

        <div
            data-slot="onboarding-actions"
            class="fixed inset-x-0 bottom-0 z-30 border-t border-border/80 bg-background/95 px-4 pt-3 pb-[max(1rem,var(--safe-area-inset-bottom))] backdrop-blur-sm sm:px-6 lg:static lg:mt-5 lg:border-0 lg:bg-transparent lg:px-0 lg:pt-4 lg:pb-0 lg:backdrop-blur-none"
        >
            <div
                class="mx-auto grid max-w-app grid-cols-1 items-center gap-2 min-[30rem]:grid-cols-2 sm:flex lg:justify-end"
            >
                <Button
                    v-if="progress.is_replay"
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-12 w-full whitespace-normal min-[30rem]:col-span-2 sm:col-auto sm:w-auto pointer-coarse:min-h-13"
                    :disabled="processing"
                    @click="emit('exitReplay')"
                >
                    {{ copy.actions.exit_replay }}
                </Button>
                <span
                    class="hidden min-w-0 flex-1 sm:block"
                    aria-hidden="true"
                />
                <Button
                    v-if="canBack"
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-12 w-full whitespace-normal sm:w-auto pointer-coarse:min-h-13"
                    :disabled="processing"
                    @click="emit('back')"
                >
                    {{ copy.actions.back }}
                </Button>
                <Button
                    type="submit"
                    form="onboarding-step-form"
                    size="lg"
                    class="min-h-12 w-full min-w-32 whitespace-normal sm:w-auto pointer-coarse:min-h-13"
                    :class="{ 'min-[30rem]:col-span-2': !canBack }"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    {{ primaryLabel }}
                </Button>
            </div>
        </div>
    </div>
</template>
