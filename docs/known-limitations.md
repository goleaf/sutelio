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

Other entries may be added only with an exact failing command, affected requirement ID, and external dependency or unavailable environment. Difficult unfinished implementation is not a limitation.
