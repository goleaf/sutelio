import assert from 'node:assert/strict';
import test from 'node:test';

interface PageScrollModule {
    bindPageScrollToRouter: (
        router: FakePageScrollRouter,
        reset: () => void,
        schedule: (callback: () => void) => void,
    ) => () => void;
}

const pageScrollModule = (await import('./pageScroll.ts').catch(
    () => null,
)) as PageScrollModule | null;

function requirePageScrollModule(): PageScrollModule {
    assert.notEqual(
        pageScrollModule,
        null,
        'the shared page scroll module must exist',
    );

    return pageScrollModule as PageScrollModule;
}

class FakePageScrollRouter {
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

test('successful foreground visits reset the page after rendering', () => {
    const { bindPageScrollToRouter } = requirePageScrollModule();
    const router = new FakePageScrollRouter();
    let resets = 0;
    const teardown = bindPageScrollToRouter(
        router,
        () => {
            resets += 1;
        },
        (callback) => callback(),
    );

    router.emit('start', {
        visit: {
            id: 'onboarding-step',
            prefetch: false,
            preserveScroll: false,
        },
    });
    router.emit('success', { visitId: 'onboarding-step' });
    router.emit('finish', { visit: { id: 'onboarding-step' } });

    assert.equal(resets, 1);
    teardown();
});

test('preserved prefetched and cancelled visits keep their scroll position', () => {
    const { bindPageScrollToRouter } = requirePageScrollModule();
    const router = new FakePageScrollRouter();
    let resets = 0;
    const teardown = bindPageScrollToRouter(
        router,
        () => {
            resets += 1;
        },
        (callback) => callback(),
    );

    router.emit('start', {
        visit: {
            id: 'preserved',
            prefetch: false,
            preserveScroll: true,
        },
    });
    router.emit('success', { visitId: 'preserved' });
    router.emit('start', {
        visit: {
            id: 'prefetch',
            prefetch: true,
            preserveScroll: false,
        },
    });
    router.emit('success', { visitId: 'prefetch' });
    router.emit('start', {
        visit: {
            id: 'cancelled',
            prefetch: false,
            preserveScroll: false,
        },
    });
    router.emit('finish', { visit: { id: 'cancelled' } });
    router.emit('success', { visitId: 'cancelled' });

    assert.equal(resets, 0);
    teardown();
});
