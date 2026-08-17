# Sutelio UI Constructor System Implementation Plan

> **Execution:** Use the repository-required `main` workflow and execute this plan in small TDD slices. The responsive plan is a prerequisite, not a competing implementation. Preserve unrelated work and never migrate a consumer that is not reachable from current routes/imports without an explicit product decision.

**Goal:** Make Sutelio's interface assemble from a small, coherent set of shared primitives and bounded domain compositions so visual, responsive, accessibility, loading, validation, and state changes are normally made once at the lowest correct layer.

**Architecture:** Keep four layers with one-way ownership: semantic CSS tokens/utilities; `components/ui` interaction primitives; `components/shared` product presentation primitives; focused domain compositions. Pages arrange domain components and supply data, but do not invent repeated control, field, page-frame, dialog, empty-state, pagination, or navigation markup.

**Non-goals:** No schema, query, route, authorization, localization-copy, package, runtime-theme, or business-workflow change. Do not create a schema-driven page builder, universal mega-form, generic card for every surface, or prop-heavy component that hides domain semantics. Do not delete or revive passkey, command-palette, legacy shell, or redirect-only settings surfaces without a separate reachability and product decision.

## Dependency And Conflict Rules

- Complete and publish `2026-08-17-sutelio-responsive-style-optimization.md` before modifying its active file set.
- Rebase this plan's source inventory on the committed responsive result before each slice.
- The motion/icon plan remains authoritative for bounded animation and Lucide semantics, except:
    - `IconTile` stays an independent primitive composed by headings; `LeadingIconHeading` must not absorb tone/size/tile policy props.
    - A page or section receives an icon only when the icon adds navigation, status, or recognition value. Decorative uniformity is not a requirement.
    - Only active import/route consumers are migration targets.
- When a repeated pattern has fewer than three semantically equivalent active consumers, prefer local clarity until another use proves the abstraction.
- Every shared component exposes slots and a deliberately small variant vocabulary; no arbitrary configuration objects for markup generation.

## Target Component Layers

### `components/ui`

Own native semantics, focus, disabled state, keyboard behavior, ARIA wiring, base dimensions, and variants:

- `Button`: variants, sizes, destructive appearance, pending/loading contract.
- `Input`, `Textarea`: native control styling and invalid/disabled behavior.
- Existing Dialog, Sheet, Menu, Select, Tabs/Toggle primitives: interaction and accessibility only.

### `components/shared`

Own repeatable product presentation and cross-domain composition:

- `WorkspacePageFrame`
- `WorkspacePageHeader`
- `LeadingIconHeading`
- `IconTile`
- `Field`
- `SearchField`
- `ColorSwatch`
- `SectionHeading`
- `InlineState`
- `EmptyState`
- `SurfacePanel`
- `DialogBody`
- `DialogActions`
- `ResponsiveSectionNavigation`
- `WorkspaceSegmentedControl`
- `FilterSheet`
- `PaginationBar`
- `ResultSummary`

### Domain compositions

Own domain vocabulary and data shape while reusing shared primitives:

- onboarding mode, preview, and field compositions;
- task title/details/taxonomy/assignee/due-date fields shared between create and update flows;
- calendar task item shared by agenda/week/month/attention views;
- project/task/activity filter field groups;
- focused workspace configuration sections.

## Phase 0: Reconcile Current Work And Lock Architecture

**Files:**

- Modify: this plan only if the committed responsive implementation differs.
- Modify append-only: `docs/progress.md`
- Modify through Laravel Boost `record-rule`: `.ai/rules/resources.md`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] Wait until the active responsive worktree changes stop and identify the exact owned commit/diff.
- [ ] Re-run the component/reachability inventory from current `HEAD` plus the preserved worktree.
- [ ] Update stale source assertions that forbid a class everywhere when the class has intentionally moved into a shared primitive.
- [ ] Add architecture guards for:
    - canonical page frame ownership;
    - no legacy page-wrapper literal in active page consumers;
    - shared fields owning label/help/error spacing and ARIA association;
    - shared dialogs owning body/actions spacing;
    - active consumers only in mandatory migration datasets.
- [ ] Record the durable rule: new UI must first reuse or extend the lowest coherent existing primitive; page-local repetition requires a documented semantic reason.
- [ ] Run the focused test RED and record why each failure is expected.

## Phase 1: Page Frame And Responsive Foundation

**Prerequisite:** Complete the active responsive plan without mixing later constructor primitives.

- [ ] Ensure all twelve audited active surfaces use `WorkspacePageFrame`.
- [ ] Preserve onboarding's intentional content width via a small class override, not a second frame.
- [ ] Keep page background, safe-area gutters, maximum width, shrink safety, and vertical rhythm in one owner.
- [ ] Resolve old tests that inspect page-local `bg-muted/20` or viewport literals so they assert the new owner instead.
- [ ] Verify 320px, tablet, desktop, 200% zoom, coarse pointer, reduced motion, forced colors, and no document overflow.
- [ ] Commit as one independently revertible responsive foundation slice.

## Phase 2: Form Construction System

**Files:**

- Create: `resources/js/components/ui/textarea/Textarea.vue`
- Create: `resources/js/components/ui/textarea/index.ts`
- Create: `resources/js/components/shared/Field.vue`
- Modify: `resources/js/components/InputError.vue`
- Modify: active forms in bounded clusters.
- Test: `tests/Feature/FrontendDesignTest.php`
- Add/update focused frontend component tests when the project harness can assert emitted attributes/slots.

### Contract

`Field` owns label, optional description, control slot, error slot/message, required marker, spacing, stable IDs, and `aria-describedby`/`aria-invalid` wiring through slot props. `Input`, `Textarea`, Select, and custom controls retain their native semantics. The field does not own form state or validation logic.

- [ ] RED: prove the component and ID/description/error contract are absent.
- [ ] GREEN: implement `Textarea` matching `Input` height, focus, invalid, disabled, and responsive rules.
- [ ] GREEN: implement compound `Field` with typed props and slot-provided IDs.
- [ ] Migrate one representative simple form and verify rendering/validation focus.
- [ ] Migrate remaining semantically matching input/textarea/select groups in domain-sized commits.
- [ ] Remove only duplication made obsolete by migrated consumers.
- [ ] Verify EN/LT/RU long labels/errors, keyboard focus, screen-reader association, disabled and processing states.

## Phase 3: Action And Request-State System

**Files:**

- Modify: `resources/js/components/ui/button/Button.vue`
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/dropdown-menu/DropdownMenuItem.vue`
- Modify: active form/dialog action consumers.

### Contract

`Button` owns visual pending state with `loading`, `loadingLabel`, spinner placement, disabled semantics, stable width where practical, and icon suppression while pending. It never initiates requests. Destructive menu/button variants own destructive styling so consumers do not repeat class recipes.

- [ ] RED: add tests for loading, disabled, accessible name, size, and destructive contracts.
- [ ] Implement the minimal typed API without breaking `as-child` links.
- [ ] Migrate repeated spinner-plus-label actions in small domains.
- [ ] Replace manual destructive menu classes with the existing or corrected variant.
- [ ] Verify touch targets, keyboard activation, pending announcements, double-submit prevention, and reduced motion.

## Phase 4: Visual Identity And State Primitives

**Files:**

- Create: `IconTile.vue`, `ColorSwatch.vue`, `SearchField.vue`, `SectionHeading.vue`, `InlineState.vue`.
- Modify: `LeadingIconHeading.vue`, `EmptyState.vue`, selected active consumers.

### Contracts

- `IconTile`: icon enclosure only; bounded semantic tones and sizes; decorative by default.
- `LeadingIconHeading`: layout only; composes a caller-provided icon/tile and text stack.
- `ColorSwatch`: safe color marker with non-color accessible label/status supplied by consumer.
- `SearchField`: search icon, input, clear affordance, label contract, and pending state; no query/navigation logic.
- `SectionHeading`: section title/description/actions layout; no forced icon or decorative eyebrow.
- `InlineState`: compact empty/loading/error/success/info feedback inside an existing panel.
- `EmptyState`: full-region absence state only.

- [ ] RED/GREEN each primitive separately.
- [ ] Migrate manual icon containers only when their tone/size semantics match.
- [ ] Migrate all active search-input recipes to `SearchField` while keeping route/filter logic local.
- [ ] Migrate repeated dashed compact states to `InlineState`; keep genuine empty regions on `EmptyState`.
- [ ] Migrate repeated standalone section header layouts to `SectionHeading`.
- [ ] Verify icons never replace visible labels, color never carries status alone, and long translations wrap.

## Phase 5: Surface And Overlay Composition

**Files:**

- Create: `SurfacePanel.vue`, `DialogBody.vue`, `DialogActions.vue`.
- Modify: `WorkspaceDialogContent.vue` and active dialog consumers.

### Contracts

- `SurfacePanel` is limited to the proven flat bordered panel recipe and exposes padding/tone only when consumers demonstrate the variants.
- `DialogBody` owns scroll-safe body spacing.
- `DialogActions` owns border, spacing, responsive stacking/order, and action alignment.
- `WorkspaceDialogContent` owns shell/header/close/viewport behavior, not each dialog's domain body.

- [ ] RED: prove duplicated dialog body/action recipes and panel recipe exist in active consumers.
- [ ] Implement primitives and migrate one dialog through a browser-visible vertical slice.
- [ ] Migrate all matching active workspace dialogs.
- [ ] Migrate only the five audited flat panels; do not wrap complex or already semantic Card compositions.
- [ ] Verify focus trap/return, Escape policy, scroll containment, narrow viewport, 200% zoom, destructive confirmation, and long translations.

## Phase 6: Navigation, Segmentation, And Filters

**Files:**

- Create: `ResponsiveSectionNavigation.vue`, `FilterSheet.vue`.
- Modify: `SettingsSectionMenu.vue`, `WorkspaceManagementNavigation.vue`, `settings/Layout.vue`, `WorkspaceSegmentedControl.vue`, and active filters.

### Contracts

- `ResponsiveSectionNavigation` owns the shared mobile select/menu and desktop list presentation; callers supply typed items, current URL/key, icons, and navigation mechanism.
- `WorkspaceSegmentedControl` requires an explicit semantic mode: tabs with tab/tabpanel IDs and selection semantics, or group with pressed buttons. It must not default every use to `tablist`.
- `FilterSheet` owns trigger badge, bottom-sheet viewport/layout, header/footer, apply/reset action arrangement, and processing state. Domain `*FilterFields` components own filter labels/options/state.

- [ ] RED: add exact semantic tests for tabs versus pressed groups.
- [ ] Migrate settings and workspace-management navigation without changing routes.
- [ ] Migrate calendar/project controls to tabs and filters/view toggles to group semantics as appropriate.
- [ ] Extract filter field groups before wrapping them in `FilterSheet` to avoid duplicated desktop/mobile markup.
- [ ] Verify keyboard arrow/tab behavior, selected/pressed announcements, mobile sheet focus, route preservation, and loading/error states.

## Phase 7: Onboarding Domain Constructor

**Files:** active `resources/js/components/onboarding/**` only.

- [ ] Extract the repeated orange preview/header into a focused onboarding preview primitive composed from `IconTile`, `LeadingIconHeading`, and `SurfacePanel` where appropriate.
- [ ] Extract the repeated mode selector using explicit group semantics.
- [ ] Extract repeated create/select field layouts using `Field`, not a schema-driven form generator.
- [ ] Replace compact onboarding empty states with `InlineState` where semantically correct.
- [ ] Keep each step's state transitions and Inertia submission in the owning step/page.
- [ ] Verify recovery/replay, required skip, validation summary focus, EN/LT/RU, offline/error/processing, mobile and reduced motion.

## Phase 8: Task Domain Constructor

**Files:** active task create/detail/form/panel components.

- [ ] Inventory exact shared field contracts between Create and Overview.
- [ ] Extract focused controls for title, details, taxonomy, assignee, due date, and other proven repeated fields.
- [ ] Keep create/update lifecycle, permissions, transformations, and submission separate.
- [ ] Use `SectionHeading`, `DialogActions`, `Button loading`, `InlineState`, and `SurfacePanel` across checklist, attachments, reminders, comments, taxonomy, and overview panels only where semantics match.
- [ ] Never introduce one universal `TaskForm` with mode flags.
- [ ] Verify dirty-draft synchronization, immutable props, foreign workspace IDs, permission-disabled states, optimistic/rollback behavior, and localized validation.

## Phase 9: Calendar, Results, And Pagination

**Files:** active calendar, notification, activity, project, and task list components.

- [ ] Create `CalendarTaskItem` for the common task-link identity/status/date presentation with bounded slots for view-specific metadata.
- [ ] Migrate agenda, week, month, and attention consumers; preserve density differences via small explicit variants.
- [ ] Create `ResultSummary` for accessible result count/status copy without owning pluralization or query state.
- [ ] Create `PaginationBar` as a slot-based layout frame supporting link pagination and manual/infinite-load actions without conflating their behavior.
- [ ] Verify item link targets, keyboard names, overdue/unread non-color cues, pagination disabled/loading/end states, and no list-wide reanimation on refetch.

## Phase 10: Split Workspace Configuration

**Files:** `WorkspaceConfigurationPanel.vue` and new focused siblings in the existing workspace component directory.

- [ ] Characterize current permissions, mutations, validation, local-draft synchronization, and emitted behavior with focused tests before moving markup.
- [ ] Split the 900+ line panel by domain responsibility first.
- [ ] Reuse shared Field/Button/SectionHeading/Surface/Dialog primitives inside the focused panels.
- [ ] Extract a metadata collection only after labels and tags demonstrate a stable shared API.
- [ ] Preserve atomic workspace scoping and all policy-controlled visibility.
- [ ] Verify owner/admin/member matrices and every mutation flow.

## Phase 11: Reachability And Dead-Code Decision

- [ ] Rebuild reachability from current routes, layout imports, dynamic imports, tests, and generated action/route usage.
- [ ] Classify each candidate as active, compatibility boundary, planned feature, or dead.
- [ ] Do not use source-assertion tests as proof that a component is reachable.
- [ ] Present any product-visible ambiguity (notably passkeys) as an explicit decision before removal or restoration.
- [ ] Remove only confirmed dead files, their orphan state, and stale tests in a separate reversible commit.
- [ ] Never mix dead-code deletion with constructor migrations.

## Phase 12: Canonical Documentation And Enforcement

**Files:**

- Modify: `docs/design-system.md`
- Modify: `docs/frontend.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/testing.md`
- Modify: `docs/architecture.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/compliance-matrix.md`
- Modify append-only: `docs/progress.md`
- Modify through Boost: `.ai/rules/resources.md`

- [ ] Document the four component layers and ownership table.
- [ ] Document when to reuse, extend, compose, or intentionally keep local markup.
- [ ] Document stable variant vocabularies and prohibited mega-component patterns.
- [ ] Add a contribution checklist requiring global inventory before a new repeated UI recipe.
- [ ] Add source/architecture guards for canonical primitives and active consumer datasets without brittle formatting assertions.
- [ ] Preserve historical evidence; append current implementation and verification facts.

## Phase 13: Full Verification And Delivery

- [ ] PHP format: `vendor/bin/pint --dirty --format agent`.
- [ ] Static backend: `composer types:check`.
- [ ] Focused Pest after every slice; then `php artisan test --compact` sequentially.
- [ ] Fresh isolated SQLite migrations, deterministic seed twice, health, integrity, and foreign-key checks.
- [ ] Frontend: tests, Vue type check, ESLint, Prettier, npm audit, production Vite build.
- [ ] Composer: strict validation, locked audit, direct outdated report, platform requirements.
- [ ] Browser: isolated Chrome DevTools and Playwright sessions across guest/authenticated representative routes, EN/LT/RU, phone/tablet/desktop, 200% zoom, keyboard, touch, reduced motion, forced colors, loading/empty/error/success/disabled/offline.
- [ ] Query delta: confirm zero new server query paths; the refactor is presentational.
- [ ] Inspect complete and staged diffs, generated outputs, lock files, and secret patterns.
- [ ] Commit each verified slice with Conventional Commits and push normally to `origin/main` after fetch/linearity checks.
- [ ] Record exact commands, test/assertion counts, bundle delta, browser evidence, commit hashes, push status, limitations, and intentionally deferred product decisions.

## Definition Of Done

- A normal page is assembled from page frame, page header, focused domain sections, and shared states/actions rather than copying layout recipes.
- A normal form is assembled from Field plus native UI controls and a loading-aware Button; label/help/error/ARIA changes require one shared edit.
- Dialog spacing/action behavior, empty/loading/error states, search, navigation, segmentation, pagination, icon enclosures, and flat panels each have one coherent owner.
- Domain state and business workflow remain explicit in domain components; no generic configuration engine obscures them.
- Active repeated recipes are either migrated or documented with a semantic reason for remaining local.
- All required checks are fresh and factual, docs/rules match implementation, commits are attributable, and local/remote `main` are synchronized.
