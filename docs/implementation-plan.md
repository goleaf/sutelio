# Production Modernization Implementation Plan

This is the living plan as of 2026-08-18. Source code, migrations, routes, tests, the live SQLite schema, canonical requirements, and the latest `docs/progress.md` entries outrank historical planning material.

## Completed Delivery Baseline

The application modernization, integrated UI/UX program, EN/LT/RU browser matrix, complete repository quality gates, registration/onboarding minimum-workspace invariant, Android APK/emulator delivery, in-place GitHub/Herd rename, Android 10 compatibility correction, and physical Samsung Task 22 verification are complete.

The current NativePHP 4.2 artifact is `storage/app/native-build/sutelio-android-debug.apk` at 127,341,720 bytes and SHA-256 `76142d0cdd55a41001f3548785c65e63b003fd153929166895295a6e2f872d4f`. Its package, SDK, signature, alignment, archive, bundle-exclusion, mandatory migration/route contract, cold-launch, process-log, SQLite integrity, foreign-key, and 39-migration gates pass on an isolated Android 14 emulator. The exact installed emulator `base.apk` matched byte-for-byte; first run, registration, default-workspace creation, navigation-free onboarding, direct protected-route/API containment, and a final zero-user application-data reset all passed. Repository publication and installation of this new artifact on the sole authorized Samsung SM-A920F remain the final active delivery boundary; the prior physical evidence belongs to the superseded responsive artifact and is retained in `docs/progress.md`.

Completed execution-plan files are removed after their durable decisions and verification evidence are represented by canonical documentation and the append-only progress journal. Git history remains the recovery source for those deleted plans.

## Active Mandatory Onboarding Route Containment

Requirement: `sys-onboarding-001`.

| Task                                                                           | Status      | Evidence / next boundary                                                                                                                   |
| ------------------------------------------------------------------------------ | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| 1. Inventory every web/API escape path and authenticated shell                 | Completed   | Existing main/settings gate, uncovered Fortify/passkey/API paths, and inherited sidebar layout identified                                  |
| 2. Add failing-first route and layout contracts                                | Completed   | Direct web recovery, route completeness, versioned API, logout, and navigation-free layout regressions proved red then green               |
| 3. Centralize web/API containment and remove onboarding navigation             | Completed   | Shared pre-throttle/pre-binding middleware, exact support-route allowlist, stable EN/LT/RU error, and dedicated Inertia layout implemented |
| 4. Verify real browser behavior                                                | Completed   | Chrome and Playwright pending-user probes pass at 390×844, 844×390, and 1440×900; Results completion alone releases the gate               |
| 5. Run full repository, NativePHP, emulator, publication, and Samsung delivery | In progress | Quality, documentation, artifact, and clean-emulator gates pass; scoped commit/push equality and physical Samsung remain                   |

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
