import type {
    GlobalEvent,
    HttpClient,
    HttpRequestConfig,
    HttpResponse,
} from '@inertiajs/core';
import { computed, reactive, ref } from 'vue';
import type { ComputedRef } from 'vue';

export type BusyKind = 'loading' | 'opening' | 'processing' | 'uploading';

export interface BusyOperationHandle {
    finish: () => void;
    setKind: (kind: BusyKind) => void;
    setProgress: (percentage?: number | null) => void;
}

export interface GlobalBusyController {
    begin: (kind: BusyKind) => BusyOperationHandle;
    isBusy: ComputedRef<boolean>;
    kind: ComputedRef<BusyKind>;
    progress: ComputedRef<number | null>;
}

interface BusyOperation {
    id: symbol;
    kind: BusyKind;
    progress: number | null;
}

interface BusyRouter {
    on(
        type: 'start',
        callback: (event: GlobalEvent<'start'>) => void,
    ): VoidFunction;
    on(
        type: 'progress',
        callback: (event: GlobalEvent<'progress'>) => void,
    ): VoidFunction;
    on(
        type: 'finish',
        callback: (event: GlobalEvent<'finish'>) => void,
    ): VoidFunction;
}

function normalizeProgress(percentage?: number | null): number | null {
    if (typeof percentage !== 'number' || !Number.isFinite(percentage)) {
        return null;
    }

    return Math.min(100, Math.max(0, percentage));
}

export function createGlobalBusyController(): GlobalBusyController {
    const operations = ref<BusyOperation[]>([]);
    const currentOperation = computed(() => operations.value.at(-1) ?? null);

    return {
        begin(kind: BusyKind): BusyOperationHandle {
            const operation = reactive<BusyOperation>({
                id: Symbol('global-busy-operation'),
                kind,
                progress: null,
            });
            let finished = false;

            operations.value.push(operation);

            return {
                finish(): void {
                    if (finished) {
                        return;
                    }

                    finished = true;
                    operations.value = operations.value.filter(
                        ({ id }) => id !== operation.id,
                    );
                },
                setKind(nextKind: BusyKind): void {
                    if (!finished) {
                        operation.kind = nextKind;
                    }
                },
                setProgress(percentage?: number | null): void {
                    if (!finished) {
                        operation.progress = normalizeProgress(percentage);
                    }
                },
            };
        },
        isBusy: computed(() => operations.value.length > 0),
        kind: computed(() => currentOperation.value?.kind ?? 'processing'),
        progress: computed(() => currentOperation.value?.progress ?? null),
    };
}

function shouldTrackVisit(visit: {
    prefetch: boolean;
    showProgress: boolean;
}): boolean {
    return visit.showProgress && !visit.prefetch;
}

export function bindGlobalBusyToRouter(
    router: BusyRouter,
    controller: GlobalBusyController,
): VoidFunction {
    const activeVisits = new Map<string, BusyOperationHandle>();
    const removeListeners = [
        router.on('start', (event) => {
            const { visit } = event.detail;

            if (!shouldTrackVisit(visit)) {
                return;
            }

            activeVisits.get(visit.id)?.finish();
            activeVisits.set(
                visit.id,
                controller.begin(
                    visit.method === 'get' ? 'opening' : 'processing',
                ),
            );
        }),
        router.on('progress', (event) => {
            const operation = Array.from(activeVisits.values()).at(-1);

            if (!operation || !event.detail.progress) {
                return;
            }

            operation.setKind('uploading');
            operation.setProgress(event.detail.progress.percentage);
        }),
        router.on('finish', (event) => {
            const operation = activeVisits.get(event.detail.visit.id);

            operation?.finish();
            activeVisits.delete(event.detail.visit.id);
        }),
    ];

    return () => {
        for (const removeListener of removeListeners) {
            removeListener();
        }

        for (const operation of activeVisits.values()) {
            operation.finish();
        }

        activeVisits.clear();
    };
}

function hasEnabledHeader(
    headers: HttpRequestConfig['headers'],
    expectedName: string,
): boolean {
    const entry = Object.entries(headers ?? {}).find(
        ([name]) => name.toLowerCase() === expectedName.toLowerCase(),
    );

    if (!entry) {
        return false;
    }

    const value = entry[1];

    if (value === false || value === null || value === undefined) {
        return false;
    }

    return !['', '0', 'false'].includes(String(value).trim().toLowerCase());
}

export function shouldTrackStandaloneRequest(
    config: HttpRequestConfig,
): boolean {
    return (
        !hasEnabledHeader(config.headers, 'X-Inertia') &&
        !hasEnabledHeader(config.headers, 'Precognition')
    );
}

export function createGlobalBusyHttpClient(
    client: HttpClient,
    controller: GlobalBusyController,
): HttpClient {
    return {
        async request(config: HttpRequestConfig): Promise<HttpResponse> {
            if (!shouldTrackStandaloneRequest(config)) {
                return client.request(config);
            }

            const operation = controller.begin(
                config.method === 'get' ? 'loading' : 'processing',
            );
            const onUploadProgress = config.onUploadProgress;

            try {
                return await client.request({
                    ...config,
                    onUploadProgress: (progress) => {
                        operation.setKind('uploading');
                        operation.setProgress(progress.percentage);
                        onUploadProgress?.(progress);
                    },
                });
            } finally {
                operation.finish();
            }
        },
    };
}

export const globalBusy = createGlobalBusyController();
