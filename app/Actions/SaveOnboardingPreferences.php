<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;

class SaveOnboardingPreferences
{
    public function __construct(
        private UpdateUserPreferences $updateUserPreferences,
        private AdvanceOnboarding $advanceOnboarding,
    ) {}

    /** @param array<string, bool|string> $attributes */
    public function handle(User $user, UserPreference $preferences, array $attributes): UserPreference
    {
        $updatedPreferences = $this->updateUserPreferences->execute($user, $attributes);
        $updatedPreferences->forceFill([
            'onboarding_step' => OnboardingStep::Preferences->value,
            'onboarding_state' => [],
        ])->save();

        return $this->advanceOnboarding->handle(
            $updatedPreferences->fresh(),
            OnboardingStep::Workspace,
        );
    }
}
