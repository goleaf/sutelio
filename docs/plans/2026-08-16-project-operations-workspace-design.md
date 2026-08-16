# Project Operations Workspace Design

## Goal

Turn the existing project detail page into a bounded, trustworthy operations workspace where a team can understand project health, find the next task, and act without losing context.

The phase preserves the fixed Warm Precision system and the existing Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, and SQLite stack.

## Current Gap

The current project page is visually consistent with the application and its task actions work, but it loads every active project task into one Inertia response and performs search entirely in the browser. That makes the page progressively heavier as a project grows, and its three header totals do not explain deadline risk, completion progress, ownership, or what needs attention next.

Five equally weighted header actions compete on mobile, task status sections are useful only while the complete project remains small, and the local search cannot produce a stable shareable view. The result is a clean task list rather than an operational project surface.

## Considered Approaches

### Presentation-only refinement

Keep the current response and reorganize the same data into a stronger header, progress card, and denser task rows. This has the smallest implementation surface, but it preserves the unbounded task collection and local-only search.

### Lightweight operational workspace

Add validated URL-backed task filters, bounded manual pagination, accurate project metrics, and a focused attention summary while retaining the existing project and task actions. This fixes the scale and decision-making gaps without introducing milestones, portfolios, or a new project domain.

### Full project planning subsystem

Add milestones, project owners, budgets, dependencies, goals, and portfolio reporting. This could become valuable later, but it requires new product semantics, migrations, policies, and workflows that are not justified by the current request.

The lightweight operational workspace is chosen because it improves the existing product truth rather than inventing a parallel planning system.

## Information Architecture

The page keeps one editorial project header, but its hierarchy becomes clearer:

- a compact back link sits above the title instead of competing with mutations;
- project color and icon identify the workstream without becoming the only state signal;
- `New task` remains the primary action;
- duplicate and archive or restore move into an accessible `More actions` menu;
- archived projects display a textual archived state and disable task creation.

The header metric strip becomes project truth:

- total active tasks;
- open tasks;
- completion rate;
- tasks needing attention.

Below it, the workspace uses a two-column desktop layout. The main column contains the task work queue. A compact right rail contains project pulse and attention details. On mobile, the pulse precedes the task queue and the filters move into the existing Sheet primitive.

## Project Pulse

The project pulse summarizes the complete authorized project rather than the current result page:

- completion progress with completed and total counts;
- overdue tasks;
- tasks due during the next seven days;
- unassigned open tasks;
- priority distribution for open tasks.

Every value is paired with text and an icon; color never carries meaning alone. Percentages use locale-aware number formatting and remain mathematically honest when the project has no tasks.

The attention list shows at most five open tasks, ordered by overdue state, due date, priority position, task position, and id. It never implies that the list is complete, and its heading reports the full attention count.

## Task Work Queue

The task queue is server-backed and URL-addressable. Supported state is intentionally small:

- search text;
- status definition;
- priority definition;
- assignee;
- attention: all, overdue, due soon, or unassigned;
- sort: project order, due date, priority, or recently updated.

Default values are omitted from the URL. A filter change cancels an outstanding Inertia visit, resets pagination, and requests only the task results and normalized filter state. Stable project metrics, task definitions, assignee options, and attention summary remain closure-wrapped so partial reloads do not repeat their queries.

The queue uses a deterministic 25-row paginator wrapped in `Inertia::scroll()`. Manual `Load more tasks` is the only pagination trigger. Loaded rows merge without duplicates, filters never merge incompatible pages, and an explicit terminal state tells the user when the complete filtered result has been reached.

Task rows retain existing completion, selection, detail-sheet, and deletion behavior. Each row presents status, priority, assignee, due state, and relevant labels with wrapping and keyboard-safe whole-row selection. The result header exposes the filtered total and a clear-filters action.

## Backend Architecture

`ProjectShowRequest` validates and normalizes search, status, priority, assignee, attention, sort, and page. Status, priority, and assignee identifiers must belong to the authorized routed workspace. Invalid or foreign identifiers fail validation without revealing whether the identifier exists elsewhere.

`ProjectDetailQuery` remains the read boundary. It will:

- start from `$workspace->todos()` and require the routed project id;
- apply only normalized filters;
- select the fields required by `TodoResource` and eager-load bounded display relations;
- apply deterministic sorting with `id` as the final tie-breaker;
- return a 25-row paginator with the active query string;
- calculate complete-project metrics and priority distribution with focused aggregate queries;
- return at most five attention tasks and a bounded distinct assignee option list.

`ProjectController::show` remains a coordinator: authorize the routed project, obtain normalized state, request scoped results and stable metadata, and render the Inertia page. It will not contain filter or aggregation logic.

No new database table is required. Existing project-task indexes will be inspected with real SQLite query plans; an additive composite index is introduced only if the final filtered ordering cannot use current indexes.

## Frontend Components And State

The page remains a Vue Composition API page with immutable typed props. Focused components keep coordination separate from presentation:

- `ProjectOperationsHeader.vue` owns identity, state, primary action, and the accessible action menu;
- `ProjectPulse.vue` owns progress, risk counts, and priority distribution;
- `ProjectTaskFilters.vue` owns desktop and mobile filter controls;
- `ProjectTaskQueue.vue` owns result state, rows, manual pagination, and filtered empty states;
- `project-operations.ts` owns typed contracts and pure URL-query construction.

The page coordinates Wayfinder visits, request cancellation, task-detail selection, task completion, deletion confirmation, task creation, and refresh behavior. Existing `TaskDetail`, `TaskCreateDialog`, and `WorkspaceConfirmDialog` components are reused.

## Visual Direction

The memorable element is a calm project briefing desk: project identity at the top, a precise progress pulse, a clear attention rail, and a work queue with strong scan rhythm. The page feels editorial and operational rather than dashboard-heavy.

Warm neutrals remain dominant. The project accent appears as a narrow identity rail and small icon field. Orange marks primary actions and active filters; semantic warning, destructive, information, and success tokens communicate deadline and completion states with adjacent labels.

The implementation reuses `max-w-app`, `rounded-panel`, `rounded-feature`, `shadow-panel`, Instrument Sans, shared page/header primitives, Lucide icons, Reka/shadcn-style Menu, Sheet, Select, Checkbox, and existing form controls. It adds no gradient-heavy decoration, emoji iconography, runtime design-family switch, or new dependency.

All controls keep visible focus, at least 44-pixel touch targets where applicable, 150–300 ms interaction feedback, reduced-motion variants, sufficient light/dark contrast, translated expansion space, and zero document-level horizontal overflow at 390 pixels.

## Loading, Empty, Error, And Mutation States

- Initial task results render with the page; filter visits preserve the visible queue and expose a concise `aria-live` loading status without shifting the layout.
- Manual pagination disables only its action, announces progress, and exposes a terminal state.
- Empty-project and filtered-empty states use different copy and actions.
- Archived projects remain readable; task creation is unavailable until restore.
- Project mutations disable project actions as a group and retain action-specific spinners.
- Task completion and deletion remain row-scoped so unrelated rows stay interactive.
- Direct invalid filter URLs return safe Laravel validation behavior.

## Localization And Accessibility

All new user-facing and assistive copy uses stable semantic English, Lithuanian, and Russian keys with English fallback. Full sentence messages own their placeholders; sentence fragments are not concatenated. Counts use locale-aware formatting and plural-aware messages where grammar changes.

The task result count is the only changing live region. Filter buttons expose their selected state, the mobile filter trigger exposes an accessible active-filter summary, the action menu follows keyboard/menu semantics, progress has a textual equivalent, and every icon-only control has a translated name.

## Testing Strategy

Implementation follows red-green-refactor.

Focused Pest coverage will prove:

- project task results are bounded, deterministic, and retain active query parameters;
- search, status, priority, assignee, attention, and sort filters remain scoped to the routed workspace and project;
- mixed or foreign identifiers are rejected without leaking or returning foreign data;
- metrics and attention counts describe the complete project rather than the current page;
- stable props are skipped during task-only partial reloads;
- archived, empty, filtered-empty, and populated projects return complete typed props;
- query counts stay bounded and representative SQLite plans use scoped indexes.

Frontend behavior tests will cover default-query omission, deterministic URL state, active-filter detection, and result plural selection. Source/design tests will cover Wayfinder, request cancellation, partial reloads, manual `InfiniteScroll`, semantic translations, mobile Sheet/Menu semantics, reduced motion, focus, and touch targets.

Live Herd verification will exercise desktop and 390-pixel mobile layouts in light and dark modes, filter combinations, reload persistence, clearing, manual loading, task detail, task creation, archived state, keyboard focus, overflow, page errors, console errors, and recent browser logs.

## Delivery Boundaries

This phase does not add milestones, project owners, budgets, goals, dependencies, portfolio reporting, drag-and-drop, automatic infinite scrolling, new task mutation semantics, a new database engine, or a new design family. Existing concurrent work remains outside the phase commits.
