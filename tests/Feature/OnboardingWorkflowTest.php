<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

/** @return array{0: User, 1: UserPreference} */
function createPendingOnboardingUser(array $preferenceAttributes = []): array
{
    $user = User::factory()->create();
    $preferences = UserPreference::factory()
        ->for($user)
        ->pendingOnboarding()
        ->create($preferenceAttributes);

    return [$user, $preferences];
}

test('progress moves only between adjacent steps and resumes after reload', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Preferences->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('onboarding.index');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Preferences)
        ->and($preferences->onboarding_started_at)->not->toBeNull();

    $this->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.step', OnboardingStep::Preferences->value)
            ->where('progress.position', 2));

    $this->patch(route('onboarding.progress'), [
        'target_step' => OnboardingStep::Welcome->value,
    ])->assertSessionHasNoErrors();

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome);
});

test('progress rejects jumps and unknown step values without moving the cursor', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->from(route('onboarding.index'))
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Task->value,
        ])
        ->assertRedirect(route('onboarding.index'))
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->fresh()->onboarding_started_at)->toBeNull();

    $this->from(route('onboarding.index'))
        ->patch(route('onboarding.progress'), ['target_step' => 'unknown'])
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome);
});

test('required onboarding can be skipped without deleting its prior facts', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'start_page' => 'calendar',
        'onboarding_step' => OnboardingStep::Project->value,
        'onboarding_state' => ['workspace_id' => (string) Str::uuid()],
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.skip'))
        ->assertRedirectToRoute('calendar')
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Results)
        ->and($preferences->onboarding_state)->toBe([])
        ->and($preferences->onboarding_skipped_at)->not->toBeNull()
        ->and($preferences->onboarding_completed_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();
});

test('completion is accepted only from results and follows the selected start page', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'start_page' => 'tasks',
        'onboarding_step' => OnboardingStep::Safety->value,
    ]);

    $this->actingAs($user)
        ->from(route('onboarding.index'))
        ->post(route('onboarding.complete'))
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboarding_completed_at)->toBeNull();

    $preferences->update(['onboarding_step' => OnboardingStep::Results->value]);

    $this->post(route('onboarding.complete'))
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('onboarding_replay')
        ->assertRedirectToRoute('todos.index');

    $preferences->refresh();

    expect($preferences->onboarding_completed_at)->not->toBeNull()
        ->and($preferences->onboarding_skipped_at)->toBeNull()
        ->and($preferences->onboarding_state)->toBe([])
        ->and($preferences->requiresOnboarding())->toBeFalse();
});

test('completed users can replay without reopening their required gate', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'start_page' => 'projects',
    ]);
    $completion = $preferences->onboarding_completed_at;
    $previousRunId = $preferences->onboarding_run_id;

    $this->actingAs($user)
        ->post(route('onboarding.restart'))
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('onboarding_replay', true);

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_run_id)->not->toBe($previousRunId)
        ->and($preferences->onboarding_version)->toBe(UserPreference::ONBOARDING_VERSION)
        ->and($preferences->onboarding_started_at)->not->toBeNull()
        ->and($preferences->onboarding_completed_at?->equalTo($completion))->toBeTrue()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();

    $this->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.is_replay', true)
            ->where('progress.step', OnboardingStep::Welcome->value));
});

test('skipping a replay ends only the replay session and preserves completion facts', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'start_page' => 'projects',
    ]);
    $completion = $preferences->onboarding_completed_at;

    $this->actingAs($user)->post(route('onboarding.restart'));

    $this->post(route('onboarding.skip'))
        ->assertRedirectToRoute('projects')
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_completed_at?->equalTo($completion))->toBeTrue()
        ->and($preferences->onboarding_skipped_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('onboarding.index'))->assertRedirectToRoute('projects');
});

test('skipping a replay preserves an earlier skipped lifecycle fact', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)->post(route('onboarding.skip'));
    $skippedAt = $preferences->fresh()->onboarding_skipped_at;

    $this->post(route('onboarding.restart'))
        ->assertSessionHas('onboarding_replay', true);
    $this->post(route('onboarding.skip'))
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboarding_skipped_at?->equalTo($skippedAt))->toBeTrue()
        ->and($preferences->onboarding_completed_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();
});

test('completed users cannot mutate onboarding outside an active replay', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Safety->value,
        ])
        ->assertForbidden();

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Results);
});

test('restarting onboarding creates a completed baseline for legacy users without preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.restart'))
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('onboarding_replay', true);

    $preferences = $user->preferences()->firstOrFail();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_completed_at)->not->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();
});

test('a future content version never silently reopens completed onboarding', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'onboarding_version' => 0,
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    expect($preferences->fresh()->onboarding_version)->toBe(0);

    $this->post(route('onboarding.restart'))
        ->assertSessionHas('onboarding_replay', true);

    expect($preferences->fresh()->onboarding_version)
        ->toBe(UserPreference::ONBOARDING_VERSION);
});

test('a genuinely pending user cannot bypass the gate with a replay session flag', function () {
    [$user] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->withSession(['onboarding_replay' => true])
        ->get(route('dashboard'))
        ->assertRedirectToRoute('onboarding.index');
});

test('the continuation checklist is hidden for legacy users and shown after a new completion', function () {
    $legacyUser = User::factory()->create();
    UserPreference::factory()->for($legacyUser)->create();

    $this->actingAs($legacyUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.show', false));

    $newUser = User::factory()->create();
    UserPreference::factory()->for($newUser)->create([
        'onboarding_completed_at' => now(),
        'onboarding_checklist_dismissed_at' => null,
    ]);

    $this->actingAs($newUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.show', true));
});

test('the continuation checklist uses only current workspace capabilities and real completion facts', function () {
    $user = User::factory()->withTwoFactor()->create();
    UserPreference::factory()->for($user)->create([
        'onboarding_completed_at' => null,
        'onboarding_skipped_at' => now(),
        'onboarding_checklist_dismissed_at' => null,
    ]);
    $currentWorkspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $otherWorkspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    WorkspaceMember::factory()->for($otherWorkspace)->create();

    $this->actingAs($user)
        ->withSession(['current_workspace_id' => $currentWorkspace->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.show', true)
            ->where('onboardingChecklist.workspace_id', $currentWorkspace->id)
            ->where('onboardingChecklist.can_invite', true)
            ->where('onboardingChecklist.has_team_member', false)
            ->where('onboardingChecklist.has_security_factor', true)
            ->where('onboardingChecklist.can_manage_backups', false));

    WorkspaceMember::factory()->for($currentWorkspace)->create();

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.has_team_member', true));
});

test('the continuation checklist withholds workspace management actions from members', function () {
    $member = User::factory()->create();
    UserPreference::factory()->for($member)->create([
        'onboarding_checklist_dismissed_at' => null,
    ]);
    $workspace = Workspace::factory()->withOwnerMembership()->create();
    WorkspaceMember::factory()->for($workspace)->for($member)->member()->create();

    $this->actingAs($member)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.workspace_id', $workspace->id)
            ->where('onboardingChecklist.can_invite', false)
            ->where('onboardingChecklist.can_manage_backups', false));
});

test('a passkey is counted as a real security factor', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create([
        'onboarding_checklist_dismissed_at' => null,
    ]);
    $user->passkeys()->create([
        'name' => 'Phone',
        'credential_id' => (string) Str::uuid(),
        'credential' => [],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.has_security_factor', true));
});

test('dismissing the continuation checklist updates only the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'onboarding_checklist_dismissed_at' => null,
    ]);
    $otherPreferences = UserPreference::factory()->for($otherUser)->create([
        'onboarding_checklist_dismissed_at' => null,
    ]);

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->delete(route('onboarding.checklist.dismiss'))
        ->assertRedirect(route('dashboard'));

    expect($preferences->fresh()->onboarding_checklist_dismissed_at)->not->toBeNull()
        ->and($otherPreferences->fresh()->onboarding_checklist_dismissed_at)->toBeNull();

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('onboardingChecklist.show', false));
});

test('settings replay preserves domain entities and prior completion facts', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create();
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $project = Project::factory()->for($workspace)->create();
    $task = Todo::factory()->for($workspace)->for($project)->create();
    $completion = $preferences->onboarding_completed_at;

    $this->actingAs($user)
        ->get(route('preferences.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('canReplayOnboarding', true));

    $this->post(route('onboarding.restart'))
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('onboarding_replay', true);

    expect($workspace->fresh())->not->toBeNull()
        ->and($project->fresh())->not->toBeNull()
        ->and($task->fresh())->not->toBeNull()
        ->and($preferences->fresh()->onboarding_completed_at?->equalTo($completion))->toBeTrue();
});

test('onboarding preferences save canonical values and advance to workspace setup', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Preferences->value,
    ]);

    $this->actingAs($user)
        ->put(route('onboarding.preferences'), [
            'language' => 'lt',
            'timezone' => 'Europe/Vilnius',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'default_view' => 'board',
            'start_page' => 'tasks',
            'week_start' => 'monday',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('locale', 'lt')
        ->assertRedirectToRoute('onboarding.index');

    $preferences->refresh();

    expect($preferences->language)->toBe('lt')
        ->and($preferences->timezone)->toBe('Europe/Vilnius')
        ->and($preferences->date_format)->toBe('d.m.Y')
        ->and($preferences->default_view)->toBe('board')
        ->and($preferences->start_page)->toBe('tasks')
        ->and($preferences->week_start)->toBe('monday')
        ->and($preferences->onboardingStep())->toBe(OnboardingStep::Workspace);
});

test('an invited member can select an accessible workspace without creating another', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Workspace->value,
    ]);
    $owner = User::factory()->create();
    $workspace = Workspace::factory()->for($owner, 'owner')->withOwnerMembership()->create();
    WorkspaceMember::factory()->for($workspace)->for($user)->create([
        'role' => WorkspaceRole::Member,
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.workspace'), [
            'mode' => 'select',
            'workspace_id' => $workspace->id,
            'request_key' => (string) Str::uuid(),
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('current_workspace_id', $workspace->id)
        ->assertRedirectToRoute('onboarding.index');

    $preferences->refresh();

    expect($user->workspaces()->count())->toBe(1)
        ->and($preferences->onboarding_state)->toMatchArray([
            'workspace_id' => $workspace->id,
        ])
        ->and($preferences->onboardingStep())->toBe(OnboardingStep::Project);
});

test('workspace creation is exactly once per onboarding run and rejects foreign selection', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Workspace->value,
    ]);
    $foreign = Workspace::factory()->withOwnerMembership()->create();
    $payload = [
        'mode' => 'create',
        'name' => 'My first workspace',
        'description' => 'A focused place to begin.',
        'request_key' => (string) Str::uuid(),
    ];

    $this->actingAs($user)->post(route('onboarding.workspace'), $payload)->assertRedirect();
    $this->post(route('onboarding.workspace'), $payload)->assertRedirect();

    expect($user->workspaces()->where('name', 'My first workspace')->count())->toBe(1)
        ->and(DB::table('onboarding_operations')->where('step', 'workspace')->count())->toBe(1);

    $selectedWorkspaceId = $preferences->fresh()->onboarding_state['workspace_id'] ?? null;

    expect($selectedWorkspaceId)->toBeString()
        ->and(session('current_workspace_id'))->toBe($selectedWorkspaceId);

    $this->from(route('onboarding.index'))
        ->post(route('onboarding.workspace'), [
            'mode' => 'select',
            'workspace_id' => $foreign->id,
            'request_key' => (string) Str::uuid(),
        ])
        ->assertSessionHasErrors('workspace_id');

    expect($preferences->fresh()->onboarding_state['workspace_id'] ?? null)
        ->toBe($selectedWorkspaceId);
});

test('project selection is scoped to the chosen workspace and excludes archived projects', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Project->value,
    ]);
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $project = Project::factory()->for($workspace)->active()->create();
    $archived = Project::factory()->for($workspace)->archived()->create();
    $foreignProject = Project::factory()->create();
    $preferences->update(['onboarding_state' => ['workspace_id' => $workspace->id]]);

    $this->actingAs($user)
        ->post(route('onboarding.project'), [
            'mode' => 'select',
            'project_id' => $project->id,
            'request_key' => (string) Str::uuid(),
        ])
        ->assertSessionHasNoErrors();

    expect($preferences->fresh()->onboarding_state)->toMatchArray([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ])->and($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Task);

    foreach ([$archived, $foreignProject] as $invalidProject) {
        $this->from(route('onboarding.index'))
            ->post(route('onboarding.project'), [
                'mode' => 'select',
                'project_id' => $invalidProject->id,
                'request_key' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors('project_id');
    }

    expect($preferences->fresh()->onboarding_state['project_id'] ?? null)->toBe($project->id);
});

test('project creation is exactly once per run and a new replay may create another project', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Project->value,
    ]);
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $preferences->update(['onboarding_state' => ['workspace_id' => $workspace->id]]);
    $payload = [
        'mode' => 'create',
        'name' => 'Launch',
        'description' => 'Prepare the first launch.',
        'color' => '#f97316',
        'icon' => 'rocket',
        'request_key' => (string) Str::uuid(),
    ];

    $this->actingAs($user)->post(route('onboarding.project'), $payload)->assertRedirect();
    $this->post(route('onboarding.project'), [
        ...$payload,
        'request_key' => (string) Str::uuid(),
    ])->assertRedirect();

    expect($workspace->projects()->where('name', 'Launch')->count())->toBe(1)
        ->and(DB::table('onboarding_operations')->where('step', 'project')->count())->toBe(1);

    $preferences->update([
        'onboarding_run_id' => (string) Str::uuid(),
        'onboarding_step' => OnboardingStep::Project->value,
        'onboarding_state' => ['workspace_id' => $workspace->id],
    ]);

    $this->post(route('onboarding.project'), [
        ...$payload,
        'request_key' => (string) Str::uuid(),
    ])->assertRedirect();

    expect($workspace->projects()->where('name', 'Launch')->count())->toBe(2)
        ->and(DB::table('onboarding_operations')->where('step', 'project')->count())->toBe(2);
});

test('task selection is limited to an active task in the selected project', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Task->value,
    ]);
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $project = Project::factory()->for($workspace)->create();
    $otherProject = Project::factory()->for($workspace)->create();
    $task = Todo::factory()->for($workspace)->for($project)->create();
    $archived = Todo::factory()->for($workspace)->for($project)->archived()->create();
    $mixedProjectTask = Todo::factory()->for($workspace)->for($otherProject)->create();
    $foreignTask = Todo::factory()->create();
    $preferences->update(['onboarding_state' => [
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ]]);

    $this->actingAs($user)
        ->post(route('onboarding.task'), [
            'mode' => 'select',
            'task_id' => $task->id,
            'request_key' => (string) Str::uuid(),
        ])
        ->assertSessionHasNoErrors();

    expect($preferences->fresh()->onboarding_state)->toMatchArray([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'task_id' => $task->id,
    ])->and($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::ProductMap);

    foreach ([$archived, $mixedProjectTask, $foreignTask] as $invalidTask) {
        $this->from(route('onboarding.index'))
            ->post(route('onboarding.task'), [
                'mode' => 'select',
                'task_id' => $invalidTask->id,
                'request_key' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors('task_id');
    }

    expect($preferences->fresh()->onboarding_state['task_id'] ?? null)->toBe($task->id);
});

test('task creation is exactly once and validates scoped definitions and assignee', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Task->value,
    ]);
    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $project = Project::factory()->for($workspace)->create();
    $member = User::factory()->create();
    WorkspaceMember::factory()->for($workspace)->for($member)->create();
    $status = $workspace->taskStatuses()->active()->where('is_default', true)->firstOrFail();
    $priority = $workspace->taskPriorities()->active()->where('is_default', true)->firstOrFail();
    $foreignWorkspace = Workspace::factory()->withOwnerMembership()->create();
    $foreignStatus = $foreignWorkspace->taskStatuses()->active()->firstOrFail();
    $foreignPriority = $foreignWorkspace->taskPriorities()->active()->firstOrFail();
    $foreignUser = User::factory()->create();
    $preferences->update(['onboarding_state' => [
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ]]);
    $payload = [
        'mode' => 'create',
        'title' => 'Ship the first milestone',
        'description' => 'A concrete first task.',
        'status_id' => $status->id,
        'priority_id' => $priority->id,
        'assigned_to' => $member->id,
        'due_date' => '2026-08-20',
        'request_key' => (string) Str::uuid(),
    ];

    $this->actingAs($user)->post(route('onboarding.task'), $payload)->assertRedirect();
    $this->post(route('onboarding.task'), [
        ...$payload,
        'request_key' => (string) Str::uuid(),
    ])->assertRedirect();

    $task = $workspace->todos()->where('title', 'Ship the first milestone')->sole();

    expect($task->project_id)->toBe($project->id)
        ->and($task->assigned_to)->toBe($member->id)
        ->and($task->status_id)->toBe($status->id)
        ->and($task->priority_id)->toBe($priority->id)
        ->and(DB::table('onboarding_operations')->where('step', 'task')->count())->toBe(1);

    foreach ([
        ['status_id' => $foreignStatus->id],
        ['priority_id' => $foreignPriority->id],
        ['assigned_to' => $foreignUser->id],
    ] as $invalid) {
        $this->from(route('onboarding.index'))
            ->post(route('onboarding.task'), [
                ...$payload,
                ...$invalid,
                'request_key' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors(array_key_first($invalid));
    }

    expect($workspace->todos()->where('title', 'Ship the first milestone')->count())->toBe(1);
});

test('stale saved selections recover to the nearest safe onboarding step', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'onboarding_step' => OnboardingStep::Safety->value,
        'onboarding_state' => [
            'workspace_id' => (string) Str::uuid(),
            'project_id' => (string) Str::uuid(),
            'task_id' => (string) Str::uuid(),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.step', OnboardingStep::Workspace->value)
            ->where('recovery', 'workspace_unavailable')
            ->where('state', []));

    $workspace = Workspace::factory()->for($user, 'owner')->withOwnerMembership()->create();
    $archivedProject = Project::factory()->for($workspace)->archived()->create();
    $preferences->update([
        'onboarding_step' => OnboardingStep::Safety->value,
        'onboarding_state' => [
            'workspace_id' => $workspace->id,
            'project_id' => $archivedProject->id,
        ],
    ]);

    $this->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.step', OnboardingStep::Project->value)
            ->where('recovery', 'project_unavailable')
            ->where('state.workspace_id', $workspace->id)
            ->missing('state.project_id'));
});
