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

| Measurement          | Baseline                                                               | Final                                               | Interpretation                                                                               |
| -------------------- | ---------------------------------------------------------------------- | --------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| PHP behavioral suite | 569 tests; 567 passed; 2 formatting-brittle failures; 3,387 assertions | 706/706 passed; 10,027 assertions                   | Adds complete modernization, feature-phase, and landmark architecture regressions            |
| Parallel PHP suite   | Not captured                                                           | 706/706; 10,027 assertions in 9.530 s               | Safe parallel execution confirmed                                                            |
| Vite build           | 3,429 modules in 9.78 s                                                | 3,494 modules in 4.39 s                             | Split feature modules remain within the baseline build-time envelope                         |
| Application CSS      | 158.29 kB / 23.61 kB gzip baseline output                              | 162.35 kB / 23.92 kB gzip main CSS                  | Current responsive feature states add 4.06 kB raw / 0.31 kB gzip across the completed phases |
| Application entry JS | 194.24 kB / 49.69 kB gzip                                              | 89.64 kB / 23.25 kB gzip; task index 39.73/10.08 kB | Page code remains split, with task workflows isolated in bounded dynamic chunks              |
| HTTP login           | Not captured                                                           | 200 in 0.123229 s; 59,313 bytes                     | Local Herd smoke, not a production latency benchmark                                         |
| Passkey discovery    | Not captured                                                           | 200 in 0.034043 s; 117 bytes                        | Public well-known endpoint boots and returns its bounded response                            |
| Browser route matrix | Not captured                                                           | 22 landmark checks, zero overflow/errors            | Eleven authenticated routes expose one `main`/`h1` at 1,440 and 390 px                       |

`PageQueryBudgetTest.php` passes for the dashboard and critical page queries. Activity uses workspace-leading actor/event indexes. Project detail uses workspace/project-leading status, priority, assignee, completion/deadline, due-date-order, and updated-order indexes; real generated sort SQL is checked with SQLite `EXPLAIN QUERY PLAN`. The notification inbox executes three bounded notification queries plus one batched authorized-task query for a full 20-row page, skips the paginated/resource query on stats-only partial reloads, and uses `notifications_notifiable_created_index` for the production-shaped unread-reminder plan. The priority-definition sort retains a bounded SQLite temporary sort because the user-defined priority position belongs to the related definition table, while the underlying project read remains index scoped. No product-data cache was introduced because these measured reads are bounded and do not justify invalidation/isolation complexity.

The first Task 2 projection slice replaces `projects.*` on the project index with the eight scalar/foreign-key columns consumed by that page plus the existing `todos_count` aggregate. On the same deterministic 24-project fixture, the resolved `projects` Inertia prop changed from 9,394 to 8,026 bytes, a reduction of 1,368 bytes (14.6%); the empty prop remains 11 bytes and the complete request remains five queries. The shared `ProjectResource` conditionally omits `position` and `created_at` only when those attributes were intentionally not selected, while full API models retain the established response fields.

## 2026-08-17 SQLite Relation-Index Evidence

The database audit covered the complete migration/model/query-object surface and the configured local SQLite schema. The real local file was inspected read-only and excluded from migration or load testing because concurrent browser work was changing its disposable identity/session rows.

| Query shape                                   | Before                       | After isolated migration                                                                         | Result                                                          |
| --------------------------------------------- | ---------------------------- | ------------------------------------------------------------------------------------------------ | --------------------------------------------------------------- |
| `workspaces` by `owner_id` with one-row limit | `SCAN workspaces`            | `SEARCH workspaces USING INDEX workspaces_owner_id_index (owner_id=?)`                           | Owner lookup and user-FK maintenance gain direct index access   |
| `workspace_invitations` by `invited_by`       | `SCAN workspace_invitations` | `SEARCH workspace_invitations USING INDEX workspace_invitations_invited_by_index (invited_by=?)` | Inviter lookup and user-FK maintenance gain direct index access |

The additive migration raises the fresh-schema explicit-index count from 94 to 96. A separate file-backed database passed fresh migration, two deterministic seed runs, populated rollback of only this migration, forward reapplication, quick/FK checks, and the application SQLite health command. Focused schema, query-budget, and runtime-health coverage passes 39 tests / 229 assertions with no page query-count increase.

Ten strict left-prefix indexes remain benchmark candidates, not approved removals. The execution plan requires identical 1k/10k/100k read/write fixtures before any consolidation. Raw activity/notification/order/aggregate expressions and unbounded or wildcard request reads are assigned to later endpoint-sized tasks so each behavioral and query-budget contract can be proven independently.
