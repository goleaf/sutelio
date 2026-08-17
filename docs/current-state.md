# Current State

As of the 2026-08-17 Task 8 source and documentation synchronization, Sutelio is an implemented Laravel 13.25/Inertia 3.3/Vue 3.5 local-first workspace task application with a versioned API, SQLite integrity and health controls, secure invitation and backup workflows, recurrence/reminder lifecycle, complete English/Lithuanian/Russian localization, factories for all 17 models, structured demo seeders, and query-budget coverage.

The complete dated evidence, exact versions, commands, failures, advisories, and live SQLite observations are in `docs/current-state-audit.md`. Active requirements and current verification statuses are in `docs/requirements.md`, `docs/non-functional-requirements.md`, and `docs/compliance-matrix.md`.

## Final Modernization State

- Herd web/development and CI use PHP 8.5, and the tracked NativePHP Mobile lock now embeds PHP 8.5.9. The Composer range remains `>=8.4 <8.6` because official NativePHP v4 documentation still describes PHP 8.4 compatibility.
- Laravel, Inertia, Fortify, Sanctum, Wayfinder, Boost, NativePHP, Pest/PHPUnit, Vite, Vue, Tailwind tooling, and their lock files are on the selected stable compatible releases with zero Composer/npm audit findings.
- Strict Eloquent behavior, typed request/query/resource boundaries, presentation-only Blade, route/controller architecture, factory states, idempotent demo seeding, and automated architecture guards are implemented.
- Email verification is intentionally absent from Fortify configuration, routes, middleware, notifications, UI, user schema, factories, and public props; authenticated registration proceeds directly to onboarding, and an active-source architecture test plus shared Boost rule prevents reintroduction.
- Active web/config/package/localization sources identify Sutelio. Deterministic clean-S, one-color wordmark, browser raster, Android adaptive/monochrome, and native splash contracts are tracked; external mobile identifiers are `com.goleaf.sutelio`, the deep-link scheme is `sutelio`, and the internal NativePHP/JNI Android namespace remains `com.nativephp.mobile`.
- The most recent complete pre-Task 8 suite passed 808 tests / 21,930 assertions. Task 8 reruns the focused identity/localization/NativePHP gate rather than claiming a new full-suite result; PHP coverage cannot be measured on this workstation because Herd has no Xdebug or PCOV driver.
- Project detail now uses the bounded Project Operations Workspace: workspace-scoped URL filters, 25-row identity-matched manual pagination, complete metrics/attention/priority summaries, localized definitions, archived-project mutation guards, and responsive accessible presentation.
- Task 8 does not claim a final rename APK or emulator verification. Task 11 must build, name, inspect, clean-install, and exercise that artifact; Task 12 must rename and verify the existing GitHub repository, checkout, and Herd site in place. Earlier APK/hash/emulator facts remain explicitly historical in `docs/current-state-audit.md` and `docs/progress.md`.

The original July findings are retained under `docs/audit`; do not use them as current status without checking the source and current compliance matrix. External environment limitations are listed only in `docs/known-limitations.md`.
