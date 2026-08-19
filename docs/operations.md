# Operations

## Scheduled And Queued Work

The scheduler runs:

- `reminders:send` every minute without overlapping;
- `tasks:recurring` every minute without overlapping;
- `backup:run` daily;
- `activities:cleanup` daily.

Production must invoke `php artisan schedule:run` every minute or run the supported scheduler worker. Reminder delivery can use the configured database queue; production must run a bounded queue worker and monitor `failed_jobs` when asynchronous delivery is enabled. Jobs carry identifiers/small payloads, are idempotent, and respect retries/backoff/timeouts.

On the aaPanel production host, Cron invokes the stable scheduler wrapper once per minute as `www`. aaPanel Supervisor runs exactly one stable queue wrapper as `www` to avoid unnecessary concurrent SQLite writers:

```bash
/www/wwwroot/sutelio.miniserver.fun/shared/bin/run-scheduler
/www/wwwroot/sutelio.miniserver.fun/shared/bin/run-queue-worker
```

Both wrappers acquire a shared `shared/runtime.lock` before changing directory to `current`. The deployment script takes that lock exclusively before backup and migration, so a release waits for active work and no new scheduler/worker can write during the schema transition. The queue worker is bounded and exits after the queue has been empty for five seconds; Supervisor restarts it. Laravel's `reload` command restarts the worker after each release, and `schedule:interrupt` stops any still-running scheduler process from using old code.

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

Production release and service checks are:

```bash
readlink -f /www/wwwroot/sutelio.miniserver.fun/current
/www/server/php/85/bin/php /www/wwwroot/sutelio.miniserver.fun/current/artisan app:database-health --json
/www/server/php/85/bin/php /www/wwwroot/sutelio.miniserver.fun/current/artisan migrate:status
/www/server/php/85/bin/php /www/wwwroot/sutelio.miniserver.fun/current/artisan schedule:list
supervisorctl status
/www/server/nginx/sbin/nginx -t
curl --fail --show-error --silent https://sutelio.miniserver.fun/up
tail -n 100 /www/wwwlogs/sutelio.miniserver.fun.error.log
tail -n 100 /www/wwwroot/sutelio.miniserver.fun/shared/storage/logs/laravel.log
```

Do not print the production environment, application key, session values, deploy key, or request payloads while diagnosing an incident. aaPanel's Nginx certificate paths and ACME validation include must stay intact when changing the vhost.

## Backup/Restore And Incidents

Backups are private and inventoried by opaque ID. Restore requires owner policy, recent password confirmation, exclusive lock, integrity/compatibility verification, rollback copy, and post-restore health/FK checks. Operators should retain filesystem-level backups outside the application retention window and test restore in an isolated environment.

On database contention or integrity failure: stop mutating workers, preserve DB/WAL/SHM together, capture non-sensitive health output, avoid `migrate:fresh` or manual file replacement, and restore only through the guarded documented workflow after diagnosis.

Unexpected failures should include request/user/workspace/operation/model/external-request identifiers when useful, never secrets or complete payloads. Exact deployment commands are in `docs/deployment.md`.
