<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

class DismissOnboardingChecklist
{
    public function handle(User $user): void
    {
        $preferences = $user->preferences()->firstOrFail();

        $preferences->update([
            'onboarding_checklist_dismissed_at' => now(),
        ]);

        $user->setRelation('preferences', $preferences);
    }
}
