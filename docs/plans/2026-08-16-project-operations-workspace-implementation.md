# Project Operations Workspace Implementation Plan

## Delivery Status

Completed and verified on 2026-08-16. All seven tasks were implemented. Independent review findings for localization, archived-project mutation, partial-reload query execution, duplicate filter submission, attention continuation, archived priority truth, timezone boundaries, production sort plans, and mobile reading order were fixed with regressions. Exact verification and delivery evidence is recorded in `docs/progress.md`.

## Objective

Deliver the approved Project Operations Workspace design as an isolated phase on `main`: validated project-task filters, bounded manual pagination, accurate project pulse and attention data, simplified accessible actions, refined Warm Precision UI, English/Lithuanian/Russian copy, tests, browser QA, progress documentation, commits, and pushes.

## Constraints

- Preserve Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, SQLite, existing project/task actions, and the fixed Warm Precision system.
- Keep every task, definition, member, and project query scoped to the authorized routed workspace and project.
- Introduce no package, project-planning domain, or database migration unless a failing SQLite query-plan test proves an additive index is required.
- Reuse `TaskDetail`, `TaskCreateDialog`, `WorkspaceConfirmDialog`, and existing Reka/shadcn-style primitives.
- Follow red-green-refactor: each behavior change begins with a focused test that fails for the intended missing behavior.

## Task 1: Establish Backend Contracts With Failing Tests

### Files

- Create `tests/Feature/ProjectOperationsPageTest.php` with `php artisan make:test --pest ProjectOperationsPageTest --no-interaction`.
- Modify `tests/Feature/PageQueryBudgetTest.php` only when query-count or query-plan coverage requires it.

### RED coverage

Add focused tests proving:

1. default project task results contain at most 25 rows, use deterministic ordering, expose a paginator, and keep page two reachable;
2. search, status, priority, assignee, attention, and sort filters preserve their query string and return only matching project tasks;
3. status, priority, and assignee identifiers from another workspace are rejected without returning or mutating foreign data;
4. project metrics use the complete active project task set rather than the current page;
5. attention counts and the five-row attention preview use open overdue and next-seven-day tasks with deterministic ordering;
6. assignee options are bounded and retain a valid selected workspace member;
7. task-only partial reloads omit stable metrics, definitions, assignees, attention, project, and workspace props;
8. archived, empty, filtered-empty, and populated projects retain complete normalized filter and resource shapes;
9. project page queries remain bounded as unrelated and project task volume grows.

Run `php artisan test --compact tests/Feature/ProjectOperationsPageTest.php` and confirm failures come from the missing request, paginator, metrics, and stable-prop contracts.

## Task 2: Implement The Scoped Project Read Boundary

### Files

- Create `app/Http/Requests/ProjectShowRequest.php` with Artisan.
- Modify `app/Queries/ProjectDetailQuery.php`.
- Modify `app/Http/Controllers/ProjectController.php`.
- Add a project-task-specific migration only if the final SQLite query plan requires one; otherwise make no schema change.

### Implementation

1. Validate and normalize `search`, `status`, `priority`, `assignee`, `attention`, `sort`, and `page` in `ProjectShowRequest`.
2. Verify status, priority, and assignee identifiers against the routed workspace without leaking foreign identifier existence.
3. Replace the unbounded project task collection with a 25-row paginator from `$workspace->todos()` constrained to the routed project, active tasks, required columns, bounded eager loads, deterministic ordering, and `withQueryString()`.
4. Add focused query methods for complete-project metrics, open-priority distribution, at-most-five attention tasks, and a 100-row assignee option list plus a valid selected assignee.
5. Keep attention semantics explicit: open overdue tasks plus open tasks due from today through the next seven days; unassigned remains a separate filter and pulse value.
6. Keep `ProjectController::show` as orchestration only and wrap the paginator in `Inertia::scroll()`.
7. Wrap stable props in typed closures so task/filter partial reloads do not execute their queries.
8. Verify representative filter and order plans with SQLite `EXPLAIN QUERY PLAN`; add a reversible composite index only when current indexes cannot cover the production SQL.

Run the new focused test until green, then run `tests/Feature/PageQueryArchitectureTest.php`, `tests/Feature/PageQueryBudgetTest.php`, `tests/Feature/ProjectTest.php`, and relevant workspace isolation tests.

## Task 3: Establish Frontend Contracts With Failing Tests

### Files

- Create `resources/js/components/ProjectOperations.test.ts` at the depth discovered by the standard frontend test command.
- Create `tests/Feature/ProjectOperationsFrontendTest.php` with Artisan.

### RED coverage

Add behavior tests proving:

1. default filter values are omitted from the URL;
2. non-default query state is deterministic and stable;
3. active-filter detection and filter-count behavior are correct;
4. result plural categories use locale-aware rules.

Add source/design tests proving:

1. the page uses generated Wayfinder routes, `router.cancelAll()`, narrow partial reloads, pagination reset, and manual `InfiniteScroll`;
2. focused header, pulse, filter, and queue components exist;
3. desktop controls and the mobile Sheet expose labels, selected state, active-filter status, clear behavior, and 44-pixel targets;
4. the project action Menu exposes keyboard semantics and action-scoped loading states;
5. loading, empty-project, filtered-empty, and terminal pagination states use complete semantic messages;
6. all copy exists with matching shapes in English, Lithuanian, and Russian;
7. dark mode, reduced motion, focus, and no unsafe dynamic Tailwind classes remain explicit.

Run the new frontend unit test and Pest source/design test and confirm they fail because the project-operations helpers/components are missing.

## Task 4: Build The Warm Precision Project Operations Interface

### Files

- Create `resources/js/components/project/project-operations.ts`.
- Create `resources/js/components/project/ProjectOperationsHeader.vue`.
- Create `resources/js/components/project/ProjectPulse.vue`.
- Create `resources/js/components/project/ProjectTaskFilters.vue`.
- Create `resources/js/components/project/ProjectTaskQueue.vue`.
- Refactor `resources/js/pages/projects/Show.vue` into page coordination.
- Extend project-operation types in `resources/js/types/models.ts` or a focused component contract when that is narrower.
- Modify `lang/en/ui.php`, `lang/lt/ui.php`, and `lang/ru/ui.php`.
- Update existing frontend design/localization contracts only where the approved hierarchy intentionally changes them.

### Implementation

1. Add typed paginator, filter, metric, attention, assignee, and priority-distribution contracts plus pure query construction and plural selection.
2. Build a compact project identity header with a back link, primary task action, archived state, and accessible action Menu for duplicate and archive or restore.
3. Build a project pulse with honest completion progress, overdue/due-soon/unassigned counts, and textual priority distribution.
4. Build desktop filter controls and a mobile Sheet for search, status, priority, assignee, attention, sort, filtered count, and clear action.
5. Build a flat, scannable task queue around manual `InfiniteScroll`, complete task metadata, task-detail selection, row-scoped completion/deletion states, and distinct empty states.
6. Coordinate URL-backed Wayfinder visits with request cancellation, `preserveState`, narrow `only` props, and `reset: ['todos']`.
7. Preserve selected-task identity isolation and reload only the props required after task create, edit, complete, or delete.
8. Add complete semantic English, Lithuanian, and Russian copy with full messages, locale-aware counts, and English fallback.
9. Preserve semantic tokens, Instrument Sans, Lucide icons, light/dark modes, visible focus, reduced motion, translated wrapping, and zero mobile document overflow.

Run the focused frontend tests until green, followed by Vue type checking, ESLint, Prettier verification, and the production build.

## Task 5: Focused Review And Verification

1. Run Pint on the phase PHP files with `vendor/bin/pint --format agent` and avoid formatting unrelated files.
2. Run focused Pest coverage for project operations, project actions, workspace isolation, translations/design, and page query budgets.
3. Run PHPStan for the touched backend and then the repository command.
4. Run `npm run test:frontend`, `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run build`.
5. Review the diff for workspace leakage, invalid identifiers, hidden N+1 queries, unbounded collections, deterministic ordering, immutable props, translation fragments, focus, touch targets, and accidental unrelated changes.
6. Dispatch the independent code-review subagent required by the review skill and resolve every critical and important finding.

## Task 6: Live Browser Verification

Use Laravel Boost to resolve the project URL and inspect recent browser logs. Use the persistent browser to verify:

- 1440-pixel desktop light and dark modes;
- 390-by-844 mobile light and dark modes;
- each filter updates URL and results and survives reload;
- clear filters returns to the canonical project route;
- manual loading reaches later tasks without duplicates or order drift;
- task detail, completion, deletion confirmation, creation, duplicate, archive, and restore retain correct pending states;
- archived projects disable task creation and communicate why;
- keyboard order, focus, labels, live regions, and the action Menu/Sheet are correct;
- no document overflow, page errors, console errors, failed responses, or fresh Boost browser errors.

Temporary task data created solely for page-two verification must use factories in tests or be recoverably removed after browser QA.

## Task 7: Full Verification And Delivery

1. Run the complete Pest suite, Pint, Larastan/PHPStan, frontend unit tests, Vue type checking, ESLint, Prettier, production build, Composer/npm audits, and `git diff --check`.
2. Append the Project Operations preflight, implementation, checks, limitations, and delivery status to `docs/progress.md`.
3. Stage only Project Operations files and inspect the cached diff.
4. Commit implementation as `feat: build project operations workspace` and push `origin main`.
5. Commit the progress record as `docs: record project operations workspace` and push `origin main`.
6. Report exact command outcomes, commit hashes, push status, and any externally blocked verification.
