<?php

use App\Actions\BulkUpdateTodos;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

function taskIndexWorkspace(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();
    WorkspaceMember::factory()->for($workspace)->for($user)->create(['role' => WorkspaceRole::Owner]);

    return [$user, $workspace];
}

test('task index validates and returns the complete URL backed state', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $project = Project::factory()->for($workspace)->create();
    Todo::factory()->count(30)->for($workspace)->for($project)->pending()->create([
        'title' => 'Release task',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', [
            'search' => 'Release',
            'project_id' => $project->id,
            'sort' => 'title',
            'direction' => 'desc',
            'per_page' => 25,
            'view' => 'board',
        ]));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('tasks/Index')
        ->where('filters.search', 'Release')
        ->where('filters.project_id', $project->id)
        ->where('filters.sort', 'title')
        ->where('filters.direction', 'desc')
        ->where('filters.per_page', 25)
        ->where('filters.view', 'board')
        ->where('stats.total', 30)
        ->where('todos.meta.total', 30)
        ->where('todos.meta.from', 1)
        ->where('todos.meta.to', 25)
        ->has('todos.data', 25));

    expect($response->getContent())->toContain(
        'search=Release',
        'project_id='.$project->id,
        'sort=title',
        'direction=desc',
        'per_page=25',
        'view=board',
    );

    $this->actingAs($user)
        ->from(route('todos.index'))
        ->get(route('todos.index', ['sort' => 'invalid', 'per_page' => 500, 'view' => 'grid']))
        ->assertRedirect(route('todos.index'))
        ->assertSessionHasErrors(['sort', 'per_page', 'view']);
});

test('task index metrics represent all filtered tasks instead of only the current page', function () {
    [$user, $workspace] = taskIndexWorkspace();
    Todo::factory()->count(30)->for($workspace)->pending()->create();
    Todo::factory()->count(20)->for($workspace)->completed()->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', ['per_page' => 25]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 50)
            ->where('stats.pending', 30)
            ->where('stats.completed', 20)
            ->has('todos.data', 25));
});

test('task focus filters are normalized and remain scoped to the current workspace', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $matching = Todo::factory()->for($workspace)->overdue()->create([
        'priority' => 'high',
        'is_pinned' => true,
        'is_favorite' => true,
        'title' => 'Focused task',
    ]);
    Todo::factory()->for($workspace)->overdue()->create([
        'priority' => 'low',
        'is_pinned' => true,
        'is_favorite' => true,
    ]);

    $foreignUser = User::factory()->create();
    $foreignWorkspace = Workspace::factory()->for($foreignUser, 'owner')->create();
    Todo::factory()->for($foreignWorkspace)->overdue()->create([
        'priority' => 'high',
        'is_pinned' => true,
        'is_favorite' => true,
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', [
            'overdue' => '1',
            'is_pinned' => '1',
            'is_favorite' => '1',
            'priority' => 'high',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.overdue', true)
            ->where('filters.is_pinned', true)
            ->where('filters.is_favorite', true)
            ->where('filters.priority', 'high')
            ->where('stats.total', 1)
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $matching->id));
});

test('completed today focus returns only tasks completed on the current day', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $completedToday = Todo::factory()->for($workspace)->completed()->create([
        'completed_at' => now(),
    ]);
    Todo::factory()->for($workspace)->completed()->create([
        'completed_at' => now()->subDay(),
    ]);
    Todo::factory()->for($workspace)->pending()->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', ['completed_today' => '1']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.completed_today', true)
            ->where('stats.total', 1)
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $completedToday->id));
});

test('task focus accepts Inertia boolean query serialization', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $matching = Todo::factory()->for($workspace)->overdue()->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', ['overdue' => 'true']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.overdue', true)
            ->where('stats.total', 1)
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $matching->id));
});

test('completed today follows the authenticated user timezone across UTC midnight', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $user->preferences()->create(['timezone' => 'Europe/Vilnius']);
    $this->travelTo(CarbonImmutable::parse('2026-08-16 21:30:00', 'UTC'));

    $matching = Todo::factory()->for($workspace)->completed()->create([
        'completed_at' => CarbonImmutable::parse('2026-08-16 21:15:00', 'UTC'),
    ]);
    Todo::factory()->for($workspace)->completed()->create([
        'completed_at' => CarbonImmutable::parse('2026-08-16 20:30:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', ['completed_today' => 'true']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.completed_today', true)
            ->where('stats.total', 1)
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $matching->id));
});

test('false task focus flags are omitted from canonical filter state', function () {
    [$user, $workspace] = taskIndexWorkspace();
    Todo::factory()->for($workspace)->pending()->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', [
            'overdue' => '0',
            'completed_today' => '0',
            'is_pinned' => '0',
            'is_favorite' => '0',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('filters.overdue')
            ->missing('filters.completed_today')
            ->missing('filters.is_pinned')
            ->missing('filters.is_favorite')
            ->where('stats.total', 1));
});

test('task focus flags reject invalid boolean query values', function (string $filter) {
    [$user, $workspace] = taskIndexWorkspace();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->from(route('todos.index'))
        ->get(route('todos.index', [$filter => 'sometimes']))
        ->assertRedirect(route('todos.index'))
        ->assertSessionHasErrors($filter);
})->with(['overdue', 'completed_today', 'is_pinned', 'is_favorite']);

test('task index uses the saved board preference unless the URL overrides it', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $user->preferences()->create(['default_view' => 'board']);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('filters.view', 'board'));

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index', ['view' => 'list']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('filters.view', 'list'));
});

test('bulk actions reject stale or duplicate sets before mutating any task', function () {
    [$user, $workspace] = taskIndexWorkspace();
    $valid = Todo::factory()->for($workspace)->create(['is_archived' => false]);
    $deleted = Todo::factory()->for($workspace)->create(['is_archived' => false]);
    $deleted->delete();

    $this->actingAs($user)
        ->post(route('todos.bulk', $workspace), [
            'ids' => [$valid->id, $deleted->id],
            'action' => 'archive',
        ])
        ->assertSessionHasErrors('ids.1');

    expect($valid->refresh()->is_archived)->toBeFalse();

    expect(fn () => app(BulkUpdateTodos::class)->setArchived(
        $workspace,
        [$valid->id, $deleted->id],
        true,
    ))->toThrow(ValidationException::class);

    expect($valid->refresh()->is_archived)->toBeFalse();

    $this->actingAs($user)
        ->post(route('todos.bulk', $workspace), [
            'ids' => [$valid->id, $valid->id],
            'action' => 'delete',
        ])
        ->assertSessionHasErrors('ids.1');

    expect($valid->fresh())->not->toBeNull();
});

test('task index coordinates the reusable workflow components', function () {
    $page = File::get(resource_path('js/pages/tasks/Index.vue'));

    expect(substr_count($page, "\n"))->toBeLessThan(450)
        ->and($page)->toContain(
            '@/components/task/TaskWorkspacePanel.vue',
            'only: [\'todos\', \'filters\', \'stats\']',
        )
        ->and(File::get(resource_path('js/components/task/TaskWorkspacePanel.vue')))->toContain(
            '@/components/task/BoardView.vue',
            '@/components/task/BulkActions.vue',
            '@/components/task/TaskFilterBar.vue',
            '@/components/task/TaskList.vue',
            '@/components/task/TaskPagination.vue',
            '@/components/task/TaskResultsBar.vue',
        )
        ->and(File::get(resource_path('js/components/task/BoardView.vue')))->toContain(
            'taskDefinitions.statuses',
            '@keydown',
            'overflow-x-auto',
        )
        ->and(File::get(resource_path('js/components/task/BulkActions.vue')))->toContain(
            ':disabled="processing"',
            'aria-live="polite"',
        );
});
