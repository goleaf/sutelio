<?php

declare(strict_types=1);

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use App\Enums\WorkspaceRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Project;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Queries\ProjectDetailQuery;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

/**
 * @return array{owner: User, workspace: Workspace, project: Project, pending: TaskStatus, completed: TaskStatus, high: TaskPriority, low: TaskPriority}
 */
function projectOperationsContext(): array
{
    $owner = User::factory()->create();
    $workspace = Workspace::factory()
        ->for($owner, 'owner')
        ->withOwnerMembership()
        ->create();

    return [
        'owner' => $owner,
        'workspace' => $workspace,
        'project' => Project::factory()->for($workspace)->create(),
        'pending' => $workspace->taskStatuses()->where('key', TodoStatus::Pending->value)->firstOrFail(),
        'completed' => $workspace->taskStatuses()->where('key', TodoStatus::Completed->value)->firstOrFail(),
        'high' => $workspace->taskPriorities()->where('key', TodoPriority::High->value)->firstOrFail(),
        'low' => $workspace->taskPriorities()->where('key', TodoPriority::Low->value)->firstOrFail(),
    ];
}

test('project task results are bounded deterministic and keep page two reachable', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();

    Todo::factory()
        ->count(27)
        ->for($workspace)
        ->for($project)
        ->state(new Sequence(fn (Sequence $sequence): array => [
            'title' => "Ordered task {$sequence->index}",
            'position' => $sequence->index,
        ]))
        ->create();

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Show')
            ->has('todos.data', 25)
            ->where('todos.meta.total', 27)
            ->where('todos.data.0.title', 'Ordered task 0')
            ->where('todos.data.24.title', 'Ordered task 24')
            ->where('todos.links.next', fn (?string $url): bool => is_string($url) && str_contains($url, 'page=2')));
});

test('project task filters are scoped and preserved in pagination links', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project, 'pending' => $pending, 'high' => $high, 'low' => $low] = projectOperationsContext();
    $assignee = User::factory()->create(['name' => 'Assigned Collaborator']);
    WorkspaceMember::factory()->for($workspace)->for($assignee)->create(['role' => WorkspaceRole::Member]);

    $matchingTask = Todo::factory()->for($workspace)->for($project)->create([
        'title' => 'Launch readiness review',
        'status' => TodoStatus::Pending,
        'status_id' => $pending->id,
        'priority' => TodoPriority::High,
        'priority_id' => $high->id,
        'assigned_to' => $assignee->id,
        'position' => 1,
    ]);
    Todo::factory()->for($workspace)->for($project)->create([
        'title' => 'Launch readiness notes',
        'status' => TodoStatus::Pending,
        'status_id' => $pending->id,
        'priority' => TodoPriority::Low,
        'priority_id' => $low->id,
        'assigned_to' => $owner->id,
        'position' => 2,
    ]);

    $query = [
        'search' => 'readiness',
        'status' => $pending->id,
        'priority' => $high->id,
        'assignee' => $assignee->id,
        'sort' => 'due_date',
    ];

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project, ...$query]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters', [
                ...$query,
                'attention' => 'all',
            ])
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $matchingTask->id));
});

test('project task attention filters distinguish overdue due soon and unassigned work', function (string $attention, string $expectedTitle) {
    CarbonImmutable::setTestNow('2026-08-16 12:00:00');
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();

    foreach ([
        ['title' => 'Overdue work', 'due_date' => now()->subDay()->toDateString(), 'assigned_to' => $owner->id],
        ['title' => 'Due soon work', 'due_date' => now()->addDays(3)->toDateString(), 'assigned_to' => $owner->id],
        ['title' => 'Unassigned work', 'due_date' => null, 'assigned_to' => null],
    ] as $task) {
        Todo::factory()->pending()->for($workspace)->for($project)->create($task);
    }

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project, 'attention' => $attention]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.attention', $attention)
            ->has('todos.data', 1)
            ->where('todos.data.0.title', $expectedTitle));
})->with([
    'overdue' => ['overdue', 'Overdue work'],
    'due soon' => ['due_soon', 'Due soon work'],
    'unassigned' => ['unassigned', 'Unassigned work'],
]);

test('project task filters reject identifiers outside the routed workspace', function (string $filter) {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    $foreignOwner = User::factory()->create();
    $foreignWorkspace = Workspace::factory()
        ->for($foreignOwner, 'owner')
        ->withOwnerMembership()
        ->create();

    $foreignValue = match ($filter) {
        'status' => $foreignWorkspace->taskStatuses()->firstOrFail()->id,
        'priority' => $foreignWorkspace->taskPriorities()->firstOrFail()->id,
        'assignee' => $foreignOwner->id,
    };

    $this->actingAs($owner)
        ->from(route('projects.show', [$workspace, $project]))
        ->get(route('projects.show', [$workspace, $project, $filter => $foreignValue]))
        ->assertRedirect(route('projects.show', [$workspace, $project]))
        ->assertSessionHasErrors($filter);
})->with(['status', 'priority', 'assignee']);

test('project metrics and attention preview describe the complete project', function () {
    CarbonImmutable::setTestNow('2026-08-16 12:00:00');
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project, 'pending' => $pending, 'completed' => $completed, 'high' => $high] = projectOperationsContext();

    Todo::factory()->count(20)->pending()->for($workspace)->for($project)->create([
        'assigned_to' => $owner->id,
        'due_date' => null,
    ]);
    Todo::factory()->count(10)->completed()->for($workspace)->for($project)->create([
        'status' => TodoStatus::Completed,
        'status_id' => $completed->id,
        'assigned_to' => $owner->id,
        'due_date' => null,
    ]);
    Todo::factory()->count(6)->pending()->for($workspace)->for($project)
        ->state(new Sequence(fn (Sequence $sequence): array => [
            'title' => "Attention task {$sequence->index}",
            'status_id' => $pending->id,
            'priority' => TodoPriority::High,
            'priority_id' => $high->id,
            'assigned_to' => $owner->id,
            'due_date' => now()->subDays(6 - $sequence->index)->toDateString(),
            'position' => $sequence->index,
        ]))
        ->create();
    Todo::factory()->pending()->for($workspace)->for($project)->create([
        'title' => 'Unassigned task',
        'assigned_to' => null,
        'due_date' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('todos.data', 25)
            ->where('metrics.total', 37)
            ->where('metrics.open', 27)
            ->where('metrics.completed', 10)
            ->where('metrics.completion_rate', 27)
            ->where('metrics.attention', 6)
            ->where('metrics.overdue', 6)
            ->where('metrics.due_soon', 0)
            ->where('metrics.unassigned', 1)
            ->has('attentionTasks.data', 5)
            ->where('attentionTasks.total', 6));
});

test('project operation stable props are omitted from task-only partial reloads', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    Todo::factory()->for($workspace)->for($project)->create();

    $headers = [
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'projects/Show',
        'X-Inertia-Partial-Data' => 'todos,filters',
    ];
    $version = app(HandleInertiaRequests::class)->version(request());

    if (is_string($version)) {
        $headers['X-Inertia-Version'] = $version;
    }

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]), $headers)
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'projects/Show')
        ->assertJsonStructure(['props' => ['todos', 'filters']])
        ->assertJsonMissingPath('props.metrics')
        ->assertJsonMissingPath('props.taskDefinitions')
        ->assertJsonMissingPath('props.assignees')
        ->assertJsonMissingPath('props.attentionTasks')
        ->assertJsonMissingPath('props.priorityDistribution');
});

test('project-only partial reloads do not execute project task queries', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    Todo::factory()->for($workspace)->for($project)->create();

    $headers = [
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'projects/Show',
        'X-Inertia-Partial-Data' => 'project',
    ];
    $version = app(HandleInertiaRequests::class)->version(request());

    if (is_string($version)) {
        $headers['X-Inertia-Version'] = $version;
    }

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]), $headers)
        ->assertOk()
        ->assertJsonStructure(['props' => ['project']])
        ->assertJsonMissingPath('props.todos');

    expect(collect($queries)->filter(
        fn (string $query): bool => preg_match('/\bfrom\s+["`]?todos["`]?\b/i', $query) === 1,
    ))->toBeEmpty();
});

test('project task rows localize built-in status and priority names', function (string $locale) {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project, 'pending' => $pending, 'high' => $high] = projectOperationsContext();
    $owner->preferences()->updateOrCreate([], [
        'language' => $locale,
        'timezone' => 'Europe/Vilnius',
    ]);
    $owner->unsetRelation('preferences');
    Todo::factory()->for($workspace)->for($project)->create([
        'status' => TodoStatus::Pending,
        'status_id' => $pending->id,
        'priority' => TodoPriority::High,
        'priority_id' => $high->id,
    ]);

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('todos.data.0.status_definition.name', trans('tasks.statuses.pending', locale: $locale))
            ->where('todos.data.0.priority_definition.name', trans('tasks.priorities.high', locale: $locale)));
})->with(['lt', 'ru']);

test('project filters reject archived status and priority identifiers', function (string $filter) {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    $definition = $filter === 'status'
        ? TaskStatus::factory()->for($workspace)->create(['is_archived' => true])
        : TaskPriority::factory()->for($workspace)->create(['is_archived' => true]);

    $this->actingAs($owner)
        ->from(route('projects.show', [$workspace, $project]))
        ->get(route('projects.show', [$workspace, $project, $filter => $definition->id]))
        ->assertRedirect(route('projects.show', [$workspace, $project]))
        ->assertSessionHasErrors($filter);
})->with(['status', 'priority']);

test('project pulse includes archived priorities that remain referenced by open tasks', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project, 'high' => $high, 'low' => $low] = projectOperationsContext();
    $high->update(['is_archived' => true]);
    Todo::factory()->count(2)->pending()->for($workspace)->for($project)->create([
        'priority' => TodoPriority::High,
        'priority_id' => $high->id,
    ]);
    Todo::factory()->pending()->for($workspace)->for($project)->create([
        'priority' => TodoPriority::Low,
        'priority_id' => $low->id,
    ]);

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('metrics.open', 3)
            ->where('priorityDistribution', fn ($distribution): bool => collect($distribution)
                ->contains(fn (array $priority): bool => $priority['id'] === $high->id && $priority['count'] === 2)
                && collect($distribution)->sum('count') === 3));
});

test('project assignee options stay bounded and include a valid selected member', function () {
    ['workspace' => $workspace, 'project' => $project] = projectOperationsContext();

    $members = User::factory()
        ->count(102)
        ->state(new Sequence(fn (Sequence $sequence): array => [
            'name' => sprintf('Member %03d', $sequence->index),
        ]))
        ->create();
    $members->each(fn (User $member) => WorkspaceMember::factory()
        ->for($workspace)
        ->for($member)
        ->create(['role' => WorkspaceRole::Member]));
    $selectedMember = User::factory()->create(['name' => 'ZZZ Selected Member']);
    WorkspaceMember::factory()
        ->for($workspace)
        ->for($selectedMember)
        ->create(['role' => WorkspaceRole::Member]);

    expect($selectedMember)->toBeInstanceOf(User::class);

    $assignees = app(ProjectDetailQuery::class)->assignees($workspace, $selectedMember->id);

    expect($assignees)
        ->toHaveCount(101)
        ->and(collect($assignees)->pluck('id'))
        ->toContain($selectedMember->id);
});

test('archived projects remain readable with normalized operations state', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    $project->update(['is_archived' => true]);
    Todo::factory()->for($workspace)->for($project)->create(['title' => 'Archived project task']);

    $this->actingAs($owner)
        ->get(route('projects.show', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('project.data.is_archived', true)
            ->where('filters', [
                'search' => null,
                'status' => null,
                'priority' => null,
                'assignee' => null,
                'attention' => 'all',
                'sort' => 'position',
            ])
            ->has('todos.data', 1)
            ->where('todos.data.0.title', 'Archived project task'));
});

test('archived projects reject direct task creation until restored', function () {
    ['owner' => $owner, 'workspace' => $workspace, 'project' => $project] = projectOperationsContext();
    $project->update(['is_archived' => true]);

    $this->actingAs($owner)
        ->postJson(route('todos.store', $workspace), [
            'title' => 'Blocked archived project task',
            'project_id' => $project->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('project_id');

    $this->assertDatabaseMissing('todos', ['title' => 'Blocked archived project task']);
});
