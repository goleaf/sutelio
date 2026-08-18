<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('custom colors use the maintained Vue picker instead of native color inputs', function () {
    /** @var array{dependencies: array<string, string>} $package */
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($package['dependencies'])
        ->toHaveKey('@zag-js/color-picker', '^1.43.1')
        ->toHaveKey('@zag-js/vue', '^1.43.1');

    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toMatch('/type=["\']color["\']/');
    }
});

test('the shared picker owns package integration accessibility and responsive popover behavior', function () {
    $path = resource_path('js/components/ui/color-picker/ColorPickerField.vue');

    expect(File::exists($path))->toBeTrue();

    $source = File::get($path);

    expect($source)
        ->toContain("import * as colorPicker from '@zag-js/color-picker'")
        ->toContain("import { normalizeProps, useMachine } from '@zag-js/vue'")
        ->toContain('defineModel<string>')
        ->toContain('api.getRootProps()')
        ->toContain('api.getTriggerProps()')
        ->toContain('api.getAreaProps()')
        ->toContain("closest<HTMLElement>('[role=\"dialog\"]')")
        ->not->toContain('api.getHiddenInputProps()')
        ->toMatch("/api\\.getChannelSliderProps\\(\\{\\s*channel: 'hue'/")
        ->toMatch("/api\\.getChannelInputProps\\(\\{\\s*channel: 'hex'/")
        ->toMatch('/api\\.getSwatchTriggerProps\\(\\{\\s*value: preset/')
        ->toContain('data-slot="color-picker-trigger"')
        ->toContain('data-slot="color-picker-content"')
        ->toContain(':aria-label="triggerLabel"')
        ->toContain(':aria-label="areaLabel"')
        ->toContain(':aria-label="hueLabel"')
        ->toContain(':aria-label="hexLabel"')
        ->toContain(':aria-invalid="invalid"')
        ->toContain('class="size-11 rounded-full')
        ->toContain('pointer-coarse:size-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('max-h-[var(--available-height)]')
        ->toContain('forced-colors:')
        ->toContain('motion-reduce:')
        ->toContain('v-for="preset in presets"')
        ->toContain(':aria-label="presetLabel(preset)"');
});

test('every color editing workflow composes the shared picker', function (string $path, int $minimumUses) {
    $source = File::get(resource_path("js/{$path}"));

    expect($source)
        ->toContain("from '@/components/ui/color-picker'")
        ->and(substr_count($source, '<ColorPickerField'))
        ->toBeGreaterThanOrEqual($minimumUses);
})->with([
    'onboarding project' => ['components/onboarding/ProjectStep.vue', 1],
    'project creation' => ['components/project/ProjectCreateDialog.vue', 1],
    'workspace labels' => ['components/workspace/WorkspaceConfigurationPanel.vue', 2],
    'workspace definitions' => ['components/workspace/WorkspaceDefinitionCard.vue', 2],
]);

test('shared picker assistive copy stays localized in every supported language', function () {
    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(trans('ui.color_picker.open', ['color' => '#ff6038'], $locale))
            ->not->toBe('ui.color_picker.open')
            ->toContain('#ff6038')
            ->and(trans('ui.color_picker.area', locale: $locale))
            ->not->toBe('ui.color_picker.area')
            ->and(trans('ui.color_picker.hue', locale: $locale))
            ->not->toBe('ui.color_picker.hue')
            ->and(trans('ui.color_picker.hex', locale: $locale))
            ->not->toBe('ui.color_picker.hex')
            ->and(trans('ui.color_picker.preset', ['color' => '#ff6038'], $locale))
            ->not->toBe('ui.color_picker.preset')
            ->toContain('#ff6038');
    }
});
