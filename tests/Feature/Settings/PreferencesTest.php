<?php

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

test('preferences page is the canonical settings page for regional and navigation preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('preferences.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Preferences')
            ->where('preferences.language', 'en')
            ->where('preferences.timezone', 'UTC')
            ->where('preferences.date_format', 'Y-m-d')
            ->where('preferences.time_format', 'H:i')
            ->where('preferences.start_page', 'dashboard')
            ->where('preferences.week_start', 'sunday')
            ->where('timezoneGroups', fn ($groups): bool => $groups->contains(
                fn (array $group): bool => $group['key'] === 'europe'
                    && $group['label'] === 'Europe'
                    && collect($group['options'])->contains(
                        fn (array $option): bool => $option['value'] === 'Europe/Vilnius'
                            && str_contains($option['label'], 'Lithuania'),
                    ),
            ))
            ->missing('timezones'));
});

test('legacy appearance page redirects to preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertRedirectToRoute('preferences.edit');
});

test('appearance is fixed to the light design and is no longer persisted', function () {
    expect(Schema::hasColumn('user_preferences', 'theme'))->toBeFalse()
        ->and(file_exists(resource_path('js/components/AppearanceTabs.vue')))->toBeFalse()
        ->and(file_exists(resource_path('js/composables/useAppearance.ts')))->toBeFalse()
        ->and(file_exists(public_path('theme.js')))->toBeFalse();

    $preferencesPage = file_get_contents(resource_path('js/pages/settings/Preferences.vue'));

    expect($preferencesPage)
        ->not->toContain('AppearanceTabs')
        ->not->toContain('settings.preferences.appearance');
});

test('theme preference removal is reversible and preserves populated rows', function () {
    $preferences = UserPreference::factory()->create();
    $migration = require database_path('migrations/2026_08_17_114137_drop_theme_from_user_preferences_table.php');

    expect(Schema::hasColumn('user_preferences', 'theme'))->toBeFalse();

    $migration->down();

    expect(Schema::hasColumn('user_preferences', 'theme'))->toBeTrue()
        ->and(UserPreference::query()->whereKey($preferences)->value('theme'))->toBe('system');

    $migration->up();

    expect(Schema::hasColumn('user_preferences', 'theme'))->toBeFalse()
        ->and(UserPreference::query()->whereKey($preferences)->exists())->toBeTrue();
});

test('settings preferences pages require authentication', function (string $routeName) {
    $this->get(route($routeName))
        ->assertRedirectToRoute('login');
})->with([
    'preferences' => 'preferences.edit',
    'legacy appearance redirect' => 'appearance.edit',
]);

test('preferences page omits the retired appearance control', function () {
    $preferencesPage = file_get_contents(resource_path('js/pages/settings/Preferences.vue'));
    $settingsLayout = file_get_contents(resource_path('js/layouts/settings/Layout.vue'));

    expect($preferencesPage)
        ->not->toContain("import AppearanceTabs from '@/components/AppearanceTabs.vue'")
        ->not->toContain('<AppearanceTabs />')
        ->and($settingsLayout)
        ->not->toContain("label: 'Appearance'");
});

test('preferences offer every implemented default view', function () {
    $preferencesPage = file_get_contents(resource_path('js/pages/settings/Preferences.vue'));

    expect($preferencesPage)
        ->toContain('value="board"')
        ->toContain('value="list"')
        ->toContain('value="calendar"');
});

test('users can persist every bounded regional and navigation preference', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('preferences.edit'))
        ->put(route('preferences.update'), [
            'timezone' => 'Europe/Vilnius',
            'language' => 'lt',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'default_view' => 'board',
            'start_page' => 'tasks',
            'week_start' => 'monday',
        ])
        ->assertRedirect(route('preferences.edit'))
        ->assertSessionHas('locale', 'lt');

    $preferences = $user->preferences()->firstOrFail();

    expect($preferences->timezone)->toBe('Europe/Vilnius')
        ->and($preferences->language)->toBe('lt')
        ->and($preferences->date_format)->toBe('d.m.Y')
        ->and($preferences->time_format)->toBe('H:i')
        ->and($preferences->default_view)->toBe('board')
        ->and($preferences->start_page)->toBe('tasks')
        ->and($preferences->week_start)->toBe('monday');
});

test('preference values are allowlisted and validation follows the saved locale', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['language' => 'ru']);

    $response = $this->actingAs($user)
        ->from(route('preferences.edit'))
        ->put(route('preferences.update'), [
            'timezone' => 'Not/A-Timezone',
            'language' => 'fr',
            'date_format' => 'arbitrary',
            'time_format' => 'arbitrary',
            'default_view' => 'arbitrary',
            'start_page' => 'https://example.com',
            'week_start' => 'arbitrary',
        ])
        ->assertRedirect(route('preferences.edit'))
        ->assertSessionHasErrors([
            'timezone',
            'language',
            'date_format',
            'time_format',
            'default_view',
            'start_page',
            'week_start',
        ]);

    expect($response->getSession()->get('errors')->get('timezone')[0])
        ->toContain('часовой пояс');

    expect($user->preferences()->firstOrFail()->language)->toBe('ru');
});
