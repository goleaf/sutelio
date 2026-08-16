# Factories And Seeding

## Coverage

All 17 first-party Eloquent models have factories: ActivityLog, Attachment, Checklist, ChecklistItem, Comment, Label, Project, Reminder, Tag, TaskPriority, TaskStatus, Todo, User, UserPreference, Workspace, WorkspaceInvitation, and WorkspaceMember. There are no current factory exemptions.

Factories create valid minimal records by default, use explicit relationship helpers/states for larger graphs, respect UUID/FK/unique/workspace/enum/date rules, remain deterministic when explicit values are provided, and never fetch the public internet. Meaningful states—not every generic adjective—cover roles, workflow status, recurrence/reminder lifecycle, visibility/completion/archive/expiry/verification, optional data, Unicode, and date edges where the model supports them.

## Seeder Structure

`DatabaseSeeder` is production guarded and delegates to `DemoSeeder`, which orchestrates the following dependency-safe local/demo graph:

1. `UserSeeder`
2. `WorkspaceSeeder`
3. `ProjectSeeder`
4. `LabelSeeder`
5. `TagSeeder`
6. `TodoSeeder`
7. `ChecklistSeeder`
8. `CommentSeeder`
9. `ReminderSeeder`
10. `ActivityLogSeeder`
11. `NotificationSeeder`

Workspace task statuses/priorities and other fixed defaults use stable workspace/natural keys and idempotent writes. Seeders must never truncate unrestricted tables or erase production data. Demo users and documented non-production credentials may be created only in local/demo/testing environments and must be rejected in production.

The graph covers owner/admin/member permission boundaries, ownership/non-ownership, active/archived/completed/pending/overdue/recurring/reminder states, related projects/tasks/taxonomy/checklists/comments/activity/notifications, optional missing values, Unicode/long content, and page-ready empty/normal/high-volume samples where practical.

## Required Verification

- Every factory and meaningful state creates a constraint-valid record.
- A fresh test database migrates and `DatabaseSeeder` completes.
- Running reference/demo orchestration again does not duplicate fixed records or violate unique/FK constraints.
- Production safeguards reject demo-user creation.
- Seeded roles can authenticate and representative main pages render.
- Seeded files use a fake/local test disk and exist where expected.

Exact tests and final counts are recorded in `docs/compliance-matrix.md` and the final `docs/progress.md` entry.

Final verification covers 17 factory mappings, 17 default factory creates, 30 meaningful states, typed helper state, schema foreign keys, complete demo creation, repeat-run table-count identity, all three locales, owner/admin/member roles, and the production guard. A separate file-backed SQLite run migrated all 33 migrations, seeded twice, produced 3 users/1 workspace/25 tasks, and returned no foreign-key violations.
