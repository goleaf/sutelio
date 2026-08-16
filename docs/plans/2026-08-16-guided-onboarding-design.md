# Guided Onboarding Design

## Status

Approved on 2026-08-16 after an interactive product-design review. This document defines the experience and architectural boundary; implementation details are sequenced in the companion implementation plan.

## Objective

Give every newly registered Xiaomi Mimo user a calm, practical introduction that leaves them with a configured account, an accessible workspace, a real project, and a real first task. The same Inertia flow must work in Laravel Herd and inside the embedded NativePHP Android application without a separate mobile client or remote API dependency.

The experience should teach by doing. It is not a marketing carousel and it does not manufacture demo data the user did not choose.

## Audience And Entry Rules

- A newly registered user is offered onboarding only after email verification and the first verified application entry.
- An existing user present when the feature is deployed is marked as already onboarded and is never interrupted automatically.
- A completed or skipped user can restart the journey from Settings at any time.
- Manual replay never clears the user's completed/skipped gate. Leaving a replay midway must return the user to normal application navigation rather than forcing the wizard on later requests.
- An invited user who already has workspace access skips workspace creation and continues with the workspace they can access.
- A user with no accessible workspace receives the workspace introduction and creation step.
- A global Skip action is always available as an escape hatch and records a server-side skipped state. Inside the guided path, required preference and ownership fields must be valid before continuing; optional education and creation choices can be skipped when a safe existing entity is available.
- Completion redirects to the user's selected start page, except the final primary action may open the first selected or created task directly.

## Considered Approaches

### Dedicated Inertia Journey — Selected

A dedicated `/onboarding` page owns progress, actions, validation, and recovery. It provides a stable mobile layout, clear focus management, resumability, and a trustworthy server-side state machine. It can reuse the existing preference, workspace, project, and task actions without coupling onboarding to the markup of unrelated pages.

### Cross-Page Overlay Tour — Rejected

An overlay can point at the live navigation but is fragile across responsive breakpoints, sheets, dialogs, long translations, and NativePHP viewport differences. It also produces difficult focus, route-transition, and recovery behavior.

### Dashboard Checklist Only — Rejected As The Primary Experience

A checklist is useful after onboarding but does not provide enough context or sequencing for a new user. A lightweight checklist remains as a post-completion continuation surface.

## Journey

The journey contains eight semantic steps. Step identifiers remain stable even if presentation is refined.

1. **Welcome** — explain the product value in one concise screen: capture work, plan it, and collaborate without losing ownership of local data.
2. **Personal setup** — choose language, timezone, date format, time format, default task view, week start, and start page. The screen updates its preview immediately while the server remains authoritative on save.
3. **Workspace** — explain the workspace boundary. Select an accessible workspace, accept the already selected invited workspace, or create the first workspace when none exists.
4. **First project** — select an existing active project or create a meaningful real project. The user can continue without creating another project if an existing one is suitable.
5. **First task** — create or select a real task and introduce title, status, priority, assignee, and due date using the chosen workspace definitions.
6. **Product map** — show the relationship between Dashboard, Tasks, Projects, Calendar, Activity, and Notifications with direct, localized descriptions rather than feature-name fragments.
7. **Team and safety** — introduce invitations, roles, account security, workspace transfer, and application backups. Permission-sensitive actions are explained without exposing controls the user cannot use.
8. **Results** — summarize saved preferences and selected/created workspace, project, and task; offer the first task as the primary action and the selected start page as the secondary destination.

Every step provides Back and Continue. Skip is visually secondary but consistently discoverable. State is saved at each successful boundary, and a resumed journey returns to the last safe step with its selections restored.

## Experience Direction: Warm Guided Route

The fixed Warm Precision system gains a route-map motif rather than a new theme. A warm editorial path connects the eight milestones and gives the user a clear sense of place without gamification.

- Orange marks the current milestone and primary action.
- Warm neutral surfaces and restrained shadows keep forms calm and legible.
- Blue is limited to explanatory product-map information, emerald to confirmed completion, and red to actual destructive or blocking states.
- Instrument Sans, existing semantic tokens, rounded panels, Lucide icons, and light/dark/system color modes remain unchanged.
- Motion is limited to short progress and state transitions and disappears under reduced motion.
- No decorative illustration or third-party image asset is required; the route motif is composed from semantic layout, icons, and CSS tokens.

## Responsive Composition

### Desktop And Wide Screens

- A bounded two-column composition places a persistent progress rail on the left and the current action plus contextual preview on the right.
- The progress rail exposes completed, current, and remaining steps with text and icon state; color is supplemental.
- The action surface remains the dominant reading region and contains one primary action.

### Mobile And NativePHP

- The composition becomes a single natural DOM column at 390 pixels and above.
- A compact sticky header communicates step, percentage, and save state without hiding the heading.
- Back, Skip, and Continue sit in a viewport-safe sticky action bar with 44–52 pixel targets and safe-area-aware spacing.
- Supporting content follows the action rather than becoming a separate overlay. Long English, Lithuanian, and Russian copy wraps without document overflow.

## Step Interaction And Feedback

- The page announces `Step :current of :total`, the complete localized step title, progress percentage, and concise saved/saving/error status.
- Focus moves to the new step heading after a successful transition. Validation failure moves focus to a localized summary that links to invalid fields.
- Workspace, project, and task steps offer choose-existing and create-new modes without duplicating backend rules.
- Creation forms provide a compact live preview and retain valid input after recoverable failure.
- Pending controls are action scoped, prevent duplicate submission, and do not block unrelated navigation.
- A server failure never advances progress or shows success. Retrying the same step is idempotent.
- If a selected workspace, project, or task is deleted or access is lost, the server removes the stale identifier and returns the user to the nearest safe step with an explanation.

## Post-Onboarding Continuation

After completion, Dashboard can show a dismissible progress card containing genuinely optional next actions:

- invite a teammate when policy allows;
- review notification preferences;
- configure account security;
- review workspace export and, for authorized operators, application backup.

The card derives completion from real server state where practical and can be dismissed permanently. It is not part of the required eight-step gate.

Settings > Preferences contains a localized Restart introduction action. Restart clears only the replay draft and step cursor; it does not delete domain data or turn a completed user back into a required-onboarding user.

## Persisted State And Versioning

Onboarding state extends the existing one-to-one `user_preferences` row because the journey is user presentation/progress state, not workspace-owned business data.

Persisted fields:

- onboarding schema/content version;
- stable current step identifier;
- started, completed, skipped, and checklist-dismissed timestamps as applicable;
- selected workspace, project, and task UUIDs inside a bounded validated state payload;
- safe drafts required to resume an unfinished form, excluding secrets and uploaded file content.

The additive SQLite migration backfills every existing preference row as completed for the current version. New registration explicitly creates a pending row. Future onboarding versions do not silently reopen the required gate for existing users; a future release must define and test an explicit rollout policy.

The persisted JSON state has a small known shape and size limit. It is never trusted for authorization. Every related identifier is re-resolved through the authenticated user's accessible workspace and the selected workspace aggregate before it is returned or used.

## State Machine

Required onboarding states are derived from persisted timestamps and safe step data:

```text
new verified user
  -> pending
  -> in progress (step + safe draft saved)
  -> completed OR skipped
  -> normal start-page navigation

completed/skipped user
  -> optional replay
  -> replay finished OR replay abandoned
  -> normal navigation remains available
```

Middleware redirects only verified browser users whose onboarding is required and unfinished. It excludes the onboarding endpoints, sign-out, verification, password confirmation, and other authentication recovery endpoints so the user cannot become trapped. JSON/API behavior is unchanged.

## Backend Boundaries

- Routes declare the authenticated/verified onboarding endpoints and middleware only.
- A focused query object loads the normalized step, safe resumable state, bounded accessible workspace/project/task options, definitions, and permission-aware educational capabilities without unbounded collections.
- Authorized Form Requests validate each command and reject unknown step transitions or oversized/foreign state.
- Focused actions own preference save, workspace selection/creation, project selection/creation, task selection/creation, skip, completion, restart, and checklist dismissal.
- Existing `UpdateUserPreferences`, `CreateWorkspace`, `CreateProject`, and `CreateTodo` actions remain the mutation authorities and are composed rather than copied.
- One short transaction records a step's domain mutation and progress cursor where atomicity is required.
- Controllers coordinate one request, one action/query boundary, and an Inertia response or redirect.
- Related identifiers are resolved from the authenticated user's memberships and then through the selected workspace. Mixed, missing, or foreign identifiers fail atomically without revealing foreign existence.

Duplicate browser submission and resumed requests must not create duplicate workspace, project, or task records. Creation steps use a persisted per-step idempotency token or equivalent unique replay guard bound to the user and step; after a successful create, retries return the already recorded entity.

## Frontend Boundaries

- `resources/js/pages/onboarding/Index.vue` coordinates the immutable Inertia page contract, active step, and focus handoff.
- Focused components under `resources/js/components/onboarding/` own the progress rail, mobile status, action frame, semantic steps, result summary, and post-onboarding checklist presentation.
- A pure typed helper owns stable step order, percent calculation, safe local draft merging, and locale-aware count/status selection so behavior is covered without relying only on source-string tests.
- Wayfinder-generated controller or named-route functions are the only route builders.
- Existing form, button, input, select, card, progress, alert, dialog/sheet, and workspace primitives are reused before a new primitive is introduced.
- Inertia's request/form APIs own mutations. Local state mirrors only the current draft and synchronizes when persisted step identity changes.

## Localization

All visible and assistive text uses stable semantic keys in the English, Lithuanian, and Russian PHP catalogs with English fallback. Each message is a complete sentence or semantic label. Count messages use locale-aware one/few/many/other forms; no translated fragments are concatenated. Dates, times, percentages, lists, and timezones use the shared formatters and the user's saved locale/timezone.

## Accessibility

- The authenticated shell remains the sole `main` landmark and the onboarding page owns one logical `h1`.
- Native form and progress semantics come before ARIA. The current step is exposed with text and `aria-current="step"` where applicable.
- Status announcements are concise polite regions; the complete changing page is not live.
- Validation summary links, field descriptions, `aria-invalid`, and visible error messages are localized.
- Focus order follows the DOM, focus rings remain visible in light/dark/forced-colors modes, and successful transitions focus the new heading.
- Sticky mobile controls remain reachable at zoom and with dynamic viewport/safe-area changes.
- Reduced motion removes nonessential movement without removing orientation or feedback.
- Touch targets are at least 44 by 44 pixels, and all choose/create interactions work with keyboard and screen reader input.

## Errors, Recovery, And Security

- A failed save preserves the current valid draft and offers retry without advancing.
- Validation, conflict, authorization, and unexpected errors use distinct localized messages and truthful state.
- A lost entity/access relationship rewinds to the closest valid selection step.
- Repeated create/continue requests return the same result and cursor after the first success.
- Onboarding adapts to owner/admin/member capabilities. It never offers a hidden policy bypass or trusts frontend role state.
- No third-party analytics, tracking SDK, remote onboarding service, external URL fetch, or secret-bearing draft is introduced.
- NativePHP continues to run the same embedded Laravel, Inertia, and SQLite application; the feature does not add a client/server split.

## Verification Contract

### Backend And Data

- Pest covers registration defaults, existing-user migration backfill, verified/unverified redirects, login/home behavior, resume, skip, completion, manual replay, version behavior, invited/workspace-less users, role adaptation, workspace isolation, invalid transitions, stale identifiers, idempotent retries, and checklist dismissal.
- Migration coverage runs on a fresh isolated SQLite database and a populated pre-migration fixture, including rollback where meaningful, foreign-key checks, and preservation of existing preferences.
- Query-budget tests prove bounded workspace/project/task options and no per-row/N+1 queries.

### Frontend And Localization

- Frontend behavior tests cover step order/progress, immutable state synchronization, validation summary targeting, saved/error status, choose/create mode, and connected-node focus restoration.
- English, Lithuanian, and Russian key/placeholder parity, complete sentences, and representative plural categories are verified.
- Source/design tests cover Wayfinder, Inertia lifecycle state, semantic progress, one page heading, static Tailwind classes, 44-pixel targets, reduced-motion, and forced-colors contracts.

### Browser And Mobile

- Live Herd QA covers first-run, resume, skip, complete, replay, stale-selection recovery, and post-onboarding checklist flows.
- The matrix includes 1,440-pixel desktop and 390-by-844 mobile, light/dark, reduced motion, forced colors, keyboard traversal, focus return, long translations, no horizontal overflow, and current browser/server logs.
- The final Android phase builds a fresh debug APK from the completed code, inspects package/min/target SDK metadata, verifies signature/alignment/archive integrity, and confirms the embedded archive excludes the host SQLite database.

### Quality And Delivery

Pint, Larastan, focused and full/parallel Pest, frontend tests, Vue type checking, ESLint, Prettier, the production build, Composer/npm audits, isolated migration/seeding, route/cache/health checks, and `git diff --check` must pass. Progress is recorded before and after each implementation phase, changes are committed as coherent slices on `main`, and each verified slice is pushed to `origin main` without rewriting history.

## Non-Goals

- No public onboarding API, alternate mobile frontend, Vue Router, Livewire, remote analytics, social login change, demo-data generator, or package dependency.
- No forced replay for existing users or automatically reopened journey after a future content-version bump.
- No redesign of normal workspace/project/task CRUD, authorization, settings information architecture, or the fixed Warm Precision theme.
- No deletion or rollback of user-created workspace, project, or task data when onboarding is skipped, restarted, or replayed.
