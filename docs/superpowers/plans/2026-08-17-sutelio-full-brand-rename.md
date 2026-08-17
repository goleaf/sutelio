# Sutelio Full Brand Rename Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace every active Xiaomi Mimo and Laravel starter identity with the approved Sutelio name, clean-S visual system, web/native assets, package identifiers, APK, repository name, and local Herd site without changing application behavior or data.

**Architecture:** One deterministic Node-based brand builder owns the master vector geometry and all tracked raster exports; a second Node script copies source-controlled adaptive, monochrome, and Android 12+ splash resources into NativePHP's ignored generated project after installation. Existing Vue components, Laravel configuration, translations, backup filenames, package metadata, current canonical documentation, and NativePHP tests consume that identity. The external GitHub repository and local Herd directory are renamed only after source, APK, emulator, and quality gates have passed and the implementation is safely pushed.

**Tech Stack:** Laravel 13, PHP 8.5/8.4-compatible code, Inertia 3, Vue 3 with `<script setup lang="ts">`, Tailwind CSS 4, Pest 5, Node 22 standard library, HarfBuzz `hb-view`, macOS `sips`, NativePHP Mobile 4.2, Android SDK/build-tools 37, SQLite, GitHub CLI, Laravel Herd.

---

## Locked Contracts

- Product/project name: `Sutelio`.
- Android application ID and iOS bundle identifier: `com.goleaf.sutelio`.
- Deep-link scheme: `sutelio`; the verified HTTPS host remains deployment-specific.
- Palette: cobalt `#123C8B`, deep cobalt `#0A285F`, signal orange `#FF6038`, warm ivory `#FFF8E9`.
- Mark: cobalt rounded tile, centered orange circle with diameter exactly 70 percent of the tile, solid ivory Instrument Sans semibold `S` outline, no stripe/cut/line/gradient/shadow.
- Wordmark: one-color `Sutelio`; the final `o` is never orange.
- Mobile package change is a clean-install boundary. Preserve `com.goleaf.xiaomimimo` while verifying `com.goleaf.sutelio`; do not claim cross-sandbox data migration.
- No schema, Eloquent query, authorization, email-verification, onboarding, or dependency-version change belongs to this phase.

## File Map

### New authoritative files

- `scripts/build-brand-assets.mjs` — validates the pinned Instrument Sans input, creates the vector sources, Android XML overrides, and tracked PNG/ICO exports.
- `scripts/apply-native-brand.mjs` — copies only the source-controlled Android overrides into the ignored generated NativePHP project.
- `resources/brand/fonts/InstrumentSans.ttf` — pinned Google Fonts variable font input.
- `resources/brand/fonts/OFL.txt` — SIL Open Font License for the font input.
- `resources/brand/sutelio-mark.svg` — authoritative clean-S application mark.
- `resources/brand/sutelio-wordmark.svg` — outlined one-color wordmark with no SVG text node.
- `resources/brand/sutelio-lockup.svg` — horizontal mark plus outlined wordmark.
- `resources/brand/sutelio-splash.svg` — 1080-by-1920 warm-ivory splash source.
- `resources/brand/sutelio-android-store-512.png` — tracked Android store artwork.
- `resources/brand/android/**` — generated adaptive, monochrome, and Android 12+ splash/theme overrides.
- `public/icon.png` — opaque 1024-by-1024 NativePHP/iOS icon source.
- `public/splash.png`, `public/splash-dark.png` — opaque 1080-by-1920 NativePHP splash sources.
- `tests/Feature/BrandIdentityTest.php` — canonical identity, geometry, asset, metadata, and legacy-string contract.

### Existing application files

- `.env.example`, local ignored `.env`, `config/app.php`, `config/nativephp.php` — display, package, deep-link, store, service, and Android brand defaults.
- `composer.json`, `package.json`, `package-lock.json` — root project metadata and brand scripts.
- `resources/js/app.ts` — Sutelio title fallback and branded Inertia progress color.
- `resources/css/app.css` — additive semantic brand tokens without changing unrelated Warm Precision state colors.
- `resources/js/components/AppLogo.vue`, `AppLogoIcon.vue`, `AppSidebar.vue`, `AppHeader.vue` — shared mark, wordmark, tooltip, and repository links.
- `resources/js/layouts/auth/AuthSimpleLayout.vue` — full-tile auth mark with an accessible link name.
- `resources/views/app.blade.php` — favicon/touch icon links and application metadata.
- `resources/js/pages/notifications/Index.vue` — Sutelio browser-reminder storage namespace.
- `app/Http/Controllers/BackupController.php` — Sutelio public backup filename.
- `lang/{en,lt,ru}/{onboarding,ui,workspace}.php` — grammatically complete proper-name replacements with unchanged keys/placeholders.
- `tests/Feature/NativePhpMobileTest.php`, `DatabaseBackupTest.php`, `ApplicationLayerContractTest.php`, `FrontendDesignTest.php` — focused regressions matching the new identity.

### Current canonical evidence

- `AGENTS.md`, `README.md`, `CHANGELOG.md`.
- `docs/index.md`, `product-requirements.md`, `requirements.md`, `architecture.md`, `frontend.md`, `design-system.md`, `accessibility.md`, `localization.md`, `testing.md`, `deployment.md`, `current-state.md`, `current-state-audit.md`, `known-limitations.md`, `implementation-plan.md`, `compliance-matrix.md`, `code-review.md`, and append-only `progress.md`.
- Preserve `docs/progress.md` history, `docs/audit/**`, `.mimocode/**`, `graphify-out/**`, prior plans, and old artifact facts as historical evidence. Add successor/current-state notes instead of rewriting old commands and hashes.

## Task 1: Start the implementation phase and establish a failing brand contract

**Files:**

- Modify: `docs/progress.md`
- Create: `tests/Feature/BrandIdentityTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Re-run the clean-main preflight**

Run:

```bash
git status --short --branch
git fetch origin main
git rev-list --left-right --count main...origin/main
git diff --check
```

Expected: clean `main`, divergence `0 0`, and no diff-check output. If unrelated files exist, preserve them and restrict every later `git add` to phase-owned paths.

- [ ] **Step 2: Append the implementation preflight to progress**

Append a new section without editing prior entries:

```markdown
## Sutelio Full Brand Rename Implementation — 2026-08-17

### Implementation Preflight

- Began from clean synchronized `main` after approval of `docs/plans/2026-08-17-sutelio-full-brand-rename-design.md` and `docs/superpowers/plans/2026-08-17-sutelio-full-brand-rename.md`.
- Scope is identity-only: deterministic brand assets, active product/package names, NativePHP generation, APK/emulator proof, canonical evidence, and final in-place GitHub/Herd rename.
- No schema, domain behavior, authorization, authentication, email-verification, or dependency-version change is authorized in this phase.
```

- [ ] **Step 3: Generate the Pest test shell**

Run:

```bash
php artisan make:test --pest BrandIdentityTest --no-interaction
```

Expected: `tests/Feature/BrandIdentityTest.php` is created.

- [ ] **Step 4: Replace the generated test with the identity contract**

Use this complete structure:

```php
<?php

use Illuminate\Support\Facades\File;

test('the clean-S master artwork uses the locked geometry and colors', function () {
    $mark = File::get(resource_path('brand/sutelio-mark.svg'));
    $wordmark = File::get(resource_path('brand/sutelio-wordmark.svg'));
    $lockup = File::get(resource_path('brand/sutelio-lockup.svg'));

    expect($mark)
        ->toContain(
            'viewBox="0 0 512 512"',
            'rx="112"',
            'r="179.2"',
            '#123C8B',
            '#FF6038',
            '#FFF8E9',
        )
        ->not->toContain('<text', '<line', '<linearGradient', '<radialGradient', 'stroke=')
        ->and($wordmark)->toContain('#0A285F', 'Sutelio')
        ->not->toContain('<text', '#FF6038')
        ->and($lockup)->toContain('#0A285F')
        ->not->toContain('<text');
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
        expect(getimagesize(base_path($path)))->not->toBeFalse();
        [$width, $height] = getimagesize(base_path($path));
        expect([$width, $height], $path)->toBe($expectedDimensions);
    }

    $icon = imagecreatefrompng(public_path('icon.png'));
    $splash = imagecreatefrompng(public_path('splash.png'));

    expect($icon)->not->toBeFalse()
        ->and($splash)->not->toBeFalse()
        ->and((imagecolorat($icon, 0, 0) >> 24) & 0x7F)->toBe(0)
        ->and((imagecolorat($splash, 0, 0) >> 24) & 0x7F)->toBe(0);

    imagedestroy($icon);
    imagedestroy($splash);
});

```

When implementing, add the literal `data-wordmark="Sutelio"` attribute to `sutelio-wordmark.svg`; this gives the second test a semantic name without using an SVG `<text>` node.

- [ ] **Step 5: Change existing focused assertions before implementation**

Make this exact expectation change:

```php
// tests/Feature/FrontendDesignTest.php
expect(File::get(resource_path('js/components/AppLogoIcon.vue')))
    ->toContain('src="/favicon.svg"', 'alt=""', 'aria-hidden="true"')
    ->not->toContain('fill="currentColor"', '<text');
```

- [ ] **Step 6: Run the focused tests and prove the red state**

Run:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendDesignTest.php
```

Expected: failures for missing brand assets and the old Laravel logo component. Save the exact failure count in the new progress entry; do not commit a red state.

## Task 2: Build the deterministic master identity and tracked exports

**Files:**

- Create: `resources/brand/fonts/InstrumentSans.ttf`
- Create: `resources/brand/fonts/OFL.txt`
- Create: `scripts/build-brand-assets.mjs`
- Create/generated: `resources/brand/sutelio-{mark,wordmark,lockup,splash}.svg`
- Create/generated: `resources/brand/sutelio-android-store-512.png`
- Create/generated: `resources/brand/android/**`
- Create/generated: `public/icon.png`, `public/splash.png`, `public/splash-dark.png`
- Replace/generated: `public/favicon.svg`, `public/favicon.ico`, `public/apple-touch-icon.png`
- Modify: `package.json`, `package-lock.json`

- [ ] **Step 1: Import the pinned OFL font and license**

Use the exact Google Fonts commit approved during planning:

```bash
mkdir -p resources/brand/fonts
curl -fsSL -o resources/brand/fonts/InstrumentSans.ttf 'https://raw.githubusercontent.com/google/fonts/2fc9491da70cf3e1bafd3c3da49244c5a496e9d5/ofl/instrumentsans/InstrumentSans%5Bwdth%2Cwght%5D.ttf'
curl -fsSL -o resources/brand/fonts/OFL.txt 'https://raw.githubusercontent.com/google/fonts/2fc9491da70cf3e1bafd3c3da49244c5a496e9d5/ofl/instrumentsans/OFL.txt'
shasum -a 256 resources/brand/fonts/InstrumentSans.ttf resources/brand/fonts/OFL.txt
```

Expected hashes:

```text
b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1  resources/brand/fonts/InstrumentSans.ttf
9e27a72ed30eb49a08678f6a5d6ed98ec7ba5368f541637ee0683ec9134ef966  resources/brand/fonts/OFL.txt
```

- [ ] **Step 2: Add the dependency-free brand builder**

Create `scripts/build-brand-assets.mjs` with Node standard-library imports only. Its complete responsibilities and constants are:

```js
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import {
    copyFileSync,
    mkdirSync,
    readFileSync,
    unlinkSync,
    writeFileSync,
} from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const brand = resolve(root, 'resources/brand');
const publicDirectory = resolve(root, 'public');
const font = resolve(brand, 'fonts/InstrumentSans.ttf');
const expectedFontHash =
    'b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1';
const colors = {
    cobalt: '#123C8B',
    deepCobalt: '#0A285F',
    orange: '#FF6038',
    ivory: '#FFF8E9',
};

function ensureDirectory(path) {
    mkdirSync(path, { recursive: true });
}

function write(path, content) {
    ensureDirectory(dirname(path));
    writeFileSync(path, `${content.trim()}\n`);
}

function between(source, start, end) {
    const startIndex = source.indexOf(start);
    const endIndex = source.indexOf(end, startIndex + start.length);

    if (startIndex < 0 || endIndex < 0) {
        throw new Error(
            `Unable to extract ${start}…${end} from hb-view output.`,
        );
    }

    return source.slice(startIndex + start.length, endIndex).trim();
}

function renderPng(source, output, width, height) {
    execFileSync(
        'sips',
        [
            '-s',
            'format',
            'png',
            '-z',
            String(height),
            String(width),
            source,
            '--out',
            output,
        ],
        { cwd: root, stdio: 'ignore' },
    );
}

const actualFontHash = createHash('sha256')
    .update(readFileSync(font))
    .digest('hex');

if (actualFontHash !== expectedFontHash) {
    throw new Error(`Instrument Sans checksum mismatch: ${actualFontHash}`);
}

const outlinedWordmark = execFileSync(
    'hb-view',
    [
        font,
        'Sutelio',
        '--font-size=256',
        '--variations=wght=600,wdth=100',
        '--foreground=0A285F',
        '--background=none',
        '--margin=0',
        '--output-format=svg',
    ],
    { cwd: root, encoding: 'utf8' },
);
const definitions = between(outlinedWordmark, '<defs>', '</defs>');
const usesMatch = outlinedWordmark.match(
    /<g fill="[^"]+" fill-opacity="1">\s*([\s\S]*?)\s*<\/g>\s*<\/svg>/,
);
const sPathMatch = definitions.match(
    /<g id="glyph-0-0">\s*<path d="([^"]+)"\/>\s*<\/g>/,
);

if (!usesMatch || !sPathMatch) {
    throw new Error('Unable to extract outlined Sutelio glyph geometry.');
}

const wordmarkUses = usesMatch[1];
const sPath = sPathMatch[1];
const markArtwork = `
  <rect width="512" height="512" rx="112" fill="${colors.cobalt}"/>
  <circle cx="256" cy="256" r="179.2" fill="${colors.orange}"/>
  <path d="${sPath}" transform="translate(149.73 376.14) scale(1.3)" fill="${colors.ivory}"/>`;
const markSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512" role="img" aria-labelledby="sutelio-mark-title">
  <title id="sutelio-mark-title">Sutelio</title>${markArtwork}
</svg>`;
const wordmarkSvg = `
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="847" height="313" viewBox="0 0 847 312.3125" data-wordmark="Sutelio">
  <defs>${definitions}</defs>
  <g fill="${colors.deepCobalt}">${wordmarkUses}</g>
</svg>`;
const lockupSvg = `
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1280" height="512" viewBox="0 0 1280 512" role="img" aria-labelledby="sutelio-lockup-title">
  <title id="sutelio-lockup-title">Sutelio</title>
  <defs>${definitions}</defs>
  <g>${markArtwork}</g>
  <g transform="translate(576 100) scale(.78)" fill="${colors.deepCobalt}">${wordmarkUses}</g>
</svg>`;
const splashSvg = `
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1080" height="1920" viewBox="0 0 1080 1920">
  <defs>${definitions}</defs>
  <rect width="1080" height="1920" fill="${colors.ivory}"/>
  <g transform="translate(348 520) scale(.75)">${markArtwork}</g>
  <g transform="translate(209.67 1030) scale(.78)" fill="${colors.deepCobalt}">${wordmarkUses}</g>
</svg>`;

write(resolve(brand, 'sutelio-mark.svg'), markSvg);
write(resolve(brand, 'sutelio-wordmark.svg'), wordmarkSvg);
write(resolve(brand, 'sutelio-lockup.svg'), lockupSvg);
write(resolve(brand, 'sutelio-splash.svg'), splashSvg);
write(resolve(publicDirectory, 'favicon.svg'), markSvg);

renderPng(
    resolve(brand, 'sutelio-mark.svg'),
    resolve(publicDirectory, 'icon.png'),
    1024,
    1024,
);
renderPng(
    resolve(brand, 'sutelio-mark.svg'),
    resolve(publicDirectory, 'apple-touch-icon.png'),
    180,
    180,
);
renderPng(
    resolve(brand, 'sutelio-mark.svg'),
    resolve(brand, 'sutelio-android-store-512.png'),
    512,
    512,
);
renderPng(
    resolve(brand, 'sutelio-splash.svg'),
    resolve(publicDirectory, 'splash.png'),
    1080,
    1920,
);
copyFileSync(
    resolve(publicDirectory, 'splash.png'),
    resolve(publicDirectory, 'splash-dark.png'),
);

const faviconPng = resolve(publicDirectory, '.sutelio-favicon-32.png');
renderPng(resolve(brand, 'sutelio-mark.svg'), faviconPng, 32, 32);
execFileSync(
    'sips',
    [
        '-s',
        'format',
        'ico',
        faviconPng,
        '--out',
        resolve(publicDirectory, 'favicon.ico'),
    ],
    { cwd: root, stdio: 'ignore' },
);
unlinkSync(faviconPng);

const circlePath =
    'M256,76.8 A179.2,179.2 0 1,1 255.999,435.2 A179.2,179.2 0 1,1 256,76.8 Z';
const androidDrawable = resolve(brand, 'android/drawable');
const androidV26 = resolve(brand, 'android/mipmap-anydpi-v26');
const androidV33 = resolve(brand, 'android/mipmap-anydpi-v33');
const androidV31 = resolve(brand, 'android/values-v31');
const androidNightV31 = resolve(brand, 'android/values-night-v31');

write(
    resolve(androidDrawable, 'ic_launcher_background.xml'),
    `
<shape xmlns:android="http://schemas.android.com/apk/res/android" android:shape="rectangle">
  <solid android:color="${colors.cobalt}"/>
</shape>`,
);
write(
    resolve(androidDrawable, 'ic_launcher_foreground.xml'),
    `
<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512">
  <group android:pivotX="256" android:pivotY="256" android:scaleX="0.86" android:scaleY="0.86">
    <path android:fillColor="${colors.orange}" android:pathData="${circlePath}"/>
    <group android:translateX="149.73" android:translateY="376.14" android:scaleX="1.3" android:scaleY="1.3">
      <path android:fillColor="${colors.ivory}" android:pathData="${sPath}"/>
    </group>
  </group>
</vector>`,
);
write(
    resolve(androidDrawable, 'ic_launcher_monochrome.xml'),
    `
<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512">
  <group android:pivotX="256" android:pivotY="256" android:scaleX="0.86" android:scaleY="0.86">
    <group android:translateX="149.73" android:translateY="376.14" android:scaleX="1.3" android:scaleY="1.3">
      <path android:fillColor="#FF000000" android:pathData="${sPath}"/>
    </group>
  </group>
</vector>`,
);

const adaptiveIcon = (withMonochrome) => `
<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">
  <background android:drawable="@drawable/ic_launcher_background"/>
  <foreground android:drawable="@drawable/ic_launcher_foreground"/>
  ${withMonochrome ? '<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>' : ''}
</adaptive-icon>`;

for (const filename of ['ic_launcher.xml', 'ic_launcher_round.xml']) {
    write(resolve(androidV26, filename), adaptiveIcon(false));
    write(resolve(androidV33, filename), adaptiveIcon(true));
}

const androidTheme = (primary) => `
<resources>
  <style name="Theme.AndroidPHP" parent="Theme.MaterialComponents.DayNight.DarkActionBar">
    <item name="colorPrimary">${primary}</item>
    <item name="colorPrimaryVariant">${primary}</item>
    <item name="colorOnPrimary">${colors.ivory}</item>
    <item name="colorAccent">${colors.orange}</item>
    <item name="android:colorAccent">${colors.orange}</item>
    <item name="android:windowDrawsSystemBarBackgrounds">true</item>
    <item name="android:statusBarColor">@android:color/transparent</item>
    <item name="android:navigationBarColor">@android:color/transparent</item>
    <item name="android:enforceStatusBarContrast">false</item>
    <item name="android:enforceNavigationBarContrast">false</item>
    <item name="android:windowSplashScreenBackground">${colors.ivory}</item>
    <item name="android:windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item>
    <item name="android:windowSplashScreenIconBackgroundColor">${colors.cobalt}</item>
  </style>
</resources>`;

write(resolve(androidV31, 'themes.xml'), androidTheme(colors.cobalt));
write(resolve(androidNightV31, 'themes.xml'), androidTheme(colors.deepCobalt));

console.log('Built deterministic Sutelio web and NativePHP brand assets.');
```

- [ ] **Step 3: Register the exact brand commands and package name**

Add to `package.json`:

```json
{
    "name": "sutelio",
    "scripts": {
        "brand:build": "node scripts/build-brand-assets.mjs",
        "brand:native": "node scripts/apply-native-brand.mjs"
    }
}
```

Keep every existing script/dependency. Regenerate only the existing lock:

```bash
npm install --package-lock-only --ignore-scripts
npm run brand:build
```

Expected: the vector/raster/Android source files listed in the file map exist; `package-lock.json` root names are `sutelio`; no dependency version changes.

- [ ] **Step 4: Verify generated asset fundamentals**

Run:

```bash
file public/icon.png public/favicon.ico public/apple-touch-icon.png public/splash.png public/splash-dark.png resources/brand/sutelio-android-store-512.png
sips -g pixelWidth -g pixelHeight -g hasAlpha public/icon.png public/apple-touch-icon.png public/splash.png resources/brand/sutelio-android-store-512.png
rg -n '<text|<line|Gradient|stroke=' resources/brand/*.svg public/favicon.svg
php artisan test --compact tests/Feature/BrandIdentityTest.php --filter='clean-S|raster assets'
```

Expected: exact dimensions from the test, opaque sampled pixels, no forbidden SVG constructs, and both filtered tests pass.

## Task 3: Add reproducible NativePHP adaptive and monochrome installation

**Files:**

- Create: `scripts/apply-native-brand.mjs`
- Modify: `tests/Feature/NativePhpMobileTest.php`

- [ ] **Step 1: Add the generated-project copy command**

Create `scripts/apply-native-brand.mjs`:

```js
import { cpSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const source = resolve(root, 'resources/brand/android');
const destination = resolve(root, 'nativephp/android/app/src/main/res');

if (!existsSync(source)) {
    throw new Error(
        'Run npm run brand:build before applying native brand assets.',
    );
}

if (!existsSync(destination)) {
    throw new Error(
        'Run php artisan native:install --force before applying native brand assets.',
    );
}

cpSync(source, destination, { recursive: true, force: true });
console.log(
    'Applied Sutelio adaptive, monochrome, and Android 12+ splash resources.',
);
```

- [ ] **Step 2: Extend the NativePHP source contract**

Append this focused test to `tests/Feature/NativePhpMobileTest.php`:

```php
test('Sutelio NativePHP brand sources include deterministic adaptive monochrome and splash resources', function () {
    $brandBuilder = File::get(base_path('scripts/build-brand-assets.mjs'));
    $nativeBrandInstaller = File::get(base_path('scripts/apply-native-brand.mjs'));
    $monochrome = File::get(resource_path('brand/android/drawable/ic_launcher_monochrome.xml'));
    $adaptive = File::get(resource_path('brand/android/mipmap-anydpi-v33/ic_launcher.xml'));
    $splashTheme = File::get(resource_path('brand/android/values-v31/themes.xml'));

    expect($brandBuilder)->toContain('b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1')
        ->and($nativeBrandInstaller)->toContain("cpSync(source, destination, { recursive: true, force: true })")
        ->and($monochrome)->toContain('#FF000000', 'android:pathData')
        ->and($adaptive)->toContain('<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>')
        ->and($splashTheme)->toContain(
            '<item name="android:windowSplashScreenBackground">#FFF8E9</item>',
            '<item name="android:windowSplashScreenIconBackgroundColor">#123C8B</item>',
        );
});
```

Run:

```bash
php artisan test --compact tests/Feature/NativePhpMobileTest.php --filter='brand sources include'
```

Expected: the new source-contract test passes.

- [ ] **Step 3: Prove deterministic source generation**

Stage the first generated pass, then run the builder again and compare the working tree to the staged source:

```bash
npm run brand:build
git add resources/brand public/favicon.svg public/favicon.ico public/apple-touch-icon.png public/icon.png public/splash.png public/splash-dark.png
npm run brand:build
git diff --exit-code -- resources/brand public/favicon.svg public/favicon.ico public/apple-touch-icon.png public/icon.png public/splash.png public/splash-dark.png
```

Expected: no unstaged diff; the second pass reproduces the staged bytes exactly.

## Task 4: Replace the web mark, wordmark, metadata, and repository links

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/js/app.ts`
- Modify: `resources/js/components/AppLogoIcon.vue`
- Modify: `resources/js/components/AppLogo.vue`
- Modify: `resources/js/components/AppSidebar.vue`
- Modify: `resources/js/components/AppHeader.vue`
- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/views/app.blade.php`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Add additive semantic brand tokens**

Add to `@theme inline`:

```css
--color-brand-cobalt: var(--brand-cobalt);
--color-brand-deep-cobalt: var(--brand-deep-cobalt);
--color-brand-orange: var(--brand-orange);
--color-brand-ivory: var(--brand-ivory);
```

Add to `:root` without changing the existing primary/status/chart tokens:

```css
--brand-cobalt: #123c8b;
--brand-deep-cobalt: #0a285f;
--brand-orange: #ff6038;
--brand-ivory: #fff8e9;
```

- [ ] **Step 2: Replace the Laravel glyph with the approved single-source full-color mark**

Keep the existing typed `className` prop and attribute forwarding in `AppLogoIcon.vue`, but consume the generated master through the canonical public SVG instead of duplicating its long path:

```vue
<img
    src="/favicon.svg"
    alt=""
    :class="className"
    aria-hidden="true"
    draggable="false"
    v-bind="$attrs"
/>
```

`public/favicon.svg` and `resources/brand/sutelio-mark.svg` are byte-identical outputs of the brand builder, so the Vue component, favicon, and generated raster assets share one geometry source. The empty `alt` and `aria-hidden` are intentional because every interactive consumer supplies the accessible Sutelio name.

- [ ] **Step 3: Make the shared wordmark one color**

Replace `AppLogo.vue` presentation with:

```vue
<template>
    <AppLogoIcon class-name="size-8 shrink-0" />
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold tracking-tight">Sutelio</span>
    </div>
</template>
```

Do not wrap the already complete tile in another orange background.

- [ ] **Step 4: Update all consumers and accessible names**

Make these exact changes:

```vue
<!-- AppSidebar.vue -->
<SidebarMenuButton size="lg" as-child tooltip="Sutelio">

<!-- AuthSimpleLayout.vue -->
<Link
    :href="home()"
    aria-label="Sutelio"
    class="flex size-12 shrink-0 items-center justify-center rounded-2xl focus-visible:ring-2 focus-visible:ring-brand-cobalt focus-visible:ring-offset-4 focus-visible:outline-none"
>
    <AppLogoIcon class-name="size-12" />
</Link>
```

In `AppHeader.vue`, keep icon-only copies decorative, use the new full-tile size, and replace starter links with:

```ts
href: 'https://github.com/goleaf/sutelio';
```

for the repository and:

```ts
href: 'https://github.com/goleaf/sutelio#readme';
```

for project documentation.

- [ ] **Step 5: Update document metadata and Inertia title fallback**

Use these exact values:

```ts
// resources/js/app.ts
const appName = import.meta.env.VITE_APP_NAME || 'Sutelio';

progress: {
    color: '#FF6038',
},
```

Add to the Blade `<head>` after the viewport meta:

```blade
<meta name="application-name" content="{{ $page['props']['name'] }}">
<meta name="apple-mobile-web-app-title" content="{{ $page['props']['name'] }}">
<meta name="theme-color" content="#123C8B">
```

Keep the existing favicon/touch links and presentation-only Blade boundary.

- [ ] **Step 6: Run focused web identity checks**

Run:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendDesignTest.php
npm run types:check
npm run lint:check
npm run format:check
npm run build
```

Expected: all pass; Vite produces the normal application build with no missing asset or Vue root error.

- [ ] **Step 7: Commit the visual slice**

```bash
git add scripts/build-brand-assets.mjs scripts/apply-native-brand.mjs resources/brand public/favicon.svg public/favicon.ico public/apple-touch-icon.png public/icon.png public/splash.png public/splash-dark.png package.json package-lock.json resources/css/app.css resources/js/app.ts resources/js/components/AppLogoIcon.vue resources/js/components/AppLogo.vue resources/js/components/AppSidebar.vue resources/js/components/AppHeader.vue resources/js/layouts/auth/AuthSimpleLayout.vue resources/views/app.blade.php tests/Feature/BrandIdentityTest.php tests/Feature/FrontendDesignTest.php tests/Feature/NativePhpMobileTest.php docs/progress.md
git diff --cached --check
git commit -m "feat: add Sutelio brand identity"
```

Expected: only this visual/asset slice is committed locally. Do not push the intentionally intermediate mixed-name state; Task 6 pushes all coherent rename commits after the complete active identity contract passes.

## Task 5: Rename user-facing copy, storage keys, and backup filenames

**Files:**

- Modify: `lang/en/onboarding.php`, `lang/en/ui.php`, `lang/en/workspace.php`
- Modify: `lang/lt/onboarding.php`, `lang/lt/ui.php`, `lang/lt/workspace.php`
- Modify: `lang/ru/onboarding.php`, `lang/ru/ui.php`, `lang/ru/workspace.php`
- Modify: `resources/js/pages/notifications/Index.vue`
- Modify: `app/Http/Controllers/BackupController.php`
- Modify: `tests/Feature/DatabaseBackupTest.php`
- Modify: `tests/Feature/ApplicationLayerContractTest.php`

- [ ] **Step 1: Add failing copy, storage-namespace, and download-name coverage**

Append to `tests/Feature/BrandIdentityTest.php`:

```php
test('active localized copy and browser-facing filenames use Sutelio', function () {
    $paths = [
        'lang/en/onboarding.php',
        'lang/en/ui.php',
        'lang/en/workspace.php',
        'lang/lt/onboarding.php',
        'lang/lt/ui.php',
        'lang/lt/workspace.php',
        'lang/ru/onboarding.php',
        'lang/ru/ui.php',
        'lang/ru/workspace.php',
        'resources/js/pages/notifications/Index.vue',
        'app/Http/Controllers/BackupController.php',
    ];
    $source = collect($paths)
        ->map(fn (string $path): string => File::get(base_path($path)))
        ->implode("\n");

    expect($source)
        ->toContain('Sutelio', 'sutelio:browser-reminder:', 'sutelio-backup-')
        ->not->toContain('Xiaomi'.' Mimo', 'xiaomi'.'-mimo');
});
```

In the existing safe-download test, define:

```php
$expectedDownloadName = 'sutelio-backup-'.now()->format('Ymd-His').'.sqlite';
```

and replace `->assertDownload()` with:

```php
->assertDownload($expectedDownloadName);
```

Run:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/DatabaseBackupTest.php
```

Expected: failures show the old localized name, browser key, backup prefix, and download name.

- [ ] **Step 2: Replace the proper name without changing translation keys**

Use this exact language contract:

```text
English: Xiaomi Mimo -> Sutelio
Lithuanian: „Xiaomi Mimo“ -> „Sutelio“
Russian: Xiaomi Mimo -> Sutelio
```

Do not concatenate sentence fragments or alter placeholders/plural keys.

- [ ] **Step 3: Rename the browser-reminder namespace**

Change only the prefix:

```ts
const storageKey = `sutelio:browser-reminder:${notification.id}`;
```

Do not add legacy-key migration code; the full technical rename intentionally removes the active old identifier.

- [ ] **Step 4: Rename public and test-only backup prefixes**

Use:

```php
// BackupController.php
'sutelio-backup-'.now()->format('Ymd-His').'.sqlite'

// DatabaseBackupTest.php
sys_get_temp_dir().'/sutelio-test-backup-'.Str::uuid()

// ApplicationLayerContractTest.php
sys_get_temp_dir().'/sutelio-test-backup-contract-'.Str::uuid()
```

Keep the controller thin and do not change authorization, snapshot format, containment, or cache headers.

- [ ] **Step 5: Run localization and backup regressions**

```bash
php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/OnboardingFrontendTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/ApplicationLayerContractTest.php tests/Feature/BrandIdentityTest.php
npm run test:frontend
```

Expected: locale key/placeholder parity remains intact, the exact Sutelio download name passes, and all frontend tests pass.

- [ ] **Step 6: Commit the copy and public-filename slice**

```bash
git add lang/en/onboarding.php lang/en/ui.php lang/en/workspace.php lang/lt/onboarding.php lang/lt/ui.php lang/lt/workspace.php lang/ru/onboarding.php lang/ru/ui.php lang/ru/workspace.php resources/js/pages/notifications/Index.vue app/Http/Controllers/BackupController.php tests/Feature/BrandIdentityTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/ApplicationLayerContractTest.php docs/progress.md
git diff --cached --check
git commit -m "refactor: rename Sutelio user-facing copy"
```

Expected: only copy, storage namespace, backup filename, focused tests, and phase evidence are committed locally. Task 6 performs the first push after technical identity is consistent.

## Task 6: Rename Laravel, package, NativePHP, and local environment identity

**Files:**

- Modify: `.env.example`
- Modify locally, do not stage: `.env`
- Modify: `config/app.php`
- Modify: `config/nativephp.php`
- Modify: `composer.json`
- Modify: `tests/Feature/NativePhpMobileTest.php`
- Modify: `tests/Feature/BrandIdentityTest.php`
- Modify: `scripts/apply-native-brand.mjs`

- [ ] **Step 1: Add failing application/package/native metadata coverage**

Append to `tests/Feature/BrandIdentityTest.php`:

```php
test('the approved Sutelio identity and package metadata are canonical', function () {
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $packageLock = json_decode(File::get(base_path('package-lock.json')), true, flags: JSON_THROW_ON_ERROR);
    $environment = File::get(base_path('.env.example'));

    expect(config('app.name'))->toBe('Sutelio')
        ->and(config('nativephp.app_id'))->toBe('com.goleaf.sutelio')
        ->and(config('nativephp.deeplink_scheme'))->toBe('sutelio')
        ->and(config('nativephp.server.service_name'))->toBe('Sutelio')
        ->and(config('nativephp.app_store_connect.app_name'))->toBe('Sutelio')
        ->and(config('nativephp.android.theme.color_primary'))->toBe('#123C8B')
        ->and(config('nativephp.android.theme.color_primary_night'))->toBe('#0A285F')
        ->and(config('nativephp.android.theme.color_on_primary'))->toBe('#FFF8E9')
        ->and($composer['name'])->toBe('goleaf/sutelio')
        ->and($composer['description'])->toContain('Sutelio')
        ->and($package['name'])->toBe('sutelio')
        ->and($packageLock['name'])->toBe('sutelio')
        ->and($packageLock['packages']['']['name'])->toBe('sutelio')
        ->and($environment)->toContain(
            'APP_NAME="Sutelio"',
            'NATIVEPHP_APP_ID=com.goleaf.sutelio',
            'NATIVEPHP_DEEPLINK_SCHEME=sutelio',
            'NATIVEPHP_SERVICE_NAME="Sutelio"',
            'APP_STORE_APP_NAME="Sutelio"',
        );
});
```

Replace every expected app ID, service name, key alias, and environment fixture in `tests/Feature/NativePhpMobileTest.php` with:

```text
com.goleaf.sutelio
Sutelio
sutelio
```

Run:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/NativePhpMobileTest.php
```

Expected: red failures identify the old Laravel/NativePHP/Composer defaults.

- [ ] **Step 2: Set exact Laravel and Composer metadata**

Use:

```php
// config/app.php
'name' => env('APP_NAME', 'Sutelio'),
```

Use this Composer root metadata, preserving all requirements/scripts/configuration:

```json
{
    "name": "goleaf/sutelio",
    "type": "project",
    "description": "Sutelio is a local-first, workspace-scoped task and collaboration application for web and NativePHP Mobile.",
    "keywords": [
        "sutelio",
        "laravel",
        "nativephp",
        "sqlite",
        "task-management",
        "collaboration"
    ]
}
```

- [ ] **Step 3: Set exact committed environment defaults**

Update `.env.example`:

```dotenv
APP_NAME="Sutelio"
NATIVEPHP_APP_ID=com.goleaf.sutelio
NATIVEPHP_DEEPLINK_SCHEME=sutelio
NATIVEPHP_SERVICE_NAME="Sutelio"
NATIVEPHP_ANDROID_COLOR_PRIMARY="#123C8B"
NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT="#0A285F"
NATIVEPHP_ANDROID_COLOR_ON_PRIMARY="#FFF8E9"
APP_STORE_APP_NAME="Sutelio"
```

Leave `NATIVEPHP_DEEPLINK_HOST=` empty because no verified production domain was supplied.

- [ ] **Step 4: Set exact NativePHP fallbacks**

Update `config/nativephp.php`:

```php
'app_id' => env('NATIVEPHP_APP_ID', 'com.goleaf.sutelio'),
'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME') ?: 'sutelio',

'theme' => [
    'color_primary' => env('NATIVEPHP_ANDROID_COLOR_PRIMARY', '#123C8B'),
    'color_primary_night' => env('NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT', '#0A285F'),
    'color_on_primary' => env('NATIVEPHP_ANDROID_COLOR_ON_PRIMARY', '#FFF8E9'),
],

'service_name' => env('NATIVEPHP_SERVICE_NAME', 'Sutelio'),

'app_name' => env('APP_STORE_APP_NAME') ?: 'Sutelio',
```

Do not invent a second iOS identifier setting; NativePHP derives both platform identities from `nativephp.app_id`.

- [ ] **Step 5: Update the ignored local environment without exposing secrets**

Edit only these existing `.env` keys:

```dotenv
APP_NAME="Sutelio"
NATIVEPHP_APP_ID=com.goleaf.sutelio
NATIVEPHP_DEEPLINK_SCHEME=sutelio
NATIVEPHP_SERVICE_NAME="Sutelio"
NATIVEPHP_ANDROID_COLOR_PRIMARY="#123C8B"
NATIVEPHP_ANDROID_COLOR_PRIMARY_NIGHT="#0A285F"
NATIVEPHP_ANDROID_COLOR_ON_PRIMARY="#FFF8E9"
APP_STORE_APP_NAME="Sutelio"
```

Keep `APP_URL` unchanged until the Herd directory rename. Never print the full `.env`; verify only the named non-secret keys with `rg`.

- [ ] **Step 6: Finish all NativePHP fixture replacements and run focused tests**

Every old fixture ID, service name, key alias, and environment line in `tests/Feature/NativePhpMobileTest.php` becomes Sutelio. Then run:

```bash
php artisan config:clear
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/NativePhpMobileTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/ApplicationLayerContractTest.php
composer validate --strict
npm install --package-lock-only --ignore-scripts
```

Expected: all focused tests and Composer validation pass; lock diffs contain project-name changes only.

- [ ] **Step 7: Commit the technical-name slice**

```bash
git add .env.example config/app.php config/nativephp.php composer.json package.json package-lock.json scripts/apply-native-brand.mjs app/Http/Controllers/BackupController.php resources/js/pages/notifications/Index.vue lang tests/Feature/NativePhpMobileTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/ApplicationLayerContractTest.php tests/Feature/BrandIdentityTest.php docs/progress.md
git diff --cached --check
git commit -m "refactor: rename application to Sutelio"
git push --verbose origin main
```

Expected: ignored `.env` remains unstaged; all three coherent identity commits are pushed together only after the complete focused identity suite passes.

## Task 7: Regenerate and verify NativePHP platform identity

**Files:**

- Generated/ignored: `nativephp/android/**`, `nativephp/ios/**`
- Tracked if runtime metadata changes: `nativephp.lock`
- Modify: `docs/progress.md`

- [ ] **Step 1: Regenerate the ignored native projects from canonical inputs**

Run:

```bash
npm run brand:build
php artisan native:install --force --no-interaction
npm run brand:native
```

Expected: Android and iOS projects regenerate; Sutelio overrides are copied after NativePHP's templates; `nativephp.lock` changes only if NativePHP refreshes embedded runtime metadata.

- [ ] **Step 2: Verify generated Android resources and manifest**

Run:

```bash
diff -u resources/brand/android/drawable/ic_launcher_background.xml nativephp/android/app/src/main/res/drawable/ic_launcher_background.xml
diff -u resources/brand/android/drawable/ic_launcher_foreground.xml nativephp/android/app/src/main/res/drawable/ic_launcher_foreground.xml
diff -u resources/brand/android/drawable/ic_launcher_monochrome.xml nativephp/android/app/src/main/res/drawable/ic_launcher_monochrome.xml
diff -u resources/brand/android/mipmap-anydpi-v33/ic_launcher.xml nativephp/android/app/src/main/res/mipmap-anydpi-v33/ic_launcher.xml
rg -n 'com.goleaf.sutelio|Sutelio|sutelio|windowSplashScreen|monochrome' nativephp/android/app/src/main nativephp/android/app/build.gradle.kts nativephp/android/settings.gradle.kts
```

Expected: every diff is empty; the generated manifest label is `Sutelio`; package/namespace uses `com.goleaf.sutelio`; adaptive, monochrome, and splash values exist.

- [ ] **Step 3: Verify generated iOS identity and assets**

Run:

```bash
rg -n 'com.goleaf.sutelio|Sutelio|sutelio' nativephp/ios
file nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png
sips -g pixelWidth -g pixelHeight nativephp/ios/NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png nativephp/ios/NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png
```

Expected: bundle identity and URL scheme are Sutelio, the icon is 1024 square, and splash assets are 1080 by 1920.

- [ ] **Step 4: Run NativePHP validation and the focused mobile test**

```bash
php artisan native:validate
php artisan test --compact tests/Feature/NativePhpMobileTest.php tests/Feature/BrandIdentityTest.php
```

Expected: validation passes with only the already documented no-NativeComponents warning; tests pass.

- [ ] **Step 5: Confirm generated output remains ignored**

```bash
git status --short
git check-ignore -v nativephp/android/app/src/main/AndroidManifest.xml nativephp/ios/NativePHP/Info.plist
```

Expected: `/nativephp` files do not appear as commit candidates. Commit `nativephp.lock` only if its factual runtime metadata changed.

## Task 8: Synchronize active repository instructions and canonical evidence

**Files:**

- Modify: `AGENTS.md`, `README.md`, `CHANGELOG.md`
- Modify: current canonical documents listed in the file map
- Modify: `tests/Feature/BrandIdentityTest.php`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Add the failing active-source legacy-identity guard**

Append to `tests/Feature/BrandIdentityTest.php`:

```php
test('active first-party files contain no legacy product package or starter identity', function () {
    $legacyIdentities = [
        'Xiaomi'.' Mimo',
        'xiaomi'.'-mimo',
        'xiaomi'.'mimo',
        'com.goleaf.'.'xiaomimimo',
        'laravel/'.'vue-starter-kit',
    ];
    $rootFiles = [
        '.env.example',
        'AGENTS.md',
        'README.md',
        'composer.json',
        'package.json',
        'package-lock.json',
        'docs/current-state.md',
        'docs/deployment.md',
        'docs/product-requirements.md',
    ];
    $activeRoots = ['app', 'config', 'lang', 'resources/js', 'resources/views', 'routes'];
    $paths = collect($rootFiles)
        ->map(fn (string $path): string => base_path($path))
        ->merge(collect($activeRoots)->flatMap(
            fn (string $path): array => collect(File::allFiles(base_path($path)))
                ->map(fn (SplFileInfo $file): string => $file->getPathname())
                ->all(),
        ));

    foreach ($paths as $path) {
        foreach ($legacyIdentities as $legacyIdentity) {
            expect(File::get($path), $path)->not->toContain($legacyIdentity);
        }
    }
});
```

Run:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php --filter='legacy product package'
```

Expected: the test fails on active AGENTS/README/current-state/deployment/product-requirements references that Task 8 owns.

- [ ] **Step 2: Rename the active engineering contract**

In `AGENTS.md`, make these exact active replacements:

```text
<xiaomi-mimo-canonical-instructions> -> <sutelio-canonical-instructions>
Xiaomi Mimo -> Sutelio
</xiaomi-mimo-canonical-instructions> -> </sutelio-canonical-instructions>
<xiaomi-mimo-project-contract> -> <sutelio-project-contract>
goleaf/xiaomi-mimo -> goleaf/sutelio
</xiaomi-mimo-project-contract> -> </sutelio-project-contract>
```

Do not alter framework/security/main-only requirements.

- [ ] **Step 3: Update the contributor/product entry points**

Use:

```markdown
# Sutelio

Sutelio is a local-first, workspace-scoped task and collaboration application for web and NativePHP Mobile.
```

Preserve the actual stack/setup instructions in README. Update current repository and site links to `goleaf/sutelio` and `sutelio.test`, but mark the site URL as effective after Task 12 until the Herd rename is complete.

- [ ] **Step 4: Add stable brand traceability**

Add `sys-brand-001` to `docs/requirements.md` with this contract:

```markdown
| `sys-brand-001` | Every active web and NativePHP surface identifies the product as Sutelio and uses the approved clean-S mark; Android/iOS use `com.goleaf.sutelio` and deep links use `sutelio`. | Users, operators, and build/release tooling. Brand assets are deterministic and package identifiers are treated as security/storage boundaries. | Web metadata/logo/favicon, one-color wordmark, adaptive/monochrome/native splash assets, APK manifest, and repository/Herd identity agree; old mobile data is not claimed to cross the new sandbox. | Brand sources/build scripts, Vue/Blade consumers, NativePHP config/resources; `BrandIdentityTest.php`, `NativePhpMobileTest.php`, browser/APK/emulator inspection | Implemented and verified after final rename gates |
```

Add a matching `sys-brand-001` row to `docs/compliance-matrix.md` pointing to the same code/tests and final browser/APK evidence.

- [ ] **Step 5: Update each owning canonical document, not historical evidence**

Record the following exact responsibilities:

```text
docs/architecture.md          product/package identity boundary and clean-install mobile sandbox
docs/frontend.md              AppLogo/AppLogoIcon consumers and metadata flow
docs/design-system.md         four brand tokens, 70% circle, one-color wordmark, no global theme family
docs/accessibility.md         decorative SVG semantics, accessible logo links, forced-colors review
docs/localization.md          Sutelio is a non-translated proper noun; EN/LT/RU grammar remains local
docs/testing.md               BrandIdentityTest plus browser/native visual checks
docs/deployment.md            icon/splash build commands, brand:native order, package ID, named APK
docs/known-limitations.md      no automatic old-package private SQLite migration
docs/implementation-plan.md   append the Sutelio rename phase and mark only observed steps complete
docs/code-review.md           brand/security/accessibility/native diff review result
docs/current-state.md         current product name and verified package after APK/emulator completion
docs/current-state-audit.md   prepend new Sutelio verification; preserve old APK/hash facts below
CHANGELOG.md                  append the in-place Sutelio rename; never rewrite prior releases
```

- [ ] **Step 6: Run active-name and documentation checks**

```bash
git grep -n -I -i -E 'Xiaomi Mimo|xiaomi-mimo|xiaomimimo|com\.goleaf\.xiaomimimo' -- ':!docs/progress.md' ':!docs/audit/**' ':!docs/audits/**' ':!docs/plans/**' ':!docs/superpowers/plans/**' ':!.mimocode/**' ':!graphify-out/**' ':!docs/current-state-audit.md'
npx prettier --check README.md CHANGELOG.md AGENTS.md docs
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/NativePhpMobileTest.php
```

Expected: the active-name search is empty. `docs/current-state-audit.md` is excluded because it intentionally retains old artifact evidence under a clear historical heading.

## Task 9: Run the complete application and data-safety gates

**Files:**

- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Format PHP and run static analysis**

```bash
vendor/bin/pint --dirty --format agent
composer types:check
```

Expected: Pint completes and Larastan reports zero errors.

- [ ] **Step 2: Run focused and complete Pest gates sequentially**

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/NativePhpMobileTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/DatabaseBackupTest.php
php artisan test --compact
```

Expected: focused and full suites pass; record exact test/assertion counts.

- [ ] **Step 3: Run the isolated parallel PHP gate without overlapping Vite**

```bash
./vendor/bin/pest --parallel --compact
```

Expected: all tests pass. Do not run this while any build command is replacing `public/build`.

- [ ] **Step 4: Run frontend and dependency gates**

```bash
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm audit
npm run build
npm run build:android
composer validate --strict
composer audit --locked --no-interaction
composer check-platform-reqs --no-dev
```

Expected: all pass; dependency locks contain no version change introduced by branding.

- [ ] **Step 5: Run isolated SQLite migration/seeding and framework caches**

Create a unique file-backed SQLite path under `storage/framework/testing`, then run:

```bash
brand_test_database=$(mktemp /Users/andrejprus/Herd/xiaomi-mimo/storage/framework/testing/sutelio-brand-XXXXXX.sqlite)
DB_CONNECTION=sqlite DB_DATABASE="$brand_test_database" php artisan migrate --force --no-interaction
DB_CONNECTION=sqlite DB_DATABASE="$brand_test_database" php artisan db:seed --force --no-interaction
DB_CONNECTION=sqlite DB_DATABASE="$brand_test_database" php artisan db:seed --force --no-interaction
DB_CONNECTION=sqlite DB_DATABASE="$brand_test_database" php artisan app:database-health --json --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

Expected: all 35 migrations and both seed runs pass, SQLite integrity is `ok`, foreign-key check is empty, and caches build/clear. Move `$brand_test_database` and only its exact `-wal`/`-shm` siblings, when present, to `/Users/andrejprus/.Trash`; never touch the real database.

## Task 10: Verify the rendered Sutelio web identity in a real browser

**Files:**

- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Resolve the current Herd URL without starting a server**

Run:

```bash
herd sites
```

Expected before Task 12: the checkout is served at `http://xiaomi-mimo.test`. Use that temporary URL for pre-rename browser verification; the rendered product must already say Sutelio.

- [ ] **Step 2: Verify guest/auth/shared-logo surfaces**

Using the persistent browser tooling, inspect login, registration, dashboard/sidebar, notification page, and an auth error state at desktop and 390-by-844 mobile viewports. Assert:

```text
Document titles end in “Sutelio”.
Favicon is the clean-S tile.
Sidebar/header/auth surfaces use the same mark.
Wordmark reads Sutelio in one color; final o is not orange.
No Laravel cube or Xiaomi Mimo text remains.
No horizontal overflow or duplicate main/heading appears.
No current JavaScript error or failed brand asset request appears.
```

- [ ] **Step 3: Verify accessibility/color modes**

Check keyboard focus, 200 percent zoom, light/dark/system appearance, reduced motion, and forced colors. The mark may simplify under forced colors, but the adjacent/accessible `Sutelio` name must remain perceivable.

- [ ] **Step 4: Record screenshots and current browser logs**

Store screenshots outside Git (under `/tmp` or the ignored artifact directory), inspect them visually, and record exact routes/viewports plus any current errors in `docs/progress.md`.

## Task 11: Build, inspect, install, and exercise the Sutelio APK

**Files:**

- Generated/ignored: `nativephp/android/**`
- Generated/ignored deliverable: `storage/app/native-build/sutelio-android-debug.apk`
- Modify current evidence: `docs/deployment.md`, `docs/current-state.md`, `docs/current-state-audit.md`, `docs/progress.md`

- [ ] **Step 1: Build a fresh APK with the known JDK/SDK**

Run:

```bash
export JAVA_HOME=/opt/homebrew/Cellar/openjdk@17/17.0.16/libexec/openjdk.jdk/Contents/Home
export NATIVEPHP_ANDROID_SDK_LOCATION=/Users/andrejprus/Library/Android/sdk
npm run build:android
DB_DATABASE=:memory: php artisan native:run android codex-build-only --build=debug --no-tty --no-interaction
```

Expected: Gradle `assembleDebug` succeeds and produces `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`; the deliberate nonexistent serial may produce only the already understood post-build install message.

- [ ] **Step 2: Publish the named ignored deliverable**

```bash
mkdir -p storage/app/native-build
cp nativephp/android/app/build/outputs/apk/debug/app-debug.apk storage/app/native-build/sutelio-android-debug.apk
shasum -a 256 storage/app/native-build/sutelio-android-debug.apk
```

Expected: the named file exists and remains ignored. Record exact bytes, timestamp, and SHA-256.

- [ ] **Step 3: Independently inspect package, resources, signature, alignment, and archives**

```bash
/Users/andrejprus/Library/Android/sdk/build-tools/37.0.0/aapt dump badging storage/app/native-build/sutelio-android-debug.apk
/Users/andrejprus/Library/Android/sdk/build-tools/37.0.0/aapt dump resources storage/app/native-build/sutelio-android-debug.apk | rg 'ic_launcher|monochrome|splash|Sutelio'
/Users/andrejprus/Library/Android/sdk/build-tools/37.0.0/apksigner verify --verbose --print-certs storage/app/native-build/sutelio-android-debug.apk
/Users/andrejprus/Library/Android/sdk/build-tools/37.0.0/zipalign -c -v 4 storage/app/native-build/sutelio-android-debug.apk
unzip -t storage/app/native-build/sutelio-android-debug.apk
```

Expected: package `com.goleaf.sutelio`, label `Sutelio`, min SDK 31, target/compile SDK 36, debug signature scheme v2, alignment/integrity pass, adaptive/monochrome/splash resources present.

Extract only the nested Laravel bundle and inspect it with these exact commands:

```bash
nested_laravel_bundle=$(mktemp /tmp/sutelio-laravel-bundle-XXXXXX.zip)
unzip -p storage/app/native-build/sutelio-android-debug.apk assets/laravel_bundle.zip > "$nested_laravel_bundle"
unzip -t "$nested_laravel_bundle"
unzip -l "$nested_laravel_bundle" | rg 'resources/brand/sutelio|public/(icon|splash|favicon)|lang/(en|lt|ru)'
unzip -p "$nested_laravel_bundle" .env | rg '^(APP_NAME="Sutelio"|NATIVEPHP_APP_ID=com\.goleaf\.sutelio|NATIVEPHP_DEEPLINK_SCHEME=sutelio)$'
if unzip -l "$nested_laravel_bundle" | rg -q 'database/database\.sqlite'; then exit 1; fi
if unzip -p "$nested_laravel_bundle" .env | rg -q '^MAIL_'; then exit 1; fi
```

Expected: nested ZIP integrity passes, required Sutelio/config/translations/assets exist, and both forbidden-data checks exit cleanly without a match. Move only `$nested_laravel_bundle` to `/Users/andrejprus/.Trash` after recording evidence.

- [ ] **Step 4: Start the existing Android 14 emulator**

Launch this exact AVD in a long-lived command session:

```bash
JAVA_HOME=/opt/homebrew/Cellar/openjdk@17/17.0.16/libexec/openjdk.jdk/Contents/Home /Users/andrejprus/Library/Android/sdk/emulator/emulator -avd BTChat_API_34 -no-snapshot-save
```

In another session, wait for one emulator and verify Android 14/API 34:

```bash
/Users/andrejprus/Library/Android/sdk/platform-tools/adb wait-for-device
/Users/andrejprus/Library/Android/sdk/platform-tools/adb devices -l
/Users/andrejprus/Library/Android/sdk/platform-tools/adb shell getprop sys.boot_completed
/Users/andrejprus/Library/Android/sdk/platform-tools/adb shell getprop ro.build.version.release
/Users/andrejprus/Library/Android/sdk/platform-tools/adb shell getprop ro.build.version.sdk
```

Expected: exactly one emulator serial, boot completed `1`, Android `14`, SDK `34`. Use the observed serial explicitly in every later ADB command.

- [ ] **Step 5: Clean-install only the new package and preserve the old package**

```bash
adb -s emulator-5554 shell pm list packages | rg 'com\.goleaf\.(xiaomimimo|sutelio)' || true
adb -s emulator-5554 uninstall com.goleaf.sutelio || true
adb -s emulator-5554 install storage/app/native-build/sutelio-android-debug.apk
adb -s emulator-5554 shell monkey -p com.goleaf.sutelio -c android.intent.category.LAUNCHER 1
```

Expected: `com.goleaf.sutelio` installs and launches; do not uninstall `com.goleaf.xiaomimimo`. If the emulator serial differs, replace only `emulator-5554` with the single observed serial.

- [ ] **Step 6: Exercise first boot and inspect visual/runtime evidence**

Verify cold system splash, NativePHP splash, login, registration rendering, locale text, app-private migration, and foreground/resumed process. Capture launcher and splash screenshots, then run:

```bash
adb -s emulator-5554 shell pidof com.goleaf.sutelio
adb -s emulator-5554 shell dumpsys activity activities | rg 'com.goleaf.sutelio|mResumedActivity'
adb -s emulator-5554 logcat -d | rg -i 'sutelio|laravel|fatal|exception|sqlite|verification|mail'
```

Expected: launcher/splash/in-app mark matches the clean-S master, app is resumed, first-run database integrity is `ok`, and no current Laravel fatal/error, SQLite containment failure, signed verification URL, or mail payload is logged. Record non-fatal emulator/framework diagnostics separately.

- [ ] **Step 7: Update exact artifact evidence**

Replace the active artifact paragraph in `docs/deployment.md` with observed Sutelio bytes/hash/package/resources/emulator facts. Prepend Sutelio evidence to `docs/current-state-audit.md` while retaining the old Xiaomi Mimo artifact paragraph as explicitly historical. Update `docs/current-state.md` only with checks actually observed.

## Task 12: Complete code review, source delivery, GitHub rename, and Herd rename

**Files:**

- Modify: `docs/code-review.md`, `docs/compliance-matrix.md`, `docs/implementation-plan.md`, `docs/progress.md`
- Modify after external rename: local ignored `.env`
- External state: GitHub repository, Git remote, local directory, Herd site

- [ ] **Step 1: Run final source and secret/generated-output scans**

```bash
git grep -n -I -i -E 'Xiaomi Mimo|xiaomi-mimo|xiaomimimo|com\.goleaf\.xiaomimimo' -- ':!docs/progress.md' ':!docs/audit/**' ':!docs/audits/**' ':!docs/plans/**' ':!docs/superpowers/plans/**' ':!.mimocode/**' ':!graphify-out/**' ':!docs/current-state-audit.md'
git diff --check
git status --short
git diff --stat
git diff
```

Expected: no active legacy hit, no secret/generated native/host database/APK staged, and all remaining diffs are phase-owned.

- [ ] **Step 2: Record the six-pillar review**

In `docs/code-review.md` and `docs/progress.md`, record factual results for:

```text
Identity consistency
Asset geometry/raster integrity
Localization/accessibility
Mobile package/storage boundary
Security/secret/generated-output scope
Web/native build and runtime verification
```

Query delta is exactly zero because this phase adds no Eloquent query or schema change.

- [ ] **Step 3: Commit and push final implementation/canonical evidence**

Validate semantic messages before use, then stage only owned files:

```bash
git add AGENTS.md README.md CHANGELOG.md docs/index.md docs/product-requirements.md docs/requirements.md docs/architecture.md docs/frontend.md docs/design-system.md docs/accessibility.md docs/localization.md docs/testing.md docs/deployment.md docs/current-state.md docs/current-state-audit.md docs/known-limitations.md docs/implementation-plan.md docs/compliance-matrix.md docs/code-review.md docs/progress.md tests/Feature/BrandIdentityTest.php
if ! git diff --quiet -- nativephp.lock; then git add nativephp.lock; fi
git diff --cached --check
git diff --cached --stat
git commit -m "docs: synchronize Sutelio delivery evidence"
git push --verbose origin main
git status --short --branch
```

Expected: clean synchronized `main`. Omit `nativephp.lock` from staging if unchanged; ignored `.env`, APK, `/nativephp`, database, and screenshots remain unstaged.

- [ ] **Step 4: Rename the existing GitHub repository in place**

Resolve and verify the exact target first:

```bash
gh repo view goleaf/xiaomi-mimo --json nameWithOwner,url,defaultBranchRef
gh repo view goleaf/sutelio --json nameWithOwner,url 2>/dev/null || true
```

Expected: source exists as `goleaf/xiaomi-mimo`, default branch `main`, and no conflicting `goleaf/sutelio` repository exists. Then run:

```bash
gh repo rename -R goleaf/xiaomi-mimo sutelio --yes
git remote set-url origin https://github.com/goleaf/sutelio.git
git remote -v
git fetch origin main
git push --verbose origin main
gh repo view goleaf/sutelio --json nameWithOwner,url,defaultBranchRef
```

Expected: the same repository/history is now `goleaf/sutelio`, `origin` uses the new URL, fetch/push succeeds, and default branch remains `main`. Never create a replacement repository or force-push.

- [ ] **Step 5: Rename the local checkout and Herd site last**

From `/Users/andrejprus/Herd`, verify the target path does not exist, then run:

```bash
herd unlink xiaomi-mimo --no-interaction
mv /Users/andrejprus/Herd/xiaomi-mimo /Users/andrejprus/Herd/sutelio
cd /Users/andrejprus/Herd/sutelio
herd link sutelio --isolate=8.5 --update-env --no-interaction
herd sites
git status --short --branch
```

Expected: `/Users/andrejprus/Herd/sutelio` is the checkout, `http://sutelio.test` resolves to it with PHP 8.5, old Herd link is absent, `.env` has `APP_URL=http://sutelio.test`, and Git remains clean/synchronized.

- [ ] **Step 6: Re-run final URL/browser smoke after the path rename**

Open `http://sutelio.test`, verify the Sutelio favicon/title/logo plus login and one authenticated page, and inspect current browser logs. Do not start a second web server.

- [ ] **Step 7: Append and deliver the final status-only evidence**

From the new path, append exact source commit hashes, push ranges, repository rename result, remote URL, Herd path/URL, APK path/hash, emulator serial/package, checks, limitations, and clean status to `docs/progress.md`. Then:

```bash
git add docs/progress.md
git diff --cached --check
git commit -m "docs: record Sutelio project rename"
git push --verbose origin main
git status --short --branch
```

Expected: final clean synchronized `main` at `goleaf/sutelio`. Report debug signing, Android-emulator-not-real-hardware coverage, missing Xdebug/PCOV if still absent, and the intentional no-cross-package-data-migration boundary without calling them completed production release work.

## Final Acceptance Checklist

- [ ] Web title, metadata, favicon, auth/shared logos, and wordmark say Sutelio and match the clean-S design.
- [ ] All EN/LT/RU active copy uses the proper Sutelio name with locale parity.
- [ ] Composer/npm/Laravel/NativePHP/deep-link/service/store/backup/storage identities use Sutelio.
- [ ] Android/iOS generated identities are `com.goleaf.sutelio`; Android has legacy, round, adaptive, monochrome, and Android 12+ splash resources.
- [ ] Fresh APK independently passes manifest/resource/signature/alignment/archive/database-exclusion checks.
- [ ] Android 14 emulator clean-install launches and shows the accepted launcher/splash/in-app identity with no current fatal/runtime/security log error.
- [ ] Active first-party source has no unintended old product/package or Laravel starter identity; historical facts remain explicitly historical.
- [ ] Pint, Larastan, focused/full/parallel Pest, frontend tests/types/lint/format, audits, web/Android builds, isolated SQLite, caches, browser checks, and Composer gates have factual recorded results.
- [ ] Existing GitHub repository is renamed in place to `goleaf/sutelio`; local checkout/Herd site is `/Users/andrejprus/Herd/sutelio` at `http://sutelio.test`.
- [ ] All phase commits and final status evidence are pushed normally to `origin/main`; no history rewrite, force push, unrelated file, secret, database, generated native project, or debug APK enters Git.
