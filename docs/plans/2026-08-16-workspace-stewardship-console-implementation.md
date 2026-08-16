# Workspace Stewardship Console Implementation Plan

Date: 2026-08-16

## Delivery Contract

Implement the approved portfolio, management-shell, and taxonomy-studio design on `main`. Preserve unrelated work, use existing Reka/Tailwind/Wayfinder components, add no dependency or schema, and follow failing-test → implementation → refactor for each behavior slice.

## Step 1: Lock The Contracts In Red

- Add a discovered TypeScript behavior test for portfolio filtering/sorting, category validation, and taxonomy summary counts.
- Extend workspace source-contract coverage for a dedicated responsive management navigator, category switcher, and per-row action menus.
- Update the management feature test to require section-specific empty/loaded props and add query-budget coverage where it proves the optimization.
- Run only the new frontend test and focused Pest filters; confirm each fails for the missing behavior rather than syntax or fixture errors.

## Step 2: Build Typed Workspace Presentation Helpers

- Add a focused workspace-stewardship TypeScript module with the supported sort/category unions and pure portfolio/taxonomy helpers.
- Replace the page-local portfolio filtering/sorting implementation with the tested helper while retaining immutable props and locale-aware comparison.
- Keep definitions small and typed; do not add a store for page-local state.
- Re-run the new frontend behavior test to green.

## Step 3: Rebuild The Portfolio Hierarchy

- Convert workspace cards to content-driven operational cards that fill the available width naturally.
- Add localized result status and complete workspace/member/project/task portfolio totals.
- Keep Manage/Switch visible and move Edit/Duplicate/Delete into a Reka action menu with connected-trigger focus behavior.
- Preserve all existing HTTP forms, permission checks, confirmations, partial reloads, empty states, and Wayfinder navigation.
- Run focused frontend/design/localization and workspace portfolio tests.

## Step 4: Add The Responsive Management Navigator

- Extract the four section links into a typed `WorkspaceManagementNavigation` component.
- Render a current-section dropdown below `lg` and the desktop segmented navigation at `lg` and above.
- Apply `aria-current`, 44-pixel targets, generated Wayfinder URLs, Inertia prefetching, dark/reduced-motion styling, and long-label wrapping.
- Replace the clipped navigation in `workspaces/Show.vue` and retain the current section content components.
- Run the source-contract test and browser-check mobile navigation before continuing.

## Step 5: Build The Progressive Taxonomy Studio

- Add a four-category switcher with counts and one active rendered collection.
- Scope search to the selected category and clear conflicting local edit/delete state when changing category.
- Render only the active status, priority, label, or tag surface while preserving every existing form and backend mutation.
- Replace repeated definition and metadata row actions with accessible Reka action menus; keep destructive labels and confirmation/replacement behavior explicit.
- Preserve focus after menu close/cancel and announce async outcomes through the existing toast/status patterns.
- Run focused task-definition, label/tag, frontend behavior, type, lint, and format checks.

## Step 6: Bound Section Data Reads

- Update `WorkspaceManagementController` so Members/Danger resolve the roster, Members resolves invitations, and Configuration resolves taxonomy collections; other sections receive empty collections.
- Keep authorization before reads and keep resources query-free.
- Update the failing feature/query tests to green and run the complete workspace/membership/configuration gate.
- Run Pint on changed PHP files and Larastan after focused Pest passes.

## Step 7: Browser And Accessibility QA

- Verify `/workspaces` and Overview, Members, Configuration, and Danger routes at desktop and mobile widths.
- Exercise search/sort, workspace menus, mobile section selection, all taxonomy categories, representative action menus, dialog cancel focus, dark mode, reduced motion, forced colors, and long translated/custom labels.
- Assert no horizontal overflow, current console/page errors, clipped navigation, or undersized touched controls. Re-read recent Boost browser logs.

## Step 8: Review, Full Gates, And Delivery

- Request an independent read-only review of the complete diff and fix every Critical, High, or Medium finding with a failing regression first.
- Run focused Pest, complete parallel Pest, frontend tests, Pint, Larastan, Vue types, ESLint, Prettier, production build, Composer/npm validation and audits, and `git diff --check`.
- Record exact results and limitations in `docs/progress.md`.
- Commit only the coherent implementation/review/progress files, push `main` to `origin`, and verify local HEAD equals `origin/main` with a clean worktree.

