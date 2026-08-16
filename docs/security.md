# Security

This document describes implemented controls; dated historical findings live under `docs/audit/security.md`.

## Implemented Boundaries

- Fortify owns browser registration, login, password reset, email verification, passkeys, two-factor challenge/recovery, password confirmation, and session lifecycle. Sensitive endpoints have explicit throttles and safe localized messages.
- Sanctum API tokens are hashed and ability scoped. API login/register are rate limited; every protected API route combines authentication, ability, policy, validation, and workspace scope.
- Invitation tokens are random, digest-backed, expiring, purpose/owner bound, single-use, cancellable, and replay-safe. Invitations do not create known-password accounts.
- Policies and aggregate-scoped queries/actions prevent direct-object and cross-workspace access. Exact mixed ID sets fail before writes.
- CSRF/origin/framework request-forgery protection remains enabled. Redirects are controlled; Vue/Blade output is escaped by default; no first-party rich-HTML rendering contract exists.
- Avatar/attachment/import inputs validate size, content/MIME pair, dimensions/shape where applicable, and use generated private paths. Downloads authorize at request time.
- Import is bounded and transactional; CSV export neutralizes spreadsheet formulas; exports stream; backups use opaque IDs, consistent SQLite snapshots, private storage, owner policy, restore confirmation, locking, integrity validation, and rollback safety.
- Configuration reads environment values only through config files. Application logs must not contain passwords, sessions, authorization headers, full tokens, private keys, backups, or sensitive request/response bodies.
- Production must use `APP_DEBUG=false`, a non-placeholder app key, secure cookies/HTTPS, private storage permissions, and the documented scheduler/queue controls.

## Current Threat Review

| Area                              | Current control / evidence                                                                                     |
| --------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| Account enumeration / brute force | Fortify and API rate limits; auth feature tests                                                                |
| Session fixation/logout           | Laravel session regeneration/invalidation tests                                                                |
| Tenant leakage / IDOR             | policies, scoped Form Requests/actions/queries, attacker/victim web/API tests                                  |
| Mass assignment                   | validated explicit data passed to actions; model fillable/guarded contracts reviewed                           |
| XSS / raw HTML                    | escaped Vue/Blade output; no trusted rich-text feature; architecture scan                                      |
| SQL/command injection             | Eloquent/query builder bindings; no user-controlled shell execution                                            |
| SSRF / open redirect              | no server-side user-URL fetching contract; controlled application redirects                                    |
| Path traversal / private files    | configured disks, generated paths, opaque backup inventory, authorized downloads                               |
| Replay / idempotency              | invitation digest consumption, recurrence occurrence uniqueness, reminder claim lifecycle, import/backup locks |
| Dependency advisories             | Composer and npm lock files were upgraded; final `composer audit` and `npm audit` both report zero advisories  |

No payment or webhook integration exists. If a server-side URL fetch, rich text, payment, webhook, impersonation, or public upload feature is added, it requires a new threat model and focused tests before exposure.

Security requirements and exact verification paths are `sec-*` rows in `docs/non-functional-requirements.md` and `docs/compliance-matrix.md`.
