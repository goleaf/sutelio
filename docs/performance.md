# Performance

Performance work is evidence driven. Cache is not a substitute for an inefficient query or unbounded response.

## Current Controls

- Focused query objects own dashboard, task/project index/detail, calendar, activity, notifications, and workspace-management reads.
- Queries start with workspace/user scope, select intentional columns, eager-load required relations, use counts/exists/aggregates, apply deterministic secondary ordering, and paginate.
- `PageQueryBudgetTest.php` guards critical page counts; model accessors and API Resources do not query.
- Exports stream with lazy queries; imports are bounded; scheduled recurrence/reminder work is chunked/claimed; no large production table uses `all()` in request paths.
- Tailwind/Vite production output and major asset sizes/build time are recorded at baseline/final. Static source detection prevents missing classes.

## Required Measurements

Release evidence compares critical query counts, N+1 results, test/migration/seed time, build time, CSS/JS sizes, and browser console/page errors. Inertia request count/payload is measured when a component or page is identified as heavy; speculative fragmentation is prohibited.

SQLite query improvements must include an actual scoped query and query-plan/budget rationale. New cache entries require `docs/caching.md` metadata and isolation/invalidation tests.

## 2026-08-16 Baseline And Final Evidence

| Measurement          | Baseline                                                               | Final                                                     | Interpretation                                                                                         |
| -------------------- | ---------------------------------------------------------------------- | --------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| PHP behavioral suite | 569 tests; 567 passed; 2 formatting-brittle failures; 3,387 assertions | 627/627 passed; 8,958 assertions in 27.067 s              | Broader architecture, activity, factory/seeder, NativePHP, and regression coverage; no failed behavior |
| Parallel PHP suite   | Not captured                                                           | 627/627; 8,958 assertions in 10.006 s                     | Safe parallel execution confirmed                                                                      |
| Vite build           | 3,429 modules in 9.78 s                                                | 3,468 modules in 5.15 s                                   | More application coverage with lower observed build time on the same workstation                       |
| Application CSS      | 158.29 kB / 23.61 kB gzip baseline output                              | 154.98 kB / 24.23 kB gzip plus 2.57 kB / 0.48 kB font CSS | Shared semantic tokens and activity UI remain within the same asset envelope                           |
| Application entry JS | 194.24 kB / 49.69 kB gzip                                              | 155.92 kB / 38.05 kB gzip                                 | 38.32 kB raw and 11.64 kB gzip reduction                                                               |
| HTTP login           | Not captured                                                           | 200 in 0.093211 s; 51,046 bytes                           | Local Herd smoke, not a production latency benchmark                                                   |
| Passkey discovery    | Not captured                                                           | 200 in 0.034043 s; 117 bytes                              | Public well-known endpoint boots and returns its bounded response                                      |
| Browser route matrix | Not captured                                                           | 20 desktop/mobile checks, zero overflow/errors            | Critical page reflow and console safety confirmed at 1,440 and 390 px                                  |

`PageQueryBudgetTest.php` passes for the dashboard and critical page queries. The activity query now filters with workspace-leading composite indexes on `(workspace_id, user_id, created_at, id)` and `(workspace_id, event, created_at, id)`, preserves deterministic ordering, selects bounded relation fields, and paginates through Inertia's infinite-scroll contract. No product-data cache was introduced because the measured reads are bounded and do not justify invalidation/isolation complexity.
