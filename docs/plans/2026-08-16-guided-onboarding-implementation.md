# Guided Onboarding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the approved resumable eight-step onboarding for newly verified users, optional replay for existing users, preference-aware week starts, a post-onboarding checklist, and a freshly verified NativePHP Android APK.

**Architecture:** Extend the existing `user_preferences` aggregate with versioned onboarding progress, protect normal verified web routes with a narrowly scoped middleware gate, and coordinate existing preference/workspace/project/task actions through focused onboarding requests/actions. Render one dedicated typed Inertia page composed from small Warm Precision Vue components; every persisted workspace-owned identifier is re-resolved through the authenticated user's workspace relation.

**Tech Stack:** Laravel 13.25, PHP 8.4-compatible syntax, Inertia Laravel 3.3 / Inertia Vue 3.6, Vue 3.5 Composition API, TypeScript 6, Tailwind CSS 4.3, Reka/shadcn-style primitives, Wayfinder 0.1, Pest 5, Larastan 3, SQLite, NativePHP Mobile 4.2.

---

## File Responsibility Map

### Persistence And Domain State

- Create `database/migrations/2026_08_16_230000_add_guided_onboarding_to_user_preferences.php`: add preference/progress fields, backfill existing rows as complete/dismissed, and create the bounded idempotency ledger.
- Create `app/Enums/OnboardingStep.php`: stable eight-step order, positions, adjacency, and completion percentage.
- Modify `app/Models/UserPreference.php`: casts, allowed week starts, onboarding defaults, gate predicates, and safe state helpers.
- Modify `database/factories/UserPreferenceFactory.php`: complete-by-default existing-user state plus an explicit `pendingOnboarding()` factory state.
- Modify `app/Actions/Fortify/CreateNewUser.php`: make actual Fortify registrations pending while preserving existing/factory users.

### Entry, Reads, And Mutations

- Create `app/Http/Middleware/EnsureOnboardingIsComplete.php`: redirect only verified browser users with an explicitly pending preference row.
- Create `app/Queries/OnboardingQuery.php`: bounded and workspace-scoped page options plus permission-aware results.
- Create `app/Queries/OnboardingChecklistQuery.php`: derive the optional Dashboard follow-up without fake completion claims.
- Create `app/Actions/RunOnboardingCreation.php`: exactly-once workspace/project/task creation per onboarding run and step.
- Create `app/Actions/AdvanceOnboarding.php`, `SaveOnboardingPreferences.php`, `ChooseOnboardingWorkspace.php`, `ChooseOnboardingProject.php`, `ChooseOnboardingTask.php`, `CompleteOnboarding.php`, `SkipOnboarding.php`, `RestartOnboarding.php`, and `DismissOnboardingChecklist.php`: one focused state change per action.
- Create `app/Http/Requests/AdvanceOnboardingRequest.php`, `UpdateOnboardingPreferencesRequest.php`, `StoreOnboardingWorkspaceRequest.php`, `StoreOnboardingProjectRequest.php`, and `StoreOnboardingTaskRequest.php`: authorized, step-aware, scoped input contracts.
- Create `app/Http/Controllers/OnboardingController.php`: thin Inertia/redirect coordinator.
- Modify `bootstrap/app.php`, `routes/web.php`, and `routes/settings.php`: register the middleware alias and named onboarding routes without affecting JSON/API behavior.

### Preferences And Calendar Truth

- Modify `app/Http/Requests/UpdateUserPreferenceRequest.php`, `app/Http/Controllers/Settings/PreferencesController.php`, and `app/Actions/UpdateUserPreferences.php`: persist `week_start` through the canonical settings path.
- Modify `app/Http/Requests/CalendarIndexRequest.php`, `app/Http/Controllers/CalendarController.php`, `resources/js/components/calendar/calendar-types.ts`, and `resources/js/components/calendar/CalendarMonthGrid.vue`: make Sunday/Monday week boundaries and weekday order use the saved preference.

### Inertia And Warm Precision UI

- Create `resources/js/pages/onboarding/Index.vue`: immutable page coordinator, active-step rendering, request lifecycle, and focus handoff.
- Create `resources/js/components/onboarding/onboarding-types.ts`: typed contracts and pure step/progress/draft helpers.
- Create `resources/js/components/onboarding/OnboardingShell.vue` and `OnboardingStepPanel.vue`: route-map progress rail, mobile status/action frame, save status, and error summary boundary.
- Create `resources/js/components/onboarding/WelcomeStep.vue`, `PreferencesStep.vue`, `WorkspaceStep.vue`, `ProjectStep.vue`, `TaskStep.vue`, `ProductMapStep.vue`, `SafetyStep.vue`, and `ResultsStep.vue`: one semantic responsibility per approved step.
- Create `resources/js/components/onboarding/OnboardingChecklist.vue`: dismissible Dashboard continuation card.
- Modify `resources/js/pages/Dashboard.vue`, `resources/js/pages/settings/Preferences.vue`, `resources/js/types/models.ts`, and `resources/js/types/global.d.ts`: render the checklist/restart control and type the expanded preference contract.
- Create `lang/en/onboarding.php`, `lang/lt/onboarding.php`, and `lang/ru/onboarding.php`; modify `lang/en/ui.php`, `lang/lt/ui.php`, and `lang/ru/ui.php`: complete semantic page/checklist/settings copy with placeholder parity.

### Tests And Documentation

- Create `tests/Feature/OnboardingPersistenceTest.php`, `OnboardingEntryTest.php`, `OnboardingWorkflowTest.php`, and `OnboardingFrontendTest.php`.
- Create `resources/js/components/OnboardingJourney.test.ts` at the depth discovered by `npm run test:frontend`.
- Modify `tests/Feature/Auth/RegistrationTest.php`, `tests/Feature/Auth/EmailVerificationTest.php`, `tests/Feature/Settings/PreferencesTest.php`, `tests/Feature/WorkspacePagesTest.php`, `tests/Feature/PageQueryBudgetTest.php`, `tests/Feature/FrontendLocalizationTest.php`, `tests/Feature/FrontendDesignTest.php`, and `tests/Feature/NativePhpMobileTest.php` only for concrete onboarding/week-start contracts.
- Modify `docs/requirements.md`, `docs/non-functional-requirements.md`, `docs/architecture.md`, `docs/domain-model.md`, `docs/data-model.md`, `docs/frontend.md`, `docs/design-system.md`, `docs/accessibility.md`, `docs/localization.md`, `docs/testing.md`, `docs/deployment.md`, `docs/compliance-matrix.md`, `docs/implementation-plan.md`, `CHANGELOG.md`, and `docs/progress.md` after behavior is verified.

## Task 1: Persist Versioned Onboarding And Week-Start State

**Files:**

- Create: `tests/Feature/OnboardingPersistenceTest.php`
- Create: `app/Enums/OnboardingStep.php`
- Create: `database/migrations/2026_08_16_230000_add_guided_onboarding_to_user_preferences.php`
- Modify: `app/Models/UserPreference.php`
- Modify: `database/factories/UserPreferenceFactory.php`
- Modify: `app/Actions/Fortify/CreateNewUser.php`
- Modify: `tests/Feature/Auth/RegistrationTest.php`

- [ ] **Step 1: Create the focused Pest file and write the RED persistence contract**

Run `php artisan make:test --pest OnboardingPersistenceTest --no-interaction`, then add tests with these assertions:

```php
use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;

test('ordinary preference factories represent existing completed users', function () {
    $preferences = UserPreference::factory()->create();

    expect($preferences->requiresOnboarding())->toBeFalse()
        ->and($preferences->onboarding_step)->toBe(OnboardingStep::Results)
        ->and($preferences->onboarding_checklist_dismissed_at)->not->toBeNull();
});

test('pending onboarding state is explicit and resumable', function () {
    $preferences = UserPreference::factory()->pendingOnboarding()->create();

    expect($preferences->requiresOnboarding())->toBeTrue()
        ->and($preferences->onboarding_step)->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_state)->toBe([])
        ->and($preferences->onboarding_run_id)->toBeUuid();
});

test('fortify registration creates a pending onboarding preference row', function () {
    $this->post(route('register.store'), [
        'name' => 'First Run User',
        'email' => 'first-run@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $preferences = User::query()->where('email', 'first-run@example.com')->firstOrFail()->preferences;

    expect($preferences?->requiresOnboarding())->toBeTrue()
        ->and($preferences?->week_start)->toBe('sunday');
});
```

- [ ] **Step 2: Run the focused test and confirm the intended RED state**

Run: `php artisan test --compact tests/Feature/OnboardingPersistenceTest.php tests/Feature/Auth/RegistrationTest.php`

Expected: failures for the missing enum, columns, `pendingOnboarding()` state, and `requiresOnboarding()` method; no unrelated failure.

- [ ] **Step 3: Create the migration with Artisan and lock its exact path**

Run:

```bash
php artisan make:migration add_guided_onboarding_to_user_preferences --table=user_preferences --no-interaction
mv database/migrations/*_add_guided_onboarding_to_user_preferences.php database/migrations/2026_08_16_230000_add_guided_onboarding_to_user_preferences.php
```

Implement the migration with this schema and populated-data behavior:

```php
Schema::table('user_preferences', function (Blueprint $table): void {
    $table->string('week_start')->default('sunday');
    $table->unsignedInteger('onboarding_version')->default(1);
    $table->string('onboarding_step')->default('welcome');
    $table->uuid('onboarding_run_id')->nullable();
    $table->json('onboarding_state')->nullable();
    $table->timestamp('onboarding_started_at')->nullable();
    $table->timestamp('onboarding_completed_at')->nullable();
    $table->timestamp('onboarding_skipped_at')->nullable();
    $table->timestamp('onboarding_checklist_dismissed_at')->nullable();
});

$now = now();
DB::table('user_preferences')->orderBy('id')->get(['id'])->each(
    fn (object $row) => DB::table('user_preferences')->where('id', $row->id)->update([
        'onboarding_step' => 'results',
        'onboarding_run_id' => (string) Str::uuid(),
        'onboarding_state' => json_encode([], JSON_THROW_ON_ERROR),
        'onboarding_started_at' => $now,
        'onboarding_completed_at' => $now,
        'onboarding_checklist_dismissed_at' => $now,
    ]),
);

Schema::create('onboarding_operations', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('version');
    $table->uuid('run_id');
    $table->string('step');
    $table->uuid('request_key');
    $table->uuid('result_id')->nullable();
    $table->timestamps();
    $table->unique(['user_id', 'version', 'run_id', 'step'], 'onboarding_operations_run_step_unique');
});
```

`down()` must drop `onboarding_operations` first, then drop the nine added preference columns in one `Schema::table()` call. Run it only against isolated test databases.

- [ ] **Step 4: Implement the stable enum and model contract**

Create `OnboardingStep` with these exact cases and helpers:

```php
enum OnboardingStep: string
{
    case Welcome = 'welcome';
    case Preferences = 'preferences';
    case Workspace = 'workspace';
    case Project = 'project';
    case Task = 'task';
    case ProductMap = 'product_map';
    case Safety = 'safety';
    case Results = 'results';

    /** @return list<self> */
    public static function ordered(): array
    {
        return [self::Welcome, self::Preferences, self::Workspace, self::Project, self::Task, self::ProductMap, self::Safety, self::Results];
    }

    public function position(): int
    {
        return array_search($this, self::ordered(), true) + 1;
    }

    public function percent(): int
    {
        return (int) round(($this->position() / count(self::ordered())) * 100);
    }
}
```

In `UserPreference`, add `ONBOARDING_VERSION = 1`, `WEEK_STARTS = ['sunday', 'monday']`, the new fillable fields/casts, `week_start => sunday` to `defaults()`, `pendingOnboardingDefaults()`, and:

```php
public function requiresOnboarding(): bool
{
    return $this->onboarding_completed_at === null && $this->onboarding_skipped_at === null;
}

public function onboardingStep(): OnboardingStep
{
    return OnboardingStep::tryFrom((string) $this->onboarding_step) ?? OnboardingStep::Welcome;
}
```

The factory default merges pending defaults with `Results`, `now()` completion, and `now()` checklist dismissal. Its `pendingOnboarding()` state returns `UserPreference::pendingOnboardingDefaults()`.

- [ ] **Step 5: Make only real registrations pending**

Change `CreateNewUser` to create:

```php
$user->preferences()->create([
    ...UserPreference::defaults(),
    ...UserPreference::pendingOnboardingDefaults(),
]);
```

Update the existing registration assertion to include `onboarding_step = welcome`, null completion/skip, and a non-null run ID without changing the existing Fortify redirect contract.

- [ ] **Step 6: Run migration/model/registration gates and format PHP**

Run:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate --force
php artisan test --compact tests/Feature/OnboardingPersistenceTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/Settings/PreferencesTest.php tests/Feature/SchemaIntegrityTest.php
vendor/bin/pint --dirty --format agent
composer run types:check -- --memory-limit=1G
```

Expected: all selected tests pass; Larastan reports zero errors; the migration creates both the added columns and the operation ledger on SQLite.

- [ ] **Step 7: Record, inspect, commit, and push the persistence slice**

Update the active onboarding entry in `docs/progress.md`, stage only Task 1 files, run `git diff --cached --check`, inspect `git diff --cached`, then:

```bash
git commit -m "feat: persist guided onboarding progress"
git push --verbose origin main
```

## Task 2: Enforce The Verified Browser Entry Gate

**Files:**

- Create: `tests/Feature/OnboardingEntryTest.php`
- Create: `app/Http/Middleware/EnsureOnboardingIsComplete.php`
- Create: `app/Http/Controllers/OnboardingController.php`
- Create: `app/Queries/OnboardingQuery.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Modify: `routes/settings.php`
- Modify: `tests/Feature/Auth/EmailVerificationTest.php`

- [ ] **Step 1: Write RED redirect and exception coverage**

Create tests proving:

```php
test('verified pending users are redirected from application pages to onboarding', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirectToRoute('onboarding.index');
});

test('unverified pending users still reach the verification notice', function () {
    $user = User::factory()->unverified()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirectToRoute('verification.notice');
});

test('existing complete users and legacy users without preferences are not redirected', function () {
    $complete = User::factory()->create();
    UserPreference::factory()->for($complete)->create();
    $legacy = User::factory()->create();

    $this->actingAs($complete)->get(route('dashboard'))->assertOk();
    $this->actingAs($legacy)->get(route('dashboard'))->assertOk();
});
```

Also prove `GET /onboarding` requires auth and verification, pending users can reach it, completed users without an active replay session return to their start page, auth recovery routes remain reachable, and `/api/v1/user` keeps its existing JSON behavior.

- [ ] **Step 2: Run the entry test and confirm only missing onboarding behavior fails**

Run: `php artisan test --compact tests/Feature/OnboardingEntryTest.php tests/Feature/Auth/EmailVerificationTest.php`

Expected: route/middleware/component failures for the new behavior; existing verification assertions remain stable until explicitly adjusted.

- [ ] **Step 3: Implement the narrow middleware**

Use this predicate in `EnsureOnboardingIsComplete`:

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if (! $user instanceof User || ! $user->hasVerifiedEmail()) {
        return $next($request);
    }

    $preferences = $user->preferences()->first();

    if ($preferences instanceof UserPreference && $preferences->requiresOnboarding()) {
        return to_route('onboarding.index');
    }

    return $next($request);
}
```

Register alias `onboarding.complete` in `bootstrap/app.php`. Put onboarding GET/mutation routes inside `auth,verified` but outside the `onboarding.complete` route group. Apply the alias to normal verified web routes and both settings groups. Do not apply it to API, login, verification, logout, password reset, or the onboarding endpoints.

- [ ] **Step 4: Add the initial route/controller/query page boundary**

Add named routes for `onboarding.index`, `progress`, `preferences`, `workspace`, `project`, `task`, `skip`, `complete`, `restart`, and `checklist.dismiss`. The first GREEN pass needs `index()` to return `Inertia::render('onboarding/Index', ...)` with:

```php
[
    'progress' => [
        'step' => $preferences->onboardingStep()->value,
        'position' => $preferences->onboardingStep()->position(),
        'total' => count(OnboardingStep::ordered()),
        'percent' => $preferences->onboardingStep()->percent(),
        'is_replay' => $request->session()->boolean('onboarding_replay'),
    ],
    'state' => $preferences->onboarding_state ?? [],
    'copy' => __('onboarding'),
]
```

Create a minimal single-root `resources/js/pages/onboarding/Index.vue` using `AppLayout`, `Head`, one `h1`, and the current localized title so the backend test resolves a real page component; Task 7 replaces the minimal body with the approved composition.

- [ ] **Step 5: Run entry, authentication, route, and architecture tests**

Run:

```bash
php artisan test --compact tests/Feature/OnboardingEntryTest.php tests/Feature/Auth/EmailVerificationTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/ArchitectureContractTest.php
php artisan route:list --path=onboarding --except-vendor
vendor/bin/pint --dirty --format agent
```

Expected: pending verified requests redirect exactly once, onboarding renders, completed/legacy/API/auth-recovery cases do not loop, and route names/methods match the contract.

- [ ] **Step 6: Commit and push the entry slice**

Update `docs/progress.md`, inspect only Task 2 files, then commit and push:

```bash
git commit -m "feat: gate first-run onboarding"
git push --verbose origin main
```

## Task 3: Implement Resumable Progress, Skip, Completion, And Replay

**Files:**

- Create: `tests/Feature/OnboardingWorkflowTest.php`
- Create: `app/Http/Requests/AdvanceOnboardingRequest.php`
- Create: `app/Actions/AdvanceOnboarding.php`
- Create: `app/Actions/CompleteOnboarding.php`
- Create: `app/Actions/SkipOnboarding.php`
- Create: `app/Actions/RestartOnboarding.php`
- Modify: `app/Http/Controllers/OnboardingController.php`
- Modify: `app/Queries/OnboardingQuery.php`

- [ ] **Step 1: Write RED lifecycle tests**

Cover legal forward/back adjacency, invalid jumps, reload resume, whole-journey skip, results completion, start-page redirect, manual replay, replay abandonment, and content-version behavior. The central assertions are:

```php
$this->actingAs($user)
    ->patch(route('onboarding.progress'), ['target_step' => 'preferences'])
    ->assertRedirectToRoute('onboarding.index');

expect($preferences->fresh()->onboarding_step)->toBe('preferences')
    ->and($preferences->fresh()->onboarding_started_at)->not->toBeNull();

$this->post(route('onboarding.restart'))
    ->assertRedirectToRoute('onboarding.index');

expect(session('onboarding_replay'))->toBeTrue()
    ->and($preferences->fresh()->onboarding_completed_at)->not->toBeNull()
    ->and($preferences->fresh()->onboarding_step)->toBe('welcome');
```

Assert that a replaying completed user may leave for Dashboard, while a genuinely pending user may not.

- [ ] **Step 2: Run the lifecycle test and observe action/request failures**

Run: `php artisan test --compact tests/Feature/OnboardingWorkflowTest.php --filter='progress|skip|complete|replay|version'`

Expected: missing request/action/controller methods; the entry gate tests stay green.

- [ ] **Step 3: Implement step-aware validation and actions**

`AdvanceOnboardingRequest` accepts only `target_step` from `OnboardingStep::ordered()`. `AdvanceOnboarding` compares current and target positions and permits only `+1` or `-1`; illegal jumps throw a localized validation error.

`SkipOnboarding` sets `onboarding_skipped_at`, moves to `Results`, clears resumable state, and forgets the replay session only for required mode. During replay, Skip simply ends the replay session and leaves prior completion/skip facts intact.

`CompleteOnboarding` requires `Results`, sets completion, clears skip, and forgets replay. `RestartOnboarding` creates/updates preferences, generates a new run UUID, resets step/state/start time, sets `onboarding_replay = true`, and deliberately preserves completion/skip/checklist dismissal.

- [ ] **Step 4: Keep controller redirects deterministic**

Every successful progress mutation returns to `onboarding.index`; completion returns to `UserPreference::startRoute(...)`; Skip returns to the saved start page. Invalid requests redirect back with errors and never move the cursor.

- [ ] **Step 5: Run and commit the lifecycle slice**

Run focused lifecycle/entry tests, Pint, scoped Larastan, `git diff --check`, then update progress and commit/push:

```bash
git commit -m "feat: add resumable onboarding lifecycle"
git push --verbose origin main
```

## Task 4: Compose Scoped Preference, Workspace, Project, And Task Actions

**Files:**

- Create: `app/Actions/RunOnboardingCreation.php`
- Create: `app/Actions/SaveOnboardingPreferences.php`
- Create: `app/Actions/ChooseOnboardingWorkspace.php`
- Create: `app/Actions/ChooseOnboardingProject.php`
- Create: `app/Actions/ChooseOnboardingTask.php`
- Create: `app/Http/Requests/UpdateOnboardingPreferencesRequest.php`
- Create: `app/Http/Requests/StoreOnboardingWorkspaceRequest.php`
- Create: `app/Http/Requests/StoreOnboardingProjectRequest.php`
- Create: `app/Http/Requests/StoreOnboardingTaskRequest.php`
- Modify: `app/Http/Controllers/OnboardingController.php`
- Modify: `app/Queries/OnboardingQuery.php`
- Modify: `tests/Feature/OnboardingWorkflowTest.php`
- Modify: `tests/Feature/PageQueryBudgetTest.php`

- [ ] **Step 1: Add RED domain/scoping/idempotency coverage**

Test select-existing and create-new paths for all three aggregates, invited-member workspace reuse, workspace-less creation, active-project/task selection, foreign/mixed UUID rejection, archived project rejection, invalid definition/assignee rejection, stale entity recovery, 100-row option caps plus selected inclusion, and duplicate request behavior.

The idempotency test submits the same `request_key` twice and asserts one row:

```php
$payload = ['mode' => 'create', 'name' => 'Launch', 'request_key' => (string) Str::uuid()];

$this->actingAs($user)->post(route('onboarding.project'), $payload)->assertRedirect();
$this->post(route('onboarding.project'), $payload)->assertRedirect();

expect($workspace->projects()->where('name', 'Launch')->count())->toBe(1)
    ->and(DB::table('onboarding_operations')->where('step', 'project')->count())->toBe(1);
```

- [ ] **Step 2: Run only the new domain cases and verify RED**

Run: `php artisan test --compact tests/Feature/OnboardingWorkflowTest.php tests/Feature/PageQueryBudgetTest.php --filter=onboarding`

Expected: missing scoped request/action/query behavior; existing project/task/workspace actions remain green.

- [ ] **Step 3: Implement exactly-once creation per run/step**

`RunOnboardingCreation::handle()` accepts preferences, step, request key, and a closure returning the created model ID. In one retryable transaction it inserts an operation row keyed by `(user_id, version, run_id, step)`, reuses an already completed `result_id`, executes the closure once, stores the result, and rolls the operation back if the domain action fails. A second request with a different key in the same run still returns the first result; a manual replay receives a new run ID and can create new data.

- [ ] **Step 4: Compose existing actions without duplicating business rules**

Implement these flows:

```php
// Preferences
$updateUserPreferences->execute($user, $request->preferenceData());
$advanceOnboarding->handle($preferences->fresh(), OnboardingStep::Workspace);

// Workspace create
$workspaceId = $runCreation->handle(
    $preferences,
    OnboardingStep::Workspace,
    $request->requestKey(),
    fn (): string => $createWorkspace->handle($request->workspaceData(), $user)->id,
);

// Project create
$projectId = $runCreation->handle(
    $preferences,
    OnboardingStep::Project,
    $request->requestKey(),
    fn (): string => $createProject->handle($workspace, $request->projectData())->id,
);

// Task create
$taskId = $runCreation->handle(
    $preferences,
    OnboardingStep::Task,
    $request->requestKey(),
    fn (): string => $createTodo->handle($workspace, $request->todoData(), $user->id)->id,
);
```

Selection queries must start from `$user->workspaces()`, then `$workspace->projects()->active()`, then `$workspace->todos()->where('is_archived', false)`. Persist only selected IDs and safe drafts in `onboarding_state`; update `current_workspace_id` in session after workspace success.

- [ ] **Step 5: Bound and serialize the page options**

`OnboardingQuery` returns at most 100 accessible workspaces, 100 active projects, 100 unarchived tasks, 100 members, 100 statuses, and 100 priorities, appending the valid selected entity when it is outside the first page. It selects only fields needed by the step and uses eager loads/resources without a query in a loop.

When a saved workspace/project/task is missing or no longer accessible, derive the nearest safe effective step and return a localized `recovery` code. The next mutation persists the normalized state before continuing.

- [ ] **Step 6: Run security, query-budget, and regression gates**

Run:

```bash
php artisan test --compact tests/Feature/OnboardingWorkflowTest.php tests/Feature/PageQueryBudgetTest.php tests/Feature/WorkspaceTest.php tests/Feature/ProjectTest.php tests/Feature/TodoTest.php
vendor/bin/pint --dirty --format agent
composer run types:check -- --memory-limit=1G
```

Expected: one entity per creation step/run, no foreign leakage, bounded query count independent of unrelated volume, and all existing domain flows pass.

- [ ] **Step 7: Commit and push the action-composition slice**

Update progress, inspect the complete/staged diff, then:

```bash
git commit -m "feat: guide first workspace project and task"
git push --verbose origin main
```

## Task 5: Make Week Start A Real Preference

**Files:**

- Modify: `tests/Feature/Settings/PreferencesTest.php`
- Modify: `tests/Feature/WorkspacePagesTest.php`
- Modify: `app/Http/Requests/UpdateUserPreferenceRequest.php`
- Modify: `app/Http/Controllers/Settings/PreferencesController.php`
- Modify: `app/Http/Requests/CalendarIndexRequest.php`
- Modify: `app/Http/Controllers/CalendarController.php`
- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/components/calendar/calendar-types.ts`
- Modify: `resources/js/components/calendar/CalendarMonthGrid.vue`
- Modify: `resources/js/types/models.ts`
- Modify: `lang/en/ui.php`, `lang/lt/ui.php`, `lang/ru/ui.php`

- [ ] **Step 1: Write RED settings and calendar boundary tests**

Persist `week_start = monday`, reject arbitrary values, and prove that the week containing Sunday 2026-08-16 is `2026-08-10..2026-08-16` for Monday-first versus `2026-08-16..2026-08-22` for Sunday-first. Assert the Inertia calendar prop includes `week_start` and the frontend rotates weekday labels rather than mutating the shared translation catalog.

- [ ] **Step 2: Run the two focused files and verify RED**

Run: `php artisan test --compact tests/Feature/Settings/PreferencesTest.php tests/Feature/WorkspacePagesTest.php`

Expected: missing validation/persistence and Monday-first boundary failures.

- [ ] **Step 3: Implement one source of week-boundary truth**

Add `week_start` validation with `Rule::in(UserPreference::WEEK_STARTS)`. Change `CalendarIndexRequest::calendarState()` to accept the saved value and choose:

```php
$firstDay = $weekStart === 'monday' ? CarbonInterface::MONDAY : CarbonInterface::SUNDAY;
$lastDay = $weekStart === 'monday' ? CarbonInterface::SUNDAY : CarbonInterface::SATURDAY;
```

Use those values for week and month range expansion, include `week_start` in the returned calendar state, and rotate the seven localized labels in `CalendarMonthGrid` with a computed array when Monday is selected.

- [ ] **Step 4: Add the canonical settings control and translations**

Add a typed Sunday/Monday select to `Preferences.vue`, use complete keys `settings.preferences.week_start`, `week_starts.sunday`, and `week_starts.monday`, and include the field in `UserPreference` TypeScript and form payloads.

- [ ] **Step 5: Run and commit the preference truth slice**

Run focused PHP tests, frontend type/lint/format checks, Pint, then update progress and commit/push:

```bash
git commit -m "feat: honor saved week starts"
git push --verbose origin main
```

## Task 6: Establish Frontend Behavior And Design Contracts

**Files:**

- Create: `resources/js/components/OnboardingJourney.test.ts`
- Create: `tests/Feature/OnboardingFrontendTest.php`
- Create: `resources/js/components/onboarding/onboarding-types.ts`
- Modify: `tests/Feature/FrontendLocalizationTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Write the pure TypeScript RED contract**

The discovered top-level component test must cover:

```ts
import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import {
    mergeOnboardingDraft,
    onboardingPercent,
    orderedOnboardingSteps,
} from './onboarding/onboarding-types.ts';

describe('guided onboarding state', () => {
    it('keeps the stable eight-step order and honest percentages', () => {
        assert.deepEqual(orderedOnboardingSteps, [
            'welcome',
            'preferences',
            'workspace',
            'project',
            'task',
            'product_map',
            'safety',
            'results',
        ]);
        assert.equal(onboardingPercent('welcome'), 13);
        assert.equal(onboardingPercent('results'), 100);
    });

    it('merges drafts immutably without crossing entity identities', () => {
        const source = { workspace_id: 'one', workspace_name: 'Original' };
        const merged = mergeOnboardingDraft(source, {
            workspace_name: 'Changed',
        });
        assert.deepEqual(source, {
            workspace_id: 'one',
            workspace_name: 'Original',
        });
        assert.deepEqual(merged, {
            workspace_id: 'one',
            workspace_name: 'Changed',
        });
    });
});
```

- [ ] **Step 2: Write RED Pest source/localization contracts**

Assert the page/components use AppLayout, one `h1`, semantic progress, `aria-current="step"`, concise `aria-live`, connected focus fallback, Wayfinder routes, Inertia processing/errors, static complete Tailwind classes, `motion-reduce`, `forced-colors`, 44-pixel targets, sticky mobile actions, and no raw user-facing English. Assert EN/LT/RU onboarding key and placeholder parity plus representative one/few/many/other messages.

- [ ] **Step 3: Run both RED suites**

Run:

```bash
npm run test:frontend
php artisan test --compact tests/Feature/OnboardingFrontendTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/FrontendDesignTest.php
```

Expected: the new helper/component/catalog assertions fail; every previously discovered frontend test remains visible.

- [ ] **Step 4: Implement the exact typed contracts only**

Define `OnboardingStep`, `OnboardingProgress`, `OnboardingState`, bounded option/resource shapes, step form payloads, and pure immutable helpers. Do not add Vue markup in this step.

- [ ] **Step 5: Run the pure behavior suite and commit the frontend contract**

Run `npm run test:frontend` and expect all tests including the new onboarding cases to pass. Commit the helper/tests with `test: define onboarding interaction contracts` and push after updating progress.

## Task 7: Build The Warm Guided Route Interface

**Files:**

- Create: all onboarding Vue components listed in the File Responsibility Map
- Modify: `resources/js/pages/onboarding/Index.vue`
- Create: `lang/en/onboarding.php`, `lang/lt/onboarding.php`, `lang/ru/onboarding.php`
- Modify: `tests/Feature/OnboardingFrontendTest.php`

- [ ] **Step 1: Add complete semantic catalogs**

Each locale must contain the same nested groups: `meta`, `steps`, `actions`, `status`, `errors`, `welcome`, `preferences`, `workspace`, `project`, `task`, `product_map`, `safety`, and `results`. Required complete-message keys include step count/title, saved/saving/failure, validation summary, resume/recovery, whole-journey skip confirmation, choose/create modes, empty option states, product-map descriptions, role-aware safety descriptions, and final entity summary. Use locale-aware count forms rather than `:count` plus a fixed noun.

- [ ] **Step 2: Build the shared shell and step panel**

`OnboardingShell.vue` renders a desktop `ol` progress rail and compact mobile status from the same ordered data. Use text plus icons for complete/current/upcoming, `aria-current="step"` only on current, a native `<progress>` with a localized label, `min-h-11` controls, `max-w-app`, semantic token colors, and safe-area padding. `OnboardingStepPanel.vue` owns the heading ref, description, preview/action slots, validation summary, and concise save status.

- [ ] **Step 3: Build Welcome and Personal Setup**

Welcome advances through the generated progress route. Preferences owns a typed `useForm` containing language, timezone, date/time formats, default view, week start, and start page; it displays a live localized preview, submits through the generated preferences route, retains input on validation, and focuses its error summary on failure.

- [ ] **Step 4: Build Workspace, Project, And Task steps**

Each component has an explicit choose/create segmented control, a bounded existing-entity select, a create form, one stable request UUID per draft, action-scoped processing, full validation errors, and a preview. Workspace requires an accessible selection/create. Project requires an active selection/create. Task requires a non-archived selection/create and exposes only the selected workspace's project, assignee, status, and priority choices.

- [ ] **Step 5: Build Product Map, Safety, And Results**

Product Map uses six semantic cards with real Wayfinder destinations but keeps Continue as the sole primary action. Safety conditionally explains invite/backup capabilities without rendering forbidden mutations. Results displays saved preference/workspace/project/task facts, opens the selected task as primary when present, offers the saved start page secondarily, and completes the server gate before navigation.

- [ ] **Step 6: Coordinate focus, identity, and async state in the page**

Watch `props.progress.step`; after `nextTick`, focus the connected step heading with `tabindex="-1"`. Cancel superseded visits with `router.cancelAll()`. Never mutate props. Reset local create drafts/request keys when the selected workspace/project identity changes. The complete changing page must not be `aria-live`.

- [ ] **Step 7: Run frontend/localization/design gates and fix to GREEN**

Run:

```bash
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
php artisan test --compact tests/Feature/OnboardingFrontendTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/FrontendDesignTest.php tests/Feature/OnboardingWorkflowTest.php
```

Expected: all pass; the production build discovers every static class and generated route import.

- [ ] **Step 8: Commit and push the interface slice**

Update progress, inspect for raw text/dynamic classes/unrelated formatting, then commit `feat: build warm guided onboarding` and push `origin main`.

## Task 8: Add Replay And The Honest Dashboard Continuation Checklist

**Files:**

- Create: `app/Queries/OnboardingChecklistQuery.php`
- Create: `app/Actions/DismissOnboardingChecklist.php`
- Create: `resources/js/components/onboarding/OnboardingChecklist.vue`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `app/Http/Controllers/Settings/PreferencesController.php`
- Modify: `resources/js/pages/Dashboard.vue`
- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `lang/en/ui.php`, `lang/lt/ui.php`, `lang/ru/ui.php`
- Modify: `tests/Feature/OnboardingWorkflowTest.php`
- Modify: `tests/Feature/OnboardingFrontendTest.php`

- [ ] **Step 1: Write RED checklist/restart behavior**

Prove the checklist is hidden for pre-deployment backfilled users, shown for newly completed/skipped users until dismissed, scoped to the current accessible workspace, omits invite/backup actions without capability, derives team/security completion only from real membership/passkey/2FA state, and dismisses only the authenticated user's timestamp. Prove Settings restart leaves domain entities and completion facts intact.

- [ ] **Step 2: Implement the bounded checklist query and dismissal action**

Return a small typed prop:

```php
[
    'show' => true,
    'workspace_id' => $workspace?->id,
    'can_invite' => $workspace ? Gate::forUser($user)->allows('invite', $workspace) : false,
    'has_team_member' => $workspace?->members()->where('users.id', '!=', $user->id)->exists() ?? false,
    'has_security_factor' => $user->hasEnabledTwoFactorAuthentication() || $user->passkeys()->exists(),
    'can_manage_backups' => Gate::forUser($user)->allows('manageDatabaseBackups'),
]
```

Use at most the documented fixed query count and no query in Vue/resource/policy. Dismissal updates only `onboarding_checklist_dismissed_at` and returns back.

- [ ] **Step 3: Render checklist and replay controls**

Place `OnboardingChecklist` before Dashboard focus queues in DOM/mobile order. It uses real Wayfinder links, complete copy, text/icon completion, a 44-pixel dismiss button, concise status, and no fake percentage. Add a Preferences card explaining replay and a generated POST restart button; processing and failure stay action scoped.

- [ ] **Step 4: Run focused plus Dashboard/Settings gates**

Run onboarding workflow/frontend, Dashboard, Preferences, query budget, localization, type/lint/format/build checks. Commit `feat: continue onboarding from dashboard` and push after progress/diff review.

## Task 9: Review, Browser QA, Canonical Docs, And Android Delivery

**Files:**

- Modify: canonical documents listed in the File Responsibility Map
- Modify: `docs/progress.md`
- Verify only: `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`

- [ ] **Step 1: Run the focused backend review matrix**

Run onboarding persistence/entry/workflow/frontend, registration/verification/preferences, workspace/project/task, calendar, query budget, localization/design/architecture, schema integrity, factory/seeder, and NativePHP feature tests. Review for redirect loops, missing-preference behavior, workspace leakage, stale IDs, duplicate creates, unsafe JSON, unbounded options, false success, and migration rollback/data preservation.

- [ ] **Step 2: Run the complete automated gate**

Run:

```bash
vendor/bin/pint --format agent
composer run types:check -- --memory-limit=1G
php artisan test --compact
php artisan test --parallel --compact
php artisan test --coverage --compact
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm audit --audit-level=low
npm run build
composer validate --strict --no-check-publish
composer audit --no-interaction
git diff --check
```

Record the exact coverage-driver result rather than claiming coverage when Xdebug/PCOV remains unavailable.

- [ ] **Step 3: Verify isolated SQLite migration and runtime health**

Use an isolated allowlisted file-backed SQLite database to migrate, seed twice, run `PRAGMA integrity_check` and `foreign_key_check`, verify existing-row onboarding backfill versus new-registration pending state, and exercise `migrate:rollback --step=1`. Do not run `migrate:fresh` on the real database. Then run route/config/view caches, `app:database-health --json`, route list, and scheduler list.

- [ ] **Step 4: Perform live Herd accessibility/responsive QA**

Resolve the URL with Laravel Boost. Verify new verified first-run, resume after reload/logout, whole skip, completion, invited user, stale entity recovery, manual replay, checklist/dismiss, and week-start calendar behavior. Cover 1440x1000 and 390x844, English/Lithuanian/Russian, light/dark, reduced motion, forced colors, keyboard-only traversal, validation-summary links, heading focus, connected focus restoration, 44-pixel controls, sticky safe-area actions, one `main`, one `h1`, no overflow, and no current browser/server errors.

- [ ] **Step 5: Synchronize canonical requirements and evidence**

Add stable `sys-onboarding-001`, map it in the compliance matrix, update architecture/domain/data/frontend/design/accessibility/localization/testing/deployment/implementation-plan/CHANGELOG/progress facts, include exact migration/route/test/build/browser limitations, and keep the approved design/implementation plans as dated evidence.

- [ ] **Step 6: Build and inspect the final Android APK**

Run:

```bash
npm run build:android
DB_DATABASE=':memory:' php artisan native:run android codex-build-only --build=debug --no-tty --no-interaction
```

After the expected no-device install boundary, verify the generated APK with `aapt dump badging`, `apksigner verify --verbose --print-certs`, `zipalign -c -v 4`, and `unzip -t`. Inspect the nested Laravel archive for the onboarding page/routes/translations and confirm it excludes `database/database.sqlite`. Record absolute path, byte size, timestamp, SHA-256, package, min/target SDK, signature, alignment, and integrity. Do not commit the ignored APK.

- [ ] **Step 7: Final diff, implementation commit, progress commit, and push**

Inspect `git status`, complete diff, staged diff, generated files, secrets, and unrelated changes. Commit any remaining coherent review fixes, then canonical docs/progress separately using semantic messages. Push each commit to `origin main` without force or history rewrite and report every hash plus exact push result.

## Self-Review Result

- Spec coverage: automatic new-user entry, existing-user backfill/manual replay, eight steps, preferences/week start, invited/workspace-less branches, real project/task creation, resume/skip/results, role-aware safety, Dashboard continuation, EN/LT/RU, accessibility, responsive Warm Precision design, security/idempotency, NativePHP, and APK verification each map to a task.
- Placeholder scan: the plan contains no deferred implementation marker; every task names exact files, RED/GREEN commands, expected outcomes, and commit boundaries.
- Type consistency: `OnboardingStep` values, preference column names, request route names, state keys, TypeScript step union, and operation-ledger identity are consistent across backend, frontend, tests, and documentation.
