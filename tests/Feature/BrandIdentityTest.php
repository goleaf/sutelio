<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

$nativeBrandAndroidAssets = [
    'drawable/ic_launcher_background.xml',
    'drawable/ic_launcher_foreground.xml',
    'drawable/ic_launcher_monochrome.xml',
    'mipmap-anydpi-v26/ic_launcher.xml',
    'mipmap-anydpi-v26/ic_launcher_round.xml',
    'mipmap-anydpi-v33/ic_launcher.xml',
    'mipmap-anydpi-v33/ic_launcher_round.xml',
    'values-v31/themes.xml',
    'values-night-v31/themes.xml',
];

$nativeBrandPublishedFiles = [
    'nativephp/android/app/build.gradle.kts',
    'nativephp/android/app/src/main/AndroidManifest.xml',
    'nativephp/android/app/src/main/ic_launcher-playstore.png',
    'nativephp/android/app/src/main/java/com/nativephp/mobile/bridge/PHPBridge.kt',
    'nativephp/android/app/src/main/java/com/nativephp/mobile/network/PHPWebViewClient.kt',
    'nativephp/android/app/src/main/java/com/nativephp/mobile/network/WebViewManager.kt',
    'nativephp/android/app/src/main/java/com/nativephp/mobile/security/LaravelCookieStore.kt',
    'nativephp/android/app/src/main/java/com/nativephp/mobile/security/LaravelSecurity.kt',
    'nativephp/ios/NativePHP.xcodeproj/project.pbxproj',
    'nativephp/ios/NativePHP/Info.plist',
    'nativephp/ios/NativePHP-simulator-Info.plist',
    'nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png',
    'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png',
    'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash-dark.png',
    ...array_map(
        static fn (string $path): string => 'nativephp/android/app/src/main/res/'.$path,
        $nativeBrandAndroidAssets,
    ),
];

$createNativeBrandInstallerFixture = static function (
    ?string $destinationSymlinkTarget = null,
): string {
    $temporaryRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-native-brand-'.Str::uuid();
    $destination = $temporaryRoot.'/nativephp/android/app/src/main/res';

    File::ensureDirectoryExists($temporaryRoot.'/scripts');
    File::ensureDirectoryExists(dirname($destination));
    File::ensureDirectoryExists($temporaryRoot.'/nativephp/ios/NativePHP');
    File::ensureDirectoryExists($temporaryRoot.'/resources/brand');
    File::ensureDirectoryExists($temporaryRoot.'/public');
    File::ensureDirectoryExists($temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/src/main');
    File::ensureDirectoryExists($temporaryRoot.'/vendor/nativephp/mobile/resources/xcode/NativePHP');

    if ($destinationSymlinkTarget === null) {
        File::copyDirectory(
            base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/res'),
            $destination,
        );
    } else {
        symlink($destinationSymlinkTarget, $destination);
    }

    File::copy(
        base_path('scripts/apply-native-brand.mjs'),
        $temporaryRoot.'/scripts/apply-native-brand.mjs',
    );
    File::copyDirectory(
        base_path('resources/brand/android'),
        $temporaryRoot.'/resources/brand/android',
    );
    File::copyDirectory(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP.xcodeproj'),
        $temporaryRoot.'/nativephp/ios/NativePHP.xcodeproj',
    );
    File::copyDirectory(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP/Assets.xcassets'),
        $temporaryRoot.'/nativephp/ios/NativePHP/Assets.xcassets',
    );
    File::copyDirectory(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/res'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/src/main/res',
    );
    File::copyDirectory(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP.xcodeproj'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/xcode/NativePHP.xcodeproj',
    );
    File::copyDirectory(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP/Assets.xcassets'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/xcode/NativePHP/Assets.xcassets',
    );

    foreach (['icon.png', 'splash.png', 'splash-dark.png'] as $asset) {
        File::copy(base_path('public/'.$asset), $temporaryRoot.'/public/'.$asset);
    }

    File::copy(
        base_path('resources/brand/sutelio-android-store-512.png'),
        $temporaryRoot.'/resources/brand/sutelio-android-store-512.png',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/build.gradle.kts'),
        $temporaryRoot.'/nativephp/android/app/build.gradle.kts',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/build.gradle.kts'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/build.gradle.kts',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/AndroidManifest.xml'),
        $temporaryRoot.'/nativephp/android/app/src/main/AndroidManifest.xml',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/AndroidManifest.xml'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/src/main/AndroidManifest.xml',
    );

    foreach ([
        'java/com/nativephp/mobile/bridge/PHPBridge.kt',
        'java/com/nativephp/mobile/network/PHPWebViewClient.kt',
        'java/com/nativephp/mobile/network/WebViewManager.kt',
        'java/com/nativephp/mobile/security/LaravelCookieStore.kt',
        'java/com/nativephp/mobile/security/LaravelSecurity.kt',
    ] as $androidSourcePath) {
        $templatePath = 'vendor/nativephp/mobile/resources/androidstudio/app/src/main/'.$androidSourcePath;
        $generatedPath = 'nativephp/android/app/src/main/'.$androidSourcePath;

        File::ensureDirectoryExists(dirname($temporaryRoot.'/'.$templatePath));
        File::ensureDirectoryExists(dirname($temporaryRoot.'/'.$generatedPath));
        File::copy(base_path($templatePath), $temporaryRoot.'/'.$templatePath);
        File::copy(base_path($templatePath), $temporaryRoot.'/'.$generatedPath);
    }
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/ic_launcher-playstore.png'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/src/main/ic_launcher-playstore.png',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/androidstudio/app/src/main/ic_launcher-playstore.png'),
        $temporaryRoot.'/nativephp/android/app/src/main/ic_launcher-playstore.png',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP/Info.plist'),
        $temporaryRoot.'/nativephp/ios/NativePHP/Info.plist',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP/Info.plist'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/xcode/NativePHP/Info.plist',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP-simulator-Info.plist'),
        $temporaryRoot.'/nativephp/ios/NativePHP-simulator-Info.plist',
    );
    File::copy(
        base_path('vendor/nativephp/mobile/resources/xcode/NativePHP-simulator-Info.plist'),
        $temporaryRoot.'/vendor/nativephp/mobile/resources/xcode/NativePHP-simulator-Info.plist',
    );

    return $temporaryRoot;
};

$runNativeBrandInstaller = static function (string $temporaryRoot): Process {
    $process = new Process(['node', 'scripts/apply-native-brand.mjs'], $temporaryRoot);
    $process->setTimeout(30);
    $process->run();

    return $process;
};

$snapshotNativeBrandFixture = static function (
    string $temporaryRoot,
    array $paths,
): array {
    return collect($paths)
        ->mapWithKeys(static fn (string $path): array => [
            $path => File::exists($temporaryRoot.'/'.$path)
                ? hash_file('sha256', $temporaryRoot.'/'.$path)
                : null,
        ])
        ->all();
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

$legacyIdentities = collect([
    'Xiaomi'.' Mimo',
    'xiaomi'.'-mimo',
    'xiaomi'.'mimo',
    'com.goleaf.'.'xiaomi'.'mimo',
    'laravel/'.'vue-starter-kit',
])->map(static fn (string $identity): string => Str::lower($identity));

$assertNoLegacyIdentity = static function (string $projectRoot) use ($legacyIdentities): void {
    $documentationIndex = File::get($projectRoot.'/docs/index.md');
    $canonicalInventory = Str::between(
        $documentationIndex,
        '## Canonical Documents',
        '## Historical Evidence',
    );

    preg_match_all('/^\| `([^`]+)`\s+\|/m', $canonicalInventory, $matches);

    $canonicalFiles = collect($matches[1])->unique()->values();
    $archiveOptionalCanonicalFiles = collect([
        'CHANGELOG.md',
        'README.md',
    ]);
    $requiredCanonicalFiles = [
        'AGENTS.md',
        'README.md',
        'CHANGELOG.md',
        'docs/index.md',
        'docs/requirements.md',
        'docs/product-requirements.md',
        'docs/non-functional-requirements.md',
        'docs/architecture.md',
        'docs/frontend.md',
        'docs/design-system.md',
        'docs/accessibility.md',
        'docs/localization.md',
        'docs/testing.md',
        'docs/deployment.md',
        'docs/current-state.md',
        'docs/current-state-audit.md',
        'docs/implementation-plan.md',
        'docs/compliance-matrix.md',
        'docs/code-review.md',
        'docs/known-limitations.md',
        'docs/progress.md',
    ];
    $exactExportIgnoredFiles = collect(preg_split('/\R/', File::get($projectRoot.'/.gitattributes')))
        ->map(static function (string $line): ?string {
            if (preg_match('/^([^\s*?\[\]{}]+)\s+export-ignore(?:\s|$)/', trim($line), $match) !== 1) {
                return null;
            }

            return ltrim($match[1], '/');
        })
        ->filter()
        ->values();
    $hasRepositoryMetadata = File::exists($projectRoot.'/.git');

    expect($canonicalFiles->all())->toContain(...$requiredCanonicalFiles);
    expect($exactExportIgnoredFiles->all())->toContain(...$archiveOptionalCanonicalFiles);

    foreach ($canonicalFiles as $canonicalFile) {
        if (File::isFile($projectRoot.'/'.$canonicalFile)) {
            continue;
        }

        $isExactArchiveOptionalFile = ! $hasRepositoryMetadata
            && $archiveOptionalCanonicalFiles->contains($canonicalFile)
            && $exactExportIgnoredFiles->contains($canonicalFile);

        expect($isExactArchiveOptionalFile, $canonicalFile)->toBeTrue();
    }

    $excludedExactPaths = [
        'docs/current-state-audit.md',
        'docs/progress.md',
    ];
    $excludedPathPrefixes = [
        '.git',
        '.mimocode',
        'bootstrap/cache',
        'docs/audit',
        'docs/audits',
        'docs/plans',
        'docs/superpowers/plans',
        'docs/superpowers/specs',
        'graphify-out',
        'nativephp',
        'node_modules',
        'public/build',
        'storage',
        'tests',
        'vendor',
    ];
    $secretDirectoryNames = ['credentials', 'secrets'];
    $secretExtensions = ['cer', 'crt', 'jks', 'key', 'keystore', 'p12', 'pem', 'pfx'];
    $secretFilenames = [
        'client-secret.json',
        'client_secret.json',
        'credentials.json',
        'secrets.json',
        'service-account.json',
        'service_account.json',
    ];
    $textExtensions = [
        'cjs',
        'css',
        'graphql',
        'htm',
        'html',
        'ini',
        'js',
        'json',
        'jsx',
        'lock',
        'md',
        'mjs',
        'neon',
        'php',
        'properties',
        'sh',
        'svg',
        'toml',
        'ts',
        'tsx',
        'txt',
        'vue',
        'xml',
        'yaml',
        'yml',
    ];
    $extensionlessTextFiles = [
        '.editorconfig',
        '.env.example',
        '.gitattributes',
        '.gitignore',
        '.npmrc',
        '.prettierignore',
        '.prettierrc',
        '.htaccess',
        'artisan',
        'native',
    ];
    $requiredSentinelPaths = [
        '.ai/rules/index.md',
        '.env.example',
        '.github/workflows/tests.yml',
        'AGENTS.md',
        'app/Models/User.php',
        'bootstrap/app.php',
        'composer.lock',
        'config/app.php',
        'database/factories/UserFactory.php',
        'docs/decisions/0001-preserve-inertia-vue.md',
        'lang/en/ui.php',
        'public/.htaccess',
        'resources/brand/sutelio-mark.svg',
        'resources/css/app.css',
        'resources/js/app.ts',
        'resources/views/app.blade.php',
        'routes/web.php',
        'scripts/apply-native-brand.mjs',
    ];

    $isExcludedPath = static fn (string $relativePath): bool => collect($excludedPathPrefixes)
        ->contains(static fn (string $prefix): bool => $relativePath === $prefix
            || str_starts_with($relativePath, $prefix.'/'));
    $isSecretPath = static function (SplFileInfo $file, string $relativePath) use ($secretDirectoryNames, $secretExtensions, $secretFilenames): bool {
        $normalizedPath = Str::lower($relativePath);
        $filename = Str::lower($file->getFilename());
        $pathSegments = explode('/', $normalizedPath);

        if ($filename === '.env' || (str_starts_with($filename, '.env.') && $filename !== '.env.example')) {
            return true;
        }

        return in_array($filename, $secretFilenames, true)
            || array_intersect($secretDirectoryNames, $pathSegments) !== []
            || in_array(Str::lower($file->getExtension()), $secretExtensions, true);
    };
    $isAllowedTextFile = static function (SplFileInfo $file, string $relativePath) use ($excludedExactPaths, $extensionlessTextFiles, $isExcludedPath, $isSecretPath, $textExtensions): bool {
        if (in_array($relativePath, $excludedExactPaths, true)
            || $isExcludedPath($relativePath)
            || $isSecretPath($file, $relativePath)) {
            return false;
        }

        return in_array(Str::lower($file->getExtension()), $textExtensions, true)
            || in_array($file->getFilename(), $extensionlessTextFiles, true);
    };
    $paths = collect(File::allFiles($projectRoot, hidden: true))
        ->map(static fn (SplFileInfo $file): array => [
            'file' => $file,
            'relative_path' => str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                Str::after($file->getPathname(), rtrim($projectRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR),
            ),
        ])
        ->filter(static fn (array $candidate): bool => $isAllowedTextFile(
            $candidate['file'],
            $candidate['relative_path'],
        ))
        ->values();
    $scannedRelativePaths = $paths->pluck('relative_path');

    expect($scannedRelativePaths->all())->toContain(...$requiredSentinelPaths);

    foreach ($paths as $candidate) {
        $normalizedSource = Str::lower(File::get($candidate['file']->getPathname()));

        foreach ($legacyIdentities as $legacyIdentity) {
            expect($normalizedSource, $candidate['relative_path'])->not->toContain($legacyIdentity);
        }
    }

    $currentStateAuditPath = $projectRoot.'/docs/current-state-audit.md';
    $currentStateAudit = File::get($currentStateAuditPath);
    $historicalHeading = '## Historical Xiaomi'.' Mimo Evidence';

    expect(substr_count($currentStateAudit, $historicalHeading), $currentStateAuditPath)->toBe(1);

    $activeCurrentStateAudit = Str::lower(Str::before($currentStateAudit, $historicalHeading));

    foreach ($legacyIdentities as $legacyIdentity) {
        expect($activeCurrentStateAudit, $currentStateAuditPath)->not->toContain($legacyIdentity);
    }
};

test('active first-party files contain no legacy product package or starter identity', function () use ($assertNoLegacyIdentity) {
    $assertNoLegacyIdentity(base_path());
});

test('the active identity guard accepts the standard repository archive', function () use ($assertNoLegacyIdentity) {
    if (! File::exists(base_path('.git'))) {
        $assertNoLegacyIdentity(base_path());

        return;
    }

    $temporaryRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-archive-'.Str::uuid();
    $archivePath = $temporaryRoot.'/repository.tar';
    $archiveRoot = $temporaryRoot.'/repository';

    File::ensureDirectoryExists($archiveRoot);

    try {
        $archive = new Process(['git', 'archive', '--format=tar', '--output='.$archivePath, 'HEAD'], base_path());
        $archive->setTimeout(30);
        $archive->run();

        expect($archive->isSuccessful(), $archive->getErrorOutput())->toBeTrue();

        $extract = new Process(['tar', '-xf', $archivePath, '-C', $archiveRoot], base_path());
        $extract->setTimeout(30);
        $extract->run();

        expect($extract->isSuccessful(), $extract->getErrorOutput())->toBeTrue()
            ->and(File::exists($archiveRoot.'/.git'))->toBeFalse()
            ->and(File::exists($archiveRoot.'/README.md'))->toBeFalse()
            ->and(File::exists($archiveRoot.'/CHANGELOG.md'))->toBeFalse();

        $assertNoLegacyIdentity($archiveRoot);
    } finally {
        File::deleteDirectory($temporaryRoot);
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
                ->not->toContain('Xiaomi'.' Mimo')
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
        ->not->toContain('xiaomi'.'-mimo:browser-reminder:');
});

test('backup downloads use the Sutelio public filename', function () {
    $controller = File::get(app_path('Http/Controllers/BackupController.php'));

    expect($controller)
        ->toContain("'sutelio-backup-'.now()->format('Ymd-His').'.sqlite'")
        ->not->toContain("'xiaomi"."-mimo-backup-'");
});

test('backup tests use isolated Sutelio temporary prefixes', function () {
    $backupTest = File::get(base_path('tests/Feature/DatabaseBackupTest.php'));
    $contractTest = File::get(base_path('tests/Feature/ApplicationLayerContractTest.php'));

    expect($backupTest)
        ->toContain("sys_get_temp_dir().'/sutelio-test-backup-'.Str::uuid()")
        ->not->toContain("sys_get_temp_dir().'/xiaomi"."-mimo-backup-'")
        ->and($contractTest)
        ->toContain("sys_get_temp_dir().'/sutelio-test-backup-contract-'.Str::uuid()")
        ->not->toContain("sys_get_temp_dir().'/xiaomi"."-mimo-backup-contract-'");
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

test('the native brand installer canonicalizes a fresh NativePHP template and is idempotent', function () use ($createNativeBrandInstallerFixture, $nativeBrandAndroidAssets, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful(), $process->getErrorOutput().$process->getOutput())
            ->toBeTrue()
            ->and($process->getOutput())
            ->toContain('Applied canonical Sutelio identity and native assets for Android and iOS.');

        $gradle = File::get($temporaryRoot.'/nativephp/android/app/build.gradle.kts');
        $manifest = File::get($temporaryRoot.'/nativephp/android/app/src/main/AndroidManifest.xml');
        $project = File::get($temporaryRoot.'/nativephp/ios/NativePHP.xcodeproj/project.pbxproj');
        $webViewManager = File::get($temporaryRoot.'/nativephp/android/app/src/main/java/com/nativephp/mobile/network/WebViewManager.kt');
        $phpWebViewClient = File::get($temporaryRoot.'/nativephp/android/app/src/main/java/com/nativephp/mobile/network/PHPWebViewClient.kt');
        $phpBridge = File::get($temporaryRoot.'/nativephp/android/app/src/main/java/com/nativephp/mobile/bridge/PHPBridge.kt');
        $cookieStore = File::get($temporaryRoot.'/nativephp/android/app/src/main/java/com/nativephp/mobile/security/LaravelCookieStore.kt');
        $laravelSecurity = File::get($temporaryRoot.'/nativephp/android/app/src/main/java/com/nativephp/mobile/security/LaravelSecurity.kt');

        expect($gradle)
            ->toContain('namespace = "com.nativephp.mobile"')
            ->toContain('applicationId = "com.goleaf.sutelio"')
            ->not->toContain('applicationId = "REPLACE_APP_ID"')
            ->not->toContain('Android-Request-Inspector-WebView')
            ->and($manifest)
            ->toContain('android:label="Sutelio"')
            ->toContain('<!-- NATIVEPHP-DEEPLINKS-START -->')
            ->toContain('<data android:scheme="sutelio" />')
            ->not->toContain('android:host=')
            ->and(substr_count($manifest, '<!-- NATIVEPHP-DEEPLINKS-START -->'))
            ->toBe(1)
            ->and(substr_count($manifest, '<!-- NATIVEPHP-DEEPLINKS-END -->'))
            ->toBe(1)
            ->and(substr_count($project, 'PRODUCT_BUNDLE_IDENTIFIER = com.goleaf.sutelio;'))
            ->toBe(4)
            ->and(substr_count($project, 'INFOPLIST_KEY_CFBundleDisplayName = "Sutelio";'))
            ->toBe(4)
            ->and(substr_count($project, 'PRODUCT_BUNDLE_IDENTIFIER = com.nativephp.NativePHPTests;'))
            ->toBe(2)
            ->and(substr_count($project, 'PRODUCT_BUNDLE_IDENTIFIER = com.nativephp.NativePHPUITests;'))
            ->toBe(2)
            ->and($webViewManager)
            ->toContain('context.applicationInfo.flags and android.content.pm.ApplicationInfo.FLAG_DEBUGGABLE != 0')
            ->not->toContain('RequestInspectorWebViewClient')
            ->not->toContain('request.requestHeaders.forEach')
            ->not->toContain('JS provided token: $token')
            ->not->toContain('reqId=$requestId')
            ->and($phpWebViewClient)
            ->not->toContain('RESPONSE HEADERS: ${responseHeaders}')
            ->not->toContain('Final request headers: $headers')
            ->not->toContain('Setting cookie from response: $value')
            ->not->toContain('Stored cookie from Set-Cookie header: $cookie')
            ->and($phpBridge)
            ->not->toContain('Response first 200 chars: ${response.take(200)}')
            ->not->toContain('Cookie line: $cookieLine')
            ->not->toContain('Stored cookie: $cookieValue')
            ->and($cookieStore)
            ->not->toContain('Stored cookie: $name=$value')
            ->not->toContain('Cookie header: $cookieString')
            ->not->toContain('→ $key = $value')
            ->and($laravelSecurity)
            ->not->toContain('Extracted CSRF token from JSON: $csrfToken')
            ->not->toContain('Extracted CSRF token from form: $csrfToken')
            ->not->toContain('Stored CSRF token manually: $token');

        foreach (['NativePHP/Info.plist', 'NativePHP-simulator-Info.plist'] as $plistPath) {
            $plist = File::get($temporaryRoot.'/nativephp/ios/'.$plistPath);

            expect(substr_count($plist, '<string>com.goleaf.sutelio</string>'), $plistPath)
                ->toBe(1)
                ->and(substr_count($plist, '<string>sutelio</string>'), $plistPath)
                ->toBe(1)
                ->and($plist, $plistPath)
                ->not->toContain('<string>com.nativephp.app</string>')
                ->not->toContain('<string>nativephp</string>');
        }

        $assetMappings = [
            'resources/brand/sutelio-android-store-512.png' => 'nativephp/android/app/src/main/ic_launcher-playstore.png',
            'public/icon.png' => 'nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png',
            'public/splash.png' => 'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png',
            'public/splash-dark.png' => 'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash-dark.png',
        ];

        foreach ($nativeBrandAndroidAssets as $path) {
            $assetMappings['resources/brand/android/'.$path] = 'nativephp/android/app/src/main/res/'.$path;
        }

        foreach ($assetMappings as $source => $destination) {
            expect(hash_file('sha256', $temporaryRoot.'/'.$destination), $destination)
                ->toBe(hash_file('sha256', $temporaryRoot.'/'.$source));
        }

        $expectedDimensions = [
            'nativephp/android/app/src/main/ic_launcher-playstore.png' => [512, 512],
            'nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png' => [1024, 1024],
            'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png' => [1080, 1920],
            'nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash-dark.png' => [1080, 1920],
        ];

        foreach ($expectedDimensions as $path => $dimensions) {
            expect(array_slice(getimagesize($temporaryRoot.'/'.$path), 0, 2), $path)
                ->toBe($dimensions);
        }

        $appIconCatalog = json_decode(
            File::get($temporaryRoot.'/nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/Contents.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $launchCatalog = json_decode(
            File::get($temporaryRoot.'/nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/Contents.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect(Arr::get($appIconCatalog, 'images.0.filename'))->toBe('icon.png')
            ->and(collect($launchCatalog['images'])->pluck('filename')->all())
            ->toBe(['splash.png', 'splash-dark.png']);

        $firstRun = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);
        $secondProcess = $runNativeBrandInstaller($temporaryRoot);

        expect($secondProcess->isSuccessful(), $secondProcess->getErrorOutput().$secondProcess->getOutput())
            ->toBeTrue()
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($firstRun);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects an arbitrary stale application ID before writing', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $buildPath = $temporaryRoot.'/nativephp/android/app/build.gradle.kts';
    $staleApplicationId = 'com.goleaf.'.'xiaomi'.'mimo';
    File::put($buildPath, str_replace('applicationId = "REPLACE_APP_ID"', 'applicationId = "'.$staleApplicationId.'"', File::get($buildPath)));
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain($staleApplicationId)
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects an arbitrary stale application label before writing', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $manifestPath = $temporaryRoot.'/nativephp/android/app/src/main/AndroidManifest.xml';
    $staleApplicationLabel = 'Xiaomi'.' Mimo';
    File::put($manifestPath, str_replace('android:label="NativePHP"', 'android:label="'.$staleApplicationLabel.'"', File::get($manifestPath)));
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain($staleApplicationLabel)
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects canonical identity decoys in comments before writing', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $buildPath = $temporaryRoot.'/nativephp/android/app/build.gradle.kts';
    $manifestPath = $temporaryRoot.'/nativephp/android/app/src/main/AndroidManifest.xml';
    $staleApplicationId = 'com.goleaf.'.'xiaomi'.'mimo';
    $staleApplicationLabel = 'Xiaomi'.' Mimo';
    File::put(
        $buildPath,
        "/* applicationId = \"com.goleaf.sutelio\" */\n// applicationId = \"com.goleaf.sutelio\"\n".str_replace(
            'applicationId = "REPLACE_APP_ID"',
            'applicationId = "'.$staleApplicationId.'"',
            File::get($buildPath),
        ),
    );
    File::put(
        $manifestPath,
        str_replace(
            '<application',
            "<!-- <application android:label=\"Sutelio\"/> -->\n    <application",
            str_replace('android:label="NativePHP"', 'android:label="'.$staleApplicationLabel.'"', File::get($manifestPath)),
        ),
    );
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain($staleApplicationId)
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects a mixed template and canonical identity before writing', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $buildPath = $temporaryRoot.'/nativephp/android/app/build.gradle.kts';
    File::put($buildPath, str_replace('applicationId = "REPLACE_APP_ID"', 'applicationId = "com.goleaf.sutelio"', File::get($buildPath)));
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('identity fields must be entirely NativePHP template or entirely canonical Sutelio values')
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects canonical identity with template and absent assets before writing', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();

    try {
        $canonicalProcess = $runNativeBrandInstaller($temporaryRoot);

        expect($canonicalProcess->isSuccessful(), $canonicalProcess->getErrorOutput().$canonicalProcess->getOutput())
            ->toBeTrue();

        $storeIcon = $temporaryRoot.'/nativephp/android/app/src/main/ic_launcher-playstore.png';
        $templateStoreIcon = $temporaryRoot.'/vendor/nativephp/mobile/resources/androidstudio/app/src/main/ic_launcher-playstore.png';
        $absentResource = $temporaryRoot.'/nativephp/android/app/src/main/res/values-night-v31/themes.xml';
        $absentResourceDirectory = dirname($absentResource);
        File::copy($templateStoreIcon, $storeIcon);
        File::delete($absentResource);
        File::deleteDirectory($absentResourceDirectory);

        $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

        expect($before['nativephp/android/app/src/main/ic_launcher-playstore.png'])
            ->toBe(hash_file('sha256', $templateStoreIcon))
            ->and($before['nativephp/android/app/src/main/res/values-night-v31/themes.xml'])
            ->toBeNull()
            ->and(File::isDirectory($absentResourceDirectory))
            ->toBeFalse();

        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('identity and assets must be entirely fresh-template or entirely canonical')
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before)
            ->and(File::isDirectory($absentResourceDirectory))
            ->toBeFalse()
            ->and(glob($temporaryRoot.'/nativephp/.sutelio-native-*') ?: [])
            ->toBeEmpty();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer rejects a symlink destination without writing outside the workspace', function () use ($createNativeBrandInstallerFixture, $runNativeBrandInstaller) {
    $externalRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sutelio-native-brand-external-'.Str::uuid();
    File::ensureDirectoryExists($externalRoot);
    $temporaryRoot = $createNativeBrandInstallerFixture($externalRoot);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('NativePHP Android resource destination must not contain a symbolic link')
            ->and(File::allFiles($externalRoot))
            ->toBeEmpty();
    } finally {
        File::deleteDirectory($temporaryRoot);
        File::deleteDirectory($externalRoot);
    }
});

test('the native brand installer rolls back handled multi-file publication failures', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $installerPath = $temporaryRoot.'/scripts/apply-native-brand.mjs';
    $installer = File::get($installerPath);
    $needle = "            publishedEntries.push(entry);\n";
    $replacement = $needle."            if (publishedEntries.length === 4) {\n                throw new Error('Injected native publication failure.');\n            }\n";
    $injectedInstaller = str_replace($needle, $replacement, $installer, $replacementCount);

    expect($replacementCount)->toBe(1);

    File::put($installerPath, $injectedInstaller);
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Injected native publication failure.')
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before);

        $publishArtifacts = collect(File::allFiles($temporaryRoot))
            ->map(static fn (SplFileInfo $file): string => $file->getFilename())
            ->filter(static fn (string $filename): bool => str_contains($filename, '.sutelio-native-stage-') || str_contains($filename, '.sutelio-native-backup-'));

        expect($publishArtifacts)->toBeEmpty();
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

test('the native brand installer removes absent v33 outputs and directories after a late publication failure', function () use ($createNativeBrandInstallerFixture, $nativeBrandPublishedFiles, $runNativeBrandInstaller, $snapshotNativeBrandFixture) {
    $temporaryRoot = $createNativeBrandInstallerFixture();
    $installerPath = $temporaryRoot.'/scripts/apply-native-brand.mjs';
    $installer = File::get($installerPath);
    $publishNeedle = "            publishedEntries.push(entry);\n";
    $publishReplacement = $publishNeedle."            if (publishedEntries.length === 15) {\n                throw new Error('Injected late native publication failure.');\n            }\n";
    $injectedInstaller = str_replace($publishNeedle, $publishReplacement, $installer, $publishReplacementCount);

    expect($publishReplacementCount)->toBe(1);

    File::put($installerPath, $injectedInstaller);

    $v33Resource = $temporaryRoot.'/nativephp/android/app/src/main/res/mipmap-anydpi-v33/ic_launcher.xml';
    $v33Directory = dirname($v33Resource);
    $before = $snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles);

    expect($before['nativephp/android/app/src/main/res/mipmap-anydpi-v33/ic_launcher.xml'])
        ->toBeNull()
        ->and(File::isDirectory($v33Directory))
        ->toBeFalse();

    try {
        $process = $runNativeBrandInstaller($temporaryRoot);

        expect($process->isSuccessful())
            ->toBeFalse()
            ->and($process->getErrorOutput().$process->getOutput())
            ->toContain('Injected late native publication failure.')
            ->and($snapshotNativeBrandFixture($temporaryRoot, $nativeBrandPublishedFiles))
            ->toBe($before)
            ->and(File::exists($v33Resource))
            ->toBeFalse()
            ->and(File::isDirectory($v33Directory))
            ->toBeFalse()
            ->and(glob($temporaryRoot.'/nativephp/.sutelio-native-*') ?: [])
            ->toBeEmpty();
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
