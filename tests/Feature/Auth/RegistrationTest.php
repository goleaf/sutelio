<?php

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'language' => 'lt',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false))
        ->assertCookie('sutelio_locale', 'lt');
    $this->assertDatabaseHas('user_preferences', [
        'user_id' => auth()->id(),
        'language' => 'lt',
        'timezone' => 'UTC',
        'week_start' => 'sunday',
        'onboarding_step' => 'welcome',
        'onboarding_completed_at' => null,
        'onboarding_skipped_at' => null,
    ]);

    expect(auth()->user()?->preferences()->value('onboarding_run_id'))->not->toBeNull();
    Notification::assertNotSentTo(auth()->user(), VerifyEmail::class);
});

test('registration rejects an unsupported language without creating an account', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'language' => 'fr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('language');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
