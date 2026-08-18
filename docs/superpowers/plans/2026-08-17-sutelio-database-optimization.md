# Sutelio Database Optimization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Improve Sutelio's SQLite integrity, read plans, write amplification, bounded data access, and operational observability without changing workspace isolation, adding another database, or touching the real local database during verification.

**Architecture:** Keep SQLite as the only relational store and preserve the existing route -> authorized request -> action/query object -> Eloquent -> Inertia/API flow. Apply only measured, populated-safe schema changes; prove every index against a real query shape; keep request reads bounded and projected; and use isolated file-backed databases for migration, seed, rollback, and query-plan verification.

**Tech Stack:** Laravel 13.25, PHP 8.5, Eloquent, SQLite 3.45, Pest 5, Laravel Boost, Larastan, Pint.

---

## Audit Snapshot

Observed on 2026-08-17 through Laravel Boost and the configured Laravel connection:

- 30 application/framework tables plus `migrations`, 38 source migrations after this phase generated its additive migration, 36 applied local migrations, 94 explicit pre-change indexes plus 26 automatic indexes, 33 foreign-key constraints covering 35 child columns, and 8 integrity triggers.
- The local file is 974,848 bytes: 238 pages of 4,096 bytes with zero freelist pages. `app:database-health --json` passes path, FK, WAL, synchronous, timeout, cache, temp-store, checkpoint, quick-check, and FK-check gates.
- The local database is intentionally not a load-test fixture. Its domain tables are empty while browser work is concurrently creating disposable users/sessions. Snapshot counts must not be treated as production cardinality, and the real file must not be migrated or refreshed by this phase.
- Both current owner lookup shapes lack supporting indexes: `workspaces.owner_id` and `workspace_invitations.invited_by` produce full table scans. They are also child columns for `users` foreign keys, so user deletion/update handling otherwise scans the child tables.
- Ten non-unique indexes are strict left-prefix duplicates of a longer index. They are candidates only, not approved removals: activity workspace, checklist parent, checklist-item parent, label workspace, tag workspace, notification notifiable, project workspace, task project, task workspace, and task workspace/archive.
- `sqlite_stat1` is absent. This is not automatically a defect; planner-statistics maintenance needs a measured plan regression before application automation is justified.
- Six of 13 cache rows were expired at the snapshot. Session rows were recent. Maintenance work needs retention/ownership evidence before scheduling deletion.
- Focused baseline is green: `DatabaseSchemaIntegrityTest`, `PageQueryBudgetTest`, and `SqliteRuntimeHealthTest` pass 37 tests / 219 assertions.
- Current request paths contain raw query expressions in activity, dashboard, notification, onboarding, project, task, and workspace-management reads. This conflicts with the supplied no-raw-query contract and must be removed incrementally with before/after query-count and plan evidence rather than through a broad rewrite.
- All inspected application-owned rows satisfy current preference enums, user/preference ownership, session ownership, workspace ownership/membership, task-parent, taxonomy, recurrence, reminder, and FK integrity checks.

## Decision

Use a schema-first sequence.

1. Add the two proven missing relation indexes now.
2. Make projection/bounded-read corrections in endpoint-sized slices.
3. Remove raw request-query expressions one subsystem at a time, using schema changes only where a first-class stored field is the correct domain model.
4. Benchmark strict-prefix indexes before dropping any of them.
5. Add maintenance automation only after retention and planner evidence exists.

Rejected as the first move:

- Query rewrite first: too many independent read contracts and a larger regression surface than the proven schema gap.
- Drop duplicate-looking indexes first: strict prefix is evidence of possible redundancy, not evidence that the shorter index has no useful cost/selectivity advantage.
- Add cache/FTS/Redis: the local data does not prove a bottleneck, Redis violates the architecture, and SQLite FTS would require a separately approved search/storage contract.

## File Map

- `database/migrations/*_add_user_relation_indexes.php`: additive/reversible owner and inviter indexes only.
- `tests/Feature/DatabaseSchemaIntegrityTest.php`: schema and populated rollback/forward regression coverage.
- `docs/database-audit-2026-08-17.md`: complete non-sensitive live schema/content, integrity, index, and priority report.
- `tests/Feature/PageQueryBudgetTest.php`: endpoint query-count and generated-query-plan budgets.
- `app/Queries/*`, `app/Services/DashboardQuery.php`: one bounded read contract per later slice.
- `app/Models/*`: relationship and reusable Eloquent scope ownership only; no presentation logic.
- `docs/data-model.md`, `docs/sqlite.md`, `docs/performance.md`: current schema, runtime safety, and measured performance evidence.
- `docs/implementation-plan.md`, `docs/compliance-matrix.md`, `docs/progress.md`: active execution, traceability, and append-only observed results.

### Task 1: Add Missing User-Relation Index Coverage

**Files:**

- Create: `database/migrations/2026_08_17_164327_add_user_relation_indexes.php`
- Modify: `tests/Feature/DatabaseSchemaIntegrityTest.php`
- Modify: `docs/data-model.md`
- Modify: `docs/performance.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/progress.md`

- [x] **Step 1: Add the failing schema test**

Use Laravel 13 schema inspection rather than adding new raw SQL:

```php
use Illuminate\Support\Facades\Schema;

test('user-owned foreign keys have supporting indexes', function () {
    $workspaceIndexes = collect(Schema::getIndexes('workspaces'))
        ->mapWithKeys(fn (array $index): array => [$index['name'] => $index['columns']]);
    $invitationIndexes = collect(Schema::getIndexes('workspace_invitations'))
        ->mapWithKeys(fn (array $index): array => [$index['name'] => $index['columns']]);

    expect($workspaceIndexes->get('workspaces_owner_id_index'))->toBe(['owner_id'])
        ->and($invitationIndexes->get('workspace_invitations_invited_by_index'))->toBe(['invited_by']);
});
```

- [x] **Step 2: Run RED**

Run:

```bash
php artisan test --compact tests/Feature/DatabaseSchemaIntegrityTest.php --filter='user-owned foreign keys'
```

Expected: failure because both named indexes are absent.

- [x] **Step 3: Generate and implement the additive migration**

Run:

```bash
php artisan make:migration add_user_relation_indexes --no-interaction
```

Use this implementation:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table): void {
            $table->index('owner_id');
        });

        Schema::table('workspace_invitations', function (Blueprint $table): void {
            $table->index('invited_by');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_invitations', function (Blueprint $table): void {
            $table->dropIndex(['invited_by']);
        });

        Schema::table('workspaces', function (Blueprint $table): void {
            $table->dropIndex(['owner_id']);
        });
    }
};
```

- [x] **Step 4: Run GREEN and the complete focused data gate**

Run:

```bash
php artisan test --compact tests/Feature/DatabaseSchemaIntegrityTest.php
php artisan test --compact tests/Feature/PageQueryBudgetTest.php tests/Feature/SqliteRuntimeHealthTest.php
```

Expected: all tests pass; no query-count budget increases.

- [x] **Step 5: Verify fresh, rollback, and forward migration on an isolated file**

Create the path with `mktemp`, run `migrate --force`, roll back only the new migration batch, migrate forward again, and run `app:database-health --json`. Never point these commands at `database/database.sqlite`.

- [x] **Step 6: Measure the exact plans**

On the isolated migrated database, verify owner and inviter lookup queries use `workspaces_owner_id_index` and `workspace_invitations_invited_by_index`. Record the before state (`SCAN`) and after state (`SEARCH ... USING INDEX`) in `docs/performance.md` and `docs/progress.md`.

- [x] **Step 7: Format, statically analyze, and inspect**

Run:

```bash
vendor/bin/pint --dirty --format agent
composer run types:check
git diff --check
```

Inspect the complete diff and an attributable temporary-index staged diff before committing.

- [x] **Step 8: Commit and push**

Completed and pushed as commit `4357ff5` (`perf(database): index user-owned relations`). The original delivery commands were:

```bash
git commit -m "perf(database): index user-owned relations"
git push origin main
```

Verify local `HEAD`, `origin/main`, and `git ls-remote origin refs/heads/main` agree.

### Task 2: Enforce Explicit Projections And Bounded Collections

**Files:**

- Modify: `app/Queries/ProjectIndexQuery.php`
- Modify: `app/Queries/TodoIndexQuery.php`
- Modify: `app/Queries/TodoDetailQuery.php`
- Modify: `app/Queries/WorkspaceManagementQuery.php`
- Modify: relevant controllers/resources only when their documented prop contract requires a field
- Test: `tests/Feature/PageQueryArchitectureTest.php`
- Test: `tests/Feature/PageQueryBudgetTest.php`
- Test: endpoint feature tests for every changed query object

- [ ] Add failing source/response regressions proving every primary collection selects its exact scalar/foreign-key columns and every potentially unbounded collection paginates or has a documented domain limit.
- [ ] Add projections to primary models and constrained eager loads, always retaining relationship keys.
- [ ] Replace unbounded project/member/invitation option reads with existing paginator contracts or explicit domain limits; do not silently truncate a user-facing management page.
- [ ] Re-run endpoint response assertions, strict-Eloquent tests, and page query budgets after each query object.
- [ ] Record Inertia payload bytes for one empty and one production-shaped response before and after each slice.

Acceptance: no `SELECT *` in the changed request path, no missing-attribute exception, stable behavior, bounded rows, and no higher endpoint query count.

Progress on 2026-08-18: the first endpoint-sized ProjectIndex projection slice is GREEN. It replaces `projects.*` with the eight page-consumed scalar/foreign-key columns, preserves `todos_count`, keeps strict resource serialization and full-model API fields green, holds the page at five queries, and reduces the deterministic 24-project prop from 9,394 to 8,026 bytes. The Task 2 checkboxes remain open until bounded project/member/invitation reads and the Todo/TodoDetail/WorkspaceManagement projection slices are complete.

### Task 3: Remove Raw Activity Query Hints

**Files:**

- Modify: `app/Queries/ActivityIndexQuery.php`
- Modify: `tests/Feature/ActivityPageTest.php`
- Modify: `tests/Feature/PageQueryBudgetTest.php`
- Modify: `docs/performance.md`

- [ ] Add production-shaped actor/event/period plan regressions without the `INDEXED BY` clause.
- [ ] Remove `fromRaw()` and the two index-name constants.
- [ ] If SQLite chooses the wrong plan, adjust one evidence-backed composite index in a new migration; do not restore a query hint.
- [ ] Replace conditional metric raw expressions with framework-generated count queries or model count subqueries only after comparing query count and elapsed time on the same fixture.

Acceptance: no raw expression in `ActivityIndexQuery`, both filters use workspace-leading indexes, results and metrics match, and page budget remains bounded.

### Task 4: Normalize Notification Kind Into First-Class Data

**Files:**

- Create: a populated-safe migration adding `notifications.kind`
- Modify: notification writers/seeders to persist the semantic kind
- Modify: `app/Queries/NotificationIndexQuery.php`
- Modify: `app/Http/Resources/NotificationInboxResource.php` only if its existing output contract needs the stored value
- Test: `tests/Feature/NotificationInboxTest.php`
- Test: `tests/Feature/PageQueryBudgetTest.php`

- [ ] Add failing tests for reminder/update filtering across current, legacy-valid, malformed, and null payloads.
- [ ] Add nullable `kind`, backfill in bounded Eloquent chunks from validated decoded payloads, verify values, then index `(notifiable_type, notifiable_id, kind, created_at, id)`.
- [ ] Keep safe fallback presentation for malformed legacy payloads, but remove JSON SQL expressions from request filtering.
- [ ] Prove the current 20/50-row pagination, totals, stats-only reload, and authorized-link batch query contracts.

Acceptance: no `whereRaw()` JSON predicate, indexed semantic filtering, malformed legacy safety preserved, and no cross-user link/access regression.

### Task 5: Remove Remaining Raw Ordering And Aggregates

**Files:**

- Modify one subsystem per commit: `DashboardQuery`, `ProjectDetailQuery`, `TodoIndexQuery`, `OnboardingQuery`, then `WorkspaceManagementQuery`
- Modify matching focused feature/query-budget tests

- [ ] For aggregates, compare framework-generated conditional relationship counts against separate bound count queries on the same fixture. Select the version with the best bounded query/time result that contains no application-authored SQL expression.
- [ ] For selected-first onboarding options, load the selected authorized row explicitly and merge it with a bounded Eloquent list excluding that ID; preserve the 100-row cap and selected inclusion.
- [ ] For role ordering, use a stored enum sort position or a bounded in-memory sort only after the member list itself is paginated/bounded; do not embed a `CASE` string.
- [ ] For due-date null ordering, split null/non-null pages only if pagination metadata remains exact; otherwise introduce a documented stored sort key through a populated-safe migration.

Acceptance: each changed file contains no `selectRaw`, `whereRaw`, `orderByRaw`, `fromRaw`, `DB::raw`, `DB::select`, or `DB::statement`; response/query budgets and deterministic pagination remain green.

### Task 6: Benchmark And Consolidate Strict-Prefix Indexes

**Files:**

- Create: one migration only after the benchmark names approved drops
- Modify: `tests/Feature/PageQueryBudgetTest.php`
- Modify: `tests/Feature/DatabaseSchemaIntegrityTest.php`
- Modify: `docs/performance.md`

- [ ] Seed isolated fixtures at 1k, 10k, and 100k task/activity/notification rows with realistic workspace skew.
- [ ] Capture read plans/timings and a bulk insert/update/delete timing with all current indexes.
- [ ] Drop each candidate in a temporary isolated copy, repeat the identical measurements, and retain the drop only when reads stay within budget and writes/storage materially improve.
- [ ] Never drop a reverse pivot index, an FK-supporting index, or an index named by a current query-plan contract without replacing that contract first.

Acceptance: every removed index has before/after numbers and rollback coverage; candidates without measurable benefit remain unchanged.

### Task 7: Add Data-Growth And Maintenance Observability

**Files:**

- Modify: `app/Services/SqliteHealthService.php`
- Modify: the existing database-health command output contract
- Modify: `tests/Feature/SqliteRuntimeHealthTest.php`
- Modify: `docs/operations.md`

- [ ] Add non-sensitive metrics for database bytes, page count, freelist count, WAL bytes when present, pending migrations, and per-domain row-count bands without exposing the physical path publicly.
- [ ] Define explicit warning thresholds in configuration, not environment reads in runtime code.
- [ ] Add retention decisions before deleting expired cache/session/activity rows; reuse framework commands where they exist.
- [ ] Add planner-statistics or vacuum automation only when freelist/plan evidence crosses a documented threshold, and never run destructive maintenance inside an HTTP request.

Acceptance: health output is bounded/non-sensitive, tests cover threshold states, and no maintenance action runs without an operator-visible policy.

### Task 8: Final Data Gate And Delivery

**Files:**

- Synchronize: `docs/data-model.md`, `docs/sqlite.md`, `docs/performance.md`, `docs/operations.md`, `docs/requirements.md`, `docs/compliance-matrix.md`, `docs/implementation-plan.md`, `docs/progress.md`

- [ ] Run Pint, Larastan, full sequential Pest, isolated fresh migration, seed twice, rollback/forward for new migrations, SQLite health/FK/integrity, frontend tests/types/lint/format/build when response props changed, Composer/npm audits, and relevant isolated-browser checks when UI behavior changed.
- [ ] Compare query counts, plans, payload bytes, database/index bytes, writes/second, and migration/seed duration against the recorded baseline.
- [ ] Inspect complete and staged diffs, secrets, generated files, pending migrations, and preservation of unrelated UI work.
- [ ] Commit phase-owned files semantically and push `origin/main` without force or history rewrite.

Completion is prohibited while a new migration lacks isolated populated rollback/forward evidence, a changed page lacks a current query budget, or canonical docs claim an unexecuted measurement.
