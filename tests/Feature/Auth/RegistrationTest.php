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
        'timezone' => 'Europe/Vilnius',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false))
        ->assertCookie('sutelio_locale', 'lt');
    $this->assertDatabaseHas('user_preferences', [
        'user_id' => auth()->id(),
        'language' => 'lt',
        'timezone' => 'Europe/Vilnius',
        'week_start' => 'monday',
        'onboarding_step' => 'welcome',
        'onboarding_completed_at' => null,
        'onboarding_skipped_at' => null,
    ]);

    expect(auth()->user()?->preferences()->value('onboarding_run_id'))->not->toBeNull();
    $workspace = auth()->user()?->workspaces()->sole();

    expect($workspace?->name)->toBe('Mano darbo erdvė')
        ->and($workspace?->owner_id)->toBe(auth()->id())
        ->and($workspace?->taskStatuses()->count())->toBe(3)
        ->and($workspace?->taskStatuses()->where('is_default', true)->count())->toBe(1)
        ->and($workspace?->taskPriorities()->count())->toBe(5)
        ->and($workspace?->taskPriorities()->where('is_default', true)->count())->toBe(1)
        ->and($workspace?->projects()->count())->toBe(0)
        ->and($workspace?->todos()->count())->toBe(0);

    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace?->id,
        'user_id' => auth()->id(),
        'role' => 'owner',
    ]);
    $response->assertSessionHas('current_workspace_id', $workspace?->id);
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

test('a registered user remains gated until onboarding is completed', function () {
    $this->post(route('register.store'), [
        'name' => 'Ready User',
        'email' => 'ready@example.com',
        'language' => 'ru',
        'timezone' => 'Europe/Vilnius',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $workspace = auth()->user()?->workspaces()->sole();

    expect($workspace?->name)->toBe('Моё рабочее пространство');

    $this->get(route('dashboard'))->assertRedirectToRoute('onboarding.index');
    $this->post('/onboarding/skip')->assertNotFound();
    $this->assertDatabaseMissing('todos', ['workspace_id' => $workspace?->id]);
});

test('registration rejects an invalid detected timezone without creating an account', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'language' => 'ru',
        'timezone' => 'Not/A-Timezone',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('timezone');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
