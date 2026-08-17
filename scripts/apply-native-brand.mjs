import { cpSync, existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const source = resolve(root, 'resources/brand/android');
const destination = resolve(root, 'nativephp/android/app/src/main/res');
const androidBuildConfiguration = resolve(
    root,
    'nativephp/android/app/build.gradle.kts',
);
const androidManifest = resolve(
    root,
    'nativephp/android/app/src/main/AndroidManifest.xml',
);
const canonicalAppId = 'com.goleaf.sutelio';
const canonicalAppName = 'Sutelio';

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

if (!existsSync(androidBuildConfiguration) || !existsSync(androidManifest)) {
    throw new Error(
        'Run php artisan native:install --force before applying native brand assets.',
    );
}

const buildConfiguration = readFileSync(androidBuildConfiguration, 'utf8');
const manifest = readFileSync(androidManifest, 'utf8');

if (
    !buildConfiguration.includes(`applicationId = "${canonicalAppId}"`) ||
    !manifest.includes(`android:label="${canonicalAppName}"`)
) {
    throw new Error(
        `Generated NativePHP identity must be ${canonicalAppId} / ${canonicalAppName}. Run php artisan native:install --force before applying native brand assets.`,
    );
}

cpSync(source, destination, { recursive: true, force: true });
console.log(
    'Applied Sutelio adaptive, monochrome, and Android 12+ splash resources.',
);
