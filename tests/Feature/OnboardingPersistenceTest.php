<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('onboarding persistence schema is available on SQLite', function () {
    expect(Schema::hasColumns('user_preferences', [
        'week_start',
        'onboarding_version',
        'onboarding_step',
        'onboarding_run_id',
        'onboarding_state',
        'onboarding_started_at',
        'onboarding_completed_at',
        'onboarding_skipped_at',
        'onboarding_checklist_dismissed_at',
    ]))->toBeTrue()
        ->and(Schema::hasTable('onboarding_operations'))->toBeTrue();
});

test('onboarding steps keep stable order and honest progress', function () {
    expect(OnboardingStep::ordered())->toBe([
        OnboardingStep::Welcome,
        OnboardingStep::Preferences,
        OnboardingStep::Workspace,
        OnboardingStep::Project,
        OnboardingStep::Task,
        OnboardingStep::ProductMap,
        OnboardingStep::Safety,
        OnboardingStep::Results,
    ])->and(OnboardingStep::Welcome->position())->toBe(1)
        ->and(OnboardingStep::Welcome->percent())->toBe(13)
        ->and(OnboardingStep::Results->position())->toBe(8)
        ->and(OnboardingStep::Results->percent())->toBe(100);
});

test('ordinary preference factories represent existing completed users', function () {
    $preferences = UserPreference::factory()->create();

    expect($preferences->requiresOnboarding())->toBeFalse()
        ->and($preferences->onboarding_step)->toBe(OnboardingStep::Results)
        ->and($preferences->onboarding_checklist_dismissed_at)->not->toBeNull();
});

test('pending onboarding state is explicit and resumable', function () {
    $preferences = UserPreference::factory()->pendingOnboarding()->create();

    expect($preferences->requiresOnboarding())->toBeTrue()
        ->and($preferences->onboarding_step)->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_state)->toBe([])
        ->and(Str::isUuid($preferences->onboarding_run_id))->toBeTrue()
        ->and($preferences->week_start)->toBe('sunday');
});

test('legacy preference rows without an onboarding run are not forced into onboarding', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::create([
        'user_id' => $user->id,
        ...UserPreference::defaults(),
    ]);

    expect($preferences->onboarding_run_id)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();
});

test('fortify registration creates a pending onboarding preference row', function () {
    $this->post(route('register.store'), [
        'name' => 'First Run User',
        'email' => 'first-run@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $preferences = User::query()
        ->where('email', 'first-run@example.com')
        ->firstOrFail()
        ->preferences;

    expect($preferences?->requiresOnboarding())->toBeTrue()
        ->and($preferences?->onboarding_step)->toBe(OnboardingStep::Welcome)
        ->and($preferences?->onboarding_completed_at)->toBeNull()
        ->and($preferences?->onboarding_skipped_at)->toBeNull()
        ->and($preferences?->week_start)->toBe('sunday');
});
