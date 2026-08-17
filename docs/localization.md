# Localization

## Supported Contract

English (`en`), Lithuanian (`lt`), and Russian (`ru`) are supported with English fallback. PHP catalogs under `lang/{locale}` are the single translation source and are shared with Inertia; do not create a parallel JSON/JavaScript catalog.

`Sutelio` is a proper noun and remains exactly `Sutelio` in English, Lithuanian, and Russian. Translate complete surrounding sentences and apply locale-appropriate grammar and punctuation there; do not translate, transliterate, inflect, or concatenate the product name itself.

Locale resolution follows authenticated account preference, encrypted five-year device cookie, session, browser `Accept-Language`, then English fallback. Browser language is only the initial presentation hint: a guest without a prior explicit device/session choice receives the mandatory first-run dialog. A confirmed guest choice is stored in session and the encrypted HTTP-only cookie; login synchronizes the account preference back to the device, registration persists the selected locale, and authenticated changes update both account and device state.

The server shares the selected locale, first-run state, extensible language option catalog, owned SVG flag URLs, and bounded first-run preview copy with Inertia. Auth and authenticated shells use the same selector. The first-run dialog translates its own complete copy immediately while the user compares choices, then confirmation triggers a normal Inertia locale update so the complete application, document `lang`, formatting, persistent layout headings, Settings, and onboarding refresh together. NativePHP phone/tablet builds use this same Laravel/session/cookie boundary rather than a second native preference implementation.

The selected locale and text direction are shared to the frontend and the Inertia Blade document language. User timezone, date format, time format, and first-day-of-week preferences drive presentation and query boundaries; canonical timestamps remain storage values.

## Contributor Workflow

1. Add a stable semantic domain key to English.
2. Add the same nested key and placeholder set to Lithuanian and Russian.
3. Translate complete sentences; do not concatenate fragments or derive labels by title-casing stored enum values.
4. Use pluralization and named placeholders where grammar or counts vary.
5. Use shared frontend/server formatters for date, time, relative time, number, percentage, and list output.
6. Translate labels, placeholders, actions, headings, loading/empty/error/success/confirmation text, validation, notifications/email, page titles, accessibility names, and end-user API messages.
7. Add the language to `UserLanguage`, the matching frontend union, owned flag asset, and bounded preview/name catalogs when extending supported locales.
8. Run `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/LanguageSelectionTest.php tests/Feature/LanguageSelectionFrontendTest.php` plus affected page/notification tests, then frontend type/lint/format/build and desktop/phone/tablet browser checks.

Tests verify locale key shape, placeholder parity, fallback, critical page rendering, localized validation and notifications, and locale/timezone-aware output. A missing human-quality translation must retain key parity and use the documented fallback honestly; it must never return a raw key in a critical flow.

Notification rows and browser delivery must call the shared typed content resolver so reminder titles/bodies and safe fallbacks cannot diverge. Today/Earlier grouping uses the server-provided date key derived from the saved IANA timezone; browser-local clock/timezone guesses are not a localization boundary.

Guided onboarding uses the dedicated `lang/{locale}/onboarding.php` semantic catalog for every step, action, save/recovery state, validation summary, role-aware safety explanation, and result fact. Language selection persists through the canonical preference action and immediately renders the redirected step in the chosen locale. Result totals use locale-aware one/few/many/other keys; step titles, descriptions, and actions are complete messages rather than concatenated fragments.
