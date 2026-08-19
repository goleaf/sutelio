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

The original onboarding schema migration is additive and populated-safe: existing preference rows were backfilled complete/dismissed, while new Fortify registrations enter the required journey. The successor mandatory-completion migration reopens only incomplete rows that previously used the retired skip path; it assigns a fresh run at Welcome without rewriting completed users. Deploy migrations before serving onboarding assets, retain session storage across the release so in-progress users can resume, and do not reset onboarding lifecycle columns during rollback or replay support work.

The email-verification removal migration intentionally drops `users.email_verified_at` after the Fortify feature, routes, middleware, notifications, UI, and public props are removed. Deploy the application and migration together. Its rollback recreates an empty nullable compatibility column but cannot reconstruct discarded timestamps; that data loss is an approved product decision.

Configure the scheduler and, when reminder delivery is queued, a supervised bounded database queue worker. Monitor application logs, failed jobs, disk space, SQLite health, backup results, and scheduler freshness.

## NativePHP Mobile

Native builds must validate and compile the allowlisted `goleaf/nativephp-email-picker` plugin, confirm the final manifest contains no account/contact permission, and exercise chooser cancellation plus successful encrypted Sutelio-history persistence on a disposable emulator. The exact inspected APK may update a physical Samsung only after source publication and remote equality; ordinary feature delivery uses a data-preserving install and must not clear the application-private encrypted history, cookies, sessions, or the SQLite database.

The tracked NativePHP Mobile 4.2 lock installs embedded PHP 8.5.9, and the current Android artifact boots on that engine. Official v4 documentation still describes PHP 8.4, so Composer retains the conservative `>=8.4 <8.6` envelope until upstream metadata converges. Build/release requires official NativePHP tools, configured app ID/version/signing, min Android SDK 29 for Android 10, target/compile SDK values from config, a production frontend build, and real-device testing before store submission. Sutelio explicitly sets `NATIVEPHP_APPEARANCE=light` to match the product's fixed presentation and keeps `NATIVEPHP_FPS_OVERLAY=false` outside deliberate profiling.

Regenerate brand inputs and NativePHP projects in this order before any native build:

```bash
npm ci
npm run brand:build
php artisan native:install --force --no-interaction
npm run brand:native
```

`brand:native` validates and publishes the canonical identity/assets only after a complete fresh-template or already-canonical preflight. It also applies the tested Android source hardening that disables production WebView debugging, removes the RequestInspector dependency and interception, and prevents request headers, cookie values, response bodies, and CSRF values from reaching Logcat. Android's external `applicationId` and iOS primary bundle identifier are `com.goleaf.sutelio`, and the hostless custom scheme is `sutelio`. Android's `namespace = "com.nativephp.mobile"` belongs to NativePHP/JNI and must remain unchanged.

The complete NativePHP 4.2 shell packages the current mandatory-onboarding, responsive, locale-rate-limit, page-start navigation, page-based record-management, and mobile-calendar contracts in the independently inspected ignored debug deliverable at `storage/app/native-build/sutelio-android-debug.apk`. The current pre-hardware artifact is 127,428,080 bytes with SHA-256 `da2a7bc1c83e8df83faaa2300942c804e721d80377be367326145e0658a47c14`; it uses package `com.goleaf.sutelio`, label `Sutelio`, min SDK 29, target/compile SDK 36, ARM64 ABI, and one valid Android debug v2 signer. ZIP integrity, 16 KiB-aware `zipalign`, and v2 signature verification pass. Its 30,226,169-byte Laravel bundle contains 13,108 files and all 39 migrations while excluding host SQLite payloads, first-party test/documentation trees, RequestInspector bytecode, agent tooling, and nonempty sensitive environment values.

The exact artifact installed on a new disposable ARM64 Android 14 emulator. Cold launches completed in 4,003 ms and 4,551 ms with `MainActivity` resumed; the installed `base.apk` matched byte-for-byte, the first-run language dialog rendered correctly, and the coherent app-private SQLite copy returned integrity `ok`, zero foreign-key violations, all 39 migrations, and zero users/workspaces/projects/tasks. Process-scoped fatal/ANR and sensitive-value matches were zero. The task-owned AVD and diagnostics were removed after proof; the unrelated `BTChat_API_34` AVD and physical Samsung remained untouched. Source/evidence publication with exact local/tracking/advertised equality remains mandatory before a final physical-device reset or installation.

`adb install -r` preserves the Android application sandbox, including NativePHP's app-private Laravel cookie store, the mirrored WebView cookies, guest session, and explicit language choice. A SQLite-only reset also leaves those preferences intact. Verify a true first run with a clean disposable emulator install or an explicitly scoped Sutelio app-state reset; never weaken locale persistence or clear another application's data merely to make the first-run dialog reappear. If the physical SQLite database must remain, back up and reset only Sutelio's cookie/session stores, then prove the dialog in the rendered WebView and confirm database integrity separately.

The canonical repository is `goleaf/sutelio`, the exact local checkout is `/Users/andrejprus/Herd/sutelio`, and Herd serves it securely at `https://sutelio.test` on PHP 8.5. The in-place Task 21 rename preserved the existing Git history and `main`; no replacement repository, branch, or checkout was created.

Full iOS simulator compilation remains externally blocked on this workstation because only Apple Command Line Tools are installed; the exact release gate and resolution trigger are recorded in `docs/known-limitations.md`.

Do not commit signing keys, provisioning profiles, production tokens, or generated local build artifacts. Verify the packaged manifest/runtime contract, the embedded onboarding routes/components/translations, and exclusion of the host SQLite file after every NativePHP major update.
