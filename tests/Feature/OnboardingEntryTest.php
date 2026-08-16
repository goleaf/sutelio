<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserPreference;
use Inertia\Testing\AssertableInertia as Assert;

test('verified pending users are redirected from application pages and settings to onboarding', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('onboarding.index');

    $this->actingAs($user)
        ->get(route('preferences.edit'))
        ->assertRedirectToRoute('onboarding.index');
});

test('unverified pending users still reach the verification notice', function () {
    $user = User::factory()->unverified()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('verification.notice');
});

test('existing complete users and legacy users without preferences are not redirected', function () {
    $complete = User::factory()->create();
    UserPreference::factory()->for($complete)->create();
    $legacy = User::factory()->create();

    $this->actingAs($complete)->get(route('dashboard'))->assertOk();
    $this->actingAs($legacy)->get(route('dashboard'))->assertOk();
});

test('onboarding requires authentication and verification', function () {
    $unverified = User::factory()->unverified()->create();
    UserPreference::factory()->for($unverified)->pendingOnboarding()->create();

    $this->get(route('onboarding.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs($unverified)
        ->get(route('onboarding.index'))
        ->assertRedirectToRoute('verification.notice');
});

test('pending users can render their resumable onboarding page', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->pendingOnboarding()->create([
        'language' => 'lt',
        'onboarding_state' => ['language' => 'lt'],
    ]);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Index')
            ->where('progress.step', OnboardingStep::Welcome->value)
            ->where('progress.position', 1)
            ->where('progress.total', 8)
            ->where('progress.percent', 13)
            ->where('progress.is_replay', false)
            ->where('preferences.language', $preferences->language)
            ->where('state', [])
            ->has('copy.title')
            ->has('copy.description'));
});

test('completed users leave onboarding for their selected start page', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['start_page' => 'projects']);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertRedirectToRoute('projects');
});

test('legacy users without preferences leave onboarding for the default start page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertRedirectToRoute('dashboard');
});

test('authentication recovery routes remain outside the onboarding gate', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->get(route('password.request'))->assertOk();
    $this->actingAs($user)->get(route('password.confirm'))->assertOk();
});

test('versioned api user responses are unchanged for pending users', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();
    $token = $user->createToken('onboarding-entry-test')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('api.v1.user.show'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonMissingPath('onboarding');
});
