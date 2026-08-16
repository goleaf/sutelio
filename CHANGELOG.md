# Changelog

All notable project changes are documented here.

## Unreleased

### Runtime And Dependencies

- Moved the Herd web/development runtime and CI to PHP 8.5 while retaining `>=8.4 <8.6` compatibility for NativePHP Mobile 4.2's embedded PHP 8.4 runtime.
- Updated Laravel to 13.25, Inertia Laravel to 3.3.1, Fortify to 1.38, Sanctum to 4.3.3, Wayfinder to 0.1.21, Boost to 2.5.3, Pest to 5.1.1/PHPUnit 13.3, NativePHP Mobile to 4.2, Vite to 8.2.1, Laravel Vite plugin to 3.2, Vue to 3.5.41, and compatible frontend tooling.
- Re-ran complete stable dependency resolution on 2026-08-16 and upgraded PHPStan from 2.2.5 to 2.2.8. Every direct Composer and npm package remains at its latest mutually compatible release; newer Guzzle, Workerman, Brick Math, PHPUnit, TypeScript, and Node-type majors remain behind explicit upstream framework/runtime peer constraints rather than unsafe overrides.
- Regenerated Composer/npm locks and cleared all eight Composer and four npm baseline advisories; removed unused frontend dependencies and retained Axios only for NativePHP's adapter contract.

### Architecture And Security

- Enabled strict Eloquent behavior outside production and corrected partial-model projections, locale/request state, private avatar serialization, route endpoint closures, and service-locator usage exposed by the stricter contract.
- Added a typed user-scoped notification request/query/resource boundary with semantic filters, deterministic pagination, safe legacy payloads, batched authorization for task destinations, and idempotent read mutations.
- Added typed activity category/filter/query/resource boundaries, workspace-safe activity filtering, bounded infinite scrolling, and two rollback-safe workspace-leading activity indexes.
- Added a typed project operations request/query/resource boundary with workspace-scoped filter identifiers, server-enforced archived-project creation rules, localized task definitions, deferred partial-reload queries, and identity-matched scroll refreshes.
- Reduced the Inertia Blade shell to presentation-only markup, moved theme boot logic to a first-party external asset, and expanded automated guards against Blade PHP/data/service calls, Livewire/Volt, unsafe environment access, debug calls, route action closures, and dynamic Tailwind interpolation.

### Interface, Accessibility, And Localization

- Centralized authenticated document semantics in the persistent application shell: ten nested page/settings `main` landmarks were removed, reusable task detail now begins at `h2`, and automated plus 22-route desktop/mobile checks enforce one `main` and one `h1` per page.
- Consolidated repeated container, radius, shadow, and semantic status values into the Tailwind 4 CSS-first Warm Precision token system.
- Rebuilt notifications as a responsive Structured Signal Stream with URL-backed status/kind filters, Today/Earlier grouping in the saved timezone, shared localized row/browser content, focused pending/empty states, and accessible pressed-button groups.
- Added responsive, keyboard-accessible activity intelligence filters and verified critical routes at desktop/mobile widths, reduced motion, dark media, and forced colors without overflow or current console errors.
- Rebuilt project detail as a responsive operations workspace with a bounded task queue, URL-backed desktop/mobile filters, complete-project metrics, priority and attention summaries, accessible task/project actions, and mobile-first information order.
- Preserved stable English, Lithuanian, and Russian translation keys and locale-aware presentation across changed interfaces.

### Data, Seeding, And Tests

- Completed valid factories for all 17 models with 30 meaningful states/helpers and a 55-case factory/state/seeder contract.
- Added a non-destructive, production-guarded `DemoSeeder`; fresh migration and repeated deterministic seeding complete with valid foreign keys.
- Added six rollback-safe project-operation indexes with production-query-plan coverage for workspace/project filters and position, due-date, updated, and priority-definition sorts.
- Replaced brittle/example-only checks with semantic architecture and application coverage. The final suite passes 706 tests with 10,027 assertions sequentially and in parallel. All 38 frontend tests, types, lint, format, Larastan level 7, Pint, and the production build pass.
- Upgraded NativePHP's generated Android project and verified a debug APK with minSdk 31 and targetSdk 36. Production signing remains a release-environment responsibility.

### Documentation

- Established the permanent repository engineering contract, stable functional/non-functional requirement IDs, compliance matrix, architecture decisions, current-state/final evidence, data/security/frontend/design/accessibility/localization/testing/seeding/performance/operations/deployment contracts, and genuine external limitations.
- Preserved the July audits, completed plans, and historical progress rather than rewriting release history.
