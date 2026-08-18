<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('language controls are shared by guest and authenticated layouts', function () {
    $authLayout = File::get(resource_path('js/layouts/auth/AuthSimpleLayout.vue'));
    $header = File::get(resource_path('js/components/AppSidebarHeader.vue'));

    expect(File::exists(resource_path('js/components/localization/LanguageSwitcher.vue')))->toBeTrue()
        ->and(File::exists(resource_path('js/components/localization/FirstRunLanguageDialog.vue')))->toBeTrue()
        ->and($authLayout)->toContain('<LanguageSwitcher')
        ->and($header)->toContain('<LanguageSwitcher');
});

test('first run dialog is mandatory accessible responsive and reduced motion safe', function () {
    $dialog = File::get(resource_path('js/components/localization/FirstRunLanguageDialog.vue'));
    $css = File::get(resource_path('css/app.css'));

    expect($dialog)
        ->toContain('localization.requires_selection')
        ->toContain('selectedPreview')
        ->toContain('previewCopy')
        ->toContain('selectedPreview.language_names[option.code]')
        ->not->toContain("t('localization.first_run.title')")
        ->not->toContain("t('localization.first_run.description')")
        ->not->toContain("t('localization.continue')")
        ->toContain('@escape-key-down.prevent')
        ->toContain('@pointer-down-outside.prevent')
        ->toContain(':show-close-button="false"')
        ->toContain('<DialogTitle')
        ->toContain('<DialogDescription')
        ->toContain('aria-live="polite"')
        ->toContain('motion-reduce:')
        ->toContain('data-slot="first-run-language-dialog"')
        ->toContain('data-slot="first-run-language-header"')
        ->toContain('data-slot="first-run-language-form"')
        ->toContain('data-slot="first-run-language-option"')
        ->toContain('text-base leading-7')
        ->toContain('text-[0.9375rem]')
        ->toContain('min-h-13')
        ->and($css)
        ->toContain('@media (orientation: landscape) and (max-height: 32rem) and (min-width: 40rem)')
        ->toContain("[data-slot='first-run-language-dialog']")
        ->toContain('grid-template-columns: minmax(14rem, 0.8fr) minmax(22rem, 1.2fr)')
        ->toContain("[data-slot='first-run-language-form']");
});

test('language switcher uses owned flag assets and the shared locale endpoint', function () {
    $switcher = File::get(resource_path('js/components/localization/LanguageSwitcher.vue'));
    $languagePreference = File::get(resource_path('js/composables/useLanguagePreference.ts'));
    $flags = collect(['gb', 'lt', 'ru'])
        ->map(fn (string $code): string => public_path("images/flags/{$code}.svg"));

    expect($flags->every(fn (string $path): bool => File::exists($path)))->toBeTrue()
        ->and($languagePreference)
        ->toContain("from '@/routes/locale'")
        ->toContain('form.submit(update()')
        ->and($switcher)
        ->toContain('localization.options')
        ->toContain('DropdownMenuRadioGroup')
        ->toContain('text-[0.9375rem]')
        ->toContain('pointer-coarse:min-h-12')
        ->toContain('aria-label')
        ->not->toMatch('/[🇬🇧🇱🇹🇷🇺]/u');
});

test('registration and document locale use the shared localization state', function () {
    $registration = File::get(resource_path('js/pages/auth/Register.vue'));
    $application = File::get(resource_path('js/app.ts'));
    $ui = File::get(resource_path('js/composables/useUi.ts'));

    expect($registration)
        ->toContain('name="language"')
        ->toContain('localization.current')
        ->and($application)
        ->toContain('page.props.localization.current')
        ->not->toContain('preferences?.language')
        ->and($ui)
        ->toContain('page.props.localization.current');
});

test('settings and onboarding reuse the shared extensible language catalog', function () {
    $settings = File::get(resource_path('js/pages/settings/Preferences.vue'));
    $onboarding = File::get(resource_path('js/components/onboarding/PreferencesStep.vue'));

    expect($settings)
        ->toContain('.localization.options')
        ->toContain('<LanguageFlag')
        ->toContain('useLanguagePreference')
        ->toContain('saveLanguage')
        ->toContain('@update:model-value="handleLanguageChange"')
        ->not->toContain("{ value: 'en'")
        ->and($onboarding)
        ->toContain('.localization.options')
        ->toContain('<LanguageFlag')
        ->toContain('useLanguagePreference')
        ->toContain('saveLanguage')
        ->toContain('@update:model-value="handleLanguageChange"');
});

test('translated persistent layout props react to live locale changes', function () {
    $pages = collect([
        ...File::glob(resource_path('js/pages/auth/*.vue')),
        ...File::glob(resource_path('js/pages/settings/*.vue')),
    ])->filter(fn (string $path): bool => str_contains(File::get($path), 'setLayoutProps'));

    expect($pages)->not->toBeEmpty();

    $pages->each(function (string $path): void {
        $source = File::get($path);

        expect($source, basename($path))
            ->toContain('watchEffect')
            ->toMatch('/watchEffect\(\(\) => \{\s*setLayoutProps/s');
    });
});
