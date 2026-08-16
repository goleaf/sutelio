# Activity Intelligence Design

## Goal

Turn the existing Activity page into a trustworthy workspace timeline that remains useful beyond its first page, exposes the full domain event vocabulary, and never sends raw change values to the browser.

The phase preserves the fixed Warm Precision system and the existing Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, and SQLite stack.

## Current Gap

The page currently receives a 50-item paginator but treats its first page as a complete client-side collection. Its four local filters cover only `created`, `updated`, and `completed`, while the domain records fifteen event values. Header metrics are derived from the loaded page and the browser clock, so they are not workspace totals. The Inertia response also serializes activity models directly, including arbitrary `properties` values such as `old` and `new`.

The visual presentation is coherent and responsive, but later records are unreachable and a selected filter can incorrectly report an empty result even when matching records exist on a later page.

## Chosen Approach

Build a server-backed activity intelligence workspace with validated URL state, an explicit event taxonomy, safe resource serialization, accurate aggregate metrics, and accessible manual infinite scrolling.

This is preferred over a client-only polish pass because it fixes correctness and privacy, and over a broad audit-log subsystem because export, retention, and compliance administration are not required for the current product phase.

## Information Architecture

The page keeps the editorial header and three-metric strip. Metrics become server truth:

- recorded actions: complete workspace activity count;
- contributors: distinct non-null actors across the workspace ledger;
- recent changes: activity recorded during the last seven days.

Below the header, a compact intelligence bar presents:

- category: all, creation, changes, completion, organization, or automation;
- contributor: all contributors or one current workspace member;
- period: all time, seven days, thirty days, or ninety days;
- a visible filtered-result count and a clear-filters action.

Desktop keeps a narrow filter rail beside the timeline. Mobile keeps the category strip visible and moves contributor and period controls into the existing accessible Sheet primitive. All interactive targets are at least 44 pixels, retain visible focus, and remain usable without hover.

## Event Taxonomy

A focused `ActivityCategory` enum owns category values and their mapping to `ActivityEvent` values:

- creation: `created`;
- changes: `updated`, `attached`, `detached`;
- completion: `completed`, `uncompleted`;
- organization: `archived`, `unarchived`, `pinned`, `unpinned`, `favorited`, `unfavorited`, `deleted`, `restored`;
- automation: `recurrence_generated`.

The backend expands a selected category into its event values. The frontend receives category options and selected state, not a duplicated event map.

## Backend Architecture

`ActivityIndexRequest` validates and normalizes `category`, `actor`, `period`, and the pagination parameter. Actor identifiers must belong to the routed workspace membership set. Invalid or foreign identifiers fail before the activity query executes.

`ActivityIndexQuery` continues to start from `$workspace->activityLogs()`. It applies the validated category, actor, and UTC period boundary, eager-loads only the actor fields required by the resource, preserves deterministic `created_at DESC, id DESC` ordering, and returns a bounded paginator with its filter query string.

The same query object exposes focused aggregate and contributor-option methods. Metrics are calculated against the authorized workspace and intentionally remain unfiltered so they describe the complete ledger; the timeline result count describes the active filters.

`ActivityController` remains a coordinator: resolve or receive the workspace, authorize it, obtain normalized filter state, ask the query object for results and metadata, and render the Inertia page. The response wraps the paginator in `Inertia::scroll()` so the Vue page can request older pages without replacing the current timeline.

## Privacy Contract

`ActivityLogResource` becomes the only activity serializer used by the page. It emits:

- id, event, subject type and subject id;
- an allowlisted subject label from `title` or `name` when it is a string;
- an allowlisted changed-field key when it is a recognized scalar field name;
- the loaded actor's public resource fields;
- creation timestamp.

The response does not emit workspace/user foreign keys or the raw `properties` object. Values stored under `old`, `new`, descriptions, tokens, paths, or unknown keys never cross the Inertia boundary.

## Frontend Components And State

The page uses immutable typed props and URL-backed filters. Selecting a filter cancels outstanding visits, then uses the generated Wayfinder activity route with `router.get`, `preserveState`, and a partial reload of activity results and filter state. Filter changes reset pagination instead of merging incompatible pages.

Focused components keep the page readable:

- `ActivityFilterPanel.vue` owns desktop/mobile filter controls and emits complete filter state;
- `ActivityTimeline.vue` groups entries by localized date and owns the manual `InfiniteScroll` presentation;
- `activity-types.ts` defines the Inertia response contracts shared by the page and components.

Each row retains the current timeline rhythm: Lucide event icon, translated action label, subject type, optional safe label, actor, localized time, and an event text badge so color is never the sole signal. A manual `Load older activity` button is the primary pagination affordance, with a pending label and an `aria-live` result status. Automatic scroll loading is intentionally disabled.

Empty states distinguish an empty workspace ledger from a valid filter with no matches. Clearing filters restores the canonical route without stale query parameters.

## Visual Direction

The memorable element is an editorial "change ledger": a calm vertical spine with compact evidence cards and a warm filter desk. Orange marks active navigation and creation; sky, emerald, violet, and neutral tones communicate secondary categories while translated badges preserve meaning.

The implementation reuses semantic colors, shared page metrics, rounded collection surfaces, restrained shadows, Instrument Sans, existing Sheet/Select primitives, dark-mode tokens, and reduced-motion variants. It does not add a new design family, dependency, global CSS layer, gradient-heavy decoration, emoji iconography, or runtime theme switcher.

## Error And Loading Behavior

- Filter controls expose a pending state while the current Inertia visit is active and prevent rapid stale responses through `router.cancelAll()`.
- The current timeline remains visible during a filter visit; status text communicates loading without layout shift.
- Manual pagination disables its action while fetching and exposes a terminal state when no older records remain.
- Invalid direct URL filters return Laravel validation behavior without leaking whether a foreign actor exists.
- A deleted actor falls back to the translated system label; a missing or deleted subject remains a non-linked historical record.

## Testing Strategy

Implementation follows red-green-refactor.

Focused Pest coverage will prove:

- every activity event belongs to exactly one public category;
- category, period, and actor filters are workspace-scoped and deterministic;
- foreign actor identifiers are rejected without revealing or returning foreign activity;
- metrics describe the complete workspace ledger rather than the current page;
- pagination preserves filters and later records remain reachable;
- the resource exposes safe labels but never raw `properties`, `old`, or `new` values;
- no-workspace, empty-ledger, filtered-empty, and populated responses remain valid;
- query counts stay bounded.

Frontend contract tests will cover typed filter state, Wayfinder use, manual Inertia scrolling, translated copy, Sheet/Select accessibility, 44-pixel controls, reduced motion, and distinct empty states.

Live Herd verification will exercise desktop and 390-pixel mobile layouts in light and dark modes, category/contributor/period changes, clear filters, loading older records, keyboard focus, reload persistence, horizontal overflow, page errors, console errors, and recent Laravel Boost browser logs.

## Delivery Boundaries

This phase does not change database schema, activity retention, exports, seeded production data, workspace switching, project presentation, notification behavior, or the semantics of activity creation. The concurrent production-modernization worktree is preserved outside the phase commits.
