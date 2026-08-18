<?php

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use App\Enums\WorkspaceRole;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array{0: User, 1: Workspace}
 */
function createWarmPrecisionContext(string $language = 'en'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();

    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);
    UserPreference::create([
        'user_id' => $user->id,
        'language' => $language,
        'timezone' => 'Europe/Vilnius',
    ]);

    return [$user, $workspace];
}

test('workspace pages expose semantic copy in the preferred language', function (string $routeName, string $component) {
    [$user, $workspace] = createWarmPrecisionContext('ru');

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('workspaceUi.projects.title', 'Проекты')
            ->where('workspaceUi.calendar.title', 'Календарь')
            ->where('workspaceUi.notifications.title', 'Уведомления')
            ->where('workspaceUi.activity.title', 'Активность')
            ->where('preferences.timezone', 'Europe/Vilnius'));
})->with([
    'activity' => ['activity', 'activity/Index'],
    'notifications' => ['notifications.index', 'notifications/Index'],
    'calendar' => ['calendar', 'calendar/Index'],
    'projects' => ['projects', 'projects/Index'],
]);

test('unsupported preferences use the English workspace copy fallback', function () {
    [$user, $workspace] = createWarmPrecisionContext('de');

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('workspaceUi.calendar.title', 'Calendar')
            ->where('workspaceUi.projects.new_project', 'New project'));
});

test('calendar returns normalized dated tasks only from the selected workspace', function () {
    [$user, $workspace] = createWarmPrecisionContext();
    $project = Project::factory()->for($workspace)->create(['name' => 'Current project']);
    $todo = Todo::factory()->for($workspace)->for($project)->create([
        'title' => 'Current workspace deadline',
        'due_date' => now()->addDay(),
        'status' => TodoStatus::Pending,
        'priority' => TodoPriority::High,
    ]);

    $foreignWorkspace = Workspace::factory()->create();
    Todo::factory()->for($foreignWorkspace)->create([
        'title' => 'Foreign workspace deadline',
        'due_date' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('calendar/Index')
            ->where('calendar.view', 'month')
            ->has('todos', 1)
            ->where('todos.0.id', $todo->id)
            ->where('todos.0.due_date', now()->addDay()->toDateString())
            ->where('todos.0.project.name', 'Current project'));
});

test('calendar exposes normalized month state and only the visible grid range', function () {
    $this->travelTo(Carbon::parse('2026-08-16 10:00:00', 'Europe/Vilnius'));
    [$user, $workspace] = createWarmPrecisionContext();

    $rangeStart = Todo::factory()->for($workspace)->create([
        'title' => 'Range start',
        'due_date' => '2026-07-26',
    ]);
    $rangeEnd = Todo::factory()->for($workspace)->create([
        'title' => 'Range end',
        'due_date' => '2026-09-05',
    ]);
    Todo::factory()->for($workspace)->create([
        'title' => 'Before range',
        'due_date' => '2026-07-25',
    ]);
    Todo::factory()->for($workspace)->create([
        'title' => 'After range',
        'due_date' => '2026-09-06',
    ]);
    Todo::factory()->for(Workspace::factory()->create())->create([
        'title' => 'Foreign range task',
        'due_date' => '2026-08-16',
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => 'month', 'date' => '2026-08-16']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('calendar/Index')
            ->where('calendar.view', 'month')
            ->where('calendar.anchor_date', '2026-08-16')
            ->where('calendar.today_date', '2026-08-16')
            ->where('calendar.start_date', '2026-07-26')
            ->where('calendar.end_date', '2026-09-05')
            ->has('todos', 2)
            ->where('todos.0.id', $rangeStart->id)
            ->where('todos.1.id', $rangeEnd->id));
});

test('calendar exposes exact bounded week and agenda ranges', function (string $view, string $startDate, string $endDate) {
    [$user, $workspace] = createWarmPrecisionContext();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => $view, 'date' => '2026-08-19']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('calendar.view', $view)
            ->where('calendar.anchor_date', '2026-08-19')
            ->where('calendar.start_date', $startDate)
            ->where('calendar.end_date', $endDate));
})->with([
    'week' => ['week', '2026-08-16', '2026-08-22'],
    'agenda' => ['agenda', '2026-08-19', '2026-09-18'],
]);

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

test('calendar keeps the overdue preview workspace scoped bounded and ordered', function () {
    $this->travelTo(Carbon::parse('2026-08-16 10:00:00', 'Europe/Vilnius'));
    [$user, $workspace] = createWarmPrecisionContext();

    foreach (range(1, 8) as $day) {
        Todo::factory()->pending()->for($workspace)->create([
            'title' => "Overdue {$day}",
            'due_date' => "2026-08-0{$day}",
        ]);
    }

    Todo::factory()->completed()->for($workspace)->create([
        'title' => 'Completed overdue task',
        'due_date' => '2026-07-01',
    ]);
    Todo::factory()->pending()->for(Workspace::factory()->create())->create([
        'title' => 'Foreign overdue task',
        'due_date' => '2026-06-01',
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => 'week', 'date' => '2026-08-16']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('overdueCount', 8)
            ->has('overdueTodos', 6)
            ->where('overdueTodos.0.due_date', '2026-08-01')
            ->where('overdueTodos.5.due_date', '2026-08-06'));
});

test('calendar week boundaries honor the saved first day of week', function (string $weekStart, string $startDate, string $endDate) {
    [$user, $workspace] = createWarmPrecisionContext();
    $user->preferences()->update(['week_start' => $weekStart]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('calendar', ['view' => 'week', 'date' => '2026-08-16']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('calendar.week_start', $weekStart)
            ->where('calendar.start_date', $startDate)
            ->where('calendar.end_date', $endDate));
})->with([
    'Sunday first' => ['sunday', '2026-08-16', '2026-08-22'],
    'Monday first' => ['monday', '2026-08-10', '2026-08-16'],
]);

test('calendar rotates a local copy of weekday labels for Monday-first users', function () {
    $monthGrid = file_get_contents(resource_path('js/components/calendar/CalendarMonthGrid.vue'));

    expect($monthGrid)
        ->toContain('calendar.week_start')
        ->toContain('copy.value.calendar.weekdays')
        ->toContain('slice(1)')
        ->not->toContain('copy.value.calendar.weekdays.shift()');
});

test('project collection includes workspace scoped task totals', function () {
    [$user, $workspace] = createWarmPrecisionContext();
    $project = Project::factory()->for($workspace)->create(['name' => 'Launch']);
    Todo::factory()->count(2)->for($workspace)->for($project)->create();

    $foreignProject = Project::factory()->create(['name' => 'Foreign']);
    Todo::factory()->for($foreignProject->workspace)->for($foreignProject)->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('projects'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Index')
            ->where('workspace.id', $workspace->id)
            ->has('projects.data', 1)
            ->where('projects.data.0.id', $project->id)
            ->where('projects.data.0.todos_count', 2));
});

test('project collection selects and exposes only its page contract', function () {
    [$user, $workspace] = createWarmPrecisionContext();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id]);

    $emptyResponse = $this->get(route('projects'))->assertOk();

    Project::factory()->count(24)->for($workspace)->create([
        'name' => 'Measured project',
        'description' => 'Deterministic project payload for the production-shaped projection regression.',
        'color' => '#ff6038',
        'icon' => 'folder',
        'is_archived' => false,
        'position' => 10,
        'created_at' => Carbon::parse('2026-08-18 12:00:00'),
        'updated_at' => Carbon::parse('2026-08-18 12:00:00'),
    ]);

    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        $populatedResponse = $this->get(route('projects'))->assertOk();
        $queries = collect($connection->getQueryLog());
    } finally {
        $connection->disableQueryLog();
    }

    $projectQuery = $queries
        ->pluck('query')
        ->first(fn (string $query): bool => str_contains($query, 'as "todos_count"')
            && str_contains($query, 'where "projects"."workspace_id" = ?')
            && ! str_contains($query, '"projects"."is_archived" = ?'));

    $emptyPayloadBytes = strlen(json_encode($emptyResponse->inertiaProps('projects'), JSON_THROW_ON_ERROR));
    $productionPayloadBytes = strlen(json_encode($populatedResponse->inertiaProps('projects'), JSON_THROW_ON_ERROR));

    expect($projectQuery)->toBeString()
        ->and($projectQuery)->toContain('select "projects"."id", "projects"."workspace_id", "projects"."name", "projects"."description", "projects"."color", "projects"."icon", "projects"."is_archived", "projects"."updated_at"')
        ->not->toContain('"projects".*')
        ->and($queries)->toHaveCount(4)
        ->and($emptyPayloadBytes)->toBe(11)
        ->and($productionPayloadBytes)->toBeLessThanOrEqual(8_050);

    $populatedResponse->assertInertia(fn (Assert $page) => $page
        ->component('projects/Index')
        ->has('projects.data', 24)
        ->missing('projects.data.0.position')
        ->missing('projects.data.0.created_at'));
});

test('activity timeline excludes events from other workspaces', function () {
    [$user, $workspace] = createWarmPrecisionContext();
    $ownActivity = ActivityLog::create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
        'subject_type' => Todo::class,
        'subject_id' => Str::uuid()->toString(),
        'event' => 'created',
    ]);

    ActivityLog::create([
        'user_id' => $user->id,
        'workspace_id' => Workspace::factory()->create()->id,
        'subject_type' => Todo::class,
        'subject_id' => Str::uuid()->toString(),
        'event' => 'updated',
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('activity'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('activity/Index')
            ->has('activities.data', 1)
            ->where('activities.data.0.id', $ownActivity->id));
});

test('notification actions cannot mark another users notification as read', function () {
    [$user] = createWarmPrecisionContext();
    $foreignUser = User::factory()->create();
    $ownNotificationId = Str::uuid()->toString();
    $foreignNotificationId = Str::uuid()->toString();

    DB::table('notifications')->insert([
        [
            'id' => $ownNotificationId,
            'type' => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Own notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $foreignNotificationId,
            'type' => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $foreignUser->id,
            'data' => json_encode(['title' => 'Foreign notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->post(route('notifications.markRead', ['id' => $foreignNotificationId]))
        ->assertRedirect();

    expect(DB::table('notifications')->where('id', $foreignNotificationId)->value('read_at'))->toBeNull();

    $this->post(route('notifications.markRead', ['id' => $ownNotificationId]))
        ->assertRedirect();

    expect(DB::table('notifications')->where('id', $ownNotificationId)->value('read_at'))->not->toBeNull();

    $secondOwnNotificationId = Str::uuid()->toString();
    DB::table('notifications')->insert([
        'id' => $secondOwnNotificationId,
        'type' => 'App\\Notifications\\GenericNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['title' => 'Second own notification']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post(route('notifications.markAllRead'))->assertRedirect();

    expect(DB::table('notifications')->where('id', $secondOwnNotificationId)->value('read_at'))->not->toBeNull()
        ->and(DB::table('notifications')->where('id', $foreignNotificationId)->value('read_at'))->toBeNull();
});
