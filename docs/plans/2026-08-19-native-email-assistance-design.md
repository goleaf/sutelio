# Native Email Assistance Design

Date: 2026-08-19
Status: Approved for implementation

## Problem

Typing an email address on a phone is avoidable friction, especially for older users. Android does not expose a safe, permission-free API for silently reading every email address from every installed application. Broad account enumeration would also violate Sutelio's privacy and least-privilege boundaries.

Sutelio therefore needs two distinct, explicit sources:

1. email addresses that the user has already used successfully inside this Sutelio installation; and
2. one Android account address that the user deliberately selects in the operating-system account chooser.

Manual entry must remain available at all times. No password, token, session identifier, account type, contact, application inventory, or complete device account list may be stored or presented.

## Selected Privacy Contract

- Successful Sutelio authentication remembers only the authenticated user's normalized email address in a MAC-authenticated Laravel ciphertext inside the application-private sandbox. NativePHP protects the per-device `APP_KEY` used for encryption in platform secure storage.
- The encrypted device-local list is most-recent-first, case-insensitively de-duplicated, and capped at five entries.
- Login prefills the most recent remembered address and exposes all remembered entries as inline choices.
- Registration does not expose remembered Sutelio accounts automatically; it starts empty so an existing account is not accidentally re-registered.
- Login and registration on Android expose an explicit “Choose from this phone” action.
- That action launches Android's system-owned account chooser. Sutelio receives only the single address selected by the user, or a cancellation result.
- The Android plugin declares no `GET_ACCOUNTS`, `READ_CONTACTS`, or other account/contact permission and never calls broad account enumeration APIs.
- Logout keeps the remembered addresses. App updates and ordinary restarts preserve them. Clearing app data or uninstalling removes them with the application sandbox.
- A remembered address can be removed inline from the login page without a modal dialog.
- Web and iOS retain a fully functional manual field. iOS may use the same Sutelio-only encrypted history, but does not show the Android chooser.
- Email verification remains permanently disabled and is not introduced by this feature.

## Architecture

### Secure Sutelio history

`RememberedEmailStore` owns the versioned payload. `EncryptedRememberedEmailStorage` encrypts and authenticates it with Laravel's device-keyed encrypter, atomically replaces a fixed application-private file with `0600` permissions, and never sends an address through native bridge parameters. Reads distinguish missing storage from unavailable or tampered ciphertext so unreadable protected data is never overwritten. The store validates every value on read and write, ignores corrupt or unsupported decoded data safely, and has no database dependency.

An `Illuminate\Auth\Events\Login` listener records the address from the authenticated `User` model. This single post-authentication boundary covers password login, completed two-factor login, registration login, and passkey login without trusting the submitted email string. Failed authentication never changes the history.

Fortify login and registration views receive bounded native-assistance props. A guest-only, throttled, CSRF-protected delete endpoint accepts an email in the request body and removes it from protected device history without putting personal data in a URL.

### Android chooser bridge

NativePHP v4 bridge calls return synchronously, while Android's account chooser returns asynchronously. The first-party `goleaf/nativephp-email-picker` plugin therefore uses this lifecycle:

1. `EmailPicker.Choose` schedules work on the Android main thread and immediately confirms that the chooser was launched.
2. A headless Fragment owns `ActivityResultContracts.StartActivityForResult` and opens `AccountManager.newChooseAccountIntent` with the modern API 23+ overload.
3. Android renders the system chooser and grants Sutelio visibility only to the account explicitly selected by the user.
4. The Fragment validates and trims `AccountManager.KEY_ACCOUNT_NAME`, then dispatches an application-scoped DOM event directly to the existing WebView.
5. The Vue adapter resolves the pending request with one email or `null`; raw addresses are not logged or dispatched through the PHP event endpoint.

The plugin is Android-only. iOS therefore has no bridge implementation or chooser control and keeps the same manual field plus Sutelio-only encrypted history. The plugin is explicitly allowlisted in `NativeServiceProvider`; transitive plugins remain blocked.

### Shared interface

One `AuthEmailAssistant` Vue component owns the email field, remembered choices, inline removal, device chooser trigger, loading/cancellation/error feedback, and accessible labels. Login and registration compose it with different modes rather than duplicating behavior.

Controls keep the Warm Precision light-only design, at least 48-pixel phone targets, visible focus, non-color selected state, live status feedback, reduced-motion safety, forced-colors boundaries, and EN/LT/RU semantic translations. The normal email input and platform autofill remain available even when native assistance fails.

## Threat And Failure Model

- Forged, malformed, or unsupported decoded JSON is filtered to at most five valid email strings; tampered ciphertext fails MAC validation and is not exposed or overwritten.
- A locked, unavailable, or failed secure store is treated as transient; writes do not replace unreadable data.
- A forged delete request is constrained by CSRF, guest middleware, validation, throttling, and a device-local operation with no database effect.
- Chooser cancellation is normal and non-destructive.
- Concurrent chooser launches are rejected while one request is active.
- Missing bridge/plugin, activity loss, malformed native payload, and timeouts produce localized inline feedback while preserving manual entry.
- The native source contains no email logging and the manifest contains no account/contact permissions.
- No new query, schema, migration, seed, workspace, onboarding, email-verification, or external-network contract is introduced.

## Test-First Implementation Sequence

1. Add failing Pest contracts for native-only Fortify props, post-login persistence, failed-login non-persistence, bounded/de-duplicated history, safe corrupt/unavailable storage, inline removal, plugin allowlisting, and permission-free native source.
2. Add failing frontend tests for the bridge event adapter, cancellation, invalid payload, timeout/cleanup, and one-active-request behavior.
3. Implement `RememberedEmailStore`, the post-authentication listener, the authorized request/controller/route, and Fortify props; regenerate Wayfinder.
4. Implement and validate the Android-only first-party NativePHP plugin with the system chooser, direct WebView result event, no permissions, and explicit provider allowlisting.
5. Implement the shared Vue assistant, integrate login/registration, and add complete EN/LT/RU copy and accessibility/source contracts.
6. Run focused RED/GREEN checks, Pint, Larastan, the complete Pest suite, frontend tests, Vue type checking, ESLint, Prettier, Composer/npm audits, fresh SQLite migration/seeding/health, and production web/Android/iOS Vite builds.
7. Verify login and registration in isolated Chrome DevTools and Playwright across EN/LT/RU phone/tablet/desktop states, including manual fallback, remembered choices, deletion, keyboard/touch, reduced motion, forced colors, and no overflow.
8. Regenerate NativePHP projects, validate the plugin, build and inspect a fresh APK, verify on a disposable Android emulator, publish the reviewed source, then update-install the exact APK on the single connected Samsung with `adb install -r` and no app-data clear.

## Delivery Boundary

Physical Samsung installation is the final mutation. It is permitted only after source, tests, browser checks, native compilation, artifact inspection, emulator proof, scoped commit, normal `origin/main` push, and exact local/tracking/advertised equality all pass. The existing Samsung application sandbox must be preserved for this feature.
