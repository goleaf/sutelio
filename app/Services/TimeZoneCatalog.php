<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TimeZoneRegion;
use App\Enums\UserLanguage;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Str;
use IntlTimeZone;

class TimeZoneCatalog
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     options: list<array{value: string, label: string, identifier: string, offset: string, search_terms: string}>
     * }>
     */
    public function forLanguage(UserLanguage $language): array
    {
        /** @var array<string, list<array{value: string, label: string, identifier: string, offset: string, search_terms: string}>> $groupedOptions */
        $groupedOptions = [];

        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            $region = TimeZoneRegion::forIdentifier($identifier);
            $groupedOptions[$region->value][] = $this->option($identifier, $language);
        }

        $groups = [];

        foreach (TimeZoneRegion::cases() as $region) {
            $options = $groupedOptions[$region->value] ?? [];

            if ($options === []) {
                continue;
            }

            $this->sortOptions($options, $language);

            $groups[] = [
                'key' => $region->value,
                'label' => $region->label($language),
                'options' => $options,
            ];
        }

        return $groups;
    }

    /** @return array{value: string, label: string, identifier: string, offset: string, search_terms: string} */
    private function option(string $identifier, UserLanguage $language): array
    {
        $label = $this->localizedLabel($identifier, $language);
        $offset = $this->offset($identifier);
        $location = Str::of($identifier)
            ->afterLast('/')
            ->replace('_', ' ')
            ->headline()
            ->toString();

        return [
            'value' => $identifier,
            'label' => $label,
            'identifier' => $identifier,
            'offset' => $offset,
            'search_terms' => implode(' ', array_unique([
                $label,
                $identifier,
                $location,
                $offset,
            ])),
        ];
    }

    private function localizedLabel(string $identifier, UserLanguage $language): string
    {
        if (class_exists(IntlTimeZone::class)) {
            $timezone = IntlTimeZone::createTimeZone($identifier);
            $displayName = $timezone->getDisplayName(
                false,
                IntlTimeZone::DISPLAY_GENERIC_LOCATION,
                $language->value,
            );

            if ($displayName) {
                return $displayName;
            }
        }

        if ($identifier === 'UTC') {
            return 'UTC';
        }

        return Str::of($identifier)
            ->afterLast('/')
            ->replace('_', ' ')
            ->headline()
            ->toString();
    }

    private function offset(string $identifier): string
    {
        $seconds = (new DateTimeZone($identifier))->getOffset(new DateTimeImmutable('now'));
        $sign = $seconds >= 0 ? '+' : '-';
        $absoluteSeconds = abs($seconds);
        $hours = intdiv($absoluteSeconds, 3600);
        $minutes = intdiv($absoluteSeconds % 3600, 60);

        return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
    }

    /**
     * @param  list<array{value: string, label: string, identifier: string, offset: string, search_terms: string}>  $options
     */
    private function sortOptions(array &$options, UserLanguage $language): void
    {
        $collator = class_exists(\Collator::class)
            ? new \Collator($language->value)
            : null;

        usort($options, static function (array $first, array $second) use ($collator): int {
            $comparison = $collator?->compare($first['label'], $second['label']);

            if (is_int($comparison)) {
                return $comparison;
            }

            return strnatcasecmp($first['label'], $second['label']);
        });
    }
}
