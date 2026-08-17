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

test('a representative active form composes the shared field contract', function () {
    $source = File::get(resource_path('js/components/task/TaskCreateDialog.vue'));

    expect($source)
        ->toContain("import Field from '@/components/shared/Field.vue'")
        ->toContain('<Field')
        ->toContain(':label="t(\'tasks.create.title\')"')
        ->toContain(':error="form.errors.title"')
        ->toContain('#default="{ id, describedBy, invalid, required }"')
        ->toContain(':id="id"')
        ->toContain(':aria-describedby="describedBy"')
        ->toContain(':aria-invalid="invalid"')
        ->toContain(':required="required"')
        ->not->toContain('<Label for="title">');
});
