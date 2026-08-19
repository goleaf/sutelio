# Sutelio Production Deployment Design

## Purpose

Deploy the web application to `https://sutelio.miniserver.fun` under
`/www/wwwroot/sutelio.miniserver.fun` and make every successful `main` CI run
publish the exact tested commit automatically. The production deployment must
preserve SQLite data and private storage, use the aaPanel-managed Nginx/PHP 8.5
stack, expose no deployment password or application secret, and retain a fast
code rollback path.

## Observed Baseline

- DNS already resolves `sutelio.miniserver.fun` to `78.158.19.114`.
- The host runs Rocky Linux 10.2, aaPanel, Nginx 1.31.3, PHP-FPM 8.5.8,
  Supervisor, Cron, firewalld, and Fail2Ban.
- PHP 8.5 has the required SQLite, PDO, intl, mbstring, curl, fileinfo, sodium,
  XML, ZIP, and OPcache extensions.
- No aaPanel website or target directory exists for Sutelio. HTTP currently
  reaches the default website and HTTPS presents the unrelated
  `deepseek.miniserver.fun` certificate.
- The repository already runs its complete quality pipeline in
  `.github/workflows/tests.yml` for `main` and pull requests.
- The working tree contains an unrelated in-progress localization/NativePHP
  slice. Deployment work must not stage or publish that slice.

## Considered Approaches

### aaPanel Git deployment

aaPanel can clone a Git repository and update a site through its Git project
workflow. This is simple but gives the production server repository credentials,
builds against mutable server tooling, and does not naturally gate publication
on the existing complete CI workflow.

### Build in place after an SSH pull

A GitHub job could SSH to the host, pull `main`, and run Composer/npm in the
live directory. This has the smallest initial script but exposes the active site
to partial dependency/build state, makes rollback slow, and couples deployment
to the server's Node and package-manager state.

### Tested artifact with atomic activation

The selected design builds an immutable archive for the successful CI commit on
a GitHub-hosted runner, transfers it through a dedicated SSH principal, and
activates it as a versioned release. The server never receives GitHub repository
credentials and never runs npm during activation. The `current` symlink changes
only after dependency, cache, migration, and database-health gates pass.

## Filesystem And Identity

```text
/www/wwwroot/sutelio.miniserver.fun/
├── current -> releases/<40-character-commit-sha>
├── releases/
│   └── <40-character-commit-sha>/
├── shared/
│   ├── .env
│   ├── database/database.sqlite
│   ├── incoming/
│   ├── storage/
│   └── deploy.lock
└── .user.ini
```

The root-only provisioning phase creates `sutelio-deploy` with group `www`.
GitHub Actions authenticates only as that user with a dedicated Ed25519 key.
The deploy user owns the release tree; PHP-FPM runs as `www`, reads releases and
the environment, and writes only shared storage/database paths and writable
Laravel cache directories. The workflow has no sudo or root capability.

The production `.env`, `APP_KEY`, SQLite database, WAL/SHM companions, uploads,
sessions, cache, logs, backups, and queue state never enter an immutable release.
`DB_DATABASE` and `DB_SQLITE_ALLOWED_DIRECTORY` use absolute shared paths.
`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` is the canonical HTTPS URL,
and session cookies are secure, HTTP-only, same-site, and encrypted.

## Build And Release Flow

1. The existing `tests` workflow runs for a push to `main`.
2. A separate `workflow_run` deployment starts only when that workflow completed
   successfully for the repository's own `main` branch.
3. The deployment job checks out `workflow_run.head_sha`, uses PHP 8.5 and Node
   22, installs locked production Composer dependencies, runs the locked Vite
   build, and packages source, `vendor`, and production assets without local
   databases, environment files, tests, documentation, Git metadata, or
   `node_modules`.
4. OpenSSH verifies the server against a pinned `known_hosts` value from the
   GitHub `production` environment. No third-party SSH action receives secrets.
5. The archive is copied to `shared/incoming/<sha>.tar.gz`; its SHA-256 checksum
   and exact commit SHA are passed to the activation script.
6. Activation takes an exclusive `flock`, validates identifiers/checksum/archive
   paths, extracts into a new release, links shared state, creates aaPanel's
   base-directory guard, builds Laravel caches, and validates the application.
7. On subsequent releases, the current application creates its SQLite-consistent
   signed backup before migration. Maintenance mode covers the migration and
   symlink switch; a trap always restores service on failure.
8. After `migrate --force` and `app:database-health`, `current` is switched
   atomically. Queue workers and an in-progress scheduler are gracefully
   interrupted/reloaded, maintenance mode ends, and local HTTPS `/up` plus
   external HTTPS checks must pass.
9. Only the five newest immutable releases are retained. Shared data and the
   current target are never part of cleanup. Retention runs only after the new
   release has passed health checks and rollback traps have been cleared.

Release retention is post-activation housekeeping, not an application-health
gate. An expired release that cannot be removed because it contains an
operator-managed or root-owned path produces an explicit warning naming only
the release commit ID, then cleanup continues. It does not turn an already
healthy activation into a failed deployment. The inaccessible path remains for
an operator to inspect and remove with the appropriate ownership; activation
never broadens the deploy user's privileges or changes ownership recursively to
hide the problem.

Repeated delivery of the active SHA is idempotent: it verifies the existing
release and health instead of mutating data or re-running a destructive setup.

## aaPanel Runtime

aaPanel owns the website record, PHP 8.5 association, Nginx vhost, access/error
logs, Let's Encrypt certificate, forced HTTPS, and automatic certificate
renewal. Its site path is `/www/wwwroot/sutelio.miniserver.fun` and its running
directory is `/current/public`, so aaPanel's open-basedir boundary can include
both immutable releases and shared Laravel state.

Nginx routes missing files to `index.php`, denies dotfiles and non-public source,
caches fingerprinted Vite assets, keeps dynamic responses uncached, limits body
size, enables only TLS 1.2/1.3, and sends HSTS, nosniff, referrer, frame, and a
same-origin production CSP. Certificate validation under `/.well-known` remains
available to aaPanel.

The aaPanel Cron manager runs `php artisan schedule:run` once per minute as
`www`. The aaPanel Supervisor plugin runs exactly one database queue worker as
`www`; a single worker avoids unnecessary SQLite writer contention and is
automatically restarted after Laravel's graceful reload signal.

## Failure And Rollback Behavior

- A failed CI run cannot reach the deployment job or its environment secrets.
- A host-key mismatch, checksum mismatch, invalid archive entry, missing `.env`,
  failed cache build, failed backup, failed migration, or failed database health
  check stops before the `current` switch.
- A failed post-switch local health check atomically restores the previous
  `current` symlink and brings the old code back online.
- A failed expired-release deletion after successful health verification is
  reported as a warning and does not roll back or fail the active release.
- Database migrations remain additive/populated-data-safe by the repository
  contract. Code rollback does not blindly run migration `down()` methods.
- The pre-migration application backup is retained for guarded manual restore;
  activation never overwrites the live SQLite file with a plain copy.
- Deployment logs contain commit IDs and command status, not environment values,
  credentials, session identifiers, private paths returned to users, or backup
  signing material.

## Threat Model

- **Credential theft:** the supplied root password is excluded from GitHub and
  repository files; automation uses a new least-privilege key and environment
  secrets.
- **Repository/supply-chain tampering:** deploy only the SHA that passed CI, use
  lock files, pin GitHub actions to full commit SHAs, and avoid third-party SSH
  actions.
- **Man-in-the-middle:** pin the server Ed25519 host key and require HTTPS with a
  hostname-valid certificate.
- **Path/archive tampering:** validate SHA/checksum, reject absolute/traversal
  archive members, use derived destination paths, and hold a deployment lock.
- **Data loss:** keep data outside releases, use the application's online SQLite
  backup, never run `migrate:fresh` or production seeders, and never clean shared
  state during release retention.
- **Privilege escalation:** the CI user receives no sudo access and cannot edit
  aaPanel, Nginx, SSH, firewall, or another website.

## Verification Contract

- Static regression coverage asserts the workflow trigger/guards, pinned action
  references, least permissions, host-key checking, checksum handoff, shared
  paths, maintenance cleanup, backup, migration, health, atomic switch, rollback,
  retention boundaries, and non-fatal post-activation cleanup failures.
- `bash -n` validates every deployment shell script.
- Focused Pest, Pint, Larastan, the full Pest suite, frontend tests, Vue type
  checking, ESLint, Prettier, Composer validation/audit, npm audit, and the final
  normal production build run before publication.
- Server gates verify aaPanel's site record, Nginx syntax, PHP-FPM selection,
  extensions, filesystem ownership/modes, production config, migration state,
  schedule, queue process, SQLite health/integrity/foreign keys, logs, and release
  SHA equality.
- Chrome DevTools MCP and Playwright MCP each navigate through a disposable
  isolated context. External checks cover HTTP-to-HTTPS redirect, valid SAN and
  renewal metadata, `/up`, registration/login shell rendering, console/network
  errors, security headers, cookies, and absence of horizontal overflow.

## Known External Boundary

Production mail credentials were not supplied. The initial environment therefore
uses the application's safe log mailer: web/auth/task behavior can run, but
password-reset, invitation, and reminder email delivery is not externally
deliverable until an SMTP provider and sender-domain records are configured.
No placeholder SMTP credential will be invented or stored.
