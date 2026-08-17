<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

dataset('leading icon heading consumers', [
    'auth shell' => ['layouts/auth/AuthSimpleLayout.vue', 1],
    'first-run language dialog' => ['components/localization/FirstRunLanguageDialog.vue', 1],
    'dashboard task queue' => ['components/dashboard/DashboardTaskQueue.vue', 1],
    'calendar attention rail' => ['components/calendar/CalendarAttentionRail.vue', 1],
    'project pulse' => ['components/project/ProjectPulse.vue', 1],
    'onboarding checklist' => ['components/onboarding/OnboardingChecklist.vue', 1],
    'onboarding project step' => ['components/onboarding/ProjectStep.vue', 2],
    'onboarding task step' => ['components/onboarding/TaskStep.vue', 2],
    'onboarding workspace step' => ['components/onboarding/WorkspaceStep.vue', 2],
    'data scope banner' => ['components/settings/data/DataScopeBanner.vue', 1],
    'workspace configuration' => ['components/workspace/WorkspaceConfigurationPanel.vue', 2],
    'workspace danger panel' => ['components/workspace/WorkspaceDangerPanel.vue', 3],
    'workspace members panel' => ['components/workspace/WorkspaceMembersPanel.vue', 2],
    'workspace overview panel' => ['components/workspace/WorkspaceOverviewPanel.vue', 1],
    'settings members page' => ['pages/settings/Members.vue', 2],
    'settings profile page' => ['pages/settings/Profile.vue', 1],
    'settings preferences page' => ['pages/settings/Preferences.vue', 1],
    'settings backup page' => ['pages/settings/Backup.vue', 1],
    'settings export page' => ['pages/settings/Export.vue', 1],
    'settings security page' => ['pages/settings/Security.vue', 1],
]);

test('leading icon headings keep the icon beside a vertically centered wrapping text stack', function () {
    expect(File::get(resource_path('js/components/shared/LeadingIconHeading.vue')))
        ->toContain('data-slot="leading-icon-heading"')
        ->toContain('flex-nowrap')
        ->toContain('items-center')
        ->toContain('data-slot="leading-icon-heading-icon"')
        ->toContain('shrink-0')
        ->toContain('data-slot="leading-icon-heading-content"')
        ->toContain('min-w-0 flex-1')
        ->not->toContain('whitespace-nowrap');
});

test('every audited icon title and subtitle cluster uses the shared alignment contract', function (string $file, int $expectedCount) {
    $source = File::get(resource_path("js/{$file}"));

    expect($source)
        ->toContain("import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue'")
        ->and(substr_count($source, '<LeadingIconHeading'))
        ->toBe($expectedCount);
})->with('leading icon heading consumers');

test('primary workspace pages use the shared warm precision header', function (string $page) {
    expect(File::get(resource_path("js/pages/{$page}")))
        ->toContain('WorkspacePageHeader')
        ->toContain('bg-muted/20')
        ->toContain('max-w-app');
})->with([
    'dashboard' => 'Dashboard.vue',
    'activity' => 'activity/Index.vue',
    'calendar' => 'calendar/Index.vue',
    'notifications' => 'notifications/Index.vue',
    'projects' => 'projects/Index.vue',
    'tasks' => 'tasks/Index.vue',
    'task detail' => 'tasks/Show.vue',
    'workspaces' => 'workspaces/Index.vue',
]);

test('project operations compose the shared warm precision header', function () {
    expect(File::get(resource_path('js/pages/projects/Show.vue')))
        ->toContain('ProjectOperationsHeader')
        ->toContain('bg-muted/20')
        ->toContain('max-w-app')
        ->and(File::get(resource_path('js/components/project/ProjectOperationsHeader.vue')))
        ->toContain('WorkspacePageHeader');
});

test('dashboard renders the complete workspace command center supplied by its page props', function () {
    $source = File::get(resource_path('js/pages/Dashboard.vue'));

    expect($source)
        ->toContain('formatNumber(stats.today_count)')
        ->toContain('formatNumber(stats.completed_today)')
        ->toContain('formatNumber(stats.completion_rate)')
        ->toContain("import DashboardTaskQueue from '@/components/dashboard/DashboardTaskQueue.vue'")
        ->toContain("import { index as todoIndex } from '@/routes/todos'")
        ->toContain(':href="todoIndex()"')
        ->toContain('<template #actions>')
        ->toContain('xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.75fr)]')
        ->toContain(':featured="true"')
        ->toContain('<ProductivityChart :data="weeklyData" />')
        ->not->toContain('day.completed / maxWeekly');
});

test('dashboard task queues provide localized accessible Wayfinder navigation', function () {
    $source = File::get(resource_path('js/components/dashboard/DashboardTaskQueue.vue'));

    expect($source)
        ->toContain("import { Link } from '@inertiajs/vue3'")
        ->toContain("import { show as showTodo } from '@/routes/todos'")
        ->toContain(':href="showTodo(todo)"')
        ->toContain('prefetch')
        ->toContain('@container')
        ->toContain('@2xl:grid-cols-2')
        ->toContain('min-h-11')
        ->toContain('focus-visible:ring-2')
        ->toContain("t('common.states.unassigned')")
        ->toContain("t('dashboard.open_task',");
});

test('weekly productivity exposes honest reduced-motion bars and a semantic data table', function () {
    $source = File::get(resource_path('js/components/dashboard/ProductivityChart.vue'));

    expect($source)
        ->toContain('const completedTotal = computed')
        ->toContain('const createdTotal = computed')
        ->toContain('value === 0')
        ->toContain("? '0%'")
        ->toContain('motion-reduce:transition-none')
        ->toContain('<table')
        ->toContain('<caption')
        ->toContain("t('dashboard.weekly_table_caption')")
        ->toContain("t('dashboard.weekly_totals'")
        ->toContain("t('dashboard.no_weekly_activity')");
});

test('every active page header action uses the shared large button contract', function (string $page, int $actionCount) {
    $source = File::get(resource_path("js/pages/{$page}"));
    $actions = Str::betweenFirst(
        $source,
        '<template #actions>',
        '</template>',
    );

    expect(substr_count($actions, 'size="lg"'))
        ->toBe($actionCount)
        ->and($actions)
        ->not->toContain('min-h-11')
        ->not->toContain('bg-orange-600')
        ->not->toContain('rounded-xl');
})->with([
    'notifications' => ['notifications/Index.vue', 1],
    'projects' => ['projects/Index.vue', 1],
    'tasks' => ['tasks/Index.vue', 1],
    'task detail' => ['tasks/Show.vue', 1],
    'workspaces' => ['workspaces/Index.vue', 1],
]);

test('project operations keep only primary and overflow actions in the header action group', function () {
    $source = File::get(resource_path('js/components/project/ProjectOperationsHeader.vue'));
    $actions = Str::betweenFirst(
        $source,
        '<template #actions>',
        '</template>',
    );

    expect(substr_count($actions, 'size="lg"'))
        ->toBe(2)
        ->and($source)
        ->toContain('<template #back>')
        ->toContain('DropdownMenu');
});

test('header mutations expose shared inert loading states', function () {
    $notifications = File::get(resource_path('js/pages/notifications/Index.vue'));
    $project = File::get(resource_path('js/pages/projects/Show.vue'));
    $projectHeader = File::get(resource_path('js/components/project/ProjectOperationsHeader.vue'));
    $task = File::get(resource_path('js/components/task/TaskDetailContent.vue'));

    expect($notifications)
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="markingAll" />')
        ->and($project)
        ->toContain("type ProjectHeaderAction = 'archive' | 'duplicate' | 'restore'")
        ->toContain('const processingAction = ref<ProjectHeaderAction | null>(null)')
        ->toContain('finally {')
        ->and($projectHeader)
        ->toContain('v-if="processingAction ===')
        ->and($task)
        ->toContain('completionRequest.processing')
        ->toContain('<Spinner v-if="completionRequest.processing" />')
        ->toContain('useHttp<Record<string, never>, { data: Todo }>');
});

test('every settings page configures the shared projects style header', function (string $page) {
    expect(File::get(resource_path("js/pages/settings/{$page}.vue")))
        ->toContain('setLayoutProps<')
        ->toContain('settingsTitle:')
        ->toContain('settingsDescription:');
})->with([
    'backup' => 'Backup',
    'export' => 'Export',
    'members' => 'Members',
    'notifications' => 'Notifications',
    'preferences' => 'Preferences',
    'profile' => 'Profile',
    'security' => 'Security',
]);

test('shared shells carry the projects page visual language', function () {
    expect(File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue')))
        ->toContain('bg-muted/20')
        ->toContain('rounded-feature')
        ->and(File::get(resource_path('js/layouts/settings/Layout.vue')))
        ->toContain('bg-muted/20')
        ->toContain('rounded-panel')
        ->toContain('WorkspacePageHeader')
        ->toContain('max-w-app')
        ->and(File::get(resource_path('js/components/ui/card/Card.vue')))
        ->toContain('rounded-panel')
        ->toContain('border-border/80');

    expect(File::get(resource_path('js/components/AppLogoIcon.vue')))
        ->toContain('src="/favicon.svg"', 'alt=""', 'aria-hidden="true"')
        ->toMatch('/<img\b(?=[^>]*\bsrc\s*=\s*["\']\/favicon\.svg["\'])(?=[^>]*\balt\s*=\s*["\']["\'])(?=[^>]*:class\s*=\s*["\']className["\'])(?=[^>]*\baria-hidden\s*=\s*["\']true["\'])[^>]*>/s')
        ->not->toContain('<svg', 'fill="currentColor"', '<text');
});

test('the frontend is light only without dormant dark appearance branches', function () {
    foreach ([resource_path('js'), resource_path('css'), resource_path('views')] as $directory) {
        foreach (File::allFiles($directory) as $file) {
            if (! in_array($file->getExtension(), ['css', 'js', 'php', 'ts', 'vue'], true)) {
                continue;
            }

            expect($file->getContents(), $file->getRelativePathname())
                ->not->toContain('dark:')
                ->not->toContain("classList.add('dark')")
                ->not->toContain("classList.toggle('dark'");
        }
    }

    expect(File::get(resource_path('css/app.css')))
        ->toContain('color-scheme: light')
        ->not->toContain('@custom-variant dark')
        ->not->toContain('.dark {')
        ->and(File::get(resource_path('views/app.blade.php')))
        ->not->toContain('data-appearance')
        ->not->toContain('/theme.js');
});

test('shared motion primitives animate navigation surfaces and interactions safely', function () {
    $css = File::get(resource_path('css/app.css'));

    expect($css)
        ->toContain('--ease-emphasized:')
        ->toContain('@keyframes ui-enter')
        ->toContain('.ui-page-surface > *')
        ->toContain('.ui-surface')
        ->toContain('.ui-stagger > *')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->and(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('ui-control')
        ->and(File::get(resource_path('js/components/ui/card/Card.vue')))
        ->toContain('ui-surface')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarInset.vue')))
        ->toContain('ui-page-surface')
        ->and(File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue')))
        ->toContain('ui-page-surface')
        ->and(File::get(resource_path('js/components/shared/WorkspacePageHeader.vue')))
        ->toContain('ui-enter')
        ->and(File::get(resource_path('js/pages/onboarding/Index.vue')))
        ->toContain('<Transition name="ui-step" mode="out-in">')
        ->toContain(':key="activeStep"');

    foreach ([
        'components/notification/NotificationFeed.vue',
        'components/task/TaskList.vue',
        'pages/projects/Index.vue',
        'pages/workspaces/Index.vue',
    ] as $path) {
        expect(File::get(resource_path("js/{$path}")), $path)
            ->toContain('ui-stagger');
    }
});

test('primary user actions pair localized labels with meaningful icons', function (string $path, string $icon) {
    expect(File::get(resource_path("js/{$path}")))
        ->toContain("{$icon}")
        ->toContain("<{$icon}");
})->with([
    'login' => ['pages/auth/Login.vue', 'LogIn'],
    'password reset request' => ['pages/auth/ForgotPassword.vue', 'Send'],
    'registration' => ['pages/auth/Register.vue', 'UserPlus'],
    'password reset' => ['pages/auth/ResetPassword.vue', 'KeyRound'],
    'password confirmation' => ['pages/auth/ConfirmPassword.vue', 'ShieldCheck'],
    'two factor challenge' => ['pages/auth/TwoFactorChallenge.vue', 'ShieldCheck'],
    'passkey registration' => ['components/PasskeyRegister.vue', 'KeyRound'],
    'task creation' => ['components/task/TaskCreateDialog.vue', 'Plus'],
    'task editing' => ['components/task/TaskOverviewPanel.vue', 'Save'],
    'task taxonomy' => ['components/task/TaskTaxonomyPanel.vue', 'Tag'],
    'preferences save' => ['pages/settings/Preferences.vue', 'Save'],
    'notifications save' => ['pages/settings/Notifications.vue', 'Save'],
]);

test('authenticated content delegates the sole main landmark to the persistent shell', function () {
    $shell = File::get(resource_path('js/components/ui/sidebar/SidebarInset.vue'));

    expect($shell)->toContain('<main');

    foreach ([
        'layouts/settings/Layout.vue',
        'pages/Dashboard.vue',
        'pages/activity/Index.vue',
        'pages/calendar/Index.vue',
        'pages/projects/Index.vue',
        'pages/projects/Show.vue',
        'pages/tasks/Index.vue',
        'pages/tasks/Show.vue',
        'pages/workspaces/Index.vue',
        'pages/workspaces/Show.vue',
        'pages/onboarding/Index.vue',
    ] as $path) {
        expect(File::get(resource_path("js/{$path}")), $path)
            ->not->toContain('<main')
            ->not->toContain('</main>');
    }
});

test('guest authentication content is contained by one main landmark', function () {
    $layout = File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue'));

    expect(substr_count($layout, '<main'))->toBe(1)
        ->and(substr_count($layout, '</main>'))->toBe(1);
});

test('guided onboarding follows the warm responsive route design contract', function () {
    $page = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));

    expect($page)
        ->toContain('bg-muted/20')
        ->toContain('max-w-app')
        ->toContain('OnboardingShell')
        ->and($shell)
        ->toContain('xl:grid-cols-[minmax(15rem,0.34fr)_minmax(0,1fr)]')
        ->toContain('rounded-panel')
        ->toContain('border-border/80')
        ->toContain('bg-card')
        ->toContain('text-orange-')
        ->not->toContain('dark:')
        ->not->toContain('bg-gradient');
});

test('reusable task detail content does not introduce a second page heading', function () {
    expect(File::get(resource_path('js/components/task/TaskDetailContent.vue')))
        ->not->toContain('<h1')
        ->not->toContain('</h1>')
        ->toContain('<h2');
});

test('dormant layouts delegate to the canonical projects style shells', function () {
    expect(File::get(resource_path('js/layouts/auth/AuthCardLayout.vue')))
        ->toContain('AuthSimpleLayout')
        ->not->toContain('bg-muted p-6')
        ->and(File::get(resource_path('js/layouts/auth/AuthSplitLayout.vue')))
        ->toContain('AuthSimpleLayout')
        ->not->toContain('bg-zinc-900')
        ->and(File::get(resource_path('js/layouts/app/AppHeaderLayout.vue')))
        ->toContain('AppSidebarLayout')
        ->not->toContain('variant="header"');
});

test('the shared state surface supports accessible loading and error variants', function () {
    expect(File::get(resource_path('js/components/shared/EmptyState.vue')))
        ->toContain("type EmptyStateStatus = 'empty' | 'loading' | 'error'")
        ->toContain('aria-busy')
        ->toContain(':role=')
        ->toContain("? 'alert'")
        ->toContain('<Skeleton')
        ->toContain('<LoaderCircle')
        ->toContain('<AlertTriangle');
});

test('remaining active secondary actions reuse the shared large control rhythm', function () {
    $emptyState = File::get(resource_path('js/components/shared/EmptyState.vue'));
    $calendarNavigator = File::get(
        resource_path('js/components/calendar/CalendarPeriodNavigator.vue'),
    );
    $taskDetail = File::get(resource_path('js/components/task/TaskDetailContent.vue'));

    expect($emptyState)
        ->toContain('size="lg"')
        ->not->toContain('bg-orange-600 text-white hover:bg-orange-700')
        ->and($calendarNavigator)
        ->toContain('size="lg"')
        ->not->toContain('class="min-h-11 cursor-pointer rounded-xl"')
        ->and(substr_count($taskDetail, 'size="lg"'))
        ->toBeGreaterThanOrEqual(2)
        ->and($taskDetail)
        ->not->toContain('class="h-10 rounded-xl text-sm"')
        ->not->toContain('class="min-h-11 rounded-xl"');
});

test('data import exposes inert preview and execution loading states', function () {
    expect(File::get(resource_path('js/pages/settings/Export.vue')))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('const previewRequest = useHttp<ImportPayload, ImportPreviewResponse>')
        ->toContain('const importRequest = useHttp<ImportPayload, ImportResponse>')
        ->toContain('previewRequest.processing ||')
        ->toContain(':aria-busy="importRequest.processing"')
        ->toContain('<Spinner v-if="importRequest.processing" />')
        ->toContain('previewRequest.progress || importRequest.progress')
        ->toContain('pointer-events-none opacity-50');
});

test('autosave uses lifecycle safe Vue watcher cleanup', function () {
    expect(File::get(resource_path('js/composables/useAutosave.ts')))
        ->toContain('onCleanup')
        ->toContain('clearTimeout(timeoutId)')
        ->not->toContain("from '@vueuse/core'")
        ->not->toContain('debouncedSave.cancel');
});

test('workspace dialogs preserve the projects visual contract on every viewport', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceDialogContent.vue')))
        ->toContain('rounded-feature')
        ->toContain('max-h-[calc(100svh-1.5rem)]')
        ->toContain('overflow-y-auto')
        ->toContain('inset-y-0 left-0 w-1.5')
        ->toContain('border-orange-500/20')
        ->and(File::get(resource_path('js/components/shared/WorkspaceConfirmDialog.vue')))
        ->toContain('WorkspaceDialogContent')
        ->toContain("accent=\"destructive ? 'red' : 'orange'\"");
});

test('feature dialogs use the shared projects style dialog surface', function (string $file) {
    expect(File::get(resource_path("js/{$file}")))
        ->toContain('WorkspaceDialogContent')
        ->not->toContain('<DialogContent');
})->with([
    'project create' => 'components/project/ProjectCreateDialog.vue',
    'task create' => 'components/task/TaskCreateDialog.vue',
    'workspace create' => 'pages/workspaces/Index.vue',
    'delete account' => 'components/DeleteUser.vue',
    'remove passkey' => 'components/PasskeyItem.vue',
    'two factor setup' => 'components/TwoFactorSetupModal.vue',
    'remove member' => 'pages/settings/Members.vue',
]);

test('active create dialogs reuse shared controls for processing states', function (string $file) {
    expect(File::get(resource_path("js/components/{$file}")))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="form.processing" />')
        ->toContain('size="lg"')
        ->not->toContain('class="h-11 rounded-xl"')
        ->not->toContain('bg-orange-600 text-white hover:bg-orange-700');
})->with([
    'project create' => 'project/ProjectCreateDialog.vue',
    'task create' => 'task/TaskCreateDialog.vue',
]);

test('workspace creation reuses shared controls for complete processing states', function () {
    expect(File::get(resource_path('js/pages/workspaces/Index.vue')))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="form.processing" />')
        ->toContain(':aria-invalid="Boolean(form.errors.description)"')
        ->toContain(':disabled="form.processing"')
        ->toContain('size="lg"')
        ->not->toContain('class="h-11 rounded-xl"')
        ->not->toContain('bg-orange-600 text-white hover:bg-orange-700');
});

test('workspace portfolio exposes complete management actions with shared dialog surfaces', function () {
    expect(File::get(resource_path('js/pages/workspaces/Index.vue')))
        ->toContain('WorkspaceConfirmDialog')
        ->toContain('editWorkspace')
        ->toContain('duplicateWorkspace')
        ->toContain('deleteWorkspace')
        ->toContain('switchWorkspace')
        ->toContain("t('workspaces.actions.manage')")
        ->toContain("t('workspaces.actions.edit')")
        ->toContain("t('workspaces.actions.duplicate')")
        ->toContain("t('workspaces.actions.delete')")
        ->toContain(':confirmation-text="deletingWorkspace?.name"')
        ->toContain('members: formatNumber(')
        ->toContain('projects: formatNumber(')
        ->toContain('tasks: formatNumber(')
        ->and(File::get(resource_path('js/components/shared/WorkspaceConfirmDialog.vue')))
        ->toContain('confirmationText?: string')
        ->toContain('confirmationValue.value === props.confirmationText')
        ->toContain(':disabled="processing || !confirmationMatches"');
});

test('settings save forms reuse shared large loading actions', function (string $page) {
    expect(File::get(resource_path("js/pages/settings/{$page}.vue")))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="form.processing" />')
        ->toContain(':disabled="form.processing"')
        ->toContain('size="lg"')
        ->not->toContain('bg-orange-600 text-white hover:bg-orange-700');
})->with([
    'preferences' => 'Preferences',
    'notifications' => 'Notifications',
]);

test('notification option copy keeps a readable mobile hierarchy', function () {
    expect(File::get(resource_path('js/pages/settings/Notifications.vue')))
        ->toContain('flex-col items-start gap-0')
        ->toContain('leading-5')
        ->toContain('text-muted-foreground');
});

test('member actions reuse shared loading and large dialog controls', function () {
    expect(File::get(resource_path('js/pages/settings/Members.vue')))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="inviteForm.processing" />')
        ->toContain('<Spinner v-if="removeForm.processing" />')
        ->toContain(':disabled="inviteForm.processing"')
        ->toContain('size="lg"')
        ->not->toContain('LoaderCircle')
        ->not->toContain('class="min-h-11 cursor-pointer rounded-xl"');
});

test('shared confirmations use the shared large loading action contract', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceConfirmDialog.vue')))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="processing" />')
        ->toContain('size="lg"')
        ->not->toContain('class="min-h-11 cursor-pointer rounded-xl"')
        ->not->toContain('bg-orange-600 text-white hover:bg-orange-700');
});

test('account deletion uses inert processing and shared loading feedback', function () {
    expect(File::get(resource_path('js/components/DeleteUser.vue')))
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('disable-while-processing')
        ->toContain('<Spinner v-if="processing" />');
});

test('remaining settings forms expose complete processing states', function () {
    $backup = File::get(resource_path('js/pages/settings/Backup.vue'));
    $profile = File::get(resource_path('js/pages/settings/Profile.vue'));
    $security = File::get(resource_path('js/pages/settings/Security.vue'));

    expect($backup)
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="creating" />')
        ->toContain('size="lg"')
        ->and($profile)
        ->toContain('size="lg"')
        ->and(substr_count($profile, ':disabled="profileForm.processing"'))
        ->toBeGreaterThanOrEqual(2)
        ->and(substr_count($security, ':disabled="passwordForm.processing"'))
        ->toBeGreaterThanOrEqual(4)
        ->and(substr_count($security, 'size="lg"'))
        ->toBeGreaterThanOrEqual(3);
});

test('task editing uses shared loading actions and locks mutable fields', function () {
    $source = File::get(resource_path('js/components/task/TaskOverviewPanel.vue'));

    expect($source)
        ->toContain("import { Spinner } from '@/components/ui/spinner'")
        ->toContain('<Spinner v-if="form.processing" />')
        ->not->toContain('LoaderCircle')
        ->and(substr_count($source, 'form.processing'))
        ->toBeGreaterThanOrEqual(7);
});

test('project creation selectors expose warm precision interaction states', function () {
    expect(File::get(resource_path('js/components/project/ProjectCreateDialog.vue')))
        ->toContain(':aria-invalid="Boolean(form.errors.description)"')
        ->toContain('border-orange-500/50 bg-orange-500/[0.08] shadow-sm')
        ->toContain('motion-reduce:transition-none')
        ->toContain(':disabled="form.processing"');
});

test('task creation fields expose complete invalid and disabled states', function () {
    expect(File::get(resource_path('js/components/task/TaskCreateDialog.vue')))
        ->toContain(':aria-invalid="Boolean(form.errors.description)"')
        ->toContain(':aria-invalid="Boolean(form.errors.priority)"')
        ->toContain(':aria-invalid="Boolean(form.errors.due_date)"')
        ->toContain('form.errors.recurring_rule')
        ->toContain(':disabled="!form.is_recurring || form.processing"')
        ->toContain('<InputError :message="form.errors.priority" />')
        ->toContain('id="due-date-error"')
        ->toContain(':message="form.errors.due_date"')
        ->toContain('<InputError :message="form.errors.recurring_rule" />');
});

test('destructive actions use application confirmations instead of browser dialogs', function (string $file) {
    expect(File::get(resource_path("js/{$file}")))
        ->toContain('WorkspaceConfirmDialog')
        ->not->toContain('confirm(');
})->with([
    'task list' => 'pages/tasks/Index.vue',
    'project task list' => 'pages/projects/Show.vue',
    'task detail' => 'components/task/TaskDetailContent.vue',
    'backup restore' => 'pages/settings/Backup.vue',
    'two factor disable' => 'pages/settings/Security.vue',
]);

test('task interfaces use accessible application controls', function () {
    expect(File::get(resource_path('js/components/task/TaskDetail.vue')))
        ->toContain('<Sheet')
        ->not->toContain('<Teleport')
        ->and(File::get(resource_path('js/components/task/TaskChecklistPanel.vue')))
        ->toContain('<Checkbox')
        ->not->toContain('type="checkbox"')
        ->and(File::get(resource_path('js/components/task/TaskCreateDialog.vue')))
        ->toContain('<Checkbox')
        ->not->toContain('type="checkbox"')
        ->and(File::get(resource_path('js/components/task/TaskList.vue')))
        ->toContain('<Checkbox')
        ->not->toContain('type="checkbox"')
        ->and(File::get(resource_path('js/components/project/ProjectTaskQueue.vue')))
        ->toContain('<Checkbox')
        ->not->toContain('type="checkbox"');
});

test('task rows use progressive semantic actions without a nested full-row overlay', function (string $file) {
    expect(File::get(resource_path("js/{$file}")))
        ->toContain('selectionMode')
        ->toContain('DropdownMenu')
        ->toContain('rememberActionTrigger')
        ->toContain('actionTriggers.get(todo.id)')
        ->toContain("t('tasks.index.open_task'")
        ->toContain('min-h-11')
        ->toContain('whitespace-normal')
        ->toContain('break-all')
        ->toContain('focus-visible:ring-orange-500')
        ->not->toContain('absolute inset-0 z-10');
})->with([
    'task index list' => 'components/task/TaskList.vue',
]);

test('task focus desk discloses active filters and selection state', function () {
    expect(File::get(resource_path('js/components/task/TaskFilterBar.vue')))
        ->toContain('activeTaskFilterCount')
        ->toContain(':aria-pressed="Boolean(focusFilters[option.key])"')
        ->toContain('tasks.filters.active_count')
        ->toContain('class="min-h-11"')
        ->not->toContain('class="min-h-9"')
        ->and(File::get(resource_path('js/components/task/TaskResultsBar.vue')))
        ->toContain('aria-live="polite"')
        ->toContain('selectionMode')
        ->toContain('min-h-11')
        ->toContain('pagination.meta.from')
        ->toContain('pagination.meta.total')
        ->and(File::get(resource_path('js/components/task/TaskPagination.vue')))
        ->toContain('class="min-h-11"')
        ->toContain('pagination.meta.last_page')
        ->toContain('pagination.links.prev')
        ->and(File::get(resource_path('js/components/task/BoardView.vue')))
        ->toContain('class="mt-3 min-h-11 w-full text-xs"')
        ->not->toContain('class="mt-3 h-8 w-full text-xs"')
        ->and(File::get(resource_path('js/components/task/TaskWorkspacePanel.vue')))
        ->toContain('TaskResultsBar')
        ->toContain('selectionMode')
        ->and(File::get(resource_path('js/pages/tasks/Index.vue')))
        ->toContain('TaskWorkspacePanel')
        ->toContain('selectionMode')
        ->toContain(':aria-busy="filtering"');
});

test('project task rows use one semantic keyboard target without nested overlays', function () {
    expect(File::get(resource_path('js/components/project/ProjectTaskQueue.vue')))
        ->toContain("t('projects.show.actions.open_task'")
        ->toContain('focus-visible:ring-2')
        ->toContain('<Checkbox')
        ->not->toContain('absolute inset-0 z-10');
});

test('segmented controls use the projects muted and card surface contract', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceSegmentedControl.vue')))
        ->toContain('rounded-xl bg-muted p-1')
        ->and(File::get(resource_path('js/components/shared/WorkspaceSegmentedButton.vue')))
        ->toContain("'bg-card text-foreground shadow-sm'")
        ->not->toContain("'bg-foreground text-background'");
});

test('active filter pages reuse the shared segmented controls', function (string $component) {
    expect(File::get(resource_path("js/{$component}")))
        ->toContain('WorkspaceSegmentedControl')
        ->toContain('WorkspaceSegmentedButton');
})->with([
    'calendar view switcher' => 'components/calendar/CalendarPeriodNavigator.vue',
    'notification filters' => 'components/notification/NotificationFilters.vue',
    'project filters' => 'pages/projects/Index.vue',
]);

test('shared segmented controls preserve the projects visual and accessibility contract', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceSegmentedControl.vue')))
        ->toContain("role?: 'group' | 'tablist'")
        ->toContain(':aria-label="label"')
        ->toContain('overflow-x-auto rounded-xl bg-muted p-1')
        ->toContain("'w-full lg:flex-col'")
        ->and(File::get(resource_path('js/components/shared/WorkspaceSegmentedButton.vue')))
        ->toContain('min-h-10 min-w-max')
        ->toContain("'bg-card text-foreground shadow-sm'")
        ->toContain('focus-visible:ring-orange-500')
        ->toContain('motion-reduce:transition-none');
});

test('shared segmented filter consumers keep the correct selection semantics', function () {
    $calendarNavigator = File::get(
        resource_path('js/components/calendar/CalendarPeriodNavigator.vue'),
    );

    expect(File::get(resource_path('js/pages/projects/Index.vue')))
        ->toContain('role="tab"')
        ->toContain(':aria-selected="activeFilter === filter.value"')
        ->and(File::get(resource_path('js/components/notification/NotificationFilters.vue')))
        ->toContain(':aria-pressed="filters.status === option.value"')
        ->toContain(':aria-pressed="filters.kind === option.value"')
        ->and($calendarNavigator)
        ->toContain(':label="copy.common.filters"')
        ->toContain('role="tab"')
        ->toContain(':aria-selected="calendar.view === option"');
});

test('calendar planning workspace uses URL state and focused accessible components', function () {
    $page = File::get(resource_path('js/pages/calendar/Index.vue'));
    $navigator = File::get(resource_path('js/components/calendar/CalendarPeriodNavigator.vue'));
    $month = File::get(resource_path('js/components/calendar/CalendarMonthGrid.vue'));
    $attention = File::get(resource_path('js/components/calendar/CalendarAttentionRail.vue'));

    expect($page)
        ->toContain('router')
        ->toContain("import { calendar as calendarRoute } from '@/routes'")
        ->toContain('CalendarPeriodNavigator')
        ->toContain('CalendarMonthGrid')
        ->toContain('CalendarWeekView')
        ->toContain('CalendarAgendaView')
        ->toContain('CalendarAttentionRail')
        ->toContain('preserveScroll: true')
        ->toContain('replace: true')
        ->not->toContain('const currentDate = ref(new Date())')
        ->not->toContain("const view = ref<CalendarView>('month')")
        ->and($navigator)
        ->toContain('aria-busy')
        ->toContain('min-h-11')
        ->toContain('focus-visible:ring-orange-500')
        ->toContain('motion-reduce:transition-none')
        ->and($month)
        ->toContain('md:hidden')
        ->toContain('md:grid')
        ->toContain('min-h-11')
        ->toContain('prefetch')
        ->and($attention)
        ->toContain('todoIndex')
        ->toContain('overdue: true')
        ->toContain('prefetch');
});

test('notification surfaces expose server pagination direct links and honest browser limits', function () {
    $inbox = File::get(resource_path('js/pages/notifications/Index.vue'));
    $feed = File::get(resource_path('js/components/notification/NotificationFeed.vue'));
    $row = File::get(resource_path('js/components/notification/NotificationRow.vue'));
    $settings = File::get(resource_path('js/pages/settings/Notifications.vue'));

    expect($inbox)
        ->toContain("only: ['notifications', 'stats', 'filters', 'today']")
        ->toContain('openNotification(notification)')
        ->toContain('!notification.browser_delivery')
        ->toContain('window.localStorage.getItem(storageKey)')
        ->and($feed)
        ->toContain('notifications.links.next')
        ->toContain('notifications.links.prev')
        ->and($row)
        ->toContain('v-if="notification.url"')
        ->and($settings)
        ->toContain('window.Notification.requestPermission()')
        ->toContain('settings.notifications.browser_live_only');
});

test('shared authentication controls use the warm precision interaction contract', function () {
    expect(File::get(resource_path('js/components/PasswordInput.vue')))
        ->toContain('focus-visible:ring-orange-500/25')
        ->toContain(':aria-pressed="showPassword"')
        ->toContain("'auth.common.show_password'")
        ->toContain("'auth.common.hide_password'")
        ->not->toContain(':tabindex="-1"')
        ->and(File::get(resource_path('js/components/TextLink.vue')))
        ->toContain('text-orange-700')
        ->toContain('focus-visible:ring-orange-500')
        ->not->toContain('decoration-neutral')
        ->and(File::get(resource_path('js/pages/auth/Login.vue')))
        ->not->toContain('tabindex=')
        ->and(File::get(resource_path('js/pages/auth/Register.vue')))
        ->not->toContain('tabindex=')
        ->and(File::get(resource_path('js/pages/auth/TwoFactorChallenge.vue')))
        ->toContain('focus-visible:ring-orange-500')
        ->not->toContain('decoration-neutral')
        ->and(File::get(resource_path('js/components/ui/input-otp/InputOTPSlot.vue')))
        ->toContain('data-[active=true]:border-orange-500')
        ->toContain('data-[active=true]:ring-orange-500/20')
        ->toContain('first:rounded-l-xl')
        ->toContain('motion-reduce:animate-none');
});

test('authentication submissions share the large projects action rhythm', function (string $page) {
    expect(File::get(resource_path("js/pages/auth/{$page}.vue")))
        ->toContain('disable-while-processing')
        ->toContain('size="lg"')
        ->toContain('<Spinner v-if="processing" />');
})->with([
    'login' => 'Login',
    'registration' => 'Register',
    'forgot password' => 'ForgotPassword',
    'reset password' => 'ResetPassword',
    'password confirmation' => 'ConfirmPassword',
    'two factor challenge' => 'TwoFactorChallenge',
]);

test('authentication credential controls expose invalid state', function (string $page) {
    expect(File::get(resource_path("js/pages/auth/{$page}.vue")))
        ->toContain(':aria-invalid="Boolean(errors.');
})->with([
    'login' => 'Login',
    'registration' => 'Register',
    'forgot password' => 'ForgotPassword',
    'reset password' => 'ResetPassword',
    'password confirmation' => 'ConfirmPassword',
    'two factor challenge' => 'TwoFactorChallenge',
]);

test('passkey verification uses the shared large loading action', function () {
    expect(File::get(resource_path('js/components/PasskeyVerify.vue')))
        ->toContain('size="lg"')
        ->toContain('<Spinner v-if="isLoading" />')
        ->toContain(':disabled="isLoading"');
});

test('shared navigation feedback uses localized labels and the projects orange accent', function () {
    expect(File::get(resource_path('js/app.ts')))
        ->toContain("color: '#FF6038'")
        ->not->toContain("color: '#4B5563'")
        ->and(File::get(resource_path('js/components/ui/spinner/Spinner.vue')))
        ->toContain("t('common.states.loading')")
        ->and(File::get(resource_path('js/components/ui/breadcrumb/Breadcrumb.vue')))
        ->toContain("t('common.navigation.breadcrumb')")
        ->and(File::get(resource_path('js/components/ui/breadcrumb/BreadcrumbEllipsis.vue')))
        ->toContain("t('common.navigation.more')")
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarTrigger.vue')))
        ->toContain("t('common.navigation.toggle_sidebar')")
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarRail.vue')))
        ->toContain("t('common.navigation.toggle_sidebar')")
        ->toContain('hover:after:bg-orange-500')
        ->and(File::get(resource_path('js/components/UserInfo.vue')))
        ->toContain('bg-orange-500/10')
        ->toContain('text-orange-800');
});

test('shared transient surfaces use the warm precision interaction contract', function () {
    expect(File::get(resource_path('js/components/ui/dropdown-menu/DropdownMenuContent.vue')))
        ->toContain('rounded-xl')
        ->toContain('border-border/80')
        ->toContain('motion-reduce:data-[state=open]:animate-none')
        ->and(File::get(resource_path('js/components/ui/dropdown-menu/DropdownMenuItem.vue')))
        ->toContain('min-h-10')
        ->toContain('focus:bg-orange-500/10')
        ->and(File::get(resource_path('js/components/ui/select/SelectContent.vue')))
        ->toContain('rounded-xl')
        ->toContain('border-border/80')
        ->and(File::get(resource_path('js/components/ui/select/SelectItem.vue')))
        ->toContain('min-h-10')
        ->toContain('focus:bg-orange-500/10')
        ->toContain('text-orange-600')
        ->and(File::get(resource_path('js/components/ui/tooltip/TooltipContent.vue')))
        ->toContain('rounded-lg')
        ->toContain('border-orange-500/15')
        ->toContain('motion-reduce:animate-none');
});

test('shared controls use warm checked focus and feedback states', function () {
    expect(File::get(resource_path('js/components/ui/checkbox/Checkbox.vue')))
        ->toContain('data-[state=checked]:bg-orange-600')
        ->toContain('data-[state=checked]:text-white')
        ->toContain('focus-visible:ring-orange-500/25')
        ->toContain('rounded-md')
        ->toContain('motion-reduce:transition-none')
        ->and(File::get(resource_path('js/components/ui/alert/index.ts')))
        ->toContain('rounded-xl')
        ->toContain('border-border/80')
        ->toContain('border-destructive/20')
        ->toContain('bg-destructive/[0.06]')
        ->and(File::get(resource_path('js/components/ui/alert/AlertTitle.vue')))
        ->not->toContain('line-clamp-1')
        ->and(File::get(resource_path('js/components/ui/badge/index.ts')))
        ->toContain('bg-orange-600 text-white')
        ->toContain('focus-visible:ring-orange-500/25')
        ->toContain('hover:border-orange-500/25')
        ->toContain('motion-reduce:transition-none')
        ->and(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('bg-orange-600 text-white')
        ->toContain('motion-reduce:transition-none');
});

test('shared form feedback uses semantic warm precision states', function () {
    expect(File::get(resource_path('js/components/ui/alert/index.ts')))
        ->toContain('border-emerald-500/20')
        ->toContain('bg-emerald-500/[0.07]')
        ->toContain('border-amber-500/25')
        ->toContain('bg-amber-500/[0.08]')
        ->and(File::get(resource_path('js/components/ui/alert/Alert.vue')))
        ->toContain('props.variant === "success" ? "status" : "alert"')
        ->and(File::get(resource_path('js/components/InputError.vue')))
        ->toContain('CircleAlert')
        ->toContain('text-destructive')
        ->toContain('role="alert"')
        ->toContain('aria-live="polite"')
        ->not->toContain('text-red-');
});

test('authentication status messages use the shared success surface', function (string $page) {
    expect(File::get(resource_path("js/pages/auth/{$page}.vue")))
        ->toContain('<Alert')
        ->toContain('variant="success"')
        ->toContain('<AlertDescription')
        ->not->toContain('text-green-600');
})->with([
    'login' => 'Login',
    'forgot password' => 'ForgotPassword',
]);

test('profile feedback uses semantic alerts and deterministic upload progress', function () {
    expect(File::get(resource_path('js/pages/settings/Profile.vue')))
        ->toContain('variant="success"')
        ->toContain('role="progressbar"')
        ->toContain('bg-orange-600')
        ->toContain('motion-reduce:transition-none')
        ->not->toContain('sendVerification')
        ->not->toContain('email_verified')
        ->not->toContain('<progress')
        ->not->toContain('bg-amber-50')
        ->and(File::get(resource_path('js/components/DeleteUser.vue')))
        ->toContain('<Alert variant="destructive">')
        ->not->toContain('bg-red-50');
});

test('active forms reuse shared field errors', function (string $component) {
    expect(File::get(resource_path("js/{$component}")))
        ->toContain('InputError')
        ->not->toContain('class="text-sm text-destructive"');
})->with([
    'task edit form' => 'components/task/TaskOverviewPanel.vue',
    'member invitation form' => 'pages/settings/Members.vue',
    'security form' => 'pages/settings/Security.vue',
]);

test('security page consumes the dedicated two factor feature props', function () {
    expect(File::get(resource_path('js/pages/settings/Security.vue')))
        ->toContain('canManageTwoFactor: boolean')
        ->toContain('twoFactorEnabled?: boolean')
        ->toContain('<Card v-if="canManageTwoFactor">')
        ->toContain('v-if="twoFactorEnabled"')
        ->not->toContain('user.two_factor_enabled');
});

test('shared and page loading states respect reduced motion', function (string $component) {
    expect(File::get(resource_path("js/{$component}")))
        ->toContain('motion-reduce:animate-none');
})->with([
    'shared spinner' => 'components/ui/spinner/Spinner.vue',
    'shared skeleton' => 'components/ui/skeleton/Skeleton.vue',
    'empty state' => 'components/shared/EmptyState.vue',
    'workspace switcher' => 'components/workspace/WorkspaceSwitcher.vue',
    'two factor setup' => 'components/TwoFactorSetupModal.vue',
    'two factor recovery codes' => 'components/TwoFactorRecoveryCodes.vue',
]);

test('segmented and inline controls respect reduced motion', function (string $component) {
    expect(File::get(resource_path("js/{$component}")))
        ->toContain('motion-reduce:transition-none');
})->with([
    'shared segmented button' => 'components/shared/WorkspaceSegmentedButton.vue',
    'settings navigation' => 'components/settings/SettingsSectionMenu.vue',
    'two factor challenge toggle' => 'pages/auth/TwoFactorChallenge.vue',
]);

test('security feedback surfaces use the shared warm card treatment', function () {
    expect(File::get(resource_path('js/components/TwoFactorSetupModal.vue')))
        ->toContain('rounded-2xl border border-border/80 bg-card')
        ->toContain('shadow-[0_16px_45px_-32px_rgba(255,96,56,0.5)]')
        ->and(File::get(resource_path('js/components/TwoFactorRecoveryCodes.vue')))
        ->toContain('rounded-xl border border-border/80 bg-muted/50')
        ->and(File::get(resource_path('js/components/PasskeyItem.vue')))
        ->toContain('rounded-lg border border-border/80 bg-muted/60');
});

test('shared overlays and feedback surfaces use warm focus and reduced motion', function () {
    expect(File::get(resource_path('js/components/ui/dialog/DialogContent.vue')))
        ->toContain('bg-card')
        ->toContain('focus-visible:ring-orange-500')
        ->toContain("t('common.actions.close')")
        ->and(File::get(resource_path('js/components/ui/dialog/DialogOverlay.vue')))
        ->toContain('bg-black/65')
        ->toContain('backdrop-blur-[2px]')
        ->toContain('motion-reduce:data-[state=open]:animate-none')
        ->and(File::get(resource_path('js/components/ui/sheet/SheetContent.vue')))
        ->toContain('bg-card')
        ->toContain('focus-visible:ring-orange-500')
        ->toContain("closeLabel ?? t('common.actions.close')")
        ->and(File::get(resource_path('js/components/ui/sheet/SheetOverlay.vue')))
        ->toContain('bg-black/65')
        ->toContain('backdrop-blur-[2px]')
        ->and(File::get(resource_path('js/components/ui/sonner/Sonner.vue')))
        ->toContain('theme="light"')
        ->toContain('0 24px 70px -36px')
        ->toContain('motion-reduce:animate-none');
});

test('shared transient accessibility copy uses semantic translations', function () {
    expect(File::get(resource_path('js/components/ui/sidebar/Sidebar.vue')))
        ->toContain("t('common.navigation.sidebar')")
        ->toContain("t('common.navigation.sidebar_description')")
        ->not->toContain('<SheetTitle>Sidebar</SheetTitle>')
        ->and(File::get(resource_path('js/components/ui/sonner/Sonner.vue')))
        ->toContain("t('common.toast.notifications')")
        ->toContain('t("common.toast.close")');
});

test('shared interaction accessibility copy exists in every supported language', function (string $locale) {
    $copy = require lang_path("{$locale}/ui.php");

    expect(data_get($copy, 'common.navigation.breadcrumb'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.navigation.toggle_sidebar'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.navigation.more'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.navigation.sidebar'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.navigation.sidebar_description'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.states.loading'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'auth.common.show_password'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'auth.common.hide_password'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.toast.close'))
        ->toBeString()
        ->not->toBeEmpty()
        ->and(data_get($copy, 'common.toast.notifications'))
        ->toBeString()
        ->not->toBeEmpty();
})->with(['en', 'lt', 'ru']);

test('list pages share the warm precision empty state', function (string $source) {
    expect(File::get(resource_path("js/{$source}")))
        ->toContain('EmptyState');
})->with([
    'notifications' => 'components/notification/NotificationFeed.vue',
    'projects' => 'pages/projects/Index.vue',
    'tasks' => 'components/task/TaskWorkspacePanel.vue',
    'workspaces' => 'pages/workspaces/Index.vue',
    'backups' => 'pages/settings/Backup.vue',
]);

test('project operations queue owns the warm precision empty states', function () {
    expect(File::get(resource_path('js/components/project/ProjectTaskQueue.vue')))
        ->toContain('EmptyState')
        ->toContain("t('projects.show.empty_filtered')")
        ->toContain("t('projects.show.empty')");
});

test('guest authentication uses the same left rail hierarchy as projects', function () {
    expect(File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue')))
        ->toContain('inset-y-0 left-0 w-1.5 bg-orange-500')
        ->toContain('tracking-[0.16em]')
        ->not->toContain('inset-x-0 top-0 h-1.5');
});
