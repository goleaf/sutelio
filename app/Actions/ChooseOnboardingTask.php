<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChooseOnboardingTask
{
    public function __construct(
        private CreateTodo $createTodo,
        private RunOnboardingCreation $runOnboardingCreation,
        private AdvanceOnboarding $advanceOnboarding,
    ) {}

    /** @param array{title: string, project_id: string, assigned_to: string|null, description: string|null, status_id: string, priority_id: string, due_date: string|null} $taskData */
    public function handle(
        User $user,
        UserPreference $preferences,
        string $mode,
        ?string $taskId,
        array $taskData,
        string $requestKey,
    ): Todo {
        [$workspace, $project] = $this->context($user, $preferences);

        if ($mode === 'create') {
            Gate::forUser($user)->authorize('create', [Todo::class, $workspace]);
            $this->validateAssignee($workspace, $taskData['assigned_to']);
            $taskData['project_id'] = $project->id;
            $taskId = $this->runOnboardingCreation->handle(
                $preferences,
                OnboardingStep::Task,
                $requestKey,
                fn (): string => $this->createTodo->handle($workspace, $taskData, $user->id)->id,
            );
        }

        $task = is_string($taskId)
            ? $workspace->todos()
                ->active()
                ->where('project_id', $project->id)
                ->whereKey($taskId)
                ->first()
            : null;

        if (! $task instanceof Todo) {
            throw ValidationException::withMessages([
                'task_id' => __('onboarding.errors.task_unavailable'),
            ]);
        }

        $preferences->refresh()->forceFill([
            'onboarding_step' => OnboardingStep::Task->value,
            'onboarding_state' => [
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
                'task_id' => $task->id,
            ],
        ])->save();

        $this->advanceOnboarding->handle($preferences->fresh(), OnboardingStep::ProductMap);

        return $task;
    }

    /** @return array{0: Workspace, 1: Project} */
    private function context(User $user, UserPreference $preferences): array
    {
        $state = $preferences->onboardingState();
        $workspaceId = $state['workspace_id'] ?? null;
        $workspace = is_string($workspaceId)
            ? $user->workspaces()->whereKey($workspaceId)->first()
            : null;

        if (! $workspace instanceof Workspace) {
            throw ValidationException::withMessages([
                'workspace_id' => __('onboarding.errors.workspace_unavailable'),
            ]);
        }

        $projectId = $state['project_id'] ?? null;
        $project = is_string($projectId)
            ? $workspace->projects()->active()->whereKey($projectId)->first()
            : null;

        if (! $project instanceof Project) {
            throw ValidationException::withMessages([
                'project_id' => __('onboarding.errors.project_unavailable'),
            ]);
        }

        return [$workspace, $project];
    }

    private function validateAssignee(Workspace $workspace, ?string $assigneeId): void
    {
        if ($assigneeId === null) {
            return;
        }

        if (! $workspace->members()->whereKey($assigneeId)->exists()) {
            throw ValidationException::withMessages([
                'assigned_to' => __('validation.exists', ['attribute' => __('validation.attributes.assignee')]),
            ]);
        }
    }
}
