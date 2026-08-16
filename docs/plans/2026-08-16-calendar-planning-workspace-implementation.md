# Calendar Planning Workspace Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. This repository session executes inline because those sub-skills are not exposed and the user already directed implementation.

**Goal:** Build a URL-persistent, workspace-scoped month/week/agenda calendar with bounded range queries and a responsive Warm Precision planning interface.

**Architecture:** An authorized Form Request normalizes `view` and `date`; `CalendarQuery` accepts explicit date bounds and returns only visible tasks plus a bounded overdue preview. The Inertia page coordinates generated Wayfinder visits and delegates each visual mode and the attention rail to focused Vue components.

**Tech Stack:** Laravel 13, PHP 8.4-compatible syntax, SQLite, Inertia 3, Vue 3 Composition API, TypeScript, Wayfinder, Tailwind CSS 4, Pest 4.

---

## File Map

- Create `app/Http/Requests/CalendarIndexRequest.php`: authorization, URL validation, normalized view/anchor/range state.
- Modify `app/Queries/CalendarQuery.php`: visible-range and bounded-overdue workspace queries.
- Modify `app/Http/Controllers/CalendarController.php`: coordinate request, workspace, query, and serialized props.
- Create `resources/js/components/calendar/calendar-types.ts`: shared calendar prop and view types.
- Create `resources/js/components/calendar/calendar-date.ts`: deterministic date-only helpers used by the page components.
- Create `resources/js/components/calendar/CalendarPeriodNavigator.vue`: view and period navigation controls.
- Create `resources/js/components/calendar/CalendarMonthGrid.vue`: desktop month grid and mobile dated-card mode.
- Create `resources/js/components/calendar/CalendarWeekView.vue`: seven-day planning columns.
- Create `resources/js/components/calendar/CalendarAgendaView.vue`: bounded chronological groups.
- Create `resources/js/components/calendar/CalendarAttentionRail.vue`: bounded overdue task preview.
- Modify `resources/js/pages/calendar/Index.vue`: page composition, immutable props, Wayfinder/Inertia visits.
- Modify `lang/en/workspace.php`, `lang/lt/workspace.php`, and `lang/ru/workspace.php`: semantic calendar copy parity.
- Modify `tests/Feature/WorkspacePagesTest.php`: request, isolation, range, and bounded-query behavior.
- Modify `tests/Feature/FrontendDesignTest.php`: frontend architecture, navigation, accessibility, responsive, and localization source contracts.
- Modify `docs/progress.md`: append factual phase decisions, checks, limitations, commits, and push status.

### Task 1: Prove normalized calendar URL and range behavior

**Files:**

- Modify: `tests/Feature/WorkspacePagesTest.php`
- Create: `app/Http/Requests/CalendarIndexRequest.php`

- [ ] **Step 1: Write failing feature tests**

Add tests that freeze time and request an exact URL:

```php
use Illuminate\Support\Carbon;

test('calendar exposes normalized month state and only the visible grid range', function () {
    Carbon::setTestNow('2026-08-16 10:00:00');
    [$user, $workspace] = createWarmPrecisionContext();

    $visible = Todo::factory()->for($workspace)->create(['due_date' => '2026-08-31']);
    Todo::factory()->for($workspace)->create(['due_date' => '2026-10-01']);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => 'month', 'date' => '2026-08-16']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('calendar/Index')
            ->where('calendar.view', 'month')
            ->where('calendar.anchor_date', '2026-08-16')
            ->where('calendar.start_date', '2026-07-26')
            ->where('calendar.end_date', '2026-09-05')
            ->has('todos', 1)
            ->where('todos.0.id', $visible->id));
});

test('calendar rejects invalid URL state', function (array $query, string $field) {
    [$user, $workspace] = createWarmPrecisionContext();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->from(route('calendar'))
        ->get(route('calendar', $query))
        ->assertRedirect(route('calendar'))
        ->assertSessionHasErrors($field);
})->with([
    'view' => [['view' => 'year', 'date' => '2026-08-16'], 'view'],
    'date' => [['view' => 'month', 'date' => '16-08-2026'], 'date'],
]);
```

- [ ] **Step 2: Run the tests and confirm RED**

Run: `php artisan test --compact tests/Feature/WorkspacePagesTest.php --filter=calendar`

Expected: failures because `calendar` metadata is absent, visible tasks are not range bounded, and invalid query values are not validated.

- [ ] **Step 3: Create the authorized Form Request**

Generate with `php artisan make:request CalendarIndexRequest --no-interaction`, then implement:

```php
public function authorize(): bool
{
    return $this->user() instanceof User;
}

public function rules(): array
{
    return [
        'view' => ['sometimes', 'string', Rule::in(['month', 'week', 'agenda'])],
        'date' => ['sometimes', Rule::date()->format('Y-m-d')],
    ];
}

/** @return array{view: string, anchor_date: string, start_date: string, end_date: string} */
public function calendarState(string $timezone): array
{
    $view = is_string($this->validated('view')) ? $this->validated('view') : 'month';
    $anchor = CarbonImmutable::createFromFormat(
        '!Y-m-d',
        is_string($this->validated('date')) ? $this->validated('date') : CarbonImmutable::now($timezone)->toDateString(),
        $timezone,
    );

    [$start, $end] = match ($view) {
        'week' => [$anchor->startOfWeek(CarbonInterface::SUNDAY), $anchor->endOfWeek(CarbonInterface::SATURDAY)],
        'agenda' => [$anchor->startOfDay(), $anchor->addDays(30)->endOfDay()],
        default => [$anchor->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY), $anchor->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY)],
    };

    return [
        'view' => $view,
        'anchor_date' => $anchor->toDateString(),
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ];
}
```

- [ ] **Step 4: Keep request tests RED until the controller consumes the state**

Run the same focused command and confirm validation now passes while response-contract tests still fail for missing props.

### Task 2: Bound workspace reads and expose normalized Inertia props

**Files:**

- Modify: `app/Queries/CalendarQuery.php`
- Modify: `app/Http/Controllers/CalendarController.php`
- Modify: `tests/Feature/WorkspacePagesTest.php`

- [ ] **Step 1: Add failing range, isolation, and overdue-preview assertions**

```php
test('calendar keeps the overdue preview workspace scoped bounded and ordered', function () {
    Carbon::setTestNow('2026-08-16 10:00:00');
    [$user, $workspace] = createWarmPrecisionContext();

    Todo::factory()->count(8)->for($workspace)->sequence(
        fn ($sequence) => ['title' => "Overdue {$sequence->index}", 'due_date' => Carbon::parse('2026-08-15')->subDays($sequence->index)],
    )->create();
    Todo::factory()->for(Workspace::factory()->create())->create(['due_date' => '2026-08-15']);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => 'week', 'date' => '2026-08-16']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('overdueTodos', 6)
            ->where('overdueTodos.0.due_date', '2026-08-08')
            ->where('overdueTodos.5.due_date', '2026-08-13'));
});
```

- [ ] **Step 2: Run focused tests and confirm RED**

Run: `php artisan test --compact tests/Feature/WorkspacePagesTest.php --filter=calendar`

Expected: overdue preview and normalized bounded query assertions fail.

- [ ] **Step 3: Implement indexed range methods**

Replace the eager all-dated-tasks method with explicit methods:

```php
/** @return Collection<int, Todo> */
public function forRange(Workspace $workspace, string $startDate, string $endDate): Collection
{
    return $this->baseQuery($workspace)
        ->whereBetween('due_date', [$startDate, $endDate])
        ->orderBy('due_date')
        ->orderBy('id')
        ->get();
}

/** @return Collection<int, Todo> */
public function overduePreview(Workspace $workspace, string $today, int $limit = 6): Collection
{
    return $this->baseQuery($workspace)
        ->where('due_date', '<', $today)
        ->whereNull('completed_at')
        ->orderBy('due_date')
        ->orderBy('id')
        ->limit($limit)
        ->get();
}
```

`baseQuery()` selects only calendar fields and eager-loads project, status, and priority definitions through the authorized workspace relation.

- [ ] **Step 4: Update the thin controller**

Type-hint `CalendarIndexRequest`, obtain the user's configured timezone, call `calendarState()`, query visible and overdue tasks, serialize both collections through one private typed method, and return:

```php
return Inertia::render('calendar/Index', [
    'calendar' => $calendar,
    'todos' => $this->serializeTodos($visibleTodos),
    'overdueTodos' => $this->serializeTodos($overdueTodos),
]);
```

- [ ] **Step 5: Verify GREEN and query budget**

Run:

- `php artisan test --compact tests/Feature/WorkspacePagesTest.php --filter=calendar`
- `php artisan test --compact tests/Feature/PageQueryBudgetTest.php --filter=calendar`

Expected: all selected tests pass with bounded query counts.

### Task 3: Prove frontend planning and accessibility contracts

**Files:**

- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Add failing source-contract tests**

Assert that the page imports `router` and the generated `calendar` route, uses immutable `calendar`, `todos`, and `overdueTodos` props, and composes all five calendar components. Assert the navigator contains `aria-busy`, disabled controls, 44-pixel targets, visible focus, and `motion-reduce:transition-none`. Assert the month component contains separate `md:hidden` and `hidden md:grid` representations and task links use Wayfinder with `prefetch`.

```php
test('calendar planning workspace uses URL state and focused accessible components', function () {
    $page = File::get(resource_path('js/pages/calendar/Index.vue'));
    $navigator = File::get(resource_path('js/components/calendar/CalendarPeriodNavigator.vue'));
    $month = File::get(resource_path('js/components/calendar/CalendarMonthGrid.vue'));

    expect($page)
        ->toContain("import { router } from '@inertiajs/vue3'")
        ->toContain("import { calendar as calendarRoute } from '@/routes'")
        ->toContain('CalendarPeriodNavigator')
        ->toContain('CalendarAttentionRail')
        ->and($navigator)
        ->toContain('aria-busy')
        ->toContain('min-h-11')
        ->toContain('focus-visible:ring-orange-500')
        ->toContain('motion-reduce:transition-none')
        ->and($month)
        ->toContain('md:hidden')
        ->toContain('hidden md:grid')
        ->toContain('prefetch');
});
```

- [ ] **Step 2: Run the test and confirm RED**

Run: `php artisan test --compact tests/Feature/FrontendDesignTest.php --filter=calendar`

Expected: failures identify missing components and URL-backed navigation.

### Task 4: Build typed calendar components and page coordination

**Files:**

- Create: `resources/js/components/calendar/calendar-types.ts`
- Create: `resources/js/components/calendar/calendar-date.ts`
- Create: `resources/js/components/calendar/CalendarPeriodNavigator.vue`
- Create: `resources/js/components/calendar/CalendarMonthGrid.vue`
- Create: `resources/js/components/calendar/CalendarWeekView.vue`
- Create: `resources/js/components/calendar/CalendarAgendaView.vue`
- Create: `resources/js/components/calendar/CalendarAttentionRail.vue`
- Modify: `resources/js/pages/calendar/Index.vue`

- [ ] **Step 1: Define shared immutable contracts**

```ts
export type CalendarView = 'month' | 'week' | 'agenda';

export type CalendarState = {
    view: CalendarView;
    anchor_date: string;
    start_date: string;
    end_date: string;
};

export type CalendarTodo = Pick<Todo, 'id' | 'title' | 'status' | 'priority' | 'due_date' | 'is_completed' | 'status_definition' | 'priority_definition'> & {
    project: Pick<Project, 'id' | 'name' | 'color'> | null;
};
```

- [ ] **Step 2: Add deterministic date-only helpers**

Implement `parseDateKey`, `toDateKey`, `addDays`, `addMonths`, `startOfSundayWeek`, and `buildMonthDays` without converting date-only values through UTC midnight.

- [ ] **Step 3: Implement the period navigator**

The component receives normalized state and `processing`, emits `navigate`, and renders the existing shared segmented controls plus Today/previous/next buttons. Every action is disabled during visits, icon buttons have localized names, and selection uses `aria-selected`.

- [ ] **Step 4: Implement view components**

Month uses a seven-column desktop grid and mobile date cards; week uses seven semantic day sections; agenda groups only the server-bounded tasks. Each component displays localized empty states, project plus priority/status text, completion icons, 44-pixel links, prefetching, visible focus, and reduced-motion-safe color transitions.

- [ ] **Step 5: Implement the attention rail**

Render the server-provided overdue preview and a task-index Wayfinder action for the complete overdue queue. Keep the task count and due date textual so color is supplementary.

- [ ] **Step 6: Rebuild the page coordinator**

Use generated navigation only:

```ts
function navigate(view: CalendarView, anchorDate: string): void {
    router.visit(
        calendarRoute({ query: { view, date: anchorDate } }),
        {
            preserveScroll: true,
            replace: true,
            onStart: () => (isNavigating.value = true),
            onFinish: () => (isNavigating.value = false),
        },
    );
}
```

The page derives metrics from the bounded visible and overdue props, maintains no duplicate view/date refs, and composes exactly one active visual mode.

- [ ] **Step 7: Run frontend contract tests and type checking**

Run:

- `php artisan test --compact tests/Feature/FrontendDesignTest.php --filter=calendar`
- `npm run types:check`

Expected: both pass.

### Task 5: Localize the complete planning experience

**Files:**

- Modify: `lang/en/workspace.php`
- Modify: `lang/lt/workspace.php`
- Modify: `lang/ru/workspace.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`

- [ ] **Step 1: Add a failing parity assertion for new semantic keys**

Extend localization coverage for `planning_period`, `visible_tasks`, `attention`, `attention_description`, `view_all_overdue`, `no_overdue`, `loading_period`, `tasks_on_date`, and `outside_month`.

- [ ] **Step 2: Run localization tests and confirm RED**

Run: `php artisan test --compact tests/Feature/FrontendLocalizationTest.php --filter=workspace`

Expected: missing keys fail parity or value assertions.

- [ ] **Step 3: Add complete English, Lithuanian, and Russian translations**

Keep identical nested keys and placeholders across all three `calendar` arrays; use complete phrases rather than concatenated fragments.

- [ ] **Step 4: Verify GREEN**

Run: `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/WorkspacePagesTest.php tests/Feature/FrontendDesignTest.php`

Expected: all focused backend, localization, and design tests pass.

### Task 6: Verify live behavior and deliver the isolated phase

**Files:**

- Modify: `docs/progress.md`

- [ ] **Step 1: Run PHP formatting and static checks**

- `vendor/bin/pint --dirty --format agent`
- `composer run types:check`

- [ ] **Step 2: Run backend and frontend suites**

- `php artisan test --compact`
- `npm run test:frontend`
- `npm run types:check`
- `npm run lint:check`
- `npm run format:check`
- `npm run build`

- [ ] **Step 3: Run repository and dependency checks**

- `composer validate --strict --no-check-publish`
- `composer audit --no-interaction`
- `npm audit --omit=dev`
- `git diff --check`

Record unchanged dependency advisories separately from regressions.

- [ ] **Step 4: Perform live Herd browser QA**

At `https://xiaomi-mimo.test/calendar`, verify desktop and 390-pixel mobile layouts in light and dark modes; switch month/week/agenda; navigate periods; confirm query parameters, reload, back/forward, task links, focus states, target sizes, no overflow, and no new browser-console/page errors.

- [ ] **Step 5: Append the progress record**

Record baseline, files, no migrations/packages, design decisions, exact checks, audit limitations, remaining work, implementation commit, documentation commit, and push status in `docs/progress.md` without staging the user's unrelated documentation work.

- [ ] **Step 6: Commit and push coherent slices**

Stage only phase-owned implementation/test/translation files, inspect the cached diff, commit `feat: build calendar planning workspace`, and push `origin main`. Then stage only `docs/progress.md`, commit `docs: record calendar planning workspace`, and push again.

## Self-Review

- Spec coverage: URL state, bounded workspace queries, month/week/agenda, overdue attention, localized responsive design, validation, accessibility, and browser verification each map to a task.
- Placeholder scan: the plan contains no deferred implementation placeholders.
- Type consistency: `CalendarView`, `CalendarState`, and `CalendarTodo` names and server prop keys are consistent across backend tests, Vue components, and page coordination.
- Scope: task creation, rescheduling, recurrence editing, external sync, new dependencies, migrations, and route changes remain excluded.
