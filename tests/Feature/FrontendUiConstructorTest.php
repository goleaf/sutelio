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

test('workspace dialogs compose shared body and action spacing', function () {
    $bodyPath = resource_path('js/components/shared/DialogBody.vue');
    $actionsPath = resource_path('js/components/shared/DialogActions.vue');

    expect(File::exists($bodyPath))->toBeTrue()
        ->and(File::exists($actionsPath))->toBeTrue();

    expect(File::get($bodyPath))
        ->toContain('data-slot="dialog-body"')
        ->toContain('space-y-6 px-4 py-5 sm:px-8 sm:py-6')
        ->toContain('<slot />');

    expect(File::get($actionsPath))
        ->toContain("import { DialogFooter } from '@/components/ui/dialog'")
        ->toContain('data-slot="dialog-actions"')
        ->toContain('border-t border-border/70')
        ->toContain('px-4 py-4')
        ->toContain('sm:px-8')
        ->toContain('<slot />');
});

test('active workspace dialogs reuse shared body and action composition', function () {
    foreach ([
        'project create' => 'project/ProjectCreateDialog.vue',
        'task create' => 'task/TaskCreateDialog.vue',
        'workspace confirmation' => 'shared/WorkspaceConfirmDialog.vue',
        'workspace edit' => 'workspace/WorkspaceOverviewPanel.vue',
        'delete user' => 'DeleteUser.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain("import DialogActions from '@/components/shared/DialogActions.vue'")
            ->toContain("import DialogBody from '@/components/shared/DialogBody.vue'")
            ->toContain('<DialogBody>')
            ->toContain('<DialogActions>')
            ->not->toContain('space-y-6 px-6 py-6 sm:px-8')
            ->not->toContain('gap-2 border-t border-border/70 pt-5 sm:gap-2');
    }
});

test('the shared button owns pending state without breaking as child composition', function () {
    $source = File::get(resource_path('js/components/ui/button/Button.vue'));

    expect($source)
        ->toContain('loading?: boolean;')
        ->toContain('loadingLabel?: string;')
        ->toContain('disabled?: boolean;')
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain(':data-loading="props.loading ? \'\' : undefined"')
        ->toContain(':aria-busy="props.loading ? \'true\' : undefined"')
        ->toContain(':aria-disabled="isDisabled ? \'true\' : undefined"')
        ->toContain(':disabled="props.asChild ? undefined : isDisabled"')
        ->toContain('v-if="props.loading && !props.asChild"')
        ->toContain('<slot v-else />')
        ->toContain('@click="preventDisabledActivation"');

    expect(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('aria-disabled:pointer-events-none')
        ->toContain('data-loading:cursor-wait');
});

test('active asynchronous actions delegate their visual pending state to the shared button', function () {
    foreach ([
        'workspace confirmation' => 'shared/WorkspaceConfirmDialog.vue',
        'project result pagination' => 'project/ProjectTaskQueue.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain(':loading=')
            ->not->toMatch('/<(Spinner|LoaderCircle)\s+v-if="(?:form\.)?processing"/');
    }
});

test('the shared inline state owns compact feedback semantics and visual treatment', function () {
    $path = resource_path('js/components/shared/InlineState.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('type InlineStateStatus =')
        ->toContain("'empty'", "'loading'", "'error'", "'success'", "'information'")
        ->toContain('data-slot="inline-state"')
        ->toContain(':data-status="props.status"')
        ->toContain("if (props.status === 'error')")
        ->toContain("return 'alert'")
        ->toContain("props.status === 'loading' ? 'true' : undefined")
        ->toContain('border-dashed border-border/80')
        ->toContain('<slot />');
});

test('task collaboration panels reuse the shared compact empty state', function () {
    foreach ([
        'attachments' => 'TaskAttachmentsPanel.vue',
        'checklists' => 'TaskChecklistPanel.vue',
        'comments' => 'TaskCommentsPanel.vue',
        'reminders' => 'TaskRemindersPanel.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/task/{$file}"));

        expect($source, $name)
            ->toContain("import InlineState from '@/components/shared/InlineState.vue'")
            ->toContain('<InlineState')
            ->not->toContain('rounded-xl border border-dashed border-border/80 px-4 py-6 text-center text-sm text-muted-foreground');
    }
});

test('the shared color swatch centralizes safe dynamic color presentation', function () {
    $path = resource_path('js/components/shared/ColorSwatch.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain("import { safeDefinitionColor } from '@/composables/useTaskDefinitions'")
        ->toContain("size?: 'xs' | 'sm' | 'md' | 'lg'")
        ->toContain('emphasized?: boolean;')
        ->toContain('data-slot="color-swatch"')
        ->toContain(':aria-label="props.label"')
        ->toContain(':aria-hidden="props.label ? undefined : \'true\'"')
        ->toContain('backgroundColor: safeDefinitionColor(props.color, props.fallback)');
});

test('calendar task links and task taxonomy reuse the shared color swatch', function () {
    foreach ([
        'calendar agenda' => 'calendar/CalendarAgendaView.vue',
        'calendar attention' => 'calendar/CalendarAttentionRail.vue',
        'calendar month' => 'calendar/CalendarMonthGrid.vue',
        'calendar week' => 'calendar/CalendarWeekView.vue',
        'task taxonomy' => 'task/TaskTaxonomyPanel.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain("import ColorSwatch from '@/components/shared/ColorSwatch.vue'")
            ->toContain('<ColorSwatch');
    }
});

test('the shared search field owns search affordances and control accessibility', function () {
    $path = resource_path('js/components/shared/SearchField.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="search-field"')
        ->toContain('useId()')
        ->toContain('useVModel')
        ->toContain('<Label')
        ->toContain('type="search"')
        ->toContain(':aria-describedby="props.describedBy"')
        ->toContain(':aria-invalid="props.invalid"')
        ->toContain('v-if="props.pending"')
        ->toContain('v-if="!props.pending && modelValue && props.clearLabel"')
        ->toContain("emit('clear')");
});

test('workspace management search inputs reuse the shared search field', function () {
    foreach ([
        'workspace members' => 'WorkspaceMembersPanel.vue',
        'workspace configuration' => 'WorkspaceConfigurationPanel.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/workspace/{$file}"));

        expect($source, $name)
            ->toContain("import SearchField from '@/components/shared/SearchField.vue'")
            ->toContain('<SearchField')
            ->not->toContain('type="search"');
    }
});
