<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

test('frontend translations cover every supported locale', function () {
    $catalogs = collect(['en', 'lt', 'ru'])
        ->mapWithKeys(fn (string $locale): array => [
            $locale => collect(Arr::dot(trans('ui', locale: $locale))),
        ]);

    $englishKeys = $catalogs->get('en')->keys()->all();

    foreach ($catalogs as $locale => $catalog) {
        expect($catalog->keys()->all())->toBe($englishKeys)
            ->and($catalog->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty("The {$locale} frontend catalog contains an empty translation.");
    }
});

test('data safety messages preserve locale parity', function () {
    $paths = [
        'settings.backup',
        'settings.export',
        'settings.navigation',
    ];

    foreach ($paths as $path) {
        $englishKeys = collect(Arr::dot(data_get(trans('ui', locale: 'en'), $path)))
            ->keys()
            ->all();

        foreach (['lt', 'ru'] as $locale) {
            expect(collect(Arr::dot(data_get(trans('ui', locale: $locale), $path)))->keys()->all())
                ->toBe($englishKeys, "The {$locale} {$path} messages are out of sync.");
        }
    }
});

test('task focus desk copy is complete in every supported locale', function () {
    $requiredKeys = [
        'filters.active_count_few',
        'filters.active_count_many',
        'filters.active_count_one',
        'filters.active_count_other',
        'filters.completed_today',
        'filters.favorites',
        'filters.focus',
        'filters.pinned',
        'index.enter_selection',
        'index.exit_selection',
        'index.filtered_empty_description',
        'index.filtered_empty_title',
        'index.open_task',
        'index.result_summary',
        'index.row_actions',
    ];

    foreach (['en', 'lt', 'ru'] as $locale) {
        $tasks = collect(Arr::dot(trans('ui.tasks', locale: $locale)));

        expect($tasks)->toHaveKeys($requiredKeys)
            ->and($tasks->only($requiredKeys)->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty();
    }
});

test('every application translation catalog has locale parity', function () {
    $catalogs = collect(File::files(lang_path('en')))
        ->map(fn (SplFileInfo $file): string => $file->getBasename('.php'))
        ->reject(fn (string $catalog): bool => in_array($catalog, ['auth', 'passwords', 'validation'], true));

    foreach ($catalogs as $catalog) {
        $englishKeys = collect(Arr::dot(trans($catalog, locale: 'en')))->keys()->all();

        foreach (['lt', 'ru'] as $locale) {
            expect(collect(Arr::dot(trans($catalog, locale: $locale)))->keys()->all())
                ->toBe($englishKeys, "The {$locale}/{$catalog}.php catalog is out of sync.");
        }
    }
});

test('guided onboarding copy has complete groups placeholder parity and plural forms', function () {
    $requiredGroups = [
        'meta',
        'steps',
        'actions',
        'status',
        'errors',
        'welcome',
        'preferences',
        'workspace',
        'project',
        'task',
        'product_map',
        'safety',
        'results',
    ];
    $english = Arr::dot(trans('onboarding', locale: 'en'));

    foreach ($requiredGroups as $group) {
        expect(trans("onboarding.{$group}", locale: 'en'))->toBeArray();
    }

    foreach (['en', 'lt', 'ru'] as $locale) {
        $catalog = Arr::dot(trans('onboarding', locale: $locale));

        expect(array_keys($catalog))->toBe(array_keys($english));

        foreach ($english as $key => $message) {
            preg_match_all('/:[A-Za-z_]+/', (string) $message, $englishPlaceholders);
            preg_match_all('/:[A-Za-z_]+/', (string) $catalog[$key], $localePlaceholders);

            expect($localePlaceholders[0], "Placeholder mismatch for {$locale}.{$key}")
                ->toBe($englishPlaceholders[0]);
        }

        expect($catalog)
            ->toHaveKeys([
                'results.entity_count_one',
                'results.entity_count_few',
                'results.entity_count_many',
                'results.entity_count_other',
            ]);
    }
});

test('calendar planning copy is complete in every supported locale', function () {
    foreach (['en', 'lt', 'ru'] as $locale) {
        $calendar = trans('workspace.calendar', locale: $locale);

        expect($calendar)
            ->toHaveKeys([
                'planning_period',
                'visible_tasks',
                'attention',
                'attention_description',
                'view_all_overdue',
                'no_overdue',
                'loading_period',
                'tasks_on_date',
                'outside_month',
            ])
            ->and(collect($calendar)->only([
                'planning_period',
                'visible_tasks',
                'attention',
                'attention_description',
                'view_all_overdue',
                'no_overdue',
                'loading_period',
                'tasks_on_date',
                'outside_month',
            ])->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty();
    }
});

test('guest requests use a supported browser language and English fallback', function () {
    $this->withHeader('Accept-Language', 'lt-LT,lt;q=0.9')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('lang="lt"', false)
        ->assertSee('dir="ltr"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('ui.auth.login.title', 'Prisijungimas'));

    $this->withHeader('Accept-Language', 'fr-FR')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('ui.auth.login.title', 'Log in'));
});

test('localized framework messages fall back to English for uncovered rules', function () {
    expect(trans('validation.timezone', locale: 'ru'))
        ->toContain('часовой пояс')
        ->and(trans('validation.after_or_equal', locale: 'ru'))
        ->toBe('The :attribute field must be a date after or equal to :date.')
        ->and(trans('auth.failed', locale: 'lt'))
        ->toContain('prisijungimo duomenys');
});

test('frontend formatters and document locale synchronization are centralized', function () {
    $ui = File::get(resource_path('js/composables/useUi.ts'));
    $workspaceUi = File::get(resource_path('js/composables/useWorkspaceUi.ts'));
    $application = File::get(resource_path('js/app.ts'));

    expect($ui)
        ->toContain("from '@/lib/formatters'")
        ->not->toContain('new Intl.DateTimeFormat')
        ->and($workspaceUi)
        ->toContain("from '@/lib/formatters'")
        ->not->toContain('new Intl.DateTimeFormat')
        ->and($application)
        ->toContain("router.on('success'")
        ->toContain('document.documentElement.lang = language')
        ->toContain("document.documentElement.dir = 'ltr'");
});

test('timezone selection uses one grouped searchable accessible component', function () {
    $componentPath = resource_path('js/components/preferences/TimezoneCombobox.vue');

    expect(File::exists($componentPath))->toBeTrue();

    $component = File::get($componentPath);
    $onboarding = File::get(resource_path('js/components/onboarding/PreferencesStep.vue'));
    $settings = File::get(resource_path('js/pages/settings/Preferences.vue'));

    expect($component)
        ->toContain('ComboboxRoot')
        ->toContain('ComboboxInput')
        ->toContain(':open="isOpen"')
        ->toContain('@update:open="handleOpenChange"')
        ->toContain('v-model="searchTerm"')
        ->toContain("searchTerm.value = ''")
        ->toContain('ComboboxGroup')
        ->toContain('ComboboxLabel')
        ->toContain('ComboboxEmpty')
        ->toContain(':text-value="option.search_terms"')
        ->toContain('${option.search_terms} ${label} ${group.label}')
        ->toContain("t('timezones.search_placeholder')")
        ->toContain("t('timezones.empty')")
        ->and($onboarding)
        ->toContain('<TimezoneCombobox')
        ->toContain('const previousWeekStart = weekStart.value')
        ->toContain('weekStart.value = previousWeekStart')
        ->not->toContain('v-for="value in timezones"')
        ->and($settings)
        ->toContain('<TimezoneCombobox')
        ->toContain('const previousWeekStart = form.week_start')
        ->toContain('form.week_start = previousWeekStart')
        ->not->toContain('v-for="tz in props.timezones"');
});

test('shared frontend copy follows the supported user locale with English fallback', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();

    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);
    UserPreference::create([
        'user_id' => $user->id,
        'timezone' => 'Europe/Vilnius',
        'language' => 'ru',
    ]);

    $this->actingAs($user)
        ->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('ui.tasks.index.title', 'Задачи')
            ->where('ui.tasks.detail.checklists', 'Списки')
            ->where('ui.settings.navigation.security', 'Безопасность'));

    $user->preferences()->update(['language' => 'en']);
    $user->unsetRelation('preferences');

    $this->get(route('todos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('ui.tasks.index.title', 'Tasks')
            ->where('ui.common.actions.cancel', 'Cancel'));
});

test('frontend application code uses generated Wayfinder routes', function () {
    $frontendSource = collect(File::allFiles(resource_path('js')))
        ->reject(fn (SplFileInfo $file): bool => str_contains($file->getPathname(), '/routes/')
            || str_contains($file->getPathname(), '/actions/')
            || str_contains($file->getPathname(), '/wayfinder/')
            || str_contains($file->getPathname(), '/components/ui/'))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'vue'], true))
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    expect($frontendSource)
        ->not->toMatch('/\broute\s*\(/')
        ->not->toMatch('/[\'\"]\/settings(?:\/|[\'\"])/');

    expect(File::exists(resource_path('js/lib/route.ts')))->toBeFalse();
});
