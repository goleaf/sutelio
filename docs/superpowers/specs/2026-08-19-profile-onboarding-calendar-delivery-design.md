# Profile, Onboarding, Task Lists, And Calendar Delivery Design

## Purpose

Deliver one coherent Sutelio release that accepts private AVIF profile avatars,
keeps the workspace selected during onboarding active after completion, aligns
task-list creation controls on one row, and expands the calendar with a
vertical week, a project-grouped year summary, and a time-aware agenda. The
release must preserve SQLite-only operation, workspace isolation, EN/LT/RU,
the fixed light design, production automation, and NativePHP Android data.

## Observed Baseline

- Profile avatars are private and authenticated, but validation and the file
  input allow only JPEG, PNG, and WebP. Laravel 13's `image` rule does not
  include AVIF.
- Local PHP 8.5 identifies a real generated AVIF as `image/avif`, and
  `getimagesize()` returns its dimensions. Production capability must still be
  checked before activation.
- Registration provisions a minimum personal workspace. Onboarding can then
  select or create another workspace and creates its project and task there.
- `CompleteOnboarding` currently asks `EnsureUserHasWorkspace` for the oldest
  membership, clears onboarding state, and returns that workspace. The project
  and task remain stored but can appear missing because the session is switched
  away from their selected workspace.
- The two task-list creation rows use `flex-col sm:flex-row`, so their input and
  Add button stack on phone widths.
- Week view becomes two columns at `md` and seven columns at `2xl`.
- Calendar supports `month`, `week`, and 31-day `agenda` ranges. Tasks expose a
  date-only `due_date`, so agenda cannot show an honest execution time.
- No year view or bounded year-summary contract exists.
- The working tree contains a separate user-owned localization/Native-route
  slice. This delivery must not stage, rewrite, or discard it.

## Considered Approaches

### AVIF: trust extension or browser MIME

Adding only `.avif` to the input accepts chooser selections but does not create
a server security boundary. Trusting the client content type permits spoofed
files and is rejected.

### AVIF: transcode every upload

Transcoding would normalize output, but it introduces decoder and memory
requirements on web, production, and embedded PHP runtimes. The user asked to
upload AVIF rather than convert it, so this is unnecessary.

### AVIF: content-derived type plus dimension validation

The selected approach replaces the AVIF-incompatible `image` rule with
Laravel's content-derived file-type validation for JPEG, PNG, WebP, and AVIF,
retains an explicit extension allowlist, the 2 MiB limit, and the 4096 by 4096
dimension boundary, and keeps SVG prohibited. A real binary AVIF fixture proves
the complete request, private-storage, and authenticated-response flow.

### Onboarding: trust the current session

Keeping whatever `current_workspace_id` happens to be in the session avoids a
query but makes a mutable transport value authoritative and fails after session
loss. This is rejected.

### Onboarding: persist another current-workspace column

A new preference column would duplicate the existing session selection and the
server-authoritative onboarding state. It adds migration and synchronization
cost without fixing the source of truth.

### Onboarding: resolve the stored selected workspace

The selected approach reads `workspace_id` from the locked onboarding state,
resolves it through the completing user's memberships, and returns that
authorized workspace before clearing state. `EnsureUserHasWorkspace` remains a
legacy-safe fallback only when no valid stored selection exists. Existing
project/task creation and idempotency operations remain unchanged.

### Agenda time: reminders or task execution time

Reminder timestamps are delivery instructions, can be multiple per task, and
must not be presented as the task deadline. Created/updated timestamps are also
not planning data. The selected approach adds optional task execution time.

### Agenda schedule modes

An undated task, a task expected sometime during a calendar day, and a task due
at an exact time are different user intentions. Requiring a clock value would
make ordinary day planning unnecessarily precise, while silently converting a
date-only task to midnight would create false information.

The selected design exposes three explicit states without adding a redundant
database enum:

- no schedule: `due_date`, `due_at`, and `due_timezone` are absent;
- during the day: `due_date` is present while `due_at` and `due_timezone` are
  absent;
- exact time: `due_date`, `due_at`, and `due_timezone` are present.

The state is derived from those canonical fields at every read boundary. It is
not persisted independently, so it cannot drift out of sync.

### Agenda exact time: local `TIME` column or UTC instant

A local wall-clock column is ambiguous for collaborators in different time
zones and around daylight-saving transitions. The selected design stores an
optional UTC `due_at` plus the originating IANA `due_timezone`, while retaining
`due_date` for during-the-day and existing date-only behavior. Input is
interpreted in the authenticated user's configured timezone. Existing dated
rows remain during-the-day tasks without fabricated hours.

### Year view: return every task or return summaries

Returning all tasks for a year creates an unbounded response and duplicates the
month/agenda drill-down. The selected year view returns twelve month summaries
grouped by project. Each month exposes total, open, completed, and timed counts;
each project group exposes its identity, color, total, open, and completed
counts. `timed` counts only exact-time tasks; during-the-day tasks still
contribute to the other applicable metrics. Month and project links provide
detail without sending a year of task cards.

## Data And Migration Contract

Add nullable `due_at` and `due_timezone` columns to `todos` in a new reversible,
SQLite-compatible migration. Existing `due_date` values are not backfilled with
invented times. `due_timezone` is present only when `due_at` is present and is a
validated IANA identifier.

The task write boundary accepts an explicit schedule mode plus optional
`due_time` in `HH:mm` form. `none` clears all schedule fields,
`during_day` requires only `due_date` and clears both exact-time fields, and
`exact_time` requires both `due_date` and `due_time`. A shared schedule service
combines date, time, and authenticated-user timezone into a UTC instant and can
serialize an instant back into local date/time for Inertia and API resources.
Requests validate shape, while actions own the stored state.

Recurring tasks preserve the source wall-clock time in `due_timezone`: the
next occurrence date is combined with that time and converted to a new UTC
instant. During-the-day recurring tasks remain during-the-day. Duplicated or
imported tasks follow their existing scope rules and never receive fabricated
exact times.

Indexes continue to lead with workspace and archive boundaries. The final
index choice must be supported by a current SQLite query plan for agenda and
year range reads; no existing index is removed without measured evidence.

## Backend Flow

### Avatar

`ProfileAvatarUpdateRequest` validates actual file content, extension, size,
and dimensions. `UpdateProfileAvatar` keeps its existing private-disk,
replacement-cleanup, and rollback behavior. `ProfileController::avatar` serves
the stored AVIF with `image/avif`, private no-store caching, authentication, and
`nosniff` exactly as for the existing formats.

### Onboarding completion

Within the existing completion transaction, the locked preference row yields
the selected workspace identifier. The workspace is resolved through the
user's memberships and is returned before onboarding state is cleared. Invalid
or foreign state cannot switch context; the fallback only guarantees the
minimum membership invariant. The controller stores the returned identifier in
the session, so the first product query is scoped to the project/task workspace.

### Calendar

`CalendarIndexRequest` adds the `year` view. Its range is January 1 through
December 31 of the anchor year; previous/next shift exactly one year, and Today
returns to the current year without changing the selected view.

Normal month/week/agenda reads keep their explicit projection and eager-loaded
project/status/priority relations. Calendar task payloads add the viewer-local
`due_time`, a typed `schedule_mode` (`during_day` or `exact_time`), and the
canonical UTC `due_at`; physical paths or unrelated task data remain absent.

A focused year-summary query iterates explicit, workspace-scoped task fields in
bounded chunks and builds only twelve month aggregates plus project-group
aggregates. It does not query in Vue, a resource, a loop, or a Blade view, and
does not return task collections. Project metadata is eager loaded per chunk.

## Frontend Flow

### Profile avatar

The profile chooser advertises `.avif` and `image/avif` in addition to existing
formats. Object-URL preview, progress, errors, replacement, removal, and focus
behavior remain unchanged. EN/LT/RU helper text lists the real accepted formats.

### Task checklist rows

Both creation forms use a two-column grid:
`minmax(0, 1fr)` for the input and `auto` for the Add button. Inputs use
`min-w-0`; buttons never shrink, preserve their localized label and 48/52 px
touch height, and the containing card has no horizontal overflow. Existing
rename/reorder/delete toolbars are outside this change.

### Vertical week

`CalendarWeekView` always renders one chronological column of seven day cards.
No responsive breakpoint restores horizontal day columns. Day headers, counts,
task cards, empty states, today treatment, keyboard order, and localized dates
remain intact.

### Time-aware agenda

A shared task schedule field composes the existing `DatePickerField`. It offers
three explicit localized choices: no schedule, During the day, and Exact time.
During-the-day mode uses day granularity, writes `due_date`, and clears
`due_time`; exact-time mode uses minute granularity and writes both. Switching
back to during-the-day never retains a hidden clock value. The field is reused
by ordinary task creation, task detail editing, and onboarding task creation.

Agenda remains grouped by day. Within each day, exact-time tasks are ordered by
local time and rendered on a vertical time rail; tasks without hours are
grouped under the localized During the day label and never display `00:00`.
Equal times use the existing stable task order. Every visible exact time honors
the viewer's timezone and 12/24-hour preference. Status, project, priority, and
direct task navigation remain visible.

### Year view

`CalendarYearView` renders twelve month sections in chronological order, one
vertical reading flow. Each month header shows localized month/year and the four
summary metrics. Project groups are ordered by descending task count and then
localized name, and expose colored identity plus open/completed counts. Empty
months have a compact explicit empty state. Selecting a month navigates to its
month view; selecting a project opens its existing project page.

Phone, tablet, desktop, 200% reflow, coarse pointer, reduced motion, forced
colors, long Russian/Lithuanian copy, loading, empty, and disabled navigation
states remain usable without horizontal page overflow.

## Localization And Accessibility

All new visible and assistive copy uses semantic PHP catalogs with exact EN/LT/RU
key and placeholder parity. New copy includes AVIF help, schedule choices,
During the day, Exact time, Year, year metrics, project-group labels, and
empty-year/month states. No language JSON file is introduced.

Controls keep native buttons/inputs, explicit labels, visible focus, stable
heading hierarchy, chronological DOM order, non-color state cues, polite
navigation status, and meaningful icons with decorative instances hidden from
assistive technology. No runtime theme or email-verification behavior changes.

## Error And Security Behavior

- Spoofed AVIF, SVG, unsupported extensions, oversized files, and images beyond
  the dimension limit fail validation without storage or path disclosure.
- A foreign onboarding workspace identifier fails authorization/validation and
  never changes the session or clears valid selected state.
- Invalid schedule modes, exact time without date, invalid timezone,
  DST-invalid local time, and malformed year/date input fail with localized
  human validation messages. During-the-day tasks never store an exact time.
- Calendar queries remain scoped to the current authorized workspace. Year
  summaries never include foreign, archived, or undated tasks.
- No secret, credential, session identifier, private avatar path, or user data is
  added to logs, GitHub artifacts, the repository, or the NativePHP bundle.

## Verification Contract

- Failing-first Pest coverage proves real AVIF acceptance/serving, spoof and
  limit rejection, selected-workspace completion, creation and editing in all
  three schedule modes, exact-time validation and UTC conversion, clearing a
  previously exact time when switching to during-the-day, DST/timezone
  presentation, recurrence-mode preservation, year ranges/aggregates, workspace
  isolation, and bounded query behavior.
- Failing-first frontend/source coverage proves both checklist add rows are
  always one line, week has no multi-column breakpoint, year is offered by the
  navigator, task creation exposes the three schedule choices, and agenda
  exposes ordered exact-time/during-the-day groups without a false midnight.
- Pint, Larastan, the complete Pest suite, fresh migration and repeated seeding,
  frontend tests, Vue type checking, ESLint, Prettier, Composer validation/audit,
  npm audit, and a final normal production Vite build must pass.
- Disposable isolated Chrome DevTools and Playwright contexts verify profile,
  onboarding completion, task lists, and month/week/agenda/year calendar modes
  in EN/LT/RU across phone, tablet, desktop, reflow, console, network, focus, and
  horizontal-overflow boundaries.
- Scoped review inspects security, authorization, SQLite migrations, queries,
  accessibility, localization, and the full/staged diff. Only attributable
  files are committed to `main` and pushed normally.
- GitHub CI must pass and the dependent automated aaPanel deployment must
  activate the exact SHA. HTTPS `/up`, production SQLite health, worker,
  scheduler, logs, AVIF runtime handling, and responsive production pages are
  rechecked.
- NativePHP Mobile is regenerated only after web delivery gates. The final APK
  is inspected for package, SDK, signature, alignment, bundle exclusions, and
  hash; emulator E2E covers onboarding/calendar. A compatible, unique physical
  Samsung is updated with `adb install -r` only as the final mutation and never
  by uninstalling or clearing user data.

## Delivery Boundaries

This release does not add email verification, another SQL server, Redis,
Filament, Livewire, Vue Router, a new JavaScript package, an alternate CSS
pipeline, avatar transcoding, reminder-as-deadline semantics, or a task-heavy
year payload. The unrelated localization/Native-route worktree slice remains
unstaged and unchanged.
