<?php

use Illuminate\Support\Facades\File;

test('project identity uses one curated icon registry with a safe fallback', function () {
    $registryPath = resource_path('js/components/project/project-icons.ts');

    expect(File::exists($registryPath))->toBeTrue();

    if (! File::exists($registryPath)) {
        return;
    }

    $source = File::get($registryPath);

    expect($source)
        ->toContain('export const projectIconOptions')
        ->toContain('export function resolveProjectIcon')
        ->toContain("value: 'folder'")
        ->toContain("value: 'briefcase'")
        ->toContain("value: 'code'")
        ->toContain("value: 'palette'")
        ->toContain("value: 'book'")
        ->toContain("value: 'star'")
        ->toContain("value: 'rocket'")
        ->toContain("value: 'globe'")
        ->toContain('?? Folder');
});

test('shared project icon picker exposes a localized touch and keyboard friendly choice group', function () {
    $pickerPath = resource_path('js/components/project/ProjectIconPicker.vue');

    expect(File::exists($pickerPath))->toBeTrue();

    if (! File::exists($pickerPath)) {
        return;
    }

    expect(File::get($pickerPath))
        ->toContain('defineModel<string>')
        ->toContain('projectIconOptions')
        ->toContain('role="group"')
        ->toContain(':aria-label="label"')
        ->toContain(':aria-pressed="icon === option.value"')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('focus-visible:ring-2')
        ->toContain('motion-reduce:transition-none')
        ->toContain('forced-colors:border-[ButtonText]');
});

test('every project creation workflow composes the shared visual icon picker', function (string $path) {
    $source = File::get(resource_path("js/{$path}"));

    expect($source)
        ->toContain("import ProjectIconPicker from '@/components/project/ProjectIconPicker.vue'")
        ->toContain('<ProjectIconPicker')
        ->not->toContain('id="icon"');
})->with([
    'ordinary project creation' => 'components/project/ProjectCreateDialog.vue',
    'mandatory onboarding project creation' => 'components/onboarding/ProjectStep.vue',
]);

test('project previews cards and headers render the selected icon through the shared resolver', function (string $path) {
    expect(File::get(resource_path("js/{$path}")))
        ->toContain("import ProjectIcon from '@/components/project/ProjectIcon.vue'")
        ->toContain('<ProjectIcon');
})->with([
    'onboarding project preview' => 'components/onboarding/ProjectStep.vue',
    'project collection cards' => 'pages/projects/Index.vue',
    'project operations header' => 'components/project/ProjectOperationsHeader.vue',
]);

test('the curated project icon list has localized labels in every supported language', function (string $locale) {
    $copy = require lang_path("{$locale}/workspace.php");

    foreach (['folder', 'briefcase', 'code', 'palette', 'book', 'star', 'rocket', 'globe'] as $icon) {
        expect(data_get($copy, "projects.icon_{$icon}"), "{$locale}.projects.icon_{$icon}")
            ->toBeString()
            ->not->toBeEmpty();
    }
})->with(['en', 'lt', 'ru']);
