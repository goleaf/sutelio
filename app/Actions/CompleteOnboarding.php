<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOnboarding
{
    public function __construct(private readonly EnsureUserHasWorkspace $ensureUserHasWorkspace) {}

    public function handle(User $user, UserPreference $preferences): Workspace
    {
        return DB::transaction(function () use ($user, $preferences): Workspace {
            $lockedPreferences = UserPreference::query()
                ->whereKey($preferences->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPreferences->onboardingStep() !== OnboardingStep::Results) {
                throw ValidationException::withMessages([
                    'target_step' => __('onboarding.errors.complete_from_results'),
                ]);
            }

            $language = UserLanguage::tryFrom((string) $lockedPreferences->language)
                ?? UserLanguage::English;
            $workspace = $this->ensureUserHasWorkspace->handle($user, $language);

            $lockedPreferences->forceFill([
                'onboarding_step' => OnboardingStep::Results->value,
                'onboarding_state' => [],
                'onboarding_started_at' => $lockedPreferences->onboarding_started_at ?? now(),
                'onboarding_completed_at' => now(),
                'onboarding_skipped_at' => null,
            ])->save();

            return $workspace;
        }, attempts: 3);
    }
}
