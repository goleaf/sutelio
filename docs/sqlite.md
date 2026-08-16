# SQLite Runtime And Data Safety

SQLite is the only supported relational database for web, testing, production, and NativePHP Mobile. Database/WAL/SHM files must live together on a local SQLite-compatible filesystem with application-only permissions; unsupported network filesystems are prohibited.

## Runtime Contract

Laravel's actual connection must report and health-check:

- foreign keys enabled;
- WAL journal mode;
- configured `NORMAL` synchronous behavior;
- bounded busy timeout;
- configured cache size and temp store;
- bounded WAL auto-checkpoint;
- `quick_check` success and zero foreign-key violations.

`SqliteHealthService` applies/verifies supported settings once at connection startup and `php artisan app:database-health` provides non-sensitive diagnostics. PRAGMAs are connection-specific; a standalone SQLite CLI result does not prove Laravel's connection state.

## Migrations And Queries

Migrations must be SQLite-compatible, safe for populated databases, and use expand/backfill/verify/switch/contract sequencing for risky changes. Table rebuilds require explicit orphan/value preflight, foreign-key revalidation, and a rollback/backup plan. Indexes derive from scoped query shapes and `EXPLAIN QUERY PLAN`/query-budget evidence.

Use short transactions, exact aggregate sets, deterministic pagination, `chunkById`/lazy streams for large work, and atomic locks/unique identities for races. Never use `migrate:fresh` outside isolated testing.

## Backup And Restore

Backups use a SQLite-consistent snapshot rather than copying only the main WAL database file. They are private, opaque, integrity checked, and path-contained. Restore is owner-only, password-confirmed, exclusive, compatibility/integrity validated, rollback protected, and followed by health/FK checks. No physical path is exposed in UI/API.

Deployment and incident commands are in `docs/operations.md`.
