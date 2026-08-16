<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition(): array
    {
        $now = now();

        return [
            'user_id' => UserFactory::new(),
            'timezone' => fake()->timezone(),
            'language' => 'en',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'theme' => 'system',
            'default_view' => 'list',
            'start_page' => 'dashboard',
            'week_start' => 'sunday',
            'notification_email' => true,
            'notification_browser' => true,
            'notification_in_app' => true,
            ...UserPreference::pendingOnboardingDefaults(),
            'onboarding_step' => 'results',
            'onboarding_started_at' => $now,
            'onboarding_completed_at' => $now,
            'onboarding_checklist_dismissed_at' => $now,
        ];
    }

    public function pendingOnboarding(): static
    {
        return $this->state(fn (): array => UserPreference::pendingOnboardingDefaults());
    }

    public function lithuanian(): static
    {
        return $this->state(fn (): array => [
            'language' => 'lt',
            'timezone' => 'Europe/Vilnius',
        ]);
    }

    public function russian(): static
    {
        return $this->state(fn (): array => ['language' => 'ru']);
    }

    public function withoutNotifications(): static
    {
        return $this->state(fn (): array => [
            'notification_email' => false,
            'notification_browser' => false,
            'notification_in_app' => false,
        ]);
    }
}
