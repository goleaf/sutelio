import assert from 'node:assert/strict';
import test from 'node:test';

type BusyKind = 'loading' | 'opening' | 'processing' | 'uploading';

interface BusyOperationHandle {
    finish: () => void;
    setKind: (kind: BusyKind) => void;
    setProgress: (percentage?: number | null) => void;
}

interface BusyController {
    begin: (kind: BusyKind) => BusyOperationHandle;
    isBusy: { value: boolean };
    kind: { value: BusyKind };
    progress: { value: number | null };
}

interface GlobalBusyModule {
    bindGlobalBusyToRouter: (
        router: unknown,
        controller: BusyController,
    ) => () => void;
    createGlobalBusyController: () => BusyController;
    createGlobalBusyHttpClient: (
        client: {
            request: (config: RequestConfig) => Promise<Response>;
        },
        controller: BusyController,
    ) => {
        request: (config: RequestConfig) => Promise<Response>;
    };
    shouldTrackStandaloneRequest: (config: RequestConfig) => boolean;
}

interface ProgressEvent {
    loaded: number;
    percentage?: number;
    progress: number | undefined;
    total: number | undefined;
}

interface RequestConfig {
    data?: unknown;
    headers?: Record<string, unknown>;
    method: string;
    onUploadProgress?: (event: ProgressEvent) => void;
    url: string;
}

interface Response {
    data: string;
    headers: Record<string, string>;
    status: number;
}

const globalBusyModule = (await import('./globalBusy.ts').catch(
    () => null,
)) as GlobalBusyModule | null;

function requireGlobalBusyModule(): GlobalBusyModule {
    assert.notEqual(
        globalBusyModule,
        null,
        'the shared global busy module must exist',
    );

    return globalBusyModule as GlobalBusyModule;
}

class FakeBusyRouter {
    private readonly listeners = new Map<
        string,
        Set<(event: { detail: Record<string, unknown> }) => void>
    >();

    emit(type: string, detail: Record<string, unknown>): void {
        for (const listener of this.listeners.get(type) ?? []) {
            listener({ detail });
        }
    }

    on(
        type: string,
        listener: (event: { detail: Record<string, unknown> }) => void,
    ): () => void {
        const listeners = this.listeners.get(type) ?? new Set();
        listeners.add(listener);
        this.listeners.set(type, listeners);

        return () => listeners.delete(listener);
    }
}

test('global busy operations remain active until every handle finishes', () => {
    const { createGlobalBusyController } = requireGlobalBusyModule();
    const controller = createGlobalBusyController();
    const navigation = controller.begin('opening');
    const mutation = controller.begin('processing');

    assert.equal(controller.isBusy.value, true);
    assert.equal(controller.kind.value, 'processing');

    navigation.finish();
    assert.equal(controller.isBusy.value, true);

    navigation.finish();
    assert.equal(controller.isBusy.value, true);

    mutation.finish();
    assert.equal(controller.isBusy.value, false);
    assert.equal(controller.progress.value, null);
});

test('the latest operation owns contextual kind and clamped progress', () => {
    const { createGlobalBusyController } = requireGlobalBusyModule();
    const controller = createGlobalBusyController();
    const navigation = controller.begin('opening');
    const upload = controller.begin('processing');

    upload.setKind('uploading');
    upload.setProgress(125);
    assert.equal(controller.kind.value, 'uploading');
    assert.equal(controller.progress.value, 100);

    upload.setProgress(-20);
    assert.equal(controller.progress.value, 0);

    upload.setProgress(Number.NaN);
    assert.equal(controller.progress.value, null);

    upload.finish();
    assert.equal(controller.kind.value, 'opening');
    assert.equal(controller.progress.value, null);

    navigation.finish();
});

test('router binding tracks foreground visits by id and ignores background work', () => {
    const { bindGlobalBusyToRouter, createGlobalBusyController } =
        requireGlobalBusyModule();
    const controller = createGlobalBusyController();
    const router = new FakeBusyRouter();
    const teardown = bindGlobalBusyToRouter(router, controller);

    router.emit('start', {
        visit: {
            id: 'prefetch',
            method: 'get',
            prefetch: true,
            showProgress: true,
        },
    });
    router.emit('start', {
        visit: {
            id: 'background',
            method: 'get',
            prefetch: false,
            showProgress: false,
        },
    });
    assert.equal(controller.isBusy.value, false);

    router.emit('start', {
        visit: {
            id: 'navigation',
            method: 'get',
            prefetch: false,
            showProgress: true,
        },
    });
    router.emit('start', {
        visit: {
            id: 'mutation',
            method: 'put',
            prefetch: false,
            showProgress: true,
        },
    });
    assert.equal(controller.kind.value, 'processing');

    router.emit('progress', {
        progress: {
            loaded: 42,
            percentage: 42,
            progress: 0.42,
            total: 100,
        },
    });
    assert.equal(controller.kind.value, 'uploading');
    assert.equal(controller.progress.value, 42);

    router.emit('finish', {
        visit: {
            id: 'navigation',
        },
    });
    assert.equal(controller.isBusy.value, true);

    router.emit('finish', {
        visit: {
            id: 'mutation',
        },
    });
    assert.equal(controller.isBusy.value, false);

    router.emit('start', {
        visit: {
            id: 'teardown',
            method: 'delete',
            prefetch: false,
            showProgress: true,
        },
    });
    teardown();
    assert.equal(controller.isBusy.value, false);
});

test('standalone request tracking excludes Inertia and Precognition headers case insensitively', () => {
    const { shouldTrackStandaloneRequest } = requireGlobalBusyModule();

    assert.equal(
        shouldTrackStandaloneRequest({
            headers: { 'X-Inertia': true },
            method: 'get',
            url: '/dashboard',
        }),
        false,
    );
    assert.equal(
        shouldTrackStandaloneRequest({
            headers: { precognition: 'true' },
            method: 'post',
            url: '/validate',
        }),
        false,
    );
    assert.equal(
        shouldTrackStandaloneRequest({
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            method: 'patch',
            url: '/tasks/1',
        }),
        true,
    );
});

test('standalone client forwards upload progress and releases on success', async () => {
    const { createGlobalBusyController, createGlobalBusyHttpClient } =
        requireGlobalBusyModule();
    const controller = createGlobalBusyController();
    let forwardedProgress = 0;
    let wasBusyDuringRequest = false;
    const baseClient = {
        async request(config: RequestConfig): Promise<Response> {
            wasBusyDuringRequest = controller.isBusy.value;
            config.onUploadProgress?.({
                loaded: 67,
                percentage: 67,
                progress: 0.67,
                total: 100,
            });

            return { data: '{}', headers: {}, status: 200 };
        },
    };
    const client = createGlobalBusyHttpClient(baseClient, controller);
    const request = client.request({
        method: 'post',
        onUploadProgress: (event) => {
            forwardedProgress = event.percentage ?? 0;
        },
        url: '/attachments',
    });

    assert.equal(controller.isBusy.value, true);
    assert.equal(wasBusyDuringRequest, true);
    assert.equal(forwardedProgress, 67);
    assert.equal(controller.kind.value, 'uploading');
    assert.equal(controller.progress.value, 67);

    await request;

    assert.equal(controller.isBusy.value, false);
});

test('standalone client releases the operation after a rejected request', async () => {
    const { createGlobalBusyController, createGlobalBusyHttpClient } =
        requireGlobalBusyModule();
    const controller = createGlobalBusyController();
    const expectedError = new Error('network unavailable');
    const client = createGlobalBusyHttpClient(
        {
            request: async () => Promise.reject(expectedError),
        },
        controller,
    );

    await assert.rejects(
        client.request({ method: 'delete', url: '/tasks/1' }),
        expectedError,
    );
    assert.equal(controller.isBusy.value, false);
});
