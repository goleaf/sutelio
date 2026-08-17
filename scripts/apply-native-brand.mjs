import {
    cpSync,
    existsSync,
    lstatSync,
    readFileSync,
    readdirSync,
    realpathSync,
} from 'node:fs';
import { dirname, isAbsolute, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const workspaceRoot = realpathSync(
    resolve(dirname(fileURLToPath(import.meta.url)), '..'),
);
const source = resolve(workspaceRoot, 'resources/brand/android');
const generatedRoot = resolve(workspaceRoot, 'nativephp/android');
const destination = resolve(generatedRoot, 'app/src/main/res');
const androidBuildConfiguration = resolve(
    generatedRoot,
    'app/build.gradle.kts',
);
const androidManifest = resolve(
    generatedRoot,
    'app/src/main/AndroidManifest.xml',
);
const canonicalAppId = 'com.goleaf.sutelio';
const canonicalAppName = 'Sutelio';

function isContained(expectedRoot, candidate) {
    const relativePath = relative(expectedRoot, candidate);

    return (
        relativePath === '' ||
        (relativePath !== '..' &&
            !relativePath.startsWith(`..${sep}`) &&
            !isAbsolute(relativePath))
    );
}

function assertNoSymlinkComponents(expectedRoot, candidate, label) {
    if (!isContained(expectedRoot, candidate)) {
        throw new Error(`${label} must stay within ${expectedRoot}.`);
    }

    const relativePath = relative(expectedRoot, candidate);
    const components = relativePath === '' ? [] : relativePath.split(sep);
    let currentPath = expectedRoot;

    if (lstatSync(currentPath).isSymbolicLink()) {
        throw new Error(
            `${label} must not contain a symbolic link: ${currentPath}.`,
        );
    }

    for (const component of components) {
        currentPath = resolve(currentPath, component);

        if (lstatSync(currentPath).isSymbolicLink()) {
            throw new Error(
                `${label} must not contain a symbolic link: ${currentPath}.`,
            );
        }
    }
}

function assertRealPathContained(expectedRoot, candidate, label) {
    const realExpectedRoot = realpathSync(expectedRoot);
    const realCandidate = realpathSync(candidate);

    if (!isContained(realExpectedRoot, realCandidate)) {
        throw new Error(`${label} resolves outside ${realExpectedRoot}.`);
    }
}

function assertSafeDirectory(expectedRoot, candidate, label) {
    assertNoSymlinkComponents(expectedRoot, candidate, label);
    assertRealPathContained(expectedRoot, candidate, label);

    if (!lstatSync(candidate).isDirectory()) {
        throw new Error(`${label} must be a directory.`);
    }
}

function assertSafeFile(expectedRoot, candidate, label) {
    assertNoSymlinkComponents(expectedRoot, candidate, label);
    assertRealPathContained(expectedRoot, candidate, label);

    if (!lstatSync(candidate).isFile()) {
        throw new Error(`${label} must be a regular file.`);
    }
}

function assertTreeHasNoSymlinks(directory, label) {
    for (const entry of readdirSync(directory, { withFileTypes: true })) {
        const entryPath = resolve(directory, entry.name);

        if (entry.isSymbolicLink()) {
            throw new Error(
                `${label} must not contain a symbolic link: ${entryPath}.`,
            );
        }

        if (entry.isDirectory()) {
            assertTreeHasNoSymlinks(entryPath, label);
        }
    }
}

function stripKotlinCommentsAndRawStrings(sourceText) {
    let result = '';
    let state = 'code';
    let quote = '';

    for (let index = 0; index < sourceText.length; index++) {
        const character = sourceText[index];
        const next = sourceText[index + 1];
        const triple = sourceText.slice(index, index + 3);

        if (state === 'line-comment') {
            if (character === '\n') {
                result += '\n';
                state = 'code';
            } else {
                result += ' ';
            }

            continue;
        }

        if (state === 'block-comment') {
            if (character === '*' && next === '/') {
                result += '  ';
                index++;
                state = 'code';
            } else {
                result += character === '\n' ? '\n' : ' ';
            }

            continue;
        }

        if (state === 'raw-string') {
            if (triple === '"""') {
                result += '   ';
                index += 2;
                state = 'code';
            } else {
                result += character === '\n' ? '\n' : ' ';
            }

            continue;
        }

        if (state === 'string') {
            result += character;

            if (character === '\\' && next !== undefined) {
                result += next;
                index++;
            } else if (character === quote) {
                state = 'code';
            }

            continue;
        }

        if (character === '/' && next === '/') {
            result += '  ';
            index++;
            state = 'line-comment';
        } else if (character === '/' && next === '*') {
            result += '  ';
            index++;
            state = 'block-comment';
        } else if (triple === '"""') {
            result += '   ';
            index += 2;
            state = 'raw-string';
        } else {
            result += character;

            if (character === '"' || character === "'") {
                quote = character;
                state = 'string';
            }
        }
    }

    return result;
}

function readGradleApplicationId() {
    const buildConfiguration = stripKotlinCommentsAndRawStrings(
        readFileSync(androidBuildConfiguration, 'utf8'),
    );
    const matches = [
        ...buildConfiguration.matchAll(
            /^[\t ]*applicationId[\t ]*=[\t ]*"([^"\r\n]+)"[\t ]*$/gm,
        ),
    ];

    if (matches.length !== 1) {
        throw new Error(
            `Generated NativePHP build must contain exactly one active applicationId assignment; found ${matches.length}.`,
        );
    }

    return matches[0][1];
}

function readManifestApplicationLabel() {
    const manifest = readFileSync(androidManifest, 'utf8').replace(
        /<!--[\s\S]*?-->/g,
        '',
    );
    const applicationTags = [...manifest.matchAll(/<application\b[^>]*>/g)];

    if (applicationTags.length !== 1) {
        throw new Error(
            `Generated NativePHP manifest must contain exactly one active application element; found ${applicationTags.length}.`,
        );
    }

    const label = applicationTags[0][0].match(
        /\bandroid:label\s*=\s*(["'])(.*?)\1/,
    );

    if (label === null) {
        throw new Error(
            'Generated NativePHP application element must declare android:label.',
        );
    }

    return label[2];
}

function assertCanonicalIdentity() {
    const applicationId = readGradleApplicationId();

    if (applicationId !== canonicalAppId) {
        throw new Error(
            `Generated NativePHP application ID must be ${canonicalAppId}; found ${applicationId}.`,
        );
    }

    const applicationLabel = readManifestApplicationLabel();

    if (applicationLabel !== canonicalAppName) {
        throw new Error(
            `Generated NativePHP manifest label must be ${canonicalAppName}; found ${applicationLabel}.`,
        );
    }
}

function assertSafeInputs() {
    if (!existsSync(source)) {
        throw new Error(
            'Run npm run brand:build before applying native brand assets.',
        );
    }

    if (
        !existsSync(generatedRoot) ||
        !existsSync(destination) ||
        !existsSync(androidBuildConfiguration) ||
        !existsSync(androidManifest)
    ) {
        throw new Error(
            'Run php artisan native:install --force before applying native brand assets.',
        );
    }

    assertSafeDirectory(workspaceRoot, source, 'Native brand source');
    assertSafeDirectory(
        workspaceRoot,
        generatedRoot,
        'NativePHP generated root',
    );
    assertSafeDirectory(
        generatedRoot,
        destination,
        'NativePHP resource destination',
    );
    assertSafeFile(
        generatedRoot,
        androidBuildConfiguration,
        'NativePHP Android build configuration',
    );
    assertSafeFile(
        generatedRoot,
        androidManifest,
        'NativePHP Android manifest',
    );
    assertTreeHasNoSymlinks(source, 'Native brand source');
    assertTreeHasNoSymlinks(destination, 'NativePHP resource destination');
    assertCanonicalIdentity();
}

assertSafeInputs();

// Repeat containment and identity reads immediately before the copy boundary.
assertSafeInputs();
cpSync(source, destination, { recursive: true, force: true });
console.log(
    'Applied Sutelio adaptive, monochrome, and Android 12+ splash resources.',
);
