<?php

use App\Enums\ActivityCategory;
use App\Enums\ActivityEvent;
use App\Enums\WorkspaceRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\ActivityLog;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Queries\ActivityIndexQuery;
use Carbon\CarbonImmutable;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('activity'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the activity page', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('activity'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('activity/Index'));
});

test('activity page displays activity logs', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    $todo = Todo::factory()->create([
        'title' => 'Test Todo',
        'workspace_id' => $workspace->id,
        'assigned_to' => $user->id,
        'position' => 0,
    ]);

    ActivityLog::create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
        'subject_type' => Todo::class,
        'subject_id' => $todo->id,
        'event' => 'created',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('activity'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('activity/Index')
        ->has('activities.data', 1)
    );
});

test('activity page shows empty state when no logs exist', function () {
    $user = User::factory()->create();
    $workspace = Workspace::create([
        'name' => 'Test Workspace',
        'slug' => 'test-workspace',
        'owner_id' => $user->id,
    ]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('activity'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('activity/Index')
        ->has('activities.data', 0)
    );
});

test('activity page shows empty state when user has no workspace', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('activity'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('activity/Index')
        ->where('activities.data', [])
    );
});

test('activity taxonomy categorizes every domain event exactly once', function () {
    $categorizedEvents = collect(ActivityCategory::cases())
        ->flatMap(fn (ActivityCategory $category): array => $category->events())
        ->map(fn (ActivityEvent $event): string => $event->value);

    expect($categorizedEvents)
        ->toHaveCount(count(ActivityEvent::cases()))
        ->and($categorizedEvents->unique())
        ->toHaveCount(count(ActivityEvent::cases()))
        ->and($categorizedEvents->sort()->values()->all())
        ->toBe(collect(ActivityEvent::cases())->map->value->sort()->values()->all());
});

test('activity page filters the complete workspace ledger by category', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    ActivityLog::factory()->for($workspace)->for($user)->create(['event' => ActivityEvent::Created]);
    ActivityLog::factory()->for($workspace)->for($user)->create(['event' => ActivityEvent::Archived]);
    ActivityLog::factory()->for($workspace)->for($user)->create(['event' => ActivityEvent::Pinned]);

    $this->actingAs($user)
        ->get(route('activity.index', [$workspace, 'category' => 'organization']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('activity/Index')
            ->where('filters.category', 'organization')
            ->has('activities.data', 2)
            ->where('activities.data.0.event', fn (string $event): bool => in_array($event, ['archived', 'pinned'], true))
            ->where('activities.data.1.event', fn (string $event): bool => in_array($event, ['archived', 'pinned'], true))
        );
});

test('activity page rejects contributor identifiers outside the workspace', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $owner->id,
        'role' => WorkspaceRole::Owner,
    ]);

    $foreignUser = User::factory()->create();
    $foreignWorkspace = Workspace::factory()->create(['owner_id' => $foreignUser->id]);
    WorkspaceMember::create([
        'workspace_id' => $foreignWorkspace->id,
        'user_id' => $foreignUser->id,
        'role' => WorkspaceRole::Owner,
    ]);
    ActivityLog::factory()->for($foreignWorkspace)->for($foreignUser)->create();

    $url = route('activity.index', [$workspace, 'actor' => $foreignUser->id]);

    $this->actingAs($owner)
        ->from(route('activity.index', $workspace))
        ->get($url)
        ->assertRedirect(route('activity.index', $workspace))
        ->assertSessionHasErrors('actor');
});

test('activity page filters by a contributor inside the workspace', function () {
    $owner = User::factory()->create();
    $contributor = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);

    foreach ([[$owner, WorkspaceRole::Owner], [$contributor, WorkspaceRole::Member]] as [$member, $role]) {
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => $role,
        ]);
    }

    ActivityLog::factory()->for($workspace)->for($owner)->create();
    $contributorActivity = ActivityLog::factory()->for($workspace)->for($contributor)->create();

    $this->actingAs($owner)
        ->get(route('activity.index', [$workspace, 'actor' => $contributor->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.actor', $contributor->id)
            ->has('activities.data', 1)
            ->where('activities.data.0.id', $contributorActivity->id)
            ->where('activities.data.0.user.id', $contributor->id));
});

test('activity page filters by period and preserves filters across pagination', function () {
    CarbonImmutable::setTestNow('2026-08-16 12:00:00');

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    ActivityLog::factory()->count(21)->for($workspace)->for($user)->create([
        'event' => ActivityEvent::Created,
        'created_at' => now()->subDays(3),
    ]);
    ActivityLog::factory()->for($workspace)->for($user)->create([
        'event' => ActivityEvent::Created,
        'created_at' => now()->subDays(40),
    ]);

    $this->actingAs($user)
        ->get(route('activity.index', [$workspace, 'category' => 'creation', 'period' => '30d']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.category', 'creation')
            ->where('filters.period', '30d')
            ->has('activities.data', 20)
            ->where('activities.meta.total', 21)
            ->where('activities.links.next', fn (?string $url): bool => is_string($url)
                && str_contains($url, 'category=creation')
                && str_contains($url, 'period=30d'))
        );

    CarbonImmutable::setTestNow();
});

test('activity metrics describe the workspace rather than the current page', function () {
    CarbonImmutable::setTestNow('2026-08-16 12:00:00');

    $owner = User::factory()->create();
    $contributor = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);

    foreach ([[$owner, WorkspaceRole::Owner], [$contributor, WorkspaceRole::Member]] as [$member, $role]) {
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => $role,
        ]);
    }

    ActivityLog::factory()->count(20)->for($workspace)->for($owner)->create([
        'created_at' => now()->subDays(2),
    ]);
    ActivityLog::factory()->count(5)->for($workspace)->for($contributor)->create([
        'created_at' => now()->subDays(10),
    ]);

    $this->actingAs($owner)
        ->get(route('activity.index', $workspace))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('activities.data', 20)
            ->where('metrics.recorded_actions', 25)
            ->where('metrics.contributors', 2)
            ->where('metrics.recent_changes', 20)
            ->has('contributors', 2)
        );

    CarbonImmutable::setTestNow();
});

test('activity partial reloads omit stable aggregate and contributor props', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::factory()->for($workspace)->for($user)->create([
        'role' => WorkspaceRole::Owner,
    ]);
    ActivityLog::factory()->for($workspace)->for($user)->create();

    $this->actingAs($user);

    $headers = [
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'activity/Index',
        'X-Inertia-Partial-Data' => 'activities,filters',
    ];
    $version = app(HandleInertiaRequests::class)->version(request());

    if (is_string($version)) {
        $headers['X-Inertia-Version'] = $version;
    }

    $this->get(route('activity.index', $workspace), $headers)
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'activity/Index')
        ->assertJsonStructure(['props' => ['activities', 'filters']])
        ->assertJsonMissingPath('props.metrics')
        ->assertJsonMissingPath('props.contributors');
});

test('activity contributor options stay bounded for large workspaces', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);
    WorkspaceMember::factory()->for($workspace)->for($owner)->create([
        'role' => WorkspaceRole::Owner,
    ]);

    User::factory()->count(ActivityIndexQuery::CONTRIBUTOR_LIMIT + 5)->create()
        ->each(fn (User $member) => WorkspaceMember::factory()
            ->for($workspace)
            ->for($member)
            ->create());

    $contributors = app(ActivityIndexQuery::class)->contributorsForWorkspace($workspace);

    expect($contributors)->toHaveCount(ActivityIndexQuery::CONTRIBUTOR_LIMIT);
});

test('activity serialization exposes safe labels without raw change values', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);

    ActivityLog::factory()->for($workspace)->for($user)->create([
        'event' => ActivityEvent::Updated,
        'subject_type' => Todo::class,
        'properties' => [
            'title' => 'Safe activity label',
            'field' => 'title',
            'old' => 'private-before',
            'new' => 'private-after',
            'token' => 'never-send-this',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('activity.index', $workspace))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('activities.data.0.subject_label', 'Safe activity label')
            ->where('activities.data.0.changed_field', 'title')
            ->where('activities.data.0.user.name', $user->name)
            ->missing('activities.data.0.properties')
            ->missing('activities.data.0.user_id')
            ->missing('activities.data.0.workspace_id')
            ->missing('activities.data.0.user.email')
        );
});
