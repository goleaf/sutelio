# Domain Model

## Roles And Aggregate Boundaries

- `User` owns identity, security credentials, private avatar, preferences, notifications, API tokens, authored comments, reminders, and workspace memberships.
- `Workspace` is the tenant aggregate. Membership role is `owner`, `admin`, or `member`; exactly one effective ownership path must be preserved through role changes/removal/transfer.
- `Project` belongs to one workspace and groups tasks.
- `Todo` belongs to one workspace, optional project/assignee/parent, one status definition, and one priority definition. It owns checklists, comments, reminders, attachments, recurrence configuration/occurrences, and subtasks; it has workspace labels/tags through pivots.
- `TaskStatus` and `TaskPriority` are ordered workspace definitions used by tasks.
- `WorkspaceInvitation` is an expiring, digest-backed, single-use membership offer.
- `ActivityLog` records normalized facts for a workspace. Database notifications are private to their user.

## Primary Workflows And State Transitions

- Workspace: create -> configure -> invite/manage members -> optionally transfer ownership -> duplicate or delete with policy/confirmation.
- Invitation: issue/resend -> accept once before expiry, or cancel/expire.
- Project: active -> archived; duplicate and delete are explicit actions.
- Task: create/update -> complete/uncomplete, archive, favorite/pin, reorder, duplicate, or delete. Parent links cannot self-reference or cycle.
- Reminder: pending -> claimed -> delivered or failed; pending/failed items may be cancelled under policy. Claim/delivery is idempotent.
- Recurrence: configured parent generates bounded occurrences keyed by unique schedule occurrence identity.
- Backup: inventory/create -> private download or guarded restore/delete. Restore validates and retains rollback safety.
- Import: validate/preview without writes -> explicit transactional execution.

## Invariants

1. Every related project, assignee, parent, definition, label, tag, child, reminder user, attachment, and submitted bulk/reorder ID belongs to the authorized workspace/aggregate.
2. Exact set operations reject missing, duplicate, mixed, or foreign identifiers before mutation.
3. Task hierarchy is acyclic and same-workspace.
4. Ownership cannot be silently lost; non-owners cannot transfer or delete a workspace.
5. Recurrence occurrence identity and invitation acceptance are replay safe.
6. File/database operations define compensation or cleanup for partial failure.
7. User preferences affect presentation and boundaries, not stored canonical timestamps or authorization.

The table-level realization is in `docs/data-model.md`; permissions are in `docs/authorization.md`; requirement IDs are in `docs/requirements.md`.
