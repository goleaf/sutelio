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
| PHP behavioral suite | 569 tests; 567 passed; 2 formatting-brittle failures; 3,387 assertions | 667/667 passed; 9,330 assertions                           | Adds project-operation filtering, isolation, partial-query, localization, lifecycle, query-plan, and frontend architecture coverage |
| Parallel PHP suite   | Not captured                                                           | 667/667; 9,330 assertions                                  | Safe parallel execution confirmed                                                                      |
| Vite build           | 3,429 modules in 9.78 s                                                | 3,477 modules in 4.11 s                                   | Project Operations adds focused modules while remaining within the baseline build-time envelope         |
| Application CSS      | 158.29 kB / 23.61 kB gzip baseline output                              | 159.22 kB / 23.65 kB gzip main CSS                         | Raw growth is 0.93 kB and gzip growth is 0.04 kB                                                        |
| Application entry JS | 194.24 kB / 49.69 kB gzip                                              | 87.25 kB / 22.73 kB gzip; project chunk 90.58/15.93 kB    | Page code is split into a reviewable project-detail chunk                                                |
| HTTP login           | Not captured                                                           | 200 in 0.093211 s; 51,046 bytes                           | Local Herd smoke, not a production latency benchmark                                                   |
| Passkey discovery    | Not captured                                                           | 200 in 0.034043 s; 117 bytes                              | Public well-known endpoint boots and returns its bounded response                                      |
| Browser route matrix | Not captured                                                           | 20 desktop/mobile checks, zero overflow/errors            | Critical page reflow and console safety confirmed at 1,440 and 390 px                                  |

`PageQueryBudgetTest.php` passes for the dashboard and critical page queries. Activity uses workspace-leading actor/event indexes. Project detail uses workspace/project-leading status, priority, assignee, completion/deadline, due-date-order, and updated-order indexes; real generated sort SQL is checked with SQLite `EXPLAIN QUERY PLAN`. The priority-definition sort retains a bounded SQLite temporary sort because the user-defined priority position belongs to the related definition table, while the underlying project read remains index scoped. No product-data cache was introduced because these measured reads are bounded and do not justify invalidation/isolation complexity.
