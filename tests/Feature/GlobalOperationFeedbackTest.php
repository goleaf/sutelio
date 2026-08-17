<?php

test('the application bootstrap installs one universal foreground operation contract', function () {
    $source = File::get(resource_path('js/app.ts'));

    expect(File::exists(resource_path('js/lib/globalBusy.ts')))
        ->toBeTrue()
        ->and(File::exists(resource_path('js/components/shared/GlobalBusyOverlay.vue')))
        ->toBeTrue()
        ->and($source)
        ->toContain("import GlobalBusyOverlay from '@/components/shared/GlobalBusyOverlay.vue'")
        ->toContain('createGlobalBusyHttpClient')
        ->toContain('bindGlobalBusyToRouter')
        ->toContain('h(GlobalBusyOverlay)')
        ->toContain('progress: false')
        ->not->toContain("progress: {\n        color: '#FF6038'");
});

test('the shared overlay is centered, seventy percent opaque, blocking, and non dismissible', function () {
    $source = File::get(resource_path('js/components/shared/GlobalBusyOverlay.vue'));

    expect($source)
        ->toContain('<Teleport to="body">')
        ->toContain('onMounted(() => {')
        ->toContain('data-slot="global-busy-overlay"')
        ->toContain('fixed inset-0')
        ->toContain('bg-background/70')
        ->toContain('items-center justify-center')
        ->toContain('data-slot="global-busy-progress"')
        ->toContain('role="status"')
        ->toContain('aria-live="polite"')
        ->toContain('aria-atomic="true"')
        ->toContain("setAttribute('aria-busy', 'true')")
        ->toContain("setAttribute('inert', '')")
        ->toContain("classList.add('is-global-busy')")
        ->toContain("window.addEventListener('keydown', preventEscape, true)")
        ->toContain("event.key !== 'Escape'")
        ->toContain('focusOrigin?.isConnected')
        ->toContain('document.activeElement === document.body')
        ->toContain('focus({ preventScroll: true })')
        ->not->toMatch('/<button|DialogClose|common\.actions\.cancel/');
});

test('the canonical stylesheet owns progress, reduced motion, forced colors, and scroll lock behavior', function () {
    $source = File::get(resource_path('css/app.css'));

    expect($source)
        ->toContain('@keyframes ui-global-progress')
        ->toContain('.ui-global-progress-indeterminate')
        ->toContain('body.is-global-busy')
        ->toContain('overflow: hidden')
        ->toContain('@media (forced-colors: active)')
        ->toContain('.ui-global-busy-status')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain('animation: none')
        ->toContain('inline-size: 65%');
});

test('global operation feedback uses semantic copy in every supported locale', function () {
    foreach (['en', 'lt', 'ru'] as $locale) {
        $copy = trans('ui.common.states', locale: $locale);

        expect($copy, $locale)
            ->toHaveKeys([
                'loading',
                'opening',
                'processing',
                'processing_hint',
                'uploading',
            ])
            ->and($copy['opening'])
            ->not->toBe('ui.common.states.opening')
            ->and($copy['processing_hint'])
            ->not->toBe('ui.common.states.processing_hint');
    }
});

test('network interruption feedback is global localized and prevents uncaught inertia failures', function () {
    $application = File::get(resource_path('js/app.ts'));
    $componentPath = resource_path('js/components/shared/NetworkStatusNotifier.vue');

    expect(File::exists($componentPath))->toBeTrue();

    $component = File::get($componentPath);

    expect($application)
        ->toContain("import NetworkStatusNotifier from '@/components/shared/NetworkStatusNotifier.vue'")
        ->toContain('h(NetworkStatusNotifier)')
        ->and($component)
        ->toContain("router.on('networkError'")
        ->toContain('event.preventDefault()')
        ->toContain("window.addEventListener('offline'")
        ->toContain("window.addEventListener('online'")
        ->toContain("window.removeEventListener('offline'")
        ->toContain("window.removeEventListener('online'")
        ->toContain("t('common.toast.connection_offline')")
        ->toContain("t('common.toast.connection_restored')")
        ->toContain("t('common.toast.network_error')");

    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(trans('ui.common.toast', locale: $locale), $locale)
            ->toHaveKeys([
                'connection_offline',
                'connection_restored',
                'network_error',
            ]);
    }
});
