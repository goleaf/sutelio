# Testing And Quality Gates

Pest is the sole primary PHP test style. Feature tests cover framework-integrated behavior; unit tests cover pure domain logic; frontend Node tests cover Vue state/interaction contracts; browser checks cover focus, layout, console, and flows that source/HTTP tests cannot prove. External requests are always faked.

## Test Organization

- `tests/Feature/Auth`, onboarding, and settings security/profile: Fortify, sessions, reset/two-factor/passkeys/preferences, the permanent absence of email verification, entry gating, resume, skip, replay, scoped/idempotent composition, and continuation.
- Workspace/project/task/child feature files: policies, validation, state transitions, isolation, files, recurrence/reminders, notifications, transfer/backup.
- `tests/Feature/Api`: API v1 envelope, auth, ability, policy, resource, validation, and domain parity.
- Schema/runtime/query/architecture files: migrations/FKs/indexes, SQLite health, application boundaries, resource typing, query counts, NativePHP, design/localization contracts.
- `resources/js/**/*.test.ts`: typed frontend state, onboarding progress/draft/plural contracts, CRUD and task/workspace interaction behavior.

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

Migration and seeding checks must use isolated testing SQLite databases. Never run `migrate:fresh` against the local working database:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan migrate --force
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan db:seed --force
```

The seeding test suite is the preferred proof when configuration needs a file-backed connection or multiple runs.

## Coverage And Assertions

Critical identity, onboarding, workspace isolation, policy, token, backup/restore, recurrence/reminder, notification-link, and integrity branches require meaningful positive/negative coverage. When a coverage driver is available, measure application coverage and target at least 90% meaningful application-code coverage; do not add assertion-free tests or broad exclusions to inflate it. The current Herd PHP 8.5 runtime has neither Xdebug nor PCOV: `herd php artisan test --coverage --compact` exits with `Code coverage driver not available`. This is tracked as `test-coverage-001`; the current behavioral suite passes 763 tests / 16,291 assertions sequentially and in parallel, and the discovered frontend suite passes 45/45.

## Browser Verification

Use the existing browser automation/Boost logs rather than installing a duplicate framework. Critical smoke includes login, navigation, dashboard, tasks, project/task detail, workspaces/members, settings/security/backup/import, validation and dialogs at representative mobile/tablet/desktop widths, keyboard focus, the fixed light mode, reduced motion, long translations, no horizontal overflow, and no fresh console/page errors.

The 2026-08-16 final smoke covered login, password confirmation, repeated keyboard Inertia navigation, dashboard/tasks/projects/calendar/activity/notifications/workspaces/profile/preferences/security at 1440x1000 and 390x844, activity URL filtering and mobile sheet state, reduced motion, dark media, and forced colors. Every checked page had one `h1`, no horizontal overflow, and no captured console/page error.

The final Notification Command Center pass repeated the authenticated notification route at 1440x1000 and 390x844. Keyboard-activated read/kind filters produced and cleared canonical URL state, filtered empty state rendered correctly, both filter groups exposed pressed-button group semantics, the page retained one `main` and one `h1`, 44-pixel controls, zero overflow, reduced-motion suppression, and zero current console/page/request failures.

The authenticated landmark-integrity pass added a source-level regression in `FrontendDesignTest.php` and exercised 11 representative routes at both 1440x1000 and 390x844. All 22 composed-DOM checks returned one shell-owned `main`, one page `h1`, zero horizontal overflow, and zero captured console, page, request, or HTTP error; the matrix includes task/project/workspace detail and nested profile settings layouts.

The guided-onboarding smoke registered real disposable users, resumed after logout/login, changed EN/LT/RU preferences, created and preserved real workspace/project/task records, accepted a pre-registration signed invitation before the completion gate, exercised required skip and replay/exit, and verified Dashboard checklist visibility/dismissal. Desktop and 390x844 mobile checks covered heading/validation focus, keyboard submission, 44-pixel actions, one landmark/heading, no overflow, and dark/reduced-motion/forced-colors modes.
