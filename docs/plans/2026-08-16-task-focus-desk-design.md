# Task Focus Desk Design

## Status

Approved on 2026-08-16. The user's repeated instruction to update the code and design is treated as approval of the recommended Progressive Task Focus Desk direction presented after repository and live-browser inspection.

## Objective

Turn `/tasks` into a calmer daily operations surface without replacing its URL-backed filters, list/board views, pagination, bulk actions, task detail, or established Laravel/Inertia boundaries. Reduce the default action density, expose already-supported focus filters, and make the list faster and safer to scan on mobile and desktop.

## Current Evidence

- The live task index renders 25 rows with roughly 80 interactive controls and a 3,217-pixel mobile document at 390 by 844 pixels.
- Every list row exposes selection, completion, whole-row open, and delete simultaneously. Destructive actions are therefore always prominent, while bulk selection is always present even when unused.
- Mobile titles are truncated to one line, and the closed filter trigger gives no concise summary of active filters.
- The server and `TodoFilters` type already support `overdue`, `completed_today`, `is_pinned`, and `is_favorite`, but the task index does not expose them.
- Existing list, board, pagination, task detail, confirmation, workspace isolation, and mutation contracts are implemented and verified; this phase must preserve them.

## Considered Directions

### Progressive Task Focus Desk — Selected

Keep the current data contract and page architecture. Add URL-backed focus toggles, a concise results/filter summary, explicit selection mode, one primary row-open target, a completion control, and an accessible overflow menu for secondary/destructive actions.

This resolves the observed control overload and hidden capability gap without adding a schema, dependency, or competing task workflow.

### Date-Grouped Focus Queue — Rejected

Grouping all work into overdue/today/upcoming sections would duplicate the dashboard and calendar, require server-owned cross-page grouping, and complicate deterministic pagination and sorting.

### Visual-Only Compaction — Rejected

Spacing and color changes alone would leave selection and delete permanently exposed, keep existing focus filters undiscoverable, and preserve the same mobile interaction burden.

## Experience Direction: Warm Working Set

The page remains inside the fixed Warm Precision system. The distinctive visual memory is a quiet working set: a clear focus strip above a disciplined queue, with secondary operations revealed only when requested.

- Orange marks the active focus state and primary creation action.
- Red is reserved for explicit overdue and destructive meaning, always paired with text or an icon.
- Emerald identifies completed work; blue supports neutral schedule information.
- Existing semantic tokens, Instrument Sans, rounded panels, warm shadows, Lucide icons, and light/dark/system themes remain unchanged.
- Motion is limited to short color/opacity transitions and is disabled for reduced-motion users.

## Information Architecture

### Header And Focus Controls

- Keep the existing page header, new-task action, and total/pending/completed metrics.
- Keep search and list/board selection as the first control row.
- Add an accessible focus strip for Overdue, Completed today, Pinned, and Favorites. Each toggle maps directly to an existing URL query parameter and may combine with project/status/priority filters.
- Show the number of active filters in the closed mobile trigger and a concise localized result summary above the collection.
- Keep project/status/priority/sort/direction/page-size controls in the current desktop grid and mobile Sheet.
- Clearing filters resets every focus flag as well as the current taxonomy/search/order state while retaining the selected list/board view.

### List Queue

- Default mode shows one completion control, one primary task-open control, and one overflow menu per row.
- Delete moves into the overflow menu and still opens the existing application confirmation dialog.
- An explicit Select tasks button enters selection mode. Only then do page and row checkboxes appear.
- Leaving selection mode clears the selection. Completing, filtering, paginating, or changing views cannot leave stale selected identifiers behind.
- Rows expose a two-line mobile title, project, translated status, priority, and due/overdue context without relying on color alone.
- Selection mode remains available only in list view; board behavior and keyboard status movement remain unchanged.

### Results And Loading

- A concise results node announces the current visible range, total matched tasks, and active-filter count. The changing task collection itself is not an `aria-live` region.
- The collection uses `aria-busy` during filter visits and keeps existing content visible while a partial reload is pending.
- Pagination remains conventional previous/next navigation and continues to preserve URL state, scroll, and page data.

## Component And State Boundaries

- `resources/js/pages/tasks/Index.vue` remains the Inertia coordinator for URL visits, detail/create dialogs, mutations, confirmations, and selection-mode state.
- `resources/js/components/task/TaskFilterBar.vue` owns synchronized local filter fields, focus toggles, active-filter count, and the mobile Sheet.
- `resources/js/components/task/TaskList.vue` owns queue presentation and emits semantic row, menu, completion, and selection events. It does not mutate Inertia props or perform requests.
- `resources/js/components/task/TaskResultsBar.vue` owns the concise result and selection-mode controls.
- `resources/js/components/task/task-focus.ts` owns pure filter-count and focus-toggle behavior for dependency-free TypeScript tests.
- Existing Reka/shadcn-style dropdown, checkbox, Sheet, Button, Badge, confirmation, toast, and task-detail components are reused.
- No query is introduced in a Vue component, resource, accessor, policy, loop, or route closure.

## Data And Interaction Flow

1. Search, focus, taxonomy, sort, direction, page-size, and view changes emit a complete immutable `TodoFilters` value.
2. The page performs the existing generated-Wayfinder `router.get` partial visit, clears selection, preserves scroll/state, replaces history, and requests only `todos`, `filters`, and `stats`.
3. Selection mode is local presentation state. Entering it does not visit the server; exiting clears selected IDs.
4. The overflow menu's Open action uses the existing authorized standalone task-detail request. Delete sets the existing confirmation target.
5. Completion and bulk mutations retain existing server-authoritative reloads, disabled states, and localized feedback.

## Accessibility And Responsive Contract

- One `h1`; collection/result controls use native headings, buttons, checkboxes, and menus before ARIA.
- Each task has one clearly named primary open button. The row does not contain a full-card overlay behind nested controls.
- Icon-only controls have localized names; menu actions contain visible text.
- All touch targets are at least 44 by 44 pixels, focus is visible, keyboard order matches DOM order, and Escape/menu focus behavior is delegated to Reka primitives.
- Active filters, completion, overdue state, status, and selection use text/icons as well as color.
- The mobile filter trigger exposes an active count to sighted and screen-reader users; results use one concise polite live region.
- Long English/Lithuanian/Russian task titles and labels wrap safely. The page must not overflow at 390 pixels or create hover-only access.
- Dark mode, forced colors, and reduced motion preserve meaning and operability.

## Error And Empty States

- Failed filter visits keep current content, release busy state, and use existing global error handling.
- Task-detail HTTP failure uses the existing localized toast and does not open stale detail.
- Completion, move, delete, and bulk failures retain current server-authoritative behavior and processing guards.
- Empty filtered results distinguish “no match” guidance from the create-first empty state using current filter presence.

## Verification

- Pest covers normalization and round-tripping of every focus flag, combined focus/taxonomy filters, filtered stats, workspace isolation, and source-level architecture/accessibility contracts.
- Direct TypeScript tests cover active-filter counting, focus-toggle behavior, and clear behavior.
- English, Lithuanian, and Russian keys use full semantic messages with representative count forms.
- Pint, Larastan, focused/full Pest, frontend tests, Vue type checking, ESLint, Prettier, production build, audits, and `git diff --check` must pass.
- Live Herd QA covers desktop/mobile, list/board, focus toggles and URL reload, selection mode, overflow delete confirmation, task detail/focus restoration, light/dark/reduced-motion/forced-colors, long text, zero overflow, and current browser/Boost logs.

## Non-Goals

- No schema, dependency, route, policy, task lifecycle, pagination strategy, dashboard/calendar workflow, drag interaction, or stored-preference change.
- No date-grouped cross-page queue, virtual scrolling, optimistic task mutation, swipe-only action, inline task editing, new assignee/label/tag dataset, or runtime theme family.
