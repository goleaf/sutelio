<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdvanceOnboarding
{
    public function handle(UserPreference $preferences, OnboardingStep $targetStep): UserPreference
    {
        return DB::transaction(function () use ($preferences, $targetStep): UserPreference {
            $lockedPreferences = UserPreference::query()
                ->whereKey($preferences->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $currentStep = $lockedPreferences->onboardingStep();

            if (abs($targetStep->position() - $currentStep->position()) !== 1) {
                throw ValidationException::withMessages([
                    'target_step' => __('onboarding.errors.invalid_step'),
                ]);
            }

            $lockedPreferences->forceFill([
                'onboarding_step' => $targetStep->value,
                'onboarding_started_at' => $lockedPreferences->onboarding_started_at ?? now(),
            ])->save();

            return $lockedPreferences;
        }, attempts: 3);
    }
}
