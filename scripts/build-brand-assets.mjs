import { execFileSync } from 'node:child_process';
import { createHash, randomUUID } from 'node:crypto';
import {
    copyFileSync,
    existsSync,
    mkdirSync,
    mkdtempSync,
    readFileSync,
    renameSync,
    rmSync,
    statSync,
    writeFileSync,
} from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const brand = resolve(root, 'resources/brand');
const font = resolve(brand, 'fonts/InstrumentSans.ttf');
const expectedFontHash =
    'b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1';
const colors = {
    cobalt: '#123C8B',
    deepCobalt: '#0A285F',
    orange: '#FF6038',
    ivory: '#FFF8E9',
};
const outputPaths = [
    'resources/brand/sutelio-mark.svg',
    'resources/brand/sutelio-wordmark.svg',
    'resources/brand/sutelio-lockup.svg',
    'resources/brand/sutelio-splash.svg',
    'resources/brand/sutelio-android-store-512.png',
    'resources/brand/android/drawable/ic_launcher_background.xml',
    'resources/brand/android/drawable/ic_launcher_foreground.xml',
    'resources/brand/android/drawable/ic_launcher_monochrome.xml',
    'resources/brand/android/drawable/sutelio_splash_animated.xml',
    'resources/brand/android/drawable/sutelio_splash_icon.xml',
    'resources/brand/android/animator/sutelio_splash_fade.xml',
    'resources/brand/android/animator/sutelio_splash_scale.xml',
    'resources/brand/android/mipmap-anydpi-v26/ic_launcher.xml',
    'resources/brand/android/mipmap-anydpi-v26/ic_launcher_round.xml',
    'resources/brand/android/mipmap-anydpi-v33/ic_launcher.xml',
    'resources/brand/android/mipmap-anydpi-v33/ic_launcher_round.xml',
    'resources/brand/android/values-v31/themes.xml',
    'resources/brand/android/values-night-v31/themes.xml',
    'resources/brand/android/values/sutelio_splash_strings.xml',
    'resources/brand/android/values-lt/sutelio_splash_strings.xml',
    'resources/brand/android/values-ru/sutelio_splash_strings.xml',
    'public/icon.png',
    'public/apple-touch-icon.png',
    'public/splash.png',
    'public/splash-dark.png',
    'public/favicon.svg',
    'public/favicon.ico',
];
const expectedRasterDimensions = new Map([
    ['resources/brand/sutelio-android-store-512.png', [512, 512]],
    ['public/icon.png', [1024, 1024]],
    ['public/apple-touch-icon.png', [180, 180]],
    ['public/splash.png', [1080, 1920]],
    ['public/splash-dark.png', [1080, 1920]],
    ['public/favicon.ico', [32, 32]],
]);
const opaquePngPaths = [
    'resources/brand/sutelio-android-store-512.png',
    'public/icon.png',
    'public/apple-touch-icon.png',
    'public/splash.png',
    'public/splash-dark.png',
];

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

function readRasterDimensions(path) {
    const metadata = execFileSync(
        'sips',
        ['-g', 'pixelWidth', '-g', 'pixelHeight', path],
        { cwd: root, encoding: 'utf8' },
    );
    const width = metadata.match(/pixelWidth: (\d+)/)?.[1];
    const height = metadata.match(/pixelHeight: (\d+)/)?.[1];

    if (!width || !height) {
        throw new Error(`Unable to validate raster dimensions for ${path}.`);
    }

    return [Number(width), Number(height)];
}

function hashFile(path) {
    return createHash('sha256').update(readFileSync(path)).digest('hex');
}

function readManifestEntry(path) {
    const metadata = statSync(path);

    if (!metadata.isFile() || metadata.size === 0) {
        throw new Error(`Manifest file is missing or empty: ${path}`);
    }

    return {
        bytes: metadata.size,
        sha256: hashFile(path),
    };
}

function createOutputManifest(outputDirectory) {
    return new Map(
        outputPaths.map((relativePath) => [
            relativePath,
            readManifestEntry(resolve(outputDirectory, relativePath)),
        ]),
    );
}

function assertFileMatchesManifest(path, expected, label) {
    const actual = readManifestEntry(path);

    if (actual.bytes !== expected.bytes || actual.sha256 !== expected.sha256) {
        throw new Error(`Manifest mismatch for ${label}.`);
    }
}

function validateOpaquePngs(buildDirectory) {
    const validationCode = String.raw`
foreach (array_slice($argv, 1) as $path) {
    $image = @imagecreatefrompng($path);

    if ($image === false) {
        fwrite(STDERR, "Unable to read PNG for opacity validation: {$path}\n");
        exit(1);
    }

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

            if ($alpha !== 0) {
                imagedestroy($image);
                fwrite(STDERR, "PNG must be fully opaque: {$path} at {$x},{$y}\n");
                exit(1);
            }
        }
    }

    imagedestroy($image);
}
`;
    const pngPaths = opaquePngPaths.map((relativePath) =>
        resolve(buildDirectory, relativePath),
    );

    execFileSync('php', ['-r', validationCode, ...pngPaths], {
        cwd: root,
        stdio: 'pipe',
    });
}

function validateBuildOutputs(buildDirectory) {
    for (const relativePath of outputPaths) {
        const output = resolve(buildDirectory, relativePath);
        const metadata = statSync(output);

        if (!metadata.isFile() || metadata.size === 0) {
            throw new Error(
                `Brand output is missing or empty: ${relativePath}`,
            );
        }
    }

    const mark = readFileSync(
        resolve(buildDirectory, 'resources/brand/sutelio-mark.svg'),
    );
    const favicon = readFileSync(resolve(buildDirectory, 'public/favicon.svg'));

    if (!mark.equals(favicon)) {
        throw new Error(
            'The generated favicon.svg differs from the master mark.',
        );
    }

    const svgPaths = outputPaths.filter((path) => path.endsWith('.svg'));
    const forbiddenSvg = /<text|<line|Gradient|stroke=|style=/;

    for (const relativePath of svgPaths) {
        const svg = readFileSync(resolve(buildDirectory, relativePath), 'utf8');

        if (forbiddenSvg.test(svg)) {
            throw new Error(
                `Forbidden construct found in generated SVG: ${relativePath}`,
            );
        }
    }

    const xmlPaths = outputPaths
        .filter((path) => path.endsWith('.svg') || path.endsWith('.xml'))
        .map((path) => resolve(buildDirectory, path));

    execFileSync('xmllint', ['--noout', ...xmlPaths], {
        cwd: root,
        stdio: 'ignore',
    });

    for (const [relativePath, expectedDimensions] of expectedRasterDimensions) {
        const dimensions = readRasterDimensions(
            resolve(buildDirectory, relativePath),
        );

        if (
            dimensions[0] !== expectedDimensions[0] ||
            dimensions[1] !== expectedDimensions[1]
        ) {
            throw new Error(
                `Unexpected raster dimensions for ${relativePath}: ${dimensions.join('x')}`,
            );
        }
    }

    validateOpaquePngs(buildDirectory);

    return createOutputManifest(buildDirectory);
}

function rollbackPublishedOutputs(publishedEntries) {
    const rollbackErrors = [];

    for (const entry of [...publishedEntries].reverse()) {
        try {
            if (entry.originalManifest === null) {
                rmSync(entry.destination, { force: true });

                if (existsSync(entry.destination)) {
                    throw new Error(
                        `New output still exists after rollback: ${entry.relativePath}`,
                    );
                }

                continue;
            }

            copyFileSync(entry.backupPath, entry.stagedPath);
            assertFileMatchesManifest(
                entry.stagedPath,
                entry.originalManifest,
                `rollback staging for ${entry.relativePath}`,
            );
            renameSync(entry.stagedPath, entry.destination);
            assertFileMatchesManifest(
                entry.destination,
                entry.originalManifest,
                `restored ${entry.relativePath}`,
            );
        } catch (error) {
            rollbackErrors.push(
                new Error(`Unable to restore ${entry.relativePath}.`, {
                    cause: error,
                }),
            );
        }
    }

    return rollbackErrors;
}

function publishBuildOutputs(buildDirectory, manifest) {
    const publishId = randomUUID();
    const entries = outputPaths.map((relativePath) => {
        const destination = resolve(root, relativePath);

        return {
            relativePath,
            source: resolve(buildDirectory, relativePath),
            destination,
            stagedPath: `${destination}.sutelio-stage-${publishId}`,
            backupPath: `${destination}.sutelio-backup-${publishId}`,
            expectedManifest: manifest.get(relativePath),
            originalManifest: null,
        };
    });
    const publishedEntries = [];

    try {
        for (const entry of entries) {
            ensureDirectory(dirname(entry.destination));

            if (!entry.expectedManifest) {
                throw new Error(
                    `Build manifest is missing ${entry.relativePath}.`,
                );
            }

            copyFileSync(entry.source, entry.stagedPath);
            assertFileMatchesManifest(
                entry.stagedPath,
                entry.expectedManifest,
                `publish staging for ${entry.relativePath}`,
            );
        }

        for (const entry of entries) {
            if (!existsSync(entry.destination)) {
                continue;
            }

            entry.originalManifest = readManifestEntry(entry.destination);
            copyFileSync(entry.destination, entry.backupPath);
            assertFileMatchesManifest(
                entry.backupPath,
                entry.originalManifest,
                `backup for ${entry.relativePath}`,
            );
        }

        try {
            // Sibling renames avoid partial files. The complete set relies on
            // caught-error rollback and cannot cover a process termination.
            for (const entry of entries) {
                const { destination, expectedManifest, relativePath } = entry;

                renameSync(entry.stagedPath, destination);
                publishedEntries.push(entry);
                assertFileMatchesManifest(
                    destination,
                    expectedManifest,
                    `published ${relativePath}`,
                );
            }
        } catch (publishError) {
            const rollbackErrors = rollbackPublishedOutputs(publishedEntries);

            if (rollbackErrors.length > 0) {
                throw new AggregateError(
                    [publishError, ...rollbackErrors],
                    'Brand publication failed and rollback was incomplete.',
                );
            }

            throw publishError;
        }
    } finally {
        for (const entry of entries) {
            rmSync(entry.stagedPath, { force: true });
            rmSync(entry.backupPath, { force: true });
        }
    }
}

function buildBrandAssets(buildDirectory) {
    const buildBrand = resolve(buildDirectory, 'resources/brand');
    const buildPublic = resolve(buildDirectory, 'public');
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

    if (!usesMatch) {
        throw new Error(
            'Unable to extract outlined Sutelio wordmark geometry.',
        );
    }

    const wordmarkUses = usesMatch[1];
    const glyphPaths = new Map(
        [
            ...definitions.matchAll(
                /<g id="([^"]+)">\s*<path d="([^"]+)"\/>\s*<\/g>/g,
            ),
        ].map(([, id, path]) => [id, path]),
    );
    const inlineWordmarkPaths = wordmarkUses.replace(
        /<use xlink:href="#([^"]+)" x="([^"]+)" y="([^"]+)"\/>/g,
        (_use, glyphId, x, y) => {
            const glyphPath = glyphPaths.get(glyphId);

            if (!glyphPath) {
                throw new Error(
                    `Unable to inline ${glyphId} from hb-view output.`,
                );
            }

            return `<path d="${glyphPath}" transform="translate(${x} ${y})"/>`;
        },
    );

    if (inlineWordmarkPaths.includes('<use')) {
        throw new Error('Unable to inline every Sutelio wordmark glyph.');
    }

    const ribbonPath =
        'M353 154 C323 115 279 94 228 99 C173 104 131 135 128 183 C125 229 158 254 222 268 L270 279 C295 285 309 299 307 318 C304 343 277 358 244 358 C199 358 164 338 137 304 L92 347 C128 394 181 421 245 421 C316 421 373 383 380 322 C386 264 347 230 284 216 L236 205 C208 199 193 188 195 173 C197 153 218 141 245 141 C281 141 308 157 331 186 Z';
    const ribbonArtwork = `<path data-mark="sutelio-ribbon" d="${ribbonPath}" fill="${colors.ivory}"/>`;
    const markArtwork = `
  <circle cx="256" cy="256" r="220" fill="${colors.orange}"/>
  ${ribbonArtwork}`;
    const markSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512" role="img" aria-labelledby="sutelio-mark-title">
  <title id="sutelio-mark-title">Sutelio</title>${markArtwork}
</svg>`;
    const opaqueMarkSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <rect width="512" height="512" fill="${colors.orange}"/>
  ${ribbonArtwork}
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
    const rasterSplashSvg = splashSvg.replace(
        wordmarkUses,
        inlineWordmarkPaths,
    );

    write(resolve(buildBrand, 'sutelio-mark.svg'), markSvg);
    write(resolve(buildBrand, 'sutelio-wordmark.svg'), wordmarkSvg);
    write(resolve(buildBrand, 'sutelio-lockup.svg'), lockupSvg);
    write(resolve(buildBrand, 'sutelio-splash.svg'), splashSvg);
    write(resolve(buildPublic, 'favicon.svg'), markSvg);

    const opaqueMarkSource = resolve(buildDirectory, 'opaque-mark.svg');

    write(opaqueMarkSource, opaqueMarkSvg);
    renderPng(opaqueMarkSource, resolve(buildPublic, 'icon.png'), 1024, 1024);
    renderPng(
        opaqueMarkSource,
        resolve(buildPublic, 'apple-touch-icon.png'),
        180,
        180,
    );
    renderPng(
        opaqueMarkSource,
        resolve(buildBrand, 'sutelio-android-store-512.png'),
        512,
        512,
    );

    const rasterSplashSource = resolve(buildDirectory, 'raster-splash.svg');

    write(rasterSplashSource, rasterSplashSvg);
    renderPng(
        rasterSplashSource,
        resolve(buildPublic, 'splash.png'),
        1080,
        1920,
    );
    copyFileSync(
        resolve(buildPublic, 'splash.png'),
        resolve(buildPublic, 'splash-dark.png'),
    );

    const faviconPng = resolve(buildDirectory, 'favicon-32.png');

    renderPng(resolve(buildBrand, 'sutelio-mark.svg'), faviconPng, 32, 32);
    execFileSync(
        'sips',
        [
            '-s',
            'format',
            'ico',
            faviconPng,
            '--out',
            resolve(buildPublic, 'favicon.ico'),
        ],
        { cwd: root, stdio: 'ignore' },
    );

    const circlePath =
        'M256,36 A220,220 0 1,1 255.999,476 A220,220 0 1,1 256,36 Z';
    const androidDrawable = resolve(buildBrand, 'android/drawable');
    const androidAnimator = resolve(buildBrand, 'android/animator');
    const androidV26 = resolve(buildBrand, 'android/mipmap-anydpi-v26');
    const androidV33 = resolve(buildBrand, 'android/mipmap-anydpi-v33');
    const androidV31 = resolve(buildBrand, 'android/values-v31');
    const androidNightV31 = resolve(buildBrand, 'android/values-night-v31');
    const androidValues = resolve(buildBrand, 'android/values');
    const androidValuesLt = resolve(buildBrand, 'android/values-lt');
    const androidValuesRu = resolve(buildBrand, 'android/values-ru');

    write(
        resolve(androidDrawable, 'ic_launcher_background.xml'),
        `<shape xmlns:android="http://schemas.android.com/apk/res/android" android:shape="rectangle"><solid android:color="${colors.orange}"/></shape>`,
    );
    write(
        resolve(androidDrawable, 'ic_launcher_foreground.xml'),
        `<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512"><group android:pivotX="256" android:pivotY="256" android:scaleX="0.8" android:scaleY="0.8"><path android:fillColor="${colors.ivory}" android:pathData="${ribbonPath}"/></group></vector>`,
    );
    write(
        resolve(androidDrawable, 'ic_launcher_monochrome.xml'),
        `<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512"><group android:pivotX="256" android:pivotY="256" android:scaleX="0.8" android:scaleY="0.8"><path android:fillColor="#FF000000" android:pathData="${ribbonPath}"/></group></vector>`,
    );
    write(
        resolve(androidDrawable, 'sutelio_splash_icon.xml'),
        `<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="192dp" android:height="192dp" android:viewportWidth="512" android:viewportHeight="512"><group android:name="sutelio_mark" android:pivotX="256" android:pivotY="256"><path android:name="signal_disc" android:fillColor="${colors.orange}" android:pathData="${circlePath}"/><path android:name="ribbon" android:fillColor="${colors.ivory}" android:pathData="${ribbonPath}"/></group></vector>`,
    );
    write(
        resolve(androidAnimator, 'sutelio_splash_scale.xml'),
        `<set xmlns:android="http://schemas.android.com/apk/res/android" android:ordering="together"><objectAnimator android:duration="650" android:propertyName="scaleX" android:valueFrom="0.9" android:valueTo="1" android:valueType="floatType" android:interpolator="@android:interpolator/fast_out_slow_in"/><objectAnimator android:duration="650" android:propertyName="scaleY" android:valueFrom="0.9" android:valueTo="1" android:valueType="floatType" android:interpolator="@android:interpolator/fast_out_slow_in"/></set>`,
    );
    write(
        resolve(androidAnimator, 'sutelio_splash_fade.xml'),
        `<objectAnimator xmlns:android="http://schemas.android.com/apk/res/android" android:duration="420" android:propertyName="fillAlpha" android:valueFrom="0.15" android:valueTo="1" android:valueType="floatType" android:interpolator="@android:interpolator/fast_out_slow_in"/>`,
    );
    write(
        resolve(androidDrawable, 'sutelio_splash_animated.xml'),
        `<animated-vector xmlns:android="http://schemas.android.com/apk/res/android" android:drawable="@drawable/sutelio_splash_icon"><target android:name="sutelio_mark" android:animation="@animator/sutelio_splash_scale"/><target android:name="signal_disc" android:animation="@animator/sutelio_splash_fade"/><target android:name="ribbon" android:animation="@animator/sutelio_splash_fade"/></animated-vector>`,
    );

    const adaptiveIcon = (withMonochrome) =>
        `<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android"><background android:drawable="@drawable/ic_launcher_background"/><foreground android:drawable="@drawable/ic_launcher_foreground"/>${withMonochrome ? '<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>' : ''}</adaptive-icon>`;

    for (const filename of ['ic_launcher.xml', 'ic_launcher_round.xml']) {
        write(resolve(androidV26, filename), adaptiveIcon(false));
        write(resolve(androidV33, filename), adaptiveIcon(true));
    }

    const androidTheme = (primary) =>
        `<resources><style name="Theme.AndroidPHP" parent="Theme.MaterialComponents.DayNight.DarkActionBar"><item name="colorPrimary">${primary}</item><item name="colorPrimaryVariant">${primary}</item><item name="colorOnPrimary">${colors.ivory}</item><item name="colorAccent">${colors.orange}</item><item name="android:colorAccent">${colors.orange}</item><item name="android:windowDrawsSystemBarBackgrounds">true</item><item name="android:statusBarColor">@android:color/transparent</item><item name="android:navigationBarColor">@android:color/transparent</item><item name="android:enforceStatusBarContrast">false</item><item name="android:enforceNavigationBarContrast">false</item><item name="android:windowSplashScreenBackground">${colors.ivory}</item><item name="android:windowSplashScreenAnimatedIcon">@drawable/sutelio_splash_animated</item><item name="android:windowSplashScreenAnimationDuration">650</item></style></resources>`;

    write(resolve(androidV31, 'themes.xml'), androidTheme(colors.cobalt));
    write(
        resolve(androidNightV31, 'themes.xml'),
        androidTheme(colors.deepCobalt),
    );
    const splashStrings = (tagline, status, privacy) =>
        `<resources><string name="sutelio_splash_title">Sutelio</string><string name="sutelio_splash_tagline">${tagline}</string><string name="sutelio_splash_status">${status}</string><string name="sutelio_splash_privacy">${privacy}</string></resources>`;

    write(
        resolve(androidValues, 'sutelio_splash_strings.xml'),
        splashStrings(
            'Your day, clearly organized',
            'Preparing your local workspace…',
            'Local-first • Privacy by design',
        ),
    );
    write(
        resolve(androidValuesLt, 'sutelio_splash_strings.xml'),
        splashStrings(
            'Jūsų diena – aiškiai ir paprastai',
            'Ruošiama vietinė darbo erdvė…',
            'Vietiniai duomenys • Privatumas pirmiausia',
        ),
    );
    write(
        resolve(androidValuesRu, 'sutelio_splash_strings.xml'),
        splashStrings(
            'Ваш день — ясно и по делу',
            'Готовим локальное рабочее пространство…',
            'Локальные данные • Приватность прежде всего',
        ),
    );

    return validateBuildOutputs(buildDirectory);
}

const buildDirectory = mkdtempSync(join(tmpdir(), 'sutelio-brand-'));

try {
    const manifest = buildBrandAssets(buildDirectory);

    publishBuildOutputs(buildDirectory, manifest);
    console.log('Built deterministic Sutelio web and NativePHP brand assets.');
} finally {
    rmSync(buildDirectory, { recursive: true, force: true });
}
