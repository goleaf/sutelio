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

test('onboarding resolves through a dedicated layout without application navigation', function () {
    $app = File::get(resource_path('js/app.ts'));
    $layoutPath = resource_path('js/layouts/OnboardingLayout.vue');

    expect(File::exists($layoutPath))->toBeTrue()
        ->and($app)
        ->toContain("import OnboardingLayout from '@/layouts/OnboardingLayout.vue'")
        ->toContain("case name.startsWith('onboarding/'):")
        ->toContain('return OnboardingLayout');

    $layout = File::get($layoutPath);

    expect($layout)
        ->toContain('<main')
        ->not->toContain('AppSidebar')
        ->not->toContain('AppSidebarHeader')
        ->not->toContain('NavMain')
        ->not->toContain('WorkspaceSwitcher');
});

test('onboarding page coordinates typed Inertia forms Wayfinder and connected focus handoff', function () {
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $componentSource = collect(File::allFiles(resource_path('js/components/onboarding')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($page)
        ->toContain("from '@/actions/App/Http/Controllers/OnboardingController'")
        ->toContain('useForm')
        ->toContain('form.processing')
        ->toContain('form.errors')
        ->toContain('nextTick')
        ->toContain('isConnected')
        ->toContain('focusStepHeading')
        ->toContain('Object.entries(form.errors)')
        ->toContain('requestAnimationFrame')
        ->toContain("flush: 'post'")
        ->not->toContain("import AppLayout from '@/layouts/AppLayout.vue'")
        ->not->toContain('<AppLayout')
        ->not->toContain("'/onboarding")
        ->and(substr_count($page.$componentSource, '<h1'))
        ->toBe(1);
});

test('onboarding shell exposes progress status validation and mobile-safe actions', function () {
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $panel = File::get(resource_path('js/components/onboarding/OnboardingStepPanel.vue'));
    $styles = File::get(resource_path('css/app.css'));
    $forms = collect(File::allFiles(resource_path('js/components/onboarding')))
        ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), 'Step.vue'))
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($shell)
        ->toContain('data-slot="onboarding-mobile-progress"')
        ->toContain(':aria-current=')
        ->toContain("? 'step'")
        ->toContain('role="progressbar"')
        ->toContain(':aria-valuenow="progress.percent"')
        ->toContain('<StatusNotice')
        ->toContain('fixed inset-x-0 bottom-0')
        ->toContain('pb-40')
        ->toContain('lg:pb-0')
        ->toContain('xl:hidden')
        ->toContain('lg:static')
        ->toContain('var(--safe-area-inset-bottom)')
        ->toContain('grid-cols-2')
        ->toContain('col-span-2')
        ->toContain('w-full')
        ->toContain('sm:w-auto')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('motion-reduce:transition-none')
        ->toContain('forced-colors:')
        ->toContain('class="text-[0.9375rem] leading-6"')
        ->not->toContain('truncate')
        ->not->toContain('lg:hidden')
        ->not->toContain('xl:static')
        ->not->toContain('sticky bottom-0')
        ->and($styles)
        ->toContain('env(safe-area-inset-bottom, 0px)')
        ->toContain('var(--inset-bottom, 0px)')
        ->toContain('--page-safe-area-inset-bottom:')
        ->and($panel)
        ->toContain('role="alert"')
        ->toContain('validation-summary')
        ->toContain('href="`#${error.field}`"')
        ->and($forms)
        ->toContain('aria-invalid');
});

test('mandatory onboarding keeps every step readable wrap safe and touch comfortable', function () {
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $panel = File::get(resource_path('js/components/onboarding/OnboardingStepPanel.vue'));
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $app = File::get(resource_path('js/app.ts'));
    $stepComponents = [
        'WelcomeStep',
        'PreferencesStep',
        'WorkspaceStep',
        'ProjectStep',
        'TaskStep',
        'ProductMapStep',
        'SafetyStep',
        'ResultsStep',
    ];

    expect($shell)
        ->toContain('data-slot="onboarding-actions"')
        ->toContain('pb-[max(1rem,var(--safe-area-inset-bottom))]')
        ->toContain('wrap-anywhere')
        ->toContain('text-[0.9375rem]')
        ->toContain('text-base')
        ->and(substr_count($shell, 'type="submit"'))
        ->toBe(1)
        ->and($panel)
        ->toContain('<StatusNotice')
        ->toContain('text-[0.9375rem]')
        ->toContain('text-base')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->and($page)
        ->toContain('focusValidationSummary')
        ->toContain("saveStatus.value = 'error'")
        ->and($app)
        ->toContain('NetworkStatusNotifier');

    foreach ($stepComponents as $component) {
        $source = File::get(resource_path("js/components/onboarding/{$component}.vue"));

        expect($source)
            ->not->toContain('text-xs')
            ->not->toContain('text-sm');
    }

    foreach (['WorkspaceStep', 'ProjectStep', 'TaskStep'] as $component) {
        $source = File::get(resource_path("js/components/onboarding/{$component}.vue"));

        expect($source)
            ->toContain('min-h-12')
            ->toContain('text-base transition-[color,box-shadow]');
    }
});

test('required onboarding exposes no skip affordance while replay has a distinct exit', function () {
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $types = File::get(resource_path('js/components/onboarding/onboarding-types.ts'));

    expect($page)
        ->toContain('exitReplay')
        ->not->toContain('skipOnboarding')
        ->not->toContain('@skip=')
        ->and($shell)
        ->toContain('v-if="progress.is_replay"')
        ->toContain('copy.actions.exit_replay')
        ->toContain('exitReplay: []')
        ->not->toContain('copy.actions.skip')
        ->not->toContain('skipOpen')
        ->not->toContain('WorkspaceConfirmDialog')
        ->and($types)
        ->toContain('exit_replay: string')
        ->not->toContain('skip: string')
        ->not->toContain('skip_confirm: string')
        ->not->toContain('skip_title: string')
        ->not->toContain('skip_description: string');
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

test('onboarding hides unavailable existing choices and explains the create only path', function () {
    foreach ([
        'WorkspaceStep' => 'workspaces',
        'ProjectStep' => 'projects',
        'TaskStep' => 'tasks',
    ] as $component => $options) {
        $source = File::get(resource_path("js/components/onboarding/{$component}.vue"));

        expect($source)
            ->toContain("const hasExistingOptions = computed(() => props.{$options}.length > 0);")
            ->toMatch('/hasExistingOptions\s+\?\s+copy\.description\s+:\s+copy\.create_description/')
            ->toContain('v-if="hasExistingOptions"')
            ->toContain('v-else-if="!hasExistingOptions || mode === \'create\'"')
            ->not->toContain(":disabled=\"processing || {$options}.length === 0\"");
    }
});

test('onboarding entity step headings stay truthful in create and select modes', function () {
    foreach ([
        'en' => [
            'workspace' => 'Set up your workspace',
            'project' => 'Give the work a project',
            'task' => 'Define the next action',
        ],
        'lt' => [
            'workspace' => 'Paruoškite darbo erdvę',
            'project' => 'Priskirkite darbui projektą',
            'task' => 'Nustatykite kitą veiksmą',
        ],
        'ru' => [
            'workspace' => 'Настройте рабочее пространство',
            'project' => 'Определите проект для работы',
            'task' => 'Определите следующее действие',
        ],
    ] as $locale => $headings) {
        foreach ($headings as $step => $heading) {
            expect(trans("onboarding.steps.{$step}.title", locale: $locale))->toBe($heading);
        }
    }
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
        ->toContain('<StatusNotice')
        ->not->toContain('percent')
        ->and($preferences)
        ->toContain('canReplayOnboarding')
        ->toContain('restartOnboarding')
        ->toContain('replayForm.processing');
});
