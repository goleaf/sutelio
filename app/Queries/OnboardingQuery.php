<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\OnboardingStep;
use App\Models\UserPreference;

class OnboardingQuery
{
    /**
     * @return array{
     *     progress: array{step: string, position: int, total: int, percent: int, is_replay: bool},
     *     state: array<array-key, mixed>
     * }
     */
    public function forPreferences(UserPreference $preferences, bool $isReplay): array
    {
        $step = $preferences->onboardingStep();
        $state = $preferences->getAttribute('onboarding_state');

        return [
            'progress' => [
                'step' => $step->value,
                'position' => $step->position(),
                'total' => count(OnboardingStep::ordered()),
                'percent' => $step->percent(),
                'is_replay' => $isReplay,
            ],
            'state' => is_array($state) ? $state : [],
        ];
    }
}
