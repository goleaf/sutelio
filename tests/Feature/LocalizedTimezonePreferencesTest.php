<?php

declare(strict_types=1);

use App\Enums\TimeZoneRegion;
use App\Enums\UserLanguage;
use App\Services\TimeZoneCatalog;

test('user languages own their default first day of the week', function (UserLanguage $language, string $weekStart) {
    expect($language->defaultWeekStart())->toBe($weekStart);
})->with([
    'English starts on Sunday' => [UserLanguage::English, 'sunday'],
    'Lithuanian starts on Monday' => [UserLanguage::Lithuanian, 'monday'],
    'Russian starts on Monday' => [UserLanguage::Russian, 'monday'],
]);

test('timezone regions expose translated labels from their enum', function () {
    expect(TimeZoneRegion::Europe->label(UserLanguage::English))->toBe('Europe')
        ->and(TimeZoneRegion::Europe->label(UserLanguage::Lithuanian))->toBe('Europa')
        ->and(TimeZoneRegion::Europe->label(UserLanguage::Russian))->toBe('Европа')
        ->and(TimeZoneRegion::Utc->label(UserLanguage::Russian))->toBe('Всемирное время');
});

test('timezone catalog localizes options and preserves searchable IANA identifiers', function () {
    $groups = app(TimeZoneCatalog::class)->forLanguage(UserLanguage::Russian);
    $europe = collect($groups)->firstWhere('key', 'europe');

    expect($europe)->toBeArray()
        ->and($europe['label'])->toBe('Европа');

    $vilnius = collect($europe['options'])->firstWhere('value', 'Europe/Vilnius');

    expect($vilnius)->toBeArray()
        ->and($vilnius['identifier'])->toBe('Europe/Vilnius')
        ->and($vilnius['offset'])->toMatch('/^UTC[+-]\d{2}:\d{2}$/')
        ->and($vilnius['search_terms'])->toContain(
            'Europe/Vilnius',
            'Vilnius',
            $vilnius['label'],
        );

    if (class_exists(IntlTimeZone::class)) {
        expect($vilnius['label'])->toMatch('/[А-Яа-яЁё]/');
    } else {
        expect($vilnius['label'])->toBe('Vilnius');
    }
});

test('timezone catalog includes every canonical identifier exactly once', function () {
    $values = collect(app(TimeZoneCatalog::class)->forLanguage(UserLanguage::English))
        ->flatMap(fn (array $group): array => array_column($group['options'], 'value'))
        ->values();
    $canonicalIdentifiers = collect(DateTimeZone::listIdentifiers())->sort()->values();

    expect($values)->toHaveCount($canonicalIdentifiers->count())
        ->and($values->unique())->toHaveCount($canonicalIdentifiers->count())
        ->and($values->sort()->values()->all())->toBe($canonicalIdentifiers->all());
});

test('UTC remains available in its own explicit translated group', function () {
    $groups = app(TimeZoneCatalog::class)->forLanguage(UserLanguage::Lithuanian);
    $utc = collect($groups)->firstWhere('key', 'utc');

    expect($utc)->toBeArray()
        ->and($utc['label'])->toBe('Pasaulinis laikas')
        ->and($utc['options'])->toHaveCount(1)
        ->and($utc['options'][0]['value'])->toBe('UTC');
});
