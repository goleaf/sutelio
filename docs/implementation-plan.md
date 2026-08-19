# Production Modernization Implementation Plan

This is the living plan as of 2026-08-18. Source code, migrations, routes, tests, the live SQLite schema, canonical requirements, and the latest `docs/progress.md` entries outrank historical planning material.

## Completed Delivery Baseline

The application modernization, integrated UI/UX program, EN/LT/RU browser matrix, complete repository quality gates, registration/onboarding minimum-workspace invariant, Android APK/emulator delivery, in-place GitHub/Herd rename, Android 10 compatibility correction, and physical Samsung Task 22 verification are complete.

The current NativePHP 4.2 pre-hardware artifact is `storage/app/native-build/sutelio-android-debug.apk` at 127,418,580 bytes and SHA-256 `bec86732239a43a91bee3b684274af13c92ccbb49646ed3aad465cb474608b09`. Its package, SDK, signature, alignment, archive, bundle-exclusion, rate-limit/page-start source contract, cold-launch, process-log, SQLite integrity, foreign-key, and 39-migration gates pass on a disposable Android 14 emulator. The exact installed emulator `base.apk` matched byte-for-byte, both cold launches passed, and the task-owned AVD was deleted after verification. Source/evidence publication and the final data-preserving Samsung SM-A920F update remain the only delivery gates for this artifact.

Completed execution-plan files are removed after their durable decisions and verification evidence are represented by canonical documentation and the append-only progress journal. Git history remains the recovery source for those deleted plans.

## Completed Senior-Friendly Responsive Program

Requirements: `ui-system-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-coherence-001`, `i18n-001`, and `test-architecture-001`.

The durable design source is `docs/plans/2026-08-18-senior-friendly-responsive-system-design.md`. The completed step-by-step execution file was deleted under the documentation retention rule after every task and verification result was synchronized into canonical documentation and `docs/progress.md`; Git history remains its recovery source.

| Task                                                         | Status    | Evidence / next boundary                                                                                              |
| ------------------------------------------------------------ | --------- | --------------------------------------------------------------------------------------------------------------------- |
| 1. Freeze audit and design contract                          | Completed | Repository/runtime inventory and selected comfort contract recorded                                                   |
| 2. Establish shared reading and touch primitives             | Completed | Failing-first source coverage and shared 48/52 px control baseline                                                    |
| 3. Adapt first run and authentication                        | Completed | EN/LT/RU phone/tablet/short-landscape browser matrix and Lighthouse accessibility 100                                 |
| 4. Move persistent navigation to the desktop boundary        | Completed | Authenticated 390-1440 px drawer/sidebar matrix; disposable data removed                                              |
| 5. Adapt mandatory onboarding                                | Completed | Reviewed implementation and browser/quality evidence pushed in `8b53e20`                                              |
| 6. Adapt tasks and projects                                  | Completed | Reviewed daily-work implementation pushed in `69f0f6c`                                                                |
| 7. Adapt dashboard, calendar, activity, and notifications    | Completed | Reviewed planning/activity implementation pushed in `834ccce`                                                         |
| 8. Adapt workspaces and settings                             | Completed | Reviewed workspace/settings implementation pushed in `47fd743`                                                        |
| 9. Consolidate semantic statuses and remove proven legacy UI | Completed | Verified semantic/legacy delivery pushed in `2dc44cf`                                                                 |
| 10. Run final quality, NativePHP, emulator, and Samsung gate | Completed | Exact artifact verified on Android 14 emulator and installed/running after a scoped clean reset on Android 10 Samsung |

## Completed Package-Based Color Picker

Requirements: `sys-onboarding-001`, `sys-project-001`, `sys-workspace-005`, `sys-task-007`, `ui-accessibility-001`, `ui-responsive-001`, `ui-system-001`, and `i18n-001`.

The maintained MIT `@zag-js/color-picker` and `@zag-js/vue` integration replaces every native HTML color input through one shared `ColorPickerField`. Onboarding project creation, project creation with presets, workspace label creation/editing, and status/priority creation/editing now share opaque HEX normalization, EN/LT/RU visible and assistive labels, keyboard/touch behavior, viewport-aware positioning, nested-dialog containment, reduced-motion suppression, and forced-colors boundaries. `FrontendColorPickerTest.php`, the related responsive/localization suites, complete quality gates, and disposable Chrome DevTools/Playwright checks are the durable implementation and verification boundary; this presentation-only delivery does not change routes, requests, queries, schema, migrations, configured application data, or NativePHP artifacts.

## Completed Passkey And System Localization Audit

Requirements: `sys-auth-001`, `sys-user-003`, `i18n-001`, `ui-accessibility-001`, and `test-feature-001`.

The shared passkey error adapter maps every official `@laravel/passkeys` error class to semantic EN/LT/RU copy and converts unknown package/server failures to a localized safe fallback instead of exposing raw exception text. Login checkbox accessibility follows the live locale explicitly, generated passkey names contain no English connector, every Laravel validation rule currently used by Sutelio has LT/RU coverage, and the Russian reminder-channel label is no longer an English copy. The strengthened localization suite checks every first-party catalog for key, non-empty value, placeholder, and suspicious English-copy parity; focused Node/Pest coverage and independent Chrome DevTools/Playwright cancellation checks are the durable regression boundary. This source-only audit does not change routes, requests, queries, schema, migrations, configured application data, NativePHP artifacts, or physical-device state.

## Completed Shared Project Icon Picker

Requirements: `sys-onboarding-001`, `sys-project-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-system-001`, and `i18n-001`.

One typed eight-option Lucide registry and `ProjectIconPicker` replace onboarding's free-text project-symbol input and the former local project-creation option array. The selected EN/LT/RU-labelled icon is visible beside each option and follows the project through onboarding previews, project collection cards, and the project detail header; unknown legacy/API/import strings resolve to a safe folder without changing the persisted or external contract. Focused Pest/frontend coverage and isolated Chrome DevTools/Playwright phone/tablet/desktop interaction checks form the regression boundary; this UI-only slice adds no dependency, query, route, request, schema, migration, configured database write, or mobile artifact.

## Completed Shared Package-Based Date Picker

Requirements: `sys-task-002`, `sys-task-008`, `ui-accessibility-001`, `ui-responsive-001`, `ui-system-001`, `i18n-001`, and `i18n-format-001`.

One shared `DatePickerField` built from Reka UI and the direct `@internationalized/date` dependency replaces every native browser date and date/time input across onboarding task creation, ordinary task creation, task overview editing, and reminder scheduling. It preserves `YYYY-MM-DD` and minute-precision `YYYY-MM-DDTHH:mm` contracts, EN/LT/RU field/calendar formatting, user week-start and time-cycle preferences, keyboard/touch behavior, 44/48 px targets, nested-dialog portal containment, reduced-motion/forced-colors styling, and bounded phone/tablet popovers. `FrontendDatePickerTest.php`, frontend value-adapter tests, and independent Chrome DevTools/Playwright checks are the durable regression boundary; the existing month/week/agenda planning page remains its own complete calendar and no route, request, query, schema, migration, configured application data, or mobile artifact changes.

## Completed Onboarding Icon Language

Requirements: `sys-onboarding-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-system-001`, and `i18n-001`.

One typed eight-step Lucide registry plus shared `OnboardingIcon` and `OnboardingFieldLabel` primitives give the complete onboarding journey one consistent icon language. Step headings and progress, field labels, mode choices, select triggers and every select/timezone option, notices, validation feedback, previews, and footer actions retain their localized text while receiving meaningful visual reinforcement. Decorative icon instances are hidden from assistive technology, selected/check state remains explicit, and the established keyboard, touch, focus, reduced-motion, forced-colors, and no-overflow contracts are preserved. `OnboardingIconLanguageTest.php`, related onboarding/design tests, and isolated Chrome DevTools/Playwright phone/tablet/desktop verification form the durable regression boundary; this presentation-only slice adds no dependency, route, request, query, schema, migration, configured database write, or mobile artifact.

## Onboarding Rate Limit And Page-Start Navigation

Requirements: `sys-user-003`, `sys-onboarding-001`, `ui-system-001`, `ui-responsive-001`, and `test-feature-001`.

The locale endpoint uses one named actor-isolated limiter: authenticated web actors receive their own user bucket, guests receive a hashed-session bucket, and the private NativePHP loopback bridge is not falsely throttled by the shared `127.0.0.1` transport address. Successful non-preserved foreground Inertia visits reset the document and every explicit scroll region after rendering; onboarding step changes, completion/replay exit, route navigation, workspace switching, and task pagination therefore begin at the page start, while deliberately preserved same-page mutations and filters retain context. Focused Pest/frontend regressions, full source gates, disposable Chrome DevTools/Playwright phone/tablet/desktop checks, and the isolated Android 14 APK proof are complete. Source publication and the final data-preserving Samsung update remain.

## Page-Based Record Management And Mobile Calendar

Requirements: `sys-user-001`, `sys-workspace-001`, `sys-project-001`, `sys-task-002`, `ui-accessibility-001`, `ui-responsive-001`, `ui-system-001`, and `i18n-001`.

Ordinary workspace/project/task create, edit, duplicate, and detail operations use dedicated Inertia pages instead of record-management `Dialog` or `Sheet` overlays. Destructive decisions for tasks, task children, projects, workspaces, members, security data, backups, and account deletion share the in-flow `PageConfirmPanel`, which remembers the initiating control, focuses its heading, restores connected initiator focus on close, and preserves explicit processing/typed-confirmation behavior without trapping the user in an overlay. Mandatory onboarding source and the permanent `GlobalBusyOverlay` remain unchanged; the first-run language chooser, passkey/two-factor security ceremonies, mobile navigation/filter sheets, package-backed color/select controls, and the calendar are specialized non-record surfaces.

The shared package-backed date picker explicitly controls its open lifecycle, closes after day/today selection while preserving an existing reminder time, marks locale/week-start-derived weekend headings and cells in Warm Precision styling, occupies the complete safe-area-aware phone viewport, and remains a bounded popover on tablet/desktop. Task creation returns at most 100 ordered active project choices plus the explicit authorized selection. `PageBasedRecordManagementTest.php`, `FrontendDatePickerTest.php`, the affected workspace/project/task/design suites, and independent Chrome DevTools/Playwright page, focus, bounding-box, autoclose, overflow, console, and network checks are the durable regression boundary. No schema, migration, dependency, seed, or onboarding contract changes.

## Active Database Optimization

Requirements: `data-schema-001`, `data-sqlite-001`, `perf-query-001`, `perf-payload-001`, `perf-cache-001`, `ops-sqlite-001`, `test-feature-001`, and `test-static-001`.

The detailed execution source is `docs/superpowers/plans/2026-08-17-sutelio-database-optimization.md`. Database optimization remains independent from the responsive program and must continue in reviewable slices without mixing ownership.

| Task                                                    | Status               | Evidence / next boundary                                                                     |
| ------------------------------------------------------- | -------------------- | -------------------------------------------------------------------------------------------- |
| 1. Add missing user-relation index coverage             | Completed and pushed | Commit `4357ff5` (`perf(database): index user-owned relations`)                              |
| 2. Enforce explicit projections and bounded collections | In progress          | Project index projection pushed in `69b0d66`; bounded collection and remaining slices follow |
| 3. Remove raw activity query hints                      | Planned              | Replace application-authored hints only after production-shaped plan comparison              |
| 4. Normalize notification kind                          | Planned              | Add first-class indexed data with populated-data-safe backfill and legacy fallback           |
| 5. Remove remaining raw ordering and aggregates         | Planned              | Select bounded Eloquent/framework alternatives from measured evidence                        |
| 6. Benchmark strict-prefix indexes                      | Planned              | Compare 1k/10k/100k fixtures before any index removal                                        |
| 7. Add data-growth and maintenance observability        | Planned              | Add non-sensitive metrics and evidence thresholds before maintenance automation              |
| 8. Run final data gate and delivery                     | Planned              | Complete quality, migration, SQLite, performance, diff, commit, and push gates               |

Tasks 2-8 must be delivered incrementally. They may not weaken workspace isolation, SQLite-only support, query budgets, migration reversibility, private-path boundaries, or existing behavior to satisfy a static rule.

## External And Environment Limitations

No unfinished repository implementation is hidden in this section. The remaining external limitations are tracked only in `docs/known-limitations.md`: previous-package private-data migration is not an approved feature, official NativePHP PHP-runtime documentation still trails observed runtime metadata, this PHP installation has no Xdebug/PCOV coverage driver, production mobile signing credentials are unavailable locally, and the full Apple simulator/device toolchain is absent.

## Delivery Rule

Each active task updates focused tests, canonical traceability, and `docs/progress.md`; runs the smallest relevant gates after each slice and the complete required gates before delivery; preserves unrelated work; commits only attributable files on `main`; and pushes `origin/main` without history rewrite or force push. Responsive work additionally runs the applicable EN/LT/RU viewport/input/state matrix and leaves physical Samsung installation as the final mutation of the complete program.
