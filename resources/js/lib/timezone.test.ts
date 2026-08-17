import assert from 'node:assert/strict';
import test from 'node:test';
import * as timezone from './timezone.ts';

const { detectBrowserTimezone } = timezone;

test('timezone detection module is available to browser entry points', async () => {
    const timezoneModule = await import('./timezone.ts').catch(() => null);

    assert.notEqual(timezoneModule, null);
});

test('browser detection returns a valid system IANA timezone', () => {
    assert.equal(
        detectBrowserTimezone(() => 'Europe/Vilnius'),
        'Europe/Vilnius',
    );
});

test('browser detection rejects missing and invalid timezone values', () => {
    assert.equal(
        detectBrowserTimezone(() => undefined),
        null,
    );
    assert.equal(
        detectBrowserTimezone(() => 'Not/A-Timezone'),
        null,
    );
    assert.equal(
        detectBrowserTimezone(() => {
            throw new Error('Intl unavailable');
        }),
        null,
    );
});

test('browser Intl localizes timezone labels in every supported language', () => {
    assert.equal(typeof timezone.localizeTimeZoneName, 'function');

    const expectations = [
        ['en', /Time|Europe|Lithuania/i],
        ['lt', /Europos|Lietuvos/i],
        ['ru', /[А-Яа-яЁё]/],
    ] as const;

    for (const [locale, expectedScript] of expectations) {
        const localized = timezone.localizeTimeZoneName(
            'Europe/Vilnius',
            locale,
            'Vilnius',
        );

        assert.notEqual(localized, 'Vilnius');
        assert.match(localized, expectedScript);
    }
});

test('timezone label localization retains its safe fallback', () => {
    assert.equal(typeof timezone.localizeTimeZoneName, 'function');
    assert.equal(
        timezone.localizeTimeZoneName('Not/A-Timezone', 'ru-RU', 'Fallback'),
        'Fallback',
    );
});
