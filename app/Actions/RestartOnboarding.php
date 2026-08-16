<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestartOnboarding
{
    public function handle(User $user): UserPreference
    {
        return DB::transaction(function () use ($user): UserPreference {
            $preferences = $user->preferences()
                ->lockForUpdate()
                ->first();

            if (! $preferences instanceof UserPreference) {
                $now = now();

                return $user->preferences()->create([
                    ...UserPreference::defaults(),
                    'onboarding_version' => UserPreference::ONBOARDING_VERSION,
                    'onboarding_step' => OnboardingStep::Welcome->value,
                    'onboarding_run_id' => (string) Str::uuid(),
                    'onboarding_state' => [],
                    'onboarding_started_at' => $now,
                    'onboarding_completed_at' => $now,
                    'onboarding_skipped_at' => null,
                    'onboarding_checklist_dismissed_at' => $now,
                ]);
            }

            $legacyWithoutLifecycle = $preferences->onboarding_run_id === null
                && $preferences->onboarding_completed_at === null
                && $preferences->onboarding_skipped_at === null;

            $preferences->forceFill([
                'onboarding_version' => UserPreference::ONBOARDING_VERSION,
                'onboarding_step' => OnboardingStep::Welcome->value,
                'onboarding_run_id' => (string) Str::uuid(),
                'onboarding_state' => [],
                'onboarding_started_at' => now(),
                'onboarding_completed_at' => $legacyWithoutLifecycle
                    ? now()
                    : $preferences->onboarding_completed_at,
                'onboarding_checklist_dismissed_at' => $legacyWithoutLifecycle
                    ? now()
                    : $preferences->onboarding_checklist_dismissed_at,
            ])->save();

            return $preferences;
        }, attempts: 3);
    }
}
