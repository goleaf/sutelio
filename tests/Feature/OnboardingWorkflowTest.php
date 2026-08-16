<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

/** @return array{0: User, 1: UserPreference} */
function createPendingOnboardingUser(array $preferenceAttributes = []): array
{
    $user = User::factory()->create();
    $preferences = UserPreference::factory()
        ->for($user)
        ->pendingOnboarding()
        ->create($preferenceAttributes);

    return [$user, $preferences];
}

test('progress moves only between adjacent steps and resumes after reload', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Preferences->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('onboarding.index');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Preferences)
        ->and($preferences->onboarding_started_at)->not->toBeNull();

    $this->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.step', OnboardingStep::Preferences->value)
            ->where('progress.position', 2));

    $this->patch(route('onboarding.progress'), [
        'target_step' => OnboardingStep::Welcome->value,
    ])->assertSessionHasNoErrors();

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome);
});

test('progress rejects jumps and unknown step values without moving the cursor', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->from(route('onboarding.index'))
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Task->value,
        ])
        ->assertRedirect(route('onboarding.index'))
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->fresh()->onboarding_started_at)->toBeNull();

    $this->from(route('onboarding.index'))
        ->patch(route('onboarding.progress'), ['target_step' => 'unknown'])
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Welcome);
});

test('required onboarding can be skipped without deleting its prior facts', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'start_page' => 'calendar',
        'onboarding_step' => OnboardingStep::Project->value,
        'onboarding_state' => ['workspace_id' => (string) Str::uuid()],
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.skip'))
        ->assertRedirectToRoute('calendar')
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Results)
        ->and($preferences->onboarding_state)->toBe([])
        ->and($preferences->onboarding_skipped_at)->not->toBeNull()
        ->and($preferences->onboarding_completed_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();
});

test('completion is accepted only from results and follows the selected start page', function () {
    [$user, $preferences] = createPendingOnboardingUser([
        'start_page' => 'tasks',
        'onboarding_step' => OnboardingStep::Safety->value,
    ]);

    $this->actingAs($user)
        ->from(route('onboarding.index'))
        ->post(route('onboarding.complete'))
        ->assertSessionHasErrors('target_step');

    expect($preferences->fresh()->onboarding_completed_at)->toBeNull();

    $preferences->update(['onboarding_step' => OnboardingStep::Results->value]);

    $this->post(route('onboarding.complete'))
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('onboarding_replay')
        ->assertRedirectToRoute('todos.index');

    $preferences->refresh();

    expect($preferences->onboarding_completed_at)->not->toBeNull()
        ->and($preferences->onboarding_skipped_at)->toBeNull()
        ->and($preferences->onboarding_state)->toBe([])
        ->and($preferences->requiresOnboarding())->toBeFalse();
});

test('completed users can replay without reopening their required gate', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'start_page' => 'projects',
    ]);
    $completion = $preferences->onboarding_completed_at;
    $previousRunId = $preferences->onboarding_run_id;

    $this->actingAs($user)
        ->post(route('onboarding.restart'))
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('onboarding_replay', true);

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_run_id)->not->toBe($previousRunId)
        ->and($preferences->onboarding_version)->toBe(UserPreference::ONBOARDING_VERSION)
        ->and($preferences->onboarding_started_at)->not->toBeNull()
        ->and($preferences->onboarding_completed_at?->equalTo($completion))->toBeTrue()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();

    $this->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.is_replay', true)
            ->where('progress.step', OnboardingStep::Welcome->value));
});

test('skipping a replay ends only the replay session and preserves completion facts', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'start_page' => 'projects',
    ]);
    $completion = $preferences->onboarding_completed_at;

    $this->actingAs($user)->post(route('onboarding.restart'));

    $this->post(route('onboarding.skip'))
        ->assertRedirectToRoute('projects')
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_completed_at?->equalTo($completion))->toBeTrue()
        ->and($preferences->onboarding_skipped_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('onboarding.index'))->assertRedirectToRoute('projects');
});

test('skipping a replay preserves an earlier skipped lifecycle fact', function () {
    [$user, $preferences] = createPendingOnboardingUser();

    $this->actingAs($user)->post(route('onboarding.skip'));
    $skippedAt = $preferences->fresh()->onboarding_skipped_at;

    $this->post(route('onboarding.restart'))
        ->assertSessionHas('onboarding_replay', true);
    $this->post(route('onboarding.skip'))
        ->assertSessionMissing('onboarding_replay');

    $preferences->refresh();

    expect($preferences->onboarding_skipped_at?->equalTo($skippedAt))->toBeTrue()
        ->and($preferences->onboarding_completed_at)->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();
});

test('completed users cannot mutate onboarding outside an active replay', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('onboarding.progress'), [
            'target_step' => OnboardingStep::Safety->value,
        ])
        ->assertForbidden();

    expect($preferences->fresh()->onboardingStep())->toBe(OnboardingStep::Results);
});

test('restarting onboarding creates a completed baseline for legacy users without preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.restart'))
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('onboarding_replay', true);

    $preferences = $user->preferences()->firstOrFail();

    expect($preferences->onboardingStep())->toBe(OnboardingStep::Welcome)
        ->and($preferences->onboarding_completed_at)->not->toBeNull()
        ->and($preferences->requiresOnboarding())->toBeFalse();

    $this->get(route('dashboard'))->assertOk();
});

test('a future content version never silently reopens completed onboarding', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'onboarding_version' => 0,
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    expect($preferences->fresh()->onboarding_version)->toBe(0);

    $this->post(route('onboarding.restart'))
        ->assertSessionHas('onboarding_replay', true);

    expect($preferences->fresh()->onboarding_version)
        ->toBe(UserPreference::ONBOARDING_VERSION);
});

test('a genuinely pending user cannot bypass the gate with a replay session flag', function () {
    [$user] = createPendingOnboardingUser();

    $this->actingAs($user)
        ->withSession(['onboarding_replay' => true])
        ->get(route('dashboard'))
        ->assertRedirectToRoute('onboarding.index');
});
