<?php

declare(strict_types=1);

use App\Enums\WorkspaceRole;
use App\Models\ActivityLog;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Queries\ProjectDetailQuery;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

/** @param Closure(): TestResponse $request */
function sqlitePageQueryCount(Closure $request): int
{
    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        $request()->assertOk();

        return count($connection->getQueryLog());
    } finally {
        $connection->disableQueryLog();
    }
}

test('major pages keep bounded query counts as workspace data grows', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();
    WorkspaceMember::factory()->for($workspace)->for($user)->create(['role' => WorkspaceRole::Owner]);
    $project = Project::factory()->for($workspace)->create();
    $todo = Todo::factory()->for($workspace)->for($project)->create([
        'due_date' => now()->toDateString(),
        'completed_at' => null,
    ]);
    Todo::factory()->for($workspace)->for($project)->create([
        'due_date' => now()->subDay()->toDateString(),
        'completed_at' => null,
    ]);
    Todo::factory()->for($workspace)->for($project)->create([
        'due_date' => now()->addDay()->toDateString(),
        'completed_at' => null,
    ]);
    $checklist = Checklist::factory()->for($todo)->create();
    ChecklistItem::factory()->for($checklist)->create();
    ActivityLog::factory()->for($workspace)->for($user)->create();
    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'query-budget',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->withSession(['current_workspace_id' => $workspace->id]);

    $requests = [
        'dashboard' => fn () => $this->get(route('dashboard')),
        'tasks' => fn () => $this->get(route('todos.index')),
        'task_detail' => fn () => $this->get(route('todos.show', $todo)),
        'projects' => fn () => $this->get(route('projects')),
        'project_detail' => fn () => $this->get(route('projects.show', [$workspace, $project])),
        'activity' => fn () => $this->get(route('activity')),
        'calendar' => fn () => $this->get(route('calendar')),
        'notifications' => fn () => $this->get(route('notifications.index')),
    ];

    Project::factory()->count(20)->for($workspace)->create();
    Todo::factory()->count(20)->for($workspace)->for($project)->create([
        'due_date' => now()->addDays(2)->toDateString(),
        'completed_at' => null,
    ]);
    $checklists = Checklist::factory()->count(20)->for($todo)->create();
    $checklists->each(fn (Checklist $item) => ChecklistItem::factory()->for($item)->create());
    ActivityLog::factory()->count(20)->for($workspace)->for($user)->create();

    DB::table('notifications')->insert(collect(range(1, 20))->map(fn (): array => [
        'id' => (string) Str::uuid(),
        'type' => 'query-budget',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ])->all());

    foreach ($requests as $request) {
        $request()->assertOk();
    }

    $expanded = collect($requests)->mapWithKeys(
        fn (Closure $request, string $name): array => [$name => sqlitePageQueryCount($request)],
    );

    Project::factory()->count(20)->for($workspace)->create();
    Todo::factory()->count(20)->for($workspace)->for($project)->create([
        'due_date' => now()->addDays(2)->toDateString(),
        'completed_at' => null,
    ]);
    ActivityLog::factory()->count(20)->for($workspace)->for($user)->create();

    DB::table('notifications')->insert(collect(range(1, 20))->map(fn (): array => [
        'id' => (string) Str::uuid(),
        'type' => 'query-budget',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ])->all());

    $larger = collect($requests)->mapWithKeys(
        fn (Closure $request, string $name): array => [$name => sqlitePageQueryCount($request)],
    );

    expect($larger->all())->toBe($expanded->all());

    foreach ($larger as $page => $queryCount) {
        expect($queryCount, "{$page} query budget")->toBeLessThanOrEqual(35);
    }
});

test('representative ordered queries use their scoped indexes without unnecessary temporary sorting', function (string $sql, string $index, bool $allowsTemporarySort = false) {
    $plan = collect(DB::select("EXPLAIN QUERY PLAN {$sql}"))->pluck('detail')->implode(' | ');

    expect($plan)->toContain($index);

    if (! $allowsTemporarySort) {
        expect($plan)->not->toContain('USE TEMP B-TREE FOR ORDER BY');
    }
})->with([
    'task index' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND is_archived = 0 ORDER BY is_pinned DESC, position, id LIMIT 50",
        'todos_workspace_archive_pinned_position_index',
    ],
    'project tasks' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 ORDER BY position, id",
        'todos_workspace_project_archive_position_index',
    ],
    'project tasks by status' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 AND status_id = 'status' ORDER BY position, id LIMIT 25",
        'todos_workspace_project_archive_status_position_index',
    ],
    'project tasks by priority' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 AND priority_id = 'priority' ORDER BY position, id LIMIT 25",
        'todos_workspace_project_archive_priority_position_index',
    ],
    'project tasks by assignee' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 AND assigned_to = 'user' ORDER BY position, id LIMIT 25",
        'todos_workspace_project_archive_assignee_position_index',
    ],
    'project tasks by deadline attention' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 AND completed_at IS NULL AND due_date IS NOT NULL AND due_date <= '2026-08-23' ORDER BY due_date, (SELECT position FROM task_priorities WHERE task_priorities.id = todos.priority_id), position, id LIMIT 5",
        'todos_workspace_project_archive_completion_due_index',
        true,
    ],
    'project tasks sorted by due date' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 ORDER BY due_date IS NULL, due_date, position, id LIMIT 25",
        'todos_workspace_project_archive_completion_due_index',
        true,
    ],
    'project tasks sorted by priority definition' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 ORDER BY (SELECT position FROM task_priorities WHERE task_priorities.id = todos.priority_id), position, id LIMIT 25",
        'todos_workspace_project_archive_priority_position_index',
        true,
    ],
    'project tasks sorted by recent update' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND project_id = 'project' AND is_archived = 0 ORDER BY updated_at DESC, id LIMIT 25",
        'todos_workspace_project_archive_completion_due_index',
        true,
    ],
    'calendar' => [
        "SELECT id FROM todos WHERE workspace_id = 'workspace' AND is_archived = 0 AND due_date IS NOT NULL ORDER BY due_date, id",
        'todos_workspace_archive_due_index',
    ],
    'projects' => [
        "SELECT id FROM projects WHERE workspace_id = 'workspace' ORDER BY position, id",
        'projects_workspace_position_index',
    ],
    'checklists' => [
        "SELECT id FROM checklists WHERE todo_id = 'todo' ORDER BY position, id",
        'checklists_todo_position_index',
    ],
    'checklist items' => [
        "SELECT id FROM checklist_items WHERE checklist_id = 'checklist' ORDER BY position, id",
        'checklist_items_checklist_position_index',
    ],
    'activity' => [
        "SELECT id FROM activity_logs WHERE workspace_id = 'workspace' ORDER BY created_at DESC, id DESC LIMIT 50",
        'activity_logs_workspace_created_index',
    ],
    'activity by actor' => [
        "SELECT id FROM activity_logs INDEXED BY activity_logs_workspace_user_created_index WHERE workspace_id = 'workspace' AND user_id = 'user' ORDER BY created_at DESC, id DESC LIMIT 20",
        'activity_logs_workspace_user_created_index',
    ],
    'activity by event category' => [
        "SELECT id FROM activity_logs INDEXED BY activity_logs_workspace_event_created_index WHERE workspace_id = 'workspace' AND event IN ('updated', 'attached', 'detached') ORDER BY created_at DESC, id DESC LIMIT 20",
        'activity_logs_workspace_event_created_index',
        true,
    ],
    'notifications' => [
        "SELECT id FROM notifications WHERE notifiable_type = 'App\\Models\\User' AND notifiable_id = 'user' ORDER BY created_at DESC, id DESC LIMIT 20",
        'notifications_notifiable_created_index',
    ],
]);

test('project operation sort queries use scoped production indexes', function (string $sort, string $index, bool $allowsTemporarySort = false) {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $project = Project::factory()->for($workspace)->create();
    Todo::factory()->pending()->for($workspace)->for($project)->create();
    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        app(ProjectDetailQuery::class)->todos($workspace, $project->id, [
            'search' => null,
            'status' => null,
            'priority' => null,
            'assignee' => null,
            'attention' => 'all',
            'sort' => $sort,
        ], CarbonImmutable::parse('2026-08-16'));

        $pageQuery = collect($connection->getQueryLog())->first(
            fn (array $query): bool => str_contains($query['query'], 'order by'),
        );
    } finally {
        $connection->disableQueryLog();
    }

    expect($pageQuery)->toBeArray();

    $plan = collect(DB::select(
        "EXPLAIN QUERY PLAN {$pageQuery['query']}",
        $pageQuery['bindings'],
    ))->pluck('detail')->implode(' | ');

    expect($plan)->toContain($index);

    if (! $allowsTemporarySort) {
        expect($plan)->not->toContain('USE TEMP B-TREE FOR ORDER BY');
    }
})->with([
    'position sort' => ['position', 'todos_workspace_project_archive_position_index'],
    'due-date sort' => ['due_date', 'todos_workspace_project_archive_due_position_index'],
    'updated sort' => ['updated', 'todos_workspace_project_archive_updated_index'],
    'priority-definition sort' => ['priority', 'todos_workspace_project_archive_', true],
]);
