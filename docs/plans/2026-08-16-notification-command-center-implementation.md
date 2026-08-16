# Notification Command Center Implementation Plan

## Status

Completed and verified on 2026-08-16. All seven tasks were executed; exact focused/full commands, browser evidence, review corrections, commits, and push results are recorded in `docs/progress.md`.

## Objective

Deliver the approved Structured Signal Stream design as an isolated phase on `main`: typed user-scoped notification data, URL-backed status and kind filters, timezone-aware Today/Earlier grouping, focused accessible Vue components, English/Lithuanian/Russian copy, tests, live browser QA, progress documentation, commits, and pushes.

## Constraints

- Preserve Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, SQLite, the existing notification schema, and the Warm Precision design system.
- Scope every read and mutation to the authenticated user's notifications; a foreign notification identifier must never reveal or mutate another user's row.
- Keep deterministic bounded pagination and the existing mark-one and mark-all lifecycle. Do not add dismiss, snooze, archive, delete, bulk selection, WebSockets, Redis, packages, or migrations.
- Normalize presentation from structured notification type/data only. Never infer semantic kind from localized title or body text.
- Follow red-green-refactor: each behavior change begins with focused failing coverage.

## Task 1: Establish Backend Contracts With Failing Tests

### Files

- Modify `tests/Feature/NotificationInboxTest.php`.
- Modify `tests/Feature/PageQueryBudgetTest.php` only if production notification-query coverage is missing.

### RED coverage

Add focused tests proving:

1. `status=read` and each supported kind filter are server backed, preserve the query string, and never include another user's rows;
2. invalid status, kind, page, and page-size values are rejected;
3. reminders are classified from the notification type or explicit structured kind, while legacy payloads receive a safe General fallback;
4. actionable rows expose only an authorized server-generated task URL;
5. equal timestamps remain deterministically ordered across page boundaries without duplicates;
6. resource output contains semantic kind, read state, timestamp, and a user-timezone date bucket without resource-side queries;
7. read-one and read-all remain idempotent and user scoped;
8. query counts remain bounded as unrelated notification volume grows.

Run `php artisan test --compact tests/Feature/NotificationInboxTest.php` and confirm failures come from the missing read/kind/resource contracts.

## Task 2: Implement The Typed Notification Read Boundary

### Files

- Modify `app/Http/Requests/NotificationIndexRequest.php`.
- Modify `app/Queries/NotificationIndexQuery.php`.
- Create `app/Http/Resources/NotificationInboxResource.php` with Artisan.
- Modify `app/Http/Controllers/NotificationController.php`.

### Implementation

1. Validate and normalize `status`, `kind`, `per_page`, and `page`, including `all`, `unread`, and `read` status values plus `all`, `reminders`, and `updates` kinds.
2. Keep the query anchored to `$user->notifications()`, classify reminders by server-owned type or explicit payload kind, and treat every other structured or legacy row as an update.
3. Apply deterministic `created_at DESC, id DESC` ordering, bounded pagination, and `withQueryString()`.
4. Emit a minimal resource contract: ID, semantic kind, safe title/body inputs, authorized action URL, read state, ISO timestamp, and timezone date key. Ensure all required user preferences are loaded by the controller before resource transformation.
5. Keep aggregate inbox statistics independent of the current filter and wrap stable props in closures where partial reloads should avoid unnecessary work.
6. Keep `NotificationController` as orchestration only and preserve existing action boundaries.

Run the focused inbox and page-query tests until green, then run relevant notification action, authentication, and localization tests.

## Task 3: Establish Frontend Contracts With Failing Tests

### Files

- Create `resources/js/components/NotificationInbox.test.ts` at a depth discovered by the standard frontend test command.
- Create `tests/Feature/NotificationCommandCenterFrontendTest.php` with Artisan.

### RED coverage

Add pure behavior tests proving:

1. default filters are omitted from the URL while non-default filters serialize deterministically;
2. notifications group into Today and Earlier from the server date key without reordering rows;
3. each semantic kind maps to a stable icon/tone contract and unknown kinds fall back safely;
4. concise result-summary plural selection is locale aware.

Add source/design coverage proving:

1. the page uses generated Wayfinder routes, narrow partial visits, and request cancellation;
2. filter, feed, and row component boundaries exist;
3. visible labels, selected/read state, 44-pixel targets, concise live regions, and focus fallback are present;
4. desktop and mobile layouts avoid unsafe dynamic Tailwind classes and document overflow;
5. all semantic copy has matching English, Lithuanian, and Russian shapes.

Run the focused frontend tests and confirm they fail because the new helper/components are not present.

## Task 4: Build The Structured Signal Stream

### Files

- Create `resources/js/components/notification/notification-inbox.ts`.
- Create `resources/js/components/notification/NotificationFilters.vue`.
- Create `resources/js/components/notification/NotificationFeed.vue`.
- Create `resources/js/components/notification/NotificationRow.vue`.
- Refactor `resources/js/pages/notifications/Index.vue` into the page coordinator.
- Modify `lang/en/ui.php`, `lang/lt/ui.php`, and `lang/ru/ui.php`.

### Implementation

1. Add typed notification, paginator, filter, statistic, group, and semantic-presentation contracts plus pure query/grouping helpers.
2. Build a compact responsive control bar for status, kind, page size, active state, clear action, and concise localized result status.
3. Build Today and Earlier feed sections with ordered headings, loading and filter-specific empty states, deterministic pagination controls, and no feed-wide live region.
4. Build a semantic row with structured icon/tone, explicit Unread/Read text, safe content, optional action link, and row-scoped mark-read state.
5. Coordinate URL-backed visits through Wayfinder, `router.cancelAll()`, `preserveState`, and narrow `only` props.
6. On successful mark-read in the Unread view, remove the row locally, reconcile visible counts, and restore focus to the next connected row or stable feed heading.
7. Keep actionable navigation server-owned, mark read before navigation, and retain the feed on mutation failure.
8. Add complete semantic English, Lithuanian, and Russian messages with locale-aware plural forms and English fallback.
9. Preserve semantic tokens, Instrument Sans, Lucide icons, light/dark modes, visible focus, reduced motion, translated wrapping, and zero mobile horizontal overflow.

Run focused frontend and Pest coverage until green, followed by Vue type checking, ESLint, Prettier verification, and the production build.

## Task 5: Focused Review And Verification

1. Run `vendor/bin/pint --dirty --format agent` for touched PHP files.
2. Run focused Pest coverage for the inbox, mutations, user isolation, translations/design, and query budgets.
3. Run Larastan for the touched backend and then the repository command.
4. Run `npm run test:frontend`, `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run build`.
5. Review the diff for user-scope leakage, unsafe action URLs, hidden queries, unbounded collections, deterministic ordering, immutable props, translation fragments, focus restoration, touch targets, and unrelated changes.
6. Apply the repository's code-review skill, resolve all critical and important findings, and rerun affected gates.

## Task 6: Live Browser Verification

Use Laravel Boost to resolve the notification URL and inspect recent browser logs. Use the persistent browser to verify:

- 1,440-pixel desktop light and dark modes;
- 390-by-844 mobile light and dark modes;
- status, kind, and page-size filters update the URL and survive reload;
- clear filters returns to the canonical notification route;
- mark-read removal preserves a useful focus target in the Unread view;
- mark-all, empty results, page navigation, and actionable rows retain correct pending states;
- keyboard order, labels, selected state, read state, and concise announcements are correct;
- no document overflow, page errors, console errors, failed responses, or fresh Boost browser errors.

## Task 7: Full Verification And Delivery

1. Run the complete Pest suite, Pint, Larastan/PHPStan, frontend unit tests, Vue type checking, ESLint, Prettier, production build, Composer/npm audits, and `git diff --check`.
2. Append the Notification Command Center preflight, implementation, checks, limitations, and delivery status to `docs/progress.md`.
3. Stage only notification-phase files and inspect the cached diff.
4. Commit implementation as `feat: build notification command center` and push `origin main`.
5. Commit the progress record as `docs: record notification command center` and push `origin main`.
6. Report exact command outcomes, commit hashes, push status, and any externally blocked verification.
