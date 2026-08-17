# Progress

## Phase 0: Repository Contract And Documentation Baseline

### Completed Work

- Read the complete `AGENTS.md`, manifests, route files, backend domain layers, migrations/indexes, Vue/TypeScript application layers, and tests.
- Inspected Git state and recent history, current Artisan routes, installed package versions, the live SQLite schema/indexes, and runtime SQLite diagnostics.
- Searched version-specific Laravel 13, Inertia 3, Fortify, Sanctum 4, and Wayfinder documentation through Laravel Boost.
- Established the permanent requirements and documentation baseline without changing product behavior.

### Changed Files

Documentation files under `docs/`, `README.md`, `CHANGELOG.md`, and the project-specific section appended to `AGENTS.md`.

### Migrations And Packages

No migrations changed. No packages were added, removed, or upgraded.

### Decisions

- Preserve Laravel/Inertia/Vue/Wayfinder and SQLite-only architecture.
- Treat workspace isolation as the primary security boundary.
- Separate page, API, action, query, policy, resource, and Vue feature responsibilities over later phases.
- Record current risks as unresolved until complete flows and tests prove corrections.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed; no PHP production files were changed.
- `php artisan test --compact`: passed, 111 tests and 329 assertions.
- `npm run build`: passed; Vite generated the production bundle and Wayfinder types.
- `vendor/bin/phpstan analyse --no-progress`: failed with 377 pre-existing application type errors.
- `npm run types:check`: failed with 15 pre-existing TypeScript/Vue errors.
- `npm run lint:check`: failed with 88 pre-existing ESLint errors.
- `npm run format:check`: failed; 19 pre-existing resource files require formatting.
- `git diff --check`: passed.

The failed checks identify baseline production-code debt. They were not modified in this documentation-only phase and are inputs to the audit and implementation roadmap.

### Git Delivery

Commit and push results are recorded after delivery.

### Known Limitations

The comprehensive finding-by-finding audit belongs to prompt 1. Current production behavior remains unchanged, including known authorization, routing, response, frontend, SQLite, import/export, and backup risks.

### Next Phase

Prompt 1: complete evidence-based backend, domain, database, frontend, security, and test audit with classified findings and implementation roadmap.

## Phase 1: Repository Audit And Source-Of-Truth Architecture Plan

### Completed Work

- Re-read `AGENTS.md`, every phase-0 requirements document, installed manifests, routes, controllers, actions, services, Form Requests, resources, policies, models, enums, commands, providers, notifications, middleware, migrations, factories, seeders, Vue pages/layouts/components/composables/stores/types, and all tests.
- Inspected the Artisan route table and recorded every routed controller method with middleware, request, ability/scope, action/query, response, consumers, duplication, and test gaps.
- Inspected the live SQLite schema and indexes through Laravel tooling, compared it with migrations, and verified runtime pragmas/integrity.
- Searched installed-version Laravel, Inertia, Fortify, Sanctum, and Wayfinder documentation through Laravel Boost before documentation edits.
- Classified backend, database, frontend, security, and testing findings as critical, high, medium, low, or informational with evidence, impact, correction, dependencies, tests, and owning phase.
- Produced an ordered implementation roadmap without changing production behavior.

### Changed Files

- `docs/current-state.md`
- `docs/audit/backend.md`
- `docs/audit/frontend.md`
- `docs/audit/database.md`
- `docs/audit/security.md`
- `docs/audit/testing.md`
- `docs/architecture.md`
- `docs/implementation-roadmap.md`
- `docs/progress.md`

### Migrations And Packages

No migration was added or changed. No Composer or npm package was added, removed, or upgraded.

### Architectural Decisions

- Repair workspace isolation, role permissions, and exact submitted-ID validation before consolidating controllers or changing route contracts.
- Keep page and API controllers as separate presentation adapters while sharing actions and query objects.
- Introduce API v1 and a single resource/error contract in phase 3; decide deprecation aliases from an explicit compatibility requirement.
- Establish final scoped query objects before changing SQLite indexes.
- Correct live UUID foreign/morph schema defects through populated-data-safe SQLite migrations rather than rewriting deployed migrations.
- Use official Wayfinder generation as the target and remove the custom global route helper only after all consumers are migrated and typed.

### Security Decisions

- Treat bulk task operations, unguarded child mutations, known-password invitation creation, and HTTP backup/restore as critical risks.
- Treat the route workspace as the tenant authority; globally existing UUIDs never prove authorization.
- Require explicit Sanctum abilities and separate API token behavior from web session behavior.
- Require owner authorization, recent password confirmation, opaque server-side inventory, WAL consistency, integrity checks, locking, and rollback for restore.

### Verification

- Focused `php artisan test --compact tests/Feature/WorkspaceTest.php tests/Feature/TodoTest.php`: passed, 14 tests and 29 assertions.
- `vendor/bin/pint --dirty --format agent`: passed; no PHP files required formatting.
- `php artisan test --compact`: passed, 111 tests and 329 assertions.
- `npm run build`: passed; Vite generated the production bundle and Wayfinder types. It emitted only the existing optional `fontaine` optimization notice.
- `vendor/bin/phpstan analyse --no-progress`: failed with the same 377 pre-existing application type errors.
- `npm run types:check`: failed with the same 15 pre-existing TypeScript/Vue errors.
- `npm run lint:check`: failed with the same 88 pre-existing ESLint errors.
- `npm run format:check`: failed; the same 19 pre-existing files under `resources/` require formatting.
- Prompt-1 documentation-only Prettier check: passed for all eight audited/updated architecture files.
- `git diff --check`: passed.

The production-code failures were not changed in this audit-only phase. No test was added solely to assert documentation content; existing focused and complete suites were used to verify unchanged behavior.

### Known Limitations

- No critical/high finding is fixed yet.
- The live domain schema has no foreign keys despite migration intent; UUID morph/foreign columns are inconsistent in passkeys, notifications, and Sanctum tokens.
- The current API remains unversioned and unrestricted by token abilities.
- The frontend quality gates remain red even though its production build succeeds.
- The untracked/staged `.mimocode/plans/1784461861035-kind-garden.md` planning artifact is unrelated to this phase and was deliberately not modified or included in the phase commit.

### Next Phase

Prompt 2: implement and test the critical/high workspace isolation, role permission, nested validation, bulk/reorder, scoped binding, and Sanctum ability corrections.

## UI Repair: Shared Responsive Sidebar

### Status

Completed.

### Completed Work

- Added an authenticated edit mode to the direct task detail page for title, description, status, priority, and due date.
- Submitted edits through the generated Wayfinder controller action and Inertia `useForm`, with server validation errors, processing state, cancel/reset behavior, refreshed props, and a success toast.
- Corrected the web update response to redirect for Inertia/browser requests while preserving JSON responses for JSON consumers.
- Corrected the update action so nullable optional fields can actually be cleared instead of being discarded before persistence.
- Added semantic task editing translations for English, Lithuanian, and Russian.
- Added focused Pest regressions for the redirect response, nullable field clearing, and localized edit-page props.

### Changed Files

- `app/Actions/UpdateTodo.php`
- `app/Http/Controllers/TodoController.php`
- `resources/js/pages/tasks/Show.vue`
- `lang/en/tasks.php`
- `lang/lt/tasks.php`
- `lang/ru/tasks.php`
- `tests/Feature/TodoTest.php`
- `docs/progress.md`

### Scope And Decisions

- Restore the starter-kit sidebar primitives that were replaced by a static `aside`, so the left navigation, desktop collapse state, keyboard shortcut, and mobile drawer share one state model.
- Keep the existing Inertia application layout as the single shell for authenticated pages instead of adding page-specific menus.
- Supply workspace and active-project navigation data through shared Inertia props, with the selected workspace resolved from the authenticated session.
- Use generated Wayfinder route functions for sidebar navigation and Inertia's standalone HTTP client for the JSON workspace-switch endpoint.
- Add English, Lithuanian, and Russian semantic navigation translations with English fallback.

### Planned Files

- Shared Inertia middleware and current-workspace resolution call sites.
- Sidebar, workspace switcher, navigation, logo/header, and shared TypeScript types.
- Navigation translation files and focused Pest coverage.

### Migrations And Packages

No migration or package change is planned.

### Verification Plan

- Focused Pest tests for shared sidebar props, locale labels, and session-aware workspace switching.
- Pint, Larastan, Vue type checking, ESLint, Prettier verification, production build, and `git diff --check`.

### Git Delivery

Commit and push results will be recorded after verification. The unrelated staged `.mimocode/plans/1784461861035-kind-garden.md` file will remain untouched and excluded.

## NativePHP Mobile 3 Quick-Start Integration

### Status

Completed.

### Completed Work

- Installed NativePHP Mobile 3 and initialized both supported mobile platforms with the embedded PHP 8.4 runtime.
- Configured `com.goleaf.xiaomimimo` as the Android application ID and iOS bundle identifier, `/` as the local start URL, and the persistent runtime mode.
- Integrated the NativePHP Vite plugin and hot-file path while retaining the existing Laravel, Inertia, Vue, Pinia, Wayfinder, and browser development workflow.
- Configured Inertia 3 to use its Axios HTTP adapter so page visits execute against the bundled on-device Laravel runtime.
- Added full-screen safe-area handling for the Android and iOS WebView.
- Kept SQLite as the only database and configured attachment upload, URL, download, and deletion behavior to use NativePHP's writable `mobile_public` disk in packaged apps and the existing public disk in browser development.
- Expanded bundle-time environment cleanup so development credentials and application secrets are not shipped in the mobile bundle.
- Added focused Pest coverage for the NativePHP command/configuration contract and on-device attachment lifecycle.
- Added no remote API, synchronization service, client/server split, Redis, or external database requirement.

### Changed Files

- `.env.example`
- `composer.json`, `composer.lock`
- `package.json`, `package-lock.json`
- `config/nativephp.php`, `config/filesystems.php`
- `native`, `nativephp.lock`
- `vite.config.ts`, `resources/js/app.ts`, `resources/views/app.blade.php`
- Attachment action, controller, resource, and model files
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

- Added `nativephp/mobile` 3.3.6 and its locked Composer dependencies.
- Added direct runtime dependencies on `@inertiajs/core` 3.6.1 and Axios 1.18.1 for the Inertia 3 mobile HTTP adapter.
- NativePHP installed embedded PHP 8.4.23 with ICU disabled.
- No application migration was added or changed. SQLite remains the only relational database.

### Verification

- `php artisan native:install both --no-interaction`: passed; Android and iOS projects and embedded PHP were installed.
- `php artisan native:plugin:list`: passed; no optional NativePHP plugins are required by this quick-start phase.
- `php artisan test --compact tests/Feature/NativePhpMobileTest.php`: passed, 2 tests and 18 assertions.
- Scoped PHPStan analysis for all NativePHP and attachment integration PHP files: passed with zero errors.
- `php artisan test --compact`: passed at the integration checkpoint with 116 tests and 392 assertions. A final run after concurrent settings/member tests and implementation appeared ran 128 tests, with 120 passing and 8 unrelated failures in that in-progress work; the NativePHP suite still passes independently.
- `vendor/bin/pint --dirty --format agent`: passed.
- Targeted ESLint for `resources/js/app.ts`: passed.
- Targeted Prettier for the NativePHP TypeScript and package files: passed.
- `npm run build`: passed; the standard production application bundle includes the Axios and NativePHP integration.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev`: passed with no known dependency vulnerabilities.
- `git diff --check`: passed.
- Full PHPStan remains at 365 pre-existing errors, improved from the documented 377-error baseline; NativePHP-touched PHP files are clean.
- Full Vue type checking remains at 11 errors and full ESLint remains at 85 errors in pre-existing or concurrently edited non-mobile files; the NativePHP application entrypoint is clean.
- Full resource Prettier verification remains red in 14 unrelated resource files.

### Git Delivery

Implementation commit `264d8d6` (`feat: integrate NativePHP Mobile 3`) was pushed successfully to `origin/main`. This phase-related progress update will be committed and pushed separately; existing sidebar, settings, task, translation, and `.mimocode` changes remain outside both commits.

### Known Limitations

- NativePHP's generated Android and iOS source projects remain locally reproducible and intentionally ignored by the generated `nativephp/.gitignore`; the tracked installer configuration, launcher, and lock file are the source of truth.
- Platform-mode Vite builds and device launch/watch commands were not executed automatically, as required by the installed NativePHP development guidance.
- This Intel Mac does not satisfy NativePHP's Apple-silicon iOS development requirement and does not have full Xcode, CocoaPods, Android Studio, or an Android SDK available. Device compilation must be completed on a supported workstation.
- No Apple development team or product-specific icon/splash assets were supplied, so code signing remains unset and the NativePHP defaults remain in use.

## UI Repair: Complete Profile Settings

### Status

Completed.

### Completed Work

- Added authenticated avatar upload, preview, progress, replacement, display, and removal with strict JPG/PNG/WebP, extension, MIME, 2 MB, and 4096-pixel validation.
- Stored generated avatar filenames on a dedicated private disk, deleted replaced and removed files with explicit failure handling and database rollback, cleaned the avatar during account deletion, and served the current user's image with private no-store and nosniff headers.
- Rebuilt the profile page around profile photo and personal information cards, removed the duplicate password form in favor of the Security page, and restored email-verification resend feedback.
- Replaced the broken confirmation-only account deletion button with the existing accessible password-confirming dialog.
- Added generated Wayfinder form routing, responsive Tailwind CSS 4 behavior, avatar fallbacks, duplicate-submit prevention, localized success/error states, and semantic English, Lithuanian, and Russian profile translations with English fallback.
- Verified the live page on desktop and a 390-pixel viewport; avatar changes propagate to the account menu and the page creates no document-level horizontal overflow.

### Changed Files

- `app/Actions/DeleteProfileAvatar.php`
- `app/Actions/UpdateProfileAvatar.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Requests/Settings/ProfileAvatarDeleteRequest.php`
- `app/Http/Requests/Settings/ProfileAvatarUpdateRequest.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `config/filesystems.php`
- `database/migrations/2026_07_19_134932_add_avatar_path_to_users_table.php`
- `routes/settings.php`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/components/DeleteUser.vue`
- `resources/js/layouts/settings/Layout.vue`
- `lang/en/settings.php`, `lang/lt/settings.php`, and `lang/ru/settings.php`
- `tests/Feature/Settings/ProfileUpdateTest.php`
- `docs/progress.md`

### Migrations And Packages

- Added and applied one SQLite-compatible nullable `users.avatar_path` column.
- No package was added, removed, or upgraded.

### Verification

- `php artisan test --compact tests/Feature/Settings/ProfileUpdateTest.php`: passed, 19 tests and 145 assertions, including simulated storage deletion failures and recoverable account-avatar staging.
- `php artisan test --compact`: passed, 148 tests and 659 assertions.
- `vendor/bin/pint --dirty --format agent`: passed.
- Scoped Larastan for both avatar actions, the controller, and both requests: passed with zero errors.
- `npm run build`: passed; Wayfinder regenerated and Vite emitted only the existing optional `fontaine` notice.
- Targeted ESLint and Prettier checks for the three changed Vue files: passed.
- `git diff --check`: passed.
- Authenticated Chromium verified upload (`302`), removal (`303`), live avatar/fallback propagation, success toasts, password-confirming deletion dialog, a cancel action that sends no DELETE request, zero page console errors, and a 390-pixel document width without horizontal overflow.
- Live private-storage verification confirmed the uploaded file exists only under `storage/app/private/profile-avatars`, the public `/storage/...` path is denied, and the authenticated endpoint returns `private, no-store` plus `nosniff` headers.
- Full Larastan remains red with 364 existing errors outside this profile flow.
- Full Vue type checking remains red with nine existing errors outside the changed profile files.
- Full ESLint remains red with 72 existing errors outside the changed profile files.
- Full Prettier verification remains red on 13 existing files; all changed profile Vue files pass their targeted check.
- The mandatory post-implementation code review found no critical issues; its private-storage, explicit deletion-failure, cancel-button, and localized-breadcrumb findings are resolved in the follow-up hardening change.

### Known Limitations

- Existing settings-navigation labels outside the profile content remain part of the broader localization phase.
- Concurrent sidebar work owns the shared Inertia middleware and responsive settings-layout base; this repair adds only avatar and profile-specific integration to those files.

### Git Delivery

- Commit `c23f582` (`feat: complete profile settings`) contains only the 14 profile code, migration, translation, layout, and test files.
- Push to `origin main` succeeded (`dc5ff2e..c23f582`).
- Follow-up commit `1bc66f1` (`fix: harden profile avatar lifecycle`) contains only the eight reviewed profile/config/test files and was pushed successfully to `origin/main` (`c23f582..1bc66f1`).
- Final review follow-up commit `c04d7a3` (`fix: make profile deletion recoverable`) stages account avatars before deletion, restores them on database failure, localizes the settings navigation landmark, and was pushed successfully to `origin/main`.
- The shared Inertia avatar prop remains in the separately staged sidebar batch; the mixed `docs/progress.md` index/worktree state also remains excluded from the isolated profile commit.

## Task Detail Editing Repair

### Status

Completed and ready for delivery.

### Scope And Decisions

- Restore editing on the direct `/tasks/{todo}` Inertia page for the title, description, status, priority, and due date.
- Keep `UpdateTodoRequest`, `TodoPolicy`, and `UpdateTodo` as the validation, authorization, and state-change boundaries.
- Return a redirect for Inertia form submissions while preserving the existing JSON response for JSON clients.
- Use generated Wayfinder controller actions, existing Vue UI primitives, and English, Lithuanian, and Russian semantic translations.

### Migrations And Packages

No migration or package change is planned.

### Verification

- The redirect and nullable-field regressions failed first for the expected `200` response and retained nullable values, then passed after their respective fixes.
- `php artisan test --compact tests/Feature/TodoTest.php`: passed, 12 tests and 37 assertions.
- `php artisan test --compact`: passed, 130 tests and 512 assertions on the final sequential run.
- Scoped Pint for the six changed PHP files: passed.
- Targeted ESLint and Prettier checks for `resources/js/pages/tasks/Show.vue`: passed.
- `npm run build`: passed; Wayfinder generated types and Vite emitted only the existing optional `fontaine` notice.
- `git diff --check` for the repair files: passed.
- Authenticated Chromium verification on `/tasks/019f7a48-e93e-700c-a984-26341f7ddf06`: edit controls rendered, both update and restore returned `303`, the refreshed title rendered, and no console error occurred. The task title was restored after verification.
- Full Larastan remains red with 364 existing application errors; the scoped controller/action run reports four existing typing/query errors.
- Full Vue type checking remains red with nine existing errors outside `tasks/Show.vue`; the changed page has no type error.
- Full ESLint remains red with 72 existing errors outside `tasks/Show.vue`.
- Full Prettier verification remains red on 15 existing files; the changed page passes its targeted check.

### Known Limitations

- Existing hardcoded task-detail display copy and locale-aware date formatting remain broader localization debt; all copy introduced by this repair is translated.
- Concurrent profile, members, preferences, sidebar, NativePHP, package, and planning work remains outside this repair.

### Git Delivery

- Commit `22ab435` (`fix: restore task detail editing`) contains only the seven task-repair code, translation, and test files.
- Push to `origin main` succeeded (`8f150c2..22ab435`).
- The mixed pre-existing `docs/progress.md` index/worktree state remains excluded from the isolated repair commit.

## Settings UI: Unified Preferences And Appearance

### Status

Completed.

### Completed Work

- Consolidated the functional light, dark, and system appearance control into the Preferences page.
- Removed the duplicate database-backed theme select from the preference form so appearance continues to use the existing cookie and local-storage behavior.
- Removed the duplicate Appearance settings navigation item and superseded standalone Vue page.
- Preserved `/settings/appearance` as an authenticated GET compatibility redirect to the canonical `/settings/preferences` route.
- Replaced the affected hardcoded frontend URLs with generated Wayfinder route functions and made the locale controls responsive on narrow screens.
- Added focused Pest coverage for the canonical Inertia page, compatibility redirect, authentication boundary, and shared appearance control.

### Changed Files

- `routes/settings.php`
- `resources/js/layouts/settings/Layout.vue`
- `resources/js/pages/settings/Preferences.vue`
- `resources/js/pages/settings/Appearance.vue` (removed)
- `tests/Feature/Settings/PreferencesTest.php`
- `docs/progress.md`

### Scope And Decisions

- Make `/settings/preferences` the canonical page for account preferences and appearance controls.
- Preserve `/settings/appearance` as an authenticated compatibility redirect to the canonical preferences route.
- Remove the duplicate Appearance navigation entry and keep the functional cookie/local-storage appearance control on the unified page.
- Use generated Wayfinder route functions for settings navigation and preference submission.

### Planned Files

- Settings routes, layout navigation, and the unified Preferences Vue page.
- The superseded standalone Appearance Vue page.
- Focused Pest coverage for the canonical page, compatibility redirect, and authentication boundary.

### Migrations And Packages

No migration or package was added, removed, or changed.

### Verification

- Focused `php artisan test --compact tests/Feature/Settings/PreferencesTest.php`: passed, 5 tests and 18 assertions after the expected RED run failed on the old separate-page behavior.
- Scoped `vendor/bin/phpstan analyse --no-progress routes/settings.php`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent routes/settings.php tests/Feature/Settings/PreferencesTest.php`: passed.
- Targeted ESLint and Prettier checks for the two modified Vue files: passed.
- `npm run build`: passed; Wayfinder regenerated route and form definitions, and Vite emitted only the existing optional `fontaine` notice.
- `git diff --check`: passed.
- Full Vue type checking remains red on 11 unrelated files; neither modified Vue file reports an error.
- Full ESLint remains red with 75 unrelated errors; both modified Vue files pass targeted ESLint.
- Full Prettier verification remains red on 13 unrelated files; both modified Vue files pass targeted Prettier.
- The complete Pest run passed 123 of 129 tests and failed only the six concurrently added, unrelated `SettingsMembersTest` cases; all unified preferences tests pass.

### Known Limitations

- Existing settings copy remains English-only as part of the repository's pre-existing localization debt; this consolidation reuses rather than expands that copy.
- Concurrent profile, members, task, sidebar, NativePHP, attachment, package, and planning work remains outside this phase.

### Git Delivery

Implementation commit `d74c709` (`feat: unify settings preferences and appearance`) was pushed successfully to `origin/main`. This phase-related progress update remains uncommitted because `docs/progress.md` also contains staged and unstaged work from concurrent phases; committing the whole file would include unrelated changes.

## Settings UI: Workspace Members Roster Repair

### Status

Completed.

### Completed Work

- Replaced the crashing nested `member.user` assumption with an explicit, workspace-scoped roster contract containing only the fields required by the page.
- Rebuilt the page as a responsive roster with member identity, localized role context, search, management summary, invitation form, read-only state, and an accessible removal dialog.
- Wired invite and removal submissions to generated Wayfinder controller actions and returned browser-compatible redirects so Inertia refreshes the roster after mutations.
- Authorized removals through the workspace policy, rejected foreign member identifiers, and prevented removal of the workspace owner or current user.
- Added semantic English, Lithuanian, and Russian members copy.
- Made the shared settings navigation responsive so the roster remains usable at mobile widths.
- Added focused Pest coverage for roster shape, selected-workspace isolation, permissions, localization, invitation, removal, and owner protection.

### Changed Files

- `app/Http/Controllers/Settings/MembersController.php`
- `app/Http/Controllers/WorkspaceController.php`
- `resources/js/pages/settings/Members.vue`
- `resources/js/layouts/settings/Layout.vue`
- `lang/en/members.php`, `lang/lt/members.php`, `lang/ru/members.php`
- `tests/Feature/SettingsMembersTest.php`
- `docs/progress.md`

### Scope And Decisions

- Repair the Inertia member payload mismatch that renders the count but crashes before displaying the roster.
- Replace the members page with a responsive, accessible workspace roster and invitation flow using the existing Vue, Tailwind CSS 4, Reka UI, and Wayfinder stack.
- Keep every member query and mutation scoped to the selected authorized workspace, expose only the fields required by the page, and prevent removal of the owner or current user.
- Add semantic English, Lithuanian, and Russian copy with English fallback.
- Preserve all unrelated sidebar, NativePHP, attachment, task, profile, preferences, package, and planning changes already in the worktree.

### Migrations And Packages

No migration or package was added, removed, or changed.

### Verification

- The six focused regressions failed first for the missing roster props, locale copy, redirect contract, and undefined removal request, then passed after implementation: 6 tests and 78 assertions.
- `php artisan test --compact`: passed, 130 tests and 512 assertions.
- Scoped Pint and Larastan for the changed PHP files: passed with zero errors.
- Targeted ESLint and Prettier verification for the two changed Vue files: passed.
- `npm run build`: passed; Vite generated the production bundle and emitted only the existing optional `fontaine` notice.
- `git diff --check`: passed.
- Authenticated Chromium verification at 1440px and 390px showed Demo User, Alice Chen, and Bob Smith; search and the removal confirmation worked; the mobile document had no horizontal overflow; no page or console error occurred.
- Full Vue type checking remains red with 9 existing errors outside the members files. Full Larastan remains red with 364 existing errors, full ESLint with 72 existing errors, and full Prettier verification with 15 existing files; all phase-scoped checks pass.

### Known Limitations

- The wider repository quality-gate baseline remains red as recorded above; this phase did not modify those unrelated files.
- Concurrent profile, preferences, task, sidebar, NativePHP, attachment, package, route, and planning work remains outside this phase.

### Git Delivery

Implementation commit `81a40ce` (`fix: rebuild workspace members roster`) was pushed successfully to `origin/main`. The phase-related progress update remains uncommitted because `docs/progress.md` also contains staged and unstaged work from concurrent phases; committing the whole file would include unrelated changes.

## NativePHP Mobile 3 Upgrade Guide Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the installed NativePHP Mobile 3.3 runtime with every applicable step in the official v3 upgrade guide.
- Replaced the broad `^3.3` dependency constraint with the guide-aligned `~3.3.0` patch line; Composer confirmed 3.3.6 is current within that line.
- Published `App\Providers\NativeServiceProvider` as the explicit security allow-list for third-party native plugin code.
- Added the documented Android compile, minimum, and target SDK configuration points with defaults of 36, 33, and 36.
- Confirmed the repository has no legacy `nativephp.composer.sh` repository or project-level Composer authentication file.
- Rebuilt both generated native platform shells with `native:install both --force`; embedded PHP remains 8.4.23 with optional ICU disabled.
- Validated plugin discovery with the v3 commands; no optional native plugins are installed or registered.
- Preserved the fully on-device Laravel, Inertia, Vue, and SQLite architecture with no remote client/server integration.

### Changed Files

- `composer.json`, `composer.lock`
- `config/nativephp.php`
- `app/Providers/NativeServiceProvider.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No application migration or new package was added. NativePHP remains at 3.3.6 with a patch-line Composer constraint.

### Verification

- The focused upgrade test failed first on the old `^3.3` constraint, then `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 3 tests and 27 assertions.
- Scoped PHPStan for the NativePHP provider and configuration passed with zero errors.
- `php artisan native:install both --force --no-interaction`: passed and regenerated both native shells and PHP binaries.
- `php artisan native:plugin:validate --no-interaction` and `php artisan native:plugin:list --all`: passed; no plugins are installed.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev`: passed with no known dependency vulnerabilities.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact`: passed, 139 tests and 597 assertions.
- `npm run build`: passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` for this upgrade passed.
- Full PHPStan remains red with 364 existing application errors; the NativePHP upgrade files are clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier with 13 existing files; this backend/configuration-only upgrade introduced none of them.

### Git Delivery

Commit `8e120a7` (`chore: reconcile NativePHP 3 upgrade guide`) was pushed successfully to `origin/main`. All concurrent sidebar, settings, task, profile, translation, and planning changes remained outside the phase commit.

### Known Limitations

- ICU remains disabled to avoid the documented mobile binary size increase; it can be enabled later with `--with-icu` if the application requires PHP `intl` on-device.
- Platform-mode frontend builds and simulator/emulator launch commands were not auto-run, per the installed NativePHP project guidance.
- Local iOS compilation remains unavailable on this Intel Mac; CocoaPods is now installed, but full Xcode and Apple silicon are still unavailable. The Android toolchain is configured in the following environment-setup phase.

## NativePHP Mobile 3 Environment Setup

### Status

Completed.

### Completed Work

- Installed Android Studio 2026.1.2.10 and CocoaPods 1.17.0 through Homebrew without trusting or changing unrelated third-party taps.
- Accepted the Android SDK licenses and installed API 36, Build Tools 36.0.0, Platform Tools 37.0.0, the current Intel Android emulator, and the Google APIs API 36 x86_64 system image.
- Created the valid `NativePHP_API_36` Pixel 6 AVD while preserving the existing AVD definitions.
- Pinned normal login shells to Temurin JDK 17 and the local Android SDK through `~/.zprofile`; NativePHP's login-shell diagnostics now detect Java 17, Android Studio, Gradle 8.13, and CocoaPods.
- Bound the ignored local `.env` to this workstation's JDK and SDK paths and exposed the portable Android environment contract in `.env.example`.
- Cast environment-overridden Android SDK levels to integers at the configuration boundary.
- Restored every Homebrew formula removed by Homebrew's automatic post-install cleanup and verified both the existing Homebrew PHP and the normal Laravel Herd PHP paths remain executable.
- Preserved the fully on-device Laravel, Inertia, Vue, and SQLite architecture with no remote client/server integration.

### Changed Files

- `.env.example`
- `config/nativephp.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`
- Ignored/local workstation configuration: `.env`, `~/.zprofile`, Homebrew packages, Android SDK packages, and `~/.android/avd/NativePHP_API_36.avd`.

### Migrations And Packages

No application migration or Composer/npm package was added, removed, or upgraded. All installed packages are workstation-only development dependencies.

### Verification

- The focused environment-contract test failed first because `.env.example` lacked the Android keys, then exposed string SDK levels after local environment wiring; after implementation, `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 4 tests and 32 assertions.
- `zsh -lic 'php artisan native:debug --json --no-interaction'`: passed with NativePHP 3.3.6, PHP 8.4.16, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no optional native plugins.
- SDK inspection confirmed Build Tools 36.0.0, Platform Tools 37.0.0, API 36, and the API 36 x86_64 system image; AVD inspection confirmed `NativePHP_API_36` and Hypervisor.Framework acceleration.
- `adb devices`: passed with an empty device list; no physical Android device is currently connected.
- Scoped Pint and Larastan for the NativePHP files passed with zero errors.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev`: passed with no known dependency vulnerabilities.
- `php artisan test --compact`: passed, 146 tests and 644 assertions.
- `npm run build`: passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors; the NativePHP configuration is clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; this configuration-only phase introduced none of them.

### Known Limitations

- iOS builds cannot run on this workstation because NativePHP Mobile 3 requires Apple silicon and full Xcode; this Mac is Intel x86_64 and only has Command Line Tools. CocoaPods alone cannot remove that platform limitation.
- No physical Android device is connected. The API 36 AVD is configured but was not launched.
- Native platform build, run, emulator-launch, and watch commands were not auto-run, per the installed NativePHP project guidance.

### Git Delivery

- Commit `8adf61d` (`chore: configure NativePHP mobile environment`) contains only the NativePHP environment contract, configuration, test, and phase progress files.
- Push to `origin main` succeeded (`1bc66f1..8adf61d`).
- Unrelated staged sidebar, navigation, task, project, export, planning, and shared model/middleware work remains excluded and preserved.

## NativePHP Mobile 3 Installation Guide Reconciliation

### Status

Completed.

### Completed Work

- Confirmed `nativephp/mobile` 3.3.6 is installed on the existing `~3.3.0` patch line and the installer command is registered.
- Confirmed the reverse-DNS application ID `com.goleaf.xiaomimimo`, debug version, and build code are declared in both local and example environments before installation.
- Cast `NATIVEPHP_APP_VERSION_CODE` to an integer at the configuration boundary so `.env` overrides preserve the numeric build-code contract.
- Added `/nativephp` to the root `.gitignore`, making the generated Android/iOS platform shell explicitly ephemeral as recommended by the installation guide.
- Ran `native:install both --force --no-interaction`; it rebuilt both native projects and installed embedded PHP 8.4.23 with the documented default non-ICU binaries.
- Confirmed the tracked `./native` wrapper reports NativePHP 3.3.6 and the regenerated platform directory contains the Android Gradle and iOS Xcode/CocoaPods projects.
- Kept `NATIVEPHP_DEVELOPMENT_TEAM` unset because no Apple team ID was provided and this Intel Mac cannot build the iOS shell.
- Preserved the fully on-device Laravel, Inertia, Vue, and SQLite architecture with no remote client/server integration.

### Changed Files

- `.gitignore`
- `config/nativephp.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No application migration or Composer/npm package was added, removed, or upgraded. NativePHP remains at 3.3.6 on the v3 patch line.

### Verification

- The focused RED run failed on the string build code and missing root ignore rule; after implementation, `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 6 tests and 41 assertions.
- `php artisan native:install both --force --no-interaction`: passed; Android and iOS shells were regenerated with PHP 8.4.23 and ICU disabled.
- `zsh -lic 'php artisan native:debug --json --no-interaction'`: passed with NativePHP 3.3.6, PHP 8.4.16, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no optional plugins.
- `./native version --no-interaction`, `native:plugin:validate`, and `native:plugin:list --all`: passed.
- Browser smoke check for `http://xiaomi-mimo.test`: passed with HTTP 200 at the final `/login` URL before any native launch.
- Scoped Pint and Larastan for the NativePHP files passed with zero errors.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev`: passed with no known dependency vulnerabilities.
- `php artisan test --compact`: passed, 148 tests and 659 assertions.
- `npm run build`: passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors; the installation files are clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; this installation phase introduced none of them.

### Known Limitations

- iOS compilation remains unavailable on this Intel Mac because NativePHP Mobile 3 requires Apple silicon and full Xcode; no Apple development team ID was supplied.
- ICU remains disabled because neither the production dependency contract nor application source requires PHP `intl`; rerun the installer with `--with-icu` if that changes.
- Native build, run, device launch, emulator launch, and watch commands were not auto-run, per the installed NativePHP project guidance.

### Git Delivery

- Commit `0366361` (`chore: reconcile NativePHP installation guide`) contains only the root ignore rule, NativePHP build-code configuration, focused installation tests, and phase progress.
- Push to `origin main` succeeded (`c04d7a3..0366361`).
- Unrelated staged sidebar, navigation, task, project, export, planning, and shared model/middleware work remains excluded and preserved.

## NativePHP Mobile 3 Configuration Guide Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the effective NativePHP Mobile 3 configuration with the official configuration guide and the installed 3.3.6 package implementation.
- Kept the stable reverse-DNS app ID, debug development version, numeric build code, root start path, persistent runtime, SQLite storage, portrait-first phone orientation, and iPad support disabled.
- Documented the optional deep-link, Apple team, App Store Connect, Android status-bar, and local development-server environment variables without inventing production hosts or credentials.
- Made Android status-bar behavior environment-configurable with the documented `auto` default and named the local discovery service `Xiaomi Mimo`.
- Excluded `.agents`, `.github`, `.mimocode`, credentials, documentation, tests, transient framework state, and logs from packaged Laravel bundles while retaining required runtime and built frontend files.
- Confirmed the Android SDK invariant `compile_sdk >= target_sdk >= min_sdk`, conservative release optimization defaults, automatic system-theme status icons, and local-only hot-reload contract.
- Confirmed no optional NativePHP plugin is installed, so app-level permission descriptions and localizations remain empty until a plugin-backed device feature requires them.
- Preserved a self-contained Laravel, Inertia, Vue, and SQLite application with no remote client/server dependency.

### Changed Files

- `.env.example`
- `config/nativephp.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded. NativePHP remains at 3.3.6 on the v3 patch line.

### Verification

- The focused configuration test failed first on missing bundle exclusions, then `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 7 tests and 87 assertions.
- `php artisan config:show nativephp` confirmed the effective runtime, Android, server, hot-reload, store, iPad, and orientation values.
- `zsh -lic 'php artisan native:debug --json --no-interaction'` passed with NativePHP 3.3.6, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no optional plugins; Xcode remains unavailable.
- `php artisan native:plugin:validate --no-interaction` and `php artisan native:plugin:list --all --no-interaction` passed with no installed plugins.
- Scoped Pint and Larastan for the NativePHP configuration files passed with zero errors.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev` passed with no known dependency vulnerabilities.
- `php artisan test --compact` passed with 149 tests and 705 assertions.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors; the NativePHP configuration file is clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; none is in a configuration-phase file.

### Known Limitations

- Deep links and verified HTTPS hosts remain disabled because no product URL contract was supplied.
- iOS signing and App Store Connect upload remain unconfigured because no Apple team or API credentials were supplied; iOS compilation is also unavailable on this Intel Mac without full Xcode and Apple silicon.
- Native permission descriptions remain empty because the application has no optional NativePHP plugin or implemented device permission flow.
- Native build, run, device launch, emulator launch, and watch commands were not auto-run, per the installed NativePHP project guidance.

### Git Delivery

- Commit `825197b` (`chore: reconcile NativePHP configuration guide`) contains only the example environment, NativePHP configuration, focused test, and phase progress files.
- Push to `origin main` succeeded (`65dc904..825197b`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## NativePHP Mobile 3 Deployment Guide Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the installed NativePHP Mobile 3 release, signing, packaging, and store-upload contracts with the official deployment guide and NativePHP 3.3.6 command implementation.
- Documented the exact Android keystore, FCM, Google Play service-account, App Store Connect, iOS certificate, provisioning-profile, and team environment variables as empty placeholders without generating or committing release credentials.
- Replaced the legacy example `APP_STORE_API_KEY` variable with the current file-based `APP_STORE_API_KEY_PATH` contract while retaining the package's legacy configuration fallback.
- Added the App Store API key path to `nativephp.app_store_connect` and confirmed the valid app ID plus integer build-code contract.
- Added Android, iOS, store, and workstation-only variables to `cleanup_env_keys`, preventing signing passwords, credential paths, service-account data, team IDs, SDK paths, and FCM server keys from entering the embedded Laravel environment.
- Exercised NativePHP's real environment-cleanup trait and confirmed it removes representative deployment credentials while preserving the app ID, release version, and SQLite runtime configuration.
- Added `/credentials` to the root ignore rules; the generated `/nativephp` tree and its credentials/artifacts remain ignored separately.
- Confirmed `native:release`, `native:credentials`, `native:package`, and `native:check-build-number`, including release APK/AAB, App Store export, internal Play track, upload, and `--no-tty` contracts.
- Preserved `DEBUG` for local development; semantic release version selection, credential generation, signed packaging, and external store uploads remain explicit owner actions.
- Preserved the on-device architecture without introducing a deployment server.

### Changed Files

- `.env.example`
- `.gitignore`
- `config/nativephp.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded. NativePHP remains at 3.3.6.

### Verification

- The focused deployment tests failed first on the missing App Store API key path and Android secret cleanup, then `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 10 tests and 155 assertions.
- The cleanup regression exercised `Native\Mobile\Traits\CleansEnvFile` against representative Android, Google, App Store, and iOS credentials and confirmed no tested secret or password remained.
- `php artisan help native:release`, `native:credentials`, `native:package`, and `native:version` with JSON formatting confirmed the installed command contracts without mutating the release version, generating credentials, packaging, or uploading.
- `php artisan config:show nativephp.app_store_connect` confirmed the file-based API key path and other store values are unset until real credentials are supplied.
- `zsh -lic 'php artisan native:debug --json --no-interaction'` passed with NativePHP 3.3.6, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no Xcode; plugin validation passed with no installed plugins.
- Scoped Pint and Larastan for the NativePHP configuration files passed with zero errors.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev` passed with no known dependency vulnerabilities.
- `php artisan test --compact` passed with 152 tests and 773 assertions.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors; the NativePHP configuration file is clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; none is in a deployment-phase file.

### Known Limitations

- No Android keystore, Google service-account key, Apple certificate, provisioning profile, App Store API key, team ID, or store application access was supplied; signed packages and uploads therefore remain intentionally unavailable.
- The public semantic version is not yet selected and local development remains on `DEBUG`; `native:release` must be run deliberately when cutting the first release.
- iOS packaging remains unavailable on this Intel Mac because full Xcode and Apple silicon are absent.
- Release builds, credential generation, signed packaging, profile validation, build-number synchronization, and store uploads were not auto-run, per the installed NativePHP project guidance and because they have persistent external consequences.

### Git Delivery

- Commit `2c37d0c` (`chore: reconcile NativePHP deployment guide`) contains only the deployment environment contract, credential ignore rule, NativePHP secret cleanup, focused tests, and phase progress files.
- Push to `origin main` succeeded (`6c13c56..2c37d0c`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## NativePHP Mobile 3 Development Guide Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the installed NativePHP Mobile 3 development workflow with the official development guide and the existing Laravel 13, Inertia 3, Vue 3, and Vite 8 application.
- Confirmed `nativephpMobile()` and `nativephpHotFile()` are wired into Vite, with dedicated `build:ios` and `build:android` scripts using the required platform modes.
- Confirmed Axios is a direct production dependency and Inertia 3 is explicitly configured with `axiosAdapter()`, allowing NativePHP's build-time Axios interception to route requests through the embedded PHP runtime.
- Added `database` to the NativePHP hot-reload paths while retaining application, route, configuration, resource, and public asset watching.
- Added `/public/ios-hot` and `/public/android-hot` to the root ignore rules alongside `/public/hot` so platform-specific Vite development state cannot be committed.
- Confirmed `NATIVEPHP_APP_VERSION=DEBUG` remains active for development re-extraction and the installed `native:run`, `native:open`, and `native:watch` commands expose the documented platform and watch options.
- Avoided speculative `System::isIos()` or `System::isAndroid()` branches because the application currently has no platform-specific behavior.
- Preserved the self-contained on-device Laravel and SQLite architecture without adding a production remote client/server dependency; Vite networking remains development-only HMR.

### Changed Files

- `.gitignore`
- `config/nativephp.php`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded. Axios and all required NativePHP/Vite/Inertia packages were already installed.

### Verification

- The focused development test failed first because `database` was absent from the hot-reload paths, then `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 8 tests and 108 assertions.
- `php artisan config:show nativephp.hot_reload` confirmed the complete effective watch and exclusion lists.
- `php artisan help native:run --format=json`, `native:open`, and `native:watch` confirmed the installed command contracts without launching a build, IDE, device, or watcher.
- `zsh -lic 'php artisan native:debug --json --no-interaction'` passed with NativePHP 3.3.6, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no Xcode; plugin validation passed with no installed plugins.
- Scoped Pint and Larastan for the NativePHP configuration files passed with zero errors.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev` passed with no known dependency vulnerabilities.
- `php artisan test --compact` passed with 150 tests and 726 assertions.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors; the NativePHP configuration file is clean.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; none is in a development-phase file.

### Known Limitations

- Native iOS development remains unavailable on this Intel Mac because full Xcode and Apple silicon are absent; the configured Android toolchain is available.
- Platform-mode frontend builds, native compilation, IDE launch, device/emulator launch, and long-running watchers were not auto-run, per the installed NativePHP project guidance.
- Real-device HMR requires the device and development workstation to share a network; this is development tooling only and is not an application runtime dependency.

### Git Delivery

- Commit `30433e2` (`chore: reconcile NativePHP development guide`) contains only the hot-file ignore rules, NativePHP hot-reload configuration, focused development test, and phase progress files.
- Push to `origin main` succeeded (`e277f15..30433e2`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## NativePHP Mobile 3 Command Reference Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the official NativePHP Mobile 3 command reference with the latest installed stable release, NativePHP Mobile 3.3.6.
- Confirmed all 20 documented development, release, and plugin commands are registered through Laravel Artisan.
- Confirmed the executable root `./native` shortcut is byte-for-byte identical to the package-provided wrapper, so commands and options delegate to Artisan without a parallel implementation.
- Added focused command-definition contracts for platform and device arguments, Jump networking controls, development watch/start URL controls, signing, packaging, validation, store-upload controls, and plugin lifecycle operations.
- Locked release, export-method, and Play Store track defaults to the installed command contract and verified required release and plugin-uninstall arguments.
- Preserved the self-contained on-device Laravel and SQLite architecture without adding a remote client/server runtime.

### Changed Files

- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded. `composer outdated nativephp/mobile --direct` confirmed 3.3.6 is the latest stable release.

### Verification

- The focused test failed first only because Pest does not expose an executable-file expectation for strings; the assertion was corrected to use PHP's executable-bit check. `php artisan test --compact tests/Feature/NativePhpMobileTest.php` then passed with 13 tests and 256 assertions.
- `php artisan test --compact` passed with 155 tests and 874 assertions.
- Scoped Pint passed for the modified NativePHP Pest test.
- File-scoped Larastan reports five pre-existing errors on lines before the new command-reference coverage; the newly added command-contract lines introduce no Larastan finding.
- `./native version`, `./native plugin:list --json`, and `./native plugin:validate` passed without changing application or native state; no NativePHP plugins are installed.
- Artisan JSON help output confirmed the installed `native:install`, `native:run`, `native:watch`, `native:jump`, `native:open`, `native:package`, release, credentials, build-number, and plugin signatures without invoking their operations.
- `native:debug --json` passed with NativePHP 3.3.6, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no Xcode.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev` passed with no known dependency vulnerabilities.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.
- Full Larastan remains red with 364 existing application errors.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; no frontend file was changed in this phase.

### Known Limitations

- The current official page advertises `native:install --fresh` and `--without-icu`, while the latest stable 3.3.6 command exposes `--force`, `--no-force`, `--with-icu`, and `--skip-php`. It also shows `native:check-build-number` without a platform argument, while 3.3.6 requires one. Tests intentionally follow the executable installed package contract rather than inventing application-level replacements for upstream command options.
- Native iOS operations remain unavailable on this Intel Mac because full Xcode and Apple silicon are absent; the Android toolchain is available.
- Native builds, IDE/device or emulator launches, Jump servers, watchers, log tailing, credential generation, release version changes, packaging, profile validation, and store uploads were not auto-run, per the installed NativePHP project guidance and because several are long-running or have persistent external consequences.

### Git Delivery

- Commit `1a87b09` (`chore: reconcile NativePHP command reference`) contains only the focused command contracts and phase progress files.
- Push to `origin main` succeeded (`022b0d7..1a87b09`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## NativePHP Mobile 3 Architecture Overview Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the official NativePHP Mobile 3 architecture overview with the installed 3.3.6 package and Laravel 13 application bootstrap.
- Confirmed the project's PHP constraint accepts the package's embedded PHP 8.4 runtime, the NativePHP package service provider is auto-discovered, and install/run commands are registered.
- Added focused contracts for the NativePHP 3.3 package range, persistent runtime mode, and native-mode selection of the package-provided `mobile_public` filesystem.
- Confirmed the development SQLite file is excluded from the application bundle while migrations remain bundled for execution against the native shell's persistent database.
- Confirmed the SQLite connection enables foreign keys, WAL journaling, normal synchronization, and a bounded busy timeout suitable for the on-device runtime.
- Confirmed the public storage link points to persistent `storage/app/public` data.
- Inspected both native shells: Android creates the persistent SQLite file then clears caches, recreates the storage link, and runs migrations; iOS performs version-aware extraction, migrations, cache clearing, and storage-link creation.
- Preserved the embedded Laravel and SQLite architecture without adding a remote web service, API client, or second application runtime.

### Changed Files

- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded.

### Verification

- The first focused run exposed two invalid test probes: Composer Semver is not an application dependency, and Laravel's immutable environment repository cannot be changed after bootstrap. Both probes were replaced with dependency-free installed-version checks and a direct native-filesystem configuration contract.
- `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 16 tests and 277 assertions.
- `php artisan test --compact` passed with 158 tests and 895 assertions.
- Scoped Pint passed for the modified NativePHP Pest test, and scoped `git diff --check` passed.
- File-scoped Larastan reports the same five pre-existing errors on lines before the new architecture coverage; no new line reports an error.
- `php artisan config:show database.connections.sqlite` confirmed SQLite, foreign keys, 5-second busy timeout, WAL, normal synchronization, and deferred transactions.
- `php artisan config:show filesystems.links` confirmed `public/storage` targets persistent `storage/app/public`.
- `./native version` and `native:debug --json` passed with NativePHP 3.3.6, local PHP 8.4.16, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 17.0.16, CocoaPods 1.17.0, and no Xcode.
- `composer check-platform-reqs --no-dev` passed for PHP 8.4.16 and all production extensions.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, and `npm audit --omit=dev` passed with no known dependency vulnerabilities.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Full Larastan remains red with 364 existing application errors.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files; no frontend file was changed in this phase.

### Known Limitations

- Native iOS execution remains unavailable on this Intel Mac because full Xcode and Apple silicon are absent; the configured Android toolchain is available.
- The native shells' actual extraction, migration, cache, symlink, and web-view startup sequence requires a platform build and simulator or physical device for end-to-end confirmation.
- Native installation, platform-mode frontend builds, native compilation, device or emulator launch, IDE launch, and watchers were not auto-run, per the installed NativePHP project guidance.

### Git Delivery

- Commit `6d7bdbb` (`chore: reconcile NativePHP architecture overview`) contains only the focused architecture contracts and phase progress files.
- Push to `origin main` succeeded (`887d7a7..6d7bdbb`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## NativePHP Mobile 3 Jump Reconciliation

### Status

Completed.

### Completed Work

- Reconciled the official Jump workflow with the installed NativePHP Mobile 3.3.6 proxy, managed Laravel server, native bridge, mDNS advertisement, and Vite HMR proxy.
- Added `npm run jump`, which starts `native:jump` and Vite together through the existing `concurrently` dependency and terminates both processes when either exits.
- Added focused contracts for every Jump network and fallback option: host/IP selection, HTTP, WebSocket, TCP bridge, Vite proxy, managed/BYO Laravel ports, mDNS disabling, and browser QR fallback.
- Confirmed Laravel forwards `JUMP_BRIDGE_PORT` and `JUMP_WS_PORT` into the managed `artisan serve` process.
- Confirmed the existing Vite configuration supplies both the NativePHP hot-file path and NativePHP Mobile plugin; Jump supplies the device-facing HMR proxy without another Vite server or CORS configuration.
- Kept Jump strictly development-only. The production application remains embedded Laravel plus SQLite and does not depend on a remote client/server deployment.

### Changed Files

- `package.json`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm dependency was added, removed, or upgraded. The existing `concurrently` development dependency was reused.

### Verification

- The first focused test run failed only because the new `jump` package script did not exist, confirming the test covered the integration; it passed after the script was added.
- `php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed with 18 tests and 306 assertions.
- `php artisan test --compact` passed with 160 tests and 924 assertions.
- Scoped Pint, package Prettier verification, and scoped `git diff --check` passed.
- File-scoped Larastan reports the same five pre-existing errors on lines before the new Jump coverage; no new line reports an error.
- `php artisan help native:jump --format=json` confirmed the complete installed command definition and safe defaults without starting the server.
- `./native version` and `native:debug --json` passed with NativePHP 3.3.6, local PHP 8.4.16, embedded PHP 8.4.23, Android Studio 2026.1.2, Gradle 8.13, Java 24.0.2, CocoaPods 1.17.0, and no Xcode.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, `composer check-platform-reqs --no-dev`, and `npm audit --omit=dev` passed with valid dependencies, satisfied production platform requirements, and no known vulnerabilities.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Full Larastan remains red with 364 existing application errors.
- Full Vue type checking remains red with 9 existing errors, full ESLint with 72 existing errors, and full resource Prettier verification with 13 existing files. The only frontend manifest changed in this phase is the formatted `package.json` script entry.

### Known Limitations

- Jump still requires the NativePHP Jump companion app and a physical device on the same local network; that device handshake was not started automatically.
- The installed 3.3.6 command selects its Jump WebSocket, TCP bridge, and Vite proxy ports independently, defaults them to 3001, 3002, and 3003, and can auto-increment occupied ports. The general `NATIVEPHP_WS_PORT=8081` server setting is not the Jump runtime default; use `--ws-port` when an explicit Jump port is needed.
- Bring-your-own Laravel server mode requires the caller to expose its selected bridge port as `JUMP_BRIDGE_PORT`; the managed workflow performs this passthrough automatically.
- Jump, Vite dev mode, native builds, device or emulator launches, IDE launches, and watchers were not auto-run, per the installed NativePHP project guidance.

### Git Delivery

- Commit `6916a9f` (`chore: integrate NativePHP Jump workflow`) contains only the Jump script, focused NativePHP contracts, and phase progress files.
- Push to `origin main` succeeded (`32c0cd5..6916a9f`).
- Unrelated staged and unstaged sidebar, navigation, task, project, export, profile, members, preferences, and planning work remains excluded and preserved.

## UI Phase: Warm Precision Workspace Pages

### Status

Completed.

### Scope And Decisions

- Redesign Activity, Notifications, Calendar, and Projects as one responsive Warm Precision interface with a restrained orange accent and strong light/dark contrast.
- Preserve the existing Inertia page contracts, workspace-scoped reads, project creation flow, notification actions, and generated Wayfinder links.
- Add shared Vue presentation primitives instead of repeating page-local header and metric structures.
- Move all copy for the four pages to semantic English, Lithuanian, and Russian translations with Laravel's English fallback.
- Format dates and times with the authenticated user's locale and timezone while keeping supplied page props immutable.
- Preserve the already staged Wayfinder conversion in `resources/js/pages/projects/Index.vue` and exclude unrelated worktree changes from delivery.

### Changed Files

- Rebuilt the Activity, Notifications, Calendar, and Projects Inertia pages around a shared responsive page header and metric presentation system.
- Added typed workspace UI props, semantic English, Lithuanian, and Russian copy, and locale/timezone-aware formatting.
- Added a workspace-scoped calendar controller that supplies normalized date DTOs and fixed scoped notification read actions.
- Reworked project creation to use the Inertia v3 HTTP client and generated Wayfinder endpoints without adding a dependency.
- Added focused Pest coverage for page contracts, translations, calendar/project/activity workspace isolation, and notification mutations.

### Migrations And Packages

No migration or package change was made.

### Verification

- `php artisan test --compact tests/Feature/WorkspacePagesTest.php tests/Feature/ActivityPageTest.php tests/Feature/ProjectTest.php tests/Feature/AppNavigationTest.php` passed with 20 tests and 231 assertions.
- Scoped Larastan passed with zero errors; scoped ESLint and Prettier verification passed for every changed Vue and TypeScript file.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.
- Browser verification passed on all four pages at 1440px and 390px in light and dark modes, including filters, notification tabs/actions, calendar views, project creation, horizontal overflow, and JavaScript/page errors.
- `npm run build` passed after the final mobile-width correction; Vite transformed 3,360 modules and emitted only the existing optional `fontaine` notice.
- The full Pest suite passed with 188 tests and 1,147 assertions. The existing repository baseline remains red with 341 Larastan errors, seven Vue type errors, one ESLint error, and 24 Prettier files outside this phase.

### Known Limitations

- Full Larastan, Vue type checking, ESLint, and resource-wide Prettier verification remain blocked by unrelated application and concurrent worktree issues; all phase-scoped checks pass.
- Vite continues to report the existing optional `fontaine` font-fallback optimization notice.

### Git Delivery

- Commit `576d602` (`feat: redesign workspace overview pages`) contains only the Warm Precision pages, shared presentation/translation infrastructure, scoped backend corrections, focused tests, and this phase record.
- Push to `origin main` succeeded (`13ec033..576d602`).
- Existing localization, task, settings, import/export, attachment, NativePHP, and planning changes remain outside this phase and are preserved in the worktree.

## Frontend Localization, Wayfinder, And Task State Isolation

### Status

Completed.

### Scope And Decisions

- Replace remaining frontend user-facing English literals with semantic Laravel translation keys supplied to Inertia for English, Lithuanian, and Russian, with Laravel's English fallback.
- Route all locale-sensitive dates and numbers through one shared formatter that respects the authenticated user's language and timezone preferences.
- Remove the custom global `route()` shim and replace its consumers, hardcoded settings paths, and other application route strings with generated Wayfinder functions.
- Reset task detail, comment, and checklist draft state whenever the selected task identity changes so unsaved values cannot leak between tasks.
- Add focused Pest localization and routing contracts plus a dependency-free frontend state regression test.
- Preserve and exclude unrelated data-transfer, attachment, runtime, and design work already present in the worktree.

### Changed Files

- `app/Http/Middleware/HandleInertiaRequests.php`
- `lang/en/ui.php`, `lang/lt/ui.php`, `lang/ru/ui.php`
- Shared frontend translation, locale-formatting, task-detail state, application bootstrap, and Inertia type files under `resources/js`.
- Auth, account-security, dashboard, project, task, workspace, settings, navigation, command-palette, and notification Vue consumers under `resources/js`.
- `tests/Feature/FrontendLocalizationTest.php`, `resources/js/composables/useTaskDetailState.test.ts`
- `package.json`, `composer.json`, `tsconfig.json`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm dependency was added, removed, or upgraded. The existing package and Composer verification commands now include the dependency-free Node frontend state test.

### Verification

- The new frontend test failed first because `useTaskDetailState.ts` did not exist, then passed after implementation. It opens task A, applies unsaved detail, comment, checklist-name, and checklist-item edits, switches to task B, and confirms only task B values and empty drafts remain.
- `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/TodoTest.php tests/Feature/AppNavigationTest.php`: passed with 19 tests and 125 assertions.
- `php artisan test --compact`: passed with 188 tests and 1,147 assertions.
- `npm run test:frontend`, `npm run types:check`, `npm run lint:check`, and `npm run build`: passed. Vite emitted only the existing optional `fontaine` notice.
- `vendor/bin/pint --dirty --format agent`, `composer validate --strict --no-check-publish`, and `git diff --check`: passed.
- Full Larastan was executed and remains red with 328 pre-existing application errors outside this frontend phase. The new translation helper return type is specified; the touched middleware still reports three existing or concurrent model/workspace-translation findings.
- Full `npm run format:check` was executed and remains red only for the pre-existing `resources/js/composables/useKeyboard.ts` and `resources/js/composables/useKeyboardShortcuts.ts`; all phase frontend files pass scoped Prettier formatting.

### Known Limitations

- Passkey relative-time strings continue to arrive preformatted from the installed passkeys package; application-owned dates and numbers now use the shared locale and timezone formatter.
- Active data-transfer, attachment, upload-runtime, and design work in the shared worktree remains excluded from this phase.

### Git Delivery

- Commit `350d19d` (`fix: localize frontend and reset task state`) contains only this phase's implementation, tests, configuration, and attributable progress entry.
- Push to `origin main` succeeded (`9791310..350d19d`).
- Unrelated staged and unstaged data-transfer, attachment, runtime, and design work remains preserved outside this phase.

## ESLint Cleanup Batch 1: Dead Frontend Bindings

### Status

Completed.

### Scope And Decisions

- Remove imports, helpers, and template loop bindings that are not consumed by the rendered pages or Pinia store.
- Keep behavior unchanged and avoid formatter-driven changes outside the flagged lines.
- Preserve and exclude the active Warm Precision workspace-page work already present in the worktree.

### Changed Files

- `resources/js/pages/projects/Show.vue`
- `resources/js/pages/settings/Backup.vue`
- `resources/js/pages/settings/Notifications.vue`
- `resources/js/pages/settings/Security.vue`
- `resources/js/pages/tasks/Index.vue`
- `resources/js/stores/todo.ts`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- `npm run lint:check` removed all 12 findings covered by this batch. The repository total moved from 73 to 62 because a concurrent Notifications-page edit added one new type-import ordering finding while this batch was running.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.

### Known Limitations

- The remaining ESLint findings are intentionally deferred to later small batches.
- Concurrent Warm Precision workspace-page and task-board work remains outside this batch.

### Git Delivery

Commit message: `fix: remove dead frontend bindings`. Push to `origin main` will be attempted immediately after the isolated commit. Unrelated worktree changes remain excluded.

## Task Board Selector Cleanup

### Status

Completed.

### Scope And Decisions

- Remove the selectable board mode from the task index because it currently hides the working list without rendering a board.
- Remove the board choice from the Default View preference so the unavailable mode is not exposed through a second UI selector.
- Keep the task list, calendar preference, backend preference compatibility, dormant board component, reorder endpoints, and dependencies untouched.
- Defer a board implementation until the Vue frontend has an approved accessible drag-and-drop architecture; the installed `@dnd-kit/core` and `@dnd-kit/sortable` packages require React and cannot provide Vue keyboard sensors as currently installed.

### Changed Files

- `resources/js/pages/tasks/Index.vue`
- `resources/js/pages/settings/Preferences.vue`
- `tests/Feature/TodoTest.php`
- `tests/Feature/Settings/PreferencesTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded.

### Verification

- The initial focused Pest run failed only on the two new source contracts: the task index still exposed the board toggle and Preferences still exposed `value="board"`. This confirmed both regressions before implementation.
- `php artisan test --compact tests/Feature/TodoTest.php tests/Feature/Settings/PreferencesTest.php` passed with 19 tests and 62 assertions after the UI cleanup.
- The stable full `php artisan test --compact` run passed with 162 tests and 931 assertions. A preceding parallel run had one transient dashboard failure because Vite was replacing the production manifest concurrently; rerunning against stable build output passed.
- The post-build dashboard and focused selector run passed with 21 tests and 65 assertions.
- Scoped Pint passed for the two changed Pest files. Scoped ESLint and Prettier verification passed for both changed Vue files.
- `npm run build` passed with only the existing optional `fontaine` notice.
- Full Larastan remains red with 365 existing application errors. Full Vue type checking remains red with 9 existing errors, full ESLint with 46 existing errors, and full resource Prettier verification with 17 existing files; none of the scoped selector files add an ESLint or Prettier failure.
- `git diff --check` passed.

### Known Limitations

- `BoardView.vue` and the React-only `@dnd-kit` packages remain dormant and unreachable; they were not expanded or deleted because this phase intentionally removes only the dead UI entry points.
- Backend preference compatibility still accepts the historical `board` value, but neither the task view selector nor the Default View selector offers it.

### Git Delivery

- Commit `fix: remove unavailable board selectors` contains only the two selector removals, focused Pest coverage, and this progress record.
- Push to `origin main` succeeded.
- Existing unrelated workspace UI, localization, and lint-cleanup changes remain excluded and preserved.

## ESLint Cleanup Batch 2: Task UI Control Flow

### Status

Completed.

### Scope And Decisions

- Normalize imports in four task UI components according to the repository's configured group ordering.
- Make conditional control flow explicit with braces and required statement separation.
- Remove the unused Inertia router import from the filter bar instead of suppressing the finding.
- Preserve component behavior and avoid whole-file formatting.

### Changed Files

- `resources/js/components/task/BulkActions.vue`
- `resources/js/components/task/RecurringBadge.vue`
- `resources/js/components/task/TaskCreateDialog.vue`
- `resources/js/components/task/TaskFilterBar.vue`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- `npm run lint:check` resolved all 25 findings in the four targeted task UI components. The repository total moved from 62 to 22 while concurrent calendar work removed 17 additional findings and concurrent project work introduced two type-import findings.
- `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.

### Known Limitations

- The remaining ESLint findings are intentionally deferred to later small batches.
- Concurrent localization, workspace-page, calendar, project, and task-board work remains outside this batch.

### Git Delivery

Commit message: `fix: normalize task UI control flow`. Push to `origin main` will be attempted immediately after the isolated commit. Unrelated worktree changes remain excluded.

## ESLint Cleanup Batch 3: Board And Keyboard Guards

### Status

Completed.

### Scope And Decisions

- Remove unused Vue, drag-and-drop, and store imports from the task board and keyboard helper.
- Keep the board's existing native drag event implementation intact rather than retaining unused React-oriented sortable helpers.
- Make guard clauses explicit and separate event-handling statements according to the configured ESLint rules.
- Avoid whole-file formatting or unrelated behavioral changes.

### Changed Files

- `resources/js/components/task/BoardView.vue`
- `resources/js/composables/useKeyboard.ts`
- `resources/js/composables/useKeyboardShortcuts.ts`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- The first `npm run lint:check` rerun identified two additional spacing findings inside this batch's guarded blocks; both were corrected without broad formatting.
- The final batch rerun resolved all 16 targeted board and keyboard findings. The repository total moved from 22 to one while concurrent work resolved the six other prior findings and introduced one type-import finding in a new task-detail composable.
- `npm run build` passed after both batch checks; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed.

### Known Limitations

- One ESLint finding remains in a newly added concurrent file and is deferred to a final isolated batch.
- Concurrent localization and workspace UI work remains outside this batch.

### Git Delivery

Commit message: `fix: clean board and keyboard guards`. Push to `origin main` will be attempted immediately after the isolated commit. Unrelated worktree changes remain excluded.

## Data Transfer And Upload Boundaries

### Status

Completed.

### Scope And Decisions

- Limit workspace imports to 5 MiB and 1,000 combined project/task records, with selected-format MIME, extension, structure, field, enum, date, and workspace-reference validation.
- Keep task attachments at 10 MiB while allowing only explicitly supported raster image, PDF, plain-text, CSV, and JSON MIME/extension pairs; preserve the existing 2 MiB image-only avatar contract.
- Authorize both web and API attachment uploads through the bound task before storing content.
- Execute every JSON and CSV import inside a database transaction so any later record failure rolls back the complete batch.
- Stream JSON, CSV, and Markdown exports with bounded lazy queries scoped through the authorized workspace; do not materialize complete datasets or payload strings.
- Escape spreadsheet-formula prefixes in CSV cells while preserving round-trip-compatible headers.

### Changed Files

- `app/Actions/UploadAttachment.php`
- `app/Http/Controllers/Api/AttachmentController.php`
- `app/Http/Controllers/AttachmentController.php`
- `app/Http/Controllers/ExportController.php`
- `app/Http/Controllers/ImportController.php`
- `app/Http/Requests/ImportWorkspaceRequest.php`
- `app/Http/Requests/StoreAttachmentRequest.php`
- `app/Services/ExportService.php`
- `app/Services/ImportService.php`
- `lang/en/data_transfer.php`
- `lang/lt/data_transfer.php`
- `lang/ru/data_transfer.php`
- `tests/Feature/DataTransferTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was needed.

### Verification

- The focused test matrix first failed all 14 cases against the original implementation, demonstrating the missing size, type, row-count, rollback, authorization, streaming, scope, and formula protections.
- `php artisan test --compact tests/Feature/DataTransferTest.php`: passed, 16 tests and 62 assertions.
- The wider focused attachment/avatar/data-transfer run passed 53 tests and 513 assertions.
- Pint with `--format agent` passed for every PHP file changed by this phase.
- Scoped Larastan analysis for every changed application PHP file passed with zero errors.
- Full Larastan remains blocked by 327 pre-existing errors in unrelated actions, controllers, models, and resources.
- Full Pest passed all 195 tests with 1,178 assertions.
- `npm run test:frontend`: passed, one test.
- `npm run lint:check`: passed with zero errors or warnings.
- `npm run types:check`: remains blocked by two unrelated errors in `useAutosave.ts` and `projects/Show.vue`.
- `npm run format:check`: remains blocked by one unrelated file, `resources/js/pages/tasks/Show.vue`.
- `npm run build`: passed; Vite transformed 3,358 modules and emitted only the existing optional `fontaine` notice.

### Known Limitations

- Production PHP must allow enough multipart request overhead for the documented 10 MiB attachment and 5 MiB import limits; the local Herd runtime is configured separately.
- CSV import intentionally ignores the human-readable `Assigned To` column because names are not stable workspace-scoped identifiers.
- JSON import validates exported label, tag, and checklist structures but preserves the existing import behavior of creating projects and tasks only.

### Git Delivery

Commit message: `fix: harden data transfer boundaries`. Push to `origin main` will be attempted immediately after the isolated commit. Existing unrelated localization, workspace UI, calendar, notification, and frontend cleanup changes remain excluded and preserved.

## ESLint Cleanup Final Verification

### Status

Completed.

### Scope And Decisions

- Cleared the tracked ESLint baseline in three small code batches without disabling rules or running a broad formatter.
- Used dead-code removal, configured import ordering, explicit control-flow braces, and required statement separation to address the underlying findings.
- Preserved a top-level type-import correction in the concurrent untracked `resources/js/composables/useTaskDetailState.ts` file without staging the unrelated feature implementation.
- Kept all other active localization, workspace UI, calendar, notification, and data-transfer changes outside the lint commits.

### Changed Files

- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded.

### Verification

- Final `npm run lint:check` passed with zero errors and zero warnings in the current worktree.
- Final `npm run build` passed; Vite emitted only the existing optional `fontaine` notice.
- Scoped `git diff --check` passed for every lint batch and the preserved concurrent type-import correction.
- No `eslint-disable` comment was needed.

### Known Limitations

- The corrected task-detail composable remains untracked as part of an active concurrent feature and is intentionally excluded from this delivery record's commit.
- Unrelated active worktree changes remain the responsibility of their owning phases.

### Git Delivery

- Commit `613c80d` (`fix: remove dead frontend bindings`) was pushed to `origin main`.
- Commit `bdac5ae` (`fix: normalize task UI control flow`) was pushed to `origin main`.
- Commit `4ed0952` (`fix: clean board and keyboard guards`) was pushed to `origin main`.
- This final delivery record will be committed as `docs: record ESLint cleanup delivery` and pushed separately to `origin main`.

## Task Index Production Build Repair

### Status

Completed.

### Scope And Decisions

- Resolve the task index Vue compiler failure without changing the localized date output or unrelated in-progress frontend work.
- Reuse the shared locale- and timezone-aware `useUi` date formatter under a distinct local name.
- Return the task priority badge's exact supported variant union so this page also passes Vue type checking.

### Changed Files

- `resources/js/pages/tasks/Index.vue`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- `npm run build`: passed; Vite transformed 3,360 modules and generated the production bundle. The existing optional `fontaine` optimization notice remains non-blocking.
- Targeted ESLint for `resources/js/pages/tasks/Index.vue`: passed.
- Targeted Prettier verification for the repaired Vue page and this progress file: passed.
- `npm run types:check`: the task index error is resolved; the repository check remains blocked by seven unrelated errors in passkey, task-create, autosave, task-state-test, and project-show files.
- Scoped `git diff --check`: passed.

### Known Limitations

- The optional `fontaine` notice was not changed because resolving it requires either an npm dependency change or disabling optimized font fallbacks.
- Seven unrelated pre-existing/concurrent Vue type errors remain outside this repair.

### Git Delivery

No commit or push was created because both changed files already contain intertwined concurrent user work that cannot be isolated safely from this repair. All unrelated worktree changes remain preserved.

## Herd Upload Runtime Limit Repair

### Status

Completed.

### Scope And Decisions

- Align Herd's PHP 8.4 request limits with the existing 10 MiB attachment and 5 MiB import validation boundaries.
- Set `upload_max_filesize` to 10 MiB and reserve multipart overhead with a 12 MiB `post_max_size` boundary.
- Use Herd's supported per-PHP-version `php.ini` configuration after confirming parked sites do not load a project `public/.user.ini` through Herd's router.

### Changed Files

- `~/Library/Application Support/Herd/config/php/84/php.ini` (local Herd runtime configuration, outside Git)
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- Reproduced the PHP startup warning with a 3 MiB request against the original 2 MiB `post_max_size` boundary.
- Confirmed new CLI values of `upload_max_filesize=10M` and `post_max_size=12M` after editing Herd's PHP 8.4 configuration.
- Fully stopped and started Herd so its previously stale PHP 8.4 FPM master reloaded the settings.
- Confirmed an 11 MiB POST reaches Laravel and returns the expected HTTP 419 CSRF response instead of `PostTooLargeException`.
- Confirmed a 13 MiB POST remains rejected at PHP startup against the new 12 MiB boundary.
- Focused import and attachment size-boundary Pest tests passed: 2 tests and 8 assertions.
- Scoped `git diff --check` passed for this progress update.

### Known Limitations

- Herd applies these values to all local sites served by its PHP 8.4 runtime; production PHP-FPM must configure equivalent limits independently.
- No application PHP behavior changed; the discarded `public/.user.ini` experiment and its temporary test were removed after the live Herd check proved that parked sites do not load it.

### Git Delivery

No commit or push was created because the functional fix lives in Herd's user-level PHP configuration and `docs/progress.md` already contains intertwined user work. Existing unrelated worktree changes remain excluded and preserved.

## UI Phase: Warm Precision Across All Pages

### Status

Completed.

### Before Implementation Inventory

- Confirmed `/projects` as the live visual reference: warm neutral canvas, orange editorial accent, oversized page title, integrated metric strip, rounded collection surfaces, and restrained depth.
- Inventoried every routed Inertia page and found the Warm Precision system already present on Activity, Notifications, Calendar, and Projects, while Dashboard, Tasks, task/project detail, Workspaces, Settings, and Auth still used starter-kit styling.
- Preserved the active localization, transfer/upload, Wayfinder, and task-state work already present in the dirty worktree.

### Scope And Decisions

- Extend the shared `WorkspacePageHeader` and `WorkspaceMetric` presentation to Dashboard, Tasks, task detail, project detail, and Workspaces without changing their Inertia contracts or state-change behavior.
- Give Settings and every Fortify/Auth page shared responsive shells that carry the same orange line, rounded white surface, warm background, and light/dark treatment.
- Align common cards, buttons, inputs, select triggers, dialogs, headings, focus rings, corner radius, and sidebar tokens so secondary pages and nested forms inherit the design automatically.
- Keep all copy semantic and translated in English, Lithuanian, and Russian; add Workspaces header and metric labels to each locale.
- Correct the task index's visible pagination total to read Laravel resource pagination from `meta.total`, with backward-compatible fallbacks.

### Changed Files

- Shared theme and primitives in `resources/css/app.css`, `resources/js/components/Heading.vue`, and the card, button, input, select, and dialog UI components.
- Shared shells in `resources/js/layouts/auth/AuthSimpleLayout.vue` and `resources/js/layouts/settings/Layout.vue`.
- Warm Precision page implementations in Dashboard, Tasks index/detail, Projects detail, and Workspaces index.
- English, Lithuanian, and Russian workspace copy in the three `lang/*/ui.php` catalogs.
- Focused presentation contract coverage in `tests/Feature/FrontendDesignTest.php`.

### Migrations And Packages

No migration or Composer/npm package was added, removed, or upgraded.

### Verification

- The new presentation contract first failed all five legacy page/shell datasets, then passed with 6 tests and 21 assertions after implementation.
- Focused frontend/design/localization/workspace/dashboard/task/project Pest coverage passed with 36 tests and 238 assertions.
- The full Pest suite passed with 196 tests and 1,181 assertions.
- Frontend state tests passed, and full ESLint plus full resource Prettier verification passed with zero findings.
- Scoped Vue type checking passes for every changed file; the full check is blocked only by the existing `useAutosave.ts` import of the unavailable `@vueuse/core` `debounce` export.
- The production Vite build passed with only the existing optional `fontaine` notice.
- Pint and `git diff --check` passed. Full Larastan remains red with 327 existing application errors; this frontend-only phase adds no PHP application error.
- Browser verification passed at 1440px and 390px for Dashboard, Tasks, Project detail, Workspaces, Settings, and Auth, including the dark mobile Tasks view. All checked pages had no horizontal overflow, console errors, or JavaScript errors.

### Known Limitations

- The repository-wide Vue type check remains blocked by the unrelated `resources/js/composables/useAutosave.ts` import noted above.
- Full Larastan retains its existing application-wide backlog of 327 errors.
- The optional `fontaine` build notice remains unchanged because resolving it requires a dependency or build-configuration decision outside this UI phase.

### Git Delivery

- Commit `bf2ed41` (`feat: extend warm precision design`) was pushed successfully to `origin main`.
- This delivery record will be committed as `docs: record warm precision delivery` and pushed separately.

## Frontend Formatting Cleanup

### Status

Completed.

### Scope And Decisions

- Run `npm run format:check` before editing; it reported only the two keyboard composables listed below.
- Run the project's `npm run format` script through a temporary scoped ignore file so Prettier could write only the explicitly reported files.
- Keep the cleanup formatting-only: the final commit diff contains line wrapping and indentation changes with no logic, dependency, or generated-file changes.
- Preserve all unrelated staged and unstaged work in the shared worktree.

### Changed Files

- `resources/js/composables/useKeyboard.ts`
- `resources/js/composables/useKeyboardShortcuts.ts`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- `npm run format:check`: passed; all files under `resources/` match Prettier style.
- `npm run lint:check`: passed as part of `composer run ci:check`.
- `npm run test:frontend`: passed, 1 test.
- `npm run build`: passed; Vite transformed 3,358 modules and generated the production bundle. The existing optional `fontaine` notice remains non-blocking.
- `php artisan test --compact`: passed, 196 tests and 1,181 assertions.
- `git diff --check`: passed, and the isolated cleanup commit was reviewed as formatting-only.
- `npm run types:check`: remains blocked by the unrelated `useAutosave.ts` import of a non-exported `debounce` member from `@vueuse/core`.
- `composer run lint:check`: remains blocked by pre-existing Pint findings in 21 unrelated PHP files.
- `composer run types:check`: remains blocked by 327 pre-existing Larastan errors.

### Known Limitations

- The repository-wide PHP and Vue type/style baselines listed above remain outside this formatting-only cleanup.
- The optional `fontaine` build notice was not changed because it is unrelated to formatting.

### Git Delivery

- Commit `be44072` (`style: format keyboard composables`) contains only the two formatting fixes and was pushed to `origin main`.
- This delivery record is committed and pushed separately so the cleanup commit remains isolated.

## End-To-End Verification And Critical/High Finding Handoff

### Status

Completed with every requested verification command passing.

- Critical findings: 0 resolved, 2 partially resolved, and 4 open.
- High findings: 4 resolved, 1 partially resolved, and 5 open.

### Exact Verification Results

| Command                                    | Exit | Current status                                                                                                                        |
| ------------------------------------------ | ---: | ------------------------------------------------------------------------------------------------------------------------------------- |
| `vendor/bin/pint --dirty --format agent`   |    0 | Passed with no reported findings.                                                                                                     |
| `php artisan test --compact`               |    0 | Passed: 332 tests and 1,442 assertions.                                                                                               |
| `vendor/bin/phpstan analyse --no-progress` |    0 | Passed with zero errors.                                                                                                              |
| `npm run types:check`                      |    0 | Passed with zero Vue/TypeScript errors.                                                                                               |
| `npm run lint:check`                       |    0 | Passed with zero ESLint errors or warnings.                                                                                           |
| `npm run format:check`                     |    0 | Passed; all files under `resources/` match Prettier style.                                                                            |
| `npm run build`                            |    0 | Passed; Vite transformed 3,362 modules and built the production bundle. The optional `fontaine` fallback notice remains non-blocking. |

### Original Critical Findings

| Finding from `docs/current-state.md`                                                        | Status             | Current evidence and next work                                                                                                                                                                                                                                                                                                                                                      |
| ------------------------------------------------------------------------------------------- | ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Bulk task updates/deletes accept global IDs and execute unscoped `Todo::whereIn(...)`.      | Open               | `BulkActionRequest`, `BulkUpdateTodos`, and `BulkDeleteTodos` still accept/query global IDs. Scope the exact submitted set through the authorized workspace and reject mixed/foreign IDs atomically with regression tests.                                                                                                                                                          |
| Task relationships and label/tag attachment accept globally existing foreign IDs.           | Open               | Todo requests still use global `exists` rules for projects, assignees, parents, labels, and tags; attach actions still sync global label/tag IDs. Add workspace-scoped validation and cross-workspace rollback tests.                                                                                                                                                               |
| Checklist, label, tag, reminder, and attachment writes lack complete policy authorization.  | Partially resolved | Attachment upload/download/delete now uses an authorized request plus `AttachmentPolicy`; checklist, label, tag, and reminder-create mutations still lack complete policy/scoped-child authorization. Finish policies and attacker/victim API/web tests for every child type.                                                                                                       |
| Backup endpoints lack owner policy/path safety and copy only the main WAL-mode SQLite file. | Open               | `BackupController` still exposes physical paths and accepts route filenames; `BackupService` still uses direct `File::copy()` for the main database. Redesign backup/restore around owner authorization, canonical identifiers, SQLite-safe snapshots, validation, and isolated restore tests.                                                                                      |
| Invitations create users with the known password `password` and no acceptance token.        | Open               | `InviteToWorkspace` still calls `firstOrCreate()` with `bcrypt('password')`; no invitation model/token acceptance flow exists. Replace account creation with expiring single-use invitations and acceptance tests.                                                                                                                                                                  |
| Live SQLite domain referential integrity is not enforced.                                   | Partially resolved | The applied corrective migration now enforces foreign keys across the core workspace domain, and the live database passes `PRAGMA foreign_key_check`; todo parent links and UUID-backed passkey/notification/Sanctum morph references remain incomplete, and domain `CHECK` constraints are still absent. Finish those schema contracts with populated-upgrade and integrity tests. |

### Original High Findings

| Finding from `docs/current-state.md`                                                         | Status             | Current evidence and next work                                                                                                                                                                                                                                                                                         |
| -------------------------------------------------------------------------------------------- | ------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Workspace member removal uses an undefined variable and can remove final ownership.          | Resolved           | The controller now uses the bound `userId`, rejects owner/self removal, verifies membership, and focused settings-member tests cover normal removal and owner protection.                                                                                                                                              |
| Workspace switching stores a session ID that `User::currentWorkspace()` ignores.             | Resolved           | `currentWorkspace()` now accepts the selected ID, verifies it through the user's membership relation, and callers pass `current_workspace_id`; navigation/page tests cover selected-workspace behavior.                                                                                                                |
| API tokens have unrestricted abilities and API login lacks an explicit limiter.              | Open               | Auth still creates tokens without abilities and the API login/register routes have no explicit throttle. Define abilities, enforce them per route/action, and add login-rate and token-scope tests.                                                                                                                    |
| Nested project/task-child route binding is not scoped to the parent workspace/task.          | Open               | Routes still bind many projects, todos, checklists, labels, tags, reminders, and attachments globally without scoped bindings or equivalent parent checks. Add scoped bindings plus mismatched-parent tests.                                                                                                           |
| Web/API controllers duplicate behavior, mix Inertia/JSON, and return inconsistent envelopes. | Open               | Separate web/API controllers still duplicate domain paths; `TodoController` still branches on `expectsJson()` and endpoint envelopes differ. Consolidate through shared actions/query objects and one versioned JSON contract.                                                                                         |
| Route closures resolve workspaces, query todos, manufacture props, and invoke controllers.   | Open               | The main web routes still call controllers through `app(...)` from closures and perform workspace/query/presentation work. Move them to named controller/query-object endpoints.                                                                                                                                       |
| Model progress accessors and dashboard/project/recurrence paths have excessive-query risks.  | Open               | `Todo::progress` and `Checklist::progress` still execute queries; dashboard weekly metrics issue per-day queries and no query-budget tests exist. Replace accessor queries with loaded aggregates/query objects and add deterministic query-count coverage.                                                            |
| Import/export/upload workflows lack boundary, content, streaming, and rollback controls.     | Resolved           | Imports now enforce size/type/record/structure boundaries and transactions; attachments enforce authorized MIME/extension/size pairs; exports stream workspace-scoped lazy queries and neutralize CSV formulas. `DataTransferTest` covers these controls.                                                              |
| Board mode is selectable but not rendered, with mixed/inaccessible drag behavior.            | Partially resolved | The unavailable board selector and unused `@dnd-kit` imports were removed, leaving the supported task list honest. The dormant `BoardView` still relies on native pointer drag and has no keyboard-accessible implementation; either remove it or complete the accessible board phase.                                 |
| Frontend copy/locale/routes and task-detail draft state are inconsistent.                    | Resolved           | Application copy is supplied through English/Lithuanian/Russian catalogs, date/number helpers use user locale/timezone with English fallback, generated Wayfinder routes replace the custom helper/hardcoded settings paths, and task identity changes reset detail/comment/checklist drafts with regression coverage. |

### Next Phase Starting Point

- Security first: close the two cross-workspace task ID findings, complete child policies/scoped binding, and add attacker/victim atomicity tests.
- Then address invitation and API token security before exposing those flows beyond trusted local use.
- Treat backup/restore and SQLite referential integrity as schema/storage phases requiring isolated databases and populated-safe migration tests.
- Keep the now-green Pint, Pest, Larastan, Vue type, ESLint, Prettier, and production-build gates green while security/schema phases proceed.
- Decide whether the board is being removed permanently or implemented as an accessible keyboard-capable feature before re-exposing a board preference.

### Session Git Delivery

The ESLint and formatting work attributable to this conversation is present on `origin/main` in this order:

1. `613c80d` — `fix: remove dead frontend bindings`
2. `bdac5ae` — `fix: normalize task UI control flow`
3. `4ed0952` — `fix: clean board and keyboard guards`
4. `13ec033` — `docs: record ESLint cleanup delivery`
5. `be44072` — `style: format keyboard composables`
6. `de61ce9` — `docs: record frontend formatting cleanup`
7. `b700964` — `docs: record verification and audit handoff`

This repeat verification is committed and pushed separately. Commits from interleaved workspace, localization, data-transfer, design, and static-analysis phases are current-state evidence but are not claimed as part of the ESLint/formatting commit chain.

## UI Phase: Projects-Style Settings Shell

### Status

Completed.

### Scope And Decisions

- Audit every reachable Inertia page against `/projects` at desktop, mobile, and dark-mode breakpoints.
- Promote the shared Warm Precision project header into the persistent settings layout so every settings route uses the same page hierarchy, orange rail, decorative geometry, spacing, and responsive behavior.
- Remove duplicate local settings headings while preserving page actions, workspace metrics, accessibility, translations, and existing form behavior.

### Changed Files

- `resources/css/app.css`
- `resources/js/components/shared/WorkspacePageHeader.vue`
- `resources/js/layouts/settings/Layout.vue`
- `resources/js/pages/calendar/Index.vue`
- `resources/js/pages/settings/Backup.vue`
- `resources/js/pages/settings/Export.vue`
- `resources/js/pages/settings/Members.vue`
- `resources/js/pages/settings/Notifications.vue`
- `resources/js/pages/settings/Preferences.vue`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/settings/Security.vue`
- `resources/js/types/navigation.ts`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php`: passed, 17 tests and 56 assertions.
- `php artisan test --compact`: passed, 207 tests and 1,216 assertions.
- `npm run lint:check`: passed with zero ESLint errors or warnings.
- `npm run format:check`: passed for all files under `resources/`.
- `npm run build`: passed; Vite transformed 3,358 modules and generated the production bundle. The existing optional `fontaine` notice remains non-blocking.
- Browser verification passed for all 13 reachable authenticated list/settings routes at a 390 px viewport, with zero horizontal overflow and no console or page errors. Desktop settings headers match the `/projects` header width and structure; dark mode and the guest login, registration, and password-reset-entry routes were also verified.
- `npm run types:check`: remains blocked only by the unrelated pre-existing `resources/js/composables/useAutosave.ts` import of a non-exported `debounce` member from `@vueuse/core`.
- `vendor/bin/phpstan analyse --no-progress`: retains the pre-existing application-wide backlog of 327 errors; this frontend-only phase added no PHP source changes.

### Known Limitations

- The existing Vue type-check and Larastan baselines listed above remain outside this visual phase.
- The security settings route requires an independent recent password-confirmation session; its shared shell is covered by the same layout implementation, source test, production build, and auth-shell browser verification.
- The optional `fontaine` build notice was not changed because it requires a dependency or build-configuration decision outside this phase.

### Git Delivery

- Commit `8c0db82` (`feat: align settings with projects design`) was pushed successfully to `origin main`.
- This final delivery record will be committed as `docs: record projects-style settings delivery` and pushed separately.

## UI Phase: Complete Projects-Style Visual Unification

### Status

Completed.

### Scope And Decisions

- Treat `/projects` as the single Warm Precision reference for every reachable page, dialog, drawer, confirmation, form control, empty state, and authentication surface.
- Introduce shared projects-style dialog and state primitives before migrating feature screens, with viewport-safe mobile scrolling and token-based light/dark styling.
- Replace browser-native confirmations and checkboxes with accessible application components while preserving existing routes, permissions, validation, and Inertia behavior.
- Align task details, settings interiors, list states, and guest authentication with the same orange rail, geometric accent, radius, hierarchy, and spacing used by `/projects`.
- Verify focused frontend contracts, the full test suite, static analysis, formatting, production build, and representative browser routes at desktop/mobile in light/dark mode.

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Changed Files

- Shared projects-style primitives: `WorkspaceDialogContent.vue`, `WorkspaceConfirmDialog.vue`, `EmptyState.vue`, and the localized Sheet close label.
- Dialogs and destructive flows: project/task/workspace creation, task deletion, account deletion, member removal, passkey removal, two-factor setup/disable, and backup restore.
- Main experiences: dashboard tokens, task/project lists, task details, workspaces, activity, notifications, and reusable task controls.
- Settings and guest surfaces: backup, export/import, members, notifications, profile, security, and `AuthSimpleLayout.vue`.
- English, Lithuanian, and Russian UI catalogs plus `FrontendDesignTest.php`.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php`: passed, 39 tests and 107 assertions.
- `php artisan test --compact`: passed on the isolated rerun, 229 tests and 1,267 assertions. An earlier parallel run raced the production build while its Vite manifest was being replaced; the clean post-build rerun passed.
- `npm run lint:check`: passed with zero ESLint errors or warnings.
- `npm run format:check`: passed for all files under `resources/`.
- `npm run build`: passed; Vite transformed 3,362 modules and generated the production bundle. The existing optional `fontaine` notice remains non-blocking.
- `npm run types:check`: the phase adds no new errors; the repository remains blocked only by the pre-existing `resources/js/composables/useAutosave.ts` import of a non-exported `debounce` member from `@vueuse/core`.
- `vendor/bin/phpstan analyse --no-progress`: retains the pre-existing application-wide backlog of 327 errors; this visual phase did not add application PHP logic.
- Browser route matrix: 13 authenticated routes passed at 1440x1000 and 390x844 in both light and dark modes (52 combinations), all with HTTP 200, no horizontal overflow, and no captured console/page errors. The protected security route correctly rendered the projects-style password confirmation screen.
- Interaction QA: project, workspace, and task creation dialogs all render at 28 px radius with the shared left rail; the mobile dark task dialog fits at 347x790 inside a 390x844 viewport without document overflow. Task detail, destructive confirmation, settings export/import, and guest registration were visually inspected.

### Known Limitations

- The existing Vue type-check and Larastan baselines listed above remain outside this visual phase.
- Laravel Boost's buffered browser log still contains historical errors from older asset hashes and unrelated route warnings; fresh isolated navigations did not reproduce page or console errors.
- The optional `fontaine` build notice was not changed because it requires a dependency or build-configuration decision outside this phase.

### Git Delivery

- Commit `8ea6e40` (`feat: unify pages with projects design`) was pushed successfully to `origin main`.
- This phase record will be committed and pushed separately while preserving unrelated pre-existing progress changes.

## UI Phase: Frontend Baseline And Dormant Surface Closure

### Status

Completed.

### Scope And Decisions

- Remove the remaining Vue type-check failure in the autosave composable with lifecycle-safe native Vue watcher cleanup.
- Make dormant authentication and header layouts delegate to the canonical projects-style shells so future route changes cannot reintroduce the old visual language.
- Extend the shared Warm Precision state surface with accessible loading and error variants while preserving every existing empty-state call site.
- Re-run focused contracts, the full frontend verification stack, production build, and representative browser coverage before delivery.

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Changed Files

- `resources/js/composables/useAutosave.ts`
- `resources/js/layouts/auth/AuthCardLayout.vue`
- `resources/js/layouts/auth/AuthSplitLayout.vue`
- `resources/js/layouts/app/AppHeaderLayout.vue`
- `resources/js/components/shared/EmptyState.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php`: passed, 42 tests and 124 assertions.
- `php artisan test --compact`: passed, 232 tests and 1,284 assertions.
- `npm run test:frontend`: passed, 1 test.
- `npm run types:check`: passed with zero Vue or TypeScript errors; the previous autosave baseline is closed.
- `npm run lint:check`: passed with zero ESLint errors or warnings.
- `npm run format:check`: passed for all files under `resources/`.
- `npm run build`: passed; Vite transformed 3,362 modules and generated the production bundle. The existing optional `fontaine` notice remains non-blocking.
- `vendor/bin/phpstan analyse --no-progress`: retains the pre-existing application-wide backlog of 327 errors; this phase changed no application PHP logic.
- Browser verification passed for `/projects`, `/tasks`, `/workspaces`, `/settings/preferences`, and `/settings/backup` at 1440x1000 light mode and 390x844 dark mode: 10/10 HTTP 200 responses, zero horizontal overflow, and no fresh console or page errors. The mobile dark `/projects` reference was also visually inspected after the production build.

### Known Limitations

- The application-wide Larastan backlog is not part of this frontend closure phase.
- The optional `fontaine` build notice remains unchanged because resolving it requires a dependency or build-configuration decision outside this phase.

### Git Delivery

- Commit `3b09177` (`fix: close frontend visual baseline`) was pushed successfully to `origin main`.
- This final phase record will be committed separately while preserving unrelated pre-existing progress changes.

## Static Analysis Phase: Typed Eloquent Foundation

### Status

Completed.

### Scope And Decisions

- Treat the 327-error Larastan report as a set of shared typing defects rather than suppressing individual findings.
- Start with model relationship generics, factory generics, typed local scopes, cast-aware properties, and the incorrect morph relation return type because these defects propagate into actions, policies, resources, factories, and seeders.
- Preserve runtime queries and domain behavior; add PHPDoc only where native PHP cannot express Laravel's generic relationship and factory types.
- Re-run focused model tests and the complete Larastan report to measure the reduction before selecting the next batch.

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Changed Files

- Typed models: `ActivityLog`, `Checklist`, `ChecklistItem`, `Comment`, `Label`, `Project`, `Reminder`, `Tag`, `Todo`, `User`, `UserPreference`, `Workspace`, and `WorkspaceMember`.
- `tests/Feature/ModelRelationsTest.php`
- `docs/progress.md`

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/ModelRelationsTest.php`: passed, 47 tests and 47 assertions across every declared relation plus membership-role behavior.
- `php artisan test --compact`: passed, 279 tests and 1,331 assertions.
- `vendor/bin/phpstan analyse --no-progress -v`: reduced the baseline from 327 errors across 104 files to 196 errors across 75 files. All findings under `app/Models` are resolved.
- `git diff --check`: passed.
- No frontend files changed, so Vue type checking, ESLint, Prettier, and the Vite build were not repeated in this backend-only batch.

### Known Limitations

- 196 Larastan findings remain in downstream resources, actions, services, controllers, policies, factories, seeders, migrations, configuration, and routes for later batches.

### Git Delivery

- Commit `a1a076c` (`fix: type eloquent model contracts`) was pushed successfully to `origin main`.
- This final phase record will be committed separately while preserving unrelated pre-existing progress changes.

## Static Analysis Phase: Typed API Resources

### Status

Completed.

### Scope And Decisions

- Bind every JSON resource to its concrete Eloquent model with `@mixin` and declare generic `array<string, mixed>` payloads without changing response keys or relation-loading behavior.
- Preserve the nullable legacy `avatar` payload through explicit attribute access instead of inventing a new API contract.
- Correct the Todo date and Reminder enum cast annotations exposed by resource analysis.
- Measure the downstream effect through the complete Larastan report rather than adding suppressions or a baseline file.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `app/Http/Resources/ActivityLogResource.php`
- `app/Http/Resources/ChecklistItemResource.php`
- `app/Http/Resources/ChecklistResource.php`
- `app/Http/Resources/CommentResource.php`
- `app/Http/Resources/LabelResource.php`
- `app/Http/Resources/ProjectResource.php`
- `app/Http/Resources/ReminderResource.php`
- `app/Http/Resources/TagResource.php`
- `app/Http/Resources/TodoResource.php`
- `app/Http/Resources/UserResource.php`
- `app/Http/Resources/WorkspaceResource.php`
- `app/Models/Reminder.php`
- `app/Models/Todo.php`
- `tests/Feature/ApiResourceTypingTest.php`
- `docs/progress.md`

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/ApiResourceTypingTest.php tests/Feature/Api`: passed, 45 tests and 119 assertions.
- `php artisan test --compact`: passed, 292 tests and 1,369 assertions.
- `vendor/bin/phpstan analyse --no-progress -v`: reduced the baseline from 196 errors across 75 files to 97 errors across 64 files. All findings under `app/Http/Resources` are resolved.
- `git diff --check`: passed.
- No frontend files changed, so Vue type checking, ESLint, Prettier, and the Vite build were not repeated in this backend-only batch.

### Known Limitations

- 97 Larastan findings remain outside the model and API resource layers.

### Git Delivery

- Commit `3b00e94` (`fix: type API resource contracts`) was pushed successfully to `origin main`.
- This final phase record will be committed separately while preserving unrelated pre-existing progress changes.

## Static Analysis Phase: Typed Application Layer

### Status

Completed.

### Scope And Decisions

- Add concrete validated-data shapes to actions and services instead of weakening their array parameters to uninformative iterables.
- Type Todo query pipelines with `Builder<Todo>`, normalize sort direction to Laravel's supported literal values, and preserve filter behavior.
- Correct real defects exposed by Larastan: the missing `ReminderPolicy` import, the backup file timestamp call, enum assignments, the recurring-task Carbon type, the backup download response type, and the non-exhaustive bulk match.
- Add standard generic return contracts to Form Requests, translations, and notifications while leaving validation rules and payload keys unchanged.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- Typed application contracts across actions, Form Requests, controllers, middleware, notifications, providers, and services.
- `app/Console/Commands/ProcessRecurringTasks.php`
- `app/Concerns/ProfileValidationRules.php`
- `tests/Feature/ApplicationLayerContractTest.php`
- `docs/progress.md`

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/ApplicationLayerContractTest.php tests/Feature/Api/ApiProjectTest.php tests/Feature/Api/ApiTodoTest.php tests/Feature/ProjectTest.php tests/Feature/TodoTest.php tests/Feature/WorkspacePagesTest.php tests/Feature/WorkspaceTest.php`: passed, 44 tests and 228 assertions.
- `php artisan test --compact`: passed, 296 tests and 1,381 assertions.
- `vendor/bin/phpstan analyse --no-progress --error-format=json`: reduced the baseline from 97 errors across 64 files to 43 errors across 22 files. All findings under `app/` are resolved.
- `git diff --check`: passed.
- No frontend files changed, so Vue type checking, ESLint, Prettier, and the Vite build were not repeated in this backend-only batch.

### Known Limitations

- Factory, seeder, migration, configuration, and route findings remain for the final static-analysis batch.

### Git Delivery

- Commit `eab814b` (`fix: type application layer contracts`) was pushed successfully to `origin main`.
- This final phase record will be committed separately while preserving unrelated pre-existing progress changes.

## Static Analysis Phase: Supporting Infrastructure

### Status

Completed.

### Scope And Decisions

- Complete the Larastan cleanup in configuration, factories, migrations, seeders, and routes without adding suppressions or a baseline.
- Bind every Eloquent factory to its concrete model and type iterable factory state explicitly.
- Separate UUID column creation from a dedicated foreign-key migration so clean installations and the existing SQLite database converge on the same 24 enforced relationships.
- Finish with the complete backend and frontend verification matrix because this is the final repository-wide static-analysis phase.

### Migrations And Packages

- Added `2026_07_19_185503_add_foreign_keys_to_workspace_tables.php` to apply and reverse the 24 constraints that the previous `uuid()->constrained()` chains did not create.
- No Composer or npm package change was made.

### Changed Files

- `app/Models/ActivityLog.php`, `Attachment.php`, `UserPreference.php`, and `WorkspaceMember.php`
- Typed factories under `database/factories/`
- `config/sanctum.php`
- Workspace-domain migrations `2026_07_18_000001` through `2026_07_18_000015`
- `database/migrations/2026_07_19_185503_add_foreign_keys_to_workspace_tables.php`
- `database/seeders/TodoSeeder.php`
- `tests/Feature/SupportingInfrastructureContractTest.php`
- `docs/progress.md`

### Verification

- Pre-migration live orphan audit: passed with zero orphaned identifiers across all 24 intended relationships.
- Temporary SQLite `migrate` -> `migrate:rollback --step=1` -> `migrate`: passed, including the new reversible foreign-key migration.
- Created local backup `storage/app/backups/backup_2026-07-19_185715.sqlite`, then applied the migration successfully to the live SQLite database.
- Live schema verification: 24 foreign keys present and `pragma_foreign_key_check` returned no violations.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/SupportingInfrastructureContractTest.php`: passed, 32 tests and 42 assertions.
- `php artisan test --compact`: passed, 328 tests and 1,423 assertions.
- `vendor/bin/phpstan analyse --no-progress --error-format=json`: passed with zero errors. The complete cleanup reduced the original baseline from 327 errors across 104 files to zero without suppressions or a baseline file.
- `npm run types:check`: passed.
- `npm run lint:check`: passed.
- `npm run format:check`: passed.
- `npm run build`: passed after transforming 3,362 modules.
- `git diff --check`: passed.

### Known Limitations

- Vite still reports the existing optional `fontaine` optimization notice; the production build succeeds and no dependency was added without approval.

### Git Delivery

- Commit `d2c6af7` (`fix: complete static analysis contracts`) was pushed successfully to `origin main`.
- This final phase record will be committed separately while preserving unrelated pre-existing progress changes.

## UI Follow-up Phase: Interaction Surface Consistency

### Status

Completed.

### Scope And Decisions

- Re-audit every reachable Inertia page at desktop and mobile widths against the `/projects` visual contract.
- Normalize the remaining Activity and appearance segmented controls to the shared muted/card treatment.
- Preserve whole-row task selection while adding a real keyboard-focusable control around nested checkbox and delete actions.
- Keep all existing routes, data contracts, behavior, translations, and dark/light theme support unchanged.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `resources/js/pages/activity/Index.vue`
    - aligned the filter rail with the shared muted/card segmented-control treatment.
- `resources/js/components/AppearanceTabs.vue`
    - replaced legacy neutral/white states with shared semantic surface tokens and orange focus states.
- `resources/js/pages/tasks/Index.vue`
- `resources/js/pages/projects/Show.vue`
    - preserved whole-row task selection through a keyboard-focusable overlay button while keeping nested checkbox and delete controls independently interactive.
- `tests/Feature/FrontendDesignTest.php`
    - added structural coverage for task-row keyboard interaction and shared segmented-control styling.
- `docs/progress.md`
    - recorded the audit, implementation, verification, limitations, and delivery state.

### Verification

- Browser route audit covered 14 authenticated destinations at desktop and mobile widths; every audited page had zero horizontal overflow and no new console or page errors.
- `/settings/security` correctly redirected to the password-confirmation screen, which retained the shared authentication shell.
- Keyboard QA confirmed task-row focus, Enter navigation to task detail, and independent delete confirmation behavior.
- Dark-mode screenshots of Activity and Preferences confirmed the same muted/card/orange visual language as `/projects`.
- `vendor/bin/pint --dirty --format agent` — passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php` — 45 tests passed, 141 assertions.
- `npm run types:check` — passed.
- `npm run lint:check` — passed.
- `npm run format:check` — passed.
- `php artisan test --compact` — 331 tests passed, 1440 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress` — passed with 0 errors.
- `npm run build` — passed.
- `git diff --check` — passed.

### Known Limitations

No functional limitation remains in the audited interaction surfaces. Laravel's persisted browser log still contains historical entries from older assets; the current desktop/mobile route audit produced no new browser errors.

### Git Delivery

- Implementation commit: `abf8312` (`fix: align interactive workspace surfaces`).
- Implementation push: successful to `origin/main`.
- Documentation commit and push: pending this record update.

## Android 12 Native Runtime Compatibility

### Status

Completed.

### Scope And Decisions

- Reproduce the Android 12 support mismatch through the NativePHP build configuration before changing application behavior.
- Keep compile and target SDK 36 while lowering only the minimum supported Android SDK to API 31.
- Preserve the existing settings UI and routing because the audited settings code contains no Android-specific native call.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `config/nativephp.php`
- `.env.example`
- `tests/Feature/NativePhpMobileTest.php`
- `docs/progress.md`

The ignored local `.env` minimum SDK override was also aligned to API 31 so local Android builds use the repaired contract.

### Verification

- TDD red phase: the Android 12 compatibility test failed as expected because the configured minimum SDK was 33 instead of 31.
- `php artisan test --compact tests/Feature/NativePhpMobileTest.php`: passed, 19 tests and 308 assertions.
- `npm run build:android`: passed after transforming 3,362 modules.
- NativePHP 3.3.6 debug APK assembly: passed; Gradle completed all 40 tasks successfully.
- Generated APK manifest inspection: confirmed `sdkVersion: 31`, `targetSdkVersion: 36`, and application ID `com.goleaf.xiaomimimo`.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact`: passed, 332 tests and 1,442 assertions.
- `vendor/bin/phpstan analyse --no-progress`: passed with zero errors.
- `npm run test:frontend`: passed, 1 test.
- `npm run types:check`: passed.
- `npm run lint:check`: passed.
- `npm run format:check`: passed.
- `npm run build`: passed after transforming 3,362 modules.
- `git diff --check`: passed.

### Known Limitations

An Android 12 device or API 31 emulator is not currently available in the configured local Android environment. The debug APK was compiled and its manifest was verified directly; installation was intentionally skipped by using a non-device ADB serial.

### Git Delivery

This phase is committed as `fix: support Android 12` and pushed to `origin/main` with only Android compatibility files staged; unrelated progress notes remain outside the commit.

## UI Follow-up Phase: Shared Interaction Polish

### Status

Completed.

### Scope And Decisions

- Audit shared controls that appear across authentication, sidebar, settings, and navigation flows after the page-level `/projects` design pass.
- Replace remaining neutral interaction accents with the established orange focus and action language.
- Restore keyboard access and translated accessible names for shared controls without changing their application behavior.
- Keep existing routes, Inertia data contracts, dark/light theme behavior, and component architecture unchanged.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `resources/js/app.ts`
    - changed the Inertia progress indicator from legacy gray to the `/projects` orange accent.
- `resources/js/components/PasswordInput.vue`
    - made the visibility toggle keyboard reachable, localized, state-aware, and visually aligned with the shared input focus treatment.
- `resources/js/components/TextLink.vue`
- `resources/js/components/UserInfo.vue`
    - replaced the remaining neutral link and avatar-fallback accents with the warm orange semantic treatment.
- `resources/js/components/ui/breadcrumb/Breadcrumb.vue`
- `resources/js/components/ui/input-otp/InputOTPSlot.vue`
- `resources/js/components/ui/sidebar/SidebarRail.vue`
- `resources/js/components/ui/spinner/Spinner.vue`
    - aligned shared feedback/focus surfaces and localized their accessible names.
- `resources/js/pages/auth/Login.vue`
- `resources/js/pages/auth/Register.vue`
- `resources/js/pages/auth/TwoFactorChallenge.vue`
    - removed manual positive tab ordering and normalized inline authentication actions.
- `lang/en/ui.php`
- `lang/lt/ui.php`
- `lang/ru/ui.php`
    - added stable interaction and accessibility copy in every supported language.
- `tests/Feature/FrontendDesignTest.php`
    - added shared interaction, focus, progress-accent, and translation coverage.
- `docs/progress.md`
    - recorded this phase and its verification state.

### Verification

- Dark-mode Login screenshots at 390×844 and 1440×1000 confirmed the same orange/black/card language as `/projects`, with zero horizontal overflow.
- Browser keyboard QA confirmed `Password -> Show password -> Confirm password -> Show password` follows natural DOM order across Login and Register.
- The password toggle exposes translated `aria-label` and `aria-pressed`; keyboard focus produced the orange three-pixel ring and Space changed the field type to text.
- A delayed Inertia visit exposed the live progress bar as `rgb(234, 88, 12)`.
- Desktop sidebar QA confirmed the translated rail label/title and orange hover rail; current browser sessions produced no console or page errors.
- `vendor/bin/pint --dirty --format agent` — passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php` — 50 tests passed, 195 assertions.
- `php artisan test --compact` — 340 tests passed, 1519 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress` — passed with 0 errors.
- `npm run test:frontend` — passed, 1 test.
- `npm run types:check` — passed.
- `npm run lint:check` — passed.
- `npm run format:check` — passed.
- `npm run build` — passed.
- `git diff --check` — passed.

### Known Limitations

The Fortify two-factor challenge redirects ordinary guest sessions to Login unless a real pending two-factor authentication session exists. OTP styling and reduced-motion behavior are covered by the focused source contract, TypeScript, lint, and production build, but the protected challenge state was not artificially forged for browser QA.

### Git Delivery

- Implementation commit: `2de801c` (`fix: polish shared interaction surfaces`).
- Implementation push: successful to `origin/main`.
- Documentation commit and push: pending this record update.

## Task Detail Label Editing

### Status

Completed.

### Scope And Decisions

- Reproduced the reported task-detail issue: assigned labels were visible, but the edit form offered no way to change them.
- Reused the existing task update route and action instead of adding a parallel label-assignment endpoint.
- Load selectable labels only from the task's workspace and validate every submitted label against that same workspace before synchronizing the pivot table.
- Keep the label editor accessible with a fieldset, named checkboxes, processing states, validation feedback, and localized English, Lithuanian, and Russian copy.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `app/Http/Controllers/TodoController.php`
- `app/Http/Requests/UpdateTodoRequest.php`
- `resources/js/pages/tasks/Show.vue`
- `lang/en/tasks.php`
- `lang/lt/tasks.php`
- `lang/ru/tasks.php`
- `tests/Feature/TodoTest.php`
- `docs/progress.md`

### Verification

- TDD red phase confirmed the detail page did not expose workspace labels and the update request accepted a foreign-workspace label.
- `php artisan test --compact tests/Feature/TodoTest.php` — passed, 16 tests and 64 assertions.
- Browser QA on `/tasks/019f7a48-e93e-700c-a984-26341f7ddf06` confirmed all six workspace labels are selectable, Bug and Security are initially checked, and saving Bug plus Feature returned HTTP 303 and updated the detail metadata. The original Bug plus Security assignment was then restored and verified.
- `vendor/bin/pint --dirty --format agent` — passed.
- `php artisan test --compact` — passed, 340 tests and 1,519 assertions.
- `vendor/bin/phpstan analyse --no-progress` — passed with 0 errors.
- `npm run test:frontend` — passed, 1 test.
- `npm run types:check` — passed.
- `npm run lint:check` — passed.
- `npm run format:check` — passed.
- `npm run build` — passed after transforming 3,362 modules.
- `git diff --check` — passed.

### Known Limitations

- This task-detail feature assigns and removes existing workspace labels; label creation and renaming remain in the workspace label-management flow.
- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.

### Git Delivery

- Implementation commit: `4012e46` (`feat: edit task labels`).
- Implementation push: successful to `origin/main`.
- This delivery record is committed and pushed separately so the feature commit remains focused.

## Transient Surface Design Unification

### Status

Completed.

### Scope And Decisions

- Extend the `/projects` Warm Precision visual language to shared dropdown, select, tooltip, dialog, sheet, toast, and mobile-sidebar surfaces.
- Standardize semantic borders, generous radii, warm shadows, orange keyboard focus, touch-friendly menu rows, and reduced-motion behavior at the shared primitive level so every consuming page inherits the same treatment.
- Replace remaining hardcoded accessibility copy in shared transient surfaces with stable English, Lithuanian, and Russian translations.
- Preserve the existing page architecture and reuse the installed Reka UI and Vue Sonner primitives without adding packages.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `resources/js/components/ui/dropdown-menu/DropdownMenuContent.vue`
- `resources/js/components/ui/dropdown-menu/DropdownMenuItem.vue`
- `resources/js/components/ui/select/SelectContent.vue`
- `resources/js/components/ui/select/SelectItem.vue`
- `resources/js/components/ui/tooltip/TooltipContent.vue`
- `resources/js/components/ui/dialog/DialogContent.vue`
- `resources/js/components/ui/dialog/DialogOverlay.vue`
- `resources/js/components/ui/dialog/DialogScrollContent.vue`
- `resources/js/components/ui/sheet/SheetContent.vue`
- `resources/js/components/ui/sheet/SheetOverlay.vue`
- `resources/js/components/ui/sonner/Sonner.vue`
- `resources/js/components/ui/sidebar/Sidebar.vue`
- `resources/js/components/ui/sidebar/SidebarTrigger.vue`
- `resources/js/components/ui/breadcrumb/BreadcrumbEllipsis.vue`
- `lang/en/ui.php`
- `lang/lt/ui.php`
- `lang/ru/ui.php`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Verification

- Light desktop browser QA on `/projects` confirmed the workspace dropdown uses the Warm Precision popover, orange keyboard highlight, pointer rows, and zero horizontal overflow.
- Light and dark browser QA on `/settings/preferences` confirmed the shared select uses the same rounded semantic surface and orange keyboard highlight; Vue Sonner resolves its theme to the active dark appearance.
- Collapsed-sidebar QA confirmed the shared tooltip uses the refined rounded inverse surface and warm border/shadow treatment.
- Mobile QA at 390×844 confirmed the translated sidebar sheet is 288px wide, uses the softened 65% overlay with 2px backdrop blur, and produces zero horizontal overflow.
- Reduced-motion browser emulation confirmed the dropdown reports `animation-name: none` and a zero-second animation duration.
- A clean browser reload and dropdown interaction on `/projects` produced no console errors or page errors.
- `vendor/bin/pint --dirty --format agent` — passed.
- `php artisan test --compact tests/Feature/FrontendDesignTest.php` — 53 tests passed, 259 assertions.
- `php artisan test --compact` — 343 tests passed, 1,583 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress` — passed with 0 errors.
- `npm run test:frontend` — passed, 1 test.
- `npm run types:check` — passed.
- `npm run lint:check` — passed.
- `npm run format:check` — passed.
- `npm run build` — passed after transforming 3,362 modules.
- `git diff --check` — passed.

### Known Limitations

Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.

### Git Delivery

- Implementation commit: `1cf3dad` (`fix: unify transient interface surfaces`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `9fb1573` (`docs: record transient surface unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Shared Control And Motion Polish

### Status

Completed.

### Scope And Decisions

- Complete the `/projects` Warm Precision contract for shared checkbox, alert, skeleton, spinner, link-focus, and compact status surfaces.
- Keep semantic invalid and disabled states while making checked, focus-visible, loading, and reduced-motion behavior consistent across authentication, tasks, workspace switching, and settings.
- Reuse the current shared primitives and semantic translations; do not introduce packages, a parallel component system, or page-specific theme variants.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `resources/js/components/ui/checkbox/Checkbox.vue`
- `resources/js/components/ui/alert/AlertTitle.vue`
- `resources/js/components/ui/alert/index.ts`
- `resources/js/components/ui/badge/index.ts`
- `resources/js/components/ui/button/index.ts`
- `resources/js/components/ui/skeleton/Skeleton.vue`
- `resources/js/components/ui/spinner/Spinner.vue`
- `resources/js/components/TextLink.vue`
- `resources/js/components/PasskeyItem.vue`
- `resources/js/components/TwoFactorRecoveryCodes.vue`
- `resources/js/components/TwoFactorSetupModal.vue`
- `resources/js/components/AppearanceTabs.vue`
- `resources/js/components/shared/EmptyState.vue`
- `resources/js/components/workspace/WorkspaceSwitcher.vue`
- `resources/js/layouts/settings/Layout.vue`
- `resources/js/pages/activity/Index.vue`
- `resources/js/pages/auth/TwoFactorChallenge.vue`
- `resources/js/pages/calendar/Index.vue`
- `resources/js/pages/notifications/Index.vue`
- `resources/js/pages/projects/Index.vue`
- `resources/js/pages/settings/Members.vue`
- `resources/js/pages/tasks/Show.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Verification

- Focused design-contract coverage passed with 70 tests and 291 assertions.
- Full Pest suite passed with 360 tests and 1,615 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,362 modules.
- Light and dark browser QA on `/settings/notifications` confirmed the shared checked state, semantic card surfaces, and zero horizontal overflow.
- Mobile browser QA at 390×844 confirmed the settings controls retain their hierarchy and produce zero horizontal overflow.
- Reduced-motion browser emulation confirmed transition properties and spinner animation resolve to `none`.
- The browser run produced no console errors or page errors.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- New pages should continue to compose these shared primitives so checked, focus, feedback, loading, and reduced-motion behavior stays aligned with `/projects`.

### Git Delivery

- Implementation commit: `1741221` (`fix: polish shared control states`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `43b26ca` (`docs: record shared control polish`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Unified Form Feedback And Progress

### Status

Completed.

### Scope And Decisions

- Extend the `/projects` Warm Precision language to validation errors, success and warning notices, and file-upload progress across authentication, task editing, and settings.
- Reuse the shared alert and field-error components so semantic colors, radii, spacing, dark mode, and assistive-technology announcements remain consistent.
- Replace duplicate page-level error markup and the browser-dependent native upload progress appearance without changing form behavior or adding packages.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `resources/js/components/InputError.vue`
- `resources/js/components/DeleteUser.vue`
- `resources/js/components/ui/alert/Alert.vue`
- `resources/js/components/ui/alert/index.ts`
- `resources/js/pages/auth/Login.vue`
- `resources/js/pages/auth/ForgotPassword.vue`
- `resources/js/pages/auth/VerifyEmail.vue`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/settings/Security.vue`
- `resources/js/pages/settings/Members.vue`
- `resources/js/pages/tasks/Show.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Verification

- Focused frontend-design and security coverage passed with 84 tests and 373 assertions.
- Full Pest suite passed with 369 tests and 1,656 assertions.
- A red-first regression test reproduced the Security page's stale `user.two_factor_enabled` binding; the page now consumes the controller's `canManageTwoFactor` and `twoFactorEnabled` props.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Light and dark browser QA on `/settings/security` confirmed the semantic 2FA status, accessible field-error treatment, 24px cards, 12px feedback surfaces, and zero horizontal overflow.
- Mobile browser QA at 390×844 on `/settings/profile` confirmed the profile, destructive warning, and settings navigation remain visually coherent with zero horizontal overflow.
- A failed password update safely exercised the validation state and confirmed the invalid input, error icon, error copy, and `aria-invalid` state without changing account data.
- The final browser run produced no console errors or page errors.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Future forms should use `InputError` and semantic `Alert` variants instead of page-level red, green, or amber feedback markup.

### Git Delivery

- Implementation commit: `8e3bd05` (`fix: unify form feedback surfaces`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `7a50f21` (`docs: record form feedback unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Create Dialog Form Unification

### Status

- Completed.

### Scope And Decisions

- Align the active project and task creation dialogs with the Warm Precision form language established on `/projects`.
- Reuse the shared button, input, select, error, and loading components instead of repeating local control styles.
- Complete disabled, invalid, loading, keyboard-focus, dark-mode, and reduced-motion states for the affected controls.
- Preserve the existing submission behavior, validation contract, routes, and translations.
- Keep dormant dropdown, sidebar, command-palette, and filter variants unchanged because they are not rendered by current application routes.

### Changed Files

- `resources/js/components/project/ProjectCreateDialog.vue`
- `resources/js/components/task/TaskCreateDialog.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- Focused frontend-design coverage passed with 83 tests and 354 assertions.
- Full Pest coverage passed with 373 tests and 1,678 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Browser QA confirmed the project dialog in desktop light mode and the task dialog in desktop dark mode use the shared 24px/28px Warm Precision surface, 44px actions, semantic selected states, and zero horizontal overflow.
- Mobile browser QA at 390×844 confirmed both dialogs resolve to 358px with 16px viewport gutters, internal vertical scrolling, complete footer actions, and zero page or dialog overflow.
- An empty task-title submission safely confirmed the field error, `aria-invalid="true"`, and semantic alert without persisting data.
- The final browser interactions produced no console errors or page errors; the Boost browser log contained only older entries from earlier resolved or unrelated sessions.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Dormant variants should be reviewed when a route begins rendering them, rather than adding unexercised styling now.

### Git Delivery

- Implementation commit: `605977d` (`fix: unify creation dialog controls`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `e3a8462` (`docs: record creation dialog unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Authentication Action And Validation Unification

### Status

- Completed.

### Scope And Decisions

- Align every active Fortify and passkey action with the 44px shared button rhythm used by the `/projects` creation flows.
- Add consistent processing guards and shared loading indicators to authentication submissions, including both two-factor challenge modes.
- Expose validation state through `aria-invalid` on credential, reset, confirmation, OTP, and recovery-code controls.
- Preserve Fortify routes, request payloads, translations, password rules, passkey behavior, and authentication redirects.
- Use Inertia's `disable-while-processing` contract to prevent duplicate interaction for every Fortify form while retaining the existing explicit disabled button states.
- Keep the two-factor challenge behavior unchanged and verify its conditional screen through source contracts because Fortify only renders it during a pending two-factor login.

### Changed Files

- `resources/js/components/PasskeyVerify.vue`
- `resources/js/pages/auth/*.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- The focused RED run produced 14 expected failures for missing large actions, processing guards, invalid states, and the passkey action contract.
- Focused frontend-design coverage passed after implementation with 97 tests and 384 assertions.
- Full Pest coverage passed with 387 tests and 1,708 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Guest route QA covered login, registration, forgot-password, reset-password, and the guarded two-factor route in desktop light and mobile dark modes with HTTP 200 results, zero horizontal overflow, and no console or page errors.
- Live measurement confirmed 44px email/password inputs, passkey actions, primary authentication actions, and password-confirmation actions.
- A single invalid demo login safely confirmed the semantic alert and `aria-invalid="true"` state without changing account data; the demo session was then restored successfully.
- The Boost browser log contained only historical entries from the previously repaired Security screen; the current auth run produced no new log entries.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Email verification and the two-factor challenge depend on account/session states not created during visual QA; both remain covered by their unchanged Fortify feature tests and the new frontend source contracts.

### Git Delivery

- Implementation commit: `8078d03` (`fix: align authentication action states`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `cf90e7b` (`docs: record authentication action unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Remaining Active Form Action Unification

### Status

- Completed.

### Scope And Decisions

- Align the workspace creation, preference, notification, member invitation, and member removal actions with the 44px shared control rhythm used by `/projects`.
- Reuse the shared spinner, button sizes, field validation states, and processing guards instead of repeating page-local orange or loading styles.
- Disable mutable form controls during submission to prevent conflicting edits and duplicate requests.
- Preserve existing routes, request payloads, translations, permissions, and success behavior.
- Correct the mobile notification option hierarchy found during visual QA so each title and description remains readable instead of collapsing into adjacent text columns.

### Changed Files

- `resources/js/pages/workspaces/Index.vue`
- `resources/js/pages/settings/Preferences.vue`
- `resources/js/pages/settings/Notifications.vue`
- `resources/js/pages/settings/Members.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- The initial focused RED run produced four expected failures for the missing shared loading, disabled, invalid, and 44px action contracts; a separate mobile hierarchy contract also failed before the notification copy repair.
- Focused frontend-design coverage passed after implementation with 101 tests and 410 assertions.
- Full Pest coverage passed with 391 tests and 1,734 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Desktop light browser QA confirmed the workspace dialog and Preferences save action use 44px shared inputs and actions with zero horizontal overflow.
- Mobile dark browser QA confirmed Notifications and Members use 44px save, invite, input, and select controls with zero horizontal overflow.
- Visual inspection caught and verified the repaired vertical title/description hierarchy inside all mobile notification option cards.
- The final browser interactions produced no console errors or page errors; the Boost browser log contained only historical entries dated 2026-07-19.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Dormant form variants should be reviewed when a route begins rendering them, rather than adding unexercised styling now.

### Git Delivery

- Implementation commit: `88248f0` (`fix: align remaining active form actions`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `f6989cd` (`docs: record active form action unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Final Async Action State Unification

### Status

- Completed.

### Scope And Decisions

- Complete the shared `/projects` action-state contract for confirmations, profile and security forms, backup creation, account deletion, and task editing.
- Use the shared 44px button size and Spinner component for primary form and dialog actions.
- Prevent mutable fields from changing while an Inertia form is processing, including `disable-while-processing` for the account deletion Form component.
- Preserve all routes, payloads, permissions, translations, and success behavior.

### Changed Files

- `resources/js/components/shared/WorkspaceConfirmDialog.vue`
- `resources/js/components/DeleteUser.vue`
- `resources/js/pages/settings/Backup.vue`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/settings/Security.vue`
- `resources/js/pages/tasks/Show.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- The focused RED run produced four expected failures for missing shared confirmation loading, inert account deletion, incomplete settings processing states, and the local task-edit loader.
- Focused frontend-design coverage passed after implementation with 104 tests and 434 assertions.
- Full Pest coverage passed with 394 tests and 1,758 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Mobile dark browser QA confirmed the Profile delete dialog and Task edit form use 44px controls, readable responsive surfaces, and zero horizontal overflow.
- Desktop light browser QA confirmed the Security password and two-factor actions, Backup create action, and shared restore confirmation use the same 44px action rhythm with zero horizontal overflow.
- Live measurement confirmed 44px profile, security, backup, confirmation, and task-edit actions plus 44px task inputs/selects and account-confirmation inputs.
- The security route's existing password-confirmation guard was completed with the demo password solely to inspect the page; no settings or data were changed.
- The final browser interactions produced no console errors or page errors; the Boost browser log contained only historical entries dated 2026-07-19.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Compact toolbar and icon actions intentionally retain their smaller semantic sizes; primary form and dialog actions now share the `/projects` 44px contract.

### Git Delivery

- Implementation commit: `aedd8bf` (`fix: unify final asynchronous action states`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `7982a52` (`docs: record final action state unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Shared Empty And Secondary Action Unification

### Status

- Completed.

### Scope And Decisions

- Remove the remaining page-local duplication from active empty-state, calendar, task-detail, and import actions.
- Reuse the shared 44px Button and Spinner contracts established by `/projects` while preserving intentionally compact tabs, badges, and icon-only controls.
- Prevent duplicate file-import interactions while an Inertia upload is processing and expose its busy state accessibly.
- Preserve existing routes, payloads, translations, permissions, export behavior, and task behavior.

### Changed Files

- `resources/js/components/shared/EmptyState.vue`
- `resources/js/components/task/TaskDetail.vue`
- `resources/js/pages/calendar/Index.vue`
- `resources/js/pages/settings/Export.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- The focused RED run produced two expected failures for duplicated large-action styles and the missing import busy state.
- Focused frontend-design coverage passed after implementation with 106 tests and 449 assertions.
- Full Pest coverage passed with 396 tests and 1,773 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Desktop light browser QA confirmed the shared task empty-state action is 44px with the canonical 12px radius and orange treatment, while Export/Import retains coherent 80px action cards, visible keyboard-focus file inputs, and zero horizontal overflow.
- Mobile dark browser QA confirmed the calendar Today action is 44px with the shared 12px radius and zero horizontal overflow at 390x844.
- Task rows continue to resolve to the canonical task show page during live navigation, so the imported task-detail drawer controls are protected by the focused source contract; the task show page was already covered in the preceding browser phase.
- No data-changing import was submitted during visual QA; the upload interlock, disabled state, accessible busy state, shared Spinner, and lifecycle reset are covered by the focused regression contract.
- The final browser interactions produced no console errors or page errors; the Boost browser log contained only historical entries dated 2026-07-19.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Compact tabs, badges, and icon-only controls intentionally retain their smaller semantic sizes.

### Git Delivery

- Implementation commit: `dea5fca` (`fix: align shared secondary actions`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `72a3b58` (`docs: record secondary action unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Page Header Action State Unification

### Status

- Completed.

### Scope And Decisions

- Make every active `WorkspacePageHeader` action use the same shared 44px Button contract as `/projects`.
- Remove page-local orange, height, radius, shadow, and focus duplication from project, task, notification, and workspace headers.
- Add shared Spinner feedback, duplicate-submission guards, disabled states, and lifecycle reset to header-level Inertia mutations.
- Preserve existing routes, payloads, translations, permissions, toast behavior, navigation, and responsive wrapping.

### Changed Files

- `resources/js/pages/notifications/Index.vue`
- `resources/js/pages/projects/Index.vue`
- `resources/js/pages/projects/Show.vue`
- `resources/js/pages/tasks/Index.vue`
- `resources/js/pages/tasks/Show.vue`
- `resources/js/pages/workspaces/Index.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- The focused RED run produced seven expected failures for six inconsistent page-header button contracts and the missing shared mutation loading states.
- Focused frontend-design coverage passed after implementation with 113 tests and 482 assertions.
- Full Pest coverage passed with 403 tests and 1,806 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,369 modules.
- Desktop light browser QA confirmed Projects, Workspaces, and Notifications use 44px header actions with the canonical 12px radius and zero horizontal overflow.
- Mobile dark browser QA confirmed Project Show and Task Show wrap their 44px actions across two balanced rows at 390x844 with zero horizontal overflow.
- Live measurements confirmed every rendered header action reports `data-size="lg"`; no page or console errors were produced.
- Data-changing header actions were not submitted during visual QA; duplicate-submission guards, disabled states, shared Spinner feedback, and `onFinish` resets are protected by focused regression contracts.
- The Boost browser log contained only historical entries dated 2026-07-19.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Compact list-row, tab, badge, and icon-only actions intentionally retain their smaller semantic sizes.

### Git Delivery

- Implementation commit: `8027849` (`fix: unify page header action states`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `15b9f40` (`docs: record page header action unification`).
- Documentation push: successful to `origin/main`.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Shared Segmented Filter Unification

### Status

- Completed.

### Scope And Decisions

- Replace the duplicated project, notification, calendar, and activity segmented filters with shared warm-precision components derived from `/projects`.
- Keep horizontal controls scroll-safe on narrow screens and preserve the activity filter's responsive vertical layout.
- Preserve each page's existing filter state, counts, translations, routes, and interaction behavior while retaining the correct tablist or button-group semantics.
- Add a missing accessible label to the calendar view switcher and keep orange focus, reduced-motion, muted inactive, and card-active states consistent.

### Changed Files

- `resources/js/components/shared/WorkspaceSegmentedControl.vue`
- `resources/js/components/shared/WorkspaceSegmentedButton.vue`
- `resources/js/pages/activity/Index.vue`
- `resources/js/pages/calendar/Index.vue`
- `resources/js/pages/notifications/Index.vue`
- `resources/js/pages/projects/Index.vue`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change is planned.

### Verification

- The focused RED run produced the expected failures for four consumers without the shared components, the absent shared component files, and the calendar switcher's missing accessible label.
- Focused segmented-control coverage passed with 11 tests and 37 assertions; the complete frontend-design file passed with 116 tests and 505 assertions.
- Full Pest coverage passed with 406 tests and 1,829 assertions.
- PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- Production build passed after transforming 3,373 modules.
- Desktop light browser QA confirmed Projects uses 40px shared tabs and Activity uses full-width 44px filters; live Archived and Created selections updated both ARIA and visual state with zero page overflow.
- Mobile dark browser QA confirmed Notifications and Calendar use 40px shared tabs, the calendar switcher now exposes the translated `Filters` label, and local state changes updated both ARIA and visual state.
- Activity retained 44px horizontal controls on mobile: its 182px excess width remains contained in the filter's intentional horizontal scroller while document overflow stays at zero.
- Browser QA produced no console errors or page errors; the Boost browser log contained only historical entries dated 2026-07-19.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.

### Known Limitations And Next Work

- Vite continues to report the existing optional `fontaine` optimization notice; the production build succeeds.
- Activity intentionally scrolls its four filters horizontally below the desktop breakpoint, preserving 44px targets without forcing the page wider than the viewport.

### Git Delivery

- Implementation commit: `cf518cc` (`fix: unify segmented filters`).
- Implementation push: successful to `origin/main`.
- Documentation commit: `dfccb7d` (`docs: record segmented filter unification`).
- Documentation push: successful to `origin/main`.
- Unrelated user-authored progress entries remain preserved outside this phase's staged changes.
- This final delivery record is committed and pushed separately so the phase commits remain focused.

## Dashboard Information Correctness Repair

### Status

Completed.

### Scope And Decisions

- Correct dashboard totals, completion metrics, overdue tasks, upcoming tasks, and weekly activity that were accidentally inheriting the first `due_date = today` constraint from a shared mutable builder.
- Move the complex read into `DashboardQuery`, keep every task query scoped to the selected workspace, and keep `DashboardController` limited to authenticated context and response assembly.
- Count all overdue tasks independently from the ten-item display list and collapse the previous per-day weekly count loop into one conditional aggregate query.
- Calculate calendar and timestamp boundaries in the authenticated user's configured timezone, with UTC boundaries for stored timestamps.
- Serialize `due_date` as the date-only `Y-m-d` value so negative-offset timezones cannot shift a displayed deadline to the previous day.
- Preserve the existing Inertia component and prop names, archived-task exclusion, empty-workspace response, and SQLite-only architecture.

### Changed Files

- `app/Http/Controllers/DashboardController.php`
- `app/Models/Todo.php`
- `app/Services/DashboardQuery.php`
- `tests/Feature/DashboardTest.php`
- `docs/progress.md`.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- The focused RED run failed because `stats.overdue_count` was `0` instead of `1`; the date-only RED run failed because `2026-07-19` serialized as `2026-07-19T00:00:00.000000Z`.
- Focused dashboard coverage passed with 3 tests and 49 assertions.
- Full Pest coverage passed with 407 tests and 1,875 assertions.
- Full PHPStan passed with 0 errors.
- Vue TypeScript checking, ESLint, resource Prettier verification, and the frontend Node test passed.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.
- The production build passed after transforming 3,373 modules; only the existing optional `fontaine` optimization notice remains.
- Live desktop browser QA at `https://xiaomi-mimo.test/dashboard` confirmed totals of 27 tasks, 8 completed, and 3 overdue against SQLite, correct deadline dates such as `Jul 21`, and no console or page errors.

### Known Limitations And Next Work

- The optional `fontaine` notice remains non-blocking and outside this dependency-free repair.
- The dashboard keeps the existing ten-item overdue/upcoming display limits; aggregate overdue totals are no longer capped by those lists.

### Git Delivery

- Implementation commit `275d364` (`fix: correct dashboard information`) was pushed successfully to `origin/main`.
- Documentation commit `a8e3df0` (`docs: record dashboard information repair`) was pushed successfully to `origin/main`.
- This final delivery record will be committed and pushed separately. Existing unrelated `docs/progress.md` changes remain preserved and excluded.

## Dashboard Information Presentation Repair

### Status

Completed.

### Scope And Decisions

- Reconcile the live dashboard with the already-correct workspace-scoped SQLite and Inertia data contract.
- Render the daily task and completion information currently supplied but ignored by `Dashboard.vue`.
- Keep today's tasks and future upcoming tasks in distinct, non-duplicated buckets.
- Reuse the existing productivity component so both completed and created weekly series are presented accessibly.

### Migrations And Packages

No migration or Composer/npm package change was made.

### Changed Files

- `app/Services/DashboardQuery.php`
- `resources/js/pages/Dashboard.vue`
- `resources/js/components/dashboard/ProductivityChart.vue`
- `lang/en/ui.php`
- `lang/lt/ui.php`
- `lang/ru/ui.php`
- `tests/Feature/DashboardTest.php`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Verification

- Live reproduction confirmed dashboard, task-index, and SQLite totals agree at 27 active tasks, 8 completed tasks, and 3 overdue tasks.
- Source inspection confirmed `today_count`, `completed_today`, `todayTasks`, `completion_rate`, and `weeklyData.created` are supplied but not fully rendered.
- The focused RED run failed because a today task was duplicated in `upcomingTasks` and the Vue page did not render the complete supplied information; the focused GREEN run passed with 5 tests and 56 assertions.
- Dashboard, frontend-design, and localization coverage passed with 123 tests and 591 assertions.
- Full Pest coverage passed with 408 tests and 1,879 assertions.
- PHPStan, Vue TypeScript checking, ESLint, Prettier verification, and the frontend Node test passed.
- `vendor/bin/pint --dirty --format agent` and `git diff --check` passed.
- The production build passed after transforming 3,375 modules; only the existing optional `fontaine` notice remains.
- Live desktop and 390-pixel mobile QA showed the overall completion summary, due-today and completed-today metrics, a distinct today section, future-only upcoming tasks, full years on deadlines, and both weekly completed/created series.
- Browser QA produced zero page errors, console errors, and horizontal overflow; Boost logs contain only historical entries from 2026-07-19.

### Known Limitations And Next Work

- Two existing SQLite tasks have due dates in 1976 and 2000. They are correctly counted as overdue and now display their full year; this phase did not alter or delete user data.
- The existing optional `fontaine` build notice remains non-blocking and outside this dependency-free repair.

### Git Delivery

- Implementation commit `de534f7` (`fix: present complete dashboard information`) was pushed successfully to `origin/main`.
- Documentation commit `b99f915` (`docs: record dashboard presentation repair`) was pushed successfully to `origin/main`.
- Unrelated user-authored progress entries remain preserved outside this phase's staged changes.
- This final delivery record will be committed and pushed separately so the phase commits remain focused.

## Workspace Management Center Phase 1: Portfolio CRUD

### Status

Completed and pushed.

### Before-Phase Baseline

- `/workspaces` could create and switch workspaces but did not expose complete edit, duplicate, and safe delete flows.
- Portfolio cards showed incomplete counts, did not identify the current workspace consistently, and did not expose policy-derived actions.
- The existing API workspace CRUD had no token ability boundary and no duplicate endpoint.

### Scope And Decisions

- Keep the portfolio at `/workspaces` as the entry point for the approved maximum management center.
- Add search and sorting, accurate member/project/task metrics, current-workspace state, and policy-aware manage, switch, edit, duplicate, and delete actions.
- Duplicate workspace metadata, labels, tags, and owner membership transactionally while intentionally excluding projects and tasks.
- Require the exact workspace name before destructive deletion and show the affected member/project/task counts.
- Select an authorized fallback workspace after deleting the current workspace, or clear the session after deleting the final workspace.
- Apply `workspaces:write` to API workspace mutations and keep API permission flags consistent with the authenticated token's abilities as well as workspace policies.
- Reuse loaded membership pivots and cached role resolution to avoid permission N+1 queries across the portfolio.

### Changed Files

- `app/Actions/DuplicateWorkspace.php`
- `app/Actions/UpdateWorkspace.php`
- `app/Http/Controllers/WorkspaceController.php`
- `app/Http/Controllers/Api/WorkspaceController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Requests/DuplicateWorkspaceRequest.php`
- `app/Http/Resources/WorkspaceResource.php`
- `app/Models/Workspace.php`
- `app/Policies/WorkspacePolicy.php`
- `bootstrap/app.php`
- `routes/web.php`
- `routes/api.php`
- `resources/js/pages/workspaces/Index.vue`
- `resources/js/components/shared/WorkspaceConfirmDialog.vue`
- `resources/js/types/models.ts`
- `lang/en/ui.php`
- `lang/lt/ui.php`
- `lang/ru/ui.php`
- `tests/Feature/WorkspaceManagementTest.php`
- `tests/Feature/ModelRelationsTest.php`
- `tests/Feature/FrontendDesignTest.php`
- `docs/progress.md`

### Migrations And Packages

No migration or Composer/npm package change was made.

### Verification

- Focused Phase 1 Pest coverage passed with 183 tests and 680 assertions, including accurate counts, current-session fallback, owner/admin/member/outsider authorization, whitespace validation, label/tag-only duplication, safe deletion, restricted API tokens, and no-query loaded-pivot reuse.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan, Vue TypeScript checking, full ESLint, resource Prettier verification, and `git diff --check` passed.
- The production build passed after transforming 3,375 modules; only the existing optional `fontaine` optimization notice remains.
- Route inspection confirmed every API workspace mutation runs through Sanctum authentication and `CheckAbilities:workspaces:write`.
- Live browser CRUD verified create, edit, duplicate, switch, and delete with temporary workspaces; all temporary data was removed afterward.
- Live browser interaction verified workspace search and descending-name sort with multiple records.
- Live delete QA verified the button remains disabled for empty and incorrect confirmation text, enables only for the exact workspace name, displays 3 members, 6 projects, and 27 tasks for `Acme Projects`, and cancels without deletion.
- A fresh browser reload produced zero page errors or console errors; Boost browser logs contained only older unrelated entries from 2026-07-19.
- Independent re-review reported no remaining blocking or important Phase 1 issues.

### Known Limitations And Next Work

- Portfolio search and sorting are client-side over the authorized workspace collection; server pagination can be added if portfolios grow materially.
- Phase 2 will replace the temporary Manage hand-off with the canonical `/workspaces/{workspace}` management center and complete member and invitation administration.

### Git Delivery

- Implementation commit `2fc1dcd` (`feat: complete workspace portfolio CRUD`) was pushed successfully to `origin/main`.
- This progress record will be committed as `docs: record workspace portfolio CRUD` and pushed separately.
- The pre-existing Task Index and Herd Upload progress entries remain preserved and excluded from this phase's staged changes.

## SQLite Runtime Health And Query Performance

### Status

Completed and pushed.

### Before-Phase Baseline

- Laravel resolves the local default to an absolute database path, but `.env.example` still advertises a stale relational-database value and production startup does not validate the file, writable parent, or permitted directory.
- Foreign keys, WAL, synchronous mode, transaction mode, and a 5-second busy timeout are fixed in `config/database.php`; cache size, temporary storage, and WAL checkpoint cadence are not configurable or verified.
- Laravel's `/up` route proves only that the application booted. It does not verify SQLite integrity, foreign keys, configured pragmas, or path safety, and there is no path-safe internal diagnostic command.
- `Todo::progress` and `Checklist::progress` execute relationship queries from model accessors. Major page query counts are not protected by deterministic budgets.
- Live `EXPLAIN QUERY PLAN` uses the existing workspace/archive task index but creates a temporary B-tree for the default task ordering. Equivalent scoped ordering gaps exist for projects, project tasks, checklists, checklist items, activity, calendar, and notifications.

### Scope And Decisions

- Validate SQLite configuration once at startup, expose environment-backed supported pragmas, and connect database checks to Laravel's health event plus an internal diagnostic command without returning filesystem paths.
- Remove query-bearing accessors and keep resource progress derived only from loaded data or explicit aggregates.
- Add only composite indexes justified by captured representative query plans, preserving populated data through a SQLite-compatible migration.
- Add focused runtime, health, migration, integrity, query-plan, no-hidden-query, query-budget, and busy/constraint classification tests before running the complete quality matrix.
- Permit in-memory SQLite only for testing/runtime-specific connections; filesystem databases must be absolute, readable/writable, and contained by an explicitly configured allowed directory with a writable parent.
- Validate every environment-backed integer and enum pragma before the connector interpolates it, and keep unsafe journal/synchronous choices outside the supported configuration contract.
- Use Laravel's `DiagnosingHealth` event so `/up` remains a path-free generic probe while `app:database-health --json` exposes only boolean check names and never physical paths.
- Preserve the existing bounded transaction retries and prove their classification against real SQLite locks and unique constraints rather than adding a second retry abstraction.
- Add stable UUID tie-breakers to ordered reads so composite indexes and pagination have deterministic order.

### Changed Files

- SQLite environment/configuration contract, startup provider integration, health service, and internal command in `.env.example`, `config/database.php`, and `app/`.
- Todo/checklist progress models and resources plus stable ordering in workspace, task-sort, project-detail, activity, and notification query paths.
- `2026_07_22_165050_add_scoped_query_indexes.php` for task, project, checklist, activity, calendar/dashboard, and notification reads.
- `tests/Feature/SqliteRuntimeHealthTest.php`, `tests/Feature/PageQueryBudgetTest.php`, and deterministic dashboard fixture coverage.

### Migrations And Packages

- Added and applied `2026_07_22_165050_add_scoped_query_indexes.php` to the populated live SQLite database; all eight indexes are reversible and no row/table rebuild was required.
- Added no Composer or npm dependency.

### Verification

- Focused runtime, query-budget, schema-integrity, NativePHP, and page-architecture coverage passed with 45 tests and 525 assertions before the final matrix.
- The complete Pest suite passed with 516 tests and 2,867 assertions. The previously random dashboard due-date fixture was made deterministic and then passed three consecutive focused runs.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan with zero errors, Vue TypeScript checking, full ESLint, resource Prettier verification, the frontend regression test, and `git diff --check` passed.
- The production build passed after transforming 3,398 modules; only the existing optional `fontaine` optimization notice and build-plugin timing advisory remain.
- The health report verified the absolute-path policy, foreign keys, WAL, synchronous mode, 5-second busy timeout, cache size, in-memory temp store, WAL autocheckpoint, `quick_check`, and `foreign_key_check` on the live database.
- Live `EXPLAIN QUERY PLAN` confirmed covering scoped indexes for default task order, project tasks, calendar/dashboard due dates, activity, and notifications with no temporary order B-tree; the focused plan suite also covers projects, checklists, and checklist items.
- Representative data-growth coverage holds query counts constant and at or below 35 queries for dashboard, task index/detail, project index/detail, activity, calendar, and notifications.
- A real second SQLite connection proved bounded transactions retry lock failures three times while a unique-constraint violation is attempted once.
- Live Herd `GET /up` followed the HTTPS redirect and returned `200 {"status":"up"}`; current browser logs contain only historical 2026-07-19 entries.

### Known Limitations And Next Work

- The absolute/allowed-directory guard cannot prove every filesystem's locking semantics; deployments must still keep the database, WAL, and SHM files on a local SQLite-compatible filesystem.
- Existing low-selectivity single-column indexes remain because this phase found no representative evidence that removing them would improve the current write/read balance.
- Recurrence, reminder claiming, import/export, and destructive storage flows will reuse the verified SQLite transaction behavior in their dedicated phases.

### Git Delivery

- Implementation commit `1c8d8ac` (`fix: harden sqlite runtime and query performance`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record sqlite runtime and query performance` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Database Backup And Restore Hardening

### Status

Completed and pushed.

### Before-Phase Baseline

- Every authenticated verified user can create, enumerate, download, and restore application-wide SQLite backups over HTTP.
- Backup responses expose physical server paths and accept raw filenames, including traversal-shaped input.
- The service copies only the main database file while WAL mode is active and replaces the live file without integrity, schema, locking, maintenance, or rollback controls.
- The current settings navigation presents this global administrative surface to every user even though workspace ownership is the application security boundary.

### Scope And Decisions

- Keep scheduled and manual CLI snapshots available while making web backup management an explicit deployment-level operator capability configured by email and additionally requiring workspace ownership.
- Require verified authentication and recent password confirmation for the web inventory, create, download, and restore routes; ordinary owners, admins, members, and unconfigured deployments remain denied.
- Create WAL-consistent snapshots with SQLite's online backup API, store them privately under opaque UUID identifiers, and expose no physical path or server filename.
- Sign snapshot manifests and verify size, checksum, application schema fingerprint, SQLite integrity, and foreign keys before download or restore.
- Serialize create and restore operations with a database-independent file lock.
- Restore through SQLite's backup API under a maintenance guard, create a verified rollback snapshot first, and automatically roll back if the restored database fails validation.
- Keep the existing Inertia settings visual language, generated Wayfinder routes, localized English/Lithuanian/Russian copy, and complete processing/error states.

### Changed Files

- Backup actions, authorized Form Requests, deployment-operator policy, service, controllers, provider registration, routes, and command under `app/`, `bootstrap/`, and `routes/`.
- Backup configuration and environment contract under `config/backup.php`, `config/nativephp.php`, and `.env.example`.
- Inertia capability sharing, settings navigation, backup page types and interaction state, and English, Lithuanian, and Russian UI translations.
- Focused backup/restore and application-layer contract tests.

### Migrations And Packages

- No migration or Composer/npm package change was made.
- Created one live recovery snapshot through `php artisan backup:run`; its opaque identifier is `1794af6c-63ad-442d-97d4-7e84648d2093`, and both snapshot and signed manifest remain on the private backup path.

### Verification

- Focused backup and application-layer Pest coverage passed with 19 tests and 93 assertions, including committed WAL data, exclusive locking, signed inventory, tamper rejection, schema mismatch rejection, successful isolated restore, rollback, maintenance release, deployment-operator authorization, password confirmation, path rejection, safe controller failures, and private download headers.
- Full Pest passed with 477 tests and 2,537 assertions.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan, Vue TypeScript checking, full ESLint, Prettier verification, frontend tests, and `git diff --check` passed.
- The production build passed after transforming 3,394 modules; only the existing optional `fontaine` optimization notice remains.
- Strict Composer validation, Composer security audit, and production npm audit passed with no advisories or vulnerabilities.
- Route inspection confirmed verified authentication, the deployment-operator gate, recent password confirmation, UUID constraints, and create/restore throttles across the five backup routes.
- Live Herd QA confirmed an anonymous direct visit redirects to the login page with no new browser console errors; current Boost browser logs contain only historical 2026-07-19 entries.
- The live recovery snapshot was created and validated without restoring over the development database; successful and failed restore paths were verified against isolated SQLite databases only.

### Known Limitations And Next Work

- Web backup management is deliberately disabled until `BACKUP_OPERATOR_EMAIL` identifies a verified workspace owner; ordinary workspace roles cannot access application-wide database material.
- Legacy filename-based snapshots are intentionally excluded from the signed opaque inventory and should remain offline or be recreated with the hardened command.
- Restore intentionally refuses a snapshot whose migration fingerprint differs from the current database, so a fresh recovery snapshot is required after schema changes.
- Production PHP-FPM, filesystem ownership, encryption-at-rest, and off-host retention remain deployment responsibilities.

### Git Delivery

- Implementation commit `0ae818b` (`fix: harden database backup and restore`) was pushed successfully to `origin/main`.
- This progress record will be committed as `docs: record database backup hardening` and pushed separately.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## SQLite UUID And Task Parent Integrity

### Status

Completed and pushed.

### Before-Phase Baseline

- The live `passkeys.user_id`, `notifications.notifiable_id`, and `personal_access_tokens.tokenable_id` columns retain integer affinity even though user identifiers are UUID strings.
- Five live tasks use `parent_id`, but the database has no self-referencing foreign key and no database-level same-workspace guard for parent assignment.
- The live database currently has five UUID-backed notification rows, zero passkeys, zero personal access tokens, five parent links, and zero reported foreign-key violations.

### Scope And Decisions

- Correct both fresh-install migrations and the populated SQLite upgrade path so user-backed relations use UUID columns without losing rows or indexes.
- Preserve polymorphic type/index contracts for notifications and Sanctum tokens while validating that every populated user-backed row still resolves.
- Add a nullable self-reference for task parents and SQLite insert/update guards that reject cross-workspace parent links atomically.
- Keep rollback populated-safe and prove migrate, rollback, and re-migrate behavior against SQLite fixtures before touching the live database.

### Changed Files

- Corrected the original passkey, notification, Sanctum token, and todo migrations for fresh SQLite installations.
- Added a populated-safe corrective migration with preflight validation, UUID column changes, the task-parent foreign key, same-workspace/self-parent triggers, and restoration of task-definition triggers after SQLite table rebuilds.
- Added focused schema-integrity coverage and updated the declared todo foreign-key count in the supporting infrastructure contract.

### Migrations And Packages

- Added `2026_07_22_154915_correct_uuid_relations_and_task_parent_integrity.php`; it ran as batch 7 on the populated live SQLite database.
- Created pre-migration recovery snapshot `5bf5e5c0-3b15-478b-9ba4-672d68cbe1f3` and current-schema recovery snapshot `a57e2662-8f55-446b-bfe3-4c120dc88060` through the signed backup service.
- No Composer or npm package change was made.

### Verification

- Focused schema-integrity coverage passed with 4 tests and 35 assertions; the combined schema/infrastructure run passed with 36 tests and 77 assertions.
- The populated rollback and re-apply both completed successfully; the final migration is recorded as batch 7.
- Live schema inspection confirmed `varchar` affinity for all three UUID relation columns, the self-referencing `todos.parent_id` foreign key with `ON DELETE SET NULL`, all four parent/task-definition triggers, and all original indexes.
- Live data remained at 5 notifications, 27 tasks, and 5 parent links, with zero invalid parent links and zero `pragma_foreign_key_check` rows; the empty passkey and token tables remained intact.
- Full Pest passed with 481 tests and 2,572 assertions; full configured PHPStan passed with zero errors.
- `vendor/bin/pint --dirty --format agent`, Vue TypeScript checking, full ESLint, resource Prettier verification, the frontend regression test, Composer strict validation, and `git diff --check` passed.
- The production build passed after transforming 3,394 modules; only the existing optional `fontaine` optimization notice remains.

### Known Limitations And Next Work

- Notifications and Sanctum tokens remain polymorphic, so database foreign keys cannot target a single table; the migration validates existing User-backed rows while preserving the polymorphic type/index contract.
- Database triggers reject missing, cross-workspace, and direct self-parent assignments. General multi-node cycle detection remains an application-level concern if parent reassignment is exposed in a future write flow.
- The pre-migration snapshot intentionally has the old schema fingerprint and can only be restored while running the matching migration set; use the post-migration snapshot for the current application version.

### Git Delivery

- Implementation commit `b7c9673` (`fix: enforce sqlite uuid and parent integrity`) was pushed successfully to `origin/main`.
- This progress record will be committed as `docs: record sqlite relation integrity` and pushed separately.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Versioned API Contract And Authentication Hardening

### Status

Completed and pushed.

### Before-Phase Baseline

- The API is exposed only through unnamed `/api/*` routes with no version boundary or compatibility/deprecation signal.
- Successful payloads vary between direct resources, domain-key wrappers, and collections; framework errors have a separate shape and no request correlation identifier.
- API login and registration have no explicit limiter, and tokens issued by those endpoints receive Sanctum's unrestricted `*` ability.
- Existing resource-family routes do enforce explicit ability middleware when a scoped token is supplied, and dedicated API controllers already exist for the main domain resources.

### Scope And Decisions

- Introduce canonical, named `/api/v1/*` routes while retaining `/api/*` as an explicitly deprecated compatibility layer backed by the same route definitions and controllers.
- Standardize v1 success and error envelopes with a UUID request identifier, API version header, deterministic machine code, and localized messages without changing legacy payload bodies.
- Add bounded credential/IP rate limits for login and registration and issue only the six documented workspace/project/task read/write abilities.
- Return API user data through `UserResource`, keep Sanctum policies plus route abilities as separate checks, and add no dependency.

### Changed Files

- Versioned API middleware, response factory, token-ability enum, API authentication Form Requests, user controller/resource integration, authentication controller, and scoped child-resource controller adapters under `app/`.
- `bootstrap/app.php`, `routes/api.php`, and the API authentication limiters in `app/Providers/AppServiceProvider.php`.
- English, Lithuanian, and Russian API error translations under `lang/`.
- `tests/Feature/Api/ApiContractTest.php` and `docs/progress.md`.

### Migrations And Packages

- No database migration was required.
- No Composer or npm package change was made.

### Verification

- The focused v1 contract suite passed with 13 tests and 103 assertions; the complete API suite passed with 51 tests and 242 assertions.
- Full Pest passed with 494 tests and 2,675 assertions, including the existing unversioned API compatibility suite.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan with zero errors, Vue TypeScript checking, full ESLint, resource Prettier verification, the frontend regression test, and `git diff --check` passed.
- The production build passed after transforming 3,394 modules; only the existing optional `fontaine` optimization notice remains.
- Route inspection confirmed 65 named canonical `/api/v1/*` routes, globally scoped bindings, explicit workspace/project/task abilities, and separate login/registration limiters; secured legacy names and paths remain available.
- Contract coverage proves normalized collection/item/204 responses, parent-scoped project and task-child bindings, explicit issued token abilities, 401/403/404/422/429 errors, UUID replacement/correlation, EN/LT fallback behavior, and unchanged legacy response bodies.
- Live Herd requests confirmed a localized v1 `401` envelope with matching request headers and a legacy `401` body with `Deprecation`, `X-API-Version: legacy`, and a canonical successor link.
- No current backend or browser error was produced by live API verification; Boost browser logs contain only historical entries from 2026-07-19, while expected test-only backup failure logs remain isolated to the testing environment.

### Known Limitations And Next Work

- The unversioned API remains enabled as a deprecated compatibility layer without a removal date so existing consumers are not broken during this phase.
- A published consumer-facing OpenAPI schema and external-client migration guide remain future documentation work; the executable Pest contract is the current source of truth.

### Git Delivery

- Implementation commit `aef5b0f` (`feat: establish versioned api contract`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record versioned api contract` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Backend Page Controllers And Query Boundaries

### Status

Completed and pushed.

### Before-Phase Baseline

- The `/tasks`, `/projects`, and `/activity` shortcut routes still resolve the selected workspace, manufacture empty Inertia props, and invoke other controllers from closures through `app()`.
- Task index/detail, project index/detail, calendar, activity, and notification reads remain embedded in HTTP controllers; only dashboard and workspace management currently use focused query objects.
- `TodoController::index` still selects Inertia or JSON presentation through `expectsJson()`, while the external API now has dedicated v1 and legacy routes.

### Scope And Decisions

- Replace all query-bearing shortcut closures with named page-controller actions and one selected-workspace query boundary.
- Move task, project, activity, calendar, and notification read construction into focused query objects while controllers retain authorization, resource transformation, and Inertia presentation.
- Remove task-index content negotiation from the web controller and keep external JSON reads on the dedicated API routes.
- Preserve route names, selected-workspace behavior, no-workspace empty states, URL-backed filters, localization, and current Inertia prop shapes.
- Add controller/query architecture and behavior regressions before implementation; add no dependency or migration.

### Changed Files

- Dedicated task/project index controllers, API membership/invitation/ownership controllers, and selected-workspace/task/project/activity/calendar/notification query objects under `app/`.
- Task, project, dashboard, calendar, activity, notification, workspace, and legacy-settings controllers plus the now query-free `User` model helper surface.
- `routes/web.php` and `routes/api.php`, including scoped web project bindings and dedicated API presentation targets.
- Task detail, workspace member, and ownership frontend request routing under `resources/js/`, now using canonical v1 endpoints for standalone JSON mutations.
- Focused page/query, task, checklist, comment, task-definition, and workspace-membership regressions under `tests/Feature/`.

### Migrations And Packages

- No database migration was required.
- No Composer or npm package change was made.

### Verification

- `tests/Feature/PageQueryArchitectureTest.php` passed with 8 tests and 76 assertions, covering named controller actions, complete no-workspace props, Inertia-only task index behavior, first-party v1 session access, no request-header response branching, and scoped project bindings.
- The combined page/query/API regression set passed with 123 tests and 859 assertions; focused current-workspace/dashboard/settings/data-transfer coverage passed with 45 tests and 353 assertions.
- Full Pest passed with 502 tests and 2,761 assertions after the presentation split.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan with zero errors, Vue TypeScript checking, full ESLint, resource Prettier verification, the frontend regression test, and `git diff --check` passed.
- The production build passed after transforming 3,398 modules; only the existing optional `fontaine` optimization notice remains.
- Source and route inspection confirmed no query-bearing product closures, no controller-to-controller container dispatch, no `expectsJson()`/API-path response negotiation in controllers, and dedicated v1 membership/invitation/ownership actions.
- Live Herd requests for `/tasks`, `/projects`, and `/activity` followed the expected HTTPS/authentication flow to the login page without a server error.

### Known Limitations And Next Work

- Query extraction establishes stable read boundaries; representative query budgets, accessor cleanup, `EXPLAIN QUERY PLAN`, index decisions, and SQLite runtime health remain the next database-performance phase.
- Web mutation routes now have one deterministic response type. Standalone first-party JSON interactions use named v1 routes; the deprecated unversioned external API remains only for compatibility.

### Git Delivery

- Implementation commit `7851df7` (`refactor: establish page query boundaries`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record page query boundaries` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Workspace Management Center Phase 3: Labels And Tags

### Status

Completed and pushed.

### Before-Phase Baseline

- Labels and tags exist as workspace-owned task relations, but update and delete routes bind them globally instead of through the authorized workspace.
- Attach validation accepts any existing relation identifier, so foreign workspace identifiers are not rejected atomically.
- The management center Task configuration section is still a localized placeholder and the API mutations do not enforce workspace write abilities.

### Scope And Decisions

- Add workspace-scoped label and tag CRUD with normalized unique names, usage counts, safe detach-on-delete behavior, and owner/admin management permissions.
- Nest every web and API mutation below the workspace and enable scoped binding or explicit workspace resolution for every related identifier.
- Require `workspaces:read` for API reads and `workspaces:write` for API mutations while preserving member read access.
- Harden task label/tag attachment and detachment so mixed-workspace identifiers fail without partial writes.
- Replace the configuration placeholder with complete accessible label and tag management using the canonical workspace page.
- Keep metadata readable by every authorized workspace member while limiting create, update, and delete operations to owners and admins.
- Normalize names before validation, enforce case-insensitive uniqueness in SQLite, and convert write-race unique constraint failures into field validation responses.
- Store Unicode-aware normalized-name keys produced by PHP so Lithuanian and Russian case variants are rejected even though SQLite `lower()` and `NOCASE` are ASCII-only.
- Delete labels and tags transactionally by detaching task pivots first; linked tasks are preserved and the confirmation communicates the affected task count.
- Wrap task creation and update relation synchronization in transactions so invalid or failed relation writes cannot partially mutate the task.
- Preserve the existing unversioned label/tag mutation API as secured compatibility aliases beside the canonical workspace-nested endpoints.
- Bound each workspace to 200 labels and 200 tags so API and Inertia metadata collections remain complete and predictable.

### Changed Files

- Label and tag actions, controllers, Form Requests, resources, workspace policy/query integration, task write actions, and task validation under `app/`.
- `routes/web.php` and `routes/api.php`.
- `resources/js/components/workspace/WorkspaceConfigurationPanel.vue`, `resources/js/pages/workspaces/Show.vue`, its focused source test, and shared TypeScript models.
- English, Lithuanian, and Russian task-configuration translations.
- `tests/Feature/LabelTagTest.php`, `tests/Feature/Api/ApiLabelTagTest.php`, and `docs/progress.md`.

### Migrations And Packages

- Added and applied `2026_07_20_181649_add_workspace_name_unique_indexes_to_labels_and_tags.php`, which adds required Unicode-normalized name keys and workspace-scoped unique indexes for labels and tags.
- The migration transactionally trims existing names, merges case-only duplicates, transfers their task pivots with duplicate protection, and preserves every linked task before creating the indexes.
- Pre-migration live checks found no trimmed or case-insensitive duplicate names; the migration still covers populated databases where such duplicates exist.
- No Composer or npm package change was made.

### Verification

- Full Pest passed with 439 tests and 2,334 assertions, including owner/admin/member authorization, nested workspace isolation, Unicode uniqueness, populated-database duplicate merging, mixed-identifier atomic rejection, bounded collections, usage counts, pivot-safe deletion, legacy API compatibility, token abilities, API JSON semantics, and existing task write regressions.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan, Vue TypeScript checking, full ESLint, resource Prettier verification, the five focused workspace source tests, and `git diff --check` passed.
- The production build passed after transforming 3,387 modules; only the existing optional `fontaine` optimization notice remains.
- Route inspection confirmed canonical nested label/tag API reads use `workspaces:read`, mutations use `workspaces:write`, update/delete bindings are scoped through the authorized workspace, and secured legacy mutation aliases remain available.
- Live desktop QA verified label and tag create, edit, usage-count presentation, confirmation, and deletion on the canonical configuration page.
- The rebuilt live page exposes item-specific edit/delete accessible names and neutral count grammar.
- All temporary Phase 3 metadata was deleted through the UI and the live SQLite query returned zero matching rows afterward.
- Current live QA produced no page errors; Boost browser logs contain only historical entries from 2026-07-19.
- Independent re-review findings for API compatibility, Unicode uniqueness, populated migration safety, bounded collections, accessible names, and count grammar were resolved before delivery.

### Known Limitations And Next Work

- Status and priority configuration remains backed by the existing enums until Phase 4 adds workspace-owned definitions, ordering, defaults, completion semantics, and safe replacement-on-delete.
- Broader task relation and legacy API compatibility hardening remains Phase 5 work; this phase covers labels, tags, and their task pivot validation only.

### Git Delivery

- Implementation commit `ba08f2e` (`feat: add workspace task metadata CRUD`) was pushed successfully to `origin/main`.
- This progress record will be committed as `docs: record workspace task metadata CRUD` after the implementation commit is pushed.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Workspace Management Center Phase 4: Statuses And Priorities

### Status

Completed and pushed.

### Before-Phase Baseline

- Tasks persist enum-backed `status` and `priority` strings, so each workspace is limited to the three built-in statuses and five built-in priorities.
- The Task configuration page manages labels and tags only; statuses and priorities have no workspace-owned CRUD, ordering, defaults, archive state, or safe replacement flow.
- Task API payloads cannot expose definition resources or accept workspace-scoped definition identifiers.

### Scope And Decisions

- Add workspace-owned status and priority definition tables with stable compatibility keys, Unicode-normalized names, colors, ordering, default selection, archive state, usage counts, and explicit completed-status semantics.
- Backfill every existing workspace and task while preserving current `status` and `priority` string keys for unversioned API compatibility.
- Add owner/admin web and Sanctum API CRUD, archive/restore, reorder, default, and delete-with-replacement operations with atomic workspace isolation.
- Integrate task create, update, complete, uncomplete, duplicate, recurrence, filtering, sorting, resources, and configuration UI with the new definitions without partial writes.
- Keep bounded collections, EN/LT/RU localization, accessible controls, and focused migration/security/regression coverage.
- Keep the legacy scalar keys as an additive compatibility layer while definition identifiers provide scoped relations; SQLite triggers reject missing, mismatched, or cross-workspace dual writes.
- Derive task completion from the selected status definition and preserve `completed_at` when another completed status is selected.
- Include soft-deleted tasks in usage counts and replacement safeguards so a definition cannot be removed while recoverable tasks still reference it.

### Changed Files

- Workspace task-definition migration, models, factories, relations, compatibility cast, provisioning, transition, and management actions under `database/` and `app/`.
- Authorized web and API controllers, Form Requests, API resources, scoped task write validation, query integration, and versioned routes for statuses and priorities.
- Task creation, editing, completion, bulk mutation, duplication, recurrence, import/export, dashboard, calendar, project, and task-list integration.
- Reusable definition-management components, task-definition composable and types, workspace configuration UI, dynamic task controls, and English, Lithuanian, and Russian translations.
- Focused task-definition, API ability, tenant-isolation, Inertia redirect, checklist, reminder, activity, and infrastructure regression tests.

### Migrations And Packages

- Added `2026_07_20_185654_create_task_definitions_and_link_todos.php` with workspace-owned status and priority definitions, bounded positions, archive/default/completion semantics, composite tenant foreign keys, partial unique indexes, and semantic SQLite triggers.
- The migration seeded every existing workspace, preserved non-standard legacy keys, backfilled all task relation identifiers, and was applied successfully to the populated live SQLite database.
- Live verification confirmed 27 of 27 tasks mapped, zero key/identifier mismatches, zero completion mismatches, and exactly one default status, completion target, and default priority for the workspace.
- No Composer or npm package change was made.

### Verification

- Full Pest passed with 462 tests and 2,456 assertions, including provisioning, semantic database constraints, tenant foreign keys, web/API CRUD, permissions, atomic replacement, completion transitions, archive behavior, token abilities, and soft-deleted-task safeguards.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan, Vue TypeScript checking, full ESLint, resource Prettier verification, and `git diff --check` passed.
- The production build passed after transforming 3,394 modules; only the existing optional `fontaine` optimization notice remains.
- Route inspection confirmed definition reads use `workspaces:read`, mutations use `workspaces:write`, and existing workspace, project, and task API families now have explicit ability middleware.
- Live desktop QA verified create, edit, reorder, default/target selection, archive/restore, replacement delete, task assignment, completion, and reopen flows.
- Live mobile QA verified the responsive configuration CRUD; temporary QA definitions and task data were removed, leaving the original 27 tasks and no mapping inconsistencies.
- Fresh configuration and task-page browser logs contain no errors or warnings; Boost logs contain only historical 2026-07-19 entries.

### Known Limitations And Next Work

- Definition catalogs are intentionally bounded to 50 statuses and 25 priorities per workspace.
- Workspace JSON exports continue to carry stable task keys rather than embedding custom definition catalogs; cross-workspace import or transfer requires matching destination keys.
- Broader relation management beyond workspace membership, labels, tags, statuses, and priorities remains future work.

### Git Delivery

- Implementation commit `bb3b97a` (`feat: add workspace task definitions`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record workspace task definitions`.
- The pre-existing Task Detail, Task Index, and Herd Upload progress changes remain preserved and excluded from this phase's staged changes.

## Workspace Management Center Phase 2: Management And Membership

### Status

Completed and pushed.

### Before-Phase Baseline

- Workspace cards temporarily send Manage through the current-workspace switch flow to `/settings/members`; there is no canonical `/workspaces/{workspace}` management page.
- Membership supports immediate add and removal only. There is no pending invitation lifecycle, role update, ownership transfer, or management API.
- Inviting an unknown email currently creates a user with a predictable password, which must be replaced by a secure invitation flow.

### Scope And Decisions

- Add shareable Overview, Members, Task configuration, and Danger routes under the authorized workspace.
- Redirect the legacy `/settings/members` page to the selected workspace's canonical Members route.
- Add pending invitations with expiring signed acceptance, cancellation, and resend support without pre-creating user accounts.
- Add workspace-scoped member role changes, removal, and transactional ownership transfer while preserving exactly one owner.
- Expose the same membership operations through Sanctum-protected API routes with workspace-write ability checks.
- Keep Phase 2 migrations SQLite-native and add focused Pest coverage before frontend integration.
- Store invitation secrets only as SHA-256 hashes, rotate them after resend, cancellation, and acceptance, and deliver signed expiring acceptance links without creating placeholder users.
- Revalidate owner and manager authority inside SQLite-compatible transactional mutations; conditional writes prevent stale requests from demoting or removing the active owner.
- Require `workspaces:read` for API membership reads and `workspaces:write` for mutations, and return JSON for every `/api/*` response independently of the client Accept header.
- Treat the management-section controls as normal grouped links because each section has its own canonical URL.

### Changed Files

- Workspace membership actions, controllers, Form Requests, resources, models, policies, query object, invitation notification, data object, factory, and migration under `app/` and `database/`.
- `routes/web.php` and `routes/api.php`.
- `resources/js/pages/workspaces/Show.vue`, its focused source test, the four workspace management panels, the workspace portfolio, the legacy settings members page, settings navigation, and shared TypeScript models.
- English, Lithuanian, and Russian workspace-management and UI translations.
- `tests/Feature/WorkspaceMembershipManagementTest.php`, `tests/Feature/SettingsMembersTest.php`, and `docs/progress.md`.

### Migrations And Packages

- Added `2026_07_20_172037_create_workspace_invitations_table.php` with UUID ownership, inviter, normalized email, role, hashed token, expiry/lifecycle timestamps, scoped uniqueness, indexes, and SQLite foreign keys.
- The migration was applied successfully to the live SQLite database and its resulting schema was verified.
- No Composer or npm package change was made.

### Verification

- Full Pest passed with 437 tests and 2,198 assertions, including canonical routes, pending invitation security, replay/expiry/cancellation, stale-owner protection, role/removal isolation, ownership transfer, token abilities, and API JSON behavior.
- `vendor/bin/pint --dirty --format agent`, full configured PHPStan, Vue TypeScript checking, full ESLint, resource Prettier verification, the four focused frontend tests, and `git diff --check` passed.
- The production build passed after transforming 3,385 modules; only the existing optional `fontaine` optimization notice remains.
- Route inspection confirmed membership reads use `workspaces:read` and every membership mutation uses `workspaces:write` under Sanctum.
- Live desktop QA verified the canonical Overview/Members navigation, accurate 3-member/6-project/27-task metrics, searchable roster, invitation creation/cancellation, and the corrected invitation form reset.
- Test invitations were cancelled and their exact audit rows removed; the live database contains zero `codex.phase2.qa%` invitation rows.
- Live QA produced no current page errors; Boost browser logs contain only historical 2026-07-19 entries.
- Independent security review found the original ownership, invitation, API, async-state, and navigation issues fixed, with no remaining critical or important blocker after visible stale-state errors were added.

### Known Limitations And Next Work

- Email delivery uses the application's configured notification channel; production deliverability configuration remains deployment-specific.
- The Task configuration section intentionally remains a localized Phase 3 hand-off until label, tag, status, and priority CRUD is integrated.
- Phase 3 will harden existing label/tag workspace isolation, add complete management CRUD and API ability coverage, and replace the placeholder configuration panel.

### Git Delivery

- Implementation commit `a562bef` (`feat: add workspace management center`) was pushed successfully to `origin/main`.
- This progress record will be committed as `docs: record workspace management center` and pushed separately.
- The pre-existing Task Index and Herd Upload progress entries remain preserved and excluded from this phase's staged changes.

## Task Index, Board, Filters, And Bulk Workflow

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- `resources/js/pages/tasks/Index.vue` is a 440-line page that reimplements filtering and list presentation while `TaskFilterBar`, `BoardView`, and `BulkActions` remain unused.
- The page exposes selection state but has no task-selection controls, no actual bulk mutations, no view switcher, and no pagination navigation despite receiving a paginated response.
- Visible pending/completed metrics count only the current page, and paginator links do not retain the complete filter/sort state.
- The dormant board hardcodes three legacy statuses, sends mutations to unversioned web routes, supports pointer drag only, and has no mobile horizontal mode or per-card asynchronous state.
- Index inputs are read directly from the request without one bounded filter contract; all supported query parameters are not returned to the client for URL/state synchronization.
- Bulk requests validate workspace IDs, but actions do not re-check the exact submitted set inside the transaction, leaving a soft-delete/TOCTOU partial-operation boundary.

### Delivered Scope And Decisions

- Reduced the task index to a typed coordinator around dedicated filter, list, board, bulk-action, pagination, create-dialog, and detail-drawer components.
- Added one authorized `TodoIndexRequest` contract for bounded filter, sort, direction, page-size, and list/board state; explicit URL state wins, otherwise the task view follows the saved user preference.
- Kept search, filtering, sorting, aggregate metrics, and pagination server-backed. Paginator links retain the full validated query string, and same-page changes use narrow Inertia partial reloads.
- Made filtered metrics cover the complete result set instead of only the current page.
- Drove board columns and status choices from workspace definitions. Native drag/drop is complemented by a keyboard/touch status select, per-card busy state, keyboard-open behavior, and a horizontally scrollable mobile presentation.
- Connected the drawer to the versioned task API and extended the detail resource with conditionally loaded collaboration relations.
- Added accessible per-task/page selection and complete, reopen, archive, and delete bulk controls with destructive confirmation and deterministic processing/error states.
- Re-resolved and locked every submitted task ID inside each bulk transaction. Stale, soft-deleted, duplicate, mixed, or foreign sets now fail before any task mutation.
- Exposed the now-implemented board choice in user preferences with English, Lithuanian, and Russian copy.

### Changed Files

- `app/Actions/BulkDeleteTodos.php`
- `app/Actions/BulkUpdateTodos.php`
- `app/Actions/ResolveWorkspaceTodos.php`
- `app/Http/Controllers/Api/TodoController.php`
- `app/Http/Controllers/TodoIndexController.php`
- `app/Http/Requests/BulkActionRequest.php`
- `app/Http/Requests/TodoIndexRequest.php`
- `app/Http/Resources/TodoResource.php`
- `app/Queries/TodoIndexQuery.php`
- `resources/js/components/task/BoardView.vue`
- `resources/js/components/task/BulkActions.vue`
- `resources/js/components/task/TaskFilterBar.vue`
- `resources/js/components/task/TaskList.vue`
- `resources/js/components/task/TaskPagination.vue`
- `resources/js/pages/settings/Preferences.vue`
- `resources/js/pages/tasks/Index.vue`
- `resources/js/types/api.ts`
- `lang/en/ui.php`
- `lang/lt/ui.php`
- `lang/ru/ui.php`
- `tests/Feature/FrontendDesignTest.php`
- `tests/Feature/Settings/PreferencesTest.php`
- `tests/Feature/TaskIndexWorkflowTest.php`
- `tests/Feature/TodoTest.php`
- `docs/progress.md`

### Migrations And Packages

- No migration was required.
- No Composer or npm dependency changed. The existing DnD packages remain untouched because this phase uses native drag/drop plus an accessible select fallback and dependency removal requires separate approval.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- Focused task/index/settings/design Pest coverage: passed, 146 tests and 713 assertions.
- `php artisan test --compact`: passed, 521 tests and 2,957 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed with zero errors.
- `npm run types:check`: passed.
- `npm run lint:check`: passed.
- `npm run format:check`: passed.
- `npm run test:frontend`: passed.
- `npm run build`: passed; Vite transformed 3,408 modules. The existing optional `fontaine` optimization notice remains non-blocking.
- `git diff --check`: passed.
- Live Herd desktop verification passed for list rendering, board switch, dynamic columns, keyboard drawer opening, page selection, and complete bulk controls with no captured console or page errors.
- Live Herd mobile verification passed at 390 x 844: the filter sheet fits the viewport, the page has no document-level horizontal overflow, and the board scrolls within its own container.
- Recent Boost browser-log output contained only historical entries from July 19; the July 22 verification produced no new browser errors.

### Known Limitations And Next Work

- Board results intentionally use the same bounded server pagination as list results; bulk selection is likewise page-scoped and clears whenever filters or data refresh.
- Browser notification delivery, recurrence catch-up, and the deeper task-detail collaboration lifecycle belong to phases 6-8.

### Git Delivery

- Implementation commit `8d0431b` (`feat: complete task index workflow`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record task index workflow` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index build-repair, and Herd upload progress edits remain preserved and excluded from this phase's staged changes.

## Versioned Import Preview Workflow

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- Selecting a JSON or CSV file executed the import immediately, without a validated review step or explicit confirmation.
- JSON workspace exports had no schema version, so future format changes could not be rejected safely.
- Import execution was already bounded to 1,000 records and transactional, but preview and execution did not share one validation path.

### Delivered Scope And Decisions

- Added schema version 1 to JSON exports and both JSON/CSV import results; legacy JSON without a version remains compatible as version 1, while unsupported versions fail validation before writes.
- Added an authorized workspace-scoped preview endpoint that validates UTF-8, structure, record limits, project references, active status/priority definitions, dates, and nested payload boundaries without persisting records.
- Reused the same parsing and validation paths for preview and transactional execution, while preloading workspace task definitions instead of issuing per-record existence queries.
- Replaced immediate browser imports with an Inertia 3 standalone HTTP preview, localized count/version review, explicit confirmation and cancellation, upload progress, processing/error states, and responsive accessible controls.
- Preserved the legacy CSV execution response key while adding the schema version, avoiding an unnecessary compatibility break.

### Changed Files

- Import/export controller and services under `app/`, plus the versioned preview route in `routes/web.php`.
- `resources/js/pages/settings/Export.vue` and English, Lithuanian, and Russian data-transfer/UI translations.
- Focused data-transfer and frontend-contract coverage in `tests/Feature/DataTransferTest.php` and `tests/Feature/FrontendDesignTest.php`.
- `docs/progress.md`.

### Migrations And Packages

- No migration was required.
- No Composer or npm dependency changed.

### Verification

- Test-first red state confirmed four missing preview-route cases and the missing frontend preview contract before implementation.
- Focused data-transfer, localization, and design coverage passed: 150 tests and 720 assertions.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact`: passed, 567 tests and 3,371 assertions.
- `composer run types:check`: passed with zero PHPStan errors.
- `npm run test:frontend`: passed, 13 tests.
- `npm run types:check`, `npm run lint:check`, and `npm run format:check`: passed.
- `npm run build`: passed after transforming 3,427 modules; only the optional `fontaine` optimization notice and plugin timing advisory were emitted.
- `composer validate --strict --no-check-publish`: passed.
- `php artisan route:list --name=import -vv`: passed and confirmed the authenticated, verified preview and execution routes.
- `git diff --check`: passed.
- Live Herd desktop QA validated a JSON preview response, counts/version review, and zero document-level horizontal overflow; only the preview request was sent, so no domain records were written.
- Live Herd mobile QA at 390 x 844 validated the review, explicit confirmation, cancellation, and zero document-level horizontal overflow with no captured console or page errors.
- Recent Boost browser logs contain only unrelated July 19 security-page entries; this import-preview verification produced no new browser errors.

### Known Limitations And Next Work

- `composer audit --no-interaction` reports eight advisories in the existing locked `guzzlehttp/guzzle` 7.15.1 and `league/commonmark` 2.8.3 packages.
- `npm audit --omit=dev` reports two advisories in the existing production dependency tree: one high-severity `nanoid` advisory and one moderate `postcss` advisory.
- Dependency upgrades require separate approval under the repository contract, so both lockfiles remain unchanged and the audit findings remain open.
- CSV files remain implicitly schema version 1 because CSV has no metadata envelope; a future incompatible CSV shape will require an explicit versioning convention.

### Git Delivery

- Implementation commit `ca3f401` (`feat: add versioned import preview`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record versioned import preview` and pushed to `origin/main`.

## Recurrence, Reminders, And Notifications

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- Recurring tasks are processed by one unbounded command that clones every completed recurring task, advances from completion time, only emits future occurrences, reopens the source task, and has no durable occurrence identity; repeated or concurrent runs can therefore duplicate or skip work.
- Recurrence rules are stored as allowlisted strings, but scheduling semantics are not centralized, monthly and daylight-saving boundaries are untested, and catch-up has no explicit limit.
- Reminder dispatch loads every due row, sends before recording completion, and tracks only `is_sent`; there are no atomic claims, retry leases, failure/cancel states, attempt records, or queue-level duplicate protection.
- Database notifications exist, but their page contract and mutation endpoints require a complete ownership, pagination, unread-count, direct-task-link, and browser-delivery review.
- The live SQLite database currently contains no recurring tasks, four reminders, and five notifications, so schema changes must preserve those rows without relying on production fixtures.

### Delivered Scope And Decisions

- Added a canonical bounded recurrence schedule with legacy interval-one normalization, calendar-based daily/weekly/monthly/yearly advancement, no-overflow month/year behavior, and user-timezone anchoring when a due date is absent.
- Added durable series, sequence, anchor, occurrence-date, and processed identity. Completion now generates the next occurrence immediately while the minute scheduler performs bounded catch-up; the completed source remains immutable and uniqueness makes repeated runs idempotent.
- Copied project/assignee/parent scalar context, labels, tags, and checklist structure into the next occurrence; checklist items reset unchecked while comments, reminders, attachments, spent time, pin/favorite state, and completion state do not carry forward. Generation records an explicit activity event.
- Replaced reminder `is_sent` scanning with pending/processing/delivered/failed/cancelled states, conditional SQLite-safe claims, ten-minute stale leases, three bounded attempts, persisted errors and retry times, channel-preference enforcement, and cancellable audit rows.
- Added one unique database-queue job per reminder. In-app and browser deliveries write a deterministic notification and mark delivery in one transaction; email remains synchronous inside the queued delivery job and records success only after the mail channel accepts it.
- Rebuilt the notification query contract around authenticated-user scoping, validated all/unread filters, stable 20/50-row pagination, global total/unread/read metrics, scoped write actions, and direct task URLs without per-row queries.
- Completed notification pagination, task-link, semantic reminder copy, browser display deduplication, permission-state, and honest open-tab-only browser delivery UX in English, Lithuanian, and Russian.
- Scheduled recurrence and reminder claiming every minute with overlap locks; production still uses the existing SQLite database queue and requires the scheduler plus a worker for the `notifications` queue.

### Changed Files

- Recurrence schedule/configuration/generation actions, completion/create/update/bulk integration, commands, todo model/resource, activity enum, reminder lifecycle actions/job/model/resource/notification/controller/query/request, factories, seeder, scheduler, and notification write actions under `app/`, `database/`, and `routes/`.
- Notification inbox, notification settings, task reminder panel, shared TypeScript models, and EN/LT/RU `ui` and `workspace` catalogs under `resources/js/` and `lang/`.
- Focused recurrence, reminder delivery, notification inbox, API, schema-upgrade, localization, design, application-contract, and query-budget regressions under `tests/Feature/`.
- `docs/progress.md`.

### Migrations And Packages

- Added `2026_07_22_183405_add_occurrence_identity_to_todos_table.php`, `2026_07_22_183406_add_delivery_lifecycle_to_reminders_table.php`, and `2026_07_22_185758_optimize_recurring_todo_processing_index.php`.
- The populated live migration normalized legacy recurrence rules, preserved all four reminder rows, and backfilled one delivered plus three pending lifecycle states before the scheduler smoke completed the three due deliveries.
- No Composer or npm dependency changed.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- Focused recurrence/reminder/notification/API/schema coverage: passed, 24 tests and 153 assertions.
- `php artisan test --compact`: passed, 555 tests and 3,255 assertions.
- `composer run types:check`: passed with zero PHPStan errors.
- `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run test:frontend`: passed; four frontend state/formatter regressions passed.
- `npm run build`: passed after transforming 3,425 modules; only the existing optional `fontaine` optimization notice remains.
- Isolated `migrate:fresh --seed` passed through all 32 migrations and the complete seed set against a temporary SQLite database inside the allowed directory; the temporary file was then moved to Trash.
- Populated down/up migration coverage passed, and the live SQLite schema exposes both recurrence uniqueness constraints plus optimized recurrence, reminder delivery, and claim-lease indexes.
- `EXPLAIN QUERY PLAN` confirmed recurrence processing uses `todos_recurrence_processing_index` without a temporary sort, reminder claims use both delivery/lease indexes, and notification pagination uses `notifications_notifiable_created_index`.
- Live scheduler/queue smoke processed zero recurring tasks, claimed and delivered the three due seeded reminders, left zero pending/failed jobs, and a repeated reminder scan claimed zero duplicates.
- Live Herd desktop QA verified global inbox metrics, server-backed unread filtering, ARIA selection, browser permission/limitation state, zero overflow, and no captured console/page errors.
- Live Herd mobile QA at 390 x 844 verified inbox and notification settings with zero document-level horizontal overflow.
- Recent Boost browser logs contain only historical July 19 entries; the July 22 verification produced no new browser errors.
- `git diff --check`: passed.

### Known Limitations And Next Work

- Browser reminders are intentionally open-tab only: there is no service worker, push subscription, or closed-browser delivery. The settings page states this explicitly.
- Database notification delivery is transactionally idempotent. Email providers cannot guarantee exactly once across a process crash after acceptance but before the local success write; claim tokens, unique jobs, and bounded retries provide duplicate protection and retain failure evidence.
- A production deployment must run `schedule:run` every minute and a queue worker that consumes the `notifications` queue; deployment reconciliation remains a Phase 11 release gate.
- Phase 9 will complete versioned import/export, remaining attachment storage boundaries, and WAL-consistent backup/restore.

### Git Delivery

- Implementation commit `2973c2b` (`feat: complete recurrence reminders notifications`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record recurrence reminders notifications` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index build-repair, and Herd upload progress edits remain preserved and excluded from this phase's staged changes.

## Localization And User Formatting

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- English, Lithuanian, and Russian frontend catalogs have key parity, and most visible page copy already uses semantic keys.
- Locale selection currently occurs inside Inertia prop sharing, after controllers and Form Request validation have already run; non-Inertia and validation responses therefore cannot reliably follow the user preference.
- Preference writes accept arbitrary language, timezone, date/time format, and start-page strings, return JSON to Inertia forms, and the preferences UI omits language, time-format, and start-page controls.
- Newly registered users do not receive a preference record, while the preferences page types the shared nullable preference as always present.
- `useUi` and `useWorkspaceUi` duplicate formatting logic, ignore saved date/time formats, have no relative formatter, and can shift date-only values across extreme timezones.
- The initial Blade document language is localized, but SPA navigation does not synchronize the document language after a preference change.

### Delivered Scope And Decisions

- Added request-time locale middleware before Inertia presentation and before API authentication/errors, resolving supported locale from the authenticated preference, session, browser language, then English fallback.
- Replaced permissive inline preference validation with one authorized Form Request, an atomic update action, bounded language/timezone/date/time/view/start-page values, and an Inertia-compatible redirect response.
- Added preference defaults during Fortify registration and a dedicated settings page controller that supplies safe defaults plus the complete PHP timezone inventory for existing users without a row.
- Completed language, timezone, date format, time format, default-view, and start-page controls with validation states and EN/LT/RU semantic copy.
- Bound a custom Fortify login response and made `/` honor the saved start page without trapping signed-out users behind a stale dashboard intention.
- Centralized locale, timezone, date, time, number, and relative formatting for both frontend translation surfaces. Date-only values are timezone invariant, saved formats are applied, invalid timezone/locale data falls back safely, and runtime defaults are deterministic.
- Added Lithuanian and Russian authentication, password-reset, and common validation translations while retaining English fallback for framework rules not overridden locally.
- Kept the initial Blade `lang` and `dir` attributes and now synchronizes them after every successful Inertia navigation.

### Changed Files

- Locale enum/middleware, preference model/request/action/controllers, Fortify login response/provider binding, registration action, API response service, middleware registration, and home/settings routes under `app/`, `bootstrap/`, and `routes/`.
- `resources/js/lib/formatters.ts` and its Node test, both UI composables, app document synchronization, preference page, shared model types, and the Inertia root view.
- English, Lithuanian, and Russian UI additions plus Lithuanian/Russian auth, password, and validation catalogs.
- Focused localization, preferences, authentication, registration, API contract, and formatter regressions.

### Migrations And Packages

- No migration was required; existing preference columns and database defaults remain compatible.
- No Composer or npm dependency changed.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- Focused localization/preferences/auth/API/dashboard coverage: passed, 41 tests and 308 assertions.
- `php artisan test --compact`: passed, 535 tests and 3,125 assertions.
- `composer run types:check`: passed with zero PHPStan errors.
- `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run test:frontend`: passed; four frontend state/formatter regressions passed.
- `npm run build`: passed after transforming 3,425 modules; only the existing optional `fontaine` optimization notice remains.
- `git diff --check`: passed.
- Route inspection confirmed `SetLocale` executes before API authentication and the version contract.
- Live Herd desktop QA verified all regional/start-page fields, EN to LT navigation, localized page title, immediate document-language synchronization, and restoration to English with no captured console or page errors.
- Live Herd mobile QA at 390 x 844 verified the complete preferences layout with zero document-level horizontal overflow.
- Recent Boost browser logs contain only historical July 19 entries; the July 22 verification produced no new browser errors.

### Known Limitations And Next Work

- All supported locales are left-to-right, so `dir` remains `ltr`; adding an RTL locale requires extending the locale metadata rather than component-specific overrides.
- Common Laravel validation/auth/password messages are localized; uncommon framework validation rules intentionally fall back to English instead of exposing untranslated keys.
- Relative formatting is centralized and tested for later activity/notification integration in Phases 8 and 10.

### Git Delivery

- Implementation commit `139fbb1` (`feat: complete localization preferences`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record localization preferences` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index build-repair, and Herd upload progress edits remain preserved and excluded from this phase's staged changes.

## Task Detail And Collaboration Lifecycle

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- The drawer is a 556-line component and the 800-line task show page separately implement the same edits, checklist drafts, checklist mutations, and comment creation.
- Checklist creation and item toggle exist, but rename/delete/edit/reorder APIs and complete processing/error states do not; web and API controllers still validate several writes inline.
- Comment listing is unbounded and the UI has no author editing, owner/admin moderation, deletion, pagination, or per-comment permission contract.
- The drawer displays labels and tags but cannot manage them, and it does not expose recurrence, reminders, attachments, upload progress, safe download, or deletion controls.
- The detail API returns every comment and exposes private attachment storage paths and disk URLs instead of an authorized download route.

### Delivered Scope And Decisions

- Replaced the duplicated drawer and full-page implementations with one typed `TaskDetailContent` surface composed from overview, taxonomy, checklist, comments, reminders, and attachment panels.
- Kept drafts keyed by task, checklist, and item identifiers, and reloads the authoritative detail payload after relational mutations.
- Completed checklist and checklist-item create, rename/edit, toggle, delete, and exact-set reorder operations through authorized Form Requests, transactional actions, scoped bindings, and versioned API routes.
- Cursor-paginated comments at 10, 20, or 50 rows, bounded the initial task payload to the newest 20, and added author plus workspace owner/admin edit and delete permissions.
- Added task recurrence editing with an explicit allowlist, workspace-scoped label/tag attach and detach, reminder create/delete, and private attachment upload/download/delete controls.
- Removed private storage paths and disk URLs from attachment resources. Downloads now pass through policy authorization and return no-store, sandboxed, attachment-only responses with MIME sniffing disabled.
- Preserved Reka Sheet focus trapping and Escape behavior, and explicitly restores focus to the task row that opened the controlled drawer.
- Added complete EN/LT/RU semantic copy and shared locale-aware dates and numbers across the new panels.

### Changed Files

- Checklist, taxonomy, attachment, reminder, recurrence, comment, and download actions, Form Requests, controllers, resources, policy, query, and versioned routes under `app/` and `routes/api.php`.
- `resources/js/components/task/TaskDetail.vue`, `TaskDetailContent.vue`, and the six focused task-detail panel components.
- `resources/js/pages/tasks/Index.vue`, `resources/js/pages/tasks/Show.vue`, shared API/model types, and English, Lithuanian, and Russian translations.
- `tests/Feature/TaskDetailCollaborationTest.php` and updated task-detail design regressions in `tests/Feature/FrontendDesignTest.php`.

### Migrations And Packages

- No migration was required; the phase uses the existing checklist, comment, reminder, attachment, label, tag, and recurrence schema.
- No Composer or npm dependency changed.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- Focused task-detail, API, localization, and design coverage: passed, 174 tests and 783 assertions.
- `php artisan test --compact`: passed, 527 tests and 3,035 assertions.
- `composer run types:check`: passed with zero PHPStan errors.
- `npm run lint:check`, `npm run format:check`, `npm run test:frontend`, and `npm run types:check`: passed.
- `npm run build`: passed after transforming 3,424 modules; only the existing optional `fontaine` optimization notice remains.
- `git diff --check`: passed.
- Route inspection confirmed the complete nested checklist/item lifecycle plus bounded comments, reminders, and authorized attachment download endpoints under `/api/v1/tasks/{todo}`.
- Live Herd desktop QA verified every panel renders from the shared surface, the controlled drawer closes with Escape, and focus returns to its originating task row with no captured console or page errors.
- Live Herd mobile QA at 390 x 844 verified the full drawer, body scroll lock, and zero document-level horizontal overflow.
- Recent Boost browser logs contain only historical July 19 entries; the July 22 verification produced no new browser errors.

### Known Limitations And Next Work

- Reminder delivery types are now stored and manageable, but the queued dispatch engine, idempotent delivery attempts, catch-up rules, and browser permission UX remain Phase 8 work.
- Recurrence editing intentionally accepts the application's bounded supported rule set; occurrence generation and completion-driven advancement remain Phase 8 work.
- Comment history is cursor-paginated newest-first; real-time collaboration is intentionally outside the current SQLite-first roadmap.

### Git Delivery

- Implementation commit `e50ae53` (`feat: complete task detail collaboration`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record task detail collaboration` and pushed to `origin/main`.
- The pre-existing Task Detail, Task Index build-repair, and Herd upload progress edits remain preserved and excluded from this phase's staged changes.

## Dashboard Command Center Design

### Status

Completed and ready for delivery.

### Before-Phase Baseline

- The dashboard placed today, overdue, and upcoming tasks in equal-height columns, so one long queue forced large empty regions into the other two cards.
- Dashboard task rows were static presentation rather than keyboard-accessible navigation into task detail.
- The weekly activity card showed paired bars and screen-reader summaries, but it had no visible semantic table equivalent and rendered non-zero-height bars for zero values.
- Seeded weeks with no activity displayed fourteen zero labels across a mostly empty plot instead of an intentional empty state.

### Delivered Scope And Decisions

- Reframed the dashboard as a refined daily command center while preserving the shared warm-orange shell and existing workspace-scoped Inertia prop contract.
- Added a generated-Wayfinder header action into the complete task index and reusable task queues whose rows navigate to task detail with Inertia prefetching, project/date context, visible keyboard focus, and 64-pixel live touch targets.
- Replaced the equal-height three-column grid with an asymmetric bento layout: overdue work is dominant, while today and upcoming queues remain compact supporting cards.
- Used a Tailwind CSS 4 container query so the featured overdue queue becomes two columns only when its own card has enough room; tablet and mobile remain single-column.
- Rebuilt weekly activity around localized totals, honest zero-height bars, reduced-motion transitions, a polished no-activity state, and a visible captioned seven-row data table that does not rely on color.
- Added complete English, Lithuanian, and Russian semantic copy for the new focus, queue, navigation, and weekly-summary surfaces.

### Changed Files

- `resources/js/pages/Dashboard.vue`.
- `resources/js/components/dashboard/DashboardTaskQueue.vue`.
- `resources/js/components/dashboard/ProductivityChart.vue`.
- `lang/en/ui.php`, `lang/lt/ui.php`, and `lang/ru/ui.php`.
- `tests/Feature/FrontendDesignTest.php`.
- `docs/plans/2026-08-16-dashboard-command-center-design.md` and `docs/progress.md`.

### Migrations And Packages

- No migration, backend query/controller, route, Composer package, or npm package changed.
- The installed UI/UX skill's optional search database script was absent, so its embedded accessibility, interaction, responsive, contrast, and chart-table rules were applied directly.

### Verification

- Test-first red state confirmed the missing queue/navigation component and weekly table/totals contracts: 118 of 121 design tests passed, with two expected failures and one expected missing-file error.
- Focused dashboard, design, and localization coverage passed: 131 tests and 687 assertions.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact`: passed, 569 tests and 3,396 assertions.
- `composer run types:check`: passed with zero PHPStan errors.
- `npm run test:frontend`: passed, 13 tests.
- `npm run types:check`, `npm run lint:check`, and `npm run format:check`: passed.
- `npm run build`: passed after transforming 3,429 modules; only the optional `fontaine` notice and plugin timing advisory were emitted.
- `composer validate --strict --no-check-publish`: passed.
- `git diff HEAD --check` and staged diff verification: passed.
- Live Herd desktop light-mode QA verified one `h1`, two logical `h2` sections, ten direct task links, generated navigation to `/tasks`, a two-column featured queue, and a seven-row activity table with zero horizontal overflow at 1,440 pixels.
- Live Herd mobile QA at 390 x 844 verified a single-column reading order, 64-pixel task targets, a 322-pixel-contained table, and zero document-level horizontal overflow.
- Live Herd dark-mode QA verified the complete dashboard with dark theme tokens and zero overflow; the prior browser appearance setting was restored afterward.
- Live page automation captured no console or page errors. Recent Boost browser logs contain only unrelated July 19 security-page entries and no errors from this dashboard phase.

### Known Limitations And Next Work

- The dashboard still receives its existing eager page props; deferred aggregate loading remains a separate Phase 10 performance decision rather than a visual-only change.
- The dashboard intentionally shows the bounded ten-item overdue preview while the aggregate badge may be higher; the prominent task-index action provides the complete queue.
- `composer audit --no-interaction` still reports eight advisories in the existing locked Guzzle and CommonMark packages, and `npm audit --omit=dev` still reports one high Nano ID plus one moderate PostCSS advisory. Dependency upgrades require separate approval.
- Remaining Phase 10 work includes projects, workspaces, calendar ranges/views, activity taxonomy/filter/privacy, and workspace-switch client-state cancellation.

### Git Delivery

- Design commit `7f90145` (`docs: design dashboard command center`) was pushed successfully to `origin/main`.
- Implementation commit `4a8eee6` (`feat: enhance dashboard with task queues and productivity insights`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record dashboard command center` and pushed to `origin/main`.

## Calendar Planning Workspace

### Status

Completed, verified, committed, and pushed.

### Before-Phase Baseline

- The calendar eagerly loaded every dated task in the workspace and kept the selected date and month/week/agenda view only in local Vue refs, so navigation state was lost on reload and could not be shared.
- Month rendering, week rendering, agenda grouping, navigation, metrics, and the upcoming rail were implemented in one large page component.
- The seeded August view was mostly empty while overdue work disappeared into an aggregate count and a misleading empty upcoming panel.
- The desktop grid did not have a dedicated mobile reading model or bounded query contract.

### Delivered Scope And Decisions

- Added validated `view` and `date` URL state with Sunday-first month/week ranges and a bounded 31-day agenda range, normalized in the user's timezone.
- Replaced the eager calendar query with workspace-scoped visible-range reads plus a six-item oldest-overdue preview and an independent overdue count.
- Split the Vue page into focused navigator, month, week, agenda, attention-rail, date-helper, and typed-contract modules while retaining Inertia, generated Wayfinder routes, and immutable server props.
- Added responsive mobile date cards, a seven-column desktop month grid, a seven-day week surface, a grouped agenda, and a persistent overdue rail with task prefetching and a generated link to the complete overdue queue.
- Preserved the fixed Warm Precision visual language with restrained orange accents, clear hierarchy, dark-mode tokens, 44-pixel calendar controls, keyboard focus, reduced-motion behavior, and localized EN/LT/RU copy.
- Updated source-contract tests to follow the extracted calendar navigator and made guest-language assertions resilient to the canonical multiline root markup.

### Changed Files

- `app/Http/Requests/CalendarIndexRequest.php`, `app/Http/Controllers/CalendarController.php`, and `app/Queries/CalendarQuery.php`.
- `resources/js/pages/calendar/Index.vue` and the focused modules under `resources/js/components/calendar/`.
- `resources/js/types/workspace-ui.ts` plus English, Lithuanian, and Russian workspace translations.
- Focused calendar backend, design, and localization regressions in `tests/Feature/WorkspacePagesTest.php`, `tests/Feature/FrontendDesignTest.php`, and `tests/Feature/FrontendLocalizationTest.php`.
- Design and implementation records under `docs/plans/`.

### Migrations And Packages

- No migration or schema change was required.
- No Composer or npm dependency was changed by this phase.
- The UI/UX skill's optional design-database search script and the generated Ruflo MCP tools were not exposed, so their embedded design and workflow rules were applied with Laravel Boost and repository/runtime evidence.

### Verification

- Test-first backend and frontend red states confirmed the missing URL metadata, bounded ranges, overdue preview, component extraction, and translation keys before implementation.
- Focused calendar coverage passed: 23 tests and 207 assertions across backend, query budget, design, and localization contracts.
- `vendor/bin/pint --format agent` on phase PHP files and `composer run types:check`: passed with zero PHPStan errors.
- `php artisan test --compact`: passed, 585 tests and 8,090 assertions.
- `npm run test:frontend`: passed, 13 tests. `npm run types:check`, `npm run lint:check`, and `npm run format:check`: passed.
- `npm run build`: passed after transforming 3,462 modules; only the optional `fontaine` notice and plugin timing advisory were emitted.
- Fresh migration and full demo seeding passed against an isolated temporary SQLite database, which was removed after verification.
- `composer validate --strict --no-check-publish`, `composer audit --no-interaction`, `npm audit --omit=dev`, and `git diff --check`: passed; both dependency audits reported zero vulnerabilities.
- Live Herd desktop QA verified month/week/agenda URL state, exact period stepping, reload persistence, generated task navigation with state-preserving back navigation, and zero horizontal overflow at 1,440 pixels.
- Live Herd mobile QA at 390 x 844 verified the dedicated date-card layout, zero document/attention-rail overflow, and no undersized calendar controls. Light and dark visual passes produced no captured console or page errors.
- Recent Boost browser logs contain only unrelated July 19 entries and no errors from this calendar phase.

### Known Limitations And Next Work

- Calendar tasks remain due-date based; time-block scheduling, drag-and-drop rescheduling, and persisted week-start preferences are intentionally outside this phase.
- The attention rail is a bounded six-item preview while its badge shows the complete overdue count; the generated task-index action opens the full filtered queue.
- The separate production-modernization worktree remains in progress and was preserved outside this phase's commits.

### Git Delivery

- Design commit `a792196` (`docs: design calendar planning workspace`) was pushed successfully to `origin/main`.
- Implementation-plan commit `dd8424b` (`docs: plan calendar planning workspace`) was pushed successfully to `origin/main`.
- Implementation commit `74a0ce3` (`feat: build calendar planning workspace`) was pushed successfully to `origin/main`.
- This progress record will be committed separately as `docs: record calendar planning workspace` and pushed to `origin/main`.

## Activity Intelligence Workspace

### Status

Completed. The workspace activity route now provides a responsive, localized intelligence ledger with scoped filters, bounded reads, accessible interaction states, and deterministic manual pagination.

### Baseline And Decisions

- Inspected the existing activity controller, query object, resource, route, Vue page, translations, tests, live SQLite schema, and current workspace authorization flow before implementation.
- Used the existing Laravel 13, Inertia 3, Vue 3, Wayfinder, Tailwind CSS 4, Pest, and SQLite architecture; no packages or alternate application stack were added.
- Kept workspace authorization as the security boundary, limited serialized activity metadata to display-safe values, and used URL-backed filters so filtered views remain shareable.
- Recorded the accepted design and implementation sequence in `docs/plans/2026-08-16-activity-intelligence-design.md` and `docs/plans/2026-08-16-activity-intelligence-implementation.md`.

### Delivered Scope

- Added creation, changes, completion, organization, and automation categories; contributor and 7/30/90-day period filters; validated actor membership; and preserved filter state across paginated requests.
- Added workspace-wide metrics, a 20-row deterministic activity ledger, bounded contributor options, lazy stable Inertia props, and manual `Inertia::scroll` loading without duplicate or reordered rows.
- Added SQLite composite indexes for actor and event filtering, with query-plan coverage for actor and multi-event category paths.
- Rebuilt the page as a responsive intelligence workspace with a desktop filter rail, mobile category strip and filter sheet, grouped timeline, loading/empty/end states, dark-mode-compatible tokens, reduced-motion handling, 44px controls, focus treatment, and concise screen-reader announcements.
- Added complete semantic activity sentences and locale-aware result pluralization for English, Lithuanian, and Russian, including safe fallbacks for system actors and unknown historical events.

### Files And Data

- Added `ActivityCategory`, `ActivityIndexRequest`, the activity filter-index migration, focused activity components and typed helpers, and Activity Intelligence frontend/feature tests.
- Updated `ActivityController`, `ActivityIndexQuery`, `ActivityLogResource`, the activity page and shared types, all three workspace translation files, and query-budget coverage.
- Ran `2026_08_16_091210_add_activity_filter_indexes_to_activity_logs_table` successfully against the local SQLite database. No dependency or configuration changes were required.

### Verification

- Focused backend/page/query gate: 47 tests passed with 481 assertions.
- Standard frontend gate: 16 tests passed, including all three Activity helper tests.
- PHPStan, Vue TypeScript checking, ESLint, Prettier verification, Pint on the phase PHP files, and the Vite production build passed.
- Composer validation and audit passed; the production npm audit reported zero vulnerabilities.
- Live Herd browser checks passed on desktop, mobile, and dark mode with URL-backed filtering, manual pagination states, no horizontal overflow, no recent browser errors, and an accessible mobile filter sheet/status.
- Independent review found no remaining critical, high, or medium issues after accessibility, localization, bounded-query, query-plan, and test-discovery fixes.
- The repository-wide Pest run was also attempted: 606 of 620 tests passed with 8,846 assertions; 13 unrelated Todo/workspace/notification mutation checks returned HTTP 419 and one concurrent run reported a transient SQLite malformed-image error. A read-only `PRAGMA integrity_check` returned `ok`, and all focused Activity/workspace/query tests passed afterward.

### Git Delivery

- Design commit `72c8337` (`docs: design activity intelligence workspace`) was pushed successfully to `origin/main`.
- Implementation-plan commit `8390a00` (`docs: plan activity intelligence workspace`) was pushed successfully to `origin/main`.
- Implementation commit `494459d` (`feat: build activity intelligence workspace`) was pushed successfully to `origin/main`.
- The finalized progress record and observed delivery status are recorded by the follow-up `docs: record activity intelligence workspace` commit and pushed to `origin/main`.

## Production Modernization: Baseline And Canonical Documentation

### Status

In progress. The protected baseline and first canonical documentation pass are complete; dependency and code implementation begins next.

### Repository Protection

- Inspected `main` at `7f90145`, initially synchronized with `origin/main`.
- The staging area and untracked set were empty.
- Preserved one pre-existing unstaged user change in `tests/Feature/FrontendDesignTest.php` (+36/-2) that extends the dashboard command-center contract.
- Confirmed npm from `package-lock.json`, Composer from `composer.lock`, and SQLite as the only relational database.
- Ruflo MCP tools requested by the generated instruction block were not exposed in this session; Laravel Boost and local repository/runtime evidence are being used instead.

### Requirements And Architecture Decisions

- Re-read all governing, active, historical, plan, and tool-specific first-party Markdown; classified generated copied skill bundles and dependency/build output as non-product documentation.
- Established `docs/index.md`, stable functional and non-functional requirement IDs, `docs/compliance-matrix.md`, and the living `docs/implementation-plan.md`.
- Rewrote the active architecture, product/domain/data, authorization/security/API, Inertia/Vue/Tailwind/design/accessibility, localization, test/seeding, SQLite/performance/cache/integration, operations/deployment, review, and limitation contracts.
- Recorded ADR 0001: preserve the repository-mandated Inertia/Vue stack and classify Livewire/Volt/Flux as non-applicable.
- Recorded ADR 0002: run web/development/CI on PHP 8.5 while retaining PHP 8.4 compatibility because official NativePHP Mobile v4 currently embeds PHP 8.4.
- Preserved July audits, roadmaps, and MimoCode plans with historical banners rather than deleting useful history.

### Baseline Evidence

- CLI runtime: Herd PHP 8.4.16; PHP 8.5 is installed. Laravel 13.20.0, Inertia Laravel 3.1.1, Tailwind 4.3.3, Vite 8.1.5, Pest 4.7.5/PHPUnit 12.5.30, Larastan 3.10, NativePHP Mobile 3.3.6.
- `composer validate --strict --no-check-publish`: passed.
- `composer audit`: failed with eight actionable advisories in Guzzle 7.15.1 and CommonMark 2.8.3.
- `npm audit`: failed with four actionable transitive findings in brace-expansion, js-yaml, nanoid, and PostCSS.
- `php artisan test --compact`: 569 tests, 567 passed, 2 failed, 3,387 assertions. Both failures are whitespace/call-signature-brittle source assertions in the preserved dashboard test change; the dashboard implementation is present.
- `composer run types:check`, `composer run lint:check`, `npm run test:frontend` (13 tests), `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `git diff --check`: passed.
- `npm run build`: passed in 9.78 seconds after 3,429 modules. Baseline CSS was 158.29 kB (23.61 kB gzip), app JS 194.24 kB (49.69 kB gzip), and the largest listed button chunk 261.82 kB (89.20 kB gzip).
- SQLite 3.45.2 health, WAL/foreign keys/configured pragmas, quick check, and foreign-key check passed through Laravel. No destructive database command was run.

### Next Pass

- Switch the Herd site/runtime and CI to PHP 8.5.
- Apply targeted stable Composer/npm updates to resolve advisories and modernize Laravel/Inertia/Fortify/Sanctum/Wayfinder/Boost/Pest/Vite/Vue/NativePHP where mutually compatible.
- Repair the preserved dashboard regression test, expand architecture/factory/seeder checks, enable strict Eloquent locally/testing, and execute the full security/data/frontend/browser/final documentation and delivery plan.

### Git Delivery

No commit or push has been created yet for this modernization. All changes remain reviewable in the worktree and the user-owned dashboard test change remains preserved.

## Production Modernization: Implementation And Final Verification

### Status

Repository-controlled implementation is complete and verified. Delivery records below are factual as of this entry; the final documentation/push-status commit remains to be recorded after observing the remote push.

### Runtime, Dependencies, And Architecture

- Isolated the Herd site and CLI to PHP 8.5.0 and updated CI to PHP 8.5. Composer intentionally declares `>=8.4 <8.6` because NativePHP Mobile 4.2 embeds PHP 8.4.24; ADR 0002 and `sys-runtime-001` record this upstream constraint.
- Updated Laravel to 13.25, Inertia Laravel to 3.3.1, Fortify to 1.38, Sanctum to 4.3.3, Wayfinder to 0.1.21, Boost to 2.5.3, NativePHP Mobile to 4.2, Pest to 5.1.1/PHPUnit 13.3, Vite to 8.2.1, Laravel Vite plugin to 3.2, Vue to 3.5.41, Tailwind to 4.3.3, TypeScript to 6.0.3, and their latest mutually compatible direct dependencies.
- Cleared the eight Composer and four npm baseline advisories, regenerated both existing lock files, removed unused dnd-kit/VueUse motion packages, and retained Axios only for NativePHP's adapter contract.
- Enabled strict Eloquent behavior outside production and fixed partial-user/two-factor/avatar/locale projections exposed by it. Removed modified-layer service-locator calls and route action closures, made the Blade bootstrap shell presentation-only, and added repository architecture guards.
- Consolidated repeated app container, panel/feature radius, shadow, and semantic status values into CSS-first Tailwind 4 tokens while preserving the fixed Warm Precision light/dark/system design.

### Data, Factories, Seeders, And Concurrent Work

- Preserved the concurrent dashboard, calendar, and activity-intelligence workflows that landed during this work. The activity implementation was separated into commit `494459d` and includes validated URL filters, workspace-safe contributors, safe resource labels, bounded Inertia infinite scroll, responsive/accessible controls, and two additive rollback-safe activity indexes.
- All 17 models retain factories; 30 meaningful states/helpers and a 55-case infrastructure contract now verify defaults, workflows, locales, roles, foreign keys, idempotent seeding, and direct production rejection by `DatabaseSeeder` and `DemoSeeder`.
- An isolated file-backed SQLite database migrated all 33 migrations, seeded twice without count drift, produced 3 users/1 workspace/25 tasks, and returned an empty `PRAGMA foreign_key_check`. Both temporary databases were moved to Trash; the local application database was never reset.

### Exact Final Verification

- `herd php -v`: PHP 8.5.0. `herd composer validate --strict --no-check-publish`, `herd composer audit --locked --no-interaction`, and `herd composer outdated --direct --strict`: passed; zero advisories and compatible direct packages current.
- PHP syntax sweep: 413 first-party PHP files passed. `vendor/bin/pint --format agent`, `composer lint:check`, and Larastan level 7 (`composer types:check`): passed with zero errors.
- Sequential `herd php artisan test --compact`: passed 627 tests / 8,958 assertions in 27.067 s. Parallel `herd php artisan test --parallel --compact`: passed 627 tests / 8,958 assertions in 10.006 s. The post-guard focused infrastructure suite passed 55 tests / 100 assertions.
- `herd php artisan test --coverage --min=0 --compact`: could not measure coverage and exited with `Code coverage driver not available. Did you install Xdebug or PCOV?`; `test-coverage-001` is the only blocked quality measurement.
- `npm audit --audit-level=low`: zero vulnerabilities. `npm run test:frontend`: 13/13 passed. `npm run types:check`, `npm run lint:check`, and `npm run format:check`: passed.
- `npm run build`: passed after 3,468 modules in 5.15 s; application CSS is 154.98 kB / 24.23 kB gzip plus 2.57 kB / 0.48 kB font CSS, and the app entry is 155.92 kB / 38.05 kB gzip.
- `app:database-health --json`: every SQLite check true. Schedule inventory, config/route/view/optimize cache builds, application boot, and cache clear passed; 303 routes registered.
- NativePHP 4.2 Android Gradle `assembleDebug` passed 40 tasks. `aapt dump badging` confirmed package `com.goleaf.xiaomimimo`, minSdk 31, targetSdk 36; the debug APK SHA-256 is `fd603c167c74bba63863d41c4a0e708ab8e30e55c79f444d5c25c37f532ca035`.
- Final HTTPS smoke returned login 200 in 0.093211 s / 51,046 bytes and passkey discovery 200 in 0.034043 s / 117 bytes.
- Chromium verification covered login/password confirmation, repeated keyboard Inertia navigation, 20 desktop/mobile page checks, activity URL/mobile filtering, reduced motion, dark media, and forced colors with one `h1`, zero horizontal overflow, and zero current console/page errors on every checked page.
- `git diff --check`, repository architecture scans, secret-pattern scan, debug/TODO/FIXME scan, and generated-artifact scan passed.

### Documentation And Requirements

- Re-read and classified first-party Markdown, retained useful audit/plan history with historical banners, and synchronized the canonical index, requirements, non-functional requirements, architecture/data/security/frontend/design/accessibility/localization/testing/seeding/performance/operations/deployment documents, compliance matrix, changelog, review record, and genuine limitations.
- There are 60 active requirement IDs: 56 repository-controlled requirements are implemented and verified once delivery is pushed, one runtime requirement is partial only because NativePHP embeds PHP 8.4, one coverage requirement is externally blocked, and Livewire/Flux are two documented non-applicable decisions under the governing Inertia/Vue contract.

### Git Delivery

- `494459d` — `feat: build activity intelligence workspace`.
- `77ee95f` — `refactor: modernize runtime and quality architecture`.
- `f8c0dde` — `docs: establish production modernization contract`.

## Production Modernization: Delivery Record

### Status

Complete for every repository-controlled requirement. `main` is synchronized with `origin/main` through the implementation and canonical-documentation commits; this delivery record is the final documentation-only commit.

### Observed Git Result

- The concurrent activity commit `494459d` reached `origin/main` from the other active repository workflow before the final modernization push and was preserved in order.
- The first plain `git push origin main` attempt returned no output and did not advance the remote; it was treated as unsuccessful rather than claimed as a push.
- `git push --verbose origin main` then exited 0 and reported `494459d..f8c0dde  main -> main` plus the updated local tracking ref.
- Modernization commits, in order: `494459d` (`feat: build activity intelligence workspace`), `77ee95f` (`refactor: modernize runtime and quality architecture`), and `f8c0dde` (`docs: establish production modernization contract`).
- The concurrent activity workflow then created `998fce1` (`docs: record activity intelligence workspace`) and preserved this modernization delivery record in the same append-only progress file.
- The initial user-owned dashboard contract change and every concurrent dashboard/calendar/activity commit remain present. No destructive Git command, force push, branch replacement, secret, generated build output, or unrelated deletion was used.

### Requirement Totals

- 60 active IDs.
- 56 implemented and verified, including `git-delivery-001` after the observed push.
- 1 partially implemented (`sys-runtime-001`) only because NativePHP Mobile 4.2 embeds PHP 8.4 while web/CI runs PHP 8.5.
- 1 blocked by external dependency (`test-coverage-001`) because Herd has no Xdebug or PCOV driver.
- 2 not applicable with documented reason (`sys-livewire-001`, `sys-flux-001`) because the governing repository contract requires Inertia/Vue and Flux is neither installed nor licensed.

## Project Operations Workspace

### Status

Implemented, independently reviewed, verified, committed, and pushed to `origin/main`. No Project Operations phase work remains open.

### Protected Baseline And Decisions

- Began on `main` with a clean tracked tree at `045f4e1`, one commit ahead of `origin/main`; preserved the pre-existing design `6758a46` and implementation-plan `045f4e1` commits.
- Re-read governing repository and canonical docs, inspected project/task routes, requests, actions, policies, models, schema, factories, seeders, translations, Vue components, tests, and the live SQLite schema before implementation.
- Preserved the repository-mandated Laravel 13/Inertia 3/Vue 3/TypeScript/Tailwind 4/Wayfinder/Pest/SQLite stack. No package, alternate frontend framework, or second database/cache dependency was added.
- Laravel Boost version-specific documentation guided Inertia partial reload, manual scroll, cancellation, request validation, and pagination behavior. Ruflo MCP tools were not exposed in this session, so no claim of Ruflo execution is made.

### Delivered Backend And Data Scope

- Added `ProjectShowRequest`, `ProjectTaskResource`, and the focused `ProjectDetailQuery` boundary for normalized URL search/status/priority/assignee/attention/sort state.
- Scoped every identifier and task read to the authorized routed workspace/project; archived status/priority filters and foreign identifiers fail validation without leaking data.
- Replaced the unbounded project collection with deterministic 25-row manual Inertia pagination, bounded eager loading, complete-project metrics, five-row attention preview, 100-row assignee cap plus selected member, and complete active/referenced-archived priority distribution.
- Deferred scroll evaluation so project-only partial reloads execute no task query and matched scroll items by durable `data.id` so reloads preserve loaded context without duplicates.
- Enforced archived-project task creation server-side, localized built-in row definitions, and serialized the user-preference date boundary so client overdue labels agree with server metrics.
- Added six rollback-safe workspace/project-leading indexes for status, priority, assignee, completion/deadline, due-date ordering, and updated ordering. Real generated SQL and representative filters are checked with SQLite query plans.

### Delivered Interface

- Split project detail into focused header, pulse, filters, queue, and typed pure-helper modules while reusing existing task detail/create and confirmation components.
- Added shareable URL filters, request cancellation, duplicate-safe scroll merging, loading/empty/filtered-empty/end states, optimistic deletion context, accurate attention continuation, localized counts, and action-specific feedback.
- Preserved the fixed Warm Precision design with semantic Tailwind tokens, mobile-first pulse-before-queue reading order, right-rail desktop layout, 44-pixel controls, visible focus, reduced motion, dark mode, non-color state text/icons, and English/Lithuanian/Russian copy.

### Independent Review Resolution

- No critical security, workspace-isolation, rollback, or data-leak issue was found.
- Fixed every important finding: task-definition localization, archived creation, duplicate desktop submit, eager task queries on unrelated partial reloads, incorrect hidden-attention routing, archived-priority count loss, and browser/server timezone divergence.
- Preserved the already-published project-index migration unchanged so previously migrated environments do not encounter duplicate-index failures.
- Added identity-matched scroll merging plus local filter/order/total reconciliation so task mutations neither duplicate rows nor discard loaded pages, and added a stable focus fallback when a reconciled row disappears.
- The final repository review verified every reported critical, high, and medium finding as resolved, including production sort-plan coverage and mobile pulse-before-queue order.

### Exact Verification

- Focused Project Operations/page-query/frontend architecture gate: 51 tests and 376 assertions passed after review fixes.
- Full `php artisan test --compact`: 667 tests / 9,330 assertions passed in 42.685 seconds. Full `php artisan test --compact --parallel`: 667 tests / 9,330 assertions passed in 9.018 seconds.
- `php artisan test --compact --coverage`: exited with `Code coverage driver not available. Did you install Xdebug or PCOV?`; `test-coverage-001` remains the documented environment-only blocker.
- `vendor/bin/pint --dirty --format agent`, Larastan level 7 through `composer run types:check`, Vue type checking, ESLint, and Prettier verification passed.
- `npm run test:frontend`: 23/23 passed. `npm run build`: passed after 3,477 modules in 4.11 seconds; main CSS 159.22 kB / 23.65 kB gzip, app entry 87.25 kB / 22.73 kB gzip, project-detail chunk 90.58 kB / 15.93 kB gzip.
- Isolated file-backed SQLite verification migrated all 34 migrations, seeded twice, produced 3 users and 25 tasks, returned `ok` from `PRAGMA integrity_check`, and returned no foreign-key violations; the explicit temporary database was removed afterward.
- Live Herd desktop QA at 1,440 pixels verified the project route, URL search/reload/clear, one-result filtering, task-detail dialog, semantic heading/action structure, and zero horizontal overflow or captured console/page errors.
- Live Herd mobile QA at 390 by 844 with dark media and reduced motion verified pulse-before-filter/queue DOM order, mobile filter Sheet open/escape close, zero overflow, and no captured console/page errors. Recent Boost logs contain only older unrelated entries and no Project Operations error.

### Git Delivery

- `6758a46` — `docs: design project operations workspace`.
- `045f4e1` — `docs: plan project operations workspace`.
- `a983291` — `feat: enhance project operations with detailed task metrics and filtering capabilities`, pushed to `origin/main` by the concurrent repository process while the shared worktree was being verified.
- `12fae3a` — follow-up removal of an invalid Inertia reload option and corrected sort-plan expectations.
- `aac592c` — `fix: preserve project task identity across reloads`.
- `4ab3a6e` — `fix: reconcile project task mutations`.
- `d392d6c` — `docs: record project operations workspace`.
- `git push origin main` succeeded: `a983291..d392d6c main -> main`. This final status-only documentation update follows in the same delivery.
- No force push, history rewrite, destructive Git command, secret, generated build output, or unrelated deletion was used.

## Notification Command Center

### Status

Completed. The feature implementation, review corrections, and completion record are independently reviewed, verified, and pushed to `origin/main`; no Notification Command Center phase work remains open.

### Protected Baseline And Decisions

- Began on `main` from the completed Project Operations delivery with a clean tracked tree and preserved all prior work.
- Inspected the notification controller, request, query, actions, notification payloads, routes, translations, tests, live SQLite schema/indexes, user timezone preferences, and current Herd page before implementation.
- Preserved Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, SQLite, the existing notification schema, and the Warm Precision design system. No dependency, migration, alternate frontend stack, or new notification lifecycle was added.
- Selected server-owned notification semantics, user-scoped read operations, deterministic bounded pagination, URL-backed status/kind filters, and user-timezone Today/Earlier grouping. Content sniffing, snooze, dismiss, delete, bulk selection, push infrastructure, WebSockets, and Redis remain explicit non-goals.
- Recorded the approved design and implementation sequence in `docs/plans/2026-08-16-notification-command-center-design.md` and `docs/plans/2026-08-16-notification-command-center-implementation.md`.
- Laravel Boost version-specific documentation and the repository's Laravel, Inertia/Vue, Tailwind, Wayfinder, Pest, TDD, frontend-design, accessibility, browser-QA, and code-review skills guided the phase. Ruflo MCP tools were not exposed in this session, so no Ruflo execution is claimed.

### Delivered Backend And Data Scope

- Added a typed inbox resource and a focused query boundary for user-scoped status/kind filtering, global totals, safe legacy payload normalization, authorized task URLs, and deterministic 20/50-row pagination.
- Classified reminders only from notification type or explicit structured payload kind; unknown legacy rows receive a safe General fallback without localized content sniffing.
- Loaded timezone preferences before transformation and emitted an explicit user-local date key plus refreshable `today` prop for stable Today/Earlier grouping across midnight.
- Kept read-one and read-all idempotent and scoped to the authenticated user. Foreign task identifiers never receive actionable URLs, and resource transformation performs no hidden queries.
- Preserved the existing schema and validated the real unread-reminder SQL against `notifications_notifiable_created_index` without a temporary order sort.

### Delivered Interface

- Replaced the monolithic notification page with typed filter, feed, row, and pure-helper components while retaining the shared Warm Precision header, metrics, segmented controls, buttons, spinner, and empty-state surfaces.
- Added canonical URL-backed All/Unread/Read and All/Reminders/Updates filters, bounded page-size controls, request cancellation, narrow Inertia partial visits, localized plural summaries, filter-specific empty states, and deterministic previous/next navigation.
- Added Today/Earlier signal groups, explicit text read state, semantic icon/tone mapping, per-row pending state, safe action navigation, concise live status, and connected-node focus restoration after an unread row disappears.
- Preserved English, Lithuanian, and Russian semantic copy, locale-aware formatting, dark/light modes, reduced motion, visible focus, 44-pixel controls, translated wrapping, and zero 390-pixel overflow.

### Independent Review Resolution

- The first review found no Critical or High issues and identified two Medium edge cases: browser reminders lost their task-specific copy, and partial visits could retain a stale `today` boundary across local midnight.
- Added one shared notification-content resolver for rows and browser delivery, added an across-midnight partial-prop regression, and included `today` in filter, pagination, mark-one, and mark-all refreshes.
- Corrected Lithuanian filtered-empty grammar and added production-shaped unread-reminder query-plan coverage.
- The fix-only review confirmed no remaining Critical, High, or Medium code, security, query, localization, accessibility, or responsive-design findings. The reviewer made no edits.

### Exact Verification

- Focused notification/query/frontend architecture gate: 162 tests and 874 assertions passed after review fixes.
- Full `php artisan test --compact`: 683 tests / 9,539 assertions passed in 38.162 seconds.
- `php artisan test --compact --coverage`: exited with `Code coverage driver not available. Did you install Xdebug or PCOV?`; the existing environment-only coverage blocker remains unchanged.
- `vendor/bin/pint --dirty --format agent`, Larastan through `composer run types:check -- --memory-limit=1G`, Vue type checking, ESLint, Prettier verification, and `git diff --check` passed.
- `npm run test:frontend`: 27/27 passed, including four Notification Command Center behavior cases. `npm run build`: passed after 3,484 modules in 6.42 seconds.
- `composer validate --strict --no-check-publish`, `composer audit --locked --no-interaction`, and `npm audit --audit-level=low` passed with no vulnerability advisories.
- Live Herd QA passed at 1,440-pixel desktop light/dark and 390-by-844 mobile light/dark/reduced-motion states. Status/kind filters produced a shareable URL, clear returned to canonical `/notifications`, both tested widths had zero overflow, controls remained 44 pixels, and no current console or page errors were captured.
- Recent Laravel Boost browser logs contain only older unrelated settings/activity entries; no Notification Command Center error was recorded.

### Known Limitations

- The runtime has no Xdebug or PCOV driver, so a numeric coverage report cannot be generated locally.
- This phase intentionally adds no snooze, dismiss, delete, archive, bulk selection, push service, WebSocket, Redis, dependency, or migration.

### Git Delivery

- `fa0923c` — `docs: design notification command center`.
- `266beec` — `docs: plan notification command center`.
- The first design push attempt failed because GitHub port 443 was temporarily unreachable. The later implementation delivery succeeded, and `c46f801` (`feat: implement notification command center with enhanced filtering and grouping capabilities`) is present on both local `main` and `origin/main` with the design and plan commits.
- `f437e6b` — `fix: close notification command center review`; `git push origin main` succeeded with `c46f801..f437e6b main -> main`.
- `c99068a` — `docs: record notification command center`; `git push origin main` succeeded with `f437e6b..c99068a main -> main`.
- No force push, history rewrite, destructive Git command, secret, generated build output, migration rewrite, or unrelated deletion was used.

## Data Safety Center

### Preflight Status

- Began from the completed Notification Command Center delivery on `main`; the tracked worktree was clean and the branch was two documentation commits ahead of `origin/main` after recording the approved Data Safety Center design and implementation plan.
- Inspected the canonical requirements and design/accessibility/security/operations documents, current settings layout, workspace export/import and operator backup routes/controllers/actions, translations, focused tests, live SQLite scope, and both live Herd settings routes before implementation.
- Live QA established the concrete responsive defect: at 390 pixels the horizontal settings navigation hides the active Export / Import section off-screen. Desktop export/import remained functional with no current console error; an ordinary user correctly received 403 from the operator-only backup route.
- Preserved the separate capability boundaries: `/settings/export` remains scoped to the authorized current workspace, while `/settings/backup` remains limited to the configured, recently password-confirmed application operator. No route, policy, schema, storage protocol, dependency, or database requirement will change.
- Recorded the approved coordinated two-route “Warm Custody Chain” design in `docs/plans/2026-08-16-data-safety-center-design.md` and the test-first execution sequence in `docs/plans/2026-08-16-data-safety-center-implementation.md`.
- Activated the repository workflow, brainstorming, frontend-design, UI/UX, web-design, Inertia/Vue, Tailwind CSS, Wayfinder, Laravel, Pest, and TDD guidance. The UI/UX skill’s optional search script is not present in its installed package, so its embedded guidance and the repository design system are the source of truth. Ruflo ToolSearch/MCP tools are not exposed in this session, so no Ruflo execution is claimed.

### Approved Direction

- Replace the clipped mobile settings strip with an accessible current-section dropdown while retaining the desktop vertical navigation and existing capability-aware item list.
- Give both routes a shared scope/risk language without combining them: workspace transfer uses Select → Review → Confirm and safe authorized download links; application backup uses a verified snapshot inventory and an explicit destructive restore boundary.
- Preserve Warm Precision tokens, English/Lithuanian/Russian semantic messages, locale-aware dates/numbers, dark mode, reduced motion, visible focus, non-color state cues, 44-pixel targets, and ordinary download semantics.

### Delivered Interface And Authorization

- Replaced the clipped small-screen settings strip with a Reka-backed current-section dropdown while preserving the capability-aware desktop navigation. The trigger exposes its action and current destination, every link uses `aria-current`, controls are at least 44 pixels, and long translated labels wrap instead of truncating.
- Added a shared typed scope banner that separates current-workspace transfer from application-database recovery through complete text, icons, and static semantic tones.
- Rebuilt workspace transfer as documented JSON/CSV/Markdown download cards and a Select → Review → Confirm import sequence with file metadata, binary size formatting, progress, validation errors, concise live status, stable cancel/success focus restoration, and matching visual/DOM action order.
- Preserved the real authorization split: every workspace member can export, but the settings controller derives `canImport` from `WorkspacePolicy::update`; non-managers receive a localized explanation and no active upload controls. The configured operator alone receives the application-backup bridge.
- Made the transfer boundary explicit before upload: import recreates projects and core task fields, but does not recreate labels, tags, checklists, assignees, attachments, comments, history, members, or workspace settings. No backend format or storage protocol was changed.
- Rebuilt the operator backup presentation around an application-scope warning, locale-aware verified snapshot inventory, ordinary authorized download links, and explicit destructive restore risk while retaining the existing password confirmation, lock, integrity, rollback, and opaque-ID boundaries.
- Added complete semantic English, Lithuanian, and Russian copy, including locale-aware backup plurals, and preserved the fixed Warm Precision light/dark/system design without a dependency, migration, or alternate frontend stack.

### Correctness And Independent Review

- Added typed pure helpers and discovered direct TypeScript tests for import stages, binary sizes, locale plural categories, and successful standalone HTTP response recognition.
- Confirmed from the installed Inertia 3.6.1 implementation and version-specific documentation that `useHttp` resolves HTTP 422 after populating validation errors. Both preview and execution now guard the resolved response before advancing, announcing success, or clearing the reviewed file.
- Added owner/member page-prop coverage and a separate restricted presentation so read-only members never upload a file only to receive a predictable authorization failure.
- The first independent read-only review found the false-success 422 path, misleading full-transfer copy, missing import capability state, Russian few-form grammar, and mobile label truncation. All were fixed with failing-first regressions.
- The fix-only review confirmed no remaining Critical, High, or Medium findings and made no edits.

### Exact Verification

- Focused transfer/backup/data-safety/design/localization gate: 177 tests / 1,116 assertions passed. Full `php artisan test --parallel --compact`: 693 tests / 9,799 assertions passed.
- `npm run test:frontend`: 31/31 passed, including four Data Safety behavior cases visibly discovered by the standard command.
- `vendor/bin/pint --dirty --format agent`, Larastan through `composer run types:check -- --memory-limit=1G`, Vue type checking, ESLint, Prettier verification, and `git diff --check` passed.
- `npm run build`: passed after 3,489 modules in 4.76 seconds; main CSS 162.18 kB / 23.91 kB gzip, app entry 89.59 kB / 23.23 kB gzip, Export 14.10 kB / 4.28 kB gzip, and Backup 6.52 kB / 2.52 kB gzip.
- `composer validate --strict --no-check-publish`, `composer audit --locked --no-interaction`, and `npm audit --audit-level=low` passed with no vulnerability advisories.
- `php artisan test --compact --coverage` remains externally blocked because the Herd runtime has no Xdebug or PCOV driver; it exits with `Code coverage driver not available`.
- Live Herd QA passed at 1,440-pixel desktop, 390-by-844 mobile dark/reduced-motion, forced colors, and simulated long Lithuanian labels with one heading, 44-pixel navigation/controls, zero horizontal overflow, and no current console/page errors. Real JSON preview/cancel restored focus.
- A final mobile browser regression intercepted execution with a production-shaped HTTP 422: the localized validation error appeared, the selected filename and review preview remained visible, no success toast appeared, page overflow stayed zero, and no page error occurred. Evidence: `data-safety-import-422-mobile-final.png` in the browser QA temporary directory.
- Recent Laravel Boost browser logs contain only older unrelated entries and no Data Safety Center error.

### Known Limitations

- The local runtime cannot generate a numeric coverage report without Xdebug or PCOV.
- The configured-operator backup page was not mutated during live QA; ordinary-user denial returned 403, while the authorized inventory, create/download/restore presentation and policy paths are covered by focused tests.

### Git Delivery

- `7838c16` — `docs: design data safety center`.
- `46c1ce5` — `docs: plan data safety center`.
- `40e625c` — `feat: build data safety center`.
- `c34fe48` — `docs: record data safety center`.
- `git push --verbose origin main` exited 0 and reported `c08fa2b..c34fe48 main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- No force push, history rewrite, destructive Git command, secret, generated build output, migration, dependency, or unrelated deletion was used.

## Workspace Stewardship Console

### Preflight Status

- Began on `main` at `f6d2813` with a clean worktree synchronized with `origin/main` after the completed Task Focus Desk delivery.
- Re-read the repository contract and phase workflow, inspected the workspace routes, controllers, query/resources, portfolio and management Vue surfaces, translations, focused tests, live SQLite workspace graph, and current Herd workspace routes before implementation.
- Live QA established the design target: at 390 by 844 pixels the management overview is 2,496 pixels tall, the horizontal section strip clips the Danger destination, and Task configuration is 5,774 pixels tall with 83 exposed controls. The single-workspace desktop portfolio also leaves most of its operational surface unused.
- Preserved the existing Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Reka UI, Wayfinder, Pest, workspace authorization/actions, and SQLite architecture. No package, schema, route, policy, API contract, or alternate frontend stack is planned.
- The user approved the complete portfolio, management-shell, and taxonomy-studio scope. The accepted Warm Stewardship Ledger design and red-green implementation sequence are recorded under `docs/plans/2026-08-16-workspace-stewardship-console-*.md`.
- Activated the repository workflow, brainstorming, frontend-design, UI/UX, web-design, Inertia/Vue, Tailwind CSS, Wayfinder, Laravel, TypeScript, refactoring, Pest/TDD, browser-QA, and review guidance. The UI/UX skill's optional search script and Ruflo ToolSearch/MCP tools are not exposed, so no execution of those helpers is claimed.

### Approved Direction

- Turn the workspace index into a useful stewardship portfolio with aggregate workspace, member, project, and task totals; client-side search and sort; visible ownership; and clear manage, switch, and secondary-action boundaries.
- Replace the clipped management-section strip with a desktop segmented navigation and a compact mobile section menu that keeps every destination reachable.
- Reframe Task configuration as a progressive taxonomy studio that exposes one of statuses, priorities, labels, or tags at a time, keeps per-category counts visible, and moves row edit/delete actions into accessible menus.

### Delivered Backend And State

- Eager-loaded each workspace owner for the portfolio and retained the existing authorized workspace counts without introducing query work in resources, Vue components, loops, accessors, or policies.
- Made management page payloads section-specific: members and invitations receive only membership data, configuration receives only taxonomy data, and unrelated expensive props remain unevaluated Inertia closures.
- Added typed immutable helpers for portfolio filtering, deterministic sorting, aggregate totals, supported taxonomy selection, and category counts. Existing routes, actions, policies, validation, workspace isolation, and mutation contracts remain unchanged.
- Added focused Pest coverage for portfolio ownership and section-specific payloads while preserving the owner membership contract, plus frontend behavior coverage discovered by the standard test command.

### Delivered Interface And Accessibility

- Built the Warm Stewardship Ledger portfolio with a full-width current-workspace card, four concise metrics, localized result status, content-driven sizing, owner context, and restrained primary/secondary actions.
- Added responsive management navigation with a 52-pixel mobile trigger, semantic current-section state, click-time prefetching, and complete access to overview, members, invitations, task configuration, and danger.
- Added progressive taxonomy switching, scoped search, count-bearing category controls, bounded wrapping for long user-defined names, viewport-safe action menus, and focus restoration after category changes and dialogs.
- Preserved English, Lithuanian, and Russian semantic copy, visible keyboard focus, dark-mode tokens, reduced-motion behavior, destructive confirmation boundaries, and zero 390-pixel horizontal overflow.

### Review Closure

- Changed management navigation prefetch from hover to click so a mutation cannot leave a prefetched destination stale.
- Restored focus to connected portfolio/taxonomy action triggers, then to stable create/search/section fallbacks when the originating element no longer exists.
- Removed long-name truncation from taxonomy rows, bounded mobile action menus, and surfaced the localized workspace owner in the portfolio.
- The first fix-only review found two final medium edges: programmatically focused taxonomy headings lacked a visible ring, and a generated 48-character status/priority key could exceed a narrow card. Both now have explicit regressions, and authenticated 320-pixel QA proved a visible four-pixel orange ring plus long-token wrapping with no page overflow.
- The final independent source-only review confirmed no remaining critical, high, or medium finding and made no edit or SQLite call.

### Final Verification

- Red-green frontend coverage now passes 42/42 cases through `npm run test:frontend`, including four Workspace Stewardship cases discovered by the standard command. The focused workspace section dataset also passed 4 tests / 128 assertions, and the frontend design contract passed 123 tests / 627 assertions during implementation.
- A uniquely named file-backed SQLite suite passed 705/706 tests; its sole failure correctly reported that the runtime-health contract requires `DB_DATABASE=:memory:`. The required explicit in-memory full rerun passed 706 tests / 10,077 assertions in 35.270 seconds.
- `vendor/bin/pint --dirty --format agent`, Larastan, Vue type checking, ESLint, Prettier, and `git diff --check` passed. The final production build passed after 3,499 modules in 4.53 seconds.
- Authenticated Herd QA passed at 1440 by 1,000 and 390 by 844 pixels: the portfolio and management pages had no horizontal overflow; taxonomy switches focused their new headings; action and create/edit dialog closures restored focus; and mobile management navigation exposed every destination with a 52-pixel trigger.
- Laravel Boost browser logs contain no current Workspace Stewardship error. Read-only live verification reports 3 users / 1 workspace / 3 memberships / 6 projects / 25 tasks.

### Data Safety And Recovery

- During concurrent verification, a test process unexpectedly targeted the live SQLite file and cleared the known demo baseline. The emptied file was preserved at `/tmp/xiaomi-mimo-recovery.OJlL9s/emptied-database.sqlite`, then the repository's canonical seeder restored the exact 3-user / 1-workspace / 3-membership / 6-project / 25-task baseline.
- Post-recovery SQLite quick-check and foreign-key checks passed, the restored counts were reverified after the final in-memory suite, and the temporary file-backed test database was moved to Trash. No schema, migration, package, route, policy, or production mutation was part of the feature.

### Known Limitations

- The local runtime still has no Xdebug or PCOV driver, so a numeric coverage report cannot be generated locally.
- This phase deliberately adds no workspace pagination, new workspace roles, taxonomy lifecycle, API response, route, policy, schema, dependency, or alternate storage behavior.

### Git Delivery

- `b18187b` — `docs: design workspace stewardship console`.
- `0781052` — `docs: plan workspace stewardship console`.
- `8de25d8` — `feat: build workspace stewardship console`.
- `git push --verbose origin main` exited 0 and reported `79828ea..8de25d8 main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- Concurrent dependency-refresh commits and their files were preserved and excluded from the Workspace Stewardship implementation commit.
- No force push, history rewrite, destructive Git command, secret, generated build output, schema change, migration, dependency, or unrelated deletion was included.

## Task Focus Desk

### Preflight Status

- Began on `main` at `09f94a9` with a clean tracked worktree synchronized with `origin/main` after the completed Data Safety Center delivery.
- Re-read the repository contract and phase workflow, inspected the task index controller/request/query, Vue coordinator/filter/list/board/pagination components, translations, types, focused tests, and current live Herd task page before implementation.
- Live QA established the concrete usability gap: 25 task rows expose roughly 80 controls, the 390-by-844 mobile document is 3,217 pixels tall, destructive and bulk controls are permanently prominent, task titles truncate, and already-supported focus filters are not discoverable.
- Preserved Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, SQLite, workspace isolation, existing task mutations, list/board views, task detail, and conventional pagination. No package, schema, route, policy, or alternate frontend stack is planned.
- Recorded the approved Progressive Task Focus Desk design in `docs/plans/2026-08-16-task-focus-desk-design.md` and its red-green execution sequence in `docs/plans/2026-08-16-task-focus-desk-implementation.md`.
- Activated the repository workflow, brainstorming, writing-plans, frontend-design, UI/UX, web-design, Inertia/Vue, Tailwind CSS, Wayfinder, Laravel, TypeScript, Pest, TDD, browser-QA, and code-review guidance. The UI/UX skill's documented search helper is absent from its installed directory, so its embedded rules and the repository design system remain authoritative. Ruflo ToolSearch/MCP tools are not exposed in this session, so no Ruflo execution is claimed.

### Approved Direction

- Add URL-backed Overdue, Completed today, Pinned, and Favorites focus toggles using the existing validated query contract.
- Reduce the default task queue to completion, open, and overflow actions; reveal checkboxes/page selection only in explicit selection mode and keep delete behind the existing confirmation boundary.
- Add concise result/filter status, mobile active-filter disclosure, two-line titles, complete translated metadata, 44-pixel targets, visible focus, dark mode, reduced motion, forced-colors support, and English/Lithuanian/Russian semantic copy.

### Delivered Backend And State

- Normalized browser-shaped `true`/`false` focus queries before validation, omitted false flags from canonical state, and retained the existing workspace-scoped query boundary for Overdue, Completed today, Pinned, and Favorites.
- Made Completed today follow the authenticated user's selected timezone by converting the local-day boundary to an indexable UTC timestamp range. A Europe/Vilnius cross-midnight regression proves both list results and filtered metrics use the same boundary.
- Corrected the task paginator contract to the Laravel resource `data`/`links`/`meta` shape and aligned the no-workspace response with it. No schema, migration, route, policy, dependency, or alternate data store was added.
- Added typed pure helpers for active-filter counting, immutable focus toggles, filter clearing, hidden URL-state preservation, locale plural selection, and connected-node focus restoration.

### Delivered Interface

- Added the URL-backed Overdue, Completed today, Pinned, and Favorites focus strip, concise localized result range, locale-correct active-filter count, visible/mobile accessible count disclosure, filtered-empty guidance, and synchronized list/board controls.
- Replaced the permanently exposed selection/delete surface with explicit selection mode, one semantic task-open target, an independent completion action, and a Reka overflow menu whose Delete action retains the application confirmation boundary.
- Preserved hidden assignee/label/tag/date URL filters when visible controls change, exits selection before filter/view/page changes, and drives pagination through Inertia `before`/`start`/`finish` lifecycle state so links and the collection expose the real busy state.
- Preserved row and overflow-trigger focus across detail refresh/close and confirmation cancel, with a connected queue fallback only when the original control disappears.
- Kept the fixed Warm Precision design with 44-pixel targets, two-line mobile titles, wrapping custom status/priority/due badges, non-color status meaning, visible focus, dark mode, reduced motion, forced colors, and full English/Lithuanian/Russian semantic copy.

### Independent Review Resolution

- The independent read-only review found no Critical or High issues and identified six Medium edge cases: user-timezone completion boundaries, hidden URL-filter loss, pagination busy lifecycle, transient menu focus origins, long custom-label wrapping, and singular/plural active-filter grammar.
- Fixed all six with failing-first regressions. A follow-up found that Inertia Link owns native click handling, so pagination was moved to its declared `before`/`start`/`finish` lifecycle callbacks.
- The final fix-only review confirmed no remaining Critical, High, or Medium findings and made no edits. A self-review also preserved the original task-row focus target across refreshes emitted from inside the detail drawer.

### Exact Verification

- Focused task workflow/query/design/localization/detail gate: 191 tests / 1,208 assertions passed. Full `php artisan test --compact --parallel`: 704 tests / 10,003 assertions passed.
- `npm run test:frontend`: 38/38 passed, including seven Task Focus Desk behavior cases visibly discovered by the standard command.
- `vendor/bin/pint --dirty --format agent`, Larastan through `composer run types:check -- --memory-limit=1G`, Vue type checking, ESLint, Prettier verification, and `git diff --check` passed.
- `npm run build` passed after 3,494 modules; main CSS is 162.35 kB / 23.92 kB gzip and the task-index chunk is 39.73 kB / 10.08 kB gzip.
- `composer validate --strict`, `composer audit --no-interaction`, and `npm audit` passed with no vulnerability advisories.
- `php artisan test --compact --coverage` remains externally blocked with `Code coverage driver not available. Did you install Xdebug or PCOV?`; the existing environment-only coverage limitation is unchanged.
- Live Herd QA passed at 1,440 by 1,000 desktop and 390 by 844 mobile. It verified focus URL/reload/clear, a two-result overdue set, hidden-filter preservation, selection entry/exit, list/board switching, task-detail and delete-cancel focus restoration, singular active-filter copy, dark mode, reduced motion, forced colors, 44-pixel controls, and zero horizontal overflow.
- Injected unbroken custom labels wrapped to 54-pixel badges at 390 pixels without internal or page overflow. Both browser pages reported zero current console errors; recent Laravel Boost logs contain only older unrelated entries.

### Known Limitations

- The local runtime has no Xdebug or PCOV driver, so a numeric coverage report cannot be generated locally.
- This phase intentionally adds no schema, dependency, new task lifecycle, date grouping, virtual scrolling, optimistic task mutation, inline editing, or alternate pagination strategy.

### Git Delivery

- `9fa549d` — `docs: design task focus desk`.
- `72cf204` — `docs: plan task focus desk`.
- `d7c7f94` — `feat: build task focus desk`.
- `ed64c0a` — `fix: close task focus desk review`.
- `5c3c719` — `docs: record task focus desk`.
- `git push origin main` exited 0 and reported `09f94a9..5c3c719 main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- No force push, history rewrite, destructive Git command, secret, generated build output, migration, dependency, or unrelated deletion was used.

## Authenticated Landmark Integrity

### Preflight Status

- Began on `main` at `f6d2813` with a clean tracked worktree synchronized with `origin/main` after the completed Task Focus Desk delivery.
- Re-ran the protected factual baseline: PHP 8.5.0, Laravel 13.25.0, 262 first-party routes, 704 Pest tests / 10,003 assertions, 38 frontend tests, Larastan, Pint, Vue type checking, ESLint, Prettier, Composer/npm audits, and the production build all passed.
- Inspected the persistent Inertia layout chain and live authenticated desktop routes. `AppSidebarLayout` renders the shell-owned `main`, while nine authenticated pages and the shared settings layout render another nested `main`; sampled routes exposed two main landmarks with one page heading and no horizontal overflow.
- Preserved Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Wayfinder, Pest, SQLite, existing routes and behavior, and the fixed Warm Precision design. This pass adds no dependency, schema, data, policy, translation, or alternate frontend stack.
- Selected a source-level architecture regression plus semantic wrapper correction because the persistent layout is the stable document-landmark owner. Focused and full automated gates plus authenticated desktop/mobile browser verification remain required before completion.

### Delivered Semantics And Regression Coverage

- Added failing-first frontend design contracts proving the persistent sidebar shell owns the sole authenticated `main` landmark and reusable task-detail content cannot introduce a second page `h1`.
- Replaced nested `main` elements in nine authenticated pages and the shared settings layout with neutral `div` wrappers while preserving every class, route, prop, mutation, and visual behavior.
- Changed the reusable task-detail content title from `h1` to `h2`; dedicated task pages retain their `WorkspacePageHeader` `h1`, while the sheet retains its own accessible title hierarchy.
- The focused `FrontendDesignTest.php` and `ArchitectureContractTest.php` gate passes 129 tests / 5,935 assertions. Pint, Vue type checking, ESLint, Prettier, and the production build pass after the correction.
- Live Herd QA covered dashboard, task index/detail, project index/detail, calendar, activity, notifications, workspace index/detail, and profile settings at 1440x1000 and 390x844. All 22 checks exposed one `main`, one `h1`, zero overflow, and zero console, page, failed-request, or HTTP error.

### Final Verification

- `herd php artisan test --compact`: 706 tests / 10,027 assertions passed in 30.164 seconds. `herd php artisan test --parallel --compact`: the same 706 tests / 10,027 assertions passed in 9.530 seconds.
- `herd php artisan test --coverage --compact` was executed and exited with `Code coverage driver not available. Did you install Xdebug or PCOV?`; `test-coverage-001` remains the existing environment-only blocker.
- Full first-party PHP syntax, `vendor/bin/pint --format agent`, Larastan level 7, Vue type checking, ESLint, Prettier, and 38/38 frontend tests passed.
- Composer validation passed; all direct dependencies are current and compatible with PHP 8.5/Laravel 13. The first final audit call hit a transient Packagist DNS timeout, and the immediate retry passed with no advisories. npm audit passed with zero vulnerabilities.
- The production build passed after 3,494 modules in 4.39 seconds: main CSS 162.35 kB / 23.92 kB gzip, app entry 89.64 kB / 23.25 kB gzip, and task-index chunk 39.73 kB / 10.08 kB gzip.
- An isolated allowed-directory SQLite database migrated all 34 migrations, seeded twice, contained 3 users / 1 workspace / 25 tasks, returned `ok` from `PRAGMA integrity_check`, and returned zero foreign-key violations. Every temporary database directory was sent to Trash.
- Config, route, and view caches built; `app:database-health --json` returned every check true; the schedule listed four intended tasks; 266 first-party routes registered; login returned 200; and the unauthenticated API user endpoint returned the expected 401.
- Laravel Boost browser logs contain only historical entries; the live browser matrix captured no current error during this phase.

### Git Delivery

- `9226b46` — `fix: enforce authenticated landmark integrity`.
- The first `git push --verbose origin main` attempt failed after 79.051 seconds because GitHub port 443 was unreachable; no remote state changed. The immediate non-force retry exited 0 and reported `f6d2813..9226b46 main -> main`, updating the local tracking ref.
- This final status-only documentation commit follows in the same verified delivery. No force push, history rewrite, destructive Git command, secret, generated build output, dependency, migration, or unrelated deletion was used.

## Compatible Dependency Refresh — 2026-08-16

### Preflight Status

- Began on `main` at `0781052`, synchronized with `origin/main`, while preserving 16 unstaged/untracked workspace-stewardship implementation files (1,015 insertions / 359 deletions) that predate and remain outside this dependency phase.
- Re-read the repository contract, canonical requirements/architecture/compliance/plan, latest progress evidence, package manifests, package workflow, and relevant Laravel/Inertia/Vue/Tailwind/Wayfinder/Pest guidance; searched installed-version documentation before changing dependencies.
- The Herd site and `herd php` use PHP 8.5.0; the shell shim currently resolves PHP 8.4.16, which remains inside the intentional `>=8.4 <8.6` Composer envelope required by NativePHP Mobile's embedded PHP 8.4 runtime. Node is 22.21.1, npm is 11.9.0, and Composer is 2.10.1.
- Baseline `composer validate --strict --no-check-publish`, locked Composer audit, and npm audit passed with no advisories or abandoned packages. Every direct Composer dependency is current.
- Authoritative Composer metadata identified one compatible lock update (`phpstan/phpstan` 2.2.5 to 2.2.8). Newer `brick/math`, Guzzle family, Workerman, and PHPUnit releases are incompatible with current upstream Laravel, NativePHP, WebAuthn/UUID, or Pest constraints and will not be forced into the graph.
- npm metadata identified no safe direct update: TypeScript 7.0.2 is outside `typescript-eslint` 8.67.0's `>=4.8.4 <6.1.0` peer range, `@types/node` remains on current major 22 for the Node 22 runtime, and absent Linux/Windows native optional packages are platform-specific rather than missing required installs.
- The dependency refresh will update only compatible Composer/npm lock state, then run the complete package, backend, frontend, database, runtime, and Git gates before delivery.

### Delivered Dependency State

- `composer update --with-all-dependencies --no-interaction` completed with one lock operation: `phpstan/phpstan` 2.2.5 to stable 2.2.8. No manifest constraint, production package, package set, npm version, platform requirement, or application source changed.
- `npm update` found no compatible package-version delta. Its incidental dependency-hoisting-only lock rewrite was not retained; a clean `npm ci` restored the committed graph and passed its zero-vulnerability audit.
- Final direct Composer metadata reports every dependency current. Remaining newer transitive releases are upstream-incompatible: Brick Math 0.19.1 is rejected by UUID/WebAuthn, Guzzle 8 and its Promise/PSR-7 majors by Laravel/Boost/NativePHP, URI Template 2 by Laravel, Workerman 5 by NativePHP, and PHPUnit 13.3.1 by Pest 5.1.1.
- Final npm metadata reports only non-macOS optional native packages, Node 26 types against the intentional Node 22 runtime, and TypeScript 7 outside `typescript-eslint` 8.67.0's declared `<6.1` peer range. No compatible JavaScript update remains.

### Exact Verification

- `composer validate --strict --no-check-publish`, install dry-run, locked audit, direct outdated check, and final audit passed: zero advisories, zero abandoned packages, and every direct dependency current. PHPStan reports installed stable 2.2.8.
- `composer run types:check -- --memory-limit=1G` passed Larastan level 7 with zero errors. The exhaustive PHP 8.5 syntax sweep passed 421 first-party PHP files.
- `herd php artisan test --compact` and `herd php artisan test --parallel --compact` each passed 706 tests / 10,081 assertions in 43.059 seconds and 10.100 seconds respectively. The assertion delta belongs to the preserved concurrent workspace-stewardship changes, not this lock update.
- `npm ci`, npm audit, 42/42 frontend tests, Vue type checking, ESLint, and the production Vite build passed. The build transformed 3,499 modules in 16.17 seconds; main CSS is 162.56 kB / 23.83 kB gzip and the app entry is 89.77 kB / 23.31 kB gzip.
- Dependency-owned Markdown passes Prettier and the combined worktree passes `git diff --check`. Repository-wide `npm run format:check` reports only the unrelated preserved `resources/js/pages/workspaces/Index.vue` work-in-progress file; this dependency phase does not rewrite or stage it.
- An isolated allowlisted SQLite file migrated all 34 migrations, seeded twice, retained 3 users / 1 workspace / 25 tasks, returned `ok` from `PRAGMA integrity_check`, and returned no foreign-key violations. Both temporary directories, including the intentionally rejected outside-allowlist attempt, were moved to Trash.
- PHP 8.4.16 booted the application for NativePHP compatibility; Herd PHP 8.5.0 passed application boot, config/route/view cache builds, 266 first-party route registration, all SQLite health checks, and the four-task schedule. Login returned 200 after HTTPS redirect and the unauthenticated API user contract returned 401.
- The required coverage attempt remains externally blocked with `Code coverage driver not available. Did you install Xdebug or PCOV?`. Laravel Boost browser logs contain historical entries only; no package-version change required a new interactive browser migration.

### Git Delivery

- The coherent dependency slice consists of `composer.lock`, `CHANGELOG.md`, `docs/code-review.md`, `docs/implementation-plan.md`, and this progress entry. The already staged/unstaged workspace-stewardship implementation remains preserved outside the commit.
- `1f771bd` — `chore: refresh compatible dependencies`; the explicit pathspec committed only the five dependency-owned files and preserved every concurrent staged/unstaged workspace-stewardship file.
- `git push --verbose origin main` exited 0 and reported `0781052..1f771bd main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- No force push, history rewrite, destructive Git command, dependency override, generated build output, secret, or unrelated workspace implementation is included.

## npm Lock Graph Normalization — 2026-08-16

### Resolution

- Began on `main` at `a93b1ce`, synchronized with `origin/main`, with a clean worktree after the completed workspace-stewardship delivery.
- Re-ran current Composer and npm registry resolution after the explicit repeated package-upgrade request. `composer update --with-all-dependencies --no-interaction --no-scripts` found no installable Composer version delta, and every direct Composer dependency remains current.
- `npm update --package-lock-only` normalized the npm 11 lock graph for Tailwind's bundled cross-platform WASM dependencies and the two simultaneously required `estree-walker` majors. No manifest constraint or application package version changed.
- The normalized graph is idempotent. A clean `npm ci` now installs 300 packages without the five previously extraneous top-level WASM support packages; `npm ls --depth=0` exits successfully, with only the expected non-macOS optional binaries absent.
- TypeScript 7 remains outside `typescript-eslint` 8.67.0's declared `<6.1` peer range, and Node 26 type declarations remain intentionally inapplicable to the Node 22 runtime. Composer's newer Brick Math, Guzzle family, PHPUnit, and Workerman releases remain blocked by the current UUID/WebAuthn, Laravel/Boost/NativePHP, Pest, and NativePHP constraints respectively.

### Exact Verification

- Composer validation, locked audit, direct outdated inspection, Larastan level 7, and both test modes passed. Pest reported 706 tests / 10,077 assertions in 34.641 seconds sequentially and 12.567 seconds in parallel.
- `npm update --package-lock-only`, a repeated idempotency run, clean `npm ci`, `npm audit --audit-level=low`, `npm ls --depth=0`, 42/42 frontend tests, Vue type checking, ESLint, Prettier, and `git diff --check` passed.
- `npm run build` passed after 3,499 modules in 5.23 seconds; main CSS is 163.94 kB / 24.04 kB gzip and the app entry is 89.77 kB / 23.31 kB gzip.
- Pint passed without changing first-party PHP. No application source, schema, route, translation, data, generated build output, or runtime configuration changed.

### Git Delivery

- `d351a56` — `chore: normalize npm lock graph`; the commit contains only `package-lock.json`.
- `git push --verbose origin main` exited 0 and reported `591f805..d351a56 main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- The concurrent Android APK commits `b017376` and `591f805` advanced the shared `main` worktree during verification; both were preserved, and the dependency commit was applied on top without rewriting history.
- No force push, history rewrite, destructive Git command, dependency override, application source change, generated build output, secret, or unrelated file is included.

## NativePHP Android APK Delivery — 2026-08-16

### Status

Complete. A fresh installable debug APK was generated from current `main`; the ignored NativePHP build output remains local and the delivery record is the only tracked change.

### Preflight And Build Contract

- Began on clean `main` synchronized with `origin/main`. NativePHP Mobile is 4.2.0, the Android build uses Temurin JDK 17 and Android build-tools 36.0.0, and the configured SDK contract remains compile 36 / minimum 31 / target 36.
- Re-read the repository NativePHP contract and prior Android 12 build evidence, searched current NativePHP v4 Android build/package documentation, and verified the embedded Laravel/SQLite model remains intact.
- `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact tests/Feature/NativePhpMobileTest.php` passed 19 tests / 326 assertions. `php artisan native:validate --no-interaction` passed with the expected warning that this web-view application has no `app/NativeComponents` classes.
- `npm run build:android` passed after 3,499 modules in 5.90 seconds and produced the current Android-mode frontend assets.

### Build Diagnosis And Resolution

- The first `php artisan native:run android codex-build-only --build=debug --no-tty --no-interaction` attempt stopped during copied-bundle Composer discovery because NativePHP correctly excludes the host `database/database.sqlite`, while the application safety guard rejects a missing file before the Android runtime exists.
- A minimal copied-bundle reproduction proved that `DB_DATABASE=:memory:` lets Composer package discovery boot safely. The final build used that build-process-only override; no safety guard was weakened, no host database entered the bundle, and NativePHP will create the device database before migrations on first launch.
- The final native command completed Laravel copying, Composer installation, authoritative autoloading, cleanup, a 40.48 MB Laravel archive, and Gradle `assembleDebug`. The intentional non-device ADB serial then reported only the expected install failure because no Android device is attached; APK compilation was already complete.

### Artifact Verification

- Artifact: `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`; 96,770,304 bytes (approximately 92.3 MiB), generated at `2026-08-16T20:44:10+0300`.
- SHA-256: `8ebbaf6c77bf90291b348dbdaf5f04ea5acebe38ee02ed48d1fed3c5af79738f`.
- `aapt dump badging` reports package `com.goleaf.xiaomimimo`, label `Xiaomi Mimo`, version `DEBUG` / code 1, compile SDK 36, minimum SDK 31, and target SDK 36.
- `apksigner verify --verbose --print-certs` passed with Android APK Signature Scheme v2 and the local Android debug certificate. `zipalign -c -v 4` and complete `unzip -t` integrity verification passed.
- The nested Laravel archive contains the latest Workspace Stewardship navigation/taxonomy components and no `database/database.sqlite`. Git confirms the APK is ignored by `/nativephp`, so the 96.8 MB binary is not committed.

### Git Delivery

- `b017376` — `docs: record android apk delivery`.
- `git push --verbose origin main` exited 0 and reported `a93b1ce..b017376 main -> main`, updating the local tracking ref. This final status-only documentation commit follows in the same delivery.
- No dependency, schema, application source, host SQLite data, signing secret, generated binary, force push, history rewrite, or unrelated file is committed.

## Guided Onboarding Design — 2026-08-16

### Preflight And Approved Direction

- Began on clean `main` at `50b86c3`, synchronized with `origin/main`, after the Android APK and npm lock normalization deliveries.
- Re-read the repository contract, canonical requirements/architecture/frontend/design/accessibility/localization/testing/deployment/SQLite documents, latest progress evidence, live `user_preferences` schema, and current registration/login/home/preferences/workspace/project/task boundaries.
- Laravel Boost confirms PHP 8.4 for the active shell, Laravel 13.25.0, Inertia Laravel 3.3.1, Inertia Vue 3.6.1, NativePHP Mobile 4.2.0, Tailwind CSS 4.3.3, Vue 3.5.41, Pest 5.1.1, and SQLite. No dependency change is planned.
- The approved product choice is a dedicated eight-step Inertia journey for newly registered users after verification, with server-side resume, whole-journey skip, role/invitation adaptation, manual replay for existing users, a dismissible post-completion Dashboard checklist, EN/LT/RU copy, and the fixed Warm Precision system.
- Existing users will be backfilled as completed. Manual replay will preserve the completed gate so abandoning a replay cannot trap an existing user in automatic redirects.
- The feature will compose existing preference/workspace/project/task actions, re-authorize every persisted identifier, bound every option collection, make creation retries idempotent, preserve the embedded NativePHP Laravel/SQLite model, and introduce no analytics or remote onboarding service.
- The approved design is recorded in `docs/plans/2026-08-16-guided-onboarding-design.md`. The next required step is a writing-plans TDD implementation plan before application code changes.

### Design Verification And Delivery Status

- Prettier verification for the two Markdown files and `git diff --check` passed. Complete and staged diff inspection, the coherent semantic documentation commit, and `git push origin main` follow in this design slice.
- No source, schema, route, runtime data, package, generated asset, or APK changes belong to this design slice.

### Design Git Delivery And Implementation Planning

- `096125c` — `docs: design guided onboarding`; `git push --verbose origin main` exited 0 and reported `50b86c3..096125c main -> main`.
- Activated the required writing-plans workflow after design approval and mapped persistence, verified-browser entry, scoped/idempotent domain composition, real week-start behavior, responsive Inertia UI, Dashboard continuation, review, canonical docs, and Android APK delivery into atomic RED/GREEN tasks.
- The implementation plan is recorded in `docs/plans/2026-08-16-guided-onboarding-implementation.md`. This planning slice changes no source, schema, route, package, runtime data, generated asset, or APK.
- The writing-plans self-review confirmed complete spec-to-task coverage and consistent step/state/route/type names. Placeholder scanning, Prettier verification for the plan/progress files, and `git diff --check` passed; staged diff inspection, the plan commit, and push follow in this slice.

## Guided Onboarding Implementation — 2026-08-16

### Phase 1 Preflight: Persistence And Registration

- Began implementation on clean `main` at `c4c72ec`, synchronized with `origin/main`, after the approved design and implementation-plan commits.
- Activated executing-plans, test-driven-development, Pest, Laravel best practices, Fortify, database design, Inertia Vue, Wayfinder, Tailwind CSS, frontend-design, and UI/UX Pro Max for their relevant boundaries. The repository's explicit `main` requirement overrides executing-plans' generic worktree preference.
- UI/UX Pro Max's bundled instructions reference a `scripts/search.py` asset that is absent from both installed skill copies. Its documented accessibility, touch, responsive, motion, contrast, and Vue guidance remains applicable; no dependency or generated design-system folder will be introduced as a workaround.
- Reconfirmed the live SQLite `user_preferences` schema and searched installed-version Laravel 13/Fortify migration, registration, middleware, Inertia validation, and Wayfinder documentation before code changes.
- Phase 1 will begin with an observed failing Pest contract, then add one new populated-safe reversible SQLite migration, stable onboarding step/value semantics, complete-by-default factory state, and explicitly pending real Fortify registrations.
- No existing migration will be rewritten. The phase will preserve the existing Fortify redirect, SQLite-only runtime, PHP 8.4 NativePHP compatibility, and all unrelated work.

### Phase 1 Delivered: Persistence And Registration

- Added a stable eight-case `OnboardingStep` enum with deterministic positions and rounded progress, then extended `user_preferences` with Sunday/Monday week start plus versioned, resumable onboarding state and lifecycle timestamps.
- Added the bounded `onboarding_operations` idempotency ledger. The populated-data migration marks every existing preference row complete and dismisses its checklist while assigning a unique run UUID; ordinary factories mirror that legacy-safe state.
- Kept only real Fortify registrations pending by composing the existing canonical preference defaults with explicit onboarding defaults. The existing successful registration redirect remains unchanged.
- The observed RED gate reported one failure and four errors for the absent schema, enum, model predicate, and pending factory state. The completed focused gate passes 21 tests / 125 assertions across onboarding persistence, registration, settings preferences, and database schema integrity.
- A clean in-memory SQLite migration ran all 35 migrations successfully, including the populated-safe onboarding migration. Pint passed after ordering one import, and Larastan passed with zero errors after the enum helper was aligned with Eloquent's runtime cast contract.
- No route, controller, frontend, translation, dependency, host data, or generated asset changed in this phase. Verified-browser entry routing is the next isolated TDD slice.

### Phase 2 Preflight: Verified Browser Entry

- Phase 1 was committed as `f2c32b4` and pushed to `origin/main` (`c4c72ec..f2c32b4`) before this slice began.
- Re-read the current web/settings route topology, Fortify verification tests, middleware bootstrap, Inertia shared-prop/layout boundary, controller/query conventions, and architecture contracts.
- Laravel Boost's installed-version documentation confirms `auth` then `verified` middleware ordering, the `verification.notice` redirect contract, ordered route-group middleware, and Inertia endpoint assertions for real page components and nested props.
- This phase will add one narrow middleware alias, onboarding routes outside the gate, a thin index controller/query boundary, a real minimal Inertia page, and failing-first coverage for pending, unverified, complete, legacy, recovery, and API behavior.
- The automatic gate remains browser-only and applies only after authentication and verification. It will not change API responses, verification/password recovery endpoints, or the completed-user start-page contract.

### Phase 2 Delivered: Verified Browser Entry

- Added the missing `MustVerifyEmail` contract to the existing user model, making the already configured Laravel/Fortify `verified` middleware enforce the intended boundary instead of silently passing unverified users.
- Registered a narrow `onboarding.complete` alias and applied it after authentication/verification to normal application and settings routes. The real `GET /onboarding` endpoint remains outside that gate, while API and Fortify recovery/verification routes are unchanged.
- Added a thin onboarding controller plus focused query object. Explicitly pending users receive a localized Inertia `onboarding/Index` page with truthful step, position, total, percentage, replay, and resumable-state props; completed and legacy users return to their saved/default start page without a loop.
- Tightened `requiresOnboarding()` so a row is pending only when it has an explicit onboarding run UUID and no completion/skip timestamp. This preserves the approved legacy behavior for rows created without onboarding state while Fortify registrations remain explicitly pending.
- Added the first semantic English, Lithuanian, and Russian onboarding catalog entries and a single-root Warm Precision placeholder page; the complete responsive journey composition will replace this bounded shell in the dedicated frontend phase.
- The observed RED gate ran 16 tests with 9 passing, two behavior failures, and five missing-route errors. The final expanded entry/auth/localization/persistence/architecture gate passes 48 tests / 5,671 assertions.
- `php artisan route:list -vv` confirms onboarding has `auth` then `verified` without the completion gate, while Dashboard has `auth`, `verified`, then `EnsureOnboardingIsComplete`; settings retain their existing verification behavior and add only the completion gate.
- Pint, Larastan, Vue type checking, ESLint, Prettier, and the Vite production build pass. The build transformed 3,501 modules and generated the real onboarding chunk in the ignored build output.
- This phase intentionally publishes only the usable onboarding GET boundary. Progress and domain mutation routes will be added atomically with their validated actions in the next lifecycle and scoped-composition slices, avoiding public placeholder endpoints.

### Phase 3 Preflight: Resumable Lifecycle

- Phase 2 was committed as `21c8cde` and pushed to `origin/main` (`f2c32b4..21c8cde`) before lifecycle mutations began.
- Re-read the approved state machine, controller/query boundary, preference casts/defaults, start-page routing, session behavior, and existing Form Request/action conventions.
- Laravel Boost's installed-version documentation confirms backed-enum validation through `Rule::enum`, Form Request authorization, translated validation failures, transactional rollback, and explicit session `put`/`forget` semantics.
- This slice will begin with failing tests for legal adjacency, invalid jumps, reload resume, required skip/completion, manual replay, replay abandonment, missing-preference restart, and non-forcing version behavior.
- Lifecycle actions will lock only the user's preference row inside short SQLite transactions. They will not create or delete workspace data, and replay will preserve prior completion/skip/checklist facts so it can never re-enable the automatic gate.

### Phase 3 Delivered: Resumable Lifecycle

- Added an authorized `AdvanceOnboardingRequest` with backed-enum validation plus focused transactional actions for adjacent progress, completion, required skip, and restart/replay.
- Progress locks the preference row, permits only one step forward or backward, records the first start time, rejects jumps with semantic localized errors, and persists the cursor for reload resume.
- Required Skip moves to Results, clears only resumable draft state, records a skip timestamp, and follows the saved start page. Completion is accepted only from Results, clears skip/draft state, records completion, and follows the same canonical start-page map.
- Restart creates a new run UUID/current content version and resets only cursor/draft/start time. Existing completion, skip, and checklist facts remain intact; a legacy user without preferences receives a completed baseline before replay, while a genuinely pending user remains required and cannot bypass the gate with a forged replay session flag.
- Added usable auth/verified routes for progress, skip, complete, and restart alongside the existing index route. Completed users may mutate only during an active replay; leaving replay for normal application navigation is always allowed.
- The observed lifecycle RED filter ran eight tests with one pass and seven missing-route errors. The complete lifecycle suite passes 11 tests / 97 assertions, including adjacent back/forward movement, invalid jumps/values, resume, required skip, guarded completion, completed/skipped replay, legacy restart, version behavior, and mutation authorization.
- The expanded persistence/entry/lifecycle/auth/localization/architecture gate passes 57 tests / 5,802 assertions. Pint, Larastan, Vue type checking, ESLint, and `php artisan route:list --path=onboarding` pass; the route list now reports the five implemented lifecycle endpoints.
- No workspace, project, task, taxonomy, package, migration, host data, or generated asset changed in this phase. Scoped domain composition and idempotent create/select routes are the next TDD slice.

### Phase 4 Preflight: Scoped Guided Creation

- Phase 3 was committed as `f6634d3` and pushed to `origin/main` (`21c8cde..f6634d3`) before domain composition began.
- Re-read the workspace/project/task models, policies, factories, schema/indexes, task-definition triggers, current create requests/controllers/actions, preference update action, and page-query budget harness.
- Laravel Boost's installed-version documentation confirms scoped `Rule::exists` constraints, UUID validation, Form Request safe data, Eloquent relationship scopes, unique constraints, and retryable transactions on SQLite.
- This phase will start with failing select/create, cross-workspace, archived/stale identifier, custom definition/assignee, bounded-options, query-budget, and duplicate-submission tests.
- Existing `CreateWorkspace`, `CreateProject`, `CreateTodo`, `UpdateUserPreferences`, policies, task-definition transition logic, and workspace membership boundaries remain authoritative. Onboarding actions will coordinate them and persist only scoped IDs/safe draft state.

### Phase 4 Delivered: Scoped Guided Creation

- Added four authorized, scoped Form Requests and thin controller endpoints for personal preferences plus choose-or-create workspace, project, and task steps. Successful workspace selection also updates the existing `current_workspace_id` session contract.
- Composed the existing `UpdateUserPreferences`, `CreateWorkspace`, `CreateProject`, and `CreateTodo` actions through focused onboarding coordinators. Workspace-owned identifiers are re-resolved from the authenticated user's memberships, then the selected active project/task aggregate; foreign, mixed, archived, deleted, and unavailable identifiers fail validation without changing saved progress.
- Added a retryable exactly-once operation ledger coordinator. Its unique user/version/run/step boundary reuses the first successful result even when a browser retry supplies a different request UUID, while a new replay run may intentionally create another entity.
- Expanded the onboarding query into a normalized page contract with canonical preferences, selected IDs only, stale-selection recovery, and bounded options for workspaces, projects, tasks, members, statuses, and priorities. Every option collection is capped at 100, keeps a valid selected record inside the cap, selects only required columns, and performs no query in a row loop.
- The observed RED filter ran 20 tests with 11 passing, two failures, and seven missing-route errors. The completed persistence/entry/workflow/query-budget filter passes 35 tests / 301 assertions, including invited-member reuse, all three exactly-once paths, foreign and archived rejection, taxonomy/assignee isolation, stale-state rewind, selected inclusion, and data-volume-independent query counts.
- The prescribed domain regression gate passes 68 tests / 408 assertions across onboarding, query budgets, workspaces, projects, and tasks. Pint passes, Larastan passes with zero errors, and `git diff --check` is clean.
- No package, migration, generated frontend asset, host SQLite data, or APK changed in this phase. Real week-start behavior is the next isolated TDD slice.

### Phase 5 Preflight: Canonical Week Start

- Phase 4 was committed as `cf5dbce` and pushed to `origin/main` (`f6634d3..cf5dbce`) before this preference/calendar slice began.
- Re-read the canonical Settings preference request/controller/page, calendar request/controller/state/grid, shared localized weekday catalog, model TypeScript contract, and existing Settings/Workspace page tests.
- Laravel Boost's installed-version documentation reconfirms Form Request allowlists, automatic Inertia validation redirects/errors, typed page props, and Carbon-backed Laravel date handling.
- The observed RED gate runs 26 tests with 21 passing and five intended failures for missing persistence/allowlisting, missing `calendar.week_start`, wrong Monday-first range, and absent immutable weekday rotation.
- This phase will make the saved preference the sole server boundary source and rotate a local copied label array in Vue; it will not mutate shared translations, infer behavior from locale, or change calendar URL semantics.

### Phase 5 Delivered: Canonical Week Start

- Added `week_start` to the canonical Settings validation, Inertia form payload, typed preference model, and English/Lithuanian/Russian semantic catalog. Sunday and Monday are the only accepted values; legacy/missing values retain the existing Sunday default.
- `CalendarIndexRequest` now derives week and expanded-month boundaries from the saved preference and returns the normalized value in `CalendarState`. For Sunday 2026-08-16, Monday-first produces `2026-08-10..2026-08-16`, while Sunday-first produces `2026-08-16..2026-08-22`.
- The desktop month grid copies the seven shared localized labels and rotates only that local array for Monday-first users. It does not mutate the reactive translation catalog; mobile and week views inherit the server's chronological range.
- The initial focused gate produced the intended five failures among 26 tests. The completed Settings/Workspace gate passes 26 tests / 299 assertions, and the localization/design gate passes 133 tests / 796 assertions.
- Pint, Larastan, Vue type checking, ESLint, Prettier, all 42 discovered frontend tests, and the Vite production build pass. The build transformed 3,501 modules in 5.05 seconds.
- No dependency, schema, route name, host data, generated tracked asset, or APK changed in this phase. Typed onboarding interaction contracts are the next TDD slice.

### Phase 6 Delivered: Typed Onboarding Interaction Contract

- Phase 5 was committed as `18d432b` and pushed to `origin/main` (`cf5dbce..18d432b`) before the frontend contract slice began.
- Re-read the existing frontend behavior tests, Inertia page/layout conventions, generated onboarding Wayfinder actions, shared Warm Precision design assertions, and localization parity harness. Laravel Boost reconfirmed typed Inertia props/forms, automatic validation-error lifecycle, direct Wayfinder form/visit support, and Tailwind reduced-motion variants.
- Added a discovered top-level Node test and a pure TypeScript onboarding contract: stable eight-step order, honest rounded progress, typed page/options/payload shapes, immutable safe draft merging with dependent-ID invalidation, recovery codes, and locale-aware plural categories.
- The frontend RED gate first failed because the helper module was absent. The completed standard `npm run test:frontend` gate now visibly includes all three onboarding cases and passes 45 tests; Vue type checking and ESLint also pass.
- Added failing-first Pest contracts for the upcoming Vue slice covering component boundaries, one page heading, AppLayout ownership, generated Wayfinder actions, form processing/errors, connected focus handoff, semantic progress/status/validation, 44-pixel targets, mobile safe-area actions, reduced motion, forced colors, Warm Precision composition, semantic copy, locale key/placeholder parity, and representative plural forms.
- The source/localization contract correctly remains RED against the intentionally minimal placeholder page: 139 tests ran with 133 passing, two assertion failures, and four missing-component errors. The next slice implements those exact components and catalogs before the Pest contract is committed.

### Phase 7 Delivered: Warm Guided Route Interface

- Phase 6 was committed as `f2852cf` and pushed to `origin/main` (`18d432b..f2852cf`) before the interface slice began.
- Replaced the bounded placeholder with a complete eight-step AppLayout experience. A responsive Warm Guided Route progress rail appears on wide screens; mobile uses a sticky compact status plus a safe-area-aware action bar. Both derive from the same stable step order and expose text, icon, current-step, percentage, and concise save status semantics.
- Added focused Welcome, Preferences, Workspace, Project, Task, Product Map, Safety, and Results components plus shared shell/panel boundaries. The page owns immutable prop synchronization, one Inertia form coordinator, generated Wayfinder actions, stable per-draft request UUIDs, superseded-visit cancellation, server error retention, validation-summary focus, and connected heading focus after successful transitions.
- Personal setup includes a live locale/timezone/date/time preview. Workspace, project, and task steps provide keyboard-accessible choose/create modes, bounded scoped options, complete validation, and compact previews. Task defaults are now explicitly marked by the server, and canonical taxonomy names are localized before serialization.
- Added complete semantic English, Lithuanian, and Russian onboarding catalogs with identical keys/placeholders, full-message action/status/recovery copy, six product-map descriptions, role-aware safety guidance, and locale-aware result count forms. Successful language selection immediately updates the session locale for the redirected step.
- The previously RED source/localization contract now passes. The expanded onboarding/frontend/localization/design gate passes 197 tests / 1,713 assertions; the standard frontend gate passes 45 tests. Pint, Larastan, Vue type checking, ESLint, Prettier, and the Vite production build pass; the build transformed 3,523 modules in 5.46 seconds.
- No dependency, schema, host data, generated tracked asset, or APK changed in this phase. Dashboard continuation and Settings replay controls are the next TDD slice; live browser QA follows once those entry points exist.

### Phase 8 Preflight: Honest Continuation And Replay

- Phase 7 was committed as `98fc6d4` and pushed to `origin/main` (`f2852cf..98fc6d4`) before the continuation slice began.
- Re-read the Dashboard query/controller/page, current-workspace boundary, workspace and database-backup policies, passkey/two-factor model contract, Settings preference page, onboarding lifecycle actions, and generated route topology.
- Laravel Boost's installed-version documentation reconfirms user-scoped Gate checks, relationship `exists` queries, typed Inertia props, generated Wayfinder actions, and partial form lifecycle behavior.
- This phase starts with failing coverage for legacy-hidden versus newly completed/skipped visibility, current-workspace scoping, capability-based item omission, real team/security completion facts, per-user dismissal, and non-destructive Settings replay.
- The Dashboard checklist will report only verifiable facts and permitted destinations. It will appear before task queues on mobile, avoid fabricated percentages, remain dismissible with a 44-pixel control, and preserve all workspace, project, and task entities when replay starts.

### Phase 8 Delivered: Honest Continuation And Replay

- Added a bounded `OnboardingChecklistQuery` that is visible only for newly completed or skipped journeys whose checklist was not dismissed. Legacy backfilled users stay hidden, stale session workspace IDs are resolved through the existing membership-scoped current-workspace query, and growth from one to 120 teammates does not change the Dashboard query count.
- The checklist exposes only policy-authorized actions. Invite appears only for workspace owners/admins, export only when an accessible workspace exists, application backup only for the configured operator, and account/notification actions remain available without inventing completion percentages.
- Team completion now means another real current-workspace membership exists. Security completion means confirmed two-factor authentication or a persisted passkey exists. Both states pair visible text with icons; advisory items deliberately make no completion claim.
- Added an idempotent authenticated-user dismissal action and generated DELETE Wayfinder endpoint. Dismissal changes only that user's checklist timestamp, preserves lifecycle/domain data, returns to the originating page, and removes the card on the next Dashboard response.
- Added a responsive Warm Precision continuation card before Dashboard focus queues with 44-pixel dismissal, keyboard focus rings, reduced-motion handling, concise live/error status, EN/LT/RU semantic copy, and direct Wayfinder destinations. Settings Preferences now offers a clearly non-destructive, action-scoped replay card for verified users.
- The failing-first contract produced four missing-prop failures and two missing route/component errors. The completed focused gate passes 77 tests / 1,220 assertions across onboarding, Dashboard, Settings, query budgets, and localization; all 45 standard frontend tests pass.
- Pint, Larastan, Vue type checking, ESLint, Prettier, and the production Vite build pass. The build transformed 3,525 modules in 6.56 seconds. Live browser review and Android delivery are the next isolated phase.

### Phase 9 Preflight: Review, Runtime QA, Canonical Evidence, And Android

- Phase 8 was committed as `ba10d35` and pushed to `origin/main` (`98fc6d4..ba10d35`) before the final delivery slice began.
- Re-read the approved review matrix, NativePHP build contract, current migration state, canonical documentation responsibility map, and persistent-browser QA workflow. The live host database still requires only the additive onboarding migration; no destructive refresh will be used.
- This phase will run the focused and complete automated gates, verify a separate file-backed SQLite migrate/seed/rollback cycle, apply the additive host migration, exercise the real Herd journey at desktop and mobile sizes, synchronize stable requirements/evidence, then build and inspect a fresh debug APK.
- Android completion requires independent manifest, signature, alignment, ZIP-integrity, nested Laravel-archive, and database-exclusion evidence. A missing attached device may stop installation after compilation but will not be reported as an APK build failure when the artifact itself passes every offline check.

### Phase 9 Runtime Review And Hardening Delivered

- The complete sequential and parallel Pest gates pass 760 tests / 11,347 assertions after the final runtime fixes. The 64-test focused onboarding, Dashboard, and workspace-membership gate passes 713 assertions; Pint, Larastan, all 45 frontend tests, Vue type checking, ESLint, Prettier, and the production build also pass.
- The coverage gate is the only unavailable automated signal: PHPUnit reports that neither Xdebug nor PCOV is installed. Dependency audits remain clean, and the production build transforms 3,525 modules without an application error.
- A separate file-backed SQLite database completed all 35 migrations, two idempotent seed runs, integrity and foreign-key checks, the onboarding migration rollback, and reapplication. The live Herd database received only the pending additive onboarding migration; no refresh or destructive data operation was used.
- Persistent-browser QA completed the required new-user creation journey, logout/login resume, EN to LT and LT to RU locale changes, whole-journey completion, Dashboard continuation, Settings replay/exit, checklist dismissal, required skip, and real pre-registration invitation acceptance. Created workspace, project, and task records remained intact through replay and exit.
- Review found and fixed two real integration defects: the onboarding page had nested the globally provided application layout, duplicating the complete page, and invitation acceptance was incorrectly placed behind the completed-onboarding gate. The page now owns one semantic main/heading, and authenticated verified invitees may accept the signed, throttled invitation before onboarding discovers their membership.
- Mobile QA at 390 by 844 pixels now uses a stable two-column Back/Continue footer with a separate Skip action, 44-pixel journey targets, no horizontal overflow, safe-area spacing, connected heading focus, and a focused validation summary after keyboard submission. Dark mode, reduced motion, and forced-colors rendering preserve visible content and progress semantics.
- Recent browser inspection produced no onboarding JavaScript error. Historical browser-log entries predate this feature; the only current application error entries are the intentionally logged private-failure branch exercised by `DatabaseBackupTest`.
- Canonical requirements/evidence synchronization and a fresh independently inspected Android APK remain the final two tasks in this phase.

### Phase 9 Canonical Evidence And Android Delivered

- Added stable `sys-onboarding-001` traceability across requirements, architecture, domain/data model, frontend/design, accessibility, localization, testing, deployment, compliance, the living implementation plan, and changelog. The records describe the actual versioned state machine, completion gate, signed-invitation exception, scoped/idempotent composition, replay behavior, responsive interaction contract, and release path.
- `npm run build:android` passed after 3,525 modules in 6.49 seconds. `DB_DATABASE=:memory: php artisan native:run android codex-build-only --build=debug --no-tty --no-interaction` completed source copying, production Composer installation, optimized autoloading, cleanup, a 42.54 MB Laravel archive, and Gradle debug assembly. Its final ADB message is only the expected missing-device install result for the intentionally nonexistent serial `codex-build-only`; the command exited 0 after producing the APK.
- Artifact: `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`; 98,833,792 bytes (94.26 MiB), generated at `2026-08-16T23:33:15+0300`. SHA-256: `4123ab9a359e17d3e1efe55d03fcc23bd56ee60f55ee477219878c690f37c7c8`.
- Android build-tools 36.0.0 independently report package `com.goleaf.xiaomimimo`, label `Xiaomi Mimo`, version `DEBUG` / code 1, compile SDK 36, minimum SDK 31, and target SDK 36. APK Signature Scheme v2 verifies with the local Android debug certificate; `zipalign -c 4`, outer `unzip -t`, and nested Laravel-bundle `unzip -t` pass.
- The 44,607,103-byte `assets/laravel_bundle.zip` contains the onboarding migration, enum, middleware, requests, controller, actions, queries, all eight Vue step components, Dashboard checklist, generated Wayfinder routes/actions, EN/LT/RU onboarding catalogs, current signed-invitation route order, and the built onboarding page entry. It contains zero `database/database.sqlite` entries.
- Git confirms `/nativephp` ignores the 98.8 MB generated artifact, so neither the APK nor generated native output will be committed. The debug signature is intentionally development-only; store distribution still requires protected production signing and a real-device release pass.
- This delivery introduces no dependency change, production signing secret, host SQLite data, destructive migration, generated tracked asset, or unrelated source change. Final NativePHP-focused verification, documentation formatting, staged review, semantic documentation commit, and push follow.

### Phase 9 Git Delivery

- `4943a2b` — `fix: harden guided onboarding runtime`; `git push --verbose origin main` exited 0 and reported `ba10d35..4943a2b main -> main`. This commit contains the single-layout/safe-area/mobile-action fixes, pre-gate signed invitation route, focused regressions, and runtime evidence.
- `9ed2896` — `docs: record guided onboarding delivery`; `git push --verbose origin main` exited 0 and reported `4943a2b..9ed2896 main -> main`. This commit contains only canonical documentation, requirement/compliance traceability, final verification facts, and the ignored APK record.
- The complete guided-onboarding implementation was delivered on `main` as the approved design/plan plus persistence, entry gate, lifecycle, scoped domain composition, preference integration, typed interaction contract, Warm Guided Route, Dashboard continuation/replay, runtime hardening, and canonical evidence commits. Every application/code commit was pushed before the next phase began.
- The generated debug APK remains local at the documented ignored path and is directly usable for sideload testing on Android 12 or newer. A device was not attached during this build, so real-device installation and production-store signing remain explicit release activities rather than falsely reported checks.
- No force push, history rewrite, pull request, replacement branch, destructive Git command, dependency change, signing secret, host database, generated native project, or unrelated user file is included. This final status-only documentation commit follows and will be pushed normally.

## Full Dependency, Installation, And Emulator Verification — 2026-08-17

### Preflight Status

- Began on clean `main` at `b0eea37`, synchronized with `origin/main`, with no staged, unstaged, or untracked user files.
- Re-read the repository contract, mandatory canonical documents, the latest dependency/APK/onboarding evidence, and the applicable Git, dependency-migration, Laravel, NativePHP Mobile 4, and verification guidance before changing package state. The repository has no `.ai/rules` directory.
- The Laravel Boost `search-docs` MCP tool is not exposed in this session; current NativePHP Mobile 4 documentation was checked through the available versioned documentation provider. It confirms `composer update` followed by `php artisan native:install --force`, Android-mode frontend assets, and `native:run android <device-id>` for a local device or emulator.
- The clean host uses Herd PHP 8.5.8, Composer 2.10.1, Node 22.23.1, npm 10.9.8, OpenJDK 17.0.16, Android emulator 36.1.9, and an existing `BTChat_API_34` arm64 AVD. The shell's inherited `JAVA_HOME` points to a stale directory, so Android SDK/Gradle commands will use the detected JDK 17 home explicitly without rewriting user shell configuration.
- PHP vendor dependencies are currently absent. A full Composer dry run resolves one compatible lock update, `laravel/mcp` 0.9.3 to 0.9.4; npm registry inspection resolves compatible direct updates for `tailwind-merge` 3.2.0 to 3.6.0, `tw-animate-css` 1.2.5 to 1.4.0, and `vue-sonner` 2.0.0 to 2.0.9. No framework major, package set, platform constraint, or package manager change is planned.
- This phase will update both lock graphs, install the complete PHP/JavaScript system, force-regenerate the ignored NativePHP native project, run the applicable complete quality and isolated-data gates, build and inspect a fresh debug APK, boot the existing emulator, install the artifact, exercise the application, and inspect current Android logs before committing and pushing only tracked phase-owned files.

### Dependency, Runtime, And Android Delivery

- `composer update --with-all-dependencies` installed the complete 164-package graph and upgraded only transitive `laravel/mcp` 0.9.3 to 0.9.4. `composer outdated --direct --strict`, validation, locked audit, install-time platform requirements, and Larastan all pass; no compatible direct Composer update or advisory remains.
- npm 11.9.0 regenerated the existing single lock and installed exactly 300 packages. Only transitive `es-toolkit` changed from 1.50.0 to 1.51.0; compatible direct dependencies were already current. `npm audit` reports zero vulnerabilities. `npm outdated` contains only non-macOS optional binaries, Node 26 types against the intentional Node 22 line, and TypeScript 7 outside `typescript-eslint` 8.67's `<6.1` peer range.
- The local system is installed and bootable on Herd PHP 8.5.8, Composer 2.10.1, Node 22.23.1, npm 11.9.0, SQLite, and JDK 17. Android emulator tools upgraded from 36.1.9 to 37.1.11 and platform-tools/ADB from 36.0.0 to 37.0.1; build-tools 37.0.0 and the existing Android 14 arm64 `BTChat_API_34` AVD remain installed.
- A missing ignored SQLite file initially caused Composer package discovery to fail, and an incomplete generated `.env` then caused missing-key/assets failures. The repository's documented setup sequence was applied without touching tracked secrets: full `.env.example`, generated local app key, canonical `com.goleaf.xiaomimimo` app ID, ignored SQLite creation, 35 migrations, Composer/npm installation, and production assets.
- Fresh `.env.example` verification exposed string/null and string/integer drift in NativePHP configuration. Deep-link values now normalize empty environment strings to `null`, and HTTP/WebSocket ports normalize to integers; the existing NativePHP configuration contract supplies the regression coverage.
- Clean emulator boot exposed that NativePHP stores its SQLite file in the app-private `persisted_data/database` sibling of `persisted_data/storage`, while the web allowlist pointed into the immutable Laravel bundle. A failing subprocess regression now proves the platform-derived allowed directory, which remains independent of `DB_DATABASE`; explicit operator configuration still takes precedence.
- The next clean boot exposed NativePHP 4.2's package-view mismatch: bundle cleanup removes package resource views while first-boot `view:cache` retained their hints. `NativePhpRuntimeService` now activates only on Android/iOS, removes only physically unavailable namespace paths, and leaves web view discovery unchanged. Its failing-first regression and the complete NativePHP suite pass.
- Registration review found the bundled local `MAIL_MAILER=log` writing a signed email-verification URL into app-private logs. NativePHP now strips every `MAIL_*` bundle value and forces Laravel's non-logging `array` mailer on-device; a focused regression and the final emulator log prove no verification URL or mail payload is recorded. Web deployments continue using their configured mail transport.
- `native:install --force` regenerated the ignored Android/iOS platform projects and refreshed `nativephp.lock` from embedded PHP 8.4.24 to PHP 8.5.9 with ICU disabled. NativePHP's official v4 documentation still describes PHP 8.4, so the conservative Composer `>=8.4 <8.6` envelope remains until documentation and runtime metadata converge.

### Final Quality Evidence

- Pint passes, Larastan reports zero errors, and the final complete Pest suite passes 762 tests / 11,359 assertions sequentially in 20.851 seconds and in an isolated parallel run in 5.322 seconds. An intervening parallel run overlapped `npm run build` and reported three `ViteException` failures while the build replaced its font manifest/assets; the production build passed, and the immediate isolated rerun passed without a code change. The focused NativePHP suite passes 20 tests / 328 assertions, and the SQLite runtime suite passes 6 tests / 51 assertions.
- All 45 discovered frontend tests, Vue type checking, ESLint, Prettier verification, npm audit, and the Vite production build pass. The final web and Android builds each transform 3,539 modules; Vite emits only its upstream/plugin-originated deprecated `server.hmr.*` configuration warning.
- Composer validation, locked audit, direct-outdated, and non-dev platform checks pass. `php artisan native:validate` passes with the expected no-NativeComponents warning. Application boot reports Laravel 13.25.0 and PHP 8.5.8; config, route, and view caches build, all SQLite health checks pass, 272 first-party routes register, and the four scheduled tasks are present before caches are cleared.
- An isolated file-backed database applied all 35 migrations, completed two deterministic seed runs, passed application SQLite health, `integrity_check`, and zero foreign-key output, then moved to Trash. The real local database was not refreshed or destructively modified.

### APK And Emulator Evidence

- `npm run build:android` and Gradle 8.14.5 `assembleDebug` pass all 40 tasks. The final `native:run` ADB message is the expected missing-device result for the deliberately nonexistent build-only serial `codex-build-only`; compilation completed before that message and the command exited 0.
- Final artifact: `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`, 114,877,574 bytes, generated at `2026-08-17T10:04:03+0300`, SHA-256 `0314225dbf0105f2a9a7b1f16dc94e960e255d08ee43a92e5906ba9a46ceba6f`.
- Android build-tools independently report package `com.goleaf.xiaomimimo`, label `Xiaomi Mimo`, version `DEBUG` / code 1, compile SDK 36, minimum SDK 31, and target SDK 36. APK Signature Scheme v2 verifies with the Android debug certificate; ZIP alignment, outer APK integrity, nested Laravel archive integrity, required runtime/config/build entries, and host-database exclusion pass. No `MAIL_*` line remains in the bundled environment.
- The visible Android 14 arm64 emulator booted as `emulator-5554`. A clean uninstall/install launched `com.nativephp.mobile.ui.MainActivity` cold, migrated the app-private database, rendered the localized-accessible login screen, accepted disposable registration input, and navigated to email verification. The device database reports integrity `ok`, one user, and all 35 migrations; the app process remains foreground/resumed.
- Final Laravel/logcat inspection contains no Laravel error/warning, fatal exception, request-handling error, SQLite containment/PDO failure, signed verification URL, or mail payload. Non-fatal emulator/framework diagnostics remain limited to software-OpenGL swap behavior, an optional bridge `startWatching` lookup, and IME frame-tracker timeouts; none interrupted the tested flow.
- The ignored debug APK and generated `/nativephp` project are not commit candidates. Debug signing is not production signing, emulator coverage is not real-hardware coverage, percentage coverage remains externally blocked by missing Xdebug/PCOV, and the non-logging on-device mailer validates the verification boundary rather than external email delivery. A production mobile verification channel requires a separately approved secure integration.
- The phase-owned tracked diff passed final formatting, staged-diff, and scope review, then committed as `a414112` (`chore: refresh dependencies and Android runtime`) and pushed normally from `b0eea37` to `origin/main` without rewriting history.

### Git Delivery

- Commit `a414112` contains only the dependency/runtime locks, NativePHP runtime corrections, focused regressions, and synchronized canonical evidence owned by this phase; generated native projects, the debug APK, local environment, host database, credentials, and unrelated files remain untracked or ignored.
- `git push --verbose origin main` completed successfully as `b0eea37..a414112`. This status-only documentation commit records the completed delivery and will be pushed normally.
- The Android 14 emulator remains running with package `com.goleaf.xiaomimimo` installed from the final verified APK and `com.nativephp.mobile.ui.MainActivity` resumed in the foreground.

## Sutelio Full Brand Rename Design — 2026-08-17

### Preflight Status

- Began on clean `main` at `81f2393`, synchronized with `origin/main`, with no staged, unstaged, or untracked user files. The repository has no `.ai/rules` directory.
- Re-read the repository contract, mandatory canonical documents, and the latest dependency/runtime/emulator delivery evidence before recording the design.
- Inspected the current Laravel-derived web mark, Xiaomi Mimo wordmark, browser icons, NativePHP mobile icon/splash output, active configuration names, package identity, localization copy, canonical documentation, tests, and generated-native boundary.

### Approved Design

- The canonical product name is `Sutelio` and the user requested a complete rename rather than a display-name-only alias.
- The selected visual direction is the third clean-S concept: a cobalt `#123C8B` rounded tile, orange `#FF6038` circle, and solid ivory `#FFF8E9` capital `S`. The `S` has no stripe, cut, line, gradient, or internal color change. The `Sutelio` wordmark is always one color and never isolates the final `o` in orange.
- The full scope includes web/native assets, application/configuration/copy/documentation names, Android application ID `com.goleaf.sutelio`, matching iOS identity, package/build/test/artifact names, and an in-place GitHub/local Herd repository rename after verified source delivery.
- The Android/iOS package-identity change is explicitly a clean-install boundary. The Sutelio package cannot update or directly read the old Xiaomi Mimo app-private database; no automatic cross-package data migration is claimed.
- Historical append-only evidence retains the old name where changing it would falsify prior commands, package IDs, artifact hashes, or delivery results.

### Verification And Delivery Status

- Design specification: `docs/plans/2026-08-17-sutelio-full-brand-rename-design.md`.
- No application source, dependency, migration, local environment, database, generated native project, emulator package, remote repository, or Herd site has been changed in this design-only phase.
- Specification self-review resolved exact geometry, iOS bundle identity, deep-link scheme, Composer/npm metadata, backup/test prefixes, and APK publication naming. `git diff --check` and focused Prettier verification pass.
- The design-only diff contains this specification and append-only progress evidence. Commit `2c0aadd` (`docs: record Sutelio full rename design`) was pushed normally to `origin/main` as `81f2393..2c0aadd`.
- This status-only progress synchronization follows on `main`; application implementation remains intentionally paused until the user confirms the specification.

## Sutelio Full Brand Rename Implementation Planning — 2026-08-17

### Planning Preflight

- The user confirmed the full Sutelio rename specification. Planning began on clean `main` at `a37c9ce`, synchronized with `origin/main`; no application implementation has started.
- Re-read the `writing-plans`, Laravel configuration/testing/style, Inertia Vue, and Pest guidance. Laravel Boost `search-docs` remains unavailable in this session, so installed NativePHP Mobile 4.2 source and bundled versioned guidance were inspected directly.
- Mapped every active `Xiaomi Mimo`, `xiaomi-mimo`, `xiaomimimo`, and `com.goleaf.xiaomimimo` reference outside append-only/historical evidence, plus the remaining Laravel starter-kit metadata and logo consumers.
- Confirmed NativePHP consumes opaque 1024-pixel `public/icon.png` and 1080-by-1920 `public/splash*.png` sources, while its current generator does not emit an Android 13 monochrome launcher resource. The implementation plan will keep generated `/nativephp` output ignored and apply source-controlled adaptive/monochrome overrides after native installation.
- Confirmed the existing Android 14 arm64 `BTChat_API_34` AVD remains available but is not currently running; Android SDK tools live under `/Users/andrejprus/Library/Android/sdk`, and JDK 17 resolves at `/opt/homebrew/Cellar/openjdk@17/17.0.16/libexec/openjdk.jdk/Contents/Home`.
- Confirmed the in-place external rename command is `gh repo rename -R goleaf/xiaomi-mimo sutelio --yes`; the current remote remains `https://github.com/goleaf/xiaomi-mimo.git` until verified source delivery succeeds.

### Implementation Plan Delivered

- Created `docs/superpowers/plans/2026-08-17-sutelio-full-brand-rename.md` with locked file ownership and twelve ordered TDD/delivery tasks covering deterministic master vectors/raster exports, web consumers, EN/LT/RU copy, Laravel/package/NativePHP identity, adaptive/monochrome/splash generation, current documentation, complete quality gates, real-browser review, APK inspection, Android 14 emulator verification, and final in-place GitHub/Herd rename.
- The plan pins the official OFL Instrument Sans source at Google Fonts commit `2fc9491da70cf3e1bafd3c3da49244c5a496e9d5` and validates SHA-256 `b24f1812584816958afcf22e22d08e44318c5e51651e25d2438efdde389b33b1`; no new package dependency is introduced.
- Fresh-eye review maps every approved specification section to an executable task. Placeholder scan, name/type consistency review, embedded Node ESM syntax checks, `git diff --check`, and focused Prettier verification pass.
- This planning phase changes documentation only. No application source, dependency lock, local environment, database, generated native project, APK, emulator package, GitHub repository name, remote URL, project directory, or Herd site has been changed.
- Commit `4df9fd0` (`docs: add Sutelio rename implementation plan`) contains only the implementation plan and append-only planning evidence; `git push --verbose origin main` completed successfully as `a37c9ce..4df9fd0`.
- This status-only progress synchronization follows on `main` and will be pushed normally. Application implementation remains paused until the execution approach is selected.

## Sutelio Full Brand Rename Implementation — 2026-08-17

### Implementation Preflight

- Began from clean synchronized `main` after approval of `docs/plans/2026-08-17-sutelio-full-brand-rename-design.md` and `docs/superpowers/plans/2026-08-17-sutelio-full-brand-rename.md`.
- Scope is identity-only: deterministic brand assets, active product/package names, NativePHP generation, APK/emulator proof, canonical evidence, and final in-place GitHub/Herd rename.
- No schema, domain behavior, authorization, authentication, email-verification, or dependency-version change is authorized in this phase.

### Red Brand Contract Evidence

- `php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendDesignTest.php` intentionally remains red: 126 tests ran with 123 passing, 1 failed assertion, 2 missing-asset errors, and 640 assertions. The three non-passing cases are the expected absent Sutelio master/raster assets and the existing inline Laravel logo in `AppLogoIcon.vue`.
- No brand asset or production component was created or changed in this contract-only task, and the red state was not committed.

### Visual Identity Slice

- The Task 1 red contract above is green after Tasks 2-4: the pinned Instrument Sans input and unchanged OFL license produce the approved clean-S vectors, opaque web/mobile rasters, Android adaptive/monochrome resources, and matching favicon through one deterministic generator. It builds in a temporary directory, validates the font checksum, output set, SVG/XML structure, favicon parity, and raster dimensions before publishing, then always removes the temporary directory; the native installer fails clearly when either source or generated destination is absent.
- The web shell now exposes the locked cobalt, deep-cobalt, orange, and ivory tokens without changing existing primary, status, chart, or color-mode behavior. The canonical full-color mark replaces the inline Laravel SVG everywhere; the visible `Sutelio` wordmark stays one color, sidebar/auth/header consumers remove redundant orange wrappers, decorative copies remain hidden from assistive technology, the auth home link has an explicit `Sutelio` accessible name and brand-cobalt focus ring, and the repository/documentation links target `goleaf/sutelio`.
- Browser presentation now falls back to the `Sutelio` title, uses `#FF6038` for Inertia progress, and declares the application name, Apple web-app title, and `#123C8B` theme color while preserving the existing favicon/touch links, theme initializer, fonts, Vite entries, and Inertia head. The narrow `.gitattributes` rule reports `resources/brand/fonts/OFL.txt: whitespace: -trailing-space`; its required checksum remains unchanged and no repository-wide whitespace suppression was added.
- `php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendDesignTest.php` passes 130 tests / 754 assertions in 2.094 seconds. `php artisan test --compact tests/Feature/NativePhpMobileTest.php --filter='brand sources include'` passes 1 test / 7 assertions in 0.203 seconds. Vue type checking, ESLint, Prettier verification, and `vendor/bin/pint --dirty --format agent` pass, and `git diff --check` is clean.
- `npm run build` passes with Vite 8.2.1: 3,539 modules transformed and the production bundle completed in 3.07 seconds. The main application CSS is 160.07 kB / 23.57 kB gzip and the main application JavaScript is 89.50 kB / 23.10 kB gzip; the only additional output is the informational plugin-timing report.
- This presentation-only slice adds zero database queries, migrations, schema changes, dependency additions, or dependency-version changes. Package metadata only names the existing npm root `sutelio` and exposes the two brand scripts. The visual work will be committed locally as `feat: add Sutelio brand identity`; no push is authorized before the technical identity work in Task 6.

## Email Verification Removal — 2026-08-17

### Implementation Preflight

- The product owner explicitly removed email verification from the entire system and approved deletion of the persisted `users.email_verified_at` values; rollback can recreate only an empty nullable compatibility column, not discarded historical timestamps.
- Began from clean synchronized `main` at `60249b2` after reading the canonical documentation, installed Laravel 13.25 / Fortify 1.38 / Wayfinder 0.1 guidance, the live SQLite `users` schema, registered routes, and every active application reference.
- Scope is a compulsory removal: disable Fortify verification routes and notifications, remove verified middleware and onboarding/profile gates, delete the verification page and copy, remove the model/factory/shared-prop contract, drop the column through a populated-safe migration, and guard the product decision with failing-first Pest coverage plus a durable Boost rule.
- Password reset, workspace invitation delivery, reminder email, passkeys, two-factor authentication, password confirmation, workspace authorization, invitation signature/token/email matching, and onboarding completion remain in scope and must not be weakened.
- No dependency addition or version change is planned. Focused RED/GREEN tests precede implementation; full backend, frontend, schema, audit, build, documentation, Git, commit, and push gates remain pending.

### Implementation Delivered

- Disabled Fortify email verification and removed the user `MustVerifyEmail` contract, verification view registration, all `verified` middleware, verification routes, verification notification behavior, the verification Vue page, profile warning/resend UI, shared verification props/types, translation copy, and the obsolete factory state.
- Registration now proceeds directly to authenticated onboarding. The onboarding completion gate remains authoritative for application pages, while the invitation-acceptance route still requires authentication, a signed URL, throttling, digest-token validation, and matching the authenticated user's email.
- Added `2026_08_17_103526_drop_email_verified_at_from_users_table.php`. The populated local SQLite database applied it successfully, and Laravel Boost schema inspection confirms the column is absent. Its tested rollback recreates only an empty nullable compatibility column, as explicitly approved.
- Added negative product-contract coverage for Fortify configuration, routes, the user interface contract, registration notifications, shared props, schema, onboarding middleware, active source tokens, the deleted page, and migration reversibility. The shared `.ai/rules` contract records that email verification must not be reintroduced.
- Updated active requirements, architecture, data, security, integration, testing, seeding, deployment, current-state, compliance, changelog, and review evidence. Historical audits, completed plans, and earlier dated progress remain unchanged as evidence rather than being rewritten.
- This slice adds zero Eloquent queries and no dependency changes. Password reset, passkeys, two-factor authentication, password confirmation, invitation delivery, reminder delivery, workspace authorization, and all other domain behavior remain in place.

### Verification Evidence

- The focused RED gate failed in the expected five product-contract locations before implementation. The final focused backend/frontend gate passes 226 tests / 12,002 assertions; the migration rollback/reapplication test passes independently.
- `vendor/bin/pint --dirty --format agent` passes. Larastan reports zero errors. The full Pest suite passes 763 tests / 16,291 assertions sequentially in 21.167 seconds and passes the same 763 tests / 16,291 assertions in isolated parallel mode in 7.734 seconds.
- All 45 frontend tests pass. Vue type checking, ESLint, repository resource Prettier verification, npm audit with zero vulnerabilities, and the Vite 8.2.1 production build pass; the web build transforms 3,536 modules and emits no `VerifyEmail` chunk.
- Composer strict validation, locked audit, direct dependency currency, and non-development platform checks pass. A fresh in-memory SQLite database applies all 36 migrations and completes the deterministic demo seed. The migrated local database passes `app:database-health`; config, route, and Blade caches build, 276 first-party routes register, and the four scheduled tasks remain present before caches are cleared.
- Wayfinder routes/actions were regenerated after route removal. `verification.notice`, `verification.send`, and `verification.verify` are absent, and the generated verification route directory is gone. Herd HTTP smoke returns 200 for `https://xiaomi-mimo.test/register` and 404 for the old notice, signed-verification, and notification endpoints with no verification copy in the registration response.
- `npm run build:android` and NativePHP/Gradle debug assembly pass; the deliberately nonexistent `codex-build-only` serial produces only the expected final ADB install message after a successful build. The new ignored APK is 131,559,203 bytes with SHA-256 `027d297747fe43b112699a3848fb3db0368ef41c7bf8ce77be47255bc5cab4d1`.
- Android build-tools report package `com.goleaf.xiaomimimo`, compile/target SDK 36, and minSdk 31. APK Signature Scheme v2, ZIP alignment, outer and nested archive integrity, the bundled removal migration, active-source verification exclusion, and host-database exclusion pass.
- A current browser DOM/console/network run is unavailable because both configured Chrome DevTools and Playwright profiles are already occupied by external browser sessions. A current APK install is also unavailable because no Android device is attached. These two environment checks are explicit limitations, not passing results; automated browser-facing contracts, production builds, HTTP smoke, and APK inspection pass.
- The complete diff and phase-owned staged diff were inspected before commit. The independently produced brand hardening work was already committed as `8b6e19f` before this phase committed and remained a separate ancestor rather than being mixed into the email-verification diff.

### Git Delivery

- `49f6d63` — `feat: remove email verification` contains the 48 phase-owned implementation, migration, negative regressions, translations, durable repository rule, and synchronized active documentation files.
- `git push --verbose origin main` exited 0 and advanced `origin/main` from `60249b2` to `49f6d63` without rewriting history. The push also published the already-separate local ancestor `8b6e19f` (`fix: harden Sutelio brand delivery`), which was created by another repository process and is not represented as part of this phase's commit.
- Generated Wayfinder outputs, production web assets, the NativePHP Android project/APK, local environment/database data, browser profiles, and credentials remain ignored or uncommitted. This status-only documentation commit records the completed delivery and will be pushed normally.
