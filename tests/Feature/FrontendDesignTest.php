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
    'workspace configuration' => ['components/workspace/WorkspaceConfigurationPanel.vue', 3],
    'workspace danger panel' => ['components/workspace/WorkspaceDangerPanel.vue', 3],
    'workspace members panel' => ['components/workspace/WorkspaceMembersPanel.vue', 4],
    'workspace overview panel' => ['components/workspace/WorkspaceOverviewPanel.vue', 1],
    'settings members page' => ['pages/settings/Members.vue', 2],
    'settings profile page' => ['pages/settings/Profile.vue', 2],
    'settings preferences page' => ['pages/settings/Preferences.vue', 3],
    'settings backup page' => ['pages/settings/Backup.vue', 1],
    'settings export page' => ['pages/settings/Export.vue', 3],
    'settings security page' => ['pages/settings/Security.vue', 2],
]);

dataset('workspace page frame consumers', [
    'dashboard' => 'pages/Dashboard.vue',
    'activity' => 'pages/activity/Index.vue',
    'calendar' => 'pages/calendar/Index.vue',
    'notifications' => 'pages/notifications/Index.vue',
    'project index' => 'pages/projects/Index.vue',
    'project detail' => 'pages/projects/Show.vue',
    'task index' => 'pages/tasks/Index.vue',
    'task detail' => 'pages/tasks/Show.vue',
    'workspace index' => 'pages/workspaces/Index.vue',
    'workspace detail' => 'pages/workspaces/Show.vue',
    'guided onboarding' => 'pages/onboarding/Index.vue',
    'settings layout' => 'layouts/settings/Layout.vue',
]);

test('leading icon headings keep the icon top aligned beside a wrapping text stack', function () {
    expect(File::get(resource_path('js/components/shared/LeadingIconHeading.vue')))
        ->toContain('data-slot="leading-icon-heading"')
        ->toContain('flex-nowrap')
        ->toContain('items-start')
        ->toContain('data-slot="leading-icon-heading-icon"')
        ->toContain('shrink-0')
        ->toContain('data-slot="leading-icon-heading-content"')
        ->toContain('min-w-0 flex-1')
        ->not->toContain('items-center')
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
        ->toContain('WorkspacePageFrame');
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
        ->toContain('WorkspacePageFrame')
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
        ->toContain('function barScale(value: number): number')
        ->toContain('value === 0 ? 0')
        ->toContain('scale-y-[var(--bar-scale)]')
        ->toContain('transition-transform')
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
        ->toContain('WorkspacePageFrame')
        ->toContain('rounded-panel')
        ->toContain('WorkspacePageHeader')
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

test('shared motion primitives keep generic surfaces static and interactions measured', function () {
    $css = File::get(resource_path('css/app.css'));

    expect($css)
        ->toContain('--motion-snap: 90ms;')
        ->toContain('--motion-feedback: 130ms;')
        ->toContain('--motion-state: 190ms;')
        ->toContain('--motion-spatial: 260ms;')
        ->toContain('--motion-signature: 340ms;')
        ->not->toContain('@keyframes ui-enter')
        ->not->toContain('.ui-page-surface > *')
        ->not->toContain('.ui-surface')
        ->not->toContain('.ui-stagger > *')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->and(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('ui-control')
        ->and(File::get(resource_path('js/components/ui/card/Card.vue')))
        ->not->toContain('ui-surface')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarInset.vue')))
        ->not->toContain('ui-page-surface')
        ->and(File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue')))
        ->not->toContain('ui-page-surface')
        ->and(File::get(resource_path('js/components/shared/WorkspacePageHeader.vue')))
        ->not->toContain('ui-enter')
        ->and(File::get(resource_path('js/pages/onboarding/Index.vue')))
        ->toContain('<Transition name="ui-step" mode="out-in">')
        ->toContain(':key="activeStep"');

    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true)) {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('ui-stagger')
            ->not->toContain('transition-all');
    }
});

test('the canonical stylesheet owns viewport safe responsive interaction primitives', function () {
    $css = File::get(resource_path('css/app.css'));

    expect($css)
        ->toContain('env(safe-area-inset-top, 0px)')
        ->toContain('env(safe-area-inset-right, 0px)')
        ->toContain('env(safe-area-inset-bottom, 0px)')
        ->toContain('env(safe-area-inset-left, 0px)')
        ->toContain('var(--inset-top, 0px)')
        ->toContain('var(--inset-right, 0px)')
        ->toContain('var(--inset-bottom, 0px)')
        ->toContain('var(--inset-left, 0px)')
        ->toContain('--page-safe-area-inset-top:')
        ->toContain('--page-safe-area-inset-right:')
        ->toContain('--page-safe-area-inset-bottom:')
        ->toContain('--page-safe-area-inset-left:')
        ->toContain('@media (orientation: portrait)')
        ->toContain('@media (orientation: landscape)')
        ->toContain('body.nativephp-safe-area')
        ->toContain('--page-gutter-inline: clamp(')
        ->toContain('--page-gutter-block: clamp(')
        ->toContain('.ui-page-frame')
        ->toContain('.ui-page-container')
        ->toContain('@media (hover: hover) and (pointer: fine)')
        ->toContain('@media (pointer: coarse)')
        ->toContain('min-height: 3rem')
        ->toContain('min-width: 3rem')
        ->toContain('@media (forced-colors: active)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('mobile toast viewport remains inside the physical and native safe area', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('@media (max-width: 37.5rem)')
        ->toContain('[data-sonner-toaster]')
        ->toContain('inset-inline:')
        ->toContain('var(--safe-area-inset-left)')
        ->toContain('var(--safe-area-inset-right)')
        ->toContain('width: auto !important')
        ->toContain('[data-sonner-toast]')
        ->toContain('width: 100% !important');
});

test('small sidebar and onboarding copy keeps accessible contrast', function () {
    expect(File::get(resource_path('js/components/ui/sidebar/SidebarGroupLabel.vue')))
        ->toContain('text-sidebar-foreground/75')
        ->not->toContain('text-sidebar-foreground/70')
        ->and(File::get(resource_path('js/components/AppSidebar.vue')))
        ->toContain('text-sidebar-foreground/75')
        ->not->toContain('text-sidebar-foreground/55')
        ->and(File::get(resource_path('js/components/workspace/WorkspaceSwitcher.vue')))
        ->toContain('text-sidebar-foreground/80')
        ->not->toContain('text-sidebar-foreground/50')
        ->and(File::get(resource_path('js/components/onboarding/OnboardingShell.vue')))
        ->toContain('text-foreground/75')
        ->and(File::get(resource_path('js/pages/onboarding/Index.vue')))
        ->toContain('<meta name="description" :content="activeCopy.description" />')
        ->and(File::get(resource_path('js/components/localization/LanguageSwitcher.vue')))
        ->toContain('useId')
        ->toContain(':aria-labelledby="`${labelId} ${codeId}`"')
        ->not->toContain(':aria-label="t(\'localization.switcher_label\')"')
        ->and(File::get(resource_path('views/app.blade.php')))
        ->toContain('name="description"')
        ->toContain("__('ui.meta.description')");
});

test('the frontend keeps one supported Tailwind CSS first build boundary', function () {
    $stylesheets = collect(File::allFiles(resource_path()))
        ->filter(fn (SplFileInfo $file): bool => in_array(
            $file->getExtension(),
            ['css', 'less', 'sass', 'scss', 'styl'],
            true,
        ))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    expect($stylesheets)
        ->toBe(['css/app.css'])
        ->and(File::get(resource_path('css/app.css')))
        ->toStartWith("@import 'tailwindcss';")
        ->toContain("@import 'tw-animate-css';")
        ->and(File::get(base_path('package.json')))
        ->not->toMatch('/"sass(?:-embedded)?"\s*:/')
        ->and(File::get(base_path('vite.config.ts')))
        ->toContain("input: ['resources/css/app.css', 'resources/js/app.ts']")
        ->and(File::get(resource_path('views/app.blade.php')))
        ->toContain('viewport-fit=cover')
        ->toContain('class="nativephp-safe-area font-sans antialiased"')
        ->toContain("@vite(['resources/css/app.css', 'resources/js/app.ts'");
});

test('primary surfaces share one mobile first page frame', function (string $path) {
    $source = File::get(resource_path("js/{$path}"));

    expect($source)
        ->toContain("import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue'")
        ->toContain('<WorkspacePageFrame')
        ->toContain('</WorkspacePageFrame>')
        ->not->toContain('min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8');
})->with('workspace page frame consumers');

test('the shared page frame exposes one shrink safe bounded container', function () {
    expect(File::get(resource_path('js/components/shared/WorkspacePageFrame.vue')))
        ->toContain('data-slot="workspace-page-frame"')
        ->toContain('ui-page-frame')
        ->toContain('data-slot="workspace-page-container"')
        ->toContain('ui-page-container')
        ->toContain('<slot />');
});

test('workspace headers wrap long copy and stack actions on narrow screens', function () {
    expect(File::get(resource_path('js/components/shared/WorkspacePageHeader.vue')))
        ->toContain('min-w-0 flex-1')
        ->toContain('wrap-anywhere')
        ->toContain('grid w-full min-w-0 grid-cols-1 gap-2')
        ->toContain('sm:flex sm:w-auto sm:flex-wrap')
        ->toContain('lg:shrink-0');
});

test('shared overlays use dynamic viewport bounds and mobile first spacing', function () {
    foreach ([
        'components/shared/WorkspaceDialogContent.vue',
        'components/shared/FilterSheet.vue',
        'components/ui/dialog/DialogContent.vue',
        'components/ui/dialog/DialogScrollContent.vue',
        'components/ui/sheet/SheetContent.vue',
    ] as $path) {
        expect(File::get(resource_path("js/{$path}")), $path)
            ->toContain('100dvh')
            ->not->toContain('100vw')
            ->not->toContain('92vh');
    }

    foreach ([
        'components/activity/ActivityFilterPanel.vue',
        'components/project/ProjectTaskFilters.vue',
        'components/task/TaskFilterBar.vue',
    ] as $path) {
        expect(File::get(resource_path("js/{$path}")), $path)
            ->toContain('<FilterSheet')
            ->not->toContain('92vh');
    }

    expect(File::get(resource_path('js/components/AppHeader.vue')))
        ->toContain('class="w-full max-w-xs p-4 sm:p-6"')
        ->not->toContain('class="w-[300px] p-6"');

    expect(File::get(resource_path('js/components/localization/FirstRunLanguageDialog.vue')))
        ->toContain('class="gap-0 p-0 sm:max-w-xl"')
        ->not->toContain('class="overflow-hidden p-0 sm:max-w-xl"');
});

test('intentional horizontal regions contain touch panning without page overscroll', function (string $path) {
    expect(File::get(resource_path("js/{$path}")))
        ->toContain('overflow-x-auto')
        ->toContain('overscroll-x-contain')
        ->toContain('touch-pan-x');
})->with([
    'activity category rail' => 'components/activity/ActivityFilterPanel.vue',
    'segmented controls' => 'components/shared/WorkspaceSegmentedControl.vue',
    'task board' => 'components/task/BoardView.vue',
]);

test('calendar navigation lets the localized period shrink between touch targets', function () {
    expect(File::get(resource_path('js/components/calendar/CalendarPeriodNavigator.vue')))
        ->toContain('min-w-0 flex-1 text-center')
        ->toContain('wrap-anywhere')
        ->not->toContain('min-w-44');
});

test('first party presentation avoids static viewport width escape hatches', function () {
    foreach ([resource_path('js'), resource_path('css')] as $directory) {
        foreach (File::allFiles($directory) as $file) {
            if (! in_array($file->getExtension(), ['css', 'ts', 'vue'], true)) {
                continue;
            }

            expect($file->getContents(), $file->getRelativePathname())
                ->not->toContain('100vw');
        }
    }

    expect(File::get(resource_path('js/components/TwoFactorSetupModal.vue')))
        ->toContain('w-64 max-w-full')
        ->and(File::get(resource_path('js/components/localization/LanguageSwitcher.vue')))
        ->toContain('w-64 max-w-[calc(100dvw-1rem)]')
        ->and(File::get(resource_path('js/components/workspace/WorkspaceSwitcher.vue')))
        ->toContain('max-w-[calc(100dvw-1rem)]')
        ->toContain('min-w-64');
});

test('shared compact actions expand to touch targets on coarse pointers', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceSegmentedButton.vue')))
        ->toContain('pointer-coarse:min-h-11')
        ->and(File::get(resource_path('js/components/ui/sidebar/index.ts')))
        ->toContain('pointer-coarse:min-h-12')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarRail.vue')))
        ->toContain('pointer-coarse:hidden')
        ->and(File::get(resource_path('js/components/ui/dropdown-menu/DropdownMenuItem.vue')))
        ->toContain('pointer-coarse:min-h-11')
        ->and(File::get(resource_path('js/components/ui/select/SelectItem.vue')))
        ->toContain('pointer-coarse:min-h-11')
        ->and(File::get(resource_path('js/components/ui/sonner/Sonner.vue')))
        ->toContain('pointer-coarse:!min-h-11');
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
        ->toContain('WorkspacePageFrame')
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
        ->toContain(':loading="importRequest.processing"')
        ->toContain(':loading-label=')
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
        ->toContain('max-h-[calc(100dvh-1rem)]')
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
        ->toContain(':loading="form.processing"')
        ->toContain(':loading-label=')
        ->toContain('size="lg"')
        ->not->toContain('<Spinner v-if="form.processing" />')
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
        ->toContain(':loading="inviteForm.processing"')
        ->toContain(':loading="removeForm.processing"')
        ->toContain(':loading-label="copy.inviting"')
        ->toContain(':loading-label="copy.removing"')
        ->toContain('size="lg"')
        ->not->toContain('<Spinner v-if="inviteForm.processing" />')
        ->not->toContain('<Spinner v-if="removeForm.processing" />')
        ->not->toContain('LoaderCircle')
        ->not->toContain('class="min-h-11 cursor-pointer rounded-xl"');
});

test('shared confirmations use the shared large loading action contract', function () {
    expect(File::get(resource_path('js/components/shared/WorkspaceConfirmDialog.vue')))
        ->toContain(':loading="processing"')
        ->toContain(':disabled="processing || !confirmationMatches"')
        ->toContain('size="lg"')
        ->not->toContain("import { Spinner } from '@/components/ui/spinner'")
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
        ->toContain(':loading="creating"')
        ->toContain(':loading-label=')
        ->not->toContain('<Spinner v-if="creating" />')
        ->toContain('size="lg"')
        ->and($profile)
        ->toContain('size="lg"')
        ->and(substr_count($profile, ':disabled="profileForm.processing"'))
        ->toBeGreaterThanOrEqual(2)
        ->and(substr_count($security, ':disabled="passwordForm.processing"'))
        ->toBeGreaterThanOrEqual(3)
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
    $dialog = File::get(resource_path('js/components/task/TaskCreateDialog.vue'));
    $descriptionField = File::get(resource_path('js/components/task/TaskDescriptionField.vue'));

    expect($dialog)
        ->toContain('<TaskDescriptionField')
        ->toContain(':error="form.errors.description"')
        ->toContain(':disabled="form.processing"')
        ->toContain('Boolean(form.errors.priority)')
        ->toContain(':aria-invalid="Boolean(form.errors.due_date)"')
        ->toContain('form.errors.recurring_rule')
        ->toContain(':disabled="!form.is_recurring || form.processing"')
        ->toContain('<InputError :message="form.errors.priority" />')
        ->toContain('id="due-date-error"')
        ->toContain(':message="form.errors.due_date"')
        ->toContain('<InputError :message="form.errors.recurring_rule" />')
        ->and($descriptionField)
        ->toContain(':aria-describedby="describedBy"')
        ->toContain(':aria-invalid="invalid"')
        ->toContain(':disabled="disabled"');
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
        ->and(File::get(resource_path('js/components/shared/ResultSummary.vue')))
        ->toContain('aria-live="polite"')
        ->toContain('aria-atomic="true"')
        ->and(File::get(resource_path('js/components/task/TaskResultsBar.vue')))
        ->toContain('<ResultSummary')
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
        ->toContain('overflow-x-auto')
        ->toContain('rounded-xl bg-muted p-1')
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
    $taskItem = File::get(resource_path('js/components/calendar/CalendarTaskItem.vue'));
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
        ->toContain('CalendarTaskItem')
        ->and($taskItem)
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
        ->toContain(':loading="processing"')
        ->toContain(':loading-label=')
        ->not->toContain('<Spinner v-if="processing" />');
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
        ->toContain('progress: false')
        ->not->toContain("color: '#4B5563'")
        ->and(File::get(resource_path('js/components/shared/GlobalBusyOverlay.vue')))
        ->toContain('bg-orange-600')
        ->toContain("t('common.states.processing_hint')")
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
        ->toContain('data-[state=checked]:from-orange-600')
        ->toContain('data-[state=checked]:via-orange-600')
        ->toContain('data-[state=checked]:to-orange-700')
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
        ->toContain('bg-linear-to-br from-orange-600 via-orange-600 to-orange-700 text-white')
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
    'responsive section navigation' => 'components/shared/ResponsiveSectionNavigation.vue',
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

test('shared controls provide the comfort touch and reading baseline', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('min-height: 3rem')
        ->toContain('min-width: 3rem')
        ->toContain('.ui-control.ui-control-lg')
        ->toContain('min-height: 3.25rem')
        ->and(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('ui-control-lg')
        ->toContain('h-12 px-6 text-base pointer-coarse:min-h-13')
        ->and(File::get(resource_path('js/components/ui/input/Input.vue')))
        ->toContain('h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('md:text-sm')
        ->and(File::get(resource_path('js/components/ui/label/Label.vue')))
        ->toContain('text-base')
        ->and(File::get(resource_path('js/components/ui/checkbox/Checkbox.vue')))
        ->toContain('size-5')
        ->toContain('pointer-coarse:size-6')
        ->and(File::get(resource_path('js/components/PasswordInput.vue')))
        ->toContain('min-w-12')
        ->toContain('pointer-coarse:min-w-13');
});

test('authentication surfaces use readable copy and a full checkbox target', function () {
    expect(File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue')))
        ->toContain('text-[0.9375rem]')
        ->toContain('max-w-sm text-base leading-7')
        ->not->toContain('tracking-[0.16em]')
        ->and(File::get(resource_path('js/pages/auth/Login.vue')))
        ->toContain('class="text-base"')
        ->toContain('class="flex min-h-12 cursor-pointer items-center space-x-3"')
        ->toContain('text-center text-base text-muted-foreground')
        ->and(File::get(resource_path('js/components/PasskeyVerify.vue')))
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-xs uppercase')
        ->and(File::get(resource_path('js/components/TextLink.vue')))
        ->toContain('inline-flex min-h-12 items-center')
        ->toContain('align-middle');
});

test('tablet navigation keeps the drawer through portrait tablet widths', function () {
    expect(File::get(resource_path('js/components/ui/sidebar/SidebarProvider.vue')))
        ->toContain('useMediaQuery("(max-width: 1023px)")')
        ->and(File::get(resource_path('js/components/ui/sidebar/utils.ts')))
        ->toContain('SIDEBAR_WIDTH_MOBILE = "min(22rem, calc(100dvi - 2rem))"')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarMenuAction.vue')))
        ->toContain('pointer-coarse:min-h-12 pointer-coarse:min-w-12')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarGroupAction.vue')))
        ->toContain('pointer-coarse:min-h-12 pointer-coarse:min-w-12')
        ->and(File::get(resource_path('js/components/ui/sidebar/SidebarMenuSubButton.vue')))
        ->toContain('pointer-coarse:h-12 pointer-coarse:text-base');
});

test('mobile sidebar closes when an Inertia navigation starts', function () {
    $provider = File::get(resource_path('js/components/ui/sidebar/SidebarProvider.vue'));

    expect($provider)
        ->toContain('import { router } from "@inertiajs/vue3"')
        ->toContain('onUnmounted(')
        ->toContain('router.on("start"')
        ->toContain('isMobile.value && openMobile.value')
        ->toContain('setOpenMobile(false)')
        ->not->toContain('router.on("prefetching"');
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
        ->toContain('tracking-[0.08em]')
        ->not->toContain('inset-x-0 top-0 h-1.5');
});
