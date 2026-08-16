# Task Focus Desk Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Execute this plan task-by-task with red-green-refactor checkpoints and independent review before delivery.

**Goal:** Build the approved Progressive Task Focus Desk with discoverable URL-backed focus filters and a safer, lower-density task queue.

**Architecture:** Keep `TodoIndexController`, `TodoIndexQuery`, existing mutations, and pagination authoritative. Extend the existing typed filter state, add a pure focus helper and focused results component, and refactor list presentation around explicit selection mode and Reka menu actions.

**Tech Stack:** Laravel 13, PHP 8.4-compatible syntax, Inertia 3, Vue 3 Composition API, TypeScript, Tailwind CSS 4, Reka/shadcn-style primitives, Wayfinder, Pest 5, Node test runner, SQLite.

---

### Task 1: Record The Phase And Establish Failing Contracts

**Files:**
- Modify: `docs/progress.md`
- Modify: `tests/Feature/TaskIndexWorkflowTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`
- Create: `resources/js/components/TaskFocusDesk.test.ts`

- [ ] Add failing request/response cases proving `overdue`, `completed_today`, `is_pinned`, and `is_favorite` normalize as booleans, survive the URL-backed response state, combine with taxonomy filters, and remain workspace-scoped.
- [ ] Add failing source contracts for `TaskResultsBar`, explicit `selectionMode`, a Reka dropdown menu, no full-row overlay, a mobile active-filter count, a concise live result node, 44-pixel controls, and EN/LT/RU key parity.
- [ ] Add failing direct TypeScript cases importing `activeTaskFilterCount`, `toggleTaskFocusFilter`, and `clearTaskFilters` from the not-yet-created pure helper.
- [ ] Run `php artisan test --compact tests/Feature/TaskIndexWorkflowTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendLocalizationTest.php` and confirm failure for the missing contracts.
- [ ] Run `node --experimental-strip-types --test resources/js/components/TaskFocusDesk.test.ts` and confirm failure because `task-focus.ts` is absent.

### Task 2: Implement Pure Focus State And Request Normalization

**Files:**
- Create: `resources/js/components/task/task-focus.ts`
- Modify: `resources/js/types/api.ts`
- Modify: `app/Http/Requests/TodoIndexRequest.php`
- Modify: `tests/Feature/TaskIndexWorkflowTest.php`
- Modify: `resources/js/components/TaskFocusDesk.test.ts`

- [ ] Define `TaskFocusFilter = 'completed_today' | 'is_favorite' | 'is_pinned' | 'overdue'` and a stable list of countable filter keys.
- [ ] Implement `activeTaskFilterCount(filters)` so empty/default sort, ascending direction, default page size, and list view do not count as narrowing filters.
- [ ] Implement immutable `toggleTaskFocusFilter(filters, key)` and `clearTaskFilters(filters)`; clearing retains only the current `view` and default direction/page size.
- [ ] Normalize query booleans with `$this->boolean()` after validation so `overdue=0` is false and `overdue=1` is true, and omit false flags from canonical response state.
- [ ] Run the direct Node test and focused task-index Pest file; expect all new behavior cases to pass.

### Task 3: Build Focus And Results Controls

**Files:**
- Create: `resources/js/components/task/TaskResultsBar.vue`
- Modify: `resources/js/components/task/TaskFilterBar.vue`
- Modify: `resources/js/pages/tasks/Index.vue`
- Modify: `lang/en/ui.php`
- Modify: `lang/lt/ui.php`
- Modify: `lang/ru/ui.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`

- [ ] Add complete localized messages for focus labels, filter counts, visible result ranges, selection-mode actions, row menu actions, and filtered empty guidance in all three locales.
- [ ] Extend `TaskFilterBar` local state with the four booleans, synchronize them from immutable props, render pressed focus buttons, and include them in every emitted filter payload.
- [ ] Display a numeric active-filter badge and screen-reader-visible status in the closed mobile trigger. Clear all filters through the pure helper without changing list/board view.
- [ ] Build `TaskResultsBar` with one concise `aria-live="polite"` result summary and explicit Enter/Exit selection controls; render page selection only while selection mode is active.
- [ ] Update the page coordinator to own `selectionMode`, clear it on view/filter/page-affecting operations, and mark the collection `aria-busy` during partial visits.
- [ ] Run localization, design, task-index, and direct frontend behavior tests; expect green.

### Task 4: Refactor The Task Queue Around Progressive Actions

**Files:**
- Modify: `resources/js/components/task/TaskList.vue`
- Modify: `resources/js/pages/tasks/Index.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `resources/js/components/task/TaskList.test.ts`

- [ ] Add a required `selectionMode` prop. Render the select-page control and row checkboxes only in that mode.
- [ ] Replace the absolute whole-row overlay with a semantic, minimum-44-pixel title/content button that emits `select` and has a complete localized accessible name.
- [ ] Keep completion as an independent 44-pixel action in normal mode and disable it only while a task mutation is active.
- [ ] Add the existing Reka dropdown primitives with visible Open and Delete items; emit `select` or `delete` and preserve the application confirmation boundary.
- [ ] Render translated status, priority, project, and due/overdue context with a two-line mobile title and wrapping metadata; keep color supplemental.
- [ ] Run task list direct tests plus focused Pest design/workflow tests; expect green.

### Task 5: Verify Real Filter And Mutation Behavior

**Files:**
- Modify: `tests/Feature/TaskIndexWorkflowTest.php`
- Modify: `tests/Feature/TodoTest.php` only if an existing mutation assertion must be extended

- [ ] Add representative tasks proving each focus flag and a combined overdue-plus-priority request returns only matching tasks and filtered stats.
- [ ] Add invalid boolean query cases and prove foreign-workspace tasks never enter the response or metrics.
- [ ] Confirm list/board pagination links retain focus query parameters through `withQueryString()`.
- [ ] Run `php artisan test --compact tests/Feature/TaskIndexWorkflowTest.php tests/Feature/TodoTest.php tests/Feature/PageQueryBudgetTest.php`; expect all cases and query budgets to pass.

### Task 6: Verify, Review, Document, And Deliver

**Files:**
- Modify: `docs/progress.md`
- Modify canonical docs only when evidence changes: `docs/frontend.md`, `docs/design-system.md`, `docs/accessibility.md`, `docs/testing.md`, `docs/known-limitations.md`, `CHANGELOG.md`

- [ ] Run `vendor/bin/pint --dirty --format agent` and the focused Pest/frontend files.
- [ ] Run `composer run types:check -- --memory-limit=1G`, `npm run test:frontend`, `npm run types:check`, `npm run lint:check`, `npm run format:check`, `npm run build`, and `git diff --check`.
- [ ] Run `php artisan test --compact --parallel`, `composer validate --strict --no-check-publish`, `composer audit --locked --no-interaction`, and `npm audit --audit-level=low`.
- [ ] Use live Herd browser QA at 1,440 by 1,000 and 390 by 844 for URL focus/reload/clear, selection mode, task open/close focus restoration, overflow delete confirmation, list/board, long translations, dark mode, reduced motion, forced colors, 44-pixel targets, zero overflow, and current browser/Boost logs.
- [ ] Request independent code review against this design and plan, resolve every Critical, High, and Medium finding, and rerun affected gates.
- [ ] Append exact files, decisions, checks, limitations, commits, and push results to `docs/progress.md`.
- [ ] Commit implementation as `feat: build task focus desk`, commit the completion record separately, and push `main` to `origin/main` without force or history rewriting.

## Plan Self-Review

- Spec coverage: Tasks 2-5 map to every selected design behavior; Task 6 covers accessibility, responsive QA, independent review, documentation, and delivery.
- Placeholder scan: no deferred product decision remains; each task names its files, behavior, and exact verification boundary.
- Type consistency: focus keys match `TodoFilters` and `TodoIndexRequest`; `TaskResultsBar`, `selectionMode`, `activeTaskFilterCount`, `toggleTaskFocusFilter`, and `clearTaskFilters` are stable across tests and production work.
