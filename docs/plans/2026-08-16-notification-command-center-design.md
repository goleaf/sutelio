# Notification Command Center Design

## Status

Approved on 2026-08-16 from the recommended Structured Signal Stream direction.

## Objective

Turn the notification inbox into a focused triage surface without adding a new notification lifecycle or changing the application stack. Preserve user-scoped read operations and bounded pagination while replacing the monolithic, content-sniffing presentation with typed server semantics, accessible filters, stable grouping, and focused Vue components.

## Chosen Direction

The Structured Signal Stream balances product value and implementation risk:

- keep read-one and read-all as the only mutations;
- add URL-backed All, Unread, and Read status filters;
- add All, Reminders, and Updates kind filters using server-owned notification semantics;
- group the visible page into Today and Earlier sections using the user's timezone;
- normalize notification kind, title, body, action, and date data before Vue renders it;
- preserve the existing database schema and deterministic bounded pagination.

A visual-only refactor was rejected because it would retain fragile title/body inspection and the 562-line coordinator. Snooze, dismiss, and bulk-selection workflows were rejected as unnecessary persistence and lifecycle scope.

## Experience And Visual System

The page remains part of Xiaomi Mimo's Warm Precision system: warm white surfaces, orange signal accents, restrained blue/emerald support tones, Instrument Sans, rounded panels, and Lucide icons. The distinctive element is a compact signal stream rather than another card grid.

- Unread rows use a visible orange leading rule, explicit Unread text, and stronger type weight; color is never the only signal.
- Notification kind controls icon and tone through structured values, never localized copy matching.
- Today and Earlier headings create temporal rhythm without obscuring pagination boundaries.
- The control bar keeps status, kind, and result count together. It remains readable at desktop widths and collapses to touch-friendly controls on mobile.
- Row actions use at least 44-pixel targets, visible focus, stable hover states, and reduced-motion-safe transitions.
- The header retains total, unread, and reviewed metrics plus one Mark all as read action.

## Architecture And Components

### Server

- `NotificationIndexRequest` validates and normalizes status, kind, page, and page-size state.
- `NotificationIndexQuery` applies user-scoped filters, deterministic ordering, bounded pagination, and aggregate counts without resource-side queries.
- A notification inbox resource emits a minimal typed presentation contract: identity, semantic kind, localized-safe content inputs, action URL, read state, timestamp, and user-timezone date bucket.
- `NotificationController` remains a thin Inertia coordinator and exposes stable filter state.
- Existing mark-one and mark-all actions remain the mutation boundary and continue to scope notification identifiers to the authenticated user.

### Client

- `notifications/Index.vue` becomes a coordinator for visits, mutation state, focus restoration, and browser-notification delivery.
- `notification-inbox.ts` owns types and pure grouping/presentation helpers.
- `NotificationFilters.vue` owns responsive URL-filter controls and concise result status.
- `NotificationFeed.vue` owns Today/Earlier sections, loading/empty states, and pagination.
- `NotificationRow.vue` owns semantic icon, unread/read treatment, action navigation, and the per-row mark-read control.

Existing shared header, metrics, segmented controls, buttons, spinner, and empty-state components remain the visual foundation.

## Data And Interaction Flow

1. The validated request resolves status and kind filters.
2. The query loads only the authenticated user's page and computes global inbox statistics.
3. The resource normalizes every row without querying.
4. Filter changes use generated Wayfinder routes and partial Inertia reloads for notifications, statistics, and filter state.
5. Mark-read mutations disable only the affected row. In the Unread view, a successful mutation removes that row locally, updates visible counts, and restores focus to the next stable row or feed heading while the server response remains authoritative.
6. Mark-all disables duplicate submission, updates the inbox through a partial reload, and announces one concise localized success message.
7. Actionable notifications mark themselves read before navigating through their authorized server-provided destination.

## Accessibility And Responsive Behavior

- One page heading and ordered section headings preserve hierarchy.
- Filter controls have visible labels, selected state, keyboard support, and 44-pixel minimum targets.
- Only concise result and mutation status nodes use `aria-live`; the changing feed itself is not a live region.
- Read state is communicated by text and structure in addition to color.
- Focus is restored after filtered-row removal and never sent to a detached trigger.
- Desktop uses a wide signal stream with actions aligned at the row edge. Mobile stacks row content and actions without horizontal scrolling.
- Light, dark, reduced-motion, 390-pixel mobile, and 1,440-pixel desktop states are verified.

## Error And Empty States

- Invalid filters return localized validation errors and never broaden access.
- Row and mark-all failures retain the current feed, clear processing state, and show the shared localized error toast.
- Empty All, Unread, Read, Reminders, and Updates results use complete semantic messages rather than fragments.
- Unknown or legacy notification payloads render a safe General update fallback with no raw translation key or unsafe URL.

## Verification

- Pest covers positive and invalid status/kind filters, pagination order, user isolation, legacy payload fallback, timezone grouping, mark-one/all idempotence, and query budgets.
- Frontend pure tests cover grouping and semantic presentation helpers.
- Frontend source/interaction coverage checks component boundaries, Wayfinder visits, focus fallback, concise live regions, and responsive controls.
- Pint, Larastan, focused/full Pest, frontend tests, Vue type checking, ESLint, Prettier, and the production build must pass.
- Live Herd browser QA verifies filtering, mark-read removal/focus, mark-all state, empty states, mobile overflow, dark mode, reduced motion, and current browser logs.

## Non-Goals

- No snooze, dismiss, delete, archive, bulk selection, push-service, WebSocket, Redis, or new database requirement.
- No Vue Router, alternate frontend framework, or dependency change.
- No change to email/browser notification preferences beyond linking or presenting their existing state where already available.
