<?php

use Illuminate\Support\Facades\File;

test('the shared field owns label description error and control accessibility ids', function () {
    $path = resource_path('js/components/shared/Field.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="field"')
        ->toContain('data-slot="field-label"')
        ->toContain('data-slot="field-description"')
        ->toContain('useId()')
        ->toContain(':id="controlId"')
        ->toContain(':described-by="describedBy"')
        ->toContain(':invalid="hasError"')
        ->toContain(':required="required"')
        ->toContain('<InputError')
        ->toContain(':id="errorId"');
});

test('the shared textarea matches the native input interaction contract', function () {
    $path = resource_path('js/components/ui/textarea/Textarea.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="textarea"')
        ->toContain('useVModel')
        ->toContain('min-h-24')
        ->toContain('focus-visible:border-orange-500')
        ->toContain('aria-invalid:ring-destructive/20')
        ->toContain('disabled:cursor-not-allowed');

    expect(File::get(resource_path('js/components/ui/textarea/index.ts')))
        ->toContain('export { default as Textarea }');
});

test('field errors expose a stable component slot for described by wiring', function () {
    expect(File::get(resource_path('js/components/InputError.vue')))
        ->toContain('data-slot="field-error"')
        ->toContain('role="alert"')
        ->toContain('aria-live="polite"');
});

test('task title and description fields compose the shared form primitives', function () {
    $titlePath = resource_path('js/components/task/TaskTitleField.vue');
    $descriptionPath = resource_path('js/components/task/TaskDescriptionField.vue');

    expect(File::exists($titlePath))->toBeTrue()
        ->and(File::exists($descriptionPath))->toBeTrue();

    expect(File::get($titlePath))
        ->toContain("import Field from '@/components/shared/Field.vue'")
        ->toContain("import { Input } from '@/components/ui/input'")
        ->toContain('<Field')
        ->toContain('#default="{ id, describedBy, invalid, required }"')
        ->toContain(':aria-describedby="describedBy"')
        ->toContain(':aria-invalid="invalid"')
        ->toContain(':required="required"');

    expect(File::get($descriptionPath))
        ->toContain("import Field from '@/components/shared/Field.vue'")
        ->toContain("import { Textarea } from '@/components/ui/textarea'")
        ->toContain('<Field')
        ->toContain('<Textarea')
        ->toContain(':aria-describedby="describedBy"')
        ->toContain(':aria-invalid="invalid"');
});

test('task create and overview compose the same focused task fields', function () {
    foreach ([
        'task create' => 'TaskCreateDialog.vue',
        'task overview' => 'TaskOverviewPanel.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/task/{$file}"));

        expect($source, $name)
            ->toContain("import TaskTitleField from '@/components/task/TaskTitleField.vue'")
            ->toContain("import TaskDescriptionField from '@/components/task/TaskDescriptionField.vue'")
            ->toContain('<TaskTitleField')
            ->toContain('<TaskDescriptionField');
    }
});
