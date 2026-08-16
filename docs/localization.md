# Localization

## Supported Contract

English (`en`), Lithuanian (`lt`), and Russian (`ru`) are supported with English fallback. PHP catalogs under `lang/{locale}` are the single translation source and are shared with Inertia; do not create a parallel JSON/JavaScript catalog.

Locale comes from the authenticated user's preference or the documented fallback. The selected locale and text direction are shared to the frontend and the Inertia Blade document language. User timezone, date format, time format, and first-day-of-week preferences drive presentation and query boundaries; canonical timestamps remain storage values.

## Contributor Workflow

1. Add a stable semantic domain key to English.
2. Add the same nested key and placeholder set to Lithuanian and Russian.
3. Translate complete sentences; do not concatenate fragments or derive labels by title-casing stored enum values.
4. Use pluralization and named placeholders where grammar or counts vary.
5. Use shared frontend/server formatters for date, time, relative time, number, percentage, and list output.
6. Translate labels, placeholders, actions, headings, loading/empty/error/success/confirmation text, validation, notifications/email, page titles, accessibility names, and end-user API messages.
7. Run `php artisan test --compact tests/Feature/FrontendLocalizationTest.php` plus affected page/notification tests, then frontend type/lint/format/build checks.

Tests verify locale key shape, placeholder parity, fallback, critical page rendering, localized validation and notifications, and locale/timezone-aware output. A missing human-quality translation must retain key parity and use the documented fallback honestly; it must never return a raw key in a critical flow.

Notification rows and browser delivery must call the shared typed content resolver so reminder titles/bodies and safe fallbacks cannot diverge. Today/Earlier grouping uses the server-provided date key derived from the saved IANA timezone; browser-local clock/timezone guesses are not a localization boundary.
