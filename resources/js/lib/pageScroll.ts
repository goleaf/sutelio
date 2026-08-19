import type { GlobalEvent } from '@inertiajs/core';

type PageScrollScheduler = (callback: () => void) => void;

interface PageScrollRouter {
    on(
        type: 'start',
        callback: (event: GlobalEvent<'start'>) => void,
    ): VoidFunction;
    on(
        type: 'success',
        callback: (event: GlobalEvent<'success'>) => void,
    ): VoidFunction;
    on(
        type: 'finish',
        callback: (event: GlobalEvent<'finish'>) => void,
    ): VoidFunction;
}

export function resetPageScroll(): void {
    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });

    for (const region of document.querySelectorAll<HTMLElement>(
        '[scroll-region]',
    )) {
        region.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    }
}

export function bindPageScrollToRouter(
    router: PageScrollRouter,
    reset: () => void = resetPageScroll,
    schedule: PageScrollScheduler = (callback) => {
        window.requestAnimationFrame(callback);
    },
): VoidFunction {
    const resetVisits = new Set<string>();
    const removeListeners = [
        router.on('start', (event) => {
            const { visit } = event.detail;

            if (!visit.prefetch && visit.preserveScroll === false) {
                resetVisits.add(visit.id);
            } else {
                resetVisits.delete(visit.id);
            }
        }),
        router.on('success', (event) => {
            const { visitId } = event.detail;

            if (!visitId || !resetVisits.delete(visitId)) {
                return;
            }

            schedule(reset);
        }),
        router.on('finish', (event) => {
            resetVisits.delete(event.detail.visit.id);
        }),
    ];

    return () => {
        for (const removeListener of removeListeners) {
            removeListener();
        }

        resetVisits.clear();
    };
}
