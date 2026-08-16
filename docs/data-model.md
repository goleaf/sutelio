# Data Model

The executable schema is the ordered migration set under `database/migrations`. This document summarizes ownership and integrity; it does not replace migration inspection.

## Identity And Framework Tables

| Tables                                                       | Ownership / notable integrity                                                                                                                                                                   |
| ------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `users`, `passkeys`, `user_preferences`                      | UUID users; passkeys/preferences cascade to their user; email is unique; preferences include presentation plus versioned onboarding run/step/state/lifecycle timestamps and checklist dismissal |
| `onboarding_operations`                                      | UUID operation ledger keyed uniquely by user + run UUID + request key; records operation kind and optional created entity identity for retry-safe guided creation                               |
| `personal_access_tokens`, `notifications`                    | UUID-compatible morph identifiers; tokens belong to users and expose hashed token material only                                                                                                 |
| `password_reset_tokens`, `sessions`                          | Framework identity/session lifecycle; session records use indexed user/last-activity fields                                                                                                     |
| `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Database-backed framework cache/locks/queue; no Redis requirement                                                                                                                               |

## Workspace Domain

| Tables                                     | Ownership / relationships / key constraints                                                                                                                                          |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `workspaces`                               | UUID owner relation and stable workspace identity                                                                                                                                    |
| `workspace_members`                        | workspace + user membership with scoped uniqueness and role values enforced by application enum/policy                                                                               |
| `workspace_invitations`                    | workspace/email/role, token digest, expiry, accepted/cancelled lifecycle, uniqueness/indexes for active lookup                                                                       |
| `projects`                                 | belongs to workspace; scoped indexes support active/archive and list queries                                                                                                         |
| `task_statuses`, `task_priorities`         | workspace-scoped ordered definitions with scoped unique names and protected/default semantics                                                                                        |
| `todos`                                    | workspace, optional project/assignee/parent, status/priority definitions; hierarchy and relation FKs; due/status/recurrence composite indexes; unique recurrence occurrence identity |
| `checklists`, `checklist_items`            | task -> checklist -> ordered item cascade with scoped ordering                                                                                                                       |
| `labels`, `tags`, `todo_label`, `todo_tag` | workspace-scoped unique names and same-workspace assignment enforced by application boundaries; pivot uniqueness prevents duplicate attachment                                       |
| `comments`, `reminders`, `attachments`     | task children with author/user ownership; reminder delivery lifecycle indexes; private attachment metadata                                                                           |
| `activity_logs`                            | workspace actor/subject event ledger; workspace/user/time/id and workspace/event/time/id composite indexes support the validated activity filters and deterministic pagination       |

## Schema Safety

- Corrective migrations align UUID foreign keys for workspace, task, passkey, notification, and Sanctum relations and are verified by foreign-key checks.
- Task-parent integrity prevents self-parent and cycles while the application also validates transitions.
- Foreign keys and unique constraints are the second line of defense; same-workspace invariants that span multiple parent tables are validated/authorized in application actions and transactions.
- Indexes were added from actual scoped query shapes and query-budget tests, not every column. Ordering prioritizes equality workspace/owner/status filters before range/order columns.
- Money is not a current domain value; no float/decimal monetary representation exists.
- Canonical timestamps are stored by Laravel/SQLite and formatted using user locale/timezone.
- The onboarding migration is populated-safe: existing preference rows are marked complete/dismissed, while real Fortify registrations explicitly start pending. Rollback removes only the added lifecycle columns/table.

## Migration Verification

Required release checks include an in-memory fresh migration, fresh seed, repeat/idempotency checks, `PRAGMA foreign_key_check`, the database health command, and representative populated upgrade/rollback when a new schema migration is introduced. Never run `migrate:fresh` against the local or production database.
