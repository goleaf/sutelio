# Integrations

## Current Integrations

- Fortify without email verification, passkeys, and Sanctum provide first-party identity/security boundaries.
- Laravel database notifications and configured web mail delivery support invitations/reminders. NativePHP uses the non-logging `array` mailer and strips bundled `MAIL_*` values so signed links and transport credentials are not stored on-device.
- NativePHP Mobile 4.2 packages the Laravel application for Android/iOS. The generated shells and tracked v4 runtime were force-refreshed on 2026-08-18 with embedded PHP 8.5.9, fixed light appearance, and the optional FPS overlay off; official v4 documentation still describes PHP 8.4, so the broader compatibility envelope remains intentional.
- The allowlisted first-party `goleaf/nativephp-email-picker` NativePHP plugin opens Android's system account chooser with no account/contact permission and returns only the user-selected email to the current WebView. It does not implement social login, request credentials/tokens, enumerate installed applications/accounts, or add an iOS integration.
- Configured Laravel filesystems store private avatars, attachments, import fixtures, and backups.

There is no current payment, webhook, AI, vector-search, semantic-search, realtime-provider, social-login, public media CDN, or user-controlled server-side URL-fetch integration.

Any substantial external HTTP integration must use Laravel's HTTP client or a justified vendor SDK behind one explicit client/gateway. Configuration comes through config files; define base URL, credentials, connection/total timeouts, safe retry/idempotency rules, rate-limit/error mapping, response-shape validation into typed data, size expectations, secret-redacted logs, and tests with `Http::preventStrayRequests()`/fakes.

NativePHP upgrades require inspection of official upgrade/configuration documentation, preservation of app ID/version/min SDK/permissions, regeneration implications, a production web build, focused PHP tests, and APK manifest verification.
