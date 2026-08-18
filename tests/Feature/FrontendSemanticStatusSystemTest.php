<?php

use Illuminate\Support\Facades\File;

test('semantic status palette exposes shared surface border text and icon channels', function () {
    $stylesheet = File::get(resource_path('css/app.css'));

    foreach (['success', 'warning', 'information', 'destructive'] as $tone) {
        foreach (['surface', 'border', 'text', 'icon'] as $channel) {
            expect($stylesheet)
                ->toContain("--color-status-{$tone}-{$channel}:")
                ->toContain("--status-{$tone}-{$channel}:");
        }
    }
});

test('semantic status text and icons meet normal text contrast against their surfaces', function () {
    $stylesheet = File::get(resource_path('css/app.css'));

    $linearRgb = function (string $variable) use ($stylesheet): array {
        preg_match(
            "/--{$variable}: oklch\\(([0-9.]+) ([0-9.]+) ([0-9.]+)\\);/",
            $stylesheet,
            $matches,
        );

        expect($matches, $variable)->toHaveCount(4);

        $lightness = (float) $matches[1];
        $chroma = (float) $matches[2];
        $hue = deg2rad((float) $matches[3]);
        $a = $chroma * cos($hue);
        $b = $chroma * sin($hue);
        $l = ($lightness + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($lightness - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($lightness - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

        return [
            max(0, min(1, 4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s)),
            max(0, min(1, -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s)),
            max(0, min(1, -0.0041960863 * $l - 0.7034186147 * $m + 1.707614701 * $s)),
        ];
    };
    $luminance = fn (array $rgb): float => 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    $contrast = function (array $foreground, array $background) use ($luminance): float {
        $values = [$luminance($foreground), $luminance($background)];

        return (max($values) + 0.05) / (min($values) + 0.05);
    };

    foreach (['success', 'warning', 'information', 'destructive'] as $tone) {
        $surface = $linearRgb("status-{$tone}-surface");

        expect($contrast($linearRgb("status-{$tone}-text"), $surface), "{$tone} text")
            ->toBeGreaterThanOrEqual(4.5)
            ->and($contrast($linearRgb("status-{$tone}-icon"), $surface), "{$tone} icon")
            ->toBeGreaterThanOrEqual(4.5);
    }
});

test('shared status primitives consume semantic status utilities', function (string $path) {
    $source = File::get(resource_path("js/{$path}"));

    expect($source)
        ->toContain('status-')
        ->not->toMatch('/\b(?:border|bg|text|ring|from|via|to)-(?:emerald|amber|sky|blue|red)-/');
})->with([
    'alert' => 'components/ui/alert/index.ts',
    'button' => 'components/ui/button/index.ts',
    'empty state' => 'components/shared/EmptyState.vue',
    'icon tile' => 'components/shared/IconTile.vue',
    'inline state' => 'components/shared/InlineState.vue',
    'status notice' => 'components/shared/StatusNotice.vue',
]);

test('runtime status colors stay semantic outside documented domain and decorative exceptions', function () {
    $allowedFixedPaletteUtilities = [
        'components/dashboard/ProductivityChart.vue' => [
            'bg-sky-500/70',
        ],
        'components/onboarding/OnboardingChecklist.vue' => [
            'to-amber-50/70',
        ],
        'components/workspace/WorkspaceMembersPanel.vue' => [
            'bg-amber-100',
            'bg-emerald-100',
            'bg-sky-100',
            'text-amber-950',
            'text-emerald-950',
            'text-sky-950',
        ],
        'pages/settings/Members.vue' => [
            'bg-amber-100',
            'bg-emerald-100',
            'bg-sky-100',
            'text-amber-950',
            'text-emerald-950',
            'text-sky-950',
        ],
        'pages/settings/Preferences.vue' => [
            'to-amber-50/60',
        ],
    ];

    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true) || str_ends_with($file->getFilename(), '.test.ts')) {
            continue;
        }

        $path = str_replace(resource_path('js').DIRECTORY_SEPARATOR, '', $file->getRealPath());
        preg_match_all(
            '/\b(?:border|bg|text|ring|from|via|to)-(?:emerald|amber|sky|blue|red)-[^\s\'"`]+/',
            $file->getContents(),
            $matches,
        );

        $actualUtilities = array_values(array_unique($matches[0]));
        $expectedUtilities = $allowedFixedPaletteUtilities[$path] ?? [];
        sort($actualUtilities);
        sort($expectedUtilities);

        expect($actualUtilities, $path)->toBe($expectedUtilities);
    }
});

test('unreachable legacy responsive components are removed in favor of active shell consumers', function () {
    foreach ([
        'components/AppHeader.vue',
        'components/NavFooter.vue',
        'components/task/TaskStats.vue',
    ] as $path) {
        expect(File::exists(resource_path("js/{$path}")), $path)->toBeFalse();
    }

    $sidebar = File::get(resource_path('js/components/AppSidebar.vue'));
    $sidebarHeader = File::get(resource_path('js/components/AppSidebarHeader.vue'));

    expect($sidebar)->toContain('NavMain', 'NavUser', 'WorkspaceSwitcher');
    expect($sidebarHeader)
        ->toContain('LanguageSwitcher')
        ->not->toContain('Search', 'useUiStore', 'openCommandPalette', 'commands.');
    expect(File::get(base_path('docs/frontend.md')))->not->toContain('`AppHeader`');
});

test('responsive section navigation uses readable semantic destructive text', function () {
    expect(File::get(resource_path('js/components/shared/ResponsiveSectionNavigation.vue')))
        ->toContain('text-status-destructive-text')
        ->not->toContain('text-destructive');

    expect(File::get(resource_path('css/app.css')))
        ->toContain('--muted-foreground: hsl(0 0% 40%);');

    expect(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('from-status-destructive-icon')
        ->not->toContain('from-destructive');
});
