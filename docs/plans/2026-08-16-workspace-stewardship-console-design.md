# Workspace Stewardship Console Design

Date: 2026-08-16

## Goal

Turn the existing workspace portfolio and management routes into one calm, responsive stewardship experience. The phase covers the `/workspaces` portfolio, the shared workspace-management shell, and the task-configuration surface while preserving the current Laravel, Inertia, Vue, Wayfinder, policy, action, and SQLite contracts.

## Observed Baseline

- The live portfolio is functional and visually consistent, but a single workspace occupies one narrow 25rem card inside a mostly empty desktop surface. Its edit, duplicate, and delete actions remain permanently visible beside the primary management action.
- At 390 by 844 pixels, the management overview is 2,496 pixels tall and its four-section segmented control is clipped after “Task configuration” with no compact current-section control.
- The task-configuration route is 5,774 pixels tall and exposes 83 controls at once. Every status and priority repeats move, edit, default, archive, and delete actions; labels and tags repeat edit and delete buttons.
- The backend already has complete workspace-scoped policies, Form Requests, actions, resources, and task-definition constraints. This is an information-architecture, responsive-design, interaction-density, and read-efficiency phase rather than a domain rewrite.

## Design Direction

The interface adopts a **Warm Stewardship Ledger** direction: an editorial operational summary, strong current-workspace hierarchy, compact management navigation, and progressively disclosed configuration. It remains inside the fixed Warm Precision system—white/dark rounded surfaces, warm orange emphasis, quiet semantic colors, and restrained motion—without introducing a theme, package, or alternate frontend stack.

## Workspace Portfolio

- The current workspace becomes a full-width operational card with its name, description, owner, member/project/task totals, and one clear Manage action.
- Switching remains a direct secondary action for inactive workspaces. Edit, duplicate, and delete move into an accessible per-workspace action menu so destructive controls are not permanently prominent.
- Search, sorting, and a concise localized result summary remain available. The grid uses compact content-driven cards instead of fixed-height cards and gracefully fills a one-workspace portfolio.
- Empty, filtered-empty, processing, error, and success behavior continues to use the existing dialogs, HTTP forms, and toast boundary.

## Management Shell

- Desktop retains the four addressable Wayfinder routes: Overview, Members, Task configuration, and Danger zone.
- Mobile replaces the clipped horizontal strip with a Reka-backed current-section dropdown modeled on the settings section selector. It announces the current section, exposes `aria-current`, preserves browser navigation, and uses 44-pixel targets.
- Desktop navigation remains visible and prefetches likely section visits. The selected section uses text, icon, and surface treatment rather than color alone.
- The shared header preserves workspace totals and current-workspace state. Section content remains independently owned by focused components.

## Taxonomy Studio

- Task configuration becomes a four-category studio: Statuses, Priorities, Labels, and Tags.
- A compact category switcher shows localized names and counts. Only the active category is rendered, cutting initial mobile height and tab stops while retaining all existing CRUD, order, archive, default, completion-target, replacement, and usage-count behavior.
- Search applies to the active category and its label reflects that scope. Switching category clears conflicting edit/delete state and places focus on the newly active panel heading.
- Definition rows keep semantic badges and usage totals. Reorder, edit, set-default, completion-target, archive/restore, and delete operations move into one accessible action menu per row. Label and tag rows use the same pattern.
- Creation and edit forms stay adjacent to the collection they affect. Destructive replacement selection remains explicit and transactional through the current backend.

## Data And Performance

- Routes, policies, actions, resources, schema, and dependencies do not change.
- The management controller resolves member data only for Members and Danger, invitations only for Members, and taxonomy collections only for Task configuration. Irrelevant sections receive empty typed collections.
- Inertia section links use generated Wayfinder functions and prefetching. Mutation refreshes continue to request only the affected props.
- Vue props remain immutable. Local category, search, menu, dialog, and form state is synchronized without mutating server data.

## Accessibility And Responsive Behavior

- Interactive targets are at least 44 pixels; focus rings are visible; menu/dialog focus returns to a connected trigger.
- Mobile at 390 pixels has no document-level horizontal overflow, clipped navigation, or unbounded row action clusters.
- Text wraps safely for long English, Lithuanian, and Russian workspace, role, status, priority, label, and tag names.
- Status, default, archived, current, and destructive meaning is never conveyed by color alone.
- Dark mode, reduced motion, and forced colors retain readable borders, focus, and state cues.
- Result and mutation announcements remain concise; large changing regions are not marked live.

## Localization

All new visible copy uses stable semantic keys with English, Lithuanian, and Russian parity. Counts use locale-aware number formatting and full messages rather than concatenated sentence fragments.

## Verification

- Failing-first Pest coverage proves section-specific prop loading and the unchanged authorization/resource contract.
- Discovered TypeScript tests cover portfolio filtering/sorting, taxonomy summaries, category validation, and source contracts for the responsive navigation and action menus.
- Focused workspace, membership, label/tag, task-definition, design, localization, and query-budget tests run before the complete suite.
- Browser QA covers portfolio and all four management routes at 1,440-pixel desktop and 390-by-844 mobile, including dark mode, reduced motion, forced colors, keyboard focus, menus, category switching, and overflow.
- Pint, Larastan, Vue type checking, ESLint, Prettier, frontend tests, production build, dependency audits, and diff checks remain release gates.

## Non-Goals

- No new workspace lifecycle, role, invitation, taxonomy rule, drag-and-drop ordering, bulk taxonomy mutation, schema, dependency, API version, or real-time transport.
- No change to the owner-only transfer/delete boundary or the existing confirmation requirements.
