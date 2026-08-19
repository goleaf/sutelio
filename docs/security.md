# Security

This document describes implemented controls. The superseded July security audit was removed from the working tree after remediation; its original content remains recoverable from Git history, while current evidence lives here, in the compliance matrix, tests, and `docs/progress.md`.

## Implemented Boundaries

- Fortify owns browser registration, login, password reset, passkeys, two-factor challenge/recovery, password confirmation, and session lifecycle. Email verification is intentionally disabled and guarded against reintroduction; sensitive endpoints retain explicit throttles and safe localized messages.
- Sanctum API tokens are hashed and ability scoped. API login/register are rate limited; every protected API product route combines authentication, mandatory-onboarding completion, ability, policy, validation, and workspace scope. Pending users receive a stable `409 onboarding_required` before product throttling, route-model binding, ability checks, or controller execution while token logout remains available.
- Invitation tokens are random, digest-backed, expiring, purpose/owner bound, single-use, cancellable, and replay-safe. Invitations do not create known-password accounts.
- Policies and aggregate-scoped queries/actions prevent direct-object and cross-workspace access. Exact mixed ID sets fail before writes.
- Notification reads and mutations are anchored to the authenticated user's relation. Optional task destinations are emitted only after one batched query proves current workspace ownership or membership; foreign, deleted, empty, and malformed identifiers produce no actionable URL.
- CSRF/origin/framework request-forgery protection remains enabled. Redirects are controlled; Vue/Blade output is escaped by default; no first-party rich-HTML rendering contract exists.
- Avatar/attachment/import inputs validate size, content/MIME pair, dimensions/shape where applicable, and use generated private paths. Downloads authorize at request time.
- Import is owner/administrator-only, bounded, previewed, and transactional; execution-time validation cannot emit false success. CSV export neutralizes spreadsheet formulas; exports stream. Backups use opaque IDs, consistent SQLite snapshots, private storage, configured-operator policy, recent password confirmation, locking, integrity validation, and rollback safety.
- Configuration reads environment values only through config files. Application logs must not contain passwords, sessions, authorization headers, full tokens, private keys, backups, or sensitive request/response bodies.
- NativePHP bundles remove all `MAIL_*` values and force the non-logging `array` mailer on-device so invitation, password-reset, and reminder mail payloads cannot enter app-private logs. Web deployments continue to use their explicitly configured mail transport.
- Native email assistance stores only up to five successfully authenticated Sutelio email addresses as Laravel-encrypted, MAC-authenticated ciphertext in a fixed application-private file. NativePHP protects the per-device `APP_KEY`; writes are atomic with `0600` file and `0700` directory permissions, tampering fails closed, and remembered addresses never cross PHP/native bridge parameters. The Android plugin requests no account/contact permission, never enumerates accounts, and receives only the address selected in the operating-system chooser; registration does not expose login history, and chooser results bypass PHP event/log channels. A CSRF-protected, validated, guest-only throttled endpoint removes one device-local address from the request body without putting it in a URL.
- Production must use `APP_DEBUG=false`, a non-placeholder app key, secure cookies/HTTPS, private storage permissions, and the documented scheduler/queue controls.

## Current Threat Review

| Area                              | Current control / evidence                                                                                             |
| --------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| Account enumeration / brute force | Fortify and API rate limits; auth feature tests                                                                        |
| Native email disclosure           | Explicit system chooser, no enumeration permission/API, bounded encrypted private-file history, registration isolation |
| Session fixation/logout           | Laravel session regeneration/invalidation tests                                                                        |
| Tenant leakage / IDOR             | policies, scoped Form Requests/actions/queries, attacker/victim web/API tests, foreign notification/task-link tests    |
| Mass assignment                   | validated explicit data passed to actions; model fillable/guarded contracts reviewed                                   |
| XSS / raw HTML                    | escaped Vue/Blade output; no trusted rich-text feature; architecture scan                                              |
| SQL/command injection             | Eloquent/query builder bindings; no user-controlled shell execution                                                    |
| SSRF / open redirect              | no server-side user-URL fetching contract; controlled application redirects                                            |
| Path traversal / private files    | configured disks, generated paths, opaque backup inventory, authorized downloads                                       |
| Replay / idempotency              | invitation digest consumption, recurrence occurrence uniqueness, reminder claim lifecycle, import/backup locks         |
| Dependency advisories             | Composer and npm lock files were upgraded; final `composer audit` and `npm audit` both report zero advisories          |

No payment or webhook integration exists. If a server-side URL fetch, rich text, payment, webhook, impersonation, or public upload feature is added, it requires a new threat model and focused tests before exposure.

Security requirements and exact verification paths are `sec-*` rows in `docs/non-functional-requirements.md` and `docs/compliance-matrix.md`.
