<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('onboarding owns one typed semantic icon for every mandatory step', function () {
    $registryPath = resource_path('js/components/onboarding/onboarding-icons.ts');

    expect(File::exists($registryPath))->toBeTrue();

    if (! File::exists($registryPath)) {
        return;
    }

    $source = File::get($registryPath);

    expect($source)
        ->toContain('satisfies Record<OnboardingStep, Component>')
        ->toContain('export function resolveOnboardingStepIcon');

    foreach ([
        'welcome',
        'preferences',
        'workspace',
        'project',
        'task',
        'product_map',
        'safety',
        'results',
    ] as $step) {
        expect($source)->toContain("{$step}:");
    }
});

test('onboarding field labels and compact icons share accessible primitives', function () {
    $iconPath = resource_path('js/components/onboarding/OnboardingIcon.vue');
    $labelPath = resource_path('js/components/onboarding/OnboardingFieldLabel.vue');

    expect(File::exists($iconPath))->toBeTrue()
        ->and(File::exists($labelPath))->toBeTrue();

    if (! File::exists($iconPath) || ! File::exists($labelPath)) {
        return;
    }

    expect(File::get($iconPath))
        ->toContain('data-slot="onboarding-icon"')
        ->toContain('aria-hidden="true"')
        ->toContain('icon?: Component;')
        ->toContain('<slot');

    expect(File::get($labelPath))
        ->toContain("as?: 'label' | 'legend';")
        ->toContain("import OnboardingIcon from '@/components/onboarding/OnboardingIcon.vue'")
        ->toContain('<OnboardingIcon :icon="icon"')
        ->toContain('wrap-anywhere');
});

test('every onboarding form label select trigger and select option carries a meaningful icon', function () {
    $fieldLabelCounts = [
        'PreferencesStep' => 7,
        'WorkspaceStep' => 3,
        'ProjectStep' => 5,
        'TaskStep' => 7,
    ];

    foreach ($fieldLabelCounts as $component => $expectedCount) {
        $source = File::get(resource_path("js/components/onboarding/{$component}.vue"));

        expect($source)
            ->toContain("import OnboardingFieldLabel from '@/components/onboarding/OnboardingFieldLabel.vue'")
            ->not->toContain("import { Label } from '@/components/ui/label'")
            ->not->toContain('<Label ')
            ->and(substr_count($source, '<OnboardingFieldLabel'))
            ->toBe($expectedCount);

        preg_match_all('/<SelectTrigger\b[^>]*>.*?<\/SelectTrigger>/s', $source, $triggers);
        preg_match_all('/<SelectItem\b[^>]*>.*?<\/SelectItem>/s', $source, $items);

        foreach ($triggers[0] as $trigger) {
            expect($trigger, "{$component} select trigger is missing an icon")
                ->toContain('<OnboardingIcon');
        }

        foreach ($items[0] as $item) {
            expect($item, "{$component} select option is missing an icon")
                ->toContain('<OnboardingIcon');
        }
    }
});

test('onboarding navigation validation guidance and standalone notices use icons', function () {
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $panel = File::get(resource_path('js/components/onboarding/OnboardingStepPanel.vue'));

    expect($page)
        ->toContain('resolveOnboardingStepIcon')
        ->toContain('const activeStepIcon = computed')
        ->toContain(':icon="activeStepIcon"')
        ->and($shell)
        ->toContain('resolveOnboardingStepIcon')
        ->toContain('ArrowLeft', 'ArrowRight', 'LogOut', 'PartyPopper')
        ->toContain('<OnboardingIcon')
        ->and($panel)
        ->toContain('icon: Component;')
        ->toContain('<IconTile')
        ->toContain('AlertTriangle')
        ->toContain('CircleAlert');

    foreach (['WorkspaceStep', 'ProjectStep', 'TaskStep'] as $component) {
        $source = File::get(resource_path("js/components/onboarding/{$component}.vue"));

        expect($source)
            ->toContain('data-slot="onboarding-guidance"')
            ->toContain('PackageOpen');
    }

    expect(File::get(resource_path('js/components/onboarding/WelcomeStep.vue')))
        ->toContain('ShieldCheck')
        ->toContain('tile-tone="success"');
    expect(File::get(resource_path('js/components/onboarding/SafetyStep.vue')))
        ->toContain('Info')
        ->toContain('tile-tone="information"');
    expect(File::get(resource_path('js/components/onboarding/ResultsStep.vue')))
        ->toContain('ArrowRight')
        ->toContain('tile-tone="information"');
});

test('the shared timezone dropdown shows a semantic icon for every option', function () {
    $source = File::get(resource_path('js/components/preferences/TimezoneCombobox.vue'));

    expect($source)
        ->toContain('Clock3')
        ->toContain('MapPin')
        ->toContain('data-slot="timezone-option-icon"')
        ->toContain('aria-hidden="true"');
});
