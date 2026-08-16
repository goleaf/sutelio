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
| PHP behavioral suite | 569 tests; 567 passed; 2 formatting-brittle failures; 3,387 assertions | 706/706 passed; 10,027 assertions                          | Adds complete modernization, feature-phase, and landmark architecture regressions                       |
| Parallel PHP suite   | Not captured                                                           | 706/706; 10,027 assertions in 9.530 s                      | Safe parallel execution confirmed                                                                       |
| Vite build           | 3,429 modules in 9.78 s                                                | 3,494 modules in 4.39 s                                   | Split feature modules remain within the baseline build-time envelope                                    |
| Application CSS      | 158.29 kB / 23.61 kB gzip baseline output                              | 162.35 kB / 23.92 kB gzip main CSS                        | Current responsive feature states add 4.06 kB raw / 0.31 kB gzip across the completed phases            |
| Application entry JS | 194.24 kB / 49.69 kB gzip                                              | 89.64 kB / 23.25 kB gzip; task index 39.73/10.08 kB       | Page code remains split, with task workflows isolated in bounded dynamic chunks                          |
| HTTP login           | Not captured                                                           | 200 in 0.123229 s; 59,313 bytes                           | Local Herd smoke, not a production latency benchmark                                                    |
| Passkey discovery    | Not captured                                                           | 200 in 0.034043 s; 117 bytes                              | Public well-known endpoint boots and returns its bounded response                                      |
| Browser route matrix | Not captured                                                           | 22 landmark checks, zero overflow/errors                  | Eleven authenticated routes expose one `main`/`h1` at 1,440 and 390 px                                  |

`PageQueryBudgetTest.php` passes for the dashboard and critical page queries. Activity uses workspace-leading actor/event indexes. Project detail uses workspace/project-leading status, priority, assignee, completion/deadline, due-date-order, and updated-order indexes; real generated sort SQL is checked with SQLite `EXPLAIN QUERY PLAN`. The notification inbox executes three bounded notification queries plus one batched authorized-task query for a full 20-row page, skips the paginated/resource query on stats-only partial reloads, and uses `notifications_notifiable_created_index` for the production-shaped unread-reminder plan. The priority-definition sort retains a bounded SQLite temporary sort because the user-defined priority position belongs to the related definition table, while the underlying project read remains index scoped. No product-data cache was introduced because these measured reads are bounded and do not justify invalidation/isolation complexity.
