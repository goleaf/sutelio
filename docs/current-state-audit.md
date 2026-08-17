# Current-State Audit — 2026-08-16

This is the active modernization baseline. The July audit under `docs/audit` is preserved as historical evidence; its critical/high findings were subsequently addressed in the source and tests recorded by `docs/progress.md`.

## Post-Audit Runtime Verification — 2026-08-17

The dated baseline and modernization measurements below remain historical evidence. The current checkout runs Herd PHP 8.5.8 and NativePHP Mobile 4.2 with tracked embedded PHP 8.5.9. A complete compatible dependency refresh upgraded `laravel/mcp` to 0.9.4 and transitive `es-toolkit` to 1.51.0 with zero Composer/npm advisories or compatible direct updates. The final suite passes 762 tests / 11,359 assertions sequentially and in parallel plus 45 frontend tests; Larastan, Pint, types, lint, format, build, isolated 35-migration/repeat-seed/health verification, and runtime caches pass. The current debug APK hash, manifest, signature, archive, and Android 14 emulator results are recorded in `docs/deployment.md` and the latest `docs/progress.md` entry and supersede older artifact measurements below.

## Repository Protection

- Branch: `main` at `7f90145`, synchronized with `origin/main` when inspected.
- Staging area: empty.
- Pre-existing work: one unstaged user change in `tests/Feature/FrontendDesignTest.php` (+36/-2) extending the dashboard command-center contract. It predates this modernization and must be preserved.
- Untracked files: none.
- JavaScript package manager: npm, selected by the existing `package-lock.json`; no second lock file is permitted.
- Relational database: SQLite in local, testing, and intended production/mobile configurations.
- Runtime restrictions: main-only delivery, SQLite-only, Inertia/Vue rather than Livewire, private local storage, scheduled reminder/recurrence/backup/cleanup commands, NativePHP Mobile packaging, and Laravel Herd web serving.

## System Inventory

- 263 first-party PHP application files, 301 routes, 32 migrations, 17 Eloquent models, 17 factories, 12 seeders, and 57 PHP test files.
- Core modules: identity/security settings; workspaces/members/invitations/ownership/task definitions; projects/tasks; checklists/comments/labels/tags; recurrence/reminders; attachments; activity/notifications; import/export; SQLite backup/restore; API v1; preferences/localization; dashboard/calendar.
- Roles: workspace owner, admin, and member. Ownership, membership, route workspace, and parent aggregate are authorization boundaries.
- Frontend: Inertia 3 pages with Vue 3 Composition API and TypeScript; Pinia for shared state; Wayfinder route functions; Reka/shadcn-style primitives; Tailwind CSS-first Vite integration; fixed Warm Precision design with light/dark/system color mode.
- External/runtime integrations: Fortify/passkeys/two-factor, Sanctum API tokens, database/mail notifications, configured filesystems, NativePHP Mobile, and SQLite. No external payment, webhook, semantic-search, AI-provider, Flux, Livewire, or Volt contract exists.
- Async execution: scheduled commands claim/generate bounded work; reminder delivery may run through the database queue. The database/cache/session/queue drivers are SQLite/database-backed, not Redis requirements.

## Baseline Versions

| Component                     | Observed baseline                                    |
| ----------------------------- | ---------------------------------------------------- |
| CLI PHP                       | 8.4.16 through Herd; Herd also has PHP 8.5 installed |
| Laravel                       | 13.20.0                                              |
| Inertia Laravel / Vue         | 3.1.1 / 3.6.1                                        |
| Vue                           | 3.5.40                                               |
| Tailwind / Tailwind Vite      | 4.3.3 / 4.3.3                                        |
| Vite / Laravel Vite plugin    | 8.1.5 / 3.1.3                                        |
| Fortify / Sanctum / Wayfinder | 1.37.2 / 4.3.2 / 0.1.20                              |
| NativePHP Mobile              | 3.3.6                                                |
| Pest / PHPUnit                | 4.7.5 / 12.5.30                                      |
| Larastan / Pint / Boost       | 3.10.0 / 1.29.3 / 2.4.13                             |
| Node / npm                    | 22.21.1 / 11.9.0                                     |

Authoritative release checks found PHP 8.5.8 current, Laravel 13.25 current in Composer metadata, Pest 5.1.1 current, and NativePHP Mobile 4.2.0 current. NativePHP's official v4 documentation states that its embedded mobile runtime is PHP 8.4, which prevents an honest Composer minimum of 8.5 while mobile remains supported.

## Baseline Commands And Results

| Command                                              | Result                                                                                                                                                                                                    |
| ---------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `composer validate --strict --no-check-publish`      | Passed                                                                                                                                                                                                    |
| `composer why-not php 8.5`                           | No dependency blocker                                                                                                                                                                                     |
| `composer why-not laravel/framework ^13.0`           | Laravel 13 already installed; no blocker                                                                                                                                                                  |
| `composer audit`                                     | Failed: 8 actionable advisories — Guzzle 7.15.1 (2) and CommonMark 2.8.3 (6)                                                                                                                              |
| `npm audit`                                          | Failed: 4 actionable transitive findings — brace-expansion, js-yaml, nanoid, and PostCSS                                                                                                                  |
| `php artisan about --only=environment,cache,drivers` | Passed; local/SQLite/database-backed runtime confirmed                                                                                                                                                    |
| `php artisan app:database-health --json`             | Passed: path, foreign keys, WAL, synchronous, busy timeout, cache/temp/checkpoint, quick check, and FK check healthy                                                                                      |
| `php artisan test --compact`                         | Failed: 569 tests, 567 passed, 2 failed, 3,387 assertions. Both failures are brittle exact-source assertions in the pre-existing dashboard test change; application behavior was not the failing subject. |
| `composer run types:check`                           | Passed with zero Larastan errors                                                                                                                                                                          |
| `composer run lint:check`                            | Passed                                                                                                                                                                                                    |
| `npm run test:frontend`                              | Passed: 13 tests                                                                                                                                                                                          |
| `npm run types:check`                                | Passed                                                                                                                                                                                                    |
| `npm run lint:check`                                 | Passed with zero warnings/errors                                                                                                                                                                          |
| `npm run format:check`                               | Passed                                                                                                                                                                                                    |
| `npm run build`                                      | Passed in 9.78 s after 3,429 modules; CSS 158.29 kB (23.61 kB gzip), app JS 194.24 kB (49.69 kB gzip), largest listed button chunk 261.82 kB (89.20 kB gzip)                                              |
| `git diff --check`                                   | Passed                                                                                                                                                                                                    |

## Live SQLite Evidence

- SQLite 3.45.2; 30 application tables observed.
- Foreign-key and quick integrity checks passed through Laravel's actual connection.
- WAL, `NORMAL` synchronous mode, busy timeout, cache/temp store, and auto-checkpoint matched the configured health contract.
- Sample local data contained 3 users, 1 workspace, 6 projects, 27 tasks, and 3 memberships; no destructive baseline command was run against it.
- The schema contains the corrective UUID relations, scoped unique/index contracts, task-parent integrity, recurrence occurrence identity, and reminder delivery lifecycle introduced by prior phases.

## Current Risks And Debt

1. Dependency advisories are actionable through stable updates and must be resolved before delivery (`sec-deps-001`).
2. Web CLI/site runtime and CI still use or declare PHP 8.3/8.4 even though PHP 8.5 is installed and requested (`sys-runtime-001`, `ops-deployment-001`).
3. NativePHP Mobile v4 embeds PHP 8.4; preserving mobile prevents a PHP `>=8.5` Composer floor until upstream ships 8.5 (`sys-runtime-001`).
4. Two dashboard source-contract assertions are too formatting-specific and block the otherwise passing PHP suite (`sys-dashboard-001`, `test-feature-001`).
5. Coverage, exhaustive factory-state creation, repeated/idempotent seeding, expanded architecture guards, final browser/accessibility smoke, and final cache/config/view checks remain to be executed in this modernization (`test-coverage-001`, `seed-model-001`, `seed-demo-001`, `test-architecture-001`).
6. Canonical documentation was stale and internally contradictory; this first rewrite establishes stable requirements but remains in progress until final code/version evidence is synchronized (`docs-traceability-001`).

No critical workspace-isolation, backup path, known-password invitation, unrestricted token, missing-FK, N+1, localization, or static-analysis defect from the July audit remains open. The focused security, schema, API, query-budget, and complete regression suites rechecked those conclusions after the dependency modernization.

## Final Modernization Result

The modernization closed every baseline risk that is controllable in the repository. The remaining upstream/environment limitations are documented in `docs/known-limitations.md`: NativePHP Mobile 4.2 embeds PHP 8.4, the installed Herd PHP 8.5 runtime has no coverage driver and exposes PHP 8.5.0 rather than the current patch, and protected store-signing credentials are unavailable on this workstation.

| Area                  | Final observed result                                                                                                                                                         |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Web runtime           | Herd PHP 8.5.0; Laravel 13.25.0; 266 first-party routes; application/database health passed                                                                                   |
| Composer              | Laravel 13.25, Inertia Laravel 3.3.1, Fortify 1.38, Sanctum 4.3.3, Wayfinder 0.1.21, NativePHP 4.2.0, Pest 5.1.1/PHPUnit 13.3; direct dependencies current; zero advisories   |
| Frontend              | Vue 3.5.41, Inertia Vue 3.6.1, Tailwind/@tailwindcss-vite 4.3.3, Vite 8.2.1, Laravel Vite plugin 3.2.0, TypeScript 6.0.3; npm audit zero                                      |
| PHP verification      | Pest passed 706 tests / 10,027 assertions sequentially in 30.164 s and in parallel in 9.530 s; Pint and Larastan level 7 passed                                               |
| Frontend verification | 38/38 Node tests, Vue type checking, ESLint, Prettier, and production build passed                                                                                            |
| Build                 | 3,494 modules in 4.39 s; main application CSS 162.35 kB / 23.92 kB gzip; application entry 89.64 kB / 23.25 kB gzip; task index chunk 39.73 kB / 10.08 kB gzip                |
| Database/seeding      | 34 migrations from zero; repeat idempotent seed; 3 users, 1 workspace, 25 tasks; integrity and foreign-key checks passed; all 17 models have factories                        |
| Android               | NativePHP 4.2 installed; Gradle `assembleDebug` passed 40 tasks; APK package `com.goleaf.xiaomimimo`, minSdk 31, targetSdk 36                                                 |
| Runtime caches/smoke  | Config, routes, and views cached; every SQLite health check true; login 200 in 0.123229 s / 59,313 bytes; unauthenticated API contract returned 401 in 0.029020 s / 137 bytes |
| Browser               | Prior login/keyboard/motion/color workflows plus 22 authenticated desktop/mobile landmark checks passed with one `main`/`h1`, zero current errors, and zero overflow          |

The post-modernization landmark audit found and closed nested `main` landmarks across ten authenticated page/settings wrappers plus a duplicate reusable task-detail `h1`. A final 11-route matrix at desktop and mobile widths produced 22/22 checks with exactly one `main`, one `h1`, zero horizontal overflow, and no captured console, page, request, or HTTP error.

Resolved baseline findings include the Composer/npm advisories, PHP 8.5 site/CI runtime, Pest/NativePHP/Vite major upgrades, brittle dashboard assertions, strict-Eloquent projection defects, Blade service/config access, route endpoint closures, insufficient architecture guards, repeated Tailwind panel literals, incomplete factory states, and non-idempotent demo orchestration. The activity ledger gained one additive, rollback-safe migration with two workspace-prefixed composite indexes for its user and event filters; all other reviewed schema integrity remains enforced by the existing migrations and live schema.

The working tree also received dashboard, calendar, and activity-intelligence phases from another active repository workflow during this modernization. Those changes were preserved, integrated, formatted, and verified rather than reverted or misattributed.

Final `npm outdated` lists only platform-specific optional Rollup/Tailwind/Lightning CSS binaries for non-macOS targets, `@types/node` 26 while the project intentionally builds on Node 22, and TypeScript 7 while `typescript-eslint` 8.67 does not declare TypeScript 7 compatibility. TypeScript 6.0.3 and Node 22 types are therefore the latest mutually compatible intentional baseline, not ignored upgrades.

## Baseline Interpretation

The repository is a mature Inertia/Vue application, not a Livewire application. Under the user-provided conflict precedence, the repository-level `AGENTS.md` requirement to preserve Inertia/Vue and forbid Livewire/Volt outranks generic Livewire migration language. Livewire/Volt/Flux requirements are therefore non-applicable, not unfinished implementation.
