# Deployment

## Web Requirements

- PHP 8.5 with required Laravel extensions including PDO SQLite/SQLite3, Mbstring, OpenSSL, JSON, Fileinfo, Tokenizer, XML, Ctype, and Curl as used by installed packages.
- Composer 2, Node 22, and npm for reproducible asset builds.
- A local SQLite-compatible persistent filesystem for the database/WAL/SHM and private writable storage for cache, sessions, queue, uploads, and backups.
- HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, secure cookie/session configuration, correct `APP_URL`, mail configuration where invitations/reminders are enabled, and no demo credentials.

## Release Sequence

```bash
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan app:database-health
```

Do not run `db:seed` in production unless an explicitly reviewed idempotent reference seeder is selected; never run the full demo `DatabaseSeeder` or `migrate:fresh`. Keep a consistent external backup before migrations, deploy populated-safe migrations, and retain the previous release for rollback. Rollback must account for code/schema compatibility; do not blindly run destructive down migrations.

The onboarding schema migration is additive and populated-safe: existing preference rows are backfilled complete/dismissed, while only new Fortify registrations enter the required journey. Deploy the migration before serving onboarding assets, retain session storage across the release so in-progress users can resume, and do not reset onboarding lifecycle columns during rollback or replay support work.

The email-verification removal migration intentionally drops `users.email_verified_at` after the Fortify feature, routes, middleware, notifications, UI, and public props are removed. Deploy the application and migration together. Its rollback recreates an empty nullable compatibility column but cannot reconstruct discarded timestamps; that data loss is an approved product decision.

Configure the scheduler and, when reminder delivery is queued, a supervised bounded database queue worker. Monitor application logs, failed jobs, disk space, SQLite health, backup results, and scheduler freshness.

## NativePHP Mobile

The tracked NativePHP Mobile 4.2 lock installs embedded PHP 8.5.9, and the current Android artifact boots on that engine. Official v4 documentation still describes PHP 8.4, so Composer retains the conservative `>=8.4 <8.6` envelope until upstream metadata converges. Build/release requires official NativePHP tools, configured app ID/version/signing, min Android SDK 31 for Android 12, target/compile SDK values from config, a production frontend build, and real-device testing before store submission.

The current onboarding-capable development artifact was rebuilt with NativePHP Mobile 4.2, embedded PHP 8.5.9, and Gradle 8.14.5 at `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`. It is 131,559,203 bytes with SHA-256 `027d297747fe43b112699a3848fb3db0368ef41c7bf8ce77be47255bc5cab4d1`; `aapt` reports package `com.goleaf.xiaomimimo`, compile/target SDK 36, and minSdk 31. APK Signature Scheme v2, ZIP alignment, outer/nested ZIP integrity, required runtime sources/assets, email-verification-source exclusion, and host-database exclusion pass. The removal migration is bundled among all 36 migrations. No Android device is currently attached, so this artifact has not replaced the older emulator installation evidence; production releases must replace debug signing and `DEBUG` version metadata and repeat the flow on real hardware.

Do not commit signing keys, provisioning profiles, production tokens, or generated local build artifacts. Verify the packaged manifest/runtime contract, the embedded onboarding routes/components/translations, and exclusion of the host SQLite file after every NativePHP major update.
