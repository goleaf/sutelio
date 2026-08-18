# Testing And Quality Gates

Pest is the sole primary PHP test style. Feature tests cover framework-integrated behavior; unit tests cover pure domain logic; frontend Node tests cover Vue state/interaction contracts; browser checks cover focus, layout, console, and flows that source/HTTP tests cannot prove. External requests are always faked.

## Test Organization

- `tests/Feature/Auth`, API auth, language selection, onboarding, and settings security/profile: Fortify, sessions, reset/two-factor/passkeys/preferences, account/device locale precedence and persistence, the permanent absence of email verification, atomic localized workspace bootstrap, current-session selection, mandatory completion-only entry gating, retired-skip 404 behavior, legacy skipped-row reopening, resume, replay-specific exit, scoped/idempotent composition, and continuation.
- Workspace/project/task/child feature files: policies, validation, state transitions, isolation, files, recurrence/reminders, notifications, transfer/backup.
- `tests/Feature/Api`: API v1 envelope, auth, ability, policy, resource, validation, and domain parity.
- Schema/runtime/query/architecture files: migrations/FKs/indexes, SQLite health, application boundaries, resource typing, query counts, NativePHP, design/localization contracts.
- `BrandIdentityTest.php`: canonical metadata, active-file legacy identity exclusion, deterministic clean-S geometry/raster outputs, browser/logo semantics, and safe NativePHP publication/rollback. `NativePhpMobileTest.php` separately protects runtime/package integration, including the external Sutelio IDs and unchanged internal Android namespace.
- `resources/js/**/*.test.ts`: typed frontend state, onboarding progress/draft/plural contracts, CRUD and task/workspace interaction behavior. `resources/js/lib/globalBusy.test.ts` proves overlap safety, router/background classification, progress normalization, upload forwarding, and cleanup after success or failure.
- `GlobalOperationFeedbackTest.php`: bootstrap, visual opacity, non-dismissible blocking, accessibility modes, and EN/LT/RU translation parity for the universal foreground-operation contract.
- `FrontendColorPickerTest.php`: maintained Zag package ownership, complete native-color-input removal, one shared picker consumer contract across onboarding/projects/workspace definitions, localized assistive copy, responsive positioning, touch, reduced-motion, and forced-colors source guarantees.

## Per-Pass Workflow

1. Add or update a focused regression test and observe the relevant failure when practical.
2. Implement the smallest coherent change.
3. Run the focused test, `vendor/bin/pint --dirty --format agent` for PHP, scoped Larastan, and relevant frontend checks.
4. Inspect the diff, update `docs/progress.md`, plan, and compliance matrix.
5. Expand to the complete gate only after the focused pass is green.

## Commands

```bash
vendor/bin/pint --format agent
composer run types:check
php artisan test --compact
php artisan test --parallel --compact
php artisan test --coverage
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
```

The focused Sutelio documentation/identity gate is:

```bash
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/NativePhpMobileTest.php
```

Automated brand tests prove deterministic source and generated-tree contracts, not a shipped artifact or human visual result. Browser verification must inspect title/metadata/favicon, shared/authentication logo composition, focus, forced colors, zoom, fixed-light behavior, and representative viewports. The Task 10 NativePHP 4.2 artifact was independently inspected for manifest/resources/signature/alignment/archives and clean-installed on Android 14. Its exact installed hash, cold launch/relaunch, mandatory first-run language dialog, Russian registration/onboarding/default-workspace/task flow, persisted Lithuanian presentation, 130% font scaling, orientation reflow, SQLite integrity/foreign keys/39 migrations, resumed activity, and process-scoped logs pass on the emulator. After repository publication, the exact hash independently passed two cold launches, clean SQLite integrity/foreign-key/39-migration checks, resumed-activity proof, and process-scoped log inspection on the sole authorized Samsung SM-A920F running Android 10 / API 29. The deterministic brand test additionally guards removal of NativePHP request/header/cookie/response/CSRF value logging after every fresh native generation.

Migration and seeding checks must use isolated testing SQLite databases. Never run `migrate:fresh` against the local working database:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate --force
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan db:seed --force
```

The seeding test suite is the preferred proof when configuration needs a file-backed connection or multiple runs.

## Coverage And Assertions

Critical identity, onboarding, workspace isolation, policy, token, backup/restore, recurrence/reminder, notification-link, and integrity branches require meaningful positive/negative coverage. When a coverage driver is available, measure application coverage and target at least 90% meaningful application-code coverage; do not add assertion-free tests or broad exclusions to inflate it. The Task 10 sequential Pest suite passes 1,001 tests / 41,587 assertions, and all 56 frontend tests pass. The current Herd PHP 8.5 runtime has neither Xdebug nor PCOV; this remains tracked as `test-coverage-001`, and no coverage percentage is available or claimed.

## Browser Verification

Every UI/UX remediation governed by `ui-system-001` must start from the exact occurrence inventory in `docs/ui-ux-audit-2026-08-17.md` and add focused regression coverage at the shared correction layer. The final matrix must cover EN/LT/RU; 390 px phone, tablet, desktop, 200% zoom/reflow; keyboard and focus return; accessible names/errors/live regions; coarse-pointer 44×44 targets; reduced motion; forced colors; loading, empty, filtered-empty, error, disabled, pending, success, and offline/network-recovery states as applicable.

The global-operation browser matrix must throttle a real language change or Inertia navigation and one configured standalone HTTP action long enough to inspect the active state. Verify the top progress line, centered localized status, computed 70% backdrop, 30% page visibility, root `inert`/`aria-busy`, blocked pointer and keyboard activation, ignored Escape, overlap-safe completion, reduced-motion static progress, forced-colors boundary, no horizontal overflow, and no fresh console error. Background `showProgress: false` and Precognition requests must not activate the page lock.

Source tests may prove contracts and inventory, but they do not replace rendered geometry, computed contrast, focus order, or device behavior. Authenticated browser tests must use an isolated non-personal fixture and must not impersonate or mutate an existing local account. Findings stay open until the listed focused tests, browser states, and relevant full quality gates pass against the committed implementation.

Use the existing browser automation/Boost logs rather than installing a duplicate framework. Critical smoke includes login, navigation, dashboard, tasks, project/task detail, workspaces/members, settings/security/backup/import, validation and dialogs at representative mobile/tablet/desktop widths, keyboard focus, the fixed light mode, reduced motion, long translations, no horizontal overflow, and no fresh console/page errors.

The 2026-08-16 final smoke covered login, password confirmation, repeated keyboard Inertia navigation, dashboard/tasks/projects/calendar/activity/notifications/workspaces/profile/preferences/security at 1440x1000 and 390x844, activity URL filtering and mobile sheet state, reduced motion, dark media, and forced colors. Every checked page had one `h1`, no horizontal overflow, and no captured console/page error.

The final Notification Command Center pass repeated the authenticated notification route at 1440x1000 and 390x844. Keyboard-activated read/kind filters produced and cleared canonical URL state, filtered empty state rendered correctly, both filter groups exposed pressed-button group semantics, the page retained one `main` and one `h1`, 44-pixel controls, zero overflow, reduced-motion suppression, and zero current console/page/request failures.

The authenticated landmark-integrity pass added a source-level regression in `FrontendDesignTest.php` and exercised 11 representative routes at both 1440x1000 and 390x844. All 22 composed-DOM checks returned one shell-owned `main`, one page `h1`, zero horizontal overflow, and zero captured console, page, request, or HTTP error; the matrix includes task/project/workspace detail and nested profile settings layouts.

The guided-onboarding smoke registers real disposable users, resumes after logout/login, changes EN/LT/RU preferences, creates and preserves real workspace/project/task records, accepts a pre-registration signed invitation before the completion gate, verifies that required onboarding has no skip control or route, exercises replay-specific exit, and verifies Dashboard checklist visibility/dismissal. Desktop and 390x844 mobile checks cover heading/validation focus, keyboard submission, 44-pixel actions, one landmark/heading, no overflow, and light/reduced-motion/forced-colors modes.

Those dated smoke statements preserve pre-light-only evidence. The current product is light-only, so the successor light-motion/final-device plan's Task 18 must freshly verify fixed-light rendering, reduced motion, and forced colors without claiming that the historical dark-media observations describe the current runtime.

Task 10 repeated fresh integrated evidence with independent disposable Chrome DevTools and Playwright profiles. Guest and disposable authenticated EN/LT/RU checks covered 320 px through 1440 px, effective 200% reflow, keyboard focus, coarse touch targets, fixed-light behavior, reduced motion, forced colors, offline/reconnect feedback, drawer dismissal/navigation, mandatory onboarding, default-workspace continuity, long task/project content, and representative settings. No checked page overflowed horizontally, final consoles and requests were clean, all disposable browser data was deleted through product UI, Lighthouse scored 100 for accessibility/best-practices/SEO/agentic with zero failed audits, and the representative task trace measured 400 ms LCP and 0.00 CLS.

The global-language smoke covers the mandatory first-run dialog and shared shell switcher at 390x844, 820x1180, and 1440x1000. It verifies immediate whole-dialog Lithuanian/Russian preview before confirmation, persisted server-authoritative `PUT /locale` updates, document language and page-copy refresh, focus containment, blocked Escape, 44-pixel controls, reduced motion, local flags, no overflow, and zero current runtime/network failures. Account precedence, invalid input, registration inheritance, and login cookie synchronization remain deterministic Pest contracts so browser QA does not mutate the local user database.

The shared color-picker smoke must cover an arbitrary HEX change during onboarding, project presets inside a nested dialog, workspace status/priority and label creation/edit controls, EN/LT/RU accessible names, 320/390 phone, 820 tablet, and 1440 desktop geometry, keyboard arrows and Escape focus return, coarse targets, reduced motion, forced colors, and final console/network cleanliness. The disposable account and all generated project/task/workspace data must be deleted through the product UI after verification.

## Soft Motion And Icon Gate

`FrontendMotionIconSystemTest.php` guards the shared motion primitives, fixed-light orange/cobalt roles, `IconTile` ownership, primary page and section-heading adoption, bounded stagger consumers, accessible icon-only controls, the intentional absence of global header search, exact non-tile presentation exclusions, and allowed persisted entity-color literals. Task 10 passes all 56 frontend tests, Vue type checking, ESLint, Prettier, Larastan with zero errors, the complete Pest suite, isolated SQLite, dependency audits, and the production build. The current production web build transforms 3,597 modules with main CSS at 184.85 kB / 26.85 kB gzip and the app entry at 137.73 kB / 33.83 kB gzip.
