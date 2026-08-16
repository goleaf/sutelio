<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\Project;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChooseOnboardingProject
{
    public function __construct(
        private CreateProject $createProject,
        private RunOnboardingCreation $runOnboardingCreation,
        private AdvanceOnboarding $advanceOnboarding,
    ) {}

    /** @param array{name: string, description?: string|null, color?: string, icon?: string} $projectData */
    public function handle(
        User $user,
        UserPreference $preferences,
        string $mode,
        ?string $projectId,
        array $projectData,
        string $requestKey,
    ): Project {
        $workspace = $this->workspace($user, $preferences);

        if ($mode === 'create') {
            Gate::forUser($user)->authorize('create', [Project::class, $workspace]);
            $projectId = $this->runOnboardingCreation->handle(
                $preferences,
                OnboardingStep::Project,
                $requestKey,
                fn (): string => $this->createProject->handle($workspace, $projectData)->id,
            );
        }

        $project = is_string($projectId)
            ? $workspace->projects()->active()->whereKey($projectId)->first()
            : null;

        if (! $project instanceof Project) {
            throw ValidationException::withMessages([
                'project_id' => __('onboarding.errors.project_unavailable'),
            ]);
        }

        $preferences->refresh()->forceFill([
            'onboarding_step' => OnboardingStep::Project->value,
            'onboarding_state' => [
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
            ],
        ])->save();

        $this->advanceOnboarding->handle($preferences->fresh(), OnboardingStep::Task);

        return $project;
    }

    private function workspace(User $user, UserPreference $preferences): Workspace
    {
        $workspaceId = $preferences->onboardingState()['workspace_id'] ?? null;
        $workspace = is_string($workspaceId)
            ? $user->workspaces()->whereKey($workspaceId)->first()
            : null;

        if (! $workspace instanceof Workspace) {
            throw ValidationException::withMessages([
                'workspace_id' => __('onboarding.errors.workspace_unavailable'),
            ]);
        }

        return $workspace;
    }
}
