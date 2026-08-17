# SQLite Database Audit — 2026-08-17

## Scope And Safety

This audit covers the configured SQLite connection, all 38 source migrations present after the first optimization migration was generated, all 17 first-party Eloquent models and factories, seeders, foreign keys, indexes, triggers, request query objects, query-budget tests, runtime health, and non-sensitive live row counts.

The real local database was read only. No row payload, email, token, credential, session payload, IP address, user agent, private path, or other personal/security-sensitive value was copied into this report. Counts are a volatile local-development snapshot: concurrent browser work changed identity, session, and cache rows during the audit. The local file has 36 applied migrations and intentionally remains behind the source schema; neither the pending theme removal nor the new relation-index migration was applied to it.

## Runtime Snapshot

| Property                 | Observed value                                                                                                                         |
| ------------------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| Database                 | SQLite 3.45.2 through Laravel 13.25 / PHP 8.5                                                                                          |
| Tables                   | 30 application/framework tables plus `migrations`                                                                                      |
| Source migrations        | 38 after creation of the relation-index migration                                                                                      |
| Applied local migrations | 36                                                                                                                                     |
| Explicit indexes         | 94 before the new migration; 96 in the fresh migrated schema                                                                           |
| Automatic SQLite indexes | 26                                                                                                                                     |
| Foreign keys             | 33 constraints covering 35 child columns                                                                                               |
| Integrity triggers       | 8                                                                                                                                      |
| File layout              | WAL, 4,096-byte pages, zero freelist pages at every sampled point                                                                      |
| Application health       | Path, FK enforcement, WAL, synchronous mode, busy timeout, cache size, temp store, auto-checkpoint, quick check, and FK check all pass |
| SQLite capabilities      | JSON1 and FTS5 are available; neither is by itself evidence to add a feature or index                                                  |
| Planner statistics       | No `sqlite_stat1`; automation is not justified without a measured planner regression                                                   |

The file grew from 238 to 245 pages while concurrent browser work was active. This confirms that the local file is unsuitable as a stable load-test fixture and that all destructive, migration, benchmark, seed, and rollback work must use isolated file-backed databases.

## Complete Table And Content Inventory

The row snapshot below was captured in one read-only statement. “Columns” records the current live schema, not the pending source state; therefore `user_preferences.theme` is still present locally.

| Table                    | Rows | Columns                                                                                                                                          |
| ------------------------ | ---: | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `activity_logs`          |    0 | `id`, `user_id`, `workspace_id`, `subject_type`, `subject_id`, `event`, `properties`, timestamps                                                 |
| `attachments`            |    0 | `id`, `todo_id`, `user_id`, `filename`, `path`, `mime_type`, `size`, timestamps                                                                  |
| `cache`                  |   17 | `key`, `value`, `expiration`                                                                                                                     |
| `cache_locks`            |    0 | `key`, `owner`, `expiration`                                                                                                                     |
| `checklist_items`        |    0 | `id`, `checklist_id`, `content`, `is_checked`, `position`, timestamps                                                                            |
| `checklists`             |    0 | `id`, `todo_id`, `name`, `position`, timestamps                                                                                                  |
| `comments`               |    0 | `id`, `todo_id`, `user_id`, `body`, timestamps, `deleted_at`                                                                                     |
| `failed_jobs`            |    0 | integer `id`, UUID, connection/queue, payload/exception, `failed_at`                                                                             |
| `job_batches`            |    0 | `id`, name, job counters, failed IDs/options, lifecycle timestamps                                                                               |
| `jobs`                   |    0 | integer `id`, queue/payload, attempts, reservation/availability/creation times                                                                   |
| `labels`                 |    0 | `id`, `workspace_id`, `name`, `normalized_name`, `color`, timestamps                                                                             |
| `migrations`             |   36 | integer `id`, migration name, batch                                                                                                              |
| `notifications`          |    0 | UUID `id`, type, notifiable morph, JSON data, read/created/updated timestamps                                                                    |
| `onboarding_operations`  |    0 | `id`, `user_id`, version, run, step, request/result identities, timestamps                                                                       |
| `passkeys`               |    0 | integer `id`, UUID user relation, name, credential identity/payload, usage/timestamps                                                            |
| `password_reset_tokens`  |    0 | email key, hashed token, creation time                                                                                                           |
| `personal_access_tokens` |    0 | integer `id`, UUID-compatible morph, name, hashed token, abilities, usage/expiry/timestamps                                                      |
| `projects`               |    0 | `id`, `workspace_id`, name/description, color/icon, archive/position, timestamps                                                                 |
| `reminders`              |    0 | IDs, schedule/type, delivery/claim/retry lifecycle, bounded error text, timestamps                                                               |
| `sessions`               |   33 | session ID, optional user ID, IP/user-agent, encrypted/serialized payload, last activity                                                         |
| `tags`                   |    0 | `id`, `workspace_id`, `name`, `normalized_name`, timestamps                                                                                      |
| `task_priorities`        |    0 | IDs, workspace/key/name/translation/color, position, default/archive flags, timestamps                                                           |
| `task_statuses`          |    0 | IDs, workspace/key/name/translation/color, position, default/completion/archive flags, timestamps                                                |
| `todo_label`             |    0 | `todo_id`, `label_id`                                                                                                                            |
| `todo_tag`               |    0 | `todo_id`, `tag_id`                                                                                                                              |
| `todos`                  |    0 | IDs/relations, text, legacy and normalized status/priority, dates/time, UI flags/position, recurrence identity/lifecycle, timestamps/soft delete |
| `user_preferences`       |    3 | IDs, locale/timezone/format/week/view/start preferences, notification flags, current live `theme`, onboarding lifecycle/state, timestamps        |
| `users`                  |    3 | UUID, name/email/password, remember/2FA secrets, avatar path, timestamps                                                                         |
| `workspace_invitations`  |    0 | IDs, workspace/inviter, email/role, token digest, expiry/accept/cancel lifecycle, timestamps                                                     |
| `workspace_members`      |    0 | `id`, workspace/user, role, timestamps                                                                                                           |
| `workspaces`             |    0 | UUID, name/slug/description, owner, timestamps                                                                                                   |

The only populated application-owned records at the snapshot are three disposable local users and their three preferences. Framework state contains 33 sessions and 17 cache rows. All workspace/task/project/taxonomy/comment/file/activity/notification/token/passkey/queue/onboarding-operation tables are empty. This is a structure and correctness audit, not evidence of production-scale performance.

## Relationship And Integrity Model

- User cascades: preferences, passkeys, owned workspaces, memberships, onboarding operations, comments, attachments, and reminders; invitation inviter and assigned task user use `SET NULL` where history/work should survive.
- Workspace cascades: membership, projects, task definitions, tasks, taxonomy, invitations; activity workspace is nullable with `SET NULL` for retained audit history.
- Todo cascades: checklist tree, comments, reminders, attachments, label/tag pivots; project and parent deletion use `SET NULL`, todo deletion uses soft deletion unless explicitly forced.
- Composite task-definition foreign keys bind each task status/priority to the same workspace. Two task-definition triggers also reject invalid cross-workspace definition links.
- Two parent triggers reject cross-workspace/self/cyclic parent writes. Four status/priority triggers preserve one default/completion-target semantic contract per workspace.
- Unique constraints protect users/email, workspace slug, membership, invitation workspace/email and token digest, workspace taxonomy names, definition keys/names/defaults, pivot pairs, recurrence occurrence identities, passkeys, API tokens, and onboarding run-step execution.
- The onboarding operation uniqueness contract is `(user_id, version, run_id, step)`. `request_key` is a stored operation input/audit value, not part of that unique tuple.

All sampled integrity checks returned zero anomalies: foreign-key violations, missing user preferences, orphan sessions, owner-without-owner-membership, owner role mismatch, invalid preference enum values, task definition/workspace mismatch, cross-workspace task parents, invalid label/tag assignment, malformed recurrence identity, and invalid reminder lifecycle state.

## Index Audit

### Proven Gap Implemented In This Phase

The following live query shapes used full scans and the child columns were not indexed for SQLite foreign-key maintenance:

| Child column                       | Active use                                                                                            | Before                       | Implemented fresh-schema plan                                                                    |
| ---------------------------------- | ----------------------------------------------------------------------------------------------------- | ---------------------------- | ------------------------------------------------------------------------------------------------ |
| `workspaces.owner_id`              | owned-workspace relation, backup policy, ownership transfer, notification authorization, user cascade | `SCAN workspaces`            | `SEARCH workspaces USING INDEX workspaces_owner_id_index (owner_id=?)`                           |
| `workspace_invitations.invited_by` | inviter relation and user `SET NULL` maintenance                                                      | `SCAN workspace_invitations` | `SEARCH workspace_invitations USING INDEX workspace_invitations_invited_by_index (invited_by=?)` |

### Existing High-Value Coverage

- Task request paths have workspace/project/archive/status/priority/assignee/due/updated/position composites plus recurrence-processing and occurrence uniqueness indexes.
- Activity has workspace/time plus workspace/actor/event/time composites.
- Notifications have notifiable identity and notifiable/created composites.
- Projects, definitions, taxonomy, invitations, reminders, jobs, sessions, cache, pivots, and lifecycle tables all have their current relationship, uniqueness, scan, or deterministic-order coverage.
- Reverse indexes exist on both taxonomy pivots, so label/tag deletion does not scan a composite keyed by task first.

### Benchmark-Only Consolidation Candidates

These ten explicit indexes are strict left prefixes of longer indexes and therefore candidates, not approved deletions:

1. `activity_logs_workspace_id_index`
2. `checklist_items_checklist_id_index`
3. `checklists_todo_id_index`
4. `labels_workspace_id_index`
5. `notifications_notifiable_type_notifiable_id_index`
6. `projects_workspace_id_index`
7. `tags_workspace_id_index`
8. `todos_project_id_index`
9. `todos_workspace_id_index`
10. `todos_workspace_id_is_archived_index`

Each candidate remains until identical production-shaped 1k/10k/100k fixtures prove that removal preserves query plans/latency while materially reducing write and storage cost. Automatic PK/unique indexes, FK-supporting reverse indexes, and indexes named by current query-plan tests are excluded from speculative removal.

## Query And Payload Findings

### Strong Existing Boundaries

- Complex reads are already concentrated in query objects/services and critical pages have strict query-count tests.
- Relationships used by current pages are generally eager-loaded; no request path uses unbounded `Model::all()`.
- Calendar ranges, task pages, notification pages, exports/imports, recurrence, and reminder processing are bounded or streamed/chunked.
- Strict Eloquent development guards and page architecture tests expose lazy loading and query-in-resource regressions.

### Optimization Backlog

1. Several query objects still rely on wildcard primary/eager-load payloads. Add exact projections while retaining relation keys, then measure Inertia bytes.
2. Project/member/invitation/taxonomy option collections require explicit domain bounds or pagination contracts rather than implicit “small forever” assumptions.
3. Activity uses `INDEXED BY` and raw conditional metrics; remove the hint only after production-shaped planner coverage proves the natural plan.
4. Notification semantic filtering reads `data.kind` through JSON SQL. Store/index a first-class nullable `kind`, backfill safely in chunks, and preserve malformed legacy fallback.
5. Dashboard/task/project/onboarding/workspace-management reads contain raw aggregate or ordering expressions. Replace one subsystem at a time without changing deterministic pagination or query budgets.
6. Maintenance lacks non-sensitive growth metrics and evidence thresholds. Add bytes/pages/freelist/WAL/pending-migration/row-band output before scheduling retention, optimize, or vacuum work.

## Prioritized Execution

| Priority    | Work                                          | Rationale                                                                          | Risk control                                                                                        |
| ----------- | --------------------------------------------- | ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| P0 complete | Add owner/inviter indexes                     | Proven scan and FK-maintenance gap; additive and reversible                        | RED/GREEN schema test, populated rollback test, isolated migrate/rollback/forward, health and plans |
| P1          | Exact projections and bounded reads           | Reduces memory/serialization and prevents scale-dependent response growth          | One endpoint per change, response schema plus query/payload budgets                                 |
| P1          | Remove raw request-query expressions          | Required engineering contract and portability/readability boundary                 | One subsystem per commit; behavior and plan equivalence                                             |
| P1          | Normalize notification kind                   | Removes JSON filter scan/expression and gives semantic schema ownership            | Nullable populated-safe backfill, legacy malformed-data tests, composite index                      |
| P2          | Benchmark prefix-index consolidation          | Potentially lowers write amplification/file size                                   | No deletion without identical scale fixtures and rollback evidence                                  |
| P2          | Add database-growth observability             | Makes future retention/planner maintenance evidence-driven                         | Non-sensitive thresholds in config; no HTTP-time maintenance                                        |
| P3 deferred | FTS/product cache/automatic vacuum/statistics | No current data or latency evidence; cache/FTS adds invalidation/storage contracts | Separate approved feature/operations design only after measurements                                 |

The executable task order, file map, commands, and acceptance criteria are in `docs/superpowers/plans/2026-08-17-sutelio-database-optimization.md`.
