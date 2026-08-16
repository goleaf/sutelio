<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class SkipOnboarding
{
    public function handle(UserPreference $preferences, bool $isReplay): UserPreference
    {
        if ($isReplay) {
            return $preferences;
        }

        return DB::transaction(function () use ($preferences): UserPreference {
            $lockedPreferences = UserPreference::query()
                ->whereKey($preferences->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPreferences->forceFill([
                'onboarding_step' => OnboardingStep::Results->value,
                'onboarding_state' => [],
                'onboarding_started_at' => $lockedPreferences->onboarding_started_at ?? now(),
                'onboarding_skipped_at' => now(),
            ])->save();

            return $lockedPreferences;
        }, attempts: 3);
    }
}
