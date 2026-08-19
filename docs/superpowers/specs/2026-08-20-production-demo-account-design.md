# Production Demo Account Design

## Problem

Sutelio has a rich `DemoSeeder`, but it is intentionally restricted to `local` and `testing`, uses factory-backed demo identities, and is not safe to invoke from a production release. The configured local database and the production database currently contain no known demo account. The requested result is one real production login with broad, representative data without weakening the existing production guard, exposing another tenant, committing a password, or silently deleting later test activity.

## Decision

Add a dedicated operator-only Artisan command and a production-safe deterministic seeder. The command is the only supported production entry point and requires the explicit `--allow-production` switch when `APP_ENV=production`. It generates a cryptographically strong password inside the process, stores only Laravel's password hash, prints the plaintext once to the invoking operator, and never accepts a password in a command-line argument, environment file, source file, application log, or database fixture.

The default operation is additive and persistent. If the canonical demo account already exists, the command reports that fact without changing its password or overwriting user-created demo data. The explicit `--reset` operation deletes and rebuilds only records belonging to the closed demo identity set and canonical demo workspace. Reset generates a new user identity and password, thereby invalidating the old login without querying or altering real users or workspaces.

The existing `DatabaseSeeder`, `DemoSeeder`, and their production rejection remain unchanged. Normal deployment also remains seed-free; demo provisioning is a separate post-deployment operator action.

## Alternatives Considered

1. **Dedicated production demo command and isolated dataset — selected.** It is explicit, reviewable, repeatable, compatible with the no-dev production dependency set, and keeps the normal seed and deploy paths closed.
2. **Allow the existing `DemoSeeder` in production — rejected.** It would expose predictable fixture credentials, depend on Faker-backed factories, mix local fixture assumptions with production state, and still omit important domain states.
3. **Run a second application and SQLite database on a separate demo host — deferred.** This gives the strongest infrastructure isolation but adds another Nginx/PHP-FPM/release/backup/monitoring surface. The current workspace authorization and operator-only global backup policy already isolate a dedicated demo tenant adequately for the requested single-account test environment.

## Operator Interface

The command contract is:

```text
php artisan demo:provision {--allow-production} {--reset}
```

- Production execution without `--allow-production` exits non-zero before any write.
- First provisioning creates the complete dataset and prints the login URL, canonical email, and generated password once.
- Re-running without `--reset` is a no-op and does not reveal or rotate the password.
- `--reset` removes only the canonical demo identities/workspace graph, recreates it transactionally, and prints a new password once.
- The command never runs from `DatabaseSeeder`, the deployment workflow, a scheduler, a queue, an HTTP route, or application startup.

## Architecture And Boundaries

`App\Console\Commands\ProvisionDemoAccount` owns environment gating, one-time password generation, operator output, and exit codes. `App\Actions\ProvisionProductionDemo` owns the transaction, existing-account/no-op decision, narrowly scoped reset, and compensation for the deterministic private attachment. `Database\Seeders\ProductionDemoSeeder` owns the finite Eloquent graph and accepts the already-created canonical owner plus synthetic collaborators. It does not use factories, Faker, raw SQL, external requests, mail delivery, notifications with outbound channels, or unbounded collections.

The action locks the canonical demo identity before deciding whether to create, no-op, or reset. The identity set uses reserved non-deliverable `*.example` addresses and exact constants. The workspace uses an exact reserved slug. Reset first proves that any matching workspace is owned by the canonical demo account; a collision with a foreign owner fails closed and preserves all data.

No `is_demo` schema column is needed: the command is not a runtime authorization branch or a public feature flag. Ordinary workspace policies remain the security boundary. Application-wide backups remain inaccessible because `DatabaseBackupPolicy` requires the separately configured operator email in addition to workspace ownership.

## Seeded Coverage

The canonical account is a Russian-language owner with `Europe/Vilnius`, Monday week start, calendar default view, dashboard start page, all notification preferences enabled, and a completed current-version onboarding lifecycle. Synthetic administrator and member users have Lithuanian and English preferences, random unknown passwords, and no reusable credentials.

The demo workspace contains:

- owner, administrator, and member memberships;
- active and archived projects;
- canonical task statuses and all five priority definitions;
- labels and tags, including Unicode content;
- unassigned and assigned tasks covering pending, in-progress, completed, overdue, due-today/all-day, future date range, no/low/medium/high/urgent priority, pinned, favorite, archived, soft-deleted, parent, and subtask states;
- one valid recurring series with a generated occurrence;
- multiple checklists and checked/unchecked items;
- comments from every role;
- pending, processing, delivered, failed, and cancelled reminder lifecycle examples, with pending/processing examples scheduled safely in the future so the production worker does not claim them during provisioning;
- pending, expired, accepted, and cancelled invitation records whose token digests are generated randomly and whose plaintext tokens are never exposed;
- private attachment metadata backed by a small deterministic text file on the configured attachment disk;
- workspace activity events and read/unread private database notifications.

Dates are relative to provisioning time so dashboard, task, project, calendar, activity, and notification screens are immediately meaningful. Persistent edits are intentionally retained until an explicit reset.

## Failure And Recovery

All database writes use one short retryable transaction. A collision with a real user email, a canonical slug owned by another user, missing task definitions, invalid relationships, storage failure, or constraint violation aborts the operation. The attachment path is fixed inside a demo-only prefix. If initial storage succeeds and the database transaction later fails, the action deletes only the newly created demo file; it never removes a pre-existing file during failed reset compensation.

The command emits generic failure text and a non-zero exit status; exception details remain in the normal protected Laravel report path without credentials. The generated password is held only in memory and printed after the transaction commits.

## Testing And Verification

Focused Pest coverage must prove:

- production refusal without the explicit flag and zero writes;
- first provisioning creates a login-capable completed-onboarding account;
- the dataset covers every listed role and domain state, including a real private attachment;
- a second non-reset run preserves counts, password, and a user edit;
- explicit reset rotates the login, removes only demo data, and preserves an unrelated user/workspace;
- production provisioning never calls factories/Faker, sends mail, queues external work, or restores email verification;
- backup capability stays false for the demo account unless an operator deliberately misconfigures `BACKUP_OPERATOR_EMAIL` to the reserved demo email.

Verification then runs focused Pest, Pint, Larastan, the full Pest suite, an isolated fresh SQLite migration and local `DatabaseSeeder`, Composer validation/audit, frontend checks, and the production Vite build. After publication and automated deployment reach the exact commit, the operator runs the command as the `www` runtime user, verifies database health, authenticates through Fortify in disposable Chrome DevTools and Playwright contexts, checks representative authenticated pages and cross-workspace isolation, and confirms no new server errors or failed jobs.

## Non-Goals

- No public “reset demo” button, scheduled reset, shared static password, admin impersonation, email verification, extra database, new dependency, or alternate deployment pipeline.
- No attempt to make external email delivery work while production uses the non-persistent `array` mailer.
- No APK rebuild or physical-device installation is required for this server-side fixture capability.
