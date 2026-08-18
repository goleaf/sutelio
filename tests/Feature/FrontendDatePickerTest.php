<?php

test('the shared date picker owns the package backed calendar contract', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $pickerPath = resource_path('js/components/ui/date-picker/DatePickerField.vue');

    expect(data_get($package, 'dependencies.reka-ui'))->toBeString()
        ->and(data_get($package, 'dependencies.@internationalized/date'))->toBeString()
        ->and(File::exists($pickerPath))->toBeTrue();

    if (! File::exists($pickerPath)) {
        return;
    }

    expect(File::get($pickerPath))
        ->toContain("from 'reka-ui'")
        ->toContain('RekaDatePickerField')
        ->toContain('CalendarRoot')
        ->toContain(":calendar-label=\"t('date_picker.calendar_label')\"")
        ->toContain(':locale="locale"')
        ->toContain(':week-starts-on="weekStartsOn"')
        ->toContain(':portal="{ to: portalTarget }"')
        ->toContain('data-slot="date-picker-content"')
        ->toContain('pointer-coarse:min-h-12')
        ->toContain('motion-reduce:data-[state=open]:animate-none')
        ->toContain('forced-colors:border-[CanvasText]');
});

test('every application date entry composes the shared picker', function (string $path, string $model, bool $includesTime) {
    $source = File::get(resource_path("js/{$path}"));

    expect($source)
        ->toContain("import { DatePickerField } from '@/components/ui/date-picker'")
        ->toContain('<DatePickerField')
        ->toContain("v-model=\"{$model}\"");

    if ($includesTime) {
        expect($source)->toContain('granularity="minute"');
    }
})->with([
    'mandatory onboarding task due date' => ['components/onboarding/TaskStep.vue', 'dueDate', false],
    'ordinary task creation due date' => ['components/task/TaskCreateDialog.vue', 'form.due_date', false],
    'task overview due date' => ['components/task/TaskOverviewPanel.vue', 'form.due_date', false],
    'task reminder date and time' => ['components/task/TaskRemindersPanel.vue', 'reminderRequest.reminded_at', true],
]);

test('native browser calendar inputs are absent from first party Vue components', function () {
    $violations = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->filter(fn (SplFileInfo $file): bool => preg_match(
            '/type\s*=\s*["\'](?:date|datetime-local|month|week)["\']/',
            $file->getContents(),
        ) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($violations)->toBe([]);
});

test('calendar controls never submit their surrounding application form', function () {
    $source = File::get(resource_path('js/components/ui/date-picker/DatePickerField.vue'));

    foreach (['DatePickerTrigger', 'DatePickerPrev', 'DatePickerNext'] as $component) {
        expect($source, $component)->toMatch(
            "/<{$component}\\s+as-child>.*?<Button\\s+[^>]*type=\"button\"/s",
        );
    }

    expect($source)
        ->toMatch('/<DatePickerCellTrigger\\s+[^>]*as="button"[^>]*type="button"/s')
        ->toMatch('/<DatePickerClose\\s+[^>]*type="button"[^>]*>/s');
});

test('date picker copy is localized in every supported language', function (string $locale) {
    $copy = require lang_path("{$locale}/ui.php");

    foreach ([
        'calendar_label',
        'clear',
        'next_month',
        'open',
        'previous_month',
        'today',
    ] as $key) {
        expect(data_get($copy, "date_picker.{$key}"), "{$locale}.date_picker.{$key}")
            ->toBeString()
            ->not->toBeEmpty();
    }

    foreach (['remind_at', 'reminder_type'] as $key) {
        expect(data_get($copy, "tasks.detail.{$key}"), "{$locale}.tasks.detail.{$key}")
            ->toBeString()
            ->not->toBeEmpty();
    }
})->with(['en', 'lt', 'ru']);
