<?php

declare(strict_types=1);

namespace App\Enums;

enum UserLanguage: string
{
    case English = 'en';
    case Lithuanian = 'lt';
    case Russian = 'ru';

    public function nativeName(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Lithuanian => 'Lietuvių',
            self::Russian => 'Русский',
        };
    }

    public function flagUrl(): string
    {
        return match ($this) {
            self::English => '/images/flags/gb.svg',
            self::Lithuanian => '/images/flags/lt.svg',
            self::Russian => '/images/flags/ru.svg',
        };
    }

    /** @return 'sunday'|'monday' */
    public function defaultWeekStart(): string
    {
        return match ($this) {
            self::English => 'sunday',
            self::Lithuanian, self::Russian => 'monday',
        };
    }

    public function localizedName(self $displayLanguage): string
    {
        return $displayLanguage->translation("ui.settings.preferences.languages.{$this->value}");
    }

    /**
     * @return array{
     *     choose: string,
     *     continue: string,
     *     first_run: array{description: string, title: string},
     *     saving: string,
     *     switcher_label: string
     * }
     */
    public function localizationCopy(): array
    {
        return [
            'choose' => $this->translation('ui.localization.choose'),
            'continue' => $this->translation('ui.localization.continue'),
            'first_run' => [
                'description' => $this->translation('ui.localization.first_run.description'),
                'title' => $this->translation('ui.localization.first_run.title'),
            ],
            'saving' => $this->translation('ui.localization.saving'),
            'switcher_label' => $this->translation('ui.localization.switcher_label'),
        ];
    }

    /**
     * @return list<array{code: string, native_name: string, localized_name: string, flag_url: string, default_week_start: 'sunday'|'monday'}>
     */
    public static function frontendOptions(): array
    {
        return array_map(
            fn (self $language): array => [
                'code' => $language->value,
                'native_name' => $language->nativeName(),
                'localized_name' => __("ui.settings.preferences.languages.{$language->value}"),
                'flag_url' => $language->flagUrl(),
                'default_week_start' => $language->defaultWeekStart(),
            ],
            self::cases(),
        );
    }

    /**
     * @return array<string, array{
     *     copy: array{
     *         choose: string,
     *         continue: string,
     *         first_run: array{description: string, title: string},
     *         saving: string,
     *         switcher_label: string
     *     },
     *     language_names: array<string, string>
     * }>
     */
    public static function frontendPreviews(): array
    {
        $previews = [];

        foreach (self::cases() as $displayLanguage) {
            $languageNames = [];

            foreach (self::cases() as $language) {
                $languageNames[$language->value] = $language->localizedName($displayLanguage);
            }

            $previews[$displayLanguage->value] = [
                'copy' => $displayLanguage->localizationCopy(),
                'language_names' => $languageNames,
            ];
        }

        return $previews;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    private function translation(string $key): string
    {
        $translation = trans($key, [], $this->value);

        return is_string($translation) ? $translation : $key;
    }
}
