<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOnboarding
{
    public function handle(UserPreference $preferences): UserPreference
    {
        return DB::transaction(function () use ($preferences): UserPreference {
            $lockedPreferences = UserPreference::query()
                ->whereKey($preferences->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPreferences->onboardingStep() !== OnboardingStep::Results) {
                throw ValidationException::withMessages([
                    'target_step' => __('onboarding.errors.complete_from_results'),
                ]);
            }

            $lockedPreferences->forceFill([
                'onboarding_step' => OnboardingStep::Results->value,
                'onboarding_state' => [],
                'onboarding_started_at' => $lockedPreferences->onboarding_started_at ?? now(),
                'onboarding_completed_at' => now(),
                'onboarding_skipped_at' => null,
            ])->save();

            return $lockedPreferences;
        }, attempts: 3);
    }
}
