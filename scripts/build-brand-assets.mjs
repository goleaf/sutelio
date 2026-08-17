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
    const sPathMatch = definitions.match(
        /<g id="glyph-0-0">\s*<path d="([^"]+)"\/>\s*<\/g>/,
    );

    if (!usesMatch || !sPathMatch) {
        throw new Error('Unable to extract outlined Sutelio glyph geometry.');
    }

    const wordmarkUses = usesMatch[1];
    const sPath = sPathMatch[1];
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

    const markArtwork = `
  <rect width="512" height="512" rx="112" fill="${colors.cobalt}"/>
  <circle cx="256" cy="256" r="179.2" fill="${colors.orange}"/>
  <path d="${sPath}" transform="translate(149.73 376.14) scale(1.3)" fill="${colors.ivory}"/>`;
    const markSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512" role="img" aria-labelledby="sutelio-mark-title">
  <title id="sutelio-mark-title">Sutelio</title>${markArtwork}
</svg>`;
    const opaqueMarkSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <rect width="512" height="512" fill="${colors.cobalt}"/>${markArtwork}
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
        'M256,76.8 A179.2,179.2 0 1,1 255.999,435.2 A179.2,179.2 0 1,1 256,76.8 Z';
    const androidDrawable = resolve(buildBrand, 'android/drawable');
    const androidV26 = resolve(buildBrand, 'android/mipmap-anydpi-v26');
    const androidV33 = resolve(buildBrand, 'android/mipmap-anydpi-v33');
    const androidV31 = resolve(buildBrand, 'android/values-v31');
    const androidNightV31 = resolve(buildBrand, 'android/values-night-v31');

    write(
        resolve(androidDrawable, 'ic_launcher_background.xml'),
        `<shape xmlns:android="http://schemas.android.com/apk/res/android" android:shape="rectangle"><solid android:color="${colors.cobalt}"/></shape>`,
    );
    write(
        resolve(androidDrawable, 'ic_launcher_foreground.xml'),
        `<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512"><group android:pivotX="256" android:pivotY="256" android:scaleX="0.86" android:scaleY="0.86"><path android:fillColor="${colors.orange}" android:pathData="${circlePath}"/><group android:translateX="149.73" android:translateY="376.14" android:scaleX="1.3" android:scaleY="1.3"><path android:fillColor="${colors.ivory}" android:pathData="${sPath}"/></group></group></vector>`,
    );
    write(
        resolve(androidDrawable, 'ic_launcher_monochrome.xml'),
        `<vector xmlns:android="http://schemas.android.com/apk/res/android" android:width="108dp" android:height="108dp" android:viewportWidth="512" android:viewportHeight="512"><group android:pivotX="256" android:pivotY="256" android:scaleX="0.86" android:scaleY="0.86"><group android:translateX="149.73" android:translateY="376.14" android:scaleX="1.3" android:scaleY="1.3"><path android:fillColor="#FF000000" android:pathData="${sPath}"/></group></group></vector>`,
    );

    const adaptiveIcon = (withMonochrome) =>
        `<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android"><background android:drawable="@drawable/ic_launcher_background"/><foreground android:drawable="@drawable/ic_launcher_foreground"/>${withMonochrome ? '<monochrome android:drawable="@drawable/ic_launcher_monochrome"/>' : ''}</adaptive-icon>`;

    for (const filename of ['ic_launcher.xml', 'ic_launcher_round.xml']) {
        write(resolve(androidV26, filename), adaptiveIcon(false));
        write(resolve(androidV33, filename), adaptiveIcon(true));
    }

    const androidTheme = (primary) =>
        `<resources><style name="Theme.AndroidPHP" parent="Theme.MaterialComponents.DayNight.DarkActionBar"><item name="colorPrimary">${primary}</item><item name="colorPrimaryVariant">${primary}</item><item name="colorOnPrimary">${colors.ivory}</item><item name="colorAccent">${colors.orange}</item><item name="android:colorAccent">${colors.orange}</item><item name="android:windowDrawsSystemBarBackgrounds">true</item><item name="android:statusBarColor">@android:color/transparent</item><item name="android:navigationBarColor">@android:color/transparent</item><item name="android:enforceStatusBarContrast">false</item><item name="android:enforceNavigationBarContrast">false</item><item name="android:windowSplashScreenBackground">${colors.ivory}</item><item name="android:windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item><item name="android:windowSplashScreenIconBackgroundColor">${colors.cobalt}</item></style></resources>`;

    write(resolve(androidV31, 'themes.xml'), androidTheme(colors.cobalt));
    write(
        resolve(androidNightV31, 'themes.xml'),
        androidTheme(colors.deepCobalt),
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
