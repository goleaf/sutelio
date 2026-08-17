# Current State

As of the 2026-08-17 Task 9 quality-gate and canonical-evidence synchronization, Sutelio is an implemented Laravel 13.25/Inertia 3.3/Vue 3.5 local-first workspace task application with a versioned API, SQLite integrity and health controls, secure invitation and backup workflows, recurrence/reminder lifecycle, complete English/Lithuanian/Russian localization, factories for all 17 models, structured demo seeders, and query-budget coverage.

The complete dated evidence, exact versions, commands, failures, advisories, and live SQLite observations are in `docs/current-state-audit.md`. Active requirements and current verification statuses are in `docs/requirements.md`, `docs/non-functional-requirements.md`, and `docs/compliance-matrix.md`.

## Final Modernization State

- Herd web/development and CI use PHP 8.5, and the tracked NativePHP Mobile lock now embeds PHP 8.5.9. The Composer range remains `>=8.4 <8.6` because official NativePHP v4 documentation still describes PHP 8.4 compatibility.
- Laravel, Inertia, Fortify, Sanctum, Wayfinder, Boost, NativePHP, Pest/PHPUnit, Vite, Vue, Tailwind tooling, and their lock files are on the selected stable compatible releases with zero Composer/npm audit findings.
- Strict Eloquent behavior, typed request/query/resource boundaries, presentation-only Blade, route/controller architecture, factory states, idempotent demo seeding, and automated architecture guards are implemented.
- Email verification is intentionally absent from Fortify configuration, routes, middleware, notifications, UI, user schema, factories, and public props; authenticated registration proceeds directly to onboarding, and an active-source architecture test plus shared Boost rule prevents reintroduction.
- Active web/config/package/localization sources identify Sutelio. Deterministic clean-S, one-color wordmark, browser raster, Android adaptive/monochrome, and native splash contracts are tracked; external mobile identifiers are `com.goleaf.sutelio`, the deep-link scheme is `sutelio`, and the internal NativePHP/JNI Android namespace remains `com.nativephp.mobile`.
- Task 9 passed both the fresh sequential and isolated parallel Pest suites at 831 tests / 32,899 assertions; all 45 frontend tests also passed. The coverage attempt exited before tests because the Herd PHP runtime has neither Xdebug nor PCOV, so no coverage percentage is available or claimed.
- Project detail now uses the bounded Project Operations Workspace: workspace-scoped URL filters, 25-row identity-matched manual pagination, complete metrics/attention/priority summaries, localized definitions, archived-project mutation guards, and responsive accessible presentation.
- The successor `docs/superpowers/plans/2026-08-17-sutelio-light-motion-icons-and-final-device.md` supersedes the earlier pending Task 10-12 numbering. Its Task 18 browser verification and Task 19 complete quality gate remain pending and have not been run as successor-plan gates; Task 20 owns the final APK/emulator work, Task 21 owns the repository/checkout/Herd rename, and Task 22 reserves physical Samsung installation as the last mutating action. Earlier APK/hash/emulator facts remain explicitly historical in `docs/current-state-audit.md` and `docs/progress.md`.

The original July findings are retained under `docs/audit`; do not use them as current status without checking the source and current compliance matrix. External environment limitations are listed only in `docs/known-limitations.md`.
