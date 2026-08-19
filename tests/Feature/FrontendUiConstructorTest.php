<?php

use Illuminate\Support\Facades\File;

test('the shared icon tile owns bounded icon enclosure tones and sizes', function () {
    $path = resource_path('js/components/shared/IconTile.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="icon-tile"')
        ->toContain('type IconTileTone =')
        ->toContain('tone?: IconTileTone;')
        ->toContain("size?: 'sm' | 'md' | 'lg'")
        ->toContain('aria-hidden="true"')
        ->toContain('<slot />');
});

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
        'task create' => 'TaskCreateForm.vue',
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

test('record confirmation uses the shared in-page panel instead of dialog helpers', function () {
    $bodyPath = resource_path('js/components/shared/DialogBody.vue');
    $actionsPath = resource_path('js/components/shared/DialogActions.vue');
    $confirmationPath = resource_path('js/components/shared/PageConfirmPanel.vue');

    expect(File::exists($bodyPath))->toBeFalse()
        ->and(File::exists($actionsPath))->toBeFalse()
        ->and(File::exists($confirmationPath))->toBeTrue();

    expect(File::get($confirmationPath))
        ->toContain('data-slot="page-confirm-panel"')
        ->toContain('role="region"')
        ->toContain(':type="confirmType"')
        ->not->toContain('@/components/ui/dialog')
        ->not->toContain('@/components/ui/sheet');
});

test('record management forms use page composition instead of dialog composition', function () {
    foreach ([
        'project form' => 'project/ProjectForm.vue',
        'task create' => 'task/TaskCreateForm.vue',
        'workspace create and edit' => 'workspace/WorkspaceForm.vue',
        'workspace duplicate' => 'workspace/WorkspaceDuplicateForm.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain('<form')
            ->toContain('rounded-panel')
            ->not->toContain('@/components/ui/dialog')
            ->not->toContain('<Dialog')
            ->not->toContain('<Sheet');
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
        'workspace confirmation' => 'shared/PageConfirmPanel.vue',
        'project result pagination' => 'project/ProjectTaskQueue.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain(':loading=')
            ->not->toMatch('/<(Spinner|LoaderCircle)\s+v-if="(?:form\.)?processing"/');
    }
});

test('authentication submissions reuse the shared button loading contract', function () {
    foreach ([
        'login' => 'Login.vue',
        'register' => 'Register.vue',
        'forgot password' => 'ForgotPassword.vue',
        'reset password' => 'ResetPassword.vue',
        'confirm password' => 'ConfirmPassword.vue',
        'two factor challenge' => 'TwoFactorChallenge.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/pages/auth/{$file}"));

        expect($source, $name)
            ->toContain(':loading="processing"')
            ->toContain(':loading-label=')
            ->not->toContain("import { Spinner } from '@/components/ui/spinner'")
            ->not->toContain('<Spinner v-if="processing"');
    }
});

test('settings mutations reuse the shared button loading contract', function () {
    foreach ([
        'preferences' => 'Preferences.vue',
        'notifications' => 'Notifications.vue',
        'profile' => 'Profile.vue',
        'security' => 'Security.vue',
        'members' => 'Members.vue',
        'backup' => 'Backup.vue',
        'export' => 'Export.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/pages/settings/{$file}"));

        expect($source, $name)
            ->toContain(':loading=')
            ->toContain(':loading-label=');
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

test('calendar task items and task taxonomy reuse the shared color swatch', function () {
    expect(File::get(resource_path('js/components/calendar/CalendarTaskItem.vue')))
        ->toContain("import ColorSwatch from '@/components/shared/ColorSwatch.vue'")
        ->toContain('<ColorSwatch');

    expect(File::get(resource_path('js/components/task/TaskTaxonomyPanel.vue')))
        ->toContain("import ColorSwatch from '@/components/shared/ColorSwatch.vue'")
        ->toContain('<ColorSwatch');
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

test('shared result and pagination frames own status and navigation layout only', function () {
    $resultPath = resource_path('js/components/shared/ResultSummary.vue');
    $paginationPath = resource_path('js/components/shared/PaginationBar.vue');

    expect(File::exists($resultPath))->toBeTrue()
        ->and(File::exists($paginationPath))->toBeTrue();

    expect(File::get($resultPath))
        ->toContain('data-slot="result-summary"')
        ->toContain('aria-live="polite"')
        ->toContain(':aria-busy="props.pending ? \'true\' : undefined"')
        ->toContain('{{ props.summary }}')
        ->toContain('{{ props.detail }}');

    expect(File::get($paginationPath))
        ->toContain('data-slot="pagination-bar"')
        ->toContain(':aria-label="props.label"')
        ->toContain('{{ props.summary }}')
        ->toContain('<slot />')
        ->not->toContain('router')
        ->not->toContain('Link');
});

test('task results and pagination compose the shared result frames', function () {
    expect(File::get(resource_path('js/components/task/TaskResultsBar.vue')))
        ->toContain("import ResultSummary from '@/components/shared/ResultSummary.vue'")
        ->toContain('<ResultSummary');

    expect(File::get(resource_path('js/components/task/TaskPagination.vue')))
        ->toContain("import PaginationBar from '@/components/shared/PaginationBar.vue'")
        ->toContain('<PaginationBar')
        ->not->toContain('<nav');
});

test('the shared surface panel owns the flat bordered product surface recipe', function () {
    $path = resource_path('js/components/shared/SurfacePanel.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain("as?: 'div' | 'section' | 'aside' | 'article' | 'nav'")
        ->toContain("padding?: 'none' | 'sm' | 'md'")
        ->toContain('data-slot="surface-panel"')
        ->toContain('rounded-panel border border-border/80 bg-card shadow-panel')
        ->toContain('<slot />');
});

test('notification filters reuse the shared flat surface', function () {
    $source = File::get(resource_path('js/components/notification/NotificationFilters.vue'));

    expect($source)
        ->toContain("import SurfacePanel from '@/components/shared/SurfacePanel.vue'")
        ->toContain('<SurfacePanel')
        ->not->toContain('overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel');
});

test('responsive section navigation owns mobile and desktop navigation presentation', function () {
    $path = resource_path('js/components/shared/ResponsiveSectionNavigation.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="responsive-section-navigation"')
        ->toContain('DropdownMenuTrigger')
        ->toContain('DropdownMenuItem')
        ->toContain('WorkspaceSegmentedControl')
        ->toContain("desktopMode?: 'list' | 'segmented'")
        ->toContain(':aria-current="item.active ? \'page\' : undefined"')
        ->toContain(':prefetch="props.prefetch"')
        ->toContain('motion-reduce:transition-none');
});

test('settings and workspace management reuse responsive section navigation', function () {
    foreach ([
        'settings' => 'settings/SettingsSectionMenu.vue',
        'workspace management' => 'workspace/WorkspaceManagementNavigation.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain("import ResponsiveSectionNavigation from '@/components/shared/ResponsiveSectionNavigation.vue'")
            ->toContain('<ResponsiveSectionNavigation')
            ->not->toContain('DropdownMenuTrigger')
            ->not->toContain('WorkspaceSegmentedControl');
    }
});

test('calendar task item owns shared task link identity and density variants', function () {
    $path = resource_path('js/components/calendar/CalendarTaskItem.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain("density?: 'comfortable' | 'compact' | 'dense'")
        ->toContain('data-slot="calendar-task-item"')
        ->toContain(':href="todoShow(props.todo)"')
        ->toContain('<ColorSwatch')
        ->toContain('props.todo.is_completed')
        ->toContain('props.secondary')
        ->toContain('props.showArrow')
        ->toContain('motion-reduce:transition-none');
});

test('calendar views reuse the shared task item', function () {
    foreach ([
        'agenda' => 'CalendarAgendaView.vue',
        'week' => 'CalendarWeekView.vue',
        'month' => 'CalendarMonthGrid.vue',
        'attention' => 'CalendarAttentionRail.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/calendar/{$file}"));

        expect($source, $name)
            ->toContain("import CalendarTaskItem from '@/components/calendar/CalendarTaskItem.vue'")
            ->toContain('<CalendarTaskItem')
            ->not->toContain('show as todoShow');
    }
});

test('filter sheet owns responsive filter overlay and action composition', function () {
    $path = resource_path('js/components/shared/FilterSheet.vue');

    expect(File::exists($path))->toBeTrue();

    expect(File::get($path))
        ->toContain('data-slot="filter-sheet"')
        ->toContain('defineModel<boolean>')
        ->toContain('<SheetTrigger')
        ->toContain('side="bottom"')
        ->toContain('<SheetHeader>')
        ->toContain('<SheetFooter')
        ->toContain(':loading="props.processing"')
        ->toContain("emit('clear')")
        ->toContain("emit('apply')");
});

test('active filter panels reuse the shared responsive filter sheet', function () {
    foreach ([
        'tasks' => 'task/TaskFilterBar.vue',
        'projects' => 'project/ProjectTaskFilters.vue',
        'activity' => 'activity/ActivityFilterPanel.vue',
    ] as $name => $file) {
        $source = File::get(resource_path("js/components/{$file}"));

        expect($source, $name)
            ->toContain("import FilterSheet from '@/components/shared/FilterSheet.vue'")
            ->toContain('<FilterSheet')
            ->not->toContain('<SheetContent')
            ->not->toContain('<SheetHeader');
    }
});

test('dismissed mobile filter drafts restore the applied task and project filters', function () {
    $taskFilters = File::get(resource_path('js/components/task/TaskFilterBar.vue'));
    $projectFilters = File::get(resource_path('js/components/project/ProjectTaskFilters.vue'));

    expect($taskFilters)
        ->toContain('function synchronizeFilters(filters: TodoFilters): void')
        ->toContain('function handleMobileFiltersOpenChange(value: boolean): void')
        ->toContain(':model-value="mobileFiltersOpen"')
        ->toContain('@update:model-value="handleMobileFiltersOpenChange"')
        ->and($projectFilters)
        ->toContain('function synchronizeFilters(filters: ProjectFilters): void')
        ->toContain('function handleMobileFiltersOpenChange(value: boolean): void')
        ->toContain(':model-value="mobileOpen"')
        ->toContain('@update:model-value="handleMobileFiltersOpenChange"');
});

test('domain filter field groups remove desktop and mobile field duplication', function () {
    foreach ([
        'tasks' => [
            'component' => 'task/TaskFilterFields.vue',
            'consumer' => 'task/TaskFilterBar.vue',
        ],
        'projects' => [
            'component' => 'project/ProjectTaskFilterFields.vue',
            'consumer' => 'project/ProjectTaskFilters.vue',
        ],
        'activity' => [
            'component' => 'activity/ActivityFilterFields.vue',
            'consumer' => 'activity/ActivityFilterPanel.vue',
        ],
    ] as $name => $paths) {
        $componentPath = resource_path("js/components/{$paths['component']}");
        $consumer = File::get(resource_path("js/components/{$paths['consumer']}"));

        expect(File::exists($componentPath), $name)->toBeTrue();
        expect(File::get($componentPath), $name)
            ->toContain('defineModel<')
            ->toContain("mode?: 'desktop' | 'mobile'");
        expect($consumer, $name)
            ->toContain('<'.pathinfo($paths['component'], PATHINFO_FILENAME))
            ->toContain('mode="desktop"')
            ->toContain('mode="mobile"');
    }
});
