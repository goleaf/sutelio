# Integrations

## Current Integrations

- Fortify, passkeys, and Sanctum provide first-party identity/security boundaries.
- Laravel database notifications and configured mail delivery support invitations/reminders.
- NativePHP Mobile packages the Laravel application for Android/iOS. NativePHP v4 currently embeds PHP 8.4 even when web/development runs PHP 8.5.
- Configured Laravel filesystems store private avatars, attachments, import fixtures, and backups.

There is no current payment, webhook, AI, vector-search, semantic-search, realtime-provider, social-login, public media CDN, or user-controlled server-side URL-fetch integration.

Any substantial external HTTP integration must use Laravel's HTTP client or a justified vendor SDK behind one explicit client/gateway. Configuration comes through config files; define base URL, credentials, connection/total timeouts, safe retry/idempotency rules, rate-limit/error mapping, response-shape validation into typed data, size expectations, secret-redacted logs, and tests with `Http::preventStrayRequests()`/fakes.

NativePHP upgrades require inspection of official upgrade/configuration documentation, preservation of app ID/version/min SDK/permissions, regeneration implications, a production web build, focused PHP tests, and APK manifest verification.
