# Production Modernization Implementation Plan

## Active System-Wide UI/UX Remediation Program — 2026-08-17

Requirements: `ui-system-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-motion-001`, `i18n-001`, `test-feature-001`, `test-static-001`.

- [x] Publish the diagnostic baseline in `docs/ui-ux-audit-2026-08-17.md` and record the audit-first, lowest-shared-correction rule.
- [x] Record the product owner's selected direction: maximum practical motion coverage with soft, non-aggressive behavior and autonomous evidence-based consolidation.
- [x] Define the high-coverage, low-amplitude motion contract in `docs/superpowers/specs/2026-08-17-sutelio-soft-motion-master-design.md`.
- [x] Publish and quality-review the unified program in `docs/superpowers/plans/2026-08-17-sutelio-soft-motion-ui-remediation-master-plan.md`, combining all unfinished UI/UX, motion, browser, build, rename, emulator, and final-device scope while excluding work already delivered by responsive/constructor/database commits.
- [x] Author the first implementation-ready source packet in `docs/superpowers/plans/2026-08-17-sutelio-soft-motion-foundation.md` with exact RED/GREEN steps, file ownership gates, commands, performance budgets, rollback boundaries, and commit checkpoints; execution remains gated behind Package 1 and active shared-file owners.
- [ ] Re-baseline the executable packet against committed `main` after responsive, gradient-control, global-operation, localized-timezone, and current motion/icon owners settle; do not implement from stale line numbers or duplicate delivered primitives.

This planning update does not claim application remediation. The optimized program closes P1 truth before P2 polish, treats motion quality as 100% meaningful-state classification rather than moving-element count, removes generic 480 ms page/Card/static-list entrances, and replaces them with measurable 90–260 ms event-driven feedback plus one bounded task-completion signature. Localized timezone, database, global busy feedback, and gradient-control work retain separate owners and explicit integration boundaries.

## Onboarding Choice Coherence — 2026-08-17

Requirements: `sys-onboarding-001`, `ui-system-001`, `ui-coherence-001`, `i18n-001`, `test-feature-001`, `test-static-001`, `git-delivery-001`.

- [x] Reproduce the new-account workspace/project/task dead-choice pattern in isolated Chrome DevTools and Playwright sessions and trace it to three sibling components rather than the authorized option query.
- [x] Add a failing-first regression for option-cardinality presentation, create-only copy, and the absence of disabled existing-choice placeholders.
- [x] Make workspace, project, and task steps create-only when their authorized option set is empty while retaining select-or-create for invitations, populated accounts, and replay.
- [x] Keep EN/LT/RU copy shape-complete and make all three entity-step headings truthful across create-only and select-or-create modes.
- [x] Record the permanent area rule and synchronize requirement/compliance traceability.
- [ ] Complete independent Playwright empty-state coverage, full quality/browser gates, disposable-account cleanup, scoped diff review, semantic commit, and normal `origin/main` push.

Detailed analysis, exact files, test order, rollback, and delivery boundaries are recorded in `docs/superpowers/plans/2026-08-17-onboarding-choice-coherence.md`. This slice changes no query, route, policy, authorization, API, schema, migration, dependency, database, Blade, or Filament behavior.

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
- [x] Select a Composer PHP constraint that runs on web PHP 8.5 while remaining honest about NativePHP's documented PHP 8.4 compatibility and currently generated PHP 8.5.9 runtime.
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
- [x] Keep the first-party Inertia Blade shell free of ordinary inline script/style and obsolete color-mode bootstrap logic without CSP, locale, or Vite regressions.
- [x] Replace inherited Tailwind orange values with the exact Sutelio signal-orange scale and accessible deep-cobalt primary foreground, without collapsing status or chart semantics.
- [x] Remove dead example Pest helpers/tests only when confirmed non-contractual and replace with meaningful smoke/architecture coverage.
- [x] Enable strict Eloquent behavior in local/testing and fix any missing-load/discarded/missing-attribute defects instead of suppressing them.

Verification: targeted design/brand-color/architecture/page/query tests, full PHP/frontend gates, browser login/dashboard fixed-light smoke and computed primary-color inspection.

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
- [x] Run critical browser workflows at representative mobile/tablet/desktop widths in the fixed light mode and reduced motion, inspect fresh console logs, keyboard focus, dialogs, long translations, and horizontal overflow.
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
- [x] Run focused and full PHP/frontend quality gates, synchronize canonical documentation, inspect the final diff, commit the coherent phase, and push `main` to `origin`.

Verification: focused frontend architecture tests, Vue type/lint/format checks, production build, authenticated browser landmark matrix, full PHP/frontend gates, and final Git inspection. Rollback is a direct semantic-wrapper reversal; no dependency, schema, data, route, policy, or public response contract changes.

## Post-Modernization Dependency Refresh — 2026-08-16

Requirements: `sys-runtime-001`, `sec-deps-001`, `test-static-001`, `ops-deployment-001`, `git-delivery-001`.

- [x] Refresh every Composer package to the latest stable release permitted by the Laravel 13, NativePHP Mobile 4, Pest 5, documented PHP 8.4 compatibility, and PHP 8.5 web/mobile runtime envelope.
- [x] Refresh every npm package to the latest stable release permitted by Node 22 and the installed TypeScript, ESLint, Inertia, Vue, Tailwind, and Vite peer contracts.
- [x] Preserve the existing Composer/npm package set and single npm lock file; document upstream major-version ceilings rather than overriding them with incompatible direct constraints.
- [x] Run dependency audits, application boot, static analysis, full backend/frontend tests, production build, isolated migration/seeding, cache, route, HTTP, and diff gates.
- [x] Keep the concurrent workspace-stewardship implementation outside the dependency commit, synchronize package evidence, commit the coherent package slice, and push `main`.

Verification: Composer validate/audit/outdated and dependency blockers, npm audit/outdated and peer metadata, Pint, Larastan, full and parallel Pest, frontend tests/types/lint/format/build, isolated SQLite migration/seed/integrity, application cache/route/view/HTTP smoke, and final Git inspection. Rollback is a direct manifest/lock/documentation commit reversal; no schema or production data changes are planned.

## Post-Modernization Product Delivery — Guided Onboarding

Requirements: `sys-onboarding-001`, `sys-user-002`, `sys-workspace-002`, `ui-*`, `i18n-*`, `data-integrity-001`, `test-feature-001`, `ops-deployment-001`, `git-delivery-001`.

- [x] Approve and record an eight-step authenticated-user journey with resume, required skip, role/invitation adaptation, manual replay, and honest Dashboard continuation.
- [x] Add populated-safe versioned preference lifecycle state and a run/request-scoped idempotency ledger; keep existing users complete and new Fortify registrations pending.
- [x] Implement adjacent lifecycle transitions, completion/skip/restart, bounded authorized option queries, and scoped preference/workspace/project/task selection or creation through canonical actions.
- [x] Implement the responsive Warm Guided Route, immediate EN/LT/RU preference rendering, accessible validation/focus/status/confirmation states, Dashboard checklist, and Settings replay.
- [x] Resolve runtime review findings for single layout ownership, pre-onboarding signed invitation acceptance, stable 390-pixel action layout, safe-area CSS architecture, and representative browser modes.
- [x] Verify focused/full/parallel Pest, Pint, Larastan, frontend behavior/types/lint/format/build, dependency audits, isolated SQLite migration/seed/rollback, live additive migration, real desktop/mobile browser journeys, and canonical traceability.
- [x] Build and independently inspect the final onboarding-capable NativePHP Android debug APK, record its hash/manifest/signature/alignment/archive evidence, then commit and push the final documentation state.

Verification and rollback details are recorded in `docs/plans/2026-08-16-guided-onboarding-implementation.md` and `docs/progress.md`. The migration down path removes only onboarding lifecycle storage; runtime rollback must preserve any workspace/project/task entities already created by users.

## Full Dependency, Installation, And Emulator Verification — 2026-08-17

Requirements: `sys-runtime-001`, `sec-deps-001`, `data-sqlite-001`, `test-feature-001`, `ops-deployment-001`, `git-delivery-001`.

- [x] Resolve and install the full compatible Composer/npm graphs, upgrade current lock drift, and verify zero advisories or compatible direct updates.
- [x] Update Android SDK tools, regenerate the ignored NativePHP platform project, and refresh the tracked embedded runtime lock.
- [x] Add failing-first coverage and repair mobile SQLite containment, NativePHP scalar normalization, and unavailable bundled Blade view hints found by clean-device testing.
- [x] Build and independently verify the APK manifest, signature, alignment, outer/nested archives, required bundle files, and host-database exclusion.
- [x] Clean-install on the existing Android 14 emulator and complete cold boot, migrations, login rendering, registration, onboarding navigation, SQLite integrity, process, and log checks.
- [x] Run the final complete backend/frontend/static/data/diff gates, synchronize final evidence, commit only phase-owned files, and push `origin/main`.

The ignored debug APK remains a local sideload artifact. Production signing and real-hardware release validation remain external release activities.

## Global Language Selection — 2026-08-17

Requirements: `sys-user-003`, `sys-user-002`, `sys-onboarding-001`, `i18n-001`, `i18n-format-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-motion-001`, `test-feature-001`, `test-static-001`, `git-delivery-001`.

- [x] Define the account/device/session/browser/fallback precedence and a server-owned extensible EN/LT/RU catalog with local flag assets.
- [x] Add failing-first coverage for first-run presentation, persistence, invalid input, account precedence, registration/login synchronization, shared shells, accessibility, and live translation behavior.
- [x] Implement the validated and rate-limited locale action, encrypted long-lived cookie, shared Inertia props, mandatory animated first-run dialog, and global auth/authenticated dropdown.
- [x] Reuse the same immediate locale mutation from Settings and onboarding, keep registration and document locale synchronized, and make persistent translated layout props reactive.
- [x] Verify focused/full Pest, Pint, Larastan, frontend tests/types/lint/format/audit/build, isolated SQLite migration/seed integrity, web browser behavior, NativePHP packaging, and representative desktop/phone/tablet rendering.
- [x] Inspect the complete and staged diffs, preserve concurrent Signal Orange work, commit the attributable localization slice on `main`, push normally, and verify remote equality.

Detailed design and execution order are recorded in `docs/superpowers/specs/2026-08-17-global-language-selection-design.md` and `docs/superpowers/plans/2026-08-17-global-language-selection.md`. The change adds no schema or dependency; rollback removes the locale endpoint/cookie/shared controls while preserving the pre-existing account preference column.

## Localized Timezone And Week Defaults — 2026-08-17

Requirements: `sys-user-002`, `sys-user-003`, `sys-onboarding-001`, `data-time-001`, `i18n-001`, `i18n-format-001`, `ui-accessibility-001`, `ui-responsive-001`, `test-feature-001`, `test-static-001`, `git-delivery-001`.

- [x] Add failing-first enum, catalog, persistence, registration, detection, and frontend interaction coverage.
- [x] Make `UserLanguage` own EN/LT/RU week-start defaults and a `TimeZoneRegion` enum own stable translated IANA region groups.
- [x] Generate a localized server timezone catalog, retaining canonical identifiers and Laravel validation without an external IP service or package dependency.
- [x] Replace the onboarding and Settings raw selects with one searchable, grouped, keyboard-accessible Reka UI combobox.
- [x] Detect the device timezone through the browser/NativePHP `Intl` runtime, preserve UTC fallback, and never overwrite an explicit user choice.
- [ ] EN/LT/RU phone/tablet/desktop browser verification and the full quality/data-safety gates pass; complete the scoped commit and normal `origin/main` push after Tasks 20-21.

Detailed acceptance criteria, execution order, and rollback are recorded in `docs/superpowers/plans/2026-08-17-localized-timezone-preferences.md`. No schema, route, policy, external API, package, or stored timezone format change is planned.

## Sutelio Full Brand Rename — 2026-08-17

Requirements: `sys-brand-001`, `ui-accessibility-001`, `i18n-001`, `test-feature-001`, `ops-deployment-001`, `docs-traceability-001`, `git-delivery-001`.

- [x] Establish deterministic clean-S, one-color wordmark, favicon/raster, adaptive/monochrome Android, splash, configuration, package-metadata, storage-name, and EN/LT/RU source contracts under the Sutelio identity.
- [x] Make `npm run brand:native` reproduce canonical Android/iOS identity and assets after a fresh NativePHP install, with complete preflight, containment, idempotence, and handled-failure rollback coverage.
- [x] Synchronize active repository instructions, contributor/release entry points, requirement traceability, frontend/design/accessibility/localization/testing/deployment ownership, and current evidence while retaining historical audits/plans/progress.
- [x] Run the complete Task 9 application, static, frontend, data-safety, dependency, build, and delivery gates.
- [x] Complete successor Task 18 browser/visual verification in the fixed light mode, including zoom, focus, reduced motion, forced colors, metadata, favicon, and shared logo consumers.
- [x] Run successor Task 19 complete application, dependency, build, and isolated data-safety gates after the design work; retain the completed Task 9 result as its own earlier evidence rather than presenting it as a Task 19 execution.
- [x] Build, independently inspect, name, clean-install, and exercise the final Sutelio Android APK in the emulator in successor Task 20; record artifact and emulator evidence only after it is observed.
- [x] Rename the existing GitHub repository, checkout, and Herd site in place and verify `goleaf/sutelio` plus `sutelio.test` in successor Task 21; do not create a replacement repository or rewrite history.
- [ ] Install and verify the final Task 20 artifact on the connected Samsung in successor Task 22 only after Tasks 18-21, Git delivery, and the repository/Herd rename are complete; keep this as the last mutating action.

The external Android `applicationId` and primary iOS bundle identifier are `com.goleaf.sutelio`; Android's internal NativePHP/JNI namespace remains `com.nativephp.mobile` and must not be renamed. This active contract supersedes contrary namespace language in historical brand specifications/plans. The application is also fixed light-only after the delivered appearance-removal phase; older dark/system verification language is historical and does not define a current runtime theme family.

## SQLite Database Optimization — 2026-08-17

Requirements: `data-integrity-001`, `data-sqlite-001`, `perf-query-001`, `test-feature-001`, `docs-traceability-001`, `git-delivery-001`.

- [x] Audit the complete live SQLite schema, data-quality aggregates, migrations, models, request query objects, health state, query budgets, and query plans without mutating the real local database.
- [x] Add failing-first schema coverage and an additive reversible migration for the proven `workspaces.owner_id` and `workspace_invitations.invited_by` relation-index gaps.
- [x] Verify fresh migration, rollback, forward reapplication, SQLite health, FK integrity, and both changed plans on an isolated file-backed database.
- [ ] Enforce projections and bounded collection contracts one request query at a time with payload/query-budget evidence.
- [ ] Remove application-authored raw request-query expressions subsystem by subsystem, including a first-class indexed notification kind where the domain requires it.
- [ ] Benchmark the ten strict-prefix index candidates on production-shaped 1k/10k/100k fixtures before approving any removal.
- [ ] Add evidence-threshold database growth/maintenance observability, then run the final complete data and delivery gate.

The dependency order, file-level tasks, exact commands, acceptance criteria, and prohibited speculative optimizations are recorded in `docs/superpowers/plans/2026-08-17-sutelio-database-optimization.md`.

## Global Operation Feedback — 2026-08-17

Requirements: `ui-system-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-motion-001`, `i18n-001`, `test-feature-001`, `test-static-001`, `git-delivery-001`.

- [x] Inventory Inertia forms, router visits, standalone `useHttp` requests, existing local spinners, and background-request classes; reject per-page duplication and router-only coverage.
- [x] Add failing-first frontend behavior and Pest source/localization contracts for overlap safety, progress, request classification, cleanup, opacity, blocking, accessibility, and EN/LT/RU parity.
- [x] Implement one token-safe `globalBusy` controller, Inertia lifecycle binding, configured standalone HTTP wrapper, and root teleported `GlobalBusyOverlay` without a new dependency.
- [x] Preserve action-specific local feedback while excluding prefetch, polling, deferred/partial background refreshes, infinite scroll, and Precognition validation from the application lock.
- [x] Record the permanent JavaScript rule and synchronize frontend, design-system, accessibility, localization, testing, NFR, compliance, and progress documentation.
- [x] The focused/full/static/dependency/data/build gates and independent Chrome DevTools and Playwright browser matrix pass; complete the semantic commit and normal `origin/main` push after Tasks 20-21.

Detailed design, execution order, rollback, and acceptance criteria are recorded in `docs/superpowers/specs/2026-08-17-global-operation-feedback-design.md` and `docs/superpowers/plans/2026-08-17-global-operation-feedback.md`. No schema, route, policy, API shape, package, lock-file, or database change is planned.

## Soft Motion And Icon Remediation — 2026-08-17

Requirements: `ui-system-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-motion-001`, `test-feature-001`, `test-static-001`, `sys-brand-001`, `git-delivery-001`.

- [x] Replace generic route/Card/static-list entrances with shared high-coverage, low-amplitude motion primitives and bounded, non-replaying collection choreography.
- [x] Establish `IconTile`, `LeadingIconHeading`, and `WorkspacePageHeader` as the icon-surface ownership chain and apply the orange action/completion versus cobalt structure/information role split.
- [x] Remediate task, calendar, activity, notification, settings, administration, shell, command-palette, empty-state, onboarding, project, and workspace consumers without changing routes, queries, schema, dependencies, or persisted entity colors.
- [x] Add failing-first and repository-wide source contracts for shared ownership, reduced motion, forced colors, accessible icon controls, bounded stagger, and intentional presentation exclusions.
- [x] Pass the focused 55-test / 2,879-assertion motion gate, 56 frontend tests, Vue type checking, ESLint, Prettier, and the production Vite build.
- [x] Complete the independent Task 18 Chrome DevTools and Playwright matrix and Task 19 complete gates.
- [x] Complete Task 20 APK/emulator verification.
- [x] Complete Task 21 in-place repository/Herd rename.
- [ ] Complete final Task 22 Samsung installation.

The implementation follows `docs/superpowers/plans/2026-08-17-sutelio-soft-motion-ui-remediation-master-plan.md`. Physical-device installation remains the final mutation after source delivery, APK inspection/emulator proof, and the canonical repository/Herd rename.
