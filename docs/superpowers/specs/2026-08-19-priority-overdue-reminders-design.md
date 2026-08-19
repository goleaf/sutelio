# Priority-Aware Overdue Reminders Design

## Purpose

Automatically remind the responsible user when a scheduled task is overdue and
still incomplete. Reminder urgency and cadence follow an explicit task-priority
policy, while grouping lower-priority work prevents notification storms. The
system reuses Sutelio's existing scheduler, queue, reminder delivery lifecycle,
notification inbox, user channel preferences, workspace authorization, and
EN/LT/RU localization.

This design depends on the task schedule contract in
`2026-08-19-profile-onboarding-calendar-delivery-design.md`: exact-time tasks
have `due_at` and `due_timezone`, during-the-day tasks retain `due_date` without
an invented time, and unscheduled tasks have neither deadline.

## Observed Baseline

- Users can create manual email, browser, or in-app reminders for an exact
  future instant.
- `reminders:send` runs every minute, atomically claims bounded candidates, and
  dispatches unique jobs on the `notifications` queue. Delivery records retry
  metadata and terminal status.
- Browser and in-app reminder delivery create database notifications; browser
  notifications can surface live only while the site or WebView is open.
- User preferences independently enable email, browser, and in-app channels.
- Tasks already expose an overdue scope, but no process converts an incomplete
  overdue task into an automatic reminder.
- Task priorities are workspace-owned definitions. They can be renamed,
  reordered, archived, or replaced, so localized names and list positions are
  not a stable reminder policy.
- Completion is represented by the workspace-authorized status transition and
  `completed_at`. Tasks may also be archived, soft-deleted, rescheduled,
  reprioritized, reassigned, or regenerated as recurring occurrences.

## Considered Approaches

### Repeat every overdue task independently

This is the smallest backend change and gives each task a direct destination.
It is rejected as the general policy because a user with many overdue tasks can
receive a burst every time the scheduler runs or the cadence window opens.

### Send one fixed daily digest

A single digest is quiet and bounded, but it delays urgent and high-priority
work and does not satisfy the expectation that a critical missed deadline is
reported promptly.

### Use a priority-aware hybrid

The selected approach sends individual urgent and high-priority notifications
and groups medium and low-priority work by recipient, workspace, priority level,
and cadence window. Every occurrence has a database uniqueness key, so repeated
scheduler runs, queue retries, or concurrent processes cannot create duplicate
delivery rows.

## Reminder Eligibility And Ownership

A task is eligible only when all of these conditions are true:

- it is not completed, archived, or soft-deleted;
- it has an exact-time or during-the-day schedule;
- its overdue boundary is earlier than or equal to the current instant;
- automatic overdue reminders are enabled for the recipient;
- automatic reminders for this task are not explicitly paused or snoozed.

For exact-time tasks, the overdue boundary is canonical `due_at` in UTC. For a
during-the-day task, the boundary is the start of the next calendar day in the
recipient's configured IANA timezone. No `00:00` execution time is stored or
shown for a during-the-day task. Unscheduled tasks never enter this system.

The recipient is the current assignee. An unassigned task falls back to the
workspace owner so it still has one accountable recipient. The recipient must
remain an authorized workspace member at generation and delivery time. A task
never fans out automatically to every workspace member.

## Priority Policy

Each `task_priorities` row gains a non-null `reminder_level` with the values
`low`, `medium`, `high`, or `urgent`. The value is independent of the priority's
name, translation key, color, and position.

Existing standard priorities migrate as follows:

| Priority key | Reminder level |
| ------------ | -------------- |
| `none`       | `low`          |
| `low`        | `low`          |
| `medium`     | `medium`       |
| `high`       | `high`         |
| `urgent`     | `urgent`       |

Existing custom priorities and newly created priorities default to `medium`.
Authorized workspace managers can change the reminder level in the existing
priority-management surface. Reordering or renaming a priority never silently
changes its reminder behavior.

The initial cadence is fixed and versioned in one policy class:

| Reminder level | Initial occurrence      | Later occurrences                                | Presentation               |
| -------------- | ----------------------- | ------------------------------------------------ | -------------------------- |
| `urgent`       | At the overdue boundary | After 1 hour, after 4 hours, then daily at 09:00 | Individual                 |
| `high`         | At the overdue boundary | Next local 09:00, then daily at 09:00            | Individual                 |
| `medium`       | Next local 09:00        | Every 2 days at 09:00                            | Recipient/workspace digest |
| `low`          | Next local 09:00        | Every 7 days at 09:00                            | Recipient/workspace digest |

Automatic delivery observes fixed quiet hours from 21:00 through 08:00 in the
recipient's timezone. An occurrence inside quiet hours moves to 08:00 without
changing its escalation stage. Manual reminders retain the exact instant chosen
by the user. Daylight-saving gaps and overlaps resolve through the same shared
timezone schedule service as task deadlines.

## Persistence Contract

### Task priority definitions

A reversible SQLite-compatible migration adds `reminder_level` to
`task_priorities`, backfills standard/custom definitions deterministically, and
adds only indexes supported by current query evidence.

### User preferences

`user_preferences` gains:

- `automatic_overdue_reminders`, defaulting to `true`;
- `automatic_overdue_email`, defaulting to `false`.

The master setting controls generation. Automatic email requires both the new
opt-in and the existing email-channel preference. Database/browser delivery
uses the existing in-app and browser preferences. No automatic reminder sends
to a disabled channel.

### Overdue reminder state

A new `overdue_reminder_states` table stores one current state per task:

- task, workspace, and recipient identifiers;
- a server-generated schedule fingerprint;
- resolved reminder level and escalation stage;
- `next_remind_at`, `last_reminded_at`, and the last occurrence key;
- nullable `snoozed_until` and `paused_at`;
- timestamps and SQLite-safe foreign keys.

The schedule fingerprint covers the task identifier, recipient, due boundary,
and reminder level. A due, assignee, or priority change is therefore detected
without trusting browser state. Rescheduling resets stage and snooze. An
explicit pause survives due or priority edits for the same assignee, while
reassignment clears pause and snooze for the new recipient.

### Automatic reminder occurrences

The existing `reminders` table gains a `source`, nullable `occurrence_key`,
nullable `priority_level`, and nullable `escalation_stage`. `source` is one of
`manual`, `overdue_individual`, or `overdue_digest`; existing rows backfill to
`manual`. A composite unique constraint on occurrence key and delivery type
prevents duplicate automatic channel rows while permitting existing manual
reminders.

Urgent and high occurrences link to one task. Medium and low digest occurrences
link to all included tasks through the normalized `overdue_reminder_items`
pivot, uniquely keyed by reminder and task. The digest stores no physical
paths, secrets, or unbounded serialized task payload.

For one automatic occurrence, generation creates at most one database-backed
channel row: browser when enabled, otherwise in-app. Browser rows remain visible
in the inbox and may also surface live. A separate email row is created only
when automatic email is opted in. This prevents duplicate inbox rows when both
browser and in-app preferences are enabled.

## Backend Flow

### Policy and reconciliation

`OverdueReminderPolicy` is a pure, timezone-aware service that receives an
overdue boundary, reminder level, current stage, and current instant, then
returns the next eligible occurrence. It contains no Eloquent queries and is
covered with deterministic clock and DST tests.

A focused reconciliation action reads explicit task, priority, recipient, and
preference fields in bounded chunks. It creates or updates state only through
workspace-scoped Eloquent relations. A lightweight task observer invalidates or
stops state after committed task lifecycle changes; it does not send
notifications or perform unbounded work.

### Generation

A scheduled command runs every minute with `withoutOverlapping`. It first
reconciles overdue candidates, then claims due state rows with a bounded limit
and an optimistic compare-and-update contract compatible with SQLite. Urgent
and high states create individual occurrences. Medium and low states are grouped
by recipient, workspace, reminder level, and cadence window before one digest
occurrence is created.

State advancement and occurrence creation share a short transaction. The
database unique key is the final idempotency boundary; cache locks are not the
source of truth and Redis is not required.

### Delivery

Automatic occurrences reuse the existing reminder claim, retry, queue, and
terminal-status pipeline. Immediately before delivery, the action rechecks the
recipient's membership, preferences, and every linked task. Completed,
archived, deleted, rescheduled, reassigned, paused, or no-longer-overdue tasks
are removed from the occurrence. An empty occurrence is cancelled.

Individual content includes task title, localized priority, due boundary, and
overdue duration. Digest content includes the count and a bounded preview, then
links to the authorized workspace task list filtered to overdue work. Server
code constructs destinations only after batched authorization; notification
payloads never supply an arbitrary URL.

Marking a notification read does not advance or stop overdue escalation. Task
completion, archiving, deletion, or explicit pause does. Uncompleting an already
overdue task starts a new cycle for the current schedule.

## Frontend Flow

The Notifications settings page adds a master automatic-overdue switch and a
separate automatic-email opt-in. Existing channel switches remain authoritative,
and copy states honestly that browser delivery requires an open browser/WebView.

The task reminder panel shows whether automatic overdue reminders are active,
the resolved priority policy, the next planned reminder, and the recipient. An
authorized user can Snooze for one day, Pause, or Resume. These mutations use
authorized Form Requests, focused actions, generated Wayfinder routes, and the
existing global request feedback. Manual reminder creation remains available.

Notification rows distinguish an overdue reminder from a manual reminder,
expose priority and overdue duration without relying on color, and use one safe
destination. Digest rows identify the workspace and task count. Empty, loading,
disabled, failure, and permission-loss states remain explicit.

## Localization And Accessibility

All visible and assistive text uses semantic PHP catalogs with exact EN/LT/RU
key and placeholder parity. This includes policy levels, cadence descriptions,
overdue durations, digest copy, settings, recipient text, Pause, Resume, Snooze,
and delivery errors.

Controls retain native semantics, visible focus, meaningful icons, 44-pixel
touch targets, non-color urgency cues, concise live regions, reduced-motion and
forced-colors support, and mobile layouts without horizontal overflow. Urgent
copy is direct but not alarmist.

## Error, Security, And Abuse Controls

- Every task, priority, recipient, state, and digest query remains workspace
  scoped. Foreign identifiers fail atomically.
- Repeated scheduler execution, concurrent claims, and queue retries cannot
  create duplicate occurrences for the same channel/window.
- Candidate and state scans are bounded and indexed. No Eloquent query occurs
  in Vue, a resource, policy, Blade view, or per-task rendering loop.
- Quiet hours, priority cadence, grouping, snooze, pause, and channel settings
  bound user-facing noise without treating a read notification as completion.
- Delivery failures retain sanitized bounded diagnostics. Credentials, email
  contents, session identifiers, and user data are not logged.
- No background push capability is claimed. This release uses inbox delivery,
  live browser notifications while open, and optional email only.

## Verification Contract

- Failing-first Pest coverage proves exact-time and during-the-day thresholds,
  no-deadline exclusion, every priority cadence, quiet hours, DST behavior,
  recipient fallback, preference/channel handling, and custom-priority policy.
- State tests cover initial creation, repeated reconciliation, rescheduling,
  reprioritization, reassignment, pause, resume, snooze, completion, uncompletion,
  archive, restore, soft deletion, and recurring occurrence isolation.
- Delivery tests cover individual and digest payloads, membership loss, partial
  digest cancellation, occurrence/channel uniqueness, repeated commands,
  concurrent claims, retries, and zero duplicate inbox notifications.
- Security tests use attacker/victim workspaces for state, pause/snooze actions,
  notification destinations, priority management, and API resources.
- Current SQLite schema inspection, candidate counts, `EXPLAIN QUERY PLAN`, and
  query-budget tests must support every new scan and grouping query.
- Frontend tests and disposable Chrome DevTools/Playwright contexts verify
  settings, task reminder controls, individual/digest inbox rows, focus, reflow,
  EN/LT/RU, browser permission states, console, network, and overflow.
- Pint, Larastan, focused/full Pest, fresh migrations, repeated seeding,
  frontend tests, Vue type checking, ESLint, Prettier, Composer validation/audit,
  npm audit, and the final normal Vite build must pass.
- Production verification proves the exact deployed SHA, scheduler execution,
  queue processing, idempotent generation, HTTPS health, SQLite integrity, and
  sanitized logs. Android verification proves inbox/WebView behavior without
  representing it as closed-app push delivery.

## Delivery Boundaries

This release does not add FCM, APNs, WebSockets, Redis, SMS, email verification,
another SQL server, a new frontend framework, or a third-party notification
package. It does not infer importance from translated priority names or silently
notify every workspace member. Manual reminders remain exact user-created
records and are not converted into automatic escalation rules.

The separate user-owned localization/Native-route worktree slice remains
unstaged and unchanged.
