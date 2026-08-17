import { createHash, randomUUID } from 'node:crypto';
import {
    constants,
    copyFileSync,
    existsSync,
    lstatSync,
    mkdirSync,
    readFileSync,
    readdirSync,
    realpathSync,
    renameSync,
    rmSync,
    rmdirSync,
    writeFileSync,
} from 'node:fs';
import { dirname, isAbsolute, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const workspaceRoot = realpathSync(
    resolve(dirname(fileURLToPath(import.meta.url)), '..'),
);
const nativeRoot = resolve(workspaceRoot, 'nativephp');
const androidGeneratedRoot = resolve(nativeRoot, 'android');
const iosGeneratedRoot = resolve(nativeRoot, 'ios');
const androidResourceDestination = resolve(
    androidGeneratedRoot,
    'app/src/main/res',
);
const androidBrandSource = resolve(workspaceRoot, 'resources/brand/android');
const androidStoreSource = resolve(
    workspaceRoot,
    'resources/brand/sutelio-android-store-512.png',
);
const iosIconSource = resolve(workspaceRoot, 'public/icon.png');
const iosSplashSource = resolve(workspaceRoot, 'public/splash.png');
const iosDarkSplashSource = resolve(workspaceRoot, 'public/splash-dark.png');
const vendorAndroidRoot = resolve(
    workspaceRoot,
    'vendor/nativephp/mobile/resources/androidstudio',
);
const vendorIosRoot = resolve(
    workspaceRoot,
    'vendor/nativephp/mobile/resources/xcode',
);
const androidBuildConfiguration = resolve(
    androidGeneratedRoot,
    'app/build.gradle.kts',
);
const androidManifest = resolve(
    androidGeneratedRoot,
    'app/src/main/AndroidManifest.xml',
);
const iosProject = resolve(
    iosGeneratedRoot,
    'NativePHP.xcodeproj/project.pbxproj',
);
const iosInfoPlist = resolve(iosGeneratedRoot, 'NativePHP/Info.plist');
const iosSimulatorInfoPlist = resolve(
    iosGeneratedRoot,
    'NativePHP-simulator-Info.plist',
);
const canonicalAppId = 'com.goleaf.sutelio';
const canonicalAppName = 'Sutelio';
const canonicalScheme = 'sutelio';
const templateAppId = 'REPLACE_APP_ID';
const templateIosAppId = 'com.nativephp.app';
const templateAppName = 'NativePHP';
const templateScheme = 'nativephp';
const fixedAndroidNamespace = 'com.nativephp.mobile';
const requestInspectorDependency =
    'implementation("com.github.acsbendi:Android-Request-Inspector-WebView:1.0.3")';
const androidSensitiveSourceDefinitions = [
    {
        relativePath:
            'app/src/main/java/com/nativephp/mobile/network/WebViewManager.kt',
        label: 'NativePHP Android WebView manager',
        replacements: [
            [
                'import com.acsbendi.requestinspectorwebview.RequestInspectorWebViewClient',
                '',
            ],
            [
                'WebView.setWebContentsDebuggingEnabled(true)',
                'WebView.setWebContentsDebuggingEnabled(\n            context.applicationInfo.flags and android.content.pm.ApplicationInfo.FLAG_DEBUGGABLE != 0\n        )',
            ],
            [
                '            private val requestInspector = RequestInspectorWebViewClient(webView)\n',
                '',
            ],
            [
                '                Log.d(TAG, "🔄 Intercepting $method request to $url")',
                '                Log.d(TAG, "🔄 Intercepting $method request")',
            ],
            [
                [
                    '                request.requestHeaders.forEach { (key, value) ->',
                    '                    Log.d("$TAG-Headers", "📋 $key: $value")',
                    '                }',
                    '',
                ].join('\n'),
                '',
            ],
            [
                '                val inspectorResponse = requestInspector.shouldInterceptRequest(view, request)\n',
                '',
            ],
            [
                '                        inspectorResponse',
                '                        null',
            ],
            [
                '        Log.d("$TAG-JS", "📦 POST data captured (fetch/XHR) for: $url reqId=$requestId (length=${data.length})")',
                '        Log.d("$TAG-JS", "📦 POST data captured (fetch/XHR, length=${data.length})")',
            ],
            [
                '        Log.d("$TAG-JS", "📦 POST data captured (form) for: $url path=$path (length=${data.length})")',
                '        Log.d("$TAG-JS", "📦 POST data captured (form, length=${data.length})")',
            ],
            [
                '        Log.d("$TAG-CSRF", "🔑 JS provided token: $token")',
                '        Log.d("$TAG-CSRF", "🔑 JS provided a CSRF token")',
            ],
            [
                '                    Log.d("$TAG-CSRF", "🔑 Extracted token from POST data: $token")',
                '                    Log.d("$TAG-CSRF", "🔑 Extracted a CSRF token from POST data")',
            ],
            [
                '                        Log.d("$TAG-CSRF", "🔑 Extracted token from form data: $token")',
                '                        Log.d("$TAG-CSRF", "🔑 Extracted a CSRF token from form data")',
            ],
        ],
    },
    {
        relativePath:
            'app/src/main/java/com/nativephp/mobile/network/PHPWebViewClient.kt',
        label: 'NativePHP Android PHP WebView client',
        replacements: [
            [
                '                Log.d(TAG, "RESPONSE HEADERS: ${responseHeaders}")',
                '                Log.d(TAG, "PHP asset response received")',
            ],
            [
                '        Log.d(TAG, "📤 Final request headers: $headers")',
                '        Log.d(TAG, "📤 Prepared ${headers.size} request headers")',
            ],
            [
                '                Log.d(TAG, "🍪 Setting cookie from response: $value")',
                '                Log.d(TAG, "🍪 Applying response cookie")',
            ],
            [
                '               Log.d(TAG, "🍪 Stored cookie from Set-Cookie header: $cookie")',
                '               Log.d(TAG, "🍪 Stored response cookie")',
            ],
        ],
    },
    {
        relativePath:
            'app/src/main/java/com/nativephp/mobile/bridge/PHPBridge.kt',
        label: 'NativePHP Android PHP bridge',
        replacements: [
            [
                '        Log.d(TAG, "Response first 200 chars: ${response.take(200)}")',
                '        Log.d(TAG, "PHP response received")',
            ],
            [
                '                Log.d(TAG, "Cookie line: $cookieLine")',
                '                Log.d(TAG, "Response cookie found")',
            ],
            [
                '                    Log.d(TAG, "Stored cookie: $cookieValue")',
                '                    Log.d(TAG, "Stored response cookie")',
            ],
        ],
    },
    {
        relativePath:
            'app/src/main/java/com/nativephp/mobile/security/LaravelCookieStore.kt',
        label: 'NativePHP Android Laravel cookie store',
        replacements: [
            [
                '            Log.d(TAG, "🍪 Stored cookie: $name=$value")',
                '            Log.d(TAG, "🍪 Stored cookie: $name")',
            ],
            [
                '        Log.d(TAG, "📤 Cookie header: $cookieString")',
                '        Log.d(TAG, "📤 Cookie header prepared")',
            ],
            [
                [
                    '        Log.d(TAG, "📦 Stored cookies:")',
                    '        cookies.forEach { (key, value) ->',
                    '            Log.d(TAG, "   → $key = $value")',
                    '        }',
                ].join('\n'),
                '        Log.d(TAG, "📦 Stored cookie count: ${cookies.size}")',
            ],
        ],
    },
    {
        relativePath:
            'app/src/main/java/com/nativephp/mobile/security/LaravelSecurity.kt',
        label: 'NativePHP Android Laravel security store',
        replacements: [
            [
                '                    Log.d(TAG, "🔑 Extracted CSRF token from JSON: $csrfToken")',
                '                    Log.d(TAG, "🔑 Extracted a CSRF token from JSON")',
            ],
            [
                '                    Log.d(TAG, "🔑 Extracted CSRF token from form: $csrfToken")',
                '                    Log.d(TAG, "🔑 Extracted a CSRF token from form")',
            ],
            [
                '        Log.d(TAG, "📥 Stored CSRF token manually: $token")',
                '        Log.d(TAG, "📥 Stored a CSRF token manually")',
            ],
        ],
    },
];
const canonicalDeepLinkBlock = `            <!-- NATIVEPHP-DEEPLINKS-START -->
            <!-- Deep Links (Custom Scheme) -->
            <intent-filter>
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="${canonicalScheme}" />
            </intent-filter>
            <!-- NATIVEPHP-DEEPLINKS-END -->`;
const androidAssetPaths = [
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

        if (!existsSync(currentPath)) {
            continue;
        }

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

function assertSafeOutputPath(expectedRoot, candidate, label) {
    assertNoSymlinkComponents(expectedRoot, candidate, label);

    let existingAncestor = candidate;

    while (!existsSync(existingAncestor)) {
        existingAncestor = dirname(existingAncestor);
    }

    assertRealPathContained(expectedRoot, existingAncestor, label);

    if (existsSync(candidate) && !lstatSync(candidate).isFile()) {
        throw new Error(`${label} must be a regular file when it exists.`);
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

function assertNonemptyFile(expectedRoot, candidate, label) {
    assertSafeFile(expectedRoot, candidate, label);

    const contents = readFileSync(candidate);

    if (contents.length === 0) {
        throw new Error(`${label} must not be empty.`);
    }

    return contents;
}

function sha256(contents) {
    return createHash('sha256').update(contents).digest('hex');
}

function assertPng(contents, expectedWidth, expectedHeight, label) {
    const signature = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]);

    if (
        contents.length < 24 ||
        !contents.subarray(0, signature.length).equals(signature)
    ) {
        throw new Error(`${label} must be a nonempty PNG image.`);
    }

    const width = contents.readUInt32BE(16);
    const height = contents.readUInt32BE(20);

    if (width !== expectedWidth || height !== expectedHeight) {
        throw new Error(
            `${label} must be ${expectedWidth}x${expectedHeight}; found ${width}x${height}.`,
        );
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

function maskXmlComments(sourceText) {
    return sourceText.replace(/<!--[\s\S]*?-->/g, (comment) =>
        comment.replace(/[^\r\n]/g, ' '),
    );
}

function readSingleMatch(sourceText, pattern, label) {
    const matches = [...sourceText.matchAll(pattern)];

    if (matches.length !== 1) {
        throw new Error(
            `${label} must occur exactly once; found ${matches.length}.`,
        );
    }

    return matches[0][1];
}

function classifyValue(value, templateValue, canonicalValue, label) {
    if (value === templateValue) {
        return 'template';
    }

    if (value === canonicalValue) {
        return 'canonical';
    }

    throw new Error(
        `${label} must be either ${templateValue} or ${canonicalValue}; found ${value}.`,
    );
}

function inspectGradle(sourceText) {
    const activeSource = stripKotlinCommentsAndRawStrings(sourceText);
    const namespace = readSingleMatch(
        activeSource,
        /^[\t ]*namespace[\t ]*=[\t ]*"([^"\r\n]+)"[\t ]*$/gm,
        'Generated NativePHP Android namespace assignment',
    );
    const applicationId = readSingleMatch(
        activeSource,
        /^[\t ]*applicationId[\t ]*=[\t ]*"([^"\r\n]+)"[\t ]*$/gm,
        'Generated NativePHP Android applicationId assignment',
    );

    if (namespace !== fixedAndroidNamespace) {
        throw new Error(
            `Generated NativePHP Android namespace must remain ${fixedAndroidNamespace}; found ${namespace}.`,
        );
    }

    const applicationIdState = classifyValue(
        applicationId,
        templateAppId,
        canonicalAppId,
        'Generated NativePHP Android applicationId',
    );
    const inspectorArtifactOccurrences = countOccurrences(
        activeSource,
        'Android-Request-Inspector-WebView',
    );
    const inspectorDependencyOccurrences = countOccurrences(
        activeSource,
        requestInspectorDependency,
    );

    if (
        inspectorArtifactOccurrences > 1 ||
        inspectorArtifactOccurrences !== inspectorDependencyOccurrences
    ) {
        throw new Error(
            'Generated NativePHP Android request-inspector dependency must be the exact NativePHP 4.2 declaration or absent.',
        );
    }

    return [
        applicationIdState,
        inspectorDependencyOccurrences === 1 ? 'template' : 'canonical',
    ];
}

function countOccurrences(sourceText, needle) {
    return sourceText.split(needle).length - 1;
}

function inspectManifest(sourceText) {
    const activeSource = maskXmlComments(sourceText);
    const applicationTags = [...activeSource.matchAll(/<application\b[^>]*>/g)];

    if (applicationTags.length !== 1) {
        throw new Error(
            `Generated NativePHP Android manifest must contain exactly one active application element; found ${applicationTags.length}.`,
        );
    }

    const labelMatch = applicationTags[0][0].match(
        /\bandroid:label\s*=\s*(["'])(.*?)\1/,
    );

    if (labelMatch === null) {
        throw new Error(
            'Generated NativePHP Android application element must declare android:label.',
        );
    }

    const labelState = classifyValue(
        labelMatch[2],
        templateAppName,
        canonicalAppName,
        'Generated NativePHP Android application label',
    );
    const activities = [
        ...activeSource.matchAll(
            /<activity\b[^>]*android:name\s*=\s*(["'])\.ui\.MainActivity\1[^>]*>[\s\S]*?<\/activity>/g,
        ),
    ];

    if (activities.length !== 1) {
        throw new Error(
            `Generated NativePHP Android manifest must contain exactly one active MainActivity; found ${activities.length}.`,
        );
    }

    const activeActivity = activities[0][0];
    const activityStart = activities[0].index;
    const rawActivity = sourceText.slice(
        activityStart,
        activityStart + activeActivity.length,
    );
    const schemes = [
        ...activeActivity.matchAll(
            /<data\b[^>]*\bandroid:scheme\s*=\s*(["'])(.*?)\1[^>]*\/?\s*>/g,
        ),
    ].map((match) => match[2]);
    const startMarkers = countOccurrences(
        rawActivity,
        '<!-- NATIVEPHP-DEEPLINKS-START -->',
    );
    const endMarkers = countOccurrences(
        rawActivity,
        '<!-- NATIVEPHP-DEEPLINKS-END -->',
    );
    let deepLinkState;

    if (schemes.length === 0 && startMarkers === 0 && endMarkers === 0) {
        deepLinkState = 'template';
    } else if (
        schemes.length === 1 &&
        schemes[0] === canonicalScheme &&
        startMarkers === 1 &&
        endMarkers === 1 &&
        rawActivity.includes(canonicalDeepLinkBlock) &&
        !activeActivity.includes('android:host')
    ) {
        deepLinkState = 'canonical';
    } else {
        throw new Error(
            'Generated NativePHP Android deep-link state must be the empty NativePHP template or the exact hostless Sutelio custom scheme.',
        );
    }

    return [labelState, deepLinkState];
}

function inspectIosProject(sourceText) {
    const activeSource = stripKotlinCommentsAndRawStrings(sourceText);
    const bundleIdentifiers = [
        ...activeSource.matchAll(
            /^[\t ]*PRODUCT_BUNDLE_IDENTIFIER[\t ]*=[\t ]*([^;\r\n]+);/gm,
        ),
    ].map((match) => match[1].trim());
    const applicationBundleIdentifiers = bundleIdentifiers.filter(
        (value) =>
            value !== 'com.nativephp.NativePHPTests' &&
            value !== 'com.nativephp.NativePHPUITests',
    );

    if (
        bundleIdentifiers.filter(
            (value) => value === 'com.nativephp.NativePHPTests',
        ).length !== 2 ||
        bundleIdentifiers.filter(
            (value) => value === 'com.nativephp.NativePHPUITests',
        ).length !== 2 ||
        applicationBundleIdentifiers.length !== 4
    ) {
        throw new Error(
            'Generated NativePHP Xcode project must retain four application and four vendor test bundle identifiers.',
        );
    }

    const applicationStates = applicationBundleIdentifiers.map((value) =>
        classifyValue(
            value,
            templateIosAppId,
            canonicalAppId,
            'Generated NativePHP iOS application bundle identifier',
        ),
    );
    const displayNames = [
        ...activeSource.matchAll(
            /^[\t ]*INFOPLIST_KEY_CFBundleDisplayName[\t ]*=[\t ]*([^;\r\n]+);/gm,
        ),
    ].map((match) => match[1].trim());

    if (displayNames.length !== 4) {
        throw new Error(
            `Generated NativePHP Xcode project must contain four application display names; found ${displayNames.length}.`,
        );
    }

    const displayStates = displayNames.map((value) =>
        classifyValue(
            value,
            templateAppName,
            `"${canonicalAppName}"`,
            'Generated NativePHP iOS display name',
        ),
    );

    return [...applicationStates, ...displayStates];
}

function inspectInfoPlist(sourceText, label) {
    const activeSource = maskXmlComments(sourceText);
    const urlName = readSingleMatch(
        activeSource,
        /<key>\s*CFBundleURLName\s*<\/key>\s*<string>([^<]*)<\/string>/g,
        `${label} CFBundleURLName`,
    );
    const scheme = readSingleMatch(
        activeSource,
        /<key>\s*CFBundleURLSchemes\s*<\/key>\s*<array>\s*<string>([^<]*)<\/string>\s*<\/array>/g,
        `${label} CFBundleURLSchemes`,
    );

    return [
        classifyValue(
            urlName,
            templateIosAppId,
            canonicalAppId,
            `${label} URL name`,
        ),
        classifyValue(
            scheme,
            templateScheme,
            canonicalScheme,
            `${label} URL scheme`,
        ),
    ];
}

function canonicalizeGradle(templateText) {
    const identifiedTemplate = templateText.replace(
        /^([\t ]*applicationId[\t ]*=[\t ]*)"REPLACE_APP_ID"([\t ]*)$/m,
        `$1"${canonicalAppId}"$2`,
    );

    return replaceExactly(
        identifiedTemplate,
        `    ${requestInspectorDependency}\n`,
        '',
        'NativePHP Android request-inspector dependency',
    );
}

function canonicalizeManifest(templateText) {
    const namedManifest = templateText.replace(
        /android:label="NativePHP"/,
        `android:label="${canonicalAppName}"`,
    );
    const activityPattern =
        /(<activity[^>]*android:name="[^"]*MainActivity"[^>]*>)(.*?)(<\/activity>)/s;

    if (!activityPattern.test(namedManifest)) {
        throw new Error(
            'NativePHP 4.2 Android template MainActivity could not be canonicalized.',
        );
    }

    return namedManifest.replace(
        activityPattern,
        (_, opening, body, closing) =>
            `${opening}${body}\n${canonicalDeepLinkBlock}\n        ${closing}`,
    );
}

function canonicalizeIosProject(templateText) {
    return templateText
        .replace(
            /^([\t ]*INFOPLIST_KEY_CFBundleDisplayName[\t ]*=[\t ]*)[^;\r\n]+;/gm,
            `$1"${canonicalAppName}";`,
        )
        .replace(
            /^([\t ]*PRODUCT_BUNDLE_IDENTIFIER[\t ]*=[\t ]*)([^;\r\n]+);/gm,
            (line, prefix, identifier) => {
                const value = identifier.trim();

                if (
                    value === 'com.nativephp.NativePHPTests' ||
                    value === 'com.nativephp.NativePHPUITests'
                ) {
                    return line;
                }

                return `${prefix}${canonicalAppId};`;
            },
        );
}

function canonicalizeInfoPlist(templateText) {
    return templateText
        .replace(
            /(<key>\s*CFBundleURLName\s*<\/key>\s*<string>)[^<]*(<\/string>)/,
            `$1${canonicalAppId}$2`,
        )
        .replace(
            /(<key>\s*CFBundleURLSchemes\s*<\/key>\s*<array>\s*<string>)[^<]*(<\/string>\s*<\/array>)/,
            `$1${canonicalScheme}$2`,
        );
}

function replaceExactly(sourceText, from, to, label) {
    const occurrences = countOccurrences(sourceText, from);

    if (occurrences !== 1) {
        throw new Error(
            `${label} source patch must match exactly once; found ${occurrences}.`,
        );
    }

    return sourceText.replace(from, to);
}

function canonicalizeSensitiveAndroidSource(templateText, replacements, label) {
    return replacements.reduce(
        (contents, [from, to], index) =>
            replaceExactly(
                contents,
                from,
                to,
                `${label} replacement ${index + 1}`,
            ),
        templateText,
    );
}

function buildSensitiveAndroidSourceEntry(definition) {
    const templatePath = resolve(vendorAndroidRoot, definition.relativePath);
    const destination = resolve(androidGeneratedRoot, definition.relativePath);
    const templateContents = assertNonemptyFile(
        workspaceRoot,
        templatePath,
        `${definition.label} template`,
    ).toString('utf8');
    const currentContents = assertNonemptyFile(
        nativeRoot,
        destination,
        definition.label,
    ).toString('utf8');
    const canonicalContents = canonicalizeSensitiveAndroidSource(
        templateContents,
        definition.replacements,
        definition.label,
    );
    const currentHash = sha256(currentContents);
    const templateHash = sha256(templateContents);
    const canonicalHash = sha256(canonicalContents);

    if (currentHash !== templateHash && currentHash !== canonicalHash) {
        throw new Error(
            `${definition.label} must be the exact NativePHP 4.2 template or the exact Sutelio security-hardened source.`,
        );
    }

    return {
        destination,
        contents: Buffer.from(canonicalContents, 'utf8'),
    };
}

function assertIdentityFieldsState(inspectedState, label) {
    const states = Array.isArray(inspectedState)
        ? inspectedState
        : [inspectedState];

    if (states.every((state) => state === 'template')) {
        return 'template';
    }

    if (states.every((state) => state === 'canonical')) {
        return 'canonical';
    }

    throw new Error(
        `${label} identity fields must be entirely NativePHP template or entirely canonical Sutelio values.`,
    );
}

function assertUniformIdentityState(states) {
    if (states.every((state) => state === 'template')) {
        return 'template';
    }

    if (states.every((state) => state === 'canonical')) {
        return 'canonical';
    }

    throw new Error(
        'Generated NativePHP identity must be entirely fresh-template or entirely canonical; mixed states are unsafe.',
    );
}

function assertCatalogReferences() {
    const iconCatalogPath = resolve(
        iosGeneratedRoot,
        'NativePHP/Assets.xcassets/AppIcon.appiconset/Contents.json',
    );
    const launchCatalogPath = resolve(
        iosGeneratedRoot,
        'NativePHP/Assets.xcassets/LaunchImage.imageset/Contents.json',
    );
    const iconCatalog = JSON.parse(
        assertNonemptyFile(
            iosGeneratedRoot,
            iconCatalogPath,
            'NativePHP iOS AppIcon catalog',
        ).toString('utf8'),
    );
    const launchCatalog = JSON.parse(
        assertNonemptyFile(
            iosGeneratedRoot,
            launchCatalogPath,
            'NativePHP iOS launch-image catalog',
        ).toString('utf8'),
    );

    if (
        iconCatalog.images?.length !== 1 ||
        iconCatalog.images[0]?.filename !== 'icon.png' ||
        iconCatalog.images[0]?.size !== '1024x1024'
    ) {
        throw new Error(
            'NativePHP iOS AppIcon catalog must reference the universal 1024x1024 icon.png.',
        );
    }

    const launchFilenames = launchCatalog.images?.map(
        (image) => image.filename,
    );

    if (
        JSON.stringify(launchFilenames) !==
            JSON.stringify(['splash.png', 'splash-dark.png']) ||
        launchCatalog.images[1]?.appearances?.[0]?.appearance !==
            'luminosity' ||
        launchCatalog.images[1]?.appearances?.[0]?.value !== 'dark'
    ) {
        throw new Error(
            'NativePHP iOS launch-image catalog must reference light and dark Sutelio splash filenames.',
        );
    }
}

function buildIdentityEntry(
    destination,
    templatePath,
    canonicalize,
    inspect,
    label,
) {
    const templateContents = assertNonemptyFile(
        workspaceRoot,
        templatePath,
        `${label} template`,
    ).toString('utf8');
    const currentContents = assertNonemptyFile(
        nativeRoot,
        destination,
        label,
    ).toString('utf8');
    const templateState = assertIdentityFieldsState(
        inspect(templateContents),
        `${label} template`,
    );
    const canonicalTemplateContents = canonicalize(templateContents);
    const canonicalTemplateState = assertIdentityFieldsState(
        inspect(canonicalTemplateContents),
        `${label} canonical template`,
    );
    const currentState = assertIdentityFieldsState(
        inspect(currentContents),
        label,
    );
    const canonicalContents =
        currentState === 'template'
            ? canonicalize(currentContents)
            : currentContents;
    const canonicalState = assertIdentityFieldsState(
        inspect(canonicalContents),
        `${label} canonical output`,
    );

    if (
        templateState !== 'template' ||
        canonicalTemplateState !== 'canonical' ||
        canonicalState !== 'canonical'
    ) {
        throw new Error(`${label} canonicalization contract is invalid.`);
    }

    return {
        destination,
        contents: Buffer.from(canonicalContents, 'utf8'),
        state: currentState,
    };
}

function buildAssetEntry({
    source,
    destination,
    template,
    label,
    dimensions = null,
}) {
    const contents = assertNonemptyFile(
        workspaceRoot,
        source,
        `${label} source`,
    );

    if (dimensions !== null) {
        assertPng(contents, dimensions[0], dimensions[1], `${label} source`);
    }

    assertSafeOutputPath(nativeRoot, destination, label);

    const canonicalHash = sha256(contents);
    const destinationExists = existsSync(destination);
    const destinationContents = destinationExists
        ? assertNonemptyFile(nativeRoot, destination, label)
        : null;
    const destinationHash = destinationContents
        ? sha256(destinationContents)
        : null;
    const templateExists = template !== null && existsSync(template);
    const templateContents = templateExists
        ? assertNonemptyFile(workspaceRoot, template, `${label} template`)
        : null;
    const templateHash = templateContents ? sha256(templateContents) : null;
    const supportsTemplate = templateExists
        ? destinationHash === templateHash
        : !destinationExists;
    const supportsCanonical = destinationHash === canonicalHash;

    if (!supportsTemplate && !supportsCanonical) {
        throw new Error(
            `${label} must be the exact NativePHP 4.2 template asset or the exact canonical Sutelio asset.`,
        );
    }

    return {
        destination,
        contents,
        supportsTemplate,
        supportsCanonical,
    };
}

function assertRequiredRoots() {
    const requiredDirectories = [
        [nativeRoot, workspaceRoot, 'NativePHP generated root'],
        [androidGeneratedRoot, nativeRoot, 'NativePHP Android generated root'],
        [iosGeneratedRoot, nativeRoot, 'NativePHP iOS generated root'],
        [
            androidResourceDestination,
            androidGeneratedRoot,
            'NativePHP Android resource destination',
        ],
        [androidBrandSource, workspaceRoot, 'Native brand Android source'],
        [vendorAndroidRoot, workspaceRoot, 'NativePHP Android template root'],
        [vendorIosRoot, workspaceRoot, 'NativePHP iOS template root'],
    ];

    for (const [directory, expectedRoot, label] of requiredDirectories) {
        if (!existsSync(directory)) {
            throw new Error(
                'Run php artisan native:install --force --no-interaction and npm run brand:build before applying native branding.',
            );
        }

        assertSafeDirectory(expectedRoot, directory, label);
    }

    assertTreeHasNoSymlinks(androidBrandSource, 'Native brand Android source');
    assertTreeHasNoSymlinks(
        androidResourceDestination,
        'NativePHP Android resource destination',
    );
}

function buildPublicationPlan() {
    assertRequiredRoots();

    const identityEntries = [
        buildIdentityEntry(
            androidBuildConfiguration,
            resolve(vendorAndroidRoot, 'app/build.gradle.kts'),
            canonicalizeGradle,
            inspectGradle,
            'NativePHP Android build configuration',
        ),
        buildIdentityEntry(
            androidManifest,
            resolve(vendorAndroidRoot, 'app/src/main/AndroidManifest.xml'),
            canonicalizeManifest,
            inspectManifest,
            'NativePHP Android manifest',
        ),
        buildIdentityEntry(
            iosProject,
            resolve(vendorIosRoot, 'NativePHP.xcodeproj/project.pbxproj'),
            canonicalizeIosProject,
            inspectIosProject,
            'NativePHP Xcode project',
        ),
        buildIdentityEntry(
            iosInfoPlist,
            resolve(vendorIosRoot, 'NativePHP/Info.plist'),
            canonicalizeInfoPlist,
            (contents) =>
                inspectInfoPlist(contents, 'NativePHP iOS Info.plist'),
            'NativePHP iOS Info.plist',
        ),
        buildIdentityEntry(
            iosSimulatorInfoPlist,
            resolve(vendorIosRoot, 'NativePHP-simulator-Info.plist'),
            canonicalizeInfoPlist,
            (contents) =>
                inspectInfoPlist(
                    contents,
                    'NativePHP iOS simulator Info.plist',
                ),
            'NativePHP iOS simulator Info.plist',
        ),
    ];
    const identityState = assertUniformIdentityState(
        identityEntries.map((entry) => entry.state),
    );
    const sensitiveSourceEntries = androidSensitiveSourceDefinitions.map(
        buildSensitiveAndroidSourceEntry,
    );

    assertCatalogReferences();

    const assetEntries = [
        buildAssetEntry({
            source: androidStoreSource,
            destination: resolve(
                androidGeneratedRoot,
                'app/src/main/ic_launcher-playstore.png',
            ),
            template: resolve(
                vendorAndroidRoot,
                'app/src/main/ic_launcher-playstore.png',
            ),
            label: 'NativePHP Android Play Store icon',
            dimensions: [512, 512],
        }),
        buildAssetEntry({
            source: iosIconSource,
            destination: resolve(
                iosGeneratedRoot,
                'NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png',
            ),
            template: resolve(
                vendorIosRoot,
                'NativePHP/Assets.xcassets/AppIcon.appiconset/icon.png',
            ),
            label: 'NativePHP iOS AppIcon',
            dimensions: [1024, 1024],
        }),
        buildAssetEntry({
            source: iosSplashSource,
            destination: resolve(
                iosGeneratedRoot,
                'NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png',
            ),
            template: resolve(
                vendorIosRoot,
                'NativePHP/Assets.xcassets/LaunchImage.imageset/splash.png',
            ),
            label: 'NativePHP iOS light splash',
            dimensions: [1080, 1920],
        }),
        buildAssetEntry({
            source: iosDarkSplashSource,
            destination: resolve(
                iosGeneratedRoot,
                'NativePHP/Assets.xcassets/LaunchImage.imageset/splash-dark.png',
            ),
            template: resolve(
                vendorIosRoot,
                'NativePHP/Assets.xcassets/LaunchImage.imageset/splash-dark.png',
            ),
            label: 'NativePHP iOS dark splash',
            dimensions: [1080, 1920],
        }),
        ...androidAssetPaths.map((assetPath) =>
            buildAssetEntry({
                source: resolve(androidBrandSource, assetPath),
                destination: resolve(androidResourceDestination, assetPath),
                template: resolve(
                    vendorAndroidRoot,
                    'app/src/main/res',
                    assetPath,
                ),
                label: `NativePHP Android resource ${assetPath}`,
            }),
        ),
    ];

    for (const entry of assetEntries) {
        const supportsIdentityState =
            identityState === 'template'
                ? entry.supportsTemplate
                : entry.supportsCanonical;

        if (!supportsIdentityState) {
            throw new Error(
                'Generated NativePHP identity and assets must be entirely fresh-template or entirely canonical; mixed states are unsafe.',
            );
        }
    }

    return [...identityEntries, ...sensitiveSourceEntries, ...assetEntries].map(
        (entry) => ({
            destination: entry.destination,
            contents: entry.contents,
            hash: sha256(entry.contents),
        }),
    );
}

function assertPlansMatch(firstPlan, secondPlan) {
    if (firstPlan.length !== secondPlan.length) {
        throw new Error(
            'Native brand publication plan changed during preflight.',
        );
    }

    for (let index = 0; index < firstPlan.length; index++) {
        if (
            firstPlan[index].destination !== secondPlan[index].destination ||
            firstPlan[index].hash !== secondPlan[index].hash
        ) {
            throw new Error(
                'Native brand publication plan changed during preflight.',
            );
        }
    }
}

function ensurePublicationDirectory(directory, createdDirectories) {
    const missingDirectories = [];
    let currentDirectory = directory;

    while (!existsSync(currentDirectory)) {
        missingDirectories.push(currentDirectory);
        currentDirectory = dirname(currentDirectory);
    }

    mkdirSync(directory, { recursive: true });
    createdDirectories.push(...missingDirectories);
}

function publishPlan(plan) {
    const token = randomUUID();
    const stageRoot = resolve(nativeRoot, `.sutelio-native-stage-${token}`);
    const stagedEntries = [];
    const publishedEntries = [];
    const createdDirectories = [];

    mkdirSync(stageRoot);

    try {
        for (const [index, entry] of plan.entries()) {
            const stagePath = resolve(stageRoot, `file-${index}`);
            const backupPath = resolve(stageRoot, `backup-${index}`);
            writeFileSync(stagePath, entry.contents, { flag: 'wx' });

            if (sha256(readFileSync(stagePath)) !== entry.hash) {
                throw new Error(
                    `Staged native brand file failed hash verification: ${entry.destination}.`,
                );
            }

            stagedEntries.push({ ...entry, stagePath, backupPath });
        }

        for (const entry of stagedEntries) {
            ensurePublicationDirectory(
                dirname(entry.destination),
                createdDirectories,
            );

            if (existsSync(entry.destination)) {
                copyFileSync(
                    entry.destination,
                    entry.backupPath,
                    constants.COPYFILE_EXCL,
                );
            }

            renameSync(entry.stagePath, entry.destination);
            publishedEntries.push(entry);
        }
    } catch (error) {
        for (const entry of publishedEntries.reverse()) {
            rmSync(entry.destination, { force: true });

            if (existsSync(entry.backupPath)) {
                renameSync(entry.backupPath, entry.destination);
            }
        }

        for (const directory of createdDirectories) {
            if (existsSync(directory)) {
                try {
                    rmdirSync(directory);
                } catch {
                    // A nonempty directory was not created solely by this publication.
                }
            }
        }

        rmSync(stageRoot, { recursive: true, force: true });

        throw error;
    }

    rmSync(stageRoot, { recursive: true, force: true });
}

const publicationPlan = buildPublicationPlan();
const confirmedPublicationPlan = buildPublicationPlan();
assertPlansMatch(publicationPlan, confirmedPublicationPlan);
publishPlan(confirmedPublicationPlan);

console.log(
    'Applied canonical Sutelio identity and native assets for Android and iOS.',
);
