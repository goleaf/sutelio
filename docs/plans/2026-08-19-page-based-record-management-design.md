# Page-Based Record Management Design

## Scope And Protected Exceptions

Sutelio will stop using modal dialogs and sheets for ordinary record creation, editing, detail viewing, duplication, deletion, and child-record confirmation. Record work belongs to a stable page URL or to an inline confirmation region inside the record's existing page.

The mandatory onboarding implementation is outside this change. The root-level `GlobalBusyOverlay` is a permanent non-dismissible exception and remains the only blocking operation surface. The first-run language chooser, authentication-security ceremonies, mobile navigation drawer, responsive filter sheet, menus/selects, and the calendar/date-picker overlay are not record-management surfaces and remain governed by their existing accessibility contracts.

The shared date picker is changed only at its lowest shared primitive. Selecting any calendar day explicitly closes the calendar. On phone widths the calendar occupies the usable dynamic viewport with safe-area-aware edges; tablet and desktop retain a bounded popover. Weekend headings and day cells receive a restrained Warm Precision weekend surface plus text semantics that do not rely on color alone or override selected/today/disabled states.

## Audit Inventory

The baseline contains four modal-first record paths:

1. `ProjectCreateDialog` is mounted by the project collection.
2. `TaskCreateDialog` is mounted by task and project collections.
3. `TaskDetail` opens task records in a sheet even though `tasks/Show.vue` already provides the canonical detail page.
4. Workspace portfolio creation/edit/duplicate/delete, workspace overview edit, and member removal use local dialogs.

Twelve active feature consumers also use `WorkspaceConfirmDialog`: task attachments, checklists, comments, task deletion, project task deletion, workspace metadata, membership, danger actions, backup restore, and security state changes. These confirmations are rendered as modal overlays today.

Two sheet consumers are intentionally not record-management: `FilterSheet` and the mobile application sidebar. They remain because they expose transient navigation/filter state, not CRUD state. `FirstRunLanguageDialog`, passkey/two-factor/account security surfaces, and onboarding dialogs are separately classified and not silently rewritten as ordinary CRUD.

## Selected Interaction Model

### Dedicated pages

- Project creation: `GET /workspaces/{workspace}/projects/create` -> `projects/Create`.
- Project editing: `GET /workspaces/{workspace}/projects/{project}/edit` -> `projects/Edit`.
- Project duplication: `GET /workspaces/{workspace}/projects/{project}/duplicate` -> `projects/Duplicate`; the existing authorized duplicate action runs only after the dedicated page is submitted.
- Task creation: `GET /workspaces/{workspace}/tasks/create` -> `tasks/Create`, with an authorized optional project query value.
- Task selection from task/project queues navigates to the existing canonical `todos.show` page.
- Workspace creation: `GET /workspaces/create` -> `workspaces/Create`.
- Workspace editing: `GET /workspaces/{workspace}/edit` -> `workspaces/Edit`.
- Workspace duplication: `GET /workspaces/{workspace}/duplicate` -> `workspaces/Duplicate`, preserving the existing authorized duplicate action on submit.
- Workspace deletion shortcuts navigate to the existing workspace danger page, where confirmation and mutation remain in page flow.

Forms are extracted into focused reusable page-form components. Existing authorized Form Requests and Actions remain the write boundary; no validation, workspace isolation, or API route is weakened.

### Inline confirmation regions

`WorkspaceConfirmDialog` is replaced by a shared non-modal `PageConfirmPanel`. It preserves localized title/description, optional typed confirmation, destructive tone, processing state, keyboard focus, cancellation, and server authorization. Opening it remembers the connected initiating control, inserts a visible page region, moves focus to the region heading, and scrolls it into view without trapping focus or obscuring the rest of the page. Closing restores focus to the initiator when it still exists.

This pattern is used for child records and high-risk actions already located on the correct task/workspace/settings page. It avoids multiplying one-off delete URLs while satisfying the no-record-modal rule.

### Calendar behavior

`DatePickerField` owns an explicit `open` model. Calendar day selection updates the value and closes the overlay for both day and minute granularities; time segments remain editable in the field after the day is chosen. The Today action uses the same close path. On widths below `640px`, content is fixed to the usable viewport, respects normalized safe-area variables, removes popover rounding, and scrolls internally. The package popper wrapper is neutralized at the same breakpoint so its inline transform cannot displace fixed content outside the viewport. Weekend dates come from `@internationalized/date` locale semantics, while header columns follow the explicit Sunday/Monday week-start preference.

## Accessibility And Responsive Contract

- Every dedicated page exposes one shell-owned `main` and one page `h1`.
- Cancel actions are normal Inertia links back to the owning collection/detail page.
- Forms retain visible labels, inline errors, disabled/processing states, 48/52-pixel coarse-pointer controls, EN/LT/RU copy, reduced-motion behavior, and forced-colors boundaries.
- Inline confirmations use a labelled region, programmatic heading focus, an error live region when applicable, and no focus trap.
- Calendar verification covers 320x568, 390x844, 820x1180, and 1440x1000; selected-day closure, weekend semantics, keyboard use, 200% reflow, reduced motion, forced colors, and no horizontal overflow are release gates.

## Data, Query, And Delivery Boundaries

No schema or migration is required. New page reads reuse existing workspace-scoped query objects and explicit resource projections. The expected query delta is zero for existing list/detail routes; new create pages perform only bounded catalog reads. Task creation exposes at most 100 ordered active project options, plus an explicitly selected authorized project when it falls outside that window, so query count and Inertia payload stay bounded.

The configured web database is never reset for verification. Final Android delivery happens only after source publication and APK/emulator gates. On exactly one authorized physical Samsung, only `com.goleaf.sutelio` may be force-stopped and cleared. That scoped reset intentionally removes the app-private SQLite database, NativePHP cookie store, WebView cookies, and guest session so the installed APK starts with zero users/workspaces/projects/tasks and a true first-run language state.
