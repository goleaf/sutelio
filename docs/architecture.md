# Architecture

## Runtime Shape

Laravel owns routing, Fortify/Sanctum authentication, authorization, validation, transactions, persistence, scheduling, files, notifications, versioned JSON, and Inertia page responses. Vue 3 pages use `<script setup lang="ts">`, the Composition API, typed Inertia props, Pinia only for genuine shared state, Wayfinder-generated routes/actions, Reka-based components, and Tailwind CSS 4. SQLite is the sole relational database. NativePHP packages the same Laravel application for mobile with its embedded runtime.

NativePHP Mobile 4.2 continues to host the existing Inertia/Vue application in its supported WebView architecture; the optional beta SuperNative renderer is not a second application architecture. Both generated platform shells are ephemeral and rebuilt with `native:install --force`, after which the deterministic project command reapplies Sutelio identity, assets, and Android log/bundle hardening. Native appearance is fixed to light and the v4 FPS overlay is opt-in only.

## Product And Package Identity

Sutelio is the single active product identity. Deterministic tracked brand inputs generate the stripe-free clean-S browser mark, one-color wordmark, raster icons, Android adaptive/monochrome resources, and native splash imagery. Android's external `applicationId` and the primary iOS bundle identifier are `com.goleaf.sutelio`; the custom deep-link scheme is `sutelio`.

The generated Android `namespace = "com.nativephp.mobile"` is an internal NativePHP/JNI integration contract and must not be renamed to the external application ID. This active architecture decision supersedes contrary namespace language in historical Sutelio design and implementation plans. Both external mobile identifiers create a new operating-system sandbox: the application does not claim automatic access to or migration from the previous package's private SQLite files.

Source and reproducible NativePHP generation satisfy `sys-brand-001`, and Task 9 supplies the completed application/data-safety baseline. Successor Tasks 18-22 provide the browser matrix, post-design complete gates, final APK/emulator evidence, the verified in-place GitHub repository/checkout/Herd transition, and successful physical Samsung installation on Android 10 / API 29. None is an application-runtime shortcut or a replacement repository.

## Module Boundaries

- Identity: registration/login/reset/passkeys/two-factor/profile/preferences; email verification is intentionally absent.
- Guided onboarding: authenticated-user entry gate, resumable lifecycle, scoped domain composition, replay, and Dashboard continuation.
- Workspace administration: workspaces, membership, invitations, ownership, statuses, and priorities.
- Planning: projects, tasks, hierarchy, bulk operations, list/dashboard/calendar.
- Collaboration: checklists, comments, labels, tags, attachments, activity, notifications.
- Automation: recurrence generation, reminder claiming/delivery/failure/cancellation.
- Data operations: versioned import preview/execution, streamed export, SQLite backup/restore/health.
- API: `/api/v1` controllers/resources/errors/abilities reusing the same actions and policies.

## Request And Domain Flow

```text
route + middleware
  -> authorized Form Request (or policy-authorized parameterless command)
  -> thin web/API controller
  -> one injected action for a write OR one scoped query object for a read
  -> Eloquent/SQLite transaction and explicit side effects
  -> Inertia props/redirect OR API Resource/stable error envelope
```

Routes contain no product queries. Controllers do not call controllers or branch between Inertia and JSON presentation. Actions represent one operation and own short transactional boundaries. Services exist for substantial reusable domain or integration logic, not one-line framework wrappers. Query objects start from an authorized user/workspace relation and own eager loading, aggregates, deterministic ordering, and pagination. API Resources serialize only already-loaded data. Policies do not query when supplied loaded relationships can decide.

The notification inbox follows the same read boundary: the request normalizes URL state, the query starts from the authenticated user's notification relation and batches task-destination authorization, the resource emits a query-free typed presentation contract, and Vue renders only the resulting semantic fields. Read-one/all remain separate idempotent actions.

Guided onboarding is a mandatory web state machine immediately after authentication and a gate on every authenticated presentation boundary. Registration first uses `EnsureUserHasWorkspace` inside the same transaction to retain an authorized membership or create one localized personal workspace with owner membership and canonical task definitions; the web response selects it in the session. The shared completion middleware runs after authentication but before throttling, route-model binding, authorization, and Inertia/controller execution: pending web requests redirect to onboarding, pending versioned API requests receive `409 onboarding_required`, and logout remains available. Only exact named onboarding-flow actions, locale selection, sign-out, and signed invitation acceptance bypass the required gate; similarly prefixed post-onboarding actions do not. The retired skip URL is not routable, and previously skipped incomplete rows are reopened as fresh mandatory runs. The onboarding query returns bounded user-authorized options; completion reuses the same idempotent workspace invariant, and explicit guided workspace/project/task creation records run-scoped request keys in `onboarding_operations`. Replay keeps the completion gate open for already completed users, exposes only a replay-specific exit, and never deletes existing domain data; projects and tasks are never fabricated by bootstrap or completion.

## State And Ownership

Workspace membership is the tenant boundary. Every project, task, taxonomy, activity, and child-resource identifier is resolved through the route workspace, task, or parent aggregate. Exact submitted sets must have the same cardinality as the authorized reload before mutation. Frontend visibility mirrors policies but never replaces them.

Inertia props are immutable inputs. Editable Vue drafts synchronize by durable entity identity and reset when the task/workspace changes. Async interactions expose action-specific progress, duplicate prevention, validation, recoverable failure, and completion. Wayfinder is the only application route-generation mechanism.

Locale resolution is request-scoped and follows one explicit precedence chain: authenticated account preference, encrypted long-lived device cookie, session, `Accept-Language`, then English fallback. One validated endpoint updates session/cookie state and, when authenticated, the existing `UserPreference` through the canonical action. Its named web limiter is isolated by authenticated user or hashed guest session instead of shared IP, while the private loopback NativePHP Android/iOS bridge is not throttled because it cannot represent distinct remote actors. `UserLanguage` owns EN/LT/RU week-start defaults, while `TimeZoneRegion` and `TimeZoneCatalog` expose canonical IANA identifiers as translated region groups and localized display options. Registration may submit the browser/NativePHP system timezone, but Laravel's timezone validator remains authoritative and UTC remains the fallback. The server shares the current locale, first-run state, extensible option catalog, owned flag URLs, bounded first-run preview copy, and page-scoped timezone catalog through Inertia; the same Laravel/session boundary is packaged by NativePHP for phone and tablet. PHP ICU enhances server labels when present, while browser `Intl` supplies the translated visible option fallback for the tracked mobile runtime that omits PHP ICU.

The application bootstrap provides the authenticated `AppLayout`; onboarding pages instead resolve through a dedicated navigation-free layout and therefore retain exactly one `main` landmark without mounting the sidebar, authenticated header, workspace switcher, or application navigation. Onboarding step changes transfer focus to the connected step heading and validation transfers focus to one concise summary. Ordinary record create/edit/duplicate/detail operations use dedicated Inertia pages; destructive record decisions use the in-flow `PageConfirmPanel`, which scrolls into view and focuses its heading without a focus-trapping overlay. First-run language selection and passkey/two-factor security ceremonies remain specialized dialog exceptions, while `GlobalBusyOverlay` remains the permanent operation-feedback boundary. Successful foreground Inertia visits that do not explicitly preserve position reset both the document and every marked scroll region after rendering; onboarding step changes, completion/exit, ordinary route changes, and task pagination all opt into this page-start contract.

## Data And Runtime Processes

SQLite uses foreign keys, WAL, configured synchronous/busy/cache/checkpoint behavior, local filesystem placement, and an application health command. Migrations are SQLite-compatible and populated-safe. Recurrence and reminder processing is bounded and idempotent; reminder claims use persisted lifecycle state. Database-backed queues/scheduling are allowed and require deployment configuration, but Redis is not required.

Files use configured disks and generated names. Avatars, attachments, and backups are private. Import is bounded and transactional; exports stream; backups are SQLite-consistent and restore under authorization, password confirmation, integrity validation, and locking.

## Frontend And Design

The fixed Warm Precision system uses semantic CSS variables, shared primitives, large rounded light surfaces, orange focus/action accents, mobile-first responsive layouts, shared reduced-motion-safe transitions, and one light color mode. Its comfort baseline keeps primary-workflow reading text at 16 px, secondary/helper text at 15 px, coarse-pointer targets at 48×48 px, and large primary actions at 52 px. Phone and tablet navigation use the same drawer through 1023 px; the persistent collapsible sidebar begins at 1024 px. Semantic status channels own readable foregrounds; user-configurable task-definition colors may remain as non-text decoration but cannot override foreground contrast. Tailwind configuration is CSS-first through the Vite plugin. Every task date and date/time entry point composes the shared Reka UI and `@internationalized/date` field, preserves the existing date/minute request shapes, follows locale and week-start preferences, closes after a day selection, distinguishes weekend columns in Warm Precision styling, fills the safe-area-aware phone viewport, remains bounded on tablet/desktop, and stays contained inside the nearest specialized dialog when one owns the field. User-facing copy comes from shared English/Lithuanian/Russian catalogs; shared auth and authenticated shells expose the same locale selector and react to successful Inertia locale changes without a parallel client translation store. Third-party client failures cross an explicit adapter that maps typed errors to semantic catalog keys and a localized safe fallback; package or browser exception text is never a presentation contract. Older dark/system appearance requirements are superseded and must not reintroduce a runtime theme family.

Livewire, Volt, and Flux are not architectural layers. Adding them would create a second page/state/request/component/testing/localization system and violates the repository contract.

## PHP 8.5 Applicability

| Feature                      | Decision                                                  | Reason / location / evidence                                                                                             |
| ---------------------------- | --------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| Web runtime on PHP 8.5       | Used and verified                                         | Herd PHP 8.5.8 runs the site, full/parallel Pest suites, Artisan caches, SQLite health, and HTTP smokes                  |
| URI extension                | Candidate only for future user-controlled URL integration | No current server-side URL-fetch or normalization domain requires a new abstraction                                      |
| Clone-with                   | Not applicable                                            | No materially improved immutable wither found in current domain objects                                                  |
| Pipe operator                | Not applicable                                            | Current action/query flows are clearer as methods/collections; the conservative Composer envelope still includes PHP 8.4 |
| `#[NoDiscard]`               | Available but not currently needed                        | Web and the tracked mobile runtime use PHP 8.5, but no existing return-value contract benefits from the attribute        |
| `#[Override]`                | Available but not currently needed                        | Current inheritance contracts are already explicit and tested                                                            |
| `array_first` / `array_last` | Prefer Laravel `Arr` methods                              | Laravel 13 documents polyfill/helper conflicts, and the existing helpers keep compatibility intent clear                 |
| Persistent cURL share        | Not applicable                                            | No measured external HTTP connection-reuse requirement                                                                   |

## Laravel 13 Feature Applicability

| Feature                                   | Decision                                           | Evidence / reason                                                                             |
| ----------------------------------------- | -------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| Modern bootstrap middleware/exceptions    | Used                                               | `bootstrap/app.php`, API exception contract tests                                             |
| Origin-aware request forgery protection   | Framework defaults preserved and verified          | No cross-site embedded workflow justifies relaxation; CSRF/origin security tests remain green |
| Controller/authorization/queue attributes | Evaluate only on materially changed endpoints/jobs | Existing middleware/policies are explicit and tested; avoid decorative migration              |
| API Resources                             | Used                                               | `app/Http/Resources`, API contract suite                                                      |
| JSON:API Resources                        | Not applicable                                     | Existing clients use a stable custom `/api/v1` envelope, not full JSON:API                    |
| `Cache::touch`                            | Not applicable                                     | No application cache item needs sliding expiration                                            |
| AI/vector/semantic search                 | Not applicable                                     | No product requirement or provider contract                                                   |
| Realtime infrastructure                   | Not applicable                                     | Notifications/reminders use database state and scheduled delivery                             |
| Image manipulation additions              | Not applicable                                     | Current avatar validation/storage does not require transformed variants                       |

Important decisions are also captured under `docs/decisions` when they cannot be derived directly from requirements.

## Modernization Decisions Applied

- Eloquent strict mode is enabled outside production; projection and two-factor access paths now handle intentionally missing attributes explicitly instead of disabling strictness.
- Providers/controllers receive collaborators through dependency injection. Route endpoint closures and first-party service-locator calls were removed from the modified application layer.
- API and Inertia Resources expose explicit safe fields; the shared user payload no longer serializes private avatar storage paths.
- Activity reads use validated URL filters, workspace-scoped contributor checks, safe resource presentation, deterministic pagination, and server aggregates.
- The Blade shell is presentation-only; no color-mode bootstrap is needed for the fixed light design, and automated architecture tests reject Blade PHP/data/service access.
