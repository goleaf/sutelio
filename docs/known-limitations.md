# Known Limitations

Only external or environmental blockers belong here.

## NativePHP Runtime Documentation Drift

- Affected requirement: `sys-runtime-001`.
- Evidence: NativePHP Mobile v4 official documentation still states that its mobile bundle uses PHP 8.4, while `native:install` on 2026-08-17 produced a tracked PHP 8.5.9 runtime and the resulting Android APK booted successfully on that engine.
- Impact: the current web and generated mobile runtimes are both PHP 8.5, but the project keeps the conservative `>=8.4 <8.6` Composer envelope rather than treating undocumented runtime behavior as a PHP 8.5-only support guarantee.
- Resolution trigger: after NativePHP's official documentation/package constraints consistently declare PHP 8.5 and a fresh web/mobile verification passes, reconsider the Composer floor and PHP 8.5-only syntax.

## Coverage Driver

- Affected requirement: `test-coverage-001`.
- Evidence: `herd coverage --ri xdebug` and `herd debug --ri xdebug` report that Xdebug is not present; `herd php artisan test --coverage --min=0 --compact` exits with `Code coverage driver not available. Did you install Xdebug or PCOV?`.
- Impact: no truthful application coverage percentage can be reported from this workstation. The complete behavioral suite still passes 763 tests / 16,291 assertions sequentially and in parallel.
- Resolution trigger: install a PHP 8.5-compatible Xdebug or PCOV extension in Herd, rerun the coverage command, review uncovered critical branches, and record the measured result.

## Store Signing Environment

- Affected requirement: `ops-deployment-001`.
- Evidence: Herd runs PHP 8.5.8 and Android verification produced a debug-signed APK; production signing credentials and an Apple team are intentionally absent.
- Impact: repository/runtime compatibility and emulator installation are verified, but this workstation cannot claim signed Android/iOS store artifacts or real-device release coverage.
- Resolution trigger: provide protected signing/provisioning credentials through the release environment and perform signed real-device/store builds without committing secrets.

## Apple Native Build Toolchain

- Affected requirements: `sys-runtime-001`, `sys-user-003`.
- Evidence: `DB_DATABASE=:memory: php artisan native:build --simulated --no-tty --no-interaction` prepared the 134,365,209-byte iOS Laravel/WebView archive, configured the generated Xcode project, installed CocoaPods, and then stopped at `tool 'xcodebuild' requires Xcode` because `xcode-select -p` points to `/Library/Developer/CommandLineTools`; `xcrun simctl` is likewise unavailable.
- Impact: the shared responsive language implementation, iOS-mode Vite build, generated iOS project, and packaged runtime contents are verified on this workstation, but an iPhone/iPad simulator binary and runtime launch cannot be claimed here. The Android debug APK and desktop/phone/tablet browser matrix provide executable mobile-layout evidence without substituting for the missing Apple release gate.
- Resolution trigger: install full Xcode, select its developer directory, create or boot an available iPhone/iPad simulator, rerun the NativePHP simulated/device build, and repeat the first-run plus persisted language workflow before an Apple release.

## Android Emulator Capacity

- Affected requirements: `sys-runtime-001`, `sys-user-003`.
- Evidence: launching the only configured AVD with `$HOME/Library/Android/sdk/emulator/emulator -avd BTChat_API_34 -no-window -no-audio -no-boot-anim -gpu swiftshader_indirect` stops during emulator preflight with `Your device does not have enough disk space to run avd`. `adb devices -l` therefore exposes no device.
- Impact: the current debug APK is built, signed, aligned, archive-inspected, and contains the locale service plus owned flag assets, while on-device cold-launch, restart persistence, and Android logcat checks cannot be rerun in this delivery. The failed preflight occurred before package installation or data clearing, so no emulator application data was changed.
- Resolution trigger: provide sufficient free disk space without deleting unrelated user data, boot an Android 12+ emulator, install the current debug APK, and repeat first-run, restart, account-precedence, responsive, logcat, and device-SQLite checks.

Other entries may be added only with an exact failing command, affected requirement ID, and external dependency or unavailable environment. Difficult unfinished implementation is not a limitation.
