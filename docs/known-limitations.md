# Known Limitations

Only external/environmental blockers and explicitly accepted platform data boundaries belong here.

## Previous Mobile Package Sandbox

- Affected requirement: `sys-brand-001`.
- Evidence: Android and iOS isolate private application storage by package/bundle identity; Sutelio uses `com.goleaf.sutelio`, which is intentionally a different sandbox from the preceding installed product identity.
- Impact: a clean Sutelio installation cannot automatically read the preceding package's private SQLite database, and this repository currently claims no automatic cross-package migration. The completed emulator and physical Samsung gates validate only the new Sutelio sandbox; deleting the preceding installed package before an explicit export/migration would make its private data unavailable.
- Resolution trigger: retain the preceding installation/data unless the user deliberately accepts a clean start, or approve and implement a separately specified, authenticated export/import or platform migration workflow before removal.

## NativePHP Runtime Documentation Drift

- Affected requirement: `sys-runtime-001`.
- Evidence: NativePHP Mobile v4 official documentation still states that its mobile bundle uses PHP 8.4, while `native:install` on 2026-08-17 produced a tracked PHP 8.5.9 runtime and the resulting Android APK booted successfully on that engine.
- Impact: the current web and generated mobile runtimes are both PHP 8.5, but the project keeps the conservative `>=8.4 <8.6` Composer envelope rather than treating undocumented runtime behavior as a PHP 8.5-only support guarantee.
- Resolution trigger: after NativePHP's official documentation/package constraints consistently declare PHP 8.5 and a fresh web/mobile verification passes, reconsider the Composer floor and PHP 8.5-only syntax.

## Coverage Driver

- Affected requirement: `test-coverage-001`.
- Evidence: `herd coverage --ri xdebug` and `herd debug --ri xdebug` report that Xdebug is not present. The Task 9 `php artisan test --coverage --compact` attempt exited 1 before running tests with `Code coverage driver not available. Did you install Xdebug or PCOV?`.
- Impact: no truthful application coverage percentage can be reported from this workstation. The fresh documentation-cleanup sequential and supported parallel behavioral suites each passed 967 tests / 40,446 assertions, but behavioral results are not substituted for measured coverage.
- Resolution trigger: install a PHP 8.5-compatible Xdebug or PCOV extension in Herd, rerun the coverage command, review uncovered critical branches, and record the measured result.

## Store Signing Environment

- Affected requirement: `ops-deployment-001`.
- Evidence: Herd runs PHP 8.5.8 and Android verification produced a debug-signed APK; production signing credentials and an Apple team are intentionally absent.
- Impact: repository/runtime compatibility, emulator installation, and physical Android 10 debug installation are verified, but this workstation cannot claim production-signed Android/iOS store artifacts or signed release-device coverage.
- Resolution trigger: provide protected signing/provisioning credentials through the release environment and perform signed real-device/store builds without committing secrets.

## Apple Native Build Toolchain

- Affected requirements: `sys-runtime-001`, `sys-user-003`.
- Evidence: `DB_DATABASE=:memory: php artisan native:build --simulated --no-tty --no-interaction` prepared the 134,365,209-byte iOS Laravel/WebView archive, configured the generated Xcode project, installed CocoaPods, and then stopped at `tool 'xcodebuild' requires Xcode` because `xcode-select -p` points to `/Library/Developer/CommandLineTools`; `xcrun simctl` is likewise unavailable.
- Impact: the shared responsive language implementation, iOS-mode Vite build, generated iOS project, and packaged runtime contents are verified on this workstation, but an iPhone/iPad simulator binary and runtime launch cannot be claimed here. The Android debug APK and desktop/phone/tablet browser matrix provide executable mobile-layout evidence without substituting for the missing Apple release gate.
- Resolution trigger: install full Xcode, select its developer directory, create or boot an available iPhone/iPad simulator, rerun the NativePHP simulated/device build, and repeat the first-run plus persisted language workflow before an Apple release.

Other entries may be added only with an exact failing command, affected requirement ID, and external dependency or unavailable environment. Difficult unfinished implementation is not a limitation.
