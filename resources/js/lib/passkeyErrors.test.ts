import assert from 'node:assert/strict';
import test from 'node:test';
import {
    InvalidDomainError,
    NotSupportedError,
    PasskeyError,
    PasskeyExistsError,
    UserCancelledError,
} from '@laravel/passkeys';
import {
    getDefaultPasskeyName,
    localizePasskeyError,
} from './passkeyErrors.ts';

const translate = (
    key: string,
    replacements: Record<string, string | number> = {},
): string => {
    const messages: Record<string, string> = {
        'account.passkeys.default_name': ':browser — :device',
        'account.passkeys.errors.cancelled': 'cancelled',
        'account.passkeys.errors.exists': 'exists',
        'account.passkeys.errors.invalid_domain': 'invalid-domain',
        'account.passkeys.errors.unexpected': 'unexpected',
        'account.passkeys.errors.unsupported': 'unsupported',
    };

    return Object.entries(replacements).reduce(
        (message, [name, value]) =>
            message.replaceAll(`:${name}`, String(value)),
        messages[key] ?? key,
    );
};

test('typed passkey failures use semantic localized messages', () => {
    const cases = [
        [new UserCancelledError(), 'cancelled'],
        [new NotSupportedError(), 'unsupported'],
        [new PasskeyExistsError(), 'exists'],
        [new InvalidDomainError('example.test'), 'invalid-domain'],
        [new PasskeyError('Untrusted server error'), 'unexpected'],
        [new Error('Untrusted browser error'), 'unexpected'],
    ] as const;

    for (const [error, expected] of cases) {
        const localized = localizePasskeyError(error, translate);

        assert.equal(localized, expected);
        assert.notEqual(localized, error.message);
    }

    assert.equal(localizePasskeyError(null, translate), null);
});

test('default passkey names contain no hardcoded English connector', () => {
    const userAgent =
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/140.0.0.0 Safari/537.36';

    assert.equal(
        getDefaultPasskeyName(userAgent, translate),
        'Chrome — Windows',
    );
});
