<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

$createNativeBrandInstallerFixture = static function (
    string $buildConfiguration,
    string $manifest,
    ?string $destinationSymlinkTarget = null,
): array {
    $temporaryRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-native-brand-'.Str::uuid();
    $sourceAsset = 'drawable/sutelio-test.xml';
    $destination = $temporaryRoot.'/nativephp/android/app/src/main/res';

    File::ensureDirectoryExists($temporaryRoot.'/scripts');
    File::ensureDirectoryExists($temporaryRoot.'/resources/brand/android/drawable');
    File::ensureDirectoryExists(dirname($destination));

    if ($destinationSymlinkTarget === null) {
        File::ensureDirectoryExists($destination);
    } else {
        symlink($destinationSymlinkTarget, $destination);
    }

    File::copy(
        base_path('scripts/apply-native-brand.mjs'),
        $temporaryRoot.'/scripts/apply-native-brand.mjs',
    );
    File::put($temporaryRoot.'/resources/brand/android/'.$sourceAsset, '<resources/>');
    File::put($temporaryRoot.'/nativephp/android/app/build.gradle.kts', $buildConfiguration);
    File::put($temporaryRoot.'/nativephp/android/app/src/main/AndroidManifest.xml', $manifest);

    return [$temporaryRoot, $sourceAsset];
};

$runNativeBrandInstaller = static function (string $temporaryRoot): Process {
    $process = new Process(['node', 'scripts/apply-native-brand.mjs'], $temporaryRoot);
    $process->setTimeout(30);
    $process->run();

    return $process;
};

test('the approved Sutelio identity and package metadata are canonical', function () {
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $packageLock = json_decode(File::get(base_path('package-lock.json')), true, flags: JSON_THROW_ON_ERROR);
    $environment = File::get(base_path('.env.example'));
    $environmentLines = explode("\n", str_replace("\r\n", "\n", $environment));

    expect(config('app.name'))->toBe('Sutelio')
        ->and(config('nativephp.app_id'))->toBe('com.goleaf.sutelio')
        ->and(config('nativephp.deeplink_scheme'))->toBe('sutelio')
        ->and(config('nativephp.server.service_name'))->toBe('Sutelio')
        ->and(config('nativephp.app_store_connect.app_name'))->toBe('Sutelio')
        ->and(config('nativephp.android.theme.color_primary'))->toBe('#123C8B')
        ->and(config('nativephp.android.theme.color_primary_night'))->toBe('#0A285F')
        ->and(config('nativephp.android.theme.color_on_primary'))->toBe('#FFF8E9')
        ->and($composer['name'])->toBe('goleaf/sutelio')
        ->and($composer['description'])->toBe('Sutelio is a local-first, workspace-scoped task and collaboration application for web and NativePHP Mobile.')
        ->and($composer['keywords'])->toBe([
            'sutelio',
            'laravel',
            'nativephp',
            'sqlite',
            'task-management',
            'collaboration',
        ])
        ->and($package['name'])->toBe('sutelio')
        ->and($packageLock['name'])->toBe('sutelio')
        ->and($packageLock['packages']['']['name'])->toBe('sutelio');

    $expectedEnvironmentLines = [
        'APP_NAME' => 'APP_NAME="Sutelio"',
        'NATIVEPHP_APP_ID' => 'NATIVEPHP_APP_ID=com.goleaf.sutelio',
        'NATIVEPHP_DEEPLINK_SCHEME' => 'NATIVEPHP_DEEPLINK_SCHEME=sutelio',
        'NATIVEPHP_DEEPLINK_HOST' => 'NATIVEPHP_DEEPLINK_HOST=',
        'NATIVEPHP_SERVICE_NAME' => 'NATIVEPHP_SERVICE_NAME="Sutelio"',
        'NATIVEPHP_ANDROID_COLOR_PRIMARY' => 'NATIVEPHP_ANDROID_COLOR_PRIMARY="#123C8B"',
        'NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT' => 'NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT="#0A285F"',
        'NATIVEPHP_ANDROID_COLOR_ON_PRIMARY' => 'NATIVEPHP_ANDROID_COLOR_ON_PRIMARY="#FFF8E9"',
        'APP_STORE_APP_NAME' => 'APP_STORE_APP_NAME="Sutelio"',
    ];

    foreach ($expectedEnvironmentLines as $key => $expectedLine) {
        $matchingLines = array_values(array_filter(
            $environmentLines,
            static fn (string $line): bool => str_starts_with($line, "{$key}="),
        ));

        expect($matchingLines, $key)->toBe([$expectedLine]);
    }
});

test('the Sutelio configuration fallbacks evaluate independently of local environment', function () {
    $environmentKeys = [
        'APP_NAME',
        'NATIVEPHP_APP_ID',
        'NATIVEPHP_DEEPLINK_SCHEME',
        'NATIVEPHP_DEEPLINK_HOST',
        'NATIVEPHP_SERVICE_NAME',
        'NATIVEPHP_ANDROID_COLOR_PRIMARY',
        'NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT',
        'NATIVEPHP_ANDROID_COLOR_ON_PRIMARY',
        'APP_STORE_APP_NAME',
    ];
    $script = <<<'PHP'
require $argv[1];

$environmentKeys = array_slice($argv, 4);

foreach ($environmentKeys as $key) {
    putenv($key);
    unset($_ENV[$key], $_SERVER[$key]);
}

$app = require $argv[2];
$native = require $argv[3];

echo json_encode([
    'app_name' => $app['name'],
    'app_id' => $native['app_id'],
    'deeplink_scheme' => $native['deeplink_scheme'],
    'deeplink_host' => $native['deeplink_host'],
    'service_name' => $native['server']['service_name'],
    'app_store_name' => $native['app_store_connect']['app_name'],
    'color_primary' => $native['android']['theme']['color_primary'],
    'color_primary_night' => $native['android']['theme']['color_primary_night'],
    'color_on_primary' => $native['android']['theme']['color_on_primary'],
], JSON_THROW_ON_ERROR);
PHP;
    $process = new Process(
        [
            PHP_BINARY,
            '-r',
            $script,
            base_path('vendor/autoload.php'),
            config_path('app.php'),
            config_path('nativephp.php'),
            ...$environmentKeys,
        ],
        base_path(),
        array_fill_keys($environmentKeys, false),
    );
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful(), $process->getErrorOutput())
        ->toBeTrue();

    expect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))->toBe([
        'app_name' => 'Sutelio',
        'app_id' => 'com.goleaf.sutelio',
        'deeplink_scheme' => 'sutelio',
        'deeplink_host' => null,
        'service_name' => 'Sutelio',
        'app_store_name' => 'Sutelio',
        'color_primary' => '#123C8B',
        'color_primary_night' => '#0A285F',
        'color_on_primary' => '#FFF8E9',
    ]);
});

test('the remaining user-facing catalogs use Sutelio with locale contract parity', function () {
    $catalogs = ['onboarding', 'ui', 'workspace'];
    $locales = ['en', 'lt', 'ru'];

    foreach ($catalogs as $catalog) {
        $english = Arr::dot(trans($catalog, locale: 'en'));

        foreach ($locales as $locale) {
            $path = lang_path("{$locale}/{$catalog}.php");
            $source = File::get($path);
            $localized = Arr::dot(trans($catalog, locale: $locale));

            expect($source, $path)
                ->toContain('Sutelio')
                ->not->toContain('Xiaomi Mimo')
                ->and(array_keys($localized), $path)
                ->toBe(array_keys($english));

            foreach ($english as $key => $message) {
                preg_match_all('/:[A-Za-z_]+/', (string) $message, $englishPlaceholders);
                preg_match_all('/:[A-Za-z_]+/', (string) $localized[$key], $localizedPlaceholders);

                sort($englishPlaceholders[0]);
                sort($localizedPlaceholders[0]);

                expect($localizedPlaceholders[0], "Placeholder mismatch for {$locale}.{$catalog}.{$key}")
                    ->toBe($englishPlaceholders[0]);
            }
        }
    }
});

test('browser reminders use the Sutelio storage namespace', function () {
    $notificationsPage = File::get(resource_path('js/pages/notifications/Index.vue'));

    expect($notificationsPage)
        ->toContain('const storageKey = `sutelio:browser-reminder:${notification.id}`;')
        ->not->toContain('xiaomi-mimo:browser-reminder:');
});

test('backup downloads use the Sutelio public filename', function () {
    $controller = File::get(app_path('Http/Controllers/BackupController.php'));

    expect($controller)
        ->toContain("'sutelio-backup-'.now()->format('Ymd-His').'.sqlite'")
        ->not->toContain("'xiaomi-mimo-backup-'");
});

test('backup tests use isolated Sutelio temporary prefixes', function () {
    $backupTest = File::get(base_path('tests/Feature/DatabaseBackupTest.php'));
    $contractTest = File::get(base_path('tests/Feature/ApplicationLayerContractTest.php'));

    expect($backupTest)
        ->toContain("sys_get_temp_dir().'/sutelio-test-backup-'.Str::uuid()")
        ->not->toContain("sys_get_temp_dir().'/xiaomi-mimo-backup-'")
        ->and($contractTest)
        ->toContain("sys_get_temp_dir().'/sutelio-test-backup-contract-'.Str::uuid()")
        ->not->toContain("sys_get_temp_dir().'/xiaomi-mimo-backup-contract-'");
});

test('the pinned Instrument Sans inputs have the approved checksums', function () {
    expect(hash_file('sha256', resource_path('brand/fonts/InstrumentSans.ttf')))
        ->toBe('b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1')
        ->and(hash_file('sha256', resource_path('brand/fonts/OFL.txt')))
        ->toBe('9e27a72ed30eb49a08678f6a5d6ed98ec7ba5368f541637ee0683ec9134ef966');
});

test('the authentication logo keeps a visible forced-colors focus outline', function () {
    $layout = File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue'));

    expect($layout)
        ->toContain(
            'aria-label="Sutelio"',
            'focus-visible:ring-brand-cobalt',
            'focus-visible:outline-hidden',
        )
        ->not->toContain('focus-visible:outline-none');
});

test('the brand builder validates opaque rasters before failure-safe publication', function () {
    $builder = File::get(base_path('scripts/build-brand-assets.mjs'));

    expect($builder)
        ->toContain(
            'const opaquePngPaths = [',
            "execFileSync('php'",
            'function createOutputManifest',
            'function rollbackPublishedOutputs',
            'publishedEntries.push(entry);',
        )
        ->toMatch('/validateOpaquePngs\(buildDirectory\);[\s\S]*return createOutputManifest\(buildDirectory\);/')
        ->toMatch('/const manifest = buildBrandAssets\(buildDirectory\);[\s\S]*publishBuildOutputs\(buildDirectory, manifest\);/');
});

test('a handled brand publish failure restores every previous output and removes new files', function () {
    $temporaryRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-brand-publish-'.Str::uuid();
    $outputPaths = [
        'resources/brand/sutelio-mark.svg',
        'resources/brand/sutelio-wordmark.svg',
        'resources/brand/sutelio-lockup.svg',
        'resources/brand/sutelio-splash.svg',
        'resources/brand/sutelio-android-store-512.png',
        'resources/brand/android/drawable/ic_launcher_background.xml',
        'resources/brand/android/drawable/ic_launcher_foreground.xml',
        'resources/brand/android/drawable/ic_launcher_monochrome.xml',
        'resources/brand/android/mipmap-anydpi-v26/ic_launcher.xml',
        'resources/brand/android/mipmap-anydpi-v26/ic_launcher_round.xml',
        'resources/brand/android/mipmap-anydpi-v33/ic_launcher.xml',
        'resources/brand/android/mipmap-anydpi-v33/ic_launcher_round.xml',
        'resources/brand/android/values-v31/themes.xml',
        'resources/brand/android/values-night-v31/themes.xml',
        'public/icon.png',
        'public/apple-touch-icon.png',
        'public/splash.png',
        'public/splash-dark.png',
        'public/favicon.svg',
        'public/favicon.ico',
    ];
    $newOutput = 'resources/brand/sutelio-wordmark.svg';

    try {
        File::ensureDirectoryExists($temporaryRoot.'/scripts');
        File::copyDirectory(resource_path('brand'), $temporaryRoot.'/resources/brand');

        foreach (array_filter($outputPaths, static fn (string $path): bool => str_starts_with($path, 'public/')) as $path) {
            File::ensureDirectoryExists(dirname($temporaryRoot.'/'.$path));
            File::copy(base_path($path), $temporaryRoot.'/'.$path);
        }

        File::delete($temporaryRoot.'/'.$newOutput);

        $originalHashes = [];

        foreach (array_diff($outputPaths, [$newOutput]) as $path) {
            $originalHashes[$path] = hash_file('sha256', $temporaryRoot.'/'.$path);
        }

        $builder = File::get(base_path('scripts/build-brand-assets.mjs'));
        $needle = "            publishedEntries.push(entry);\n";
        $replacement = $needle."            if (relativePath === 'public/icon.png') {\n                throw new Error('Injected publish failure.');\n            }\n";
        $injectedBuilder = str_replace($needle, $replacement, $builder, $replacementCount);

        expect($replacementCount)->toBe(1);

        File::put($temporaryRoot.'/scripts/build-brand-assets.mjs', $injectedBuilder);

        $process = new Process(['node', 'scripts/build-brand-assets.mjs'], $temporaryRoot);
        $process->setTimeout(30);
        $process->run();

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Injected publish failure.');

        foreach ($originalHashes as $path => $hash) {
            expect(hash_file('sha256', $temporaryRoot.'/'.$path), $path)->toBe($hash);
        }

        expect(File::exists($temporaryRoot.'/'.$newOutput))->toBeFalse();

        $publishArtifacts = collect(File::allFiles($temporaryRoot))
            ->map(static fn (SplFileInfo $file): string => $file->getFilename())
            ->filter(static fn (string $filename): bool => str_contains($filename, '.sutelio-stage-') || str_contains($filename, '.sutelio-backup-'));

        expect($publishArtifacts)->toBeEmpty();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects a stale application ID with a canonical label', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    [$temporaryRoot, $sourceAsset] = $createNativeBrandInstallerFixture(
        'applicationId = "com.goleaf.xiaomimimo"',
        '<application android:label="Sutelio"/>',
    );

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Generated NativePHP application ID must be com.goleaf.sutelio; found com.goleaf.xiaomimimo.')
            ->and(File::exists($temporaryRoot.'/nativephp/android/app/src/main/res/'.$sourceAsset))
            ->toBeFalse();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects a stale label with a canonical application ID', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    [$temporaryRoot, $sourceAsset] = $createNativeBrandInstallerFixture(
        'applicationId = "com.goleaf.sutelio"',
        '<application android:label="Xiaomi Mimo"/>',
    );

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Generated NativePHP manifest label must be Sutelio; found Xiaomi Mimo.')
            ->and(File::exists($temporaryRoot.'/nativephp/android/app/src/main/res/'.$sourceAsset))
            ->toBeFalse();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects canonical identity decoys in comments', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    [$temporaryRoot, $sourceAsset] = $createNativeBrandInstallerFixture(
        "/* applicationId = \"com.goleaf.sutelio\" */\n// applicationId = \"com.goleaf.sutelio\"\napplicationId = \"com.goleaf.xiaomimimo\"",
        "<!-- <application android:label=\"Sutelio\"/> -->\n<application android:label=\"Xiaomi Mimo\"/>",
    );

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Generated NativePHP application ID must be com.goleaf.sutelio; found com.goleaf.xiaomimimo.')
            ->and(File::exists($temporaryRoot.'/nativephp/android/app/src/main/res/'.$sourceAsset))
            ->toBeFalse();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects a symlink destination without writing outside the workspace', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    $externalRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-native-brand-external-'.Str::uuid();
    File::ensureDirectoryExists($externalRoot);
    [$temporaryRoot, $sourceAsset] = $createNativeBrandInstallerFixture(
        'applicationId = "com.goleaf.sutelio"',
        '<application android:label="Sutelio"/>',
        $externalRoot,
    );

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('NativePHP resource destination must not contain a symbolic link')
            ->and(File::exists($externalRoot.'/'.$sourceAsset))
            ->toBeFalse();
    } finally {
        File::deleteDirectory($temporaryRoot);
        File::deleteDirectory($externalRoot);
    }
});

test('the native brand installer copies assets for the active canonical identity', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    [$temporaryRoot, $sourceAsset] = $createNativeBrandInstallerFixture(
        'applicationId = "com.goleaf.sutelio"',
        '<application android:label="Sutelio"/>',
    );

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful(), $process->getErrorOutput())
            ->toBeTrue()
            ->and($process->getOutput())
            ->toContain('Applied Sutelio adaptive, monochrome, and Android 12+ splash resources.')
            ->and(File::get($temporaryRoot.'/nativephp/android/app/src/main/res/'.$sourceAsset))
            ->toBe('<resources/>');
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the npm metadata exposes the approved Sutelio brand commands', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $packageLock = json_decode(File::get(base_path('package-lock.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($package['name'])->toBe('sutelio')
        ->and($package['scripts']['brand:build'])->toBe('node scripts/build-brand-assets.mjs')
        ->and($package['scripts']['brand:native'])->toBe('node scripts/apply-native-brand.mjs')
        ->and($packageLock['name'])->toBe('sutelio')
        ->and($packageLock['packages']['']['name'])->toBe('sutelio');
});

test('the complete canonical brand export set is tracked', function () {
    $expectedAssets = [
        'resources/brand/sutelio-mark.svg',
        'resources/brand/sutelio-wordmark.svg',
        'resources/brand/sutelio-lockup.svg',
        'resources/brand/sutelio-splash.svg',
        'resources/brand/sutelio-android-store-512.png',
        'resources/brand/android/drawable/ic_launcher_background.xml',
        'resources/brand/android/drawable/ic_launcher_foreground.xml',
        'resources/brand/android/drawable/ic_launcher_monochrome.xml',
        'resources/brand/android/mipmap-anydpi-v26/ic_launcher.xml',
        'resources/brand/android/mipmap-anydpi-v26/ic_launcher_round.xml',
        'resources/brand/android/mipmap-anydpi-v33/ic_launcher.xml',
        'resources/brand/android/mipmap-anydpi-v33/ic_launcher_round.xml',
        'resources/brand/android/values-v31/themes.xml',
        'resources/brand/android/values-night-v31/themes.xml',
        'public/icon.png',
        'public/apple-touch-icon.png',
        'public/splash.png',
        'public/splash-dark.png',
        'public/favicon.svg',
        'public/favicon.ico',
    ];

    foreach ($expectedAssets as $path) {
        expect(File::isFile(base_path($path)), $path)->toBeTrue();
    }

    expect(File::get(public_path('favicon.svg')))
        ->toBe(File::get(resource_path('brand/sutelio-mark.svg')));

    $favicon = getimagesize(public_path('favicon.ico'));

    expect($favicon)->not->toBeFalse();

    if ($favicon === false) {
        return;
    }

    expect([$favicon[0], $favicon[1]])->toBe([32, 32])
        ->and($favicon[2])->toBe(IMAGETYPE_ICO)
        ->and($favicon['mime'])->toBe('image/vnd.microsoft.icon');
});

test('the clean-S master artwork uses the locked geometry and colors', function () {
    $mark = File::get(resource_path('brand/sutelio-mark.svg'));
    $wordmark = File::get(resource_path('brand/sutelio-wordmark.svg'));
    $lockup = File::get(resource_path('brand/sutelio-lockup.svg'));

    expect($mark)
        ->toContain(
            'viewBox="0 0 512 512"',
            '<rect width="512" height="512" rx="112" fill="#123C8B"/>',
            '<circle cx="256" cy="256" r="179.2" fill="#FF6038"/>',
            '<path d="',
            'transform="translate(149.73 376.14) scale(1.3)"',
            'fill="#FFF8E9"',
        )
        ->toMatch('/<path d="[^"]+" transform="translate\(149\.73 376\.14\) scale\(1\.3\)" fill="#FFF8E9"\/>/')
        ->not->toContain('<text', '<line', '<linearGradient', '<radialGradient', 'stroke=', 'style=');

    expect($wordmark)
        ->toContain(
            'viewBox="0 0 847 312.3125"',
            'data-wordmark="Sutelio"',
            '<defs>',
            'id="glyph-0-0"',
            '<path d="',
            '<use xlink:href="#glyph-',
            '<g fill="#0A285F">',
        )
        ->toMatch('/<defs>[\s\S]*<g id="glyph-0-0">\s*<path d="[^"]+"\/>\s*<\/g>[\s\S]*<\/defs>/')
        ->toMatch('/<use xlink:href="#glyph-[^"]+"[^>]*\/>/')
        ->not->toContain('<text', '<line', '<linearGradient', '<radialGradient', 'stroke=', 'style=', '#FF6038');

    expect($lockup)
        ->toContain(
            'viewBox="0 0 1280 512"',
            '<rect width="512" height="512" rx="112" fill="#123C8B"/>',
            '<circle cx="256" cy="256" r="179.2" fill="#FF6038"/>',
            '<path d="',
            'transform="translate(149.73 376.14) scale(1.3)"',
            'fill="#FFF8E9"',
            '<defs>',
            '<use xlink:href="#glyph-',
            '<g transform="translate(576 100) scale(.78)" fill="#0A285F">',
        )
        ->toMatch('/<path d="[^"]+" transform="translate\(149\.73 376\.14\) scale\(1\.3\)" fill="#FFF8E9"\/>/')
        ->toMatch('/<use xlink:href="#glyph-[^"]+"[^>]*\/>/')
        ->not->toContain('<text', '<line', '<linearGradient', '<radialGradient', 'stroke=', 'style=');
});

test('the Android source overrides use the approved adaptive and splash contract', function () {
    $androidAsset = static fn (string $path): string => File::get(resource_path("brand/android/{$path}"));
    $background = $androidAsset('drawable/ic_launcher_background.xml');
    $foreground = $androidAsset('drawable/ic_launcher_foreground.xml');
    $monochrome = $androidAsset('drawable/ic_launcher_monochrome.xml');
    $adaptiveIcon = static fn (bool $withMonochrome): string => '<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android"><background android:drawable="@drawable/ic_launcher_background"/><foreground android:drawable="@drawable/ic_launcher_foreground"/>'.($withMonochrome ? '<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>' : '').'</adaptive-icon>'.PHP_EOL;
    $androidTheme = static fn (string $primary): string => '<resources><style name="Theme.AndroidPHP" parent="Theme.MaterialComponents.DayNight.DarkActionBar"><item name="colorPrimary">'.$primary.'</item><item name="colorPrimaryVariant">'.$primary.'</item><item name="colorOnPrimary">#FFF8E9</item><item name="colorAccent">#FF6038</item><item name="android:colorAccent">#FF6038</item><item name="android:windowDrawsSystemBarBackgrounds">true</item><item name="android:statusBarColor">@android:color/transparent</item><item name="android:navigationBarColor">@android:color/transparent</item><item name="android:enforceStatusBarContrast">false</item><item name="android:enforceNavigationBarContrast">false</item><item name="android:windowSplashScreenBackground">#FFF8E9</item><item name="android:windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item><item name="android:windowSplashScreenIconBackgroundColor">#123C8B</item></style></resources>'.PHP_EOL;

    expect($background)
        ->toBe('<shape xmlns:android="http://schemas.android.com/apk/res/android" android:shape="rectangle"><solid android:color="#123C8B"/></shape>'.PHP_EOL)
        ->and($foreground)
        ->toContain(
            'android:viewportWidth="512"',
            'android:viewportHeight="512"',
            'android:scaleX="0.86"',
            'android:scaleY="0.86"',
            'android:fillColor="#FF6038"',
            'android:pathData="M256,76.8 A179.2,179.2 0 1,1 255.999,435.2 A179.2,179.2 0 1,1 256,76.8 Z"',
            'android:translateX="149.73"',
            'android:translateY="376.14"',
            'android:fillColor="#FFF8E9"',
        )
        ->and($monochrome)
        ->toContain(
            'android:viewportWidth="512"',
            'android:viewportHeight="512"',
            'android:scaleX="0.86"',
            'android:scaleY="0.86"',
            'android:translateX="149.73"',
            'android:translateY="376.14"',
            'android:fillColor="#FF000000"',
        )
        ->not->toContain('#FF6038', '#FFF8E9');

    foreach (['ic_launcher.xml', 'ic_launcher_round.xml'] as $filename) {
        expect($androidAsset("mipmap-anydpi-v26/{$filename}"))
            ->toBe($adaptiveIcon(false))
            ->not->toContain('<monochrome')
            ->and($androidAsset("mipmap-anydpi-v33/{$filename}"))
            ->toBe($adaptiveIcon(true))
            ->toContain('<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>');
    }

    expect($androidAsset('values-v31/themes.xml'))
        ->toBe($androidTheme('#123C8B'))
        ->and($androidAsset('values-night-v31/themes.xml'))
        ->toBe($androidTheme('#0A285F'));
});

test('tracked browser and NativePHP raster assets have the required dimensions', function () {
    $assets = [
        'public/icon.png' => [1024, 1024],
        'public/apple-touch-icon.png' => [180, 180],
        'public/splash.png' => [1080, 1920],
        'public/splash-dark.png' => [1080, 1920],
        'resources/brand/sutelio-android-store-512.png' => [512, 512],
    ];

    foreach ($assets as $path => $expectedDimensions) {
        $imageInfo = getimagesize(base_path($path));

        expect($imageInfo, $path)->not->toBeFalse();

        if ($imageInfo === false) {
            continue;
        }

        [$width, $height] = $imageInfo;

        expect([$width, $height], $path)->toBe($expectedDimensions);

        $image = imagecreatefrompng(base_path($path));

        expect($image, $path)->not->toBeFalse();

        if ($image === false) {
            continue;
        }

        $maxAlpha = 0;
        $isTrueColor = imageistruecolor($image);

        for ($y = 0; $y < imagesy($image); $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                $colorIndex = imagecolorat($image, $x, $y);

                if ($isTrueColor) {
                    $alpha = ($colorIndex >> 24) & 0x7F;
                } else {
                    $color = imagecolorsforindex($image, $colorIndex);
                    $alpha = is_array($color) ? $color['alpha'] : 127;
                }

                $maxAlpha = max($maxAlpha, $alpha);
            }
        }

        expect($maxAlpha, $path)->toBe(0);
    }
});
