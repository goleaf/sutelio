# Calendar Planning Workspace Design

## Status

Approved through the user's repeated instruction to proceed with the recommended all-skills code and design update.

## Problem

The calendar currently loads every dated task in the selected workspace and keeps its month, week, or agenda selection only in local component state. A reload or shared link loses the user's context, period navigation does not change the server query, and a mostly empty current month can hide a large overdue backlog outside the visible range.

The existing interface already establishes the Warm Precision visual language and accessible task links. This phase should strengthen it as a planning tool without adding task creation, drag-and-drop rescheduling, a new dependency, or a second routing system.

## Considered Approaches

### 1. Planning workspace — selected

Make `view` and `date` validated URL parameters, calculate bounded server ranges for month, week, and agenda, and redesign the page around a visible planning period with an overdue attention rail. This resolves the product and performance gaps together while preserving the current task-detail workflow.

### 2. Visual refresh only

Keep eager data and local state, but refine spacing, hierarchy, and responsive behavior. This has lower implementation risk, but leaves reload, sharing, and unbounded-query defects intact.

### 3. Interactive scheduling

Add task creation and drag or keyboard rescheduling directly in the calendar. This would be useful, but it requires new mutation, conflict, rollback, mobile gesture, and authorization contracts and is intentionally outside this focused phase.

## Experience Direction

The calendar becomes a refined planning desk: a calm editorial frame with one dominant schedule canvas and a narrow attention rail. Warm orange marks the active date and primary navigation; project and priority colors provide secondary context but never carry meaning alone. The visual memory is the clear period navigator paired with an overdue queue that remains useful even when the visible calendar range is quiet.

Desktop keeps a spacious calendar grid and contextual rail. Tablet preserves the same information hierarchy with a narrower rail. Mobile replaces the compressed seven-column month grid with an agenda-like sequence of dated cards while retaining the same URL-backed view and period controls. All interactive targets remain at least 44 pixels, focus is visible, motion is reduced when requested, and light/dark modes use existing semantic tokens.

## Architecture

Introduce an authorized `CalendarIndexRequest` that validates:

- `view` as `month`, `week`, or `agenda`;
- `date` as an ISO date;
- defaults derived from the current user date in their selected timezone.

The controller resolves the selected workspace, asks the request for a normalized calendar state, and passes that state to `CalendarQuery`. The query receives explicit inclusive UTC-compatible date bounds and returns only tasks needed for the selected period plus a separately bounded overdue preview. It remains workspace-scoped, deterministically ordered, and eager-loads project, status, and priority definitions.

The Inertia response exposes normalized `calendar` metadata (`view`, anchor date, start, end), `todos` for the visible range, and `overdueTodos` for the attention rail. No route or database schema changes are required.

## Frontend Components And State

`resources/js/pages/calendar/Index.vue` remains the page coordinator. It initializes exclusively from immutable server props and generates every period/view transition with the existing `calendar` Wayfinder route. Inertia visits use `preserveScroll`, replace history for repeated navigation, and expose a busy state through the page controls.

The page is split into focused presentation components:

- `CalendarPeriodNavigator.vue` owns the view switcher, today action, period label, previous/next actions, accessible current-state semantics, and loading state.
- `CalendarMonthGrid.vue` renders the desktop/tablet month grid and a mobile dated-card representation from the same normalized days.
- `CalendarWeekView.vue` renders seven explicit day sections with task counts and empty states.
- `CalendarAgendaView.vue` renders chronological date groups within the bounded agenda range.
- `CalendarAttentionRail.vue` renders the overdue preview with task-detail Wayfinder links and a clear empty state.

Task links continue to use generated task-show helpers with Inertia prefetching. Existing shared header, metric, segmented-control, button, translation, and locale/timezone formatting primitives remain authoritative.

## Data Flow

1. A visit to `/calendar` normalizes to the user's current date and preferred/default month view.
2. A view or period action creates a generated Wayfinder URL containing `view` and `date`.
3. Inertia requests the new page; the Form Request validates the URL state before any calendar query runs.
4. The query calculates and loads only the selected workspace's visible date window and bounded overdue preview.
5. Server props replace the page state, making reloads, browser navigation, and shared URLs reproducible.

Month queries include the full leading/trailing week cells. Week queries include exactly seven days. Agenda queries use a bounded forward window rather than loading the entire workspace history. The attention rail is independent of those windows so overdue work remains visible.

## Validation And Failure Behavior

Invalid view or date values redirect back to the canonical calendar defaults through normal Laravel validation behavior without running an unbounded fallback query. A missing current workspace returns the existing empty page contract with normalized calendar metadata. Foreign workspace tasks remain impossible because all reads begin from the authorized workspace relation.

Navigation controls disable while an Inertia visit is active. Network failures keep the existing page visible, release the busy state, and rely on the application's established error feedback. Empty visible ranges and empty overdue previews receive distinct localized states.

## Localization And Accessibility

All new copy uses stable English, Lithuanian, and Russian keys with English fallback. Dates and period labels use `useWorkspaceUi` and the server-selected timezone. Weekday ordering respects the user's configured week start; no hardcoded English or `en-US` formatting is introduced.

The calendar uses one `h1`, a logical `h2` for the planning period, semantic sections for dates, accessible names for icon buttons, `aria-current` or selected state for the active view/date, visible focus, non-color task metadata, and live busy status. Mobile reading order matches the visual order and the page must not overflow at 390 pixels.

## Testing

Test-first coverage will prove:

- valid month, week, and agenda URL state and normalized response metadata;
- invalid views and dates fail validation safely;
- selected-workspace isolation and exact inclusive range boundaries;
- agenda and overdue previews remain bounded and deterministically ordered;
- the Vue source uses generated Wayfinder navigation, immutable props, localized copy, responsive alternatives, focus states, reduced-motion-safe transitions, and minimum target sizing;
- live desktop/mobile light/dark navigation updates the URL, survives reload and browser back/forward, opens task detail, has no horizontal overflow, and emits no new console or page errors.

## Out Of Scope

- Creating tasks from a calendar date.
- Drag, touch, or keyboard rescheduling.
- Recurrence-instance editing.
- External calendar synchronization.
- New dependencies, migrations, or route names.
