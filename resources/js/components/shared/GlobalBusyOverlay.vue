<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import { Spinner } from '@/components/ui/spinner';
import { useUi } from '@/composables/useUi';
import { globalBusy } from '@/lib/globalBusy';
import type { BusyKind } from '@/lib/globalBusy';

const { t } = useUi();
const stateKeys: Record<BusyKind, string> = {
    loading: 'common.states.loading',
    opening: 'common.states.opening',
    processing: 'common.states.processing',
    uploading: 'common.states.uploading',
};
const title = computed(() => t(stateKeys[globalBusy.kind.value]));
const progressStyle = computed(() =>
    globalBusy.progress.value === null
        ? undefined
        : { '--progress': globalBusy.progress.value / 100 },
);
let appRoot: HTMLElement | null = null;
let appRootWasInert = false;
let previousAriaBusy: string | null = null;
let focusOrigin: HTMLElement | null = null;
let stopWatching: (() => void) | null = null;

function preventEscape(event: KeyboardEvent): void {
    if (event.key !== 'Escape') {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
}

function lockApplication(): void {
    const activeElement = document.activeElement;
    focusOrigin =
        activeElement instanceof HTMLElement && appRoot?.contains(activeElement)
            ? activeElement
            : null;
    appRoot?.setAttribute('aria-busy', 'true');
    appRoot?.setAttribute('inert', '');
    document.body.classList.add('is-global-busy');
    window.addEventListener('keydown', preventEscape, true);
}

function unlockApplication(): void {
    if (appRootWasInert) {
        appRoot?.setAttribute('inert', '');
    } else {
        appRoot?.removeAttribute('inert');
    }

    if (previousAriaBusy === null) {
        appRoot?.removeAttribute('aria-busy');
    } else {
        appRoot?.setAttribute('aria-busy', previousAriaBusy);
    }

    document.body.classList.remove('is-global-busy');
    window.removeEventListener('keydown', preventEscape, true);

    const connectedFocusOrigin = focusOrigin?.isConnected ? focusOrigin : null;
    focusOrigin = null;

    if (
        connectedFocusOrigin !== null &&
        document.activeElement === document.body
    ) {
        connectedFocusOrigin.focus({ preventScroll: true });
    }
}

onMounted(() => {
    appRoot = document.getElementById('app');
    appRootWasInert = appRoot?.hasAttribute('inert') ?? false;
    previousAriaBusy = appRoot?.getAttribute('aria-busy') ?? null;
    stopWatching = watch(
        globalBusy.isBusy,
        (isBusy) => {
            if (isBusy) {
                lockApplication();

                return;
            }

            unlockApplication();
        },
        { immediate: true },
    );
});

onBeforeUnmount(() => {
    stopWatching?.();
    stopWatching = null;
    unlockApplication();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-[var(--motion-feedback)] ease-[var(--ease-standard)] motion-reduce:transition-none"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-[var(--motion-snap)] ease-[var(--ease-exit)] motion-reduce:transition-none"
            leave-to-class="opacity-0"
        >
            <div
                v-if="globalBusy.isBusy.value"
                data-slot="global-busy-overlay"
                class="fixed inset-0 z-[100] flex min-h-dvh items-center justify-center overflow-hidden bg-background/70 p-4 backdrop-blur-[1px]"
            >
                <div
                    data-slot="global-busy-progress"
                    class="fixed inset-x-0 top-0 z-[101] h-1 overflow-hidden bg-orange-100 forced-colors:bg-[Canvas]"
                    aria-hidden="true"
                >
                    <div
                        class="h-full w-full origin-left bg-orange-600 forced-colors:bg-[Highlight]"
                        :class="{
                            'ui-global-progress-indeterminate':
                                globalBusy.progress.value === null,
                            'scale-x-[var(--progress)] transition-transform duration-[var(--motion-feedback)] ease-[var(--ease-standard)] motion-reduce:transition-none':
                                globalBusy.progress.value !== null,
                        }"
                        :style="progressStyle"
                    />
                </div>

                <div
                    class="ui-global-busy-status flex max-w-sm flex-col items-center gap-3 rounded-panel border border-border/90 bg-card/95 px-6 py-5 text-center shadow-dialog sm:px-8 sm:py-6"
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    <span
                        class="grid size-14 place-items-center rounded-full bg-orange-100 text-orange-700 forced-colors:border forced-colors:border-[ButtonText] forced-colors:bg-[Canvas] forced-colors:text-[ButtonText]"
                        aria-hidden="true"
                    >
                        <Spinner class="size-8" aria-hidden="true" />
                    </span>
                    <span class="text-base font-semibold text-foreground">
                        {{ title }}
                    </span>
                    <span class="text-sm leading-5 text-muted-foreground">
                        {{ t('common.states.processing_hint') }}
                    </span>
                    <span
                        v-if="globalBusy.progress.value !== null"
                        class="sr-only"
                    >
                        {{ globalBusy.progress.value }}%
                    </span>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
