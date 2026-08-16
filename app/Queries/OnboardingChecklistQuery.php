<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Support\Facades\Gate;

class OnboardingChecklistQuery
{
    /**
     * @return array{show: bool, workspace_id: string|null, can_invite: bool, has_team_member: bool, has_security_factor: bool, can_manage_backups: bool}
     */
    public function forUser(User $user, ?Workspace $workspace): array
    {
        $user->loadMissing('preferences');
        $preferences = $user->preferences;
        $show = $preferences instanceof UserPreference
            && $preferences->onboarding_checklist_dismissed_at === null
            && ($preferences->onboarding_completed_at !== null
                || $preferences->onboarding_skipped_at !== null);

        if (! $show) {
            return $this->hidden();
        }

        return [
            'show' => true,
            'workspace_id' => $workspace?->id,
            'can_invite' => $workspace instanceof Workspace
                && Gate::forUser($user)->allows('invite', $workspace),
            'has_team_member' => $workspace?->members()
                ->whereKeyNot($user->id)
                ->exists() ?? false,
            'has_security_factor' => $user->hasEnabledTwoFactorAuthentication()
                || $user->passkeys()->exists(),
            'can_manage_backups' => Gate::forUser($user)->allows('manageDatabaseBackups'),
        ];
    }

    /**
     * @return array{show: false, workspace_id: null, can_invite: false, has_team_member: false, has_security_factor: false, can_manage_backups: false}
     */
    private function hidden(): array
    {
        return [
            'show' => false,
            'workspace_id' => null,
            'can_invite' => false,
            'has_team_member' => false,
            'has_security_factor' => false,
            'can_manage_backups' => false,
        ];
    }
}
