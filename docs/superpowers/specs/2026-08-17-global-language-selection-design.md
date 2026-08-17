# Global Language Selection Design

## Goal

Provide an obvious language switcher at the top of guest and authenticated pages, require a language choice on the first application visit, and persist later changes consistently across web and NativePHP phone/tablet use.

## Product Contract

- English (`en`), Lithuanian (`lt`), and Russian (`ru`) remain the supported languages with English fallback.
- A first visit without an authenticated preference or prior device choice opens a blocking, accessible language dialog before the page can be used.
- The dialog starts in the best supported `Accept-Language` locale, presents every language by its native name and a project-owned SVG flag, and requires an explicit confirmation.
- A compact language dropdown remains visible in the top region of login, registration, password, two-factor, onboarding, application, and settings pages.
- Guest selections persist on the device in an encrypted, long-lived, same-site cookie and in the current session.
- Authenticated selections additionally persist in `user_preferences.language`. An account preference always outranks a stale device/browser choice.
- Registration copies the already selected guest language into the new preference row. Login synchronizes the device cookie to the account language so logout and the next launch remain coherent.
- Changing language performs one server-authoritative Inertia request, reloads translated shared props, updates the document `lang`, and preserves the current safe application destination.
- Invalid language codes never persist and return localized validation feedback without changing the active language.

## Architecture

`App\Enums\UserLanguage` remains the source of truth for supported codes and exposes the native name and project-owned flag asset. `App\Services\LocalePreference` resolves locale precedence, determines whether first-run selection is required, updates the request session, and creates the encrypted persistence cookie. `SetLocale` delegates resolution to that service before Inertia shared translations are built.

A guest-accessible, throttled, named locale endpoint uses a Form Request, a thin controller, and the existing `UpdateUserPreferences` action for authenticated persistence. The controller returns only a safe back redirect with the locale cookie; it never accepts an arbitrary redirect URL. `HandleInertiaRequests` shares a bounded `localization` contract containing the current code, first-run requirement, supported options, and first-run preview copy; the frontend reaches the endpoint through generated Wayfinder functions.

The frontend uses one `LanguageSwitcher.vue` component in both shell entry points. It owns the dropdown and composes `FirstRunLanguageDialog.vue`; both consume the same shared contract and `useForm`. `LanguageFlag.vue` renders only the server-provided first-party asset URL. No Pinia store, localStorage catalog, duplicated language array, Axios request, or raw route string is introduced.

## Resolution And Persistence

Locale resolution order is:

1. valid authenticated `user_preferences.language`;
2. valid encrypted `sutelio_locale` device cookie;
3. valid locale already stored in the Laravel session;
4. the best supported `Accept-Language` value;
5. configured English fallback.

The first-run dialog is required only when neither an authenticated preference nor an explicit device/session selection exists. Browser detection controls the initial presentation language but does not count as consent and does not suppress the dialog.

The cookie is encrypted by Laravel's existing middleware, `HttpOnly`, `SameSite=Lax`, secure when the request is secure, and long-lived. It contains only one allowlisted language code and no personal data. NativePHP PHP-mode web views share the Laravel session; mobile verification must additionally prove persistence after process restart.

## UI And Motion

- The first-run dialog uses the existing Reka dialog primitive, focus trap, labelled title/description, Escape/outside-click prevention, and a visible confirmation button.
- Language options are large keyboard-operable cards with native names, localized names, SVG flags, and a non-color selected marker.
- The dialog uses the existing Warm Precision surface, orange/cobalt accents, a restrained fade/scale and staggered option entrance, and the global reduced-motion clamp.
- The dropdown trigger is at least 44 px, shows the current flag and language code/name where space allows, and exposes a translated accessible name.
- Guest pages position it in a safe-area-aware top bar; authenticated pages place it at the trailing side of the persistent sticky header.
- At 390 px and tablet widths the dialog fits dynamic viewport bounds without horizontal overflow or keyboard clipping.

## Failure And Concurrency Behavior

- While a selection request is processing, language controls are disabled and show an action-specific spinner/status.
- A failed request keeps the dialog/dropdown usable, announces a localized error, and does not optimistically change document language.
- Selection requests are serialized: every language control is disabled while its request is active, so concurrent or superseded writes are not emitted.
- The server remains authoritative; cookies, session values, route payloads, and browser-held locale codes are validated against `UserLanguage`.
- The feature adds no Eloquent query for guests. Authenticated requests reuse the already required preference relation, so page query budgets remain unchanged.

## Migration And Existing Users

No schema change is required. Existing users already have `user_preferences.language`; that value is treated as their established account preference. New registrations receive the explicitly selected guest language instead of the former unconditional English default. Factory and seeder defaults remain deterministic.

## Verification

- Pest coverage: precedence, first-run shared contract, encrypted cookie persistence, invalid values, authenticated persistence, account-over-cookie priority, registration inheritance, login synchronization, route throttling, and existing locale fallback.
- Frontend source/behavior coverage: shared catalog rendering, flag assets, required dialog semantics, processing/failure states, no duplicated language list, auth/application placement, reduced motion, and document language synchronization.
- Browser coverage: login and registration plus an authenticated page at desktop, 390 px phone, and tablet viewport; keyboard traversal, focus trap/return, long Russian/Lithuanian copy, no overflow, and zero current console/network errors.
- NativePHP coverage: Android phone and tablet build/install/cold-launch checks, first-run selection, restart persistence, registration/login/account precedence, and responsive layout. iOS simulator verification is run when the installed toolchain and generated platform permit it; any unavailable external device/tool is reported factually.

## Delivery Boundary

Implementation stays on `main`, preserves the concurrent light-mode/Signal Orange/native regeneration work, updates canonical localization/architecture/testing/requirements/compliance/progress documents, runs the full applicable backend/frontend/data/mobile gates, commits only attributable language-selection changes, and pushes normally without rewriting history.
