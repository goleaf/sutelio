<?php

use App\Enums\WorkspaceRole;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function todoCrudWorkspace(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();
    WorkspaceMember::factory()->for($workspace)->for($user)->create([
        'role' => WorkspaceRole::Owner,
    ]);

    return [$user, $workspace];
}

test('created todo persists with priority and due date and appears on a fresh list request', function () {
    [$user, $workspace] = todoCrudWorkspace();

    $this->actingAs($user)
        ->postJson(route('todos.store', $workspace), [
            'title' => 'Ship the release',
            'priority' => 'high',
            'due_date' => '2026-08-20',
        ])
        ->assertCreated();

    $todo = Todo::query()->where('workspace_id', $workspace->id)->sole();

    expect($todo->title)->toBe('Ship the release')
        ->and($todo->priorityKey())->toBe('high')
        ->and($todo->due_date?->toDateString())->toBe('2026-08-20')
        ->and($todo->completed_at)->toBeNull();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tasks/Index')
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $todo->id)
            ->where('todos.data.0.title', 'Ship the release')
            ->where('todos.data.0.priority', 'high')
            ->where('todos.data.0.due_date', '2026-08-20')
            ->where('todos.data.0.is_completed', false));
});

test('updated todo fields and completion persist on a fresh list request', function () {
    [$user, $workspace] = todoCrudWorkspace();
    $todo = Todo::factory()->for($workspace)->pending()->create([
        'title' => 'Draft release notes',
        'priority' => 'low',
        'due_date' => '2026-08-18',
    ]);

    $this->actingAs($user)
        ->putJson(route('todos.update', $todo), [
            'title' => 'Publish release notes',
            'priority' => 'urgent',
            'due_date' => '2026-08-22',
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->postJson(route('todos.complete', $todo))
        ->assertRedirect();

    $todo->refresh();

    expect($todo->title)->toBe('Publish release notes')
        ->and($todo->priorityKey())->toBe('urgent')
        ->and($todo->due_date?->toDateString())->toBe('2026-08-22')
        ->and($todo->completed_at)->not->toBeNull();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('todos.data', 1)
            ->where('todos.data.0.id', $todo->id)
            ->where('todos.data.0.title', 'Publish release notes')
            ->where('todos.data.0.priority', 'urgent')
            ->where('todos.data.0.due_date', '2026-08-22')
            ->where('todos.data.0.is_completed', true));
});

test('deleted todo stays absent from a fresh list request', function () {
    [$user, $workspace] = todoCrudWorkspace();
    $todo = Todo::factory()->for($workspace)->create();

    $this->actingAs($user)
        ->deleteJson(route('todos.destroy', $todo))
        ->assertRedirect();

    $this->assertSoftDeleted($todo);

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('todos.data', 0)
            ->where('stats.total', 0));
});

test('user cannot view update complete or delete another users private todo', function () {
    [$owner, $workspace] = todoCrudWorkspace();
    $todo = Todo::factory()->for($workspace)->pending()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('todos.show', $todo))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->putJson(route('todos.update', $todo), ['title' => 'Stolen task'])
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->postJson(route('todos.complete', $todo))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->deleteJson(route('todos.destroy', $todo))
        ->assertForbidden();

    expect($todo->refresh()->title)->not->toBe('Stolen task')
        ->and($todo->completed_at)->toBeNull()
        ->and($todo->deleted_at)->toBeNull();

    expect($owner->id)->not->toBe($otherUser->id);
});
