<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('shared management navigation keeps readable wrap safe touch targets', function () {
    $navigation = File::get(resource_path('js/components/shared/ResponsiveSectionNavigation.vue'));
    $metric = File::get(resource_path('js/components/shared/WorkspaceMetric.vue'));
    $pageHeader = File::get(resource_path('js/components/shared/WorkspacePageHeader.vue'));
    $managementNavigation = File::get(resource_path('js/components/workspace/WorkspaceManagementNavigation.vue'));
    $switcher = File::get(resource_path('js/components/workspace/WorkspaceSwitcher.vue'));

    expect($navigation)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('min-h-11')
        ->not->toContain('text-sm')
        ->not->toContain('text-[10px]')
        ->not->toContain('whitespace-nowrap')
        ->and($metric)
        ->toContain('text-[0.9375rem]')
        ->toContain('wrap-anywhere')
        ->not->toContain('text-xs')
        ->not->toContain('truncate')
        ->and($pageHeader)
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-[0.7rem]')
        ->and($managementNavigation)
        ->toContain('desktop-at="xl"')
        ->and($switcher)
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->toContain('line-clamp-2')
        ->not->toContain('text-sm')
        ->not->toContain('text-[10px]')
        ->not->toContain('truncate');
});

test('workspace portfolio and overview keep identities and actions readable', function () {
    $sources = [
        File::get(resource_path('js/pages/workspaces/Index.vue')),
        File::get(resource_path('js/pages/workspaces/Show.vue')),
        File::get(resource_path('js/components/workspace/WorkspaceOverviewPanel.vue')),
    ];

    foreach ($sources as $source) {
        expect($source)
            ->not->toContain('min-h-11')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm')
            ->not->toContain('truncate');
    }

    expect($sources[0])
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('data-workspace-portfolio-metrics')
        ->toContain('grid-cols-1')
        ->toContain('sm:grid-cols-3')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->toMatch('/CardTitle\\s+as="h2"/')
        ->not->toContain('size-11 shrink-0')
        ->and($sources[1])
        ->toContain('break-words')
        ->and($sources[2])
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->toMatch('/CardTitle\\s+as="div"/')
        ->toMatch('/CardTitle\\s+as="h2"/')
        ->toContain('wrap-anywhere');
});

test('workspace member and invitation rows reflow without truncating people', function () {
    $management = File::get(resource_path('js/components/workspace/WorkspaceMembersPanel.vue'));
    $settings = File::get(resource_path('js/pages/settings/Members.vue'));

    foreach ([$management, $settings] as $source) {
        expect($source)
            ->toContain('min-h-12')
            ->toContain('pointer-coarse:min-h-13')
            ->toContain('text-base')
            ->toContain('text-[0.9375rem]')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm')
            ->not->toContain('text-[10px]')
            ->not->toContain('text-[11px]')
            ->not->toContain('truncate');
    }

    expect($management)
        ->toContain("'text-[0.9375rem] whitespace-normal'")
        ->toMatch('/CardTitle\\s+as="div"/')
        ->toMatch('/CardTitle\\s+as="h2"/')
        ->and($settings)
        ->toContain("'text-[0.9375rem] whitespace-normal'");
});

test('workspace configuration and danger controls use phone safe management layouts', function () {
    $configuration = File::get(resource_path('js/components/workspace/WorkspaceConfigurationPanel.vue'));
    $definition = File::get(resource_path('js/components/workspace/WorkspaceDefinitionCard.vue'));
    $taxonomy = File::get(resource_path('js/components/workspace/WorkspaceTaxonomySwitcher.vue'));
    $danger = File::get(resource_path('js/components/workspace/WorkspaceDangerPanel.vue'));

    foreach ([$configuration, $definition, $taxonomy, $danger] as $source) {
        expect($source)
            ->not->toContain('min-h-11')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm');
    }

    expect($configuration)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($definition)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('wrap-anywhere')
        ->and(preg_match_all('/<Badge\\b/', $definition))
        ->toBe(
            preg_match_all(
                '/<Badge\\b[^>]*class="[^"]*text-\\[0\\.9375rem\\]/s',
                $definition,
            ),
        )
        ->and($taxonomy)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->and($danger)
        ->toContain('grid-cols-1')
        ->toContain('sm:grid-cols-3')
        ->toContain('text-[0.9375rem]')
        ->toMatch('/CardTitle\\s+as="h2"/');
});

test('settings surfaces keep readable copy and full action targets', function () {
    $layout = File::get(resource_path('js/layouts/settings/Layout.vue'));
    $sources = [
        File::get(resource_path('js/pages/settings/Profile.vue')),
        File::get(resource_path('js/pages/settings/Preferences.vue')),
        File::get(resource_path('js/pages/settings/Notifications.vue')),
        File::get(resource_path('js/pages/settings/Security.vue')),
        File::get(resource_path('js/pages/settings/Export.vue')),
        File::get(resource_path('js/pages/settings/Backup.vue')),
        File::get(resource_path('js/components/settings/data/DataScopeBanner.vue')),
    ];

    expect($layout)
        ->toContain('lg:flex-row')
        ->toContain('settings-page min-w-0 flex-1');

    foreach ($sources as $source) {
        expect($source)
            ->not->toContain('min-h-11')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm')
            ->not->toContain('text-[10px]')
            ->not->toContain('text-[11px]')
            ->not->toContain('truncate');
    }

    expect($sources[0])
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($sources[1])
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->and($sources[4])
        ->toContain('grid-cols-1')
        ->toContain('sm:grid-cols-3')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->and($sources[5])
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13');
});

test('settings cards and destructive guidance preserve accessible semantics', function () {
    $cardTitle = File::get(resource_path('js/components/ui/card/CardTitle.vue'));
    $alertDescription = File::get(resource_path('js/components/ui/alert/AlertDescription.vue'));
    $alertVariants = File::get(resource_path('js/components/ui/alert/index.ts'));
    $profile = File::get(resource_path('js/pages/settings/Profile.vue'));
    $settingsPages = [
        $profile,
        File::get(resource_path('js/pages/settings/Preferences.vue')),
        File::get(resource_path('js/pages/settings/Notifications.vue')),
        File::get(resource_path('js/pages/settings/Security.vue')),
        File::get(resource_path('js/pages/settings/Members.vue')),
        File::get(resource_path('js/pages/settings/Export.vue')),
        File::get(resource_path('js/pages/settings/Backup.vue')),
    ];

    expect($cardTitle)
        ->toContain("as?: 'div' | 'h2' | 'h3' | 'h4'")
        ->toContain('<component')
        ->and($alertDescription)
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-sm')
        ->and($alertVariants)
        ->toContain('text-red-800')
        ->not->toContain('text-destructive [&>svg]')
        ->and($profile)
        ->toContain(':aria-label="')
        ->toContain('labels.avatar.choose');

    foreach ($settingsPages as $settingsPage) {
        preg_match_all('/<CardTitle\\b[^>]*>/s', $settingsPage, $cardTitles);

        expect($cardTitles[0])->not->toBeEmpty();

        foreach ($cardTitles[0] as $cardTitleTag) {
            expect($cardTitleTag)->toContain('as="h2"');
        }
    }
});
