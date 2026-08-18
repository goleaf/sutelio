<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class SkipOnboarding
{
    public function __construct(private readonly EnsureUserHasWorkspace $ensureUserHasWorkspace) {}

    public function handle(User $user, UserPreference $preferences, bool $isReplay): Workspace
    {
        return DB::transaction(function () use ($user, $preferences, $isReplay): Workspace {
            $lockedPreferences = UserPreference::query()
                ->whereKey($preferences->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $language = UserLanguage::tryFrom((string) $lockedPreferences->language)
                ?? UserLanguage::English;
            $workspace = $this->ensureUserHasWorkspace->handle($user, $language);

            if (! $isReplay) {
                $lockedPreferences->forceFill([
                    'onboarding_step' => OnboardingStep::Results->value,
                    'onboarding_state' => [],
                    'onboarding_started_at' => $lockedPreferences->onboarding_started_at ?? now(),
                    'onboarding_skipped_at' => now(),
                ])->save();
            }

            return $workspace;
        }, attempts: 3);
    }
}
