import {
    InvalidDomainError,
    NotSupportedError,
    PasskeyExistsError,
    UserCancelledError,
} from '@laravel/passkeys';

type Translate = (
    key: string,
    replacements?: Record<string, number | string>,
) => string;

export function localizePasskeyError(
    error: unknown,
    translate: Translate,
): string | null {
    if (error === null || error === undefined) {
        return null;
    }

    if (error instanceof UserCancelledError) {
        return translate('account.passkeys.errors.cancelled');
    }

    if (error instanceof NotSupportedError) {
        return translate('account.passkeys.errors.unsupported');
    }

    if (error instanceof PasskeyExistsError) {
        return translate('account.passkeys.errors.exists');
    }

    if (error instanceof InvalidDomainError) {
        return translate('account.passkeys.errors.invalid_domain');
    }

    return translate('account.passkeys.errors.unexpected');
}

export function getDefaultPasskeyName(
    userAgent: string,
    translate: Translate,
): string {
    const browser = [
        { pattern: /Edg|Edge/, name: 'Edge' },
        { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
        { pattern: /Firefox|FxiOS/, name: 'Firefox' },
        { pattern: /Chrome|CriOS/, name: 'Chrome' },
        { pattern: /Safari/, name: 'Safari' },
    ].find(({ pattern }) => pattern.test(userAgent))?.name;

    const device = [
        { pattern: /iPhone/, name: 'iPhone' },
        { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
        { pattern: /Android/, name: 'Android' },
        { pattern: /Mac/, name: 'Mac' },
        { pattern: /Windows/, name: 'Windows' },
    ].find(({ pattern }) => pattern.test(userAgent))?.name;

    if (browser && device) {
        return translate('account.passkeys.default_name', {
            browser,
            device,
        });
    }

    return browser ?? device ?? '';
}
