# Activity Intelligence Implementation Plan

## Objective

Deliver the approved Activity Intelligence design as an isolated phase on `main`: validated server-side filters, complete event taxonomy, privacy-safe serialization, accurate metrics, accessible manual pagination, refined Warm Precision UI, EN/LT/RU copy, tests, browser QA, progress documentation, commits, and pushes.

## Constraints

- Preserve the concurrent production-modernization worktree and its edits to shared frontend files, routes, documentation, dependencies, factories, and seeders.
- Keep SQLite as the only relational database and introduce no dependency or schema change.
- Keep the existing Activity route names and generated Wayfinder surface.
- Preserve the concurrent `max-w-app` modernization change already present in the Activity page.
- Follow red-green-refactor: every behavior change begins with a focused failing test that fails for the intended missing behavior.

## Task 1: Establish Backend Contracts With Failing Tests

### Files

- Modify `tests/Feature/ActivityPageTest.php`.

### RED coverage

Add focused tests proving:

1. all `ActivityEvent` values belong to exactly one `ActivityCategory`;
2. category filters include every mapped event and exclude unrelated categories;
3. contributor filters accept current workspace members and reject foreign member identifiers without returning foreign activity;
4. period filters apply bounded date thresholds;
5. metrics use the complete workspace ledger instead of the current page;
6. page two remains reachable and paginator links preserve active filters;
7. the Inertia resource includes safe subject labels while omitting raw properties, workspace/user foreign keys, and sensitive old/new values;
8. no-workspace, empty-ledger, and filtered-empty responses retain valid typed props.

Run `php artisan test --compact tests/Feature/ActivityPageTest.php` and confirm failures are caused by the missing category/request/query/resource contracts.

## Task 2: Implement The Backend Activity Boundary

### Files

- Create `app/Enums/ActivityCategory.php`.
- Create `app/Http/Requests/ActivityIndexRequest.php`.
- Modify `app/Queries/ActivityIndexQuery.php`.
- Modify `app/Http/Resources/ActivityLogResource.php`.
- Modify `app/Http/Controllers/ActivityController.php`.

### Implementation

1. Define exhaustive category-to-event mappings in `ActivityCategory`.
2. Validate `category`, `actor`, `period`, and `page` in `ActivityIndexRequest`; expose normalized state and verify actor membership against the authorized workspace before querying activity.
3. Apply category, actor, and period constraints in `ActivityIndexQuery`; select required columns, eager-load minimal actor columns, sort by `created_at` and `id`, paginate at twenty items, and retain query parameters.
4. Add workspace-wide conditional aggregates and ordered contributor options to the query object.
5. Serialize through `ActivityLogResource`, allowlisting only safe subject label and changed-field metadata.
6. Keep `ActivityController` thin and use `Inertia::scroll()` for the resource paginator.
7. Preserve zeroed state for authenticated users without a workspace.

Run the focused Activity page test until green, then run `tests/Feature/WorkspacePagesTest.php` and `tests/Feature/PageQueryBudgetTest.php` to catch isolation and query-budget regressions.

## Task 3: Establish Frontend Contracts With Failing Tests

### Files

- Create `resources/js/components/activity/activity-types.test.ts`.
- Create `tests/Feature/ActivityIntelligenceFrontendTest.php`.

### RED coverage

Add tests proving:

1. filter query construction omits default values and resets pagination;
2. complete non-default state is encoded deterministically;
3. the page uses generated Wayfinder routes, `router.cancelAll()`, partial reloads, and manual `InfiniteScroll`;
4. focused filter and timeline components exist;
5. the mobile filter Sheet, Select labels, loading status, filtered-empty state, 44-pixel controls, reduced-motion behavior, and semantic translations are wired.

Run the new frontend unit test and Pest source-contract test and confirm they fail for missing modules/components.

## Task 4: Build The Warm Precision Activity Workspace

### Files

- Create `resources/js/components/activity/activity-types.ts`.
- Create `resources/js/components/activity/ActivityFilterPanel.vue`.
- Create `resources/js/components/activity/ActivityTimeline.vue`.
- Modify `resources/js/pages/activity/Index.vue` on top of the concurrent `max-w-app` change.
- Modify the `ActivityLog` interface in `resources/js/types/models.ts`.
- Modify `resources/js/types/workspace-ui.ts`.
- Modify `lang/en/workspace.php`.
- Modify `lang/lt/workspace.php`.
- Modify `lang/ru/workspace.php`.

### Implementation

1. Add typed filter, metric, contributor, paginator, and category contracts plus a pure query-state builder.
2. Build a desktop filter rail with category controls, contributor Select, period Select, filtered count, and clear action.
3. Reuse the accessible Sheet primitive for mobile contributor/period controls while keeping category actions visible.
4. Build the grouped editorial timeline around manual `InfiniteScroll`, safe labels, complete translated event presentation, loading/terminal states, and empty versus filtered-empty states.
5. Refactor the page into coordination only: typed props, URL-backed Wayfinder visits, request cancellation, partial reload state, metrics, and component composition.
6. Preserve semantic surfaces, restrained orange emphasis, Lucide icons, dark mode, reduced motion, visible focus, and no mobile document overflow.
7. Add complete English, Lithuanian, and Russian translation keys and matching TypeScript copy types.

Run the focused frontend tests until green, followed by Vue type checking, targeted ESLint, Prettier verification, and the production build.

## Task 5: Review And Focused Verification

1. Run Pint on dirty PHP files with `vendor/bin/pint --dirty --format agent`.
2. Run focused Pest coverage for Activity, workspace isolation, localization/design contracts, and page query budgets.
3. Run targeted PHPStan for touched backend files, then the repository PHPStan command.
4. Run `npm run test:frontend`, `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run build`.
5. Review the phase diff for security, privacy, N+1 queries, deterministic ordering, type completeness, accessibility, and accidental inclusion of concurrent modernization changes.
6. Request an independent code review as required by the review skill and resolve every critical or important finding.

## Task 6: Live Browser Verification

Use Laravel Boost to resolve the Activity URL and inspect recent browser logs. Use the persistent local browser to verify:

- 1440-pixel desktop light and dark modes;
- 390-by-844 mobile light and dark modes;
- category, contributor, and period changes update the URL and results;
- reload retains the selected state;
- clear filters returns to the canonical route;
- manual loading reaches older records and disables while pending;
- keyboard focus and accessible labels are visible and correct;
- no horizontal document overflow, page errors, console errors, or fresh Boost browser errors.

Any temporary live data created solely for pagination verification must be removed or restored afterward.

## Task 7: Full Verification And Delivery

1. Run the complete Pest suite, Pint, Larastan/PHPStan, frontend unit tests, Vue type checking, ESLint, Prettier, production build, Composer/npm audits, and `git diff --check`.
2. Append the Activity Intelligence preflight, implementation, checks, limitations, and delivery status to `docs/progress.md` without staging the concurrent modernization section.
3. Stage only Activity Intelligence files and verify the cached diff.
4. Commit implementation as `feat: build activity intelligence workspace` and push `origin main`.
5. Commit the isolated progress record as `docs: record activity intelligence workspace` and push `origin main`.
6. Report exact command results, commit hashes, push status, and remaining Phase 10 work.
