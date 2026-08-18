# Production Modernization Implementation Plan

This is the living plan as of 2026-08-18. Source code, migrations, routes, tests, the live SQLite schema, canonical requirements, and the latest `docs/progress.md` entries outrank historical planning material.

## Completed Delivery Baseline

The application modernization, integrated UI/UX program, EN/LT/RU browser matrix, complete repository quality gates, Android APK/emulator delivery, in-place GitHub/Herd rename, Android 10 compatibility correction, and physical Samsung Task 22 verification are complete.

The final verified debug artifact is `storage/app/native-build/sutelio-android-debug.apk` at SHA-256 `e827ee3eed48f44151494be5541b6e93ec9a5e3983f57d9f0f868ed29f9b4408`. It was installed successfully as `com.goleaf.sutelio` on the explicitly resolved Samsung SM-A920F running Android 10 / API 29. Russian locale persistence, resumed activity state, SQLite integrity, foreign keys, all 38 migrations, and process-scoped fatal/ANR/sensitive-log checks passed.

Completed execution-plan files are removed after their durable decisions and verification evidence are represented by canonical documentation and the append-only progress journal. Git history remains the recovery source for those deleted plans.

## Active Database Optimization

Requirements: `data-schema-001`, `data-sqlite-001`, `perf-query-001`, `perf-payload-001`, `perf-cache-001`, `ops-sqlite-001`, `test-feature-001`, and `test-static-001`.

The detailed execution source is `docs/superpowers/plans/2026-08-17-sutelio-database-optimization.md`. It is the only retained implementation plan with unfinished product work.

| Task                                                    | Status               | Evidence / next boundary                                                                |
| ------------------------------------------------------- | -------------------- | --------------------------------------------------------------------------------------- |
| 1. Add missing user-relation index coverage             | Completed and pushed | Commit `4357ff5` (`perf(database): index user-owned relations`)                         |
| 2. Enforce explicit projections and bounded collections | In progress          | Project index projection is GREEN; bounded collection and remaining query slices follow |
| 3. Remove raw activity query hints                      | Planned              | Replace application-authored hints only after production-shaped plan comparison         |
| 4. Normalize notification kind                          | Planned              | Add first-class indexed data with populated-data-safe backfill and legacy fallback      |
| 5. Remove remaining raw ordering and aggregates         | Planned              | Select bounded Eloquent/framework alternatives from measured evidence                   |
| 6. Benchmark strict-prefix indexes                      | Planned              | Compare 1k/10k/100k fixtures before any index removal                                   |
| 7. Add data-growth and maintenance observability        | Planned              | Add non-sensitive metrics and evidence thresholds before maintenance automation         |
| 8. Run final data gate and delivery                     | Planned              | Complete quality, migration, SQLite, performance, diff, commit, and push gates          |

Tasks 2-8 must be delivered incrementally. They may not weaken workspace isolation, SQLite-only support, query budgets, migration reversibility, private-path boundaries, or existing behavior to satisfy a static rule.

## External And Environment Limitations

No unfinished repository implementation is hidden in this section. The remaining external limitations are tracked only in `docs/known-limitations.md`: previous-package private-data migration is not an approved feature, official NativePHP PHP-runtime documentation still trails observed runtime metadata, this PHP installation has no Xdebug/PCOV coverage driver, production mobile signing credentials are unavailable locally, and the full Apple simulator/device toolchain is absent.

## Delivery Rule

Each active database task updates focused tests, canonical traceability, and `docs/progress.md`; runs the smallest relevant gates after each slice and the complete required gates before delivery; preserves unrelated work; commits only attributable files on `main`; and pushes `origin/main` without history rewrite or force push.
