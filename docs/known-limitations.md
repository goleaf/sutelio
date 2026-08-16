# Known Limitations

Only external or environmental blockers belong here.

## NativePHP Embedded PHP Runtime

- Affected requirement: `sys-runtime-001`.
- Evidence: NativePHP Mobile v4 official documentation states that its mobile bundle currently embeds PHP 8.4 and applications must work with that version.
- Impact: web/development/CI can run PHP 8.5, but Composer cannot truthfully require `>=8.5` and still produce a working NativePHP mobile package. The compatible contract must allow PHP 8.4 until NativePHP publishes an embedded PHP 8.5 runtime.
- Resolution trigger: upgrade the NativePHP package/runtime after an official stable 8.5 mobile build exists, run the full web/mobile gates, then raise the Composer floor.

## Coverage Driver

- Affected requirement: `test-coverage-001`.
- Evidence: `herd coverage --ri xdebug` and `herd debug --ri xdebug` report that Xdebug is not present; `herd php artisan test --coverage --min=0 --compact` exits with `Code coverage driver not available. Did you install Xdebug or PCOV?`.
- Impact: no truthful application coverage percentage can be reported from this workstation. The complete behavioral suite still passes 627 tests / 8,958 assertions sequentially and in parallel.
- Resolution trigger: install a PHP 8.5-compatible Xdebug or PCOV extension in Herd, rerun the coverage command, review uncovered critical branches, and record the measured result.

## Runtime Patch And Store Signing Environment

- Affected requirements: `sys-runtime-001`, `ops-deployment-001`.
- Evidence: Herd exposes PHP 8.5.0 and reports no installed update, while the official PHP release check found 8.5.8 current. Android verification produced a debug APK; production signing credentials and an Apple team are intentionally absent.
- Impact: repository/runtime compatibility is verified on PHP 8.5, but this workstation cannot claim the newest PHP patch or signed Android/iOS store artifacts.
- Resolution trigger: update Herd's PHP 8.5 runtime when available, rerun all gates, then provide protected signing/provisioning credentials through the release environment and perform signed store builds without committing secrets.

Other entries may be added only with an exact failing command, affected requirement ID, and external dependency or unavailable environment. Difficult unfinished implementation is not a limitation.
