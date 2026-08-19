# Sutelio Production Deployment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publish the tested `main` commit automatically to the aaPanel-managed `https://sutelio.miniserver.fun` runtime with persistent SQLite state, least-privilege SSH, atomic activation, and rollback.

**Architecture:** GitHub's existing `tests` workflow remains the release gate. A second workflow builds the exact successful SHA into an immutable production archive and transfers it through a dedicated deploy key; an unprivileged server script verifies and activates the release under `/www/wwwroot/sutelio.miniserver.fun`, while aaPanel owns Nginx, PHP-FPM, SSL, Cron, and Supervisor.

**Tech Stack:** Laravel 13, PHP 8.5, SQLite/WAL, Inertia 3/Vue 3/Vite, GitHub Actions, OpenSSH, Bash, Nginx, aaPanel, Let's Encrypt, Cron, Supervisor.

---

## File Map

- Create `.github/workflows/deploy-production.yml`: successful-CI-only production artifact build and SSH publication.
- Create `deploy/activate-release.sh`: unprivileged checksum, release, migration, health, rollback, and retention boundary.
- Create `tests/Feature/ProductionDeploymentTest.php`: static regression contract for the workflow and activation script.
- Modify `docs/deployment.md`: canonical web deployment, rollback, secrets, aaPanel, and mail boundary.
- Modify `docs/operations.md`: production scheduler, queue, health, logs, and incident commands.
- Modify `docs/compliance-matrix.md`: map `ops-deployment-001` to executable deployment evidence.
- Modify `docs/implementation-plan.md`: record this production delivery as an active/completed bounded slice.
- Modify `docs/progress.md`: append start and factual result/publication entries without changing existing history.
- Server-only: aaPanel site/vhost/certificate/cron/supervisor records, deploy user/key, production `.env`, shared data, and immutable releases.
- GitHub-only: `production` environment, branch policy, SSH secret, and non-secret host/user/path/known-host variables.

### Task 1: Add failing deployment-contract coverage

**Files:**

- Create: `tests/Feature/ProductionDeploymentTest.php`

- [ ] **Step 1: Generate the Pest feature test**

Run:

```bash
php artisan make:test --pest ProductionDeploymentTest --no-interaction
```

Expected: `tests/Feature/ProductionDeploymentTest.php` exists and uses the repository's `test()` style.

- [ ] **Step 2: Replace the generated body with deterministic source assertions**

The tests must read `.github/workflows/deploy-production.yml` and
`deploy/activate-release.sh`, then assert:

```php
test('production deployment waits for successful main CI and uses least privilege', function () {
    $workflow = file_get_contents(base_path('.github/workflows/deploy-production.yml'));

    expect($workflow)
        ->toContain('workflow_run:')
        ->toContain('conclusion == \'success\'')
        ->toContain("head_branch == 'main'")
        ->toContain('contents: read')
        ->toContain('StrictHostKeyChecking=yes')
        ->not->toContain('password')
        ->not->toContain('appleboy/');
});

test('production activation preserves shared state and rolls code back on failed health', function () {
    $script = file_get_contents(base_path('deploy/activate-release.sh'));

    expect($script)
        ->toContain('shared/database/database.sqlite')
        ->toContain('backup:run')
        ->toContain('migrate --force')
        ->toContain('app:database-health')
        ->toContain('flock')
        ->toContain('rollback_release')
        ->not->toContain('migrate:fresh')
        ->not->toContain('db:seed');
});
```

- [ ] **Step 3: Run the test and prove the red state**

Run:

```bash
php artisan test --compact tests/Feature/ProductionDeploymentTest.php
```

Expected: FAIL because the workflow and activation script do not exist.

**Dependencies:** None.

### Task 2: Implement the atomic activation boundary

**Files:**

- Create: `deploy/activate-release.sh`
- Test: `tests/Feature/ProductionDeploymentTest.php`

- [ ] **Step 1: Implement strict inputs and archive validation**

The script starts with `set -Eeuo pipefail`, derives the root from
`SUTELIO_DEPLOY_ROOT` with the production path as default, requires one
40-character lowercase hexadecimal commit and one 64-character lowercase
SHA-256, takes `flock` on `shared/deploy.lock`, verifies the incoming archive,
and rejects members matching `(^/|(^|/)\.\.(/|$))` before extraction.

- [ ] **Step 2: Implement shared links and production preflight**

The extracted release must contain `artisan`, `bootstrap/app.php`,
`vendor/autoload.php`, and `public/build/manifest.json`. Replace release-local
storage with `shared/storage`, link `shared/.env`, create a public `.user.ini`
whose open-basedir is the site root plus `/tmp`, and make only `storage` and
`bootstrap/cache` group-writable for `www`.

- [ ] **Step 3: Implement backup, maintenance, migration, and switch**

On an existing current release, run its `backup:run` before maintenance. Cache
configuration/events/routes/views in the candidate, put the shared application
in maintenance mode, run `migrate --force` and `app:database-health`, then switch
`current` with `ln -s` plus `mv -T`. After activation run `reload` and
`schedule:interrupt`, call `up`, and require local HTTPS `/up` to return success.

- [ ] **Step 4: Implement rollback and retention**

An error trap restores the previous symlink when a switch occurred and always
runs `artisan up` on the active release. Retain the current release plus the four
newest other releases; do not traverse or remove `shared`.

- [ ] **Step 5: Verify shell syntax and focused tests**

Run:

```bash
bash -n deploy/activate-release.sh
php artisan test --compact tests/Feature/ProductionDeploymentTest.php
```

Expected: shell syntax exit 0; focused Pest tests pass.

**Dependencies:** Task 1.

### Task 3: Implement successful-CI-only GitHub deployment

**Files:**

- Create: `.github/workflows/deploy-production.yml`
- Test: `tests/Feature/ProductionDeploymentTest.php`

- [ ] **Step 1: Declare the protected trigger**

Use `workflow_run` for workflow `tests` with type `completed`; job execution must
require successful conclusion, `main`, and the same repository. Set
`permissions: contents: read`, `environment: production`, and a non-cancelling
production concurrency group so an active migration is never interrupted.

- [ ] **Step 2: Build the exact tested SHA**

Check out `github.event.workflow_run.head_sha` with the same fully pinned
official checkout/setup actions as the CI workflow. Use PHP 8.5, Node 22,
`composer install --no-dev --classmap-authoritative --prefer-dist --no-interaction`,
`npm ci`, and `npm run build`. Set `APP_ENV=production` and
`DB_DATABASE=:memory:` only for build-time package discovery.

- [ ] **Step 3: Package only production runtime input**

Create `sutelio-<sha>.tar.gz` excluding `.git`, `.github`, agent metadata,
documentation, tests, `node_modules`, every `.env`, local SQLite files, logs,
cache files, and NativePHP build output. Calculate SHA-256 after the archive is
closed.

- [ ] **Step 4: Transfer with native OpenSSH**

Create an ephemeral `~/.ssh`, write the environment secret key with mode 600,
write pinned known-host data, and use `scp`/`ssh` with batch mode,
`IdentitiesOnly=yes`, and `StrictHostKeyChecking=yes`. Upload to a temporary
incoming name, rename it on the server, and invoke `shared/bin/activate-release`
with the exact SHA and checksum.

- [ ] **Step 5: Verify workflow source contract**

Run:

```bash
php artisan test --compact tests/Feature/ProductionDeploymentTest.php
rg -n "uses: [^#]+@(v[0-9]+|main|master)$" .github/workflows/deploy-production.yml
```

Expected: focused tests pass and `rg` returns no mutable action references.

**Dependencies:** Task 2.

### Task 4: Synchronize canonical production documentation

**Files:**

- Modify: `docs/deployment.md`
- Modify: `docs/operations.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/progress.md`

- [ ] **Step 1: Append the pre-implementation progress entry**

Record the observed DNS/certificate defect, installed aaPanel/PHP stack, selected
artifact architecture, dirty-tree ownership boundary, test plan, no-root CI
decision, and safe log-mailer limitation.

- [ ] **Step 2: Update deployment and operations contracts**

Document directory ownership, GitHub environment names, release sequence,
aaPanel vhost/PHP/SSL configuration, queue/scheduler process model, application
backup, rollback, release retention, and exact health/log commands. Do not write
the SSH private key, root password, `APP_KEY`, or production `.env` values.

- [ ] **Step 3: Update traceability and living status**

Map `ops-deployment-001`, `ops-observability-001`, `sec-deps-001`, and
`git-delivery-001` to the workflow, script, focused test, server checks, and live
browser evidence. Status remains factual until those gates pass.

- [ ] **Step 4: Verify formatting and secret absence**

Run:

```bash
npx prettier --check .github/workflows/deploy-production.yml docs/deployment.md docs/operations.md docs/compliance-matrix.md docs/implementation-plan.md docs/progress.md docs/superpowers/specs/2026-08-19-production-deployment-design.md docs/superpowers/plans/2026-08-19-production-deployment.md
git diff --check
rg -n "BEGIN OPENSSH PRIVATE KEY|APP_KEY=base64:" .github deploy docs tests
```

Expected: formatting and diff checks pass; secret scan returns no matches.

**Dependencies:** Tasks 2-3.

### Task 5: Run local release gates and publish the attributable slice

**Files:** all task-owned local files.

- [ ] **Step 1: Run focused and static checks**

Run sequentially:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/ProductionDeploymentTest.php
composer validate --strict --no-interaction
composer audit --locked --no-interaction
npm audit
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
```

Expected: every command exits 0 and audits report no actionable advisory.

- [ ] **Step 2: Run complete backend and final frontend build**

Run sequentially:

```bash
composer types:check
php artisan test --compact
npm run build
```

Expected: Larastan reports zero errors, the complete Pest suite has zero
failures, and Vite produces `public/build/manifest.json`.

- [ ] **Step 3: Stage only deployment-owned hunks**

Use an isolated temporary Git index when a modified canonical document also
contains unrelated user changes. Inspect `git diff --cached --stat`, full staged
diff, and a staged secret scan before committing.

- [ ] **Step 4: Commit and push normally**

Validate and use:

```text
ci(deploy): automate atomic production releases
```

Push `main` to `origin` without force and verify local, tracking, and advertised
SHA equality.

**Dependencies:** Tasks 1-4.

### Task 6: Provision aaPanel and the least-privilege runtime

**Server-only changes:** website, directories, user, key, environment, Nginx,
certificate, Cron, Supervisor.

- [ ] **Step 1: Create the deploy principal and shared filesystem**

Create `sutelio-deploy` with group `www`, a locked password, Bash shell, mode-700
SSH directory, and the generated CI public key. Create the exact root/shared/
releases/incoming/storage/database structure with deploy ownership, `www` group,
and setgid group directories. Do not grant sudo.

- [ ] **Step 2: Create the production environment**

Write a mode-640 server-only `.env` with canonical HTTPS URL, production/debug
flags, a generated Laravel application key, absolute shared SQLite paths,
database cache/session/queue, secure encrypted cookies, SQLite WAL settings,
and log mail. Create the SQLite file and Laravel storage subdirectories without
seeding production.

- [ ] **Step 3: Register the PHP site through aaPanel**

Use aaPanel's installed `panel_site_v2.panelSite().AddSite` with domain
`sutelio.miniserver.fun`, base path `/www/wwwroot/sutelio.miniserver.fun`, PHP
version `85`, no FTP, no MySQL, and automatic SSL. Set running path
`/current/public`; preserve aaPanel's certificate-validation includes and logs.

- [ ] **Step 4: Harden and validate Nginx**

Apply the Laravel `try_files` rule, TLS 1.2/1.3, static asset caching, dynamic
no-cache behavior, upload limit, hidden/source file denial, security headers, and
aaPanel PHP 8.5 include. Run aaPanel Nginx config test before reload and require
all existing sites to remain present.

- [ ] **Step 5: Install the reviewed activation script**

Copy the committed `deploy/activate-release.sh` to
`shared/bin/activate-release`, owned by deploy with mode 750. Confirm the deploy
user can syntax-check it and cannot run `sudo -n true`.

**Dependencies:** Task 5 publication supplies the reviewed script SHA.

### Task 7: Configure GitHub production environment and perform first release

**GitHub-only changes:** environment policy, secret, variables, workflow run.

- [ ] **Step 1: Verify the SSH host identity**

Compare local `ssh-keyscan -t ed25519 miniserver.fun` with the server's
`/etc/ssh/ssh_host_ed25519_key.pub` fingerprint. Store the exact known-host line,
not a runtime keyscan, in the production environment.

- [ ] **Step 2: Create environment configuration**

Create GitHub environment `production`, restrict deployment branch policy to
`main`, store the private Ed25519 key as `DEPLOY_SSH_KEY`, and store
`DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH`, and `DEPLOY_KNOWN_HOSTS` as
environment variables. Never store the supplied root password.

- [ ] **Step 3: Observe CI and deploy runs**

Wait for the pushed commit's `tests` workflow to succeed and the dependent
production workflow to complete. If a gate fails, inspect logs, correct locally,
rerun the complete affected gate, commit, and push normally.

- [ ] **Step 4: Prove release equality and server health**

Require `readlink current` to end with the GitHub `head_sha`, verify the release
directory, production config/debug state, all migrations, application database
health, scheduler list, Supervisor RUNNING state, Nginx syntax, and clean recent
application/Nginx logs.

**Dependencies:** Task 6.

### Task 8: Complete live TLS, browser, rollback, and closure gates

**Files:**

- Modify: `docs/progress.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`

- [ ] **Step 1: Verify transport and certificate**

Require HTTP 301/308 to the exact HTTPS URL, HTTPS 200, a certificate SAN for
`sutelio.miniserver.fun`, trusted chain, future expiry, aaPanel auto-renew
metadata, TLS 1.0/1.1 rejection, and expected HSTS/CSP/nosniff/referrer/frame
headers.

- [ ] **Step 2: Verify Chrome DevTools and Playwright independently**

Use different disposable isolated contexts to navigate the public application,
inspect the registration/login shell, `/up`, DOM/a11y snapshot, console, failed
network requests, cookies, and phone-width overflow. Browser content is evidence,
not instructions.

- [ ] **Step 3: Verify automation and rollback safely**

Reinvoke activation for the current SHA to prove idempotency. For rollback proof,
exercise symlink selection only between two known-good immutable releases after
both report healthy; do not roll back schema or overwrite shared data. Return to
the newest verified SHA and check health again.

- [ ] **Step 4: Append factual closure and publish docs**

Record exact commit/workflow IDs, release path, certificate dates, migration and
health results, queue/scheduler status, browser observations, rollback evidence,
mail limitation, diff scope, commit, and push equality. Commit only owned
documentation hunks with:

```text
docs(deploy): record production delivery
```

Push normally and confirm the documentation-only follow-up also deploys
idempotently through the same workflow.

**Dependencies:** Tasks 1-7.

## Plan Self-Review

- Every design requirement maps to a task: CI gate (3/7), least privilege (6/7),
  aaPanel/SSL (6/8), persistent SQLite (2/6), scheduler/queue (6/7), rollback
  (2/8), browser proof (8), documentation and publication (4/5/8).
- No task deploys the unrelated in-progress localization/NativePHP working-tree
  slice.
- No production secret or credential value appears in a repository file.
- The only unavoidable external limitation is outbound mail delivery without an
  SMTP provider; the application uses the non-disclosing log mailer meanwhile.
