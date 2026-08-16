# Production Modernization Implementation Plan

This living plan began from the 2026-08-16 baseline. A pass is complete only after its implementation, focused tests, applicable full gates, documentation/compliance update, diff inspection, and delivery status are recorded.

## Pass 0 — Repository Protection, Inventory, And Baseline

Requirements: `docs-traceability-001`, `git-delivery-001`.

- [x] Record branch, HEAD, staging, untracked files, and the pre-existing dashboard test change.
- [x] Read all governing/canonical/historical first-party Markdown and classify generated/third-party material.
- [x] Inventory routes, modules, models, migrations, schema, factories, seeders, tests, frontend, integrations, runtime, and SQLite health.
- [x] Run safe Composer, npm, Artisan, PHP, frontend, build, quality, and Git baselines.
- [x] Verify current stable versions through Composer/npm metadata and official sources.

Verification: commands and exact baseline results in `docs/current-state-audit.md`.

## Pass 1 — Canonical Requirements And Documentation

Requirements: `docs-traceability-001`, every `sys-*` and non-functional ID.

- [x] Establish mandatory reading order, durable root instructions, stable IDs, functional/non-functional requirements, and controlled statuses.
- [x] Create the documentation inventory and compliance matrix.
- [x] Record the Inertia/Livewire precedence decision and NativePHP/PHP runtime constraint.
- [x] Rewrite all active architecture, domain/data, security, API, frontend, localization, testing, seeding, performance, caching, integration, operations, deployment, review, and limitation documents against final code.
- [x] Mark historical audits/roadmaps as historical without deleting evidence.

Verification: Markdown links/content review, `git diff --check`, Prettier where applicable. Rollback: documentation-only file restoration is straightforward; no runtime state changes.

## Pass 2 — Runtime And Dependency Modernization

Requirements: `sys-runtime-001`, `sec-deps-001`, `test-static-001`, `ops-deployment-001`.

- [x] Isolate the Herd site/CLI to installed PHP 8.5 and record the exact patch version/extensions.
- [x] Select a Composer PHP constraint that runs on web PHP 8.5 while remaining honest about NativePHP's embedded PHP 8.4.
- [x] Upgrade Laravel 13, Inertia, Boost, Fortify, Sanctum, Wayfinder, Pint, Pail/Sail, Guzzle/CommonMark transitively, and other compatible first-party direct packages.
- [x] Upgrade NativePHP Mobile 3 to stable 4 only after its upgrade contract and generated native-project implications are understood; verify Android 12/minSdk compatibility.
- [x] Upgrade Pest 4 to stable Pest 5/PHPUnit 13 when the existing suite and Laravel plugin resolve cleanly; preserve Pest as the sole primary PHP test style.
- [x] Upgrade Vite/Laravel Vite plugin/Vue/Pinia/Reka/TypeScript tooling and audit-fixed transitive JS dependencies without changing package manager.
- [x] Rebuild lock files through Composer/npm, never by hand.
- [x] Update CI to PHP 8.5 and selected Node baseline.

Verification after each targeted update: Composer validation/audit, npm audit, boot/about, focused/full Pest, Larastan, Pint, frontend tests/types/lint/format/build. Compatibility risk: major Pest/NativePHP/Vite updates. Rollback: revert each coherent lock/constraint pass; NativePHP v4 installer is destructive to generated native output, so preserve project-specific config and inspect before/after.

## Pass 3 — Regression And Architecture Guard Closure

Requirements: `sys-dashboard-001`, `test-feature-001`, `test-architecture-001`, `sec-web-001`.

- [x] Repair the user-owned dashboard design test so it asserts semantic structure instead of whitespace/call-argument formatting; do not weaken its intended dashboard contract.
- [x] Add/expand architecture coverage for no Livewire/Volt, no Blade PHP/data/service calls, no `env()` outside config, no debug calls, no forbidden frontend dependencies, and no unsafe dynamic Tailwind patterns where reliable.
- [x] Externalize or otherwise remove ordinary inline script/style from the first-party Inertia Blade shell without theme flash, CSP, locale, or Vite regressions.
- [x] Remove dead example Pest helpers/tests only when confirmed non-contractual and replace with meaningful smoke/architecture coverage.
- [x] Enable strict Eloquent behavior in local/testing and fix any missing-load/discarded/missing-attribute defects instead of suppressing them.

Verification: targeted design/architecture/page/query tests, full PHP/frontend gates, browser login/dashboard/theme smoke.

## Pass 4 — PHP 8.5 And Laravel 13 Applicability

Requirements: `sys-runtime-001`, `data-time-001`, `test-static-001`.

- [x] Scan all first-party PHP for PHP 8.5 warnings/deprecations and incompatible assumptions with full error reporting.
- [x] Apply `declare(strict_types=1)` to new/materially modified PHP files and improve real type boundaries without mechanical churn.
- [x] Remove service-locator usage in materially touched providers/controllers when dependency injection is supported.
- [x] Record applicability and tests for PHP 8.5 URI, clone-with, pipe, `NoDiscard`, `Override`, array helpers, and persistent cURL sharing; use only features with a real clarity/correctness benefit.
- [x] Record applicability and tests for Laravel 13 request-forgery protection, attributes, API resources/JSON:API, cache touch, queues, AI/vector/realtime/image features; do not add features without requirements.

Verification: PHP 8.5 syntax/deprecation/full suite, Laravel cache/route/view/boot checks, applicability matrices in architecture docs.

## Pass 5 — Data, Security, Factories, And Seeders

Requirements: `sec-*`, `data-*`, `seed-model-001`, `seed-demo-001`, all domain `sys-*`.

- [x] Re-run focused attacker/victim, wrong-workspace, policy, API-ability, token replay, backup, import/upload, recurrence/reminder, and schema integrity suites after upgrades.
- [x] Compare every model/cast/relation/hidden attribute/factory to every table/constraint/index and correct discovered drift with populated-safe SQLite migrations only.
- [x] Inventory factory states and add only meaningful missing states/helpers; create an automated every-factory/every-state validity contract.
- [x] Separate/idempotently verify reference/default-definition seeding from local/demo graphs where needed; add deterministic, repeat-run, production-guard, role-authentication, page-render, and file-fixture tests.
- [x] Run fresh test migration, rollback where meaningful, fresh seed, repeat seed, FK/unique/check validation, and representative populated upgrade.

Migration risk: SQLite table rebuilds and backup/restore. Required rollback design: preflight orphan/value checks, transaction where supported, explicit down behavior, representative backup, post-migration FK/integrity checks.

## Pass 6 — Frontend, Tailwind, Localization, Accessibility, And Performance

Requirements: `ui-*`, `i18n-*`, `perf-*`, `sys-dashboard-001`, task/project/calendar/activity/notification requirements.

- [x] Verify Tailwind remains CSS-first with complete source detection and coherent design tokens; remove obsolete frontend wiring/dependencies only after usage tracing.
- [x] Audit all Vue pages/components for immutable props, task/workspace identity resets, minimal async state, action-specific loading, duplicate prevention, keyboard/focus behavior, translated copy, and static Wayfinder imports.
- [x] Verify translation key/placeholder parity and critical rendering for `en`, `lt`, and `ru` plus fallback and locale/timezone formatting.
- [x] Run critical browser workflows at representative mobile/tablet/desktop widths, light/dark/system and reduced motion, inspect fresh console logs, keyboard focus, dialogs, long translations, and horizontal overflow.
- [x] Re-measure critical query budgets, bundle sizes/build time, page errors, and any measurable Inertia payloads; change caching only for measured stable work.

Verification: frontend tests/types/lint/format/build, localization/design/query suites, Boost browser logs, browser matrix. Accessibility limitation must identify an unavailable tool/device, never substitute documentation for a fix.

## Pass 7 — Final Verification, Documentation, Review, And Delivery

Requirements: all active requirements, `docs-traceability-001`, `git-delivery-001`.

- [x] Run PHP version, Composer validate/audit/outdated/compatibility, syntax, Pint, Larastan, full and parallel Pest, coverage attempt, architecture tests, fresh migrate/seed/repeat seed, frontend tests/types/lint/format/audit/build, browser checks, route/config/view caches, boot/HTTP/API smoke, query/translation/security checks. Coverage is externally blocked by the missing driver recorded under `test-coverage-001`.
- [x] Compare baseline/final tests, advisories, query budgets, assets, build time, migration/seed time, and coverage availability.
- [x] Re-read every first-party Markdown and synchronize versions, behavior, commands, paths, requirements, compliance, plan, changelog, audit resolution, and genuine limitations.
- [x] Inspect `git status`, complete diff, staged diff, secrets/generated artifacts, lock files, and preservation of the pre-existing dashboard change.
- [x] Commit coherent phase-owned files on `main`, record hashes, push `origin main`, and report the observed result.

Completion is prohibited while an applicable item above remains unchecked unless its requirement is explicitly marked `blocked by external dependency` with current evidence in `docs/known-limitations.md`.

## Post-Modernization Drift Closure — Project Operations

- [x] Implement the accepted Project Operations design/plan with scoped request/query/resource boundaries and bounded Inertia scroll results.
- [x] Add rollback-safe filter/sort indexes and verify simplified plus production-generated SQLite query plans.
- [x] Resolve every independent review finding with backend, frontend, localization, lifecycle, timezone, and architecture regressions.
- [x] Verify responsive desktop/mobile behavior, dark/reduced-motion presentation, URL filtering, clear state, task detail, focus semantics, and zero current browser errors/overflow.
- [x] Synchronize requirements, performance evidence, current state, changelog, review, plan, and progress records.

## Post-Modernization Drift Closure — Notification Command Center

- [x] Replace content-sniffing presentation with a typed user-scoped request/query/resource boundary, deterministic bounded pages, global inbox totals, and semantic notification kinds.
- [x] Prove idempotent user-only mutations, safe legacy payloads, foreign task-link rejection, batched task authorization, stats-only partial-query omission, and the production unread-reminder index plan.
- [x] Split the Vue page into typed filter/feed/row/helper components with URL state, cancellation, Today/Earlier grouping, shared row/browser content, focused pending/empty states, and EN/LT/RU copy.
- [x] Resolve review and live-browser findings for local-midnight refresh, Lithuanian grammar, pressed-button group semantics, one page landmark/heading, 44-pixel controls, reduced motion, and zero desktop/mobile overflow or current browser errors.
- [x] Synchronize the stable notification requirement, compliance, architecture, frontend, security, localization, accessibility, testing, performance, audit, review, changelog, plan, limitation, and progress records.

## Post-Modernization Drift Closure — Authenticated Landmark Integrity

Requirements: `ui-accessibility-001`, `test-architecture-001`, `docs-traceability-001`.

- [x] Add a failing architecture regression proving the persistent authenticated shell is the sole owner of the page `main` landmark.
- [x] Replace nested page and settings-layout `main` elements with neutral presentation wrappers without changing layout, routing, or interaction behavior.
- [x] Verify one `main` and one logical page heading across authenticated desktop and mobile routes with no horizontal overflow or current browser errors.
- [ ] Run focused and full PHP/frontend quality gates, synchronize canonical documentation, inspect the final diff, commit the coherent phase, and push `main` to `origin`.

Verification: focused frontend architecture tests, Vue type/lint/format checks, production build, authenticated browser landmark matrix, full PHP/frontend gates, and final Git inspection. Rollback is a direct semantic-wrapper reversal; no dependency, schema, data, route, policy, or public response contract changes.
