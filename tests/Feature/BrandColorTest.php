<?php

test('the Sutelio signal orange palette is exact and accessible', function () {
    $css = File::get(resource_path('css/app.css'));
    $expectedPalette = [
        '50' => '#fff5f1',
        '100' => '#ffe7df',
        '200' => '#ffcdbd',
        '300' => '#ffaa90',
        '400' => '#ff7f5c',
        '600' => '#cd431f',
        '700' => '#a63416',
        '800' => '#832c16',
        '900' => '#652617',
        '950' => '#36130a',
    ];

    $cssHexVariable = function (string $name) use ($css): string {
        $matched = preg_match(
            '/--'.preg_quote($name, '/').':\s*(#[0-9a-f]{6});/i',
            $css,
            $matches,
        );

        expect($matched, "Missing six-digit CSS variable --{$name}")->toBe(1);

        return strtolower($matches[1]);
    };

    $relativeLuminance = static function (string $hex): float {
        $channels = array_map(
            static fn (int $offset): float => hexdec(substr($hex, $offset, 2)) / 255,
            [1, 3, 5],
        );
        $linearChannels = array_map(
            static fn (float $channel): float => $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return ($linearChannels[0] * 0.2126)
            + ($linearChannels[1] * 0.7152)
            + ($linearChannels[2] * 0.0722);
    };

    $contrastRatio = static function (string $first, string $second) use ($relativeLuminance): float {
        $lighter = max($relativeLuminance($first), $relativeLuminance($second));
        $darker = min($relativeLuminance($first), $relativeLuminance($second));

        return ($lighter + 0.05) / ($darker + 0.05);
    };

    foreach ($expectedPalette as $step => $hex) {
        expect($cssHexVariable("brand-orange-{$step}"))->toBe($hex)
            ->and($css)->toContain("--color-orange-{$step}: var(--brand-orange-{$step});");
    }

    $signalOrange = $cssHexVariable('brand-orange');
    $deepCobalt = $cssHexVariable('brand-deep-cobalt');
    $accessibleOrange = $cssHexVariable('brand-orange-600');

    expect($signalOrange)
        ->toBe('#ff6038')
        ->and($css)
        ->toContain('--color-orange-500: var(--brand-orange);')
        ->toContain('--primary: var(--brand-orange);')
        ->toContain('--primary-foreground: var(--brand-deep-cobalt);')
        ->toContain('--ring: var(--brand-orange-600);')
        ->toContain('--sidebar-primary: var(--brand-orange);')
        ->toContain('--sidebar-primary-foreground: var(--brand-deep-cobalt);')
        ->and($contrastRatio($signalOrange, $deepCobalt))->toBeGreaterThanOrEqual(4.5)
        ->and($contrastRatio($signalOrange, '#ffffff'))->toBeLessThan(4.5)
        ->and($contrastRatio($accessibleOrange, '#ffffff'))->toBeGreaterThanOrEqual(4.5);
});

test('shared controls and presentation sources consume the Sutelio orange contract', function () {
    $button = File::get(resource_path('js/components/ui/button/index.ts'));
    $checkbox = File::get(resource_path('js/components/ui/checkbox/Checkbox.vue'));
    $presentationSource = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'vue'], true))
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($button)
        ->toContain('bg-orange-600 text-white')
        ->not->toContain('bg-primary text-primary-foreground')
        ->and($checkbox)
        ->toContain('data-[state=checked]:border-orange-600')
        ->toContain('data-[state=checked]:bg-orange-600')
        ->toContain('data-[state=checked]:text-white')
        ->and($presentationSource)
        ->not->toMatch('/bg-(?:orange-500|primary)[^\'"\n]*text-primary-foreground/')
        ->not->toContain('bg-orange-500 text-white')
        ->not->toContain('#f97316')
        ->not->toContain('rgba(249,115,22')
        ->not->toContain('rgba(234,88,12');
});
