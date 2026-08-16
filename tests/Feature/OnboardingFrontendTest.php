<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('guided onboarding is composed from one focused component per semantic step', function () {
    foreach ([
        'OnboardingShell',
        'OnboardingStepPanel',
        'WelcomeStep',
        'PreferencesStep',
        'WorkspaceStep',
        'ProjectStep',
        'TaskStep',
        'ProductMapStep',
        'SafetyStep',
        'ResultsStep',
    ] as $component) {
        expect(File::exists(resource_path("js/components/onboarding/{$component}.vue")))
            ->toBeTrue("Missing {$component}.");
    }
});

test('onboarding page coordinates typed Inertia forms Wayfinder and connected focus handoff', function () {
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $componentSource = collect(File::allFiles(resource_path('js/components/onboarding')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($page)
        ->toContain("import AppLayout from '@/layouts/AppLayout.vue'")
        ->toContain("from '@/actions/App/Http/Controllers/OnboardingController'")
        ->toContain('useForm')
        ->toContain('form.processing')
        ->toContain('form.errors')
        ->toContain('nextTick')
        ->toContain('isConnected')
        ->toContain('focusStepHeading')
        ->not->toContain("'/onboarding")
        ->and(substr_count($page.$componentSource, '<h1'))
        ->toBe(1);
});

test('onboarding shell exposes progress status validation and mobile-safe actions', function () {
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $panel = File::get(resource_path('js/components/onboarding/OnboardingStepPanel.vue'));
    $forms = collect(File::allFiles(resource_path('js/components/onboarding')))
        ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), 'Step.vue'))
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($shell)
        ->toContain(':aria-current=')
        ->toContain("? 'step'")
        ->toContain('role="progressbar"')
        ->toContain(':aria-valuenow="progress.percent"')
        ->toContain('aria-live="polite"')
        ->toContain('sticky bottom-0')
        ->toContain('env(safe-area-inset-bottom)')
        ->toContain('min-h-11')
        ->toContain('motion-reduce:transition-none')
        ->toContain('forced-colors:')
        ->and($panel)
        ->toContain('role="alert"')
        ->toContain('validation-summary')
        ->toContain('href="`#${error.field}`"')
        ->and($forms)
        ->toContain('aria-invalid');
});

test('onboarding visible actions and assistive messages come from semantic copy', function () {
    $source = File::get(resource_path('js/pages/onboarding/Index.vue'))
        .collect(File::allFiles(resource_path('js/components/onboarding')))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
            ->map(fn (SplFileInfo $file): string => $file->getContents())
            ->implode("\n");

    expect($source)
        ->not->toMatch('/>\s*(Continue|Back|Skip|Save|Create|Choose)\s*</')
        ->toContain('copy.actions')
        ->toContain('copy.status');
});

test('dashboard continuation and settings replay use accessible policy-aware Wayfinder controls', function () {
    $dashboard = File::get(resource_path('js/pages/Dashboard.vue'));
    $checklist = File::get(resource_path('js/components/onboarding/OnboardingChecklist.vue'));
    $preferences = File::get(resource_path('js/pages/settings/Preferences.vue'));

    expect($dashboard)
        ->toContain("from '@/components/onboarding/OnboardingChecklist.vue'")
        ->toContain('<OnboardingChecklist')
        ->and(strpos($dashboard, '<OnboardingChecklist'))
        ->toBeLessThan(strpos($dashboard, 'dashboard-focus-title'))
        ->and($checklist)
        ->toContain("from '@/actions/App/Http/Controllers/OnboardingController'")
        ->toContain("from '@/routes/workspaces'")
        ->toContain('checklist.can_invite')
        ->toContain('checklist.can_manage_backups')
        ->toContain('checklist.has_team_member')
        ->toContain('checklist.has_security_factor')
        ->toContain('min-h-11')
        ->toContain('aria-live="polite"')
        ->not->toContain('percent')
        ->and($preferences)
        ->toContain('canReplayOnboarding')
        ->toContain('restartOnboarding')
        ->toContain('replayForm.processing');
});
