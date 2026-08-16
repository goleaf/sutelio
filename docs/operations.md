# Operations

## Scheduled And Queued Work

The scheduler runs:

- `reminders:send` every minute without overlapping;
- `tasks:recurring` every minute without overlapping;
- `backup:run` daily;
- `activities:cleanup` daily.

Production must invoke `php artisan schedule:run` every minute or run the supported scheduler worker. Reminder delivery can use the configured database queue; production must run a bounded queue worker and monitor `failed_jobs` when asynchronous delivery is enabled. Jobs carry identifiers/small payloads, are idempotent, and respect retries/backoff/timeouts.

## Health And Release Checks

```bash
php artisan about --only=environment,cache,drivers
php artisan app:database-health
php artisan schedule:list
php artisan migrate:status
php artisan route:list --except-vendor
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run cache commands with production-like environment values and clear/rebuild them after configuration or route changes. Health output must not expose credentials or private paths in public responses.

## Backup/Restore And Incidents

Backups are private and inventoried by opaque ID. Restore requires owner policy, recent password confirmation, exclusive lock, integrity/compatibility verification, rollback copy, and post-restore health/FK checks. Operators should retain filesystem-level backups outside the application retention window and test restore in an isolated environment.

On database contention or integrity failure: stop mutating workers, preserve DB/WAL/SHM together, capture non-sensitive health output, avoid `migrate:fresh` or manual file replacement, and restore only through the guarded documented workflow after diagnosis.

Unexpected failures should include request/user/workspace/operation/model/external-request identifiers when useful, never secrets or complete payloads. Exact deployment commands are in `docs/deployment.md`.
