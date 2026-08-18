<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('the shared status notice owns compact lifecycle iconography and semantics', function () {
    $path = resource_path('js/components/shared/StatusNotice.vue');

    expect(File::exists($path))->toBeTrue();

    if (! File::exists($path)) {
        return;
    }

    expect(File::get($path))
        ->toContain('type StatusNoticeStatus =')
        ->toContain("'information'", "'loading'", "'success'", "'error'")
        ->toContain("import IconTile from '@/components/shared/IconTile.vue'")
        ->toContain('Info', 'LoaderCircle', 'BadgeCheck', 'CircleAlert')
        ->toContain('data-slot="status-notice"')
        ->toContain(':data-status="props.status"')
        ->toContain("props.status === 'error' ? 'alert' : 'status'")
        ->toContain("props.status === 'loading' ? 'true' : undefined")
        ->toContain(':aria-live="ariaLive"')
        ->toContain('aria-atomic="true"')
        ->toContain('bg-linear-to-br')
        ->toContain('from-status-information-surface')
        ->toContain('from-orange-50/90')
        ->toContain('from-status-success-surface')
        ->toContain('from-status-destructive-surface')
        ->toContain('motion-safe:ui-status-pop')
        ->toContain('motion-reduce:animate-none')
        ->toContain('forced-colors:border-[CanvasText]')
        ->toContain('wrap-anywhere');
});

test('equivalent orphaned operation messages compose the shared status notice', function () {
    $consumers = [
        'onboarding shell' => ['onboarding/OnboardingShell.vue', 1],
        'onboarding preferences' => ['onboarding/PreferencesStep.vue', 2],
        'settings preferences' => ['../pages/settings/Preferences.vue', 2],
        'onboarding checklist' => ['onboarding/OnboardingChecklist.vue', 1],
    ];

    foreach ($consumers as $name => [$path, $minimumUses]) {
        $source = str_starts_with($path, '../pages/')
            ? File::get(resource_path('js/'.substr($path, 3)))
            : File::get(resource_path("js/components/{$path}"));

        expect($source, $name)
            ->toContain("import StatusNotice from '@/components/shared/StatusNotice.vue'");
        expect(substr_count($source, '<StatusNotice'), $name)
            ->toBeGreaterThanOrEqual($minimumUses);
    }

    expect(File::get(resource_path('js/components/onboarding/OnboardingShell.vue')))
        ->toContain('const saveNoticeStatus = computed<StatusNoticeStatus>')
        ->toContain(':status="saveNoticeStatus"')
        ->not->toContain('class="min-h-5 px-2 text-sm text-muted-foreground"');
});
