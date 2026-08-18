# Changelog

All notable project changes are documented here.

## Unreleased

### Brand Identity

- Renamed the active product in place to Sutelio across application metadata, EN/LT/RU copy, deterministic browser/native brand sources, package metadata, and canonical engineering documentation while preserving earlier release and audit evidence.
- Locked the stripe-free clean-S mark to cobalt `#123C8B`, deep cobalt `#0A285F`, Signal Orange `#FF6038`, and ivory `#FFF8E9`; the one-color wordmark, including its final `o`, remains deep cobalt.
- Established `com.goleaf.sutelio` as the external Android application ID and iOS bundle ID, `sutelio` as the deep-link scheme, and `com.nativephp.mobile` as the unchanged NativePHP/JNI Android namespace. The new mobile package is an intentional clean-install sandbox with no automatic migration of private data from the previous package.
- Preserved the delivered fixed light-only Warm Precision presentation; earlier dark/system verification entries below describe superseded historical behavior rather than a current runtime theme family.
- Completed the Sutelio APK/emulator, in-place GitHub/Herd rename, Android 10 compatibility, and physical Samsung delivery gates without changing the legacy package sandbox or its private data.

### Runtime And Dependencies

- Re-ran the complete NativePHP Mobile 4 upgrade procedure against the latest stable `4.2.0` package: refreshed Composer resolution, force-regenerated both ignored platform shells, reapplied deterministic Sutelio branding and Android log/bundle hardening, and rebuilt the Android artifact with embedded PHP 8.5.9. The tracked mobile configuration now fixes native appearance to the product's single light mode and keeps the v4 FPS overlay disabled unless explicitly enabled.
- Lowered the supported Android floor from API 31 to NativePHP's API 29 baseline so the debug application can run on Android 10 while retaining compile/target SDK 36, ARM64 packaging, and the existing Sutelio package identity.
- Installed and launched the exact verified API 29 debug APK on a Samsung SM-A920F running Android 10; Russian locale persistence, resumed activity state, SQLite integrity/foreign keys/38 migrations, and process-scoped fatal/ANR/sensitive-log checks passed.
- Moved the Herd web/development runtime and CI to PHP 8.5 while retaining `>=8.4 <8.6` compatibility because NativePHP Mobile 4.2's generated runtime is PHP 8.5.9 while its official support documentation still describes PHP 8.4.
- Updated Laravel to 13.25, Inertia Laravel to 3.3.1, Fortify to 1.38, Sanctum to 4.3.3, Wayfinder to 0.1.21, Boost to 2.5.3, Pest to 5.1.1/PHPUnit 13.3, NativePHP Mobile to 4.2, Vite to 8.2.1, Laravel Vite plugin to 3.2, Vue to 3.5.41, and compatible frontend tooling.
- Re-ran complete stable dependency resolution on 2026-08-16 and upgraded PHPStan from 2.2.5 to 2.2.8. Every direct Composer and npm package remains at its latest mutually compatible release; newer Guzzle, Workerman, Brick Math, PHPUnit, TypeScript, and Node-type majors remain behind explicit upstream framework/runtime peer constraints rather than unsafe overrides.
- Re-ran complete stable dependency resolution on 2026-08-17, upgraded `laravel/mcp` to 0.9.4 and transitive `es-toolkit` to 1.51.0, and refreshed the tracked NativePHP embedded runtime from PHP 8.4.24 to 8.5.9. Composer and npm audits remain clean, and no compatible direct dependency update remains.
- Regenerated Composer/npm locks and cleared all eight Composer and four npm baseline advisories; removed unused frontend dependencies and retained Axios only for NativePHP's adapter contract.

### Architecture And Security

- Made a usable workspace a registration invariant: web/API sign-up now atomically creates a localized personal workspace, owner membership, and canonical task definitions; required onboarding skip/completion selects the same idempotent baseline so task creation works immediately without fabricated projects or tasks.
- Added a versioned, resumable guided-onboarding state machine with authenticated-user gating, run-scoped idempotent workspace/project/task composition, authorization-aware invitation discovery, safe replay, and populated-data migration behavior.
- Removed email verification from Fortify configuration, routes, middleware, notifications, onboarding/profile UI, shared types/props, factories, translations, and the SQLite user schema; registration now proceeds directly to onboarding, with architecture coverage and a durable repository rule preventing reintroduction.
- Normalized NativePHP environment scalars, allowed the independently derived app-private mobile SQLite directory, removed only unavailable mobile-bundle Blade namespace hints, and disabled token-bearing mail logs on-device so clean Android boot, migration, `view:cache`, and registration complete without runtime/security log errors.
- Enabled strict Eloquent behavior outside production and corrected partial-model projections, locale/request state, private avatar serialization, route endpoint closures, and service-locator usage exposed by the stricter contract.
- Added a typed user-scoped notification request/query/resource boundary with semantic filters, deterministic pagination, safe legacy payloads, batched authorization for task destinations, and idempotent read mutations.
- Added typed activity category/filter/query/resource boundaries, workspace-safe activity filtering, bounded infinite scrolling, and two rollback-safe workspace-leading activity indexes.
- Added a typed project operations request/query/resource boundary with workspace-scoped filter identifiers, server-enforced archived-project creation rules, localized task definitions, deferred partial-reload queries, and identity-matched scroll refreshes.
- Reduced the Inertia Blade shell to presentation-only markup, moved theme boot logic to a first-party external asset, and expanded automated guards against Blade PHP/data/service calls, Livewire/Volt, unsafe environment access, debug calls, route action closures, and dynamic Tailwind interpolation.

### Interface, Accessibility, And Localization

- Closed the mobile left navigation automatically when an Inertia page visit starts, while preserving the persistent desktop sidebar state and ignoring prefetch-only activity.
- Added a complete eight-step Warm Guided Route for new users, an honest dismissible Dashboard continuation checklist, Settings replay, role-aware safety guidance, and immediate English/Lithuanian/Russian preference rendering across desktop and mobile.
- Verified onboarding heading/validation focus, 44-pixel mobile actions, stable Back/Continue layout, safe-area handling, reduced motion, dark and forced-colors modes, and zero 390-pixel horizontal overflow.
- Centralized authenticated document semantics in the persistent application shell: ten nested page/settings `main` landmarks were removed, reusable task detail now begins at `h2`, and automated plus 22-route desktop/mobile checks enforce one `main` and one `h1` per page.
- Consolidated repeated container, radius, shadow, and semantic status values into the Tailwind 4 CSS-first Warm Precision token system.
- Rebuilt notifications as a responsive Structured Signal Stream with URL-backed status/kind filters, Today/Earlier grouping in the saved timezone, shared localized row/browser content, focused pending/empty states, and accessible pressed-button groups.
- Added responsive, keyboard-accessible activity intelligence filters and verified critical routes at desktop/mobile widths, reduced motion, dark media, and forced colors without overflow or current console errors.
- Rebuilt project detail as a responsive operations workspace with a bounded task queue, URL-backed desktop/mobile filters, complete-project metrics, priority and attention summaries, accessible task/project actions, and mobile-first information order.
- Preserved stable English, Lithuanian, and Russian translation keys and locale-aware presentation across changed interfaces.

### Data, Seeding, And Tests

- Completed valid factories for all 17 models with 29 meaningful states/helpers and a 56-test factory/state/seeder contract.
- Added a non-destructive, production-guarded `DemoSeeder`; fresh migration and repeated deterministic seeding complete with valid foreign keys.
- Added six rollback-safe project-operation indexes with production-query-plan coverage for workspace/project filters and position, due-date, updated, and priority-definition sorts.
- Replaced brittle/example-only checks with semantic architecture and application coverage. The current suite passes 763 tests with 16,291 assertions sequentially and in parallel. All 45 frontend tests, types, lint, format, Larastan level 7, Pint, and the production build pass.
- Upgraded NativePHP's generated Android project and verified a fresh onboarding-capable debug APK with minSdk 31, targetSdk 36, v2 debug signature, ZIP alignment/integrity, embedded onboarding sources/translations, and no host SQLite database. Production signing remains a release-environment responsibility.
- At the earlier 36-migration artifact stage, rebuilt and independently inspected the debug APK without a host SQLite database and with passing signature/alignment/archive checks; later entries and canonical deployment evidence supersede that artifact and its then-pending device status.

### Documentation

- Established the permanent repository engineering contract, stable functional/non-functional requirement IDs, compliance matrix, architecture decisions, current-state/final evidence, data/security/frontend/design/accessibility/localization/testing/seeding/performance/operations/deployment contracts, and genuine external limitations.
- Removed superseded July audit files, completed execution plans, obsolete tool plans, and tracked generated graph output after retaining durable decisions/evidence in canonical documentation, the append-only progress journal, and recoverable Git history.
