# Sutelio

Sutelio is a local-first, workspace-scoped task and collaboration application for web and NativePHP Mobile.

The implementation uses Laravel 13, Inertia 3, Vue 3, TypeScript, Pinia, Tailwind CSS 4, Reka UI primitives, Fortify, Sanctum, Wayfinder, Pest, Larastan, Pint, and SQLite.

## Start Here

Read `AGENTS.md` and `docs/index.md` before changing code. Active behavior is defined by `docs/requirements.md`, `docs/non-functional-requirements.md`, and `docs/compliance-matrix.md`; historical audits and plans are evidence, not current requirements.

## Local Development

Prerequisites are PHP 8.5 for the web/development runtime, Composer, Node 22 with npm, and SQLite/PDO SQLite. The tracked NativePHP lock currently installs PHP 8.5.9 for mobile; the conservative `>=8.4 <8.6` Composer range remains until NativePHP's official v4 documentation catches up with the shipped runtime.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
```

Laravel Herd serves this parked repository; do not start a second application server. Use Laravel Boost's absolute-URL tool before sharing a local URL.

The canonical repository and Herd identities are [`goleaf/sutelio`](https://github.com/goleaf/sutelio) and `https://sutelio.test`. They become effective only after the verified in-place GitHub, checkout, and Herd rename in Task 12; until then, use the currently configured remote and Herd link rather than creating a replacement repository or site.

For a non-destructive local demo graph:

```bash
php artisan db:seed
```

Seeders are local/demo guarded and must never erase production data. See `docs/seeding.md`.

## Quality Gates

```bash
composer validate --strict --no-check-publish
composer audit
vendor/bin/pint --format agent
composer run types:check
php artisan test --compact
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm audit
npm run build
```

See `docs/testing.md` for focused, parallel, coverage, migration, seeding, and browser checks.

## Runtime Boundaries

- SQLite is the only relational database. Keep the database and WAL/SHM files on a local SQLite-compatible filesystem.
- Web authentication uses Fortify without email verification; API v1 uses Sanctum abilities and the same policy/action boundaries as web flows.
- Recurrence, reminders, backups, and activity cleanup are scheduled operations. Deployment must configure the scheduler and the documented database queue execution model.
- Attachments, avatars, and backups are private and require application authorization.
- Livewire, Volt, Flux, Vue Router, Redis, and a second SQL database are not part of this repository's architecture.

Deployment and operational commands are documented in `docs/deployment.md` and `docs/operations.md`.
