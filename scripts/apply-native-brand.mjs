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
