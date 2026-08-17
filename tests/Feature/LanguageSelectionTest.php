<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserPreference;
use Inertia\Testing\AssertableInertia as Assert;

const LANGUAGE_COOKIE = 'sutelio_locale';

test('a first guest visit uses browser language for presentation and still requires a choice', function () {
    $this->withHeader('Accept-Language', 'lt-LT,lt;q=0.9,en;q=0.8')
        ->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'lt')
            ->where('localization.current', 'lt')
            ->where('localization.requires_selection', true)
            ->where('localization.previews.en.copy.first_run.title', 'Welcome to Sutelio')
            ->where('localization.previews.lt.copy.continue', 'Naudoti šią kalbą')
            ->where('localization.previews.ru.copy.first_run.title', 'Добро пожаловать в Sutelio')
            ->where('localization.previews.ru.language_names.en', 'Английский')
            ->where('localization.previews.ru.language_names.lt', 'Литовский')
            ->where('localization.previews.ru.language_names.ru', 'Русский')
            ->where('localization.options', [
                [
                    'code' => 'en',
                    'default_week_start' => 'sunday',
                    'native_name' => 'English',
                    'localized_name' => 'Anglų',
                    'flag_url' => '/images/flags/gb.svg',
                ],
                [
                    'code' => 'lt',
                    'default_week_start' => 'monday',
                    'native_name' => 'Lietuvių',
                    'localized_name' => 'Lietuvių',
                    'flag_url' => '/images/flags/lt.svg',
                ],
                [
                    'code' => 'ru',
                    'default_week_start' => 'monday',
                    'native_name' => 'Русский',
                    'localized_name' => 'Rusų',
                    'flag_url' => '/images/flags/ru.svg',
                ],
            ]));
});

test('a guest language choice persists in session and an encrypted device cookie', function () {
    $response = $this->from(route('login'))
        ->put('/locale', ['language' => 'ru'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'ru')
        ->assertCookie(LANGUAGE_COOKIE, 'ru')
        ->assertCookieNotExpired(LANGUAGE_COOKIE);

    $cookie = $response->getCookie(LANGUAGE_COOKIE);

    expect($cookie)->not->toBeNull()
        ->and($cookie?->isHttpOnly())->toBeTrue()
        ->and($cookie?->getSameSite())->toBe('lax')
        ->and($cookie?->getExpiresTime())->toBeGreaterThan(now()->addYears(4)->getTimestamp());

    $this->withCookie(LANGUAGE_COOKIE, 'ru')
        ->get(route('register'))
        ->assertOk()
        ->assertSee('lang="ru"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('localization.current', 'ru')
            ->where('localization.requires_selection', false));
});

test('unsupported language choices never change session cookie or account state', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create(['language' => 'en']);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->from(route('dashboard'))
        ->put('/locale', ['language' => 'fr'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors('language')
        ->assertSessionHas('locale', 'en')
        ->assertCookieMissing(LANGUAGE_COOKIE);

    expect($preferences->fresh()->language)->toBe('en');
});

test('account language outranks the device cookie and authenticated changes persist everywhere', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'language' => 'en',
        'week_start' => 'sunday',
    ]);

    $this->actingAs($user)
        ->withCookie(LANGUAGE_COOKIE, 'ru')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('localization.current', 'en')
            ->where('localization.requires_selection', false));

    $this->from(route('dashboard'))
        ->put('/locale', ['language' => 'lt'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('locale', 'lt')
        ->assertCookie(LANGUAGE_COOKIE, 'lt');

    expect($preferences->fresh()->language)->toBe('lt')
        ->and($preferences->fresh()->week_start)->toBe('monday');
});

test('an authenticated English language change restores the Sunday default', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->create([
        'language' => 'ru',
        'week_start' => 'monday',
    ]);

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->put('/locale', ['language' => 'en'])
        ->assertRedirect(route('dashboard'));

    expect($preferences->fresh()->language)->toBe('en')
        ->and($preferences->fresh()->week_start)->toBe('sunday');
});

test('settings and onboarding language changes refresh the device preference', function () {
    $settingsUser = User::factory()->create();
    UserPreference::factory()->for($settingsUser)->create(['language' => 'en']);

    $this->actingAs($settingsUser)
        ->from(route('preferences.edit'))
        ->put(route('preferences.update'), ['language' => 'ru'])
        ->assertRedirect(route('preferences.edit'))
        ->assertSessionHas('locale', 'ru')
        ->assertCookie(LANGUAGE_COOKIE, 'ru');

    $onboardingUser = User::factory()->create();
    $preferences = UserPreference::factory()->for($onboardingUser)->pendingOnboarding()->create([
        'language' => 'en',
        'onboarding_step' => 'preferences',
    ]);

    $this->actingAs($onboardingUser)
        ->put(route('onboarding.preferences'), [
            'language' => 'lt',
            'timezone' => 'Europe/Vilnius',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'default_view' => 'board',
            'start_page' => 'tasks',
            'week_start' => 'monday',
        ])
        ->assertRedirectToRoute('onboarding.index')
        ->assertSessionHas('locale', 'lt')
        ->assertCookie(LANGUAGE_COOKIE, 'lt');

    expect($preferences->fresh()->language)->toBe('lt');
});
