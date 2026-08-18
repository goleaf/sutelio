<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Models\UserPreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        UserPreference::query()
            ->select(['id', 'onboarding_completed_at', 'onboarding_skipped_at'])
            ->whereNull('onboarding_completed_at')
            ->whereNotNull('onboarding_skipped_at')
            ->lazyById()
            ->each(function (UserPreference $preferences): void {
                $preferences->forceFill([
                    'onboarding_version' => UserPreference::ONBOARDING_VERSION,
                    'onboarding_step' => OnboardingStep::Welcome->value,
                    'onboarding_run_id' => (string) Str::uuid(),
                    'onboarding_state' => [],
                    'onboarding_started_at' => null,
                    'onboarding_checklist_dismissed_at' => null,
                ])->saveQuietly();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        UserPreference::query()
            ->select([
                'id',
                'onboarding_step',
                'onboarding_started_at',
                'onboarding_completed_at',
                'onboarding_skipped_at',
            ])
            ->whereNull('onboarding_completed_at')
            ->whereNotNull('onboarding_skipped_at')
            ->where('onboarding_step', OnboardingStep::Welcome->value)
            ->whereNull('onboarding_started_at')
            ->lazyById()
            ->each(function (UserPreference $preferences): void {
                $preferences->forceFill([
                    'onboarding_step' => OnboardingStep::Results->value,
                    'onboarding_state' => [],
                ])->saveQuietly();
            });
    }
};
