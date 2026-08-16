<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Validation\ValidationException;

class ChooseOnboardingWorkspace
{
    public function __construct(
        private CreateWorkspace $createWorkspace,
        private RunOnboardingCreation $runOnboardingCreation,
        private AdvanceOnboarding $advanceOnboarding,
    ) {}

    /** @param array{name: string, description?: string|null} $workspaceData */
    public function handle(
        User $user,
        UserPreference $preferences,
        string $mode,
        ?string $workspaceId,
        array $workspaceData,
        string $requestKey,
    ): Workspace {
        if ($mode === 'create') {
            $workspaceId = $this->runOnboardingCreation->handle(
                $preferences,
                OnboardingStep::Workspace,
                $requestKey,
                fn (): string => $this->createWorkspace->handle($workspaceData, $user)->id,
            );
        }

        $workspace = is_string($workspaceId)
            ? $user->workspaces()->whereKey($workspaceId)->first()
            : null;

        if (! $workspace instanceof Workspace) {
            throw ValidationException::withMessages([
                'workspace_id' => __('onboarding.errors.workspace_unavailable'),
            ]);
        }

        $preferences->refresh()->forceFill([
            'onboarding_step' => OnboardingStep::Workspace->value,
            'onboarding_state' => ['workspace_id' => $workspace->id],
        ])->save();

        $this->advanceOnboarding->handle($preferences->fresh(), OnboardingStep::Project);

        return $workspace;
    }
}
