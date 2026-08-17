<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

dataset('primary Sutelio pages', [
    'dashboard' => ['pages/Dashboard.vue', 'LayoutDashboard'],
    'projects' => ['pages/projects/Index.vue', 'FolderKanban'],
    'project detail' => [
        'components/project/ProjectOperationsHeader.vue',
        'FolderKanban',
    ],
    'tasks' => ['pages/tasks/Index.vue', 'ListChecks'],
    'task detail' => ['pages/tasks/Show.vue', 'ListChecks'],
    'calendar' => ['pages/calendar/Index.vue', 'CalendarDays'],
    'activity' => ['pages/activity/Index.vue', 'History'],
    'notifications' => ['pages/notifications/Index.vue', 'BellRing'],
    'workspaces' => ['pages/workspaces/Index.vue', 'Building2'],
    'workspace detail' => ['pages/workspaces/Show.vue', 'Building2'],
    'settings' => ['layouts/settings/Layout.vue', 'activeNavItem'],
]);

dataset('animated Sutelio overlays', [
    'dialog' => 'ui/dialog/DialogContent.vue',
    'sheet' => 'ui/sheet/SheetContent.vue',
    'dropdown menu' => 'ui/dropdown-menu/DropdownMenuContent.vue',
    'select' => 'ui/select/SelectContent.vue',
]);

dataset('measured layout transition exceptions', [
    'sidebar shell' => 'components/ui/sidebar/Sidebar.vue',
    'sidebar label' => 'components/ui/sidebar/SidebarGroupLabel.vue',
    'sidebar menu button' => 'components/ui/sidebar/index.ts',
]);

dataset('accessible icon only controls', [
    'app header menu and search' => ['components/AppHeader.vue', 'aria-label'],
    'sidebar search' => ['components/AppSidebarHeader.vue', 'aria-label'],
    'timezone selector' => ['components/preferences/TimezoneCombobox.vue', ':aria-label'],
    'workspace dialog close' => ['components/shared/WorkspaceDialogContent.vue', 'sr-only'],
    'dialog close' => ['components/ui/dialog/DialogContent.vue', 'sr-only'],
    'sheet close' => ['components/ui/sheet/SheetContent.vue', 'sr-only'],
    'onboarding dismiss' => ['components/onboarding/OnboardingChecklist.vue', ':aria-label'],
    'project color selection' => ['components/project/ProjectCreateDialog.vue', ':aria-label="color"'],
]);

dataset('intentional non tile presentation', [
    'decorative background pattern' => [
        'components/PlaceholderPattern.vue',
        '<svg',
        'the inline svg is a decorative full surface pattern rather than an icon',
    ],
    'calendar date cells' => [
        'components/calendar/CalendarMonthGrid.vue',
        'tabular-nums',
        'calendar numerals are data labels rather than icon introductions',
    ],
    'project color controls' => [
        'components/project/ProjectCreateDialog.vue',
        ':aria-pressed="form.color === color"',
        'the swatches are native color choices with visible selected state',
    ],
    'one time password slots' => [
        'components/ui/input-otp/InputOTPSlot.vue',
        'data-[active=true]',
        'the square is an input character cell rather than an icon tile',
    ],
]);

test('the shared Sutelio motion primitives are available', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('--motion-snap: 90ms;')
        ->toContain('--motion-feedback: 130ms;')
        ->toContain('--motion-state: 190ms;')
        ->toContain('--motion-spatial: 260ms;')
        ->toContain('--motion-signature: 340ms;')
        ->toContain('--ease-standard: cubic-bezier(0.2, 0, 0, 1);')
        ->toContain('--ease-emphasized: cubic-bezier(0.16, 1, 0.3, 1);')
        ->toContain('@keyframes ui-reveal')
        ->toContain('@keyframes ui-status-pop')
        ->toContain('.ui-reveal')
        ->toContain('.ui-lift')
        ->toContain('.ui-status-pop')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the shared Sutelio icon tile is available', function () {
    $iconTilePath = resource_path('js/components/shared/IconTile.vue');

    expect(File::exists($iconTilePath))->toBeTrue();

    expect(File::get($iconTilePath))
        ->toContain('data-slot="icon-tile"')
        ->toContain('type IconTileTone =')
        ->toContain("| 'brand'", "| 'cobalt'", "| 'muted'", "| 'success'")
        ->toContain("| 'warning'", "| 'destructive'", "| 'information'")
        ->toContain('tone?: IconTileTone;')
        ->toContain('aria-hidden="true"');
});

test('the shared leading icon heading offers opt in icon tiles', function () {
    expect(File::get(resource_path('js/components/shared/LeadingIconHeading.vue')))
        ->toContain("import IconTile from '@/components/shared/IconTile.vue'")
        ->toContain("import type { IconTileTone } from '@/components/shared/IconTile.vue'")
        ->toContain('tile?: boolean;')
        ->toContain('tileTone?: IconTileTone;')
        ->toContain("tileSize?: 'sm' | 'md' | 'lg';")
        ->toContain('<IconTile')
        ->toContain('v-if="props.tile"')
        ->toContain('v-else');
});

test('primary pages compose the shared icon bearing header', function (string $path, string $icon) {
    $source = File::get(resource_path("js/{$path}"));
    $header = Str::betweenFirst(
        $source,
        '<WorkspacePageHeader',
        '</WorkspacePageHeader>',
    );

    expect($source)->toContain('WorkspacePageHeader');
    expect($header)
        ->toContain('<template #icon>')
        ->toContain($icon);
})->with('primary Sutelio pages');

test('shared overlays own bounded open and close motion with reduced motion parity', function (string $path) {
    expect(File::get(resource_path("js/components/{$path}")))
        ->toContain('data-[state=open]:animate-in')
        ->toContain('data-[state=closed]:animate-out')
        ->toContain('motion-reduce:data-[state=open]:animate-none')
        ->toContain('motion-reduce:data-[state=closed]:animate-none');
})->with('animated Sutelio overlays');

test('static collections do not inherit a generic stagger', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true)) {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('ui-stagger');
    }
});

test('generic surfaces do not animate merely because they render', function () {
    foreach ([
        resource_path('js/components/ui/card/Card.vue'),
        resource_path('js/components/shared/WorkspacePageHeader.vue'),
        resource_path('js/components/ui/sidebar/SidebarInset.vue'),
        resource_path('js/layouts/auth/AuthSimpleLayout.vue'),
    ] as $file) {
        expect(File::get($file), $file)
            ->not->toContain('ui-enter')
            ->not->toContain('ui-surface')
            ->not->toContain('ui-page-surface');
    }
});

test('first party controls do not use broad transitions', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true)) {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('transition-all');
    }
});

test('measured layout transitions use named properties', function (string $path) {
    expect(File::get(resource_path("js/{$path}")))
        ->toContain('transition-[')
        ->not->toContain('transition-all');
})->with('measured layout transition exceptions');

test('visual progress uses composite safe transforms', function () {
    foreach ([
        'components/dashboard/ProductivityChart.vue',
        'components/onboarding/OnboardingShell.vue',
        'components/project/ProjectPulse.vue',
        'components/shared/GlobalBusyOverlay.vue',
        'pages/settings/Profile.vue',
    ] as $path) {
        expect(File::get(resource_path("js/{$path}")), $path)
            ->toContain('transition-transform')
            ->toMatch('/scale-[xy]-\[var\(--(?:bar-scale|progress)\)\]/')
            ->not->toContain('transition-[width]')
            ->not->toContain('transition-[height]');
    }
});

test('shared overlays use spatial open and shorter exit timing', function (string $path) {
    expect(File::get(resource_path("js/components/{$path}")))
        ->toContain('data-[state=open]:duration-[var(--motion-spatial)]')
        ->toContain('data-[state=closed]:duration-[195ms]')
        ->toContain('motion-reduce:data-[state=open]:animate-none')
        ->toContain('motion-reduce:data-[state=closed]:animate-none');
})->with('animated Sutelio overlays');

test('icon only controls keep an accessible name', function (string $path, string $accessibleToken) {
    expect(File::get(resource_path("js/{$path}")))->toContain($accessibleToken);
})->with('accessible icon only controls');

test('non tile presentation exclusions remain explicit and semantic', function (string $path, string $semanticToken, string $reason) {
    expect($reason)->not->toBeEmpty();
    expect(File::get(resource_path("js/{$path}")))->toContain($semanticToken);
})->with('intentional non tile presentation');

test('every leading icon heading either owns a tile or documents the interactive logo exception', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        $source = File::get($file->getPathname());

        if (! str_contains($source, '<LeadingIconHeading')) {
            continue;
        }

        preg_match_all('/<LeadingIconHeading\b[^>]*>/s', $source, $matches);

        foreach ($matches[0] as $tag) {
            if (str_contains($tag, ' tile')) {
                continue;
            }

            expect($file->getRelativePathname())->toBe('layouts/auth/AuthSimpleLayout.vue');
            expect($source)->toContain('<IconTile tone="cobalt" size="lg">');
        }
    }
});

test('manual square containers are limited to named non icon controls', function () {
    $allowedControls = [
        'components/calendar/CalendarMonthGrid.vue' => 'numeric calendar cells',
        'components/preferences/TimezoneCombobox.vue' => 'combobox trigger',
        'components/project/ProjectCreateDialog.vue' => 'color selection buttons',
        'components/shared/WorkspaceDialogContent.vue' => 'dialog close control',
        'components/ui/dialog/DialogContent.vue' => 'dialog close control',
        'components/ui/dialog/DialogScrollContent.vue' => 'dialog close control',
        'components/ui/input-otp/InputOTPSlot.vue' => 'one time password cell',
        'components/ui/sheet/SheetContent.vue' => 'sheet close control',
    ];

    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        foreach (preg_split('/\R/', File::get($file->getPathname())) ?: [] as $line) {
            if (! preg_match('/flex size-(?:8|9|10|11|12|14|16).*items-center.*justify-center.*rounded/', $line)) {
                continue;
            }

            $path = $file->getRelativePathname();

            expect($allowedControls, "Unexpected manual icon-like container in {$path}")
                ->toHaveKey($path);
            expect($allowedControls[$path])->not->toBeEmpty();
        }
    }
});

test('shared controls and surfaces keep motion opt in at the correct layer', function () {
    $card = File::get(resource_path('js/components/ui/card/Card.vue'));
    $button = File::get(resource_path('js/components/ui/button/index.ts'));
    $badge = File::get(resource_path('js/components/ui/badge/index.ts'));

    expect($card)->not->toContain('ui-surface', 'ui-enter', 'ui-lift');
    expect($button)->toContain('ui-control', 'motion-reduce:transition-none');
    expect($badge)->toContain('motion-reduce:transition-none');
    expect($badge)->not->toContain('ui-status-pop');
});

test('authentication and language entry use bounded shared icon motion', function () {
    $authShell = File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue'));
    $languageDialog = File::get(resource_path('js/components/localization/FirstRunLanguageDialog.vue'));

    expect($authShell)
        ->toContain("import IconTile from '@/components/shared/IconTile.vue'")
        ->toContain('<IconTile tone="cobalt" size="lg">');
    expect($languageDialog)
        ->toContain('tile-tone="brand"')
        ->toContain('tile-size="lg"')
        ->toContain('ui-status-pop');
    expect($languageDialog)->not->toContain('motion-safe:animate-pulse');
});

test('onboarding composes shared icon tiles and bounded step motion', function () {
    $onboarding = File::get(resource_path('js/pages/onboarding/Index.vue'));
    $shell = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));
    $welcome = File::get(resource_path('js/components/onboarding/WelcomeStep.vue'));
    $workspace = File::get(resource_path('js/components/onboarding/WorkspaceStep.vue'));
    $project = File::get(resource_path('js/components/onboarding/ProjectStep.vue'));
    $task = File::get(resource_path('js/components/onboarding/TaskStep.vue'));

    expect($onboarding)->toContain('<Transition name="ui-step" mode="out-in">');
    expect($shell)->toContain('IconTile');
    expect($welcome)->toContain('IconTile')->not->toContain('ui-stagger');
    expect($workspace)->toContain('tile-tone="brand"');
    expect($project)->toContain('tile-tone="brand"');
    expect($task)->toContain('tile-tone="brand"');
});

test('onboarding results and safety states use one shot shared motion', function () {
    $preferences = File::get(resource_path('js/components/onboarding/PreferencesStep.vue'));
    $productMap = File::get(resource_path('js/components/onboarding/ProductMapStep.vue'));
    $safety = File::get(resource_path('js/components/onboarding/SafetyStep.vue'));
    $results = File::get(resource_path('js/components/onboarding/ResultsStep.vue'));

    expect($preferences)->toContain('IconTile');
    expect($productMap)->toContain('IconTile')->not->toContain('ui-stagger');
    expect($safety)->toContain('IconTile')->not->toContain('ui-stagger');
    expect($results)->toContain('IconTile', 'ui-status-pop')->not->toContain('ui-stagger');
});

test('dashboard project and workspace surfaces opt into shared icon and lift motion', function () {
    $dashboardQueue = File::get(resource_path('js/components/dashboard/DashboardTaskQueue.vue'));
    $productivity = File::get(resource_path('js/components/dashboard/ProductivityChart.vue'));
    $projectPulse = File::get(resource_path('js/components/project/ProjectPulse.vue'));
    $projectQueue = File::get(resource_path('js/components/project/ProjectTaskQueue.vue'));
    $workspaceOverview = File::get(resource_path('js/components/workspace/WorkspaceOverviewPanel.vue'));

    expect($dashboardQueue)->toContain('tile-tone', 'ui-lift')->not->toContain('ui-stagger');
    expect($productivity)->toContain('IconTile', 'motion-reduce:transition-none');
    expect($projectPulse)->toContain('tile-tone="brand"', 'ui-lift')->not->toContain('ui-stagger');
    expect($projectQueue)->not->toContain('ui-stagger');
    expect($workspaceOverview)->toContain('tile-tone="brand"');
});

test('task interaction surfaces compose shared icons and bounded motion', function () {
    $overview = File::get(resource_path('js/components/task/TaskOverviewPanel.vue'));
    $checklists = File::get(resource_path('js/components/task/TaskChecklistPanel.vue'));
    $attachments = File::get(resource_path('js/components/task/TaskAttachmentsPanel.vue'));
    $reminders = File::get(resource_path('js/components/task/TaskRemindersPanel.vue'));
    $comments = File::get(resource_path('js/components/task/TaskCommentsPanel.vue'));
    $taxonomy = File::get(resource_path('js/components/task/TaskTaxonomyPanel.vue'));
    $taskList = File::get(resource_path('js/components/task/TaskList.vue'));

    expect($overview)->toContain('LeadingIconHeading', '<User');
    expect($checklists)->toContain('LeadingIconHeading', '<ListChecks')->not->toContain('ui-stagger');
    expect($attachments)->toContain('LeadingIconHeading', '<Paperclip')->not->toContain('ui-stagger');
    expect($reminders)->toContain('LeadingIconHeading', '<Bell')->not->toContain('ui-stagger');
    expect($comments)->toContain('LeadingIconHeading', '<MessageSquare');
    expect($taxonomy)->toContain('LeadingIconHeading', '<Tags');
    expect($taskList)->toContain('ui-lift', 'motion-reduce:transition-none')->not->toContain('ui-stagger');
});

test('planning feeds use shared status icons without replaying infinite history', function () {
    $attention = File::get(resource_path('js/components/calendar/CalendarAttentionRail.vue'));
    $agenda = File::get(resource_path('js/components/calendar/CalendarAgendaView.vue'));
    $week = File::get(resource_path('js/components/calendar/CalendarWeekView.vue'));
    $timeline = File::get(resource_path('js/components/activity/ActivityTimeline.vue'));
    $filters = File::get(resource_path('js/components/activity/ActivityFilterPanel.vue'));
    $notificationFeed = File::get(resource_path('js/components/notification/NotificationFeed.vue'));
    $notificationRow = File::get(resource_path('js/components/notification/NotificationRow.vue'));
    $notificationPage = File::get(resource_path('js/pages/notifications/Index.vue'));

    expect($attention)->toContain('tile-tone="warning"', 'IconTile')->not->toContain('ui-stagger');
    expect($agenda)->toContain('IconTile')->not->toContain('ui-stagger');
    expect($week)->toContain('IconTile')->not->toContain('ui-stagger');
    expect($timeline)->toContain('IconTile', 'LeadingIconHeading', '<History');
    expect($timeline)->not->toContain('ui-stagger');
    expect($filters)->toContain('IconTile', 'LeadingIconHeading', '<Filter');
    expect($notificationRow)->toContain('IconTile', 'justMarkedRead', 'ui-status-pop');
    expect($notificationPage)->toContain('markingAllSucceeded', 'ui-status-pop');
    expect($notificationFeed)->not->toContain('animateInitialGroup', 'ui-stagger');
});

test('account settings use shared section icons and one shot request feedback', function () {
    $profile = File::get(resource_path('js/pages/settings/Profile.vue'));
    $preferences = File::get(resource_path('js/pages/settings/Preferences.vue'));
    $notifications = File::get(resource_path('js/pages/settings/Notifications.vue'));
    $security = File::get(resource_path('js/pages/settings/Security.vue'));
    $passkeys = File::get(resource_path('js/components/ManagePasskeys.vue'));
    $recoveryCodes = File::get(resource_path('js/components/TwoFactorRecoveryCodes.vue'));

    expect($profile)
        ->toContain('tile-tone="brand"', '<Camera', '<UserRound', '<Save')
        ->toContain('profileForm.recentlySuccessful', 'ui-status-pop');
    expect($preferences)
        ->toContain('<Languages', '<PanelsTopLeft', 'tile-tone="brand"')
        ->toContain('form.recentlySuccessful', 'ui-status-pop');
    expect($notifications)
        ->toContain('IconTile', 'LeadingIconHeading', '<BellRing')
        ->toContain('form.recentlySuccessful', 'ui-status-pop');
    expect($security)
        ->toContain('tile-tone="brand"', '<LockKeyhole', '<Shield')
        ->toContain('twoFactorForm.recentlySuccessful', 'ui-status-pop');
    expect($passkeys)->toContain('IconTile', 'LeadingIconHeading', '<KeyRound');
    expect($recoveryCodes)
        ->toContain('LeadingIconHeading', '<LockKeyhole')
        ->toContain('recentlySuccessful', 'ui-status-pop', '<Spinner');
});

test('administration surfaces preserve scoped and destructive icon semantics', function () {
    $scopeBanner = File::get(resource_path('js/components/settings/data/DataScopeBanner.vue'));
    $backup = File::get(resource_path('js/pages/settings/Backup.vue'));
    $export = File::get(resource_path('js/pages/settings/Export.vue'));
    $members = File::get(resource_path('js/pages/settings/Members.vue'));
    $configuration = File::get(resource_path('js/components/workspace/WorkspaceConfigurationPanel.vue'));
    $workspaceMembers = File::get(resource_path('js/components/workspace/WorkspaceMembersPanel.vue'));
    $danger = File::get(resource_path('js/components/workspace/WorkspaceDangerPanel.vue'));

    expect($scopeBanner)->toContain('tileTone', '<LeadingIconHeading', ':tile-tone');
    expect($backup)->toContain('IconTile', 'creatingSucceeded', 'ui-status-pop')->not->toContain('ui-stagger');
    expect($export)->toContain('IconTile', 'tile-tone="brand"', 'ui-lift');
    expect($members)->toContain('tile-tone="brand"', 'tile-tone="muted"');
    expect($configuration)->toContain('tile-tone="brand"', 'tile-tone="information"');
    expect($workspaceMembers)->toContain('tile-tone="brand"')->not->toContain('ui-stagger');
    expect($danger)
        ->toContain('tile-tone="warning"')
        ->toContain('tile-tone="destructive"')
        ->toContain('tile-tone="muted"');
});

test('navigation shells and empty states expose accessible shared interactions', function () {
    $emptyState = File::get(resource_path('js/components/shared/EmptyState.vue'));
    $header = File::get(resource_path('js/components/AppHeader.vue'));
    $mainNavigation = File::get(resource_path('js/components/NavMain.vue'));
    $footerNavigation = File::get(resource_path('js/components/NavFooter.vue'));
    $userNavigation = File::get(resource_path('js/components/NavUser.vue'));
    $userMenu = File::get(resource_path('js/components/UserMenuContent.vue'));
    $breadcrumbs = File::get(resource_path('js/components/Breadcrumbs.vue'));
    $palette = File::get(resource_path('js/components/shared/CommandPalette.vue'));
    $sidebarHeader = File::get(resource_path('js/components/AppSidebarHeader.vue'));
    $sidebarLayout = File::get(resource_path('js/layouts/app/AppSidebarLayout.vue'));

    expect($emptyState)
        ->toContain('IconTile', 'PackageOpen', 'LoaderCircle', 'AlertTriangle')
        ->not->toContain('<svg');
    expect($header)->toContain('aria-label', 'openCommandPalette');
    expect($mainNavigation)->toContain('aria-current', 'font-semibold');
    expect($footerNavigation)->toContain(':tooltip="item.title"');
    expect($userNavigation)->toContain(':tooltip="user.name"', 'aria-hidden="true"');
    expect($userMenu)->toContain('aria-hidden="true"');
    expect($breadcrumbs)->toContain('BreadcrumbSeparator');
    expect($palette)->toContain('<Dialog', 'IconTile', '<Button', 'aria-label', '$el?.focus()');
    expect($sidebarHeader)->toContain('openCommandPalette', 'aria-label');
    expect($sidebarLayout)->toContain('<CommandPalette');
});

test('first party presentation remains fixed light and free of raw brand colors', function () {
    $rawColorAllowlist = [
        realpath(resource_path('views/app.blade.php')) => ['#123c8b' => 1],
        realpath(resource_path('js/pages/onboarding/Index.vue')) => ['#ff6038' => 2],
        realpath(resource_path('js/components/project/ProjectCreateDialog.vue')) => ['#ff6038' => 3],
    ];
    $forbiddenRawColors = ['#123c8b', '#0a285f', '#ff6038', '#cd431f'];

    foreach ([resource_path('js'), resource_path('views')] as $root) {
        foreach (File::allFiles($root) as $file) {
            if (! in_array($file->getExtension(), ['js', 'ts', 'vue', 'php'], true)) {
                continue;
            }

            $source = File::get($file->getPathname());
            $normalizedSource = strtolower($source);

            expect($source, $file->getRelativePathname())->not->toContain('dark:');

            foreach ($forbiddenRawColors as $rawColor) {
                $allowedOccurrences = $rawColorAllowlist[$file->getRealPath()][$rawColor] ?? 0;

                expect(
                    substr_count($normalizedSource, $rawColor),
                    $file->getRelativePathname(),
                )->toBe($allowedOccurrences);
            }
        }
    }
});
