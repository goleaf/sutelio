# Global Language Selection Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans` to implement this plan task-by-task in the existing `main` checkout. Do not dispatch subagents; preserve every unrelated staged and unstaged change.

**Goal:** Add first-run and always-available language selection with durable guest/device and authenticated-account persistence across web and NativePHP phone/tablet use.

**Architecture:** `UserLanguage` and a focused locale service own supported metadata and resolution. A guest-safe Form Request/controller endpoint persists an encrypted cookie/session and reuses the existing preference action for authenticated users; Inertia shares one typed localization contract consumed by reusable Reka/Tailwind components in both shells.

**Tech Stack:** Laravel 13, PHP 8.5, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Reka UI, Wayfinder, Pest 5, Vite 8, NativePHP Mobile 4, SQLite.

---

### Task 1: Establish failing server-side locale contracts

**Files:**

- Create: `tests/Feature/LanguageSelectionTest.php`
- Modify: `tests/Feature/Auth/RegistrationTest.php`
- Modify: `tests/Feature/Auth/AuthenticationTest.php`

- [x] Assert first guest visit resolves `Accept-Language` for presentation but shares `localization.requires_selection = true`.
- [x] Assert a valid guest selection stores session locale, returns an encrypted long-lived cookie, and suppresses the next first-run prompt.
- [x] Assert invalid/unsupported codes do not change session, cookie, or database state.
- [x] Assert authenticated selection updates only the current user's preference and that account language outranks a conflicting device cookie.
- [x] Assert registration inherits the explicit selected language and successful login synchronizes the device choice to the saved account language.
- [x] Run `php artisan test --compact tests/Feature/LanguageSelectionTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/AuthenticationTest.php` and confirm failures identify the missing endpoint/shared contract/persistence.

### Task 2: Implement the server-authoritative locale foundation

**Files:**

- Modify: `app/Enums/UserLanguage.php`
- Create: `app/Services/LocalePreference.php`
- Modify: `app/Http/Middleware/SetLocale.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Create: `app/Http/Requests/UpdateLocaleRequest.php`
- Create: `app/Http/Controllers/LocaleController.php`
- Modify: `routes/web.php`
- Modify: `app/Actions/Fortify/CreateNewUser.php`
- Modify: `app/Http/Responses/LoginResponse.php`

- [x] Add enum metadata for native names and `/images/flags/{region}.svg` paths plus a bounded frontend option array.
- [x] Implement `LocalePreference::resolve()`, `requiresSelection()`, `remember()`, and encrypted cookie construction with the documented precedence.
- [x] Delegate middleware locale choice to the service and share `localization.current`, `requires_selection`, `options`, and bounded preview copy; use generated Wayfinder functions for the endpoint.
- [x] Add an allowlisted `language` Form Request and a throttled `PUT /locale` route grouped with prefix/name/controller conventions.
- [x] Persist authenticated changes through `UpdateUserPreferences`; persist all choices in session/cookie; return `back()` without accepting redirect input.
- [x] Validate registration language with `Rule::enum(UserLanguage::class)` and use it when creating preferences.
- [x] Attach the account locale cookie to successful Fortify login responses, including JSON responses without changing their body contract.
- [x] Re-run the focused Task 1 Pest command until green.

### Task 3: Add owned flag assets and typed frontend contract

**Files:**

- Create: `public/images/flags/gb.svg`
- Create: `public/images/flags/lt.svg`
- Create: `public/images/flags/ru.svg`
- Create: `resources/js/types/localization.ts`
- Modify: `resources/js/types/global.d.ts`
- Create: `resources/js/components/language/LanguageFlag.vue`

- [x] Add compact, project-owned, viewBox-consistent SVG flags with no scripts, external references, embedded raster content, or text.
- [x] Define `SupportedLanguage` and `LocalizationState` types matching the exact shared server shape.
- [x] Type the global Inertia `locale` and `localization` props.
- [x] Render flag URLs only from the typed server option and keep images decorative when adjacent text supplies the accessible name.
- [x] Run scoped Prettier and `npm run types` (or the repository's actual equivalent confirmed from `package.json`).

### Task 4: Build the reusable dropdown and first-run dialog failing-first

**Files:**

- Create: `resources/js/components/language/LanguageSwitcher.test.ts`
- Create: `resources/js/components/language/FirstRunLanguageDialog.vue`
- Create: `resources/js/components/language/LanguageSwitcher.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`

- [x] Add behavior/source tests for the shared server option list, generated Wayfinder route, required dialog, native-name labels, processing/error announcements, focus/escape/outside-click behavior, 44 px targets, and reduced-motion classes.
- [x] Run the new focused frontend/Pest tests and observe the expected missing-component failures.
- [x] Implement the blocking Reka dialog with selected-card state, explicit Continue action, focus trap, no close affordance, and `prevent` handlers for Escape/outside interaction.
- [x] Implement the compact dropdown with current flag/code, radio semantics, immediate server submission, spinner/disabled state, and one concise failure live region.
- [x] Use Inertia `useForm` plus the generated `locale.update` Wayfinder function; serialize requests by disabling controls while processing and do not mutate document language optimistically.
- [x] Re-run the focused tests until green.

### Task 5: Place one switcher in every page shell

**Files:**

- Modify: `resources/js/components/AppSidebarHeader.vue`
- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/pages/auth/Register.vue`

- [x] Place the switcher at the trailing edge of the authenticated sticky header without crowding breadcrumbs or the mobile sidebar trigger.
- [x] Add a safe-area-aware top row to the auth shell so login, registration, reset, confirmation, and two-factor pages all expose it.
- [x] Submit the current shared language as a hidden registration field so Fortify receives the server-authoritative selected code.
- [x] Verify onboarding inherits the authenticated shell and does not render a duplicate switcher.
- [x] Run auth, frontend design, localization, onboarding frontend, and type checks.

### Task 6: Align settings and onboarding language controls

**Files:**

- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/components/onboarding/PreferencesStep.vue`

- [x] Replace local duplicated language arrays with `page.props.localization.options`.
- [x] Render the same flag/native-name presentation in settings and onboarding selects.
- [x] Preserve the existing full-preferences save transaction and onboarding step transition while ensuring the global switcher remains the immediate language-change path.
- [x] Confirm no stale local catalog remains with `rg` and run preferences/onboarding/localization tests.

### Task 7: Add complete localized copy

**Files:**

- Modify: `lang/en/ui.php`
- Modify: `lang/lt/ui.php`
- Modify: `lang/ru/ui.php`

- [x] Add semantic keys for switcher label, first-run eyebrow/title/description, native/localized language labels, selection status, Continue, saving, and failure.
- [x] Keep key shapes and placeholders identical across all three locales and use complete sentences.
- [x] Run `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/LanguageSelectionTest.php`.

### Task 8: Focused code quality and regression checkpoint

- [x] Run `vendor/bin/pint --dirty --format agent` for phase-owned PHP.
- [x] Run scoped Prettier for phase-owned Vue/TypeScript/SVG/Markdown.
- [x] Run focused Pest: language, auth, preferences, onboarding, localization, design, navigation, and query-budget suites.
- [x] Run the exact frontend test/type/lint scripts found in `package.json`.
- [x] Confirm the locale endpoint adds no guest Eloquent query and no additional authenticated page query.

### Task 9: Live responsive browser verification

- [x] Resolve the Herd URL with Laravel Boost and use an isolated browser profile.
- [x] Verify browser-safe first visit, English/Lithuanian/Russian selection, reload persistence, and shared switcher updates; cover login, registration inheritance, authenticated precedence, logout continuity, and invalid input through deterministic Pest tests without mutating the real user database.
- [x] Verify 1440 desktop, 390x844 phone, and representative 768/820 tablet viewports with no horizontal overflow.
- [x] Verify keyboard-only operation, initial/final focus, Escape/outside-click blocking, 44 px targets, one `main`/`h1`, reduced motion, forced colors, and long translated labels.
- [x] Inspect current console, network, and Laravel/browser logs; capture screenshots as untracked evidence only.

### Task 10: NativePHP phone and tablet verification

- [x] Confirm generated NativePHP platform state and preserve concurrent regeneration outputs.
- [x] Build the current Android debug artifact using the repository's documented NativePHP/Gradle workflow.
- [ ] Install cleanly on available phone and tablet Android emulators without touching unrelated device packages/data. Blocked: the only AVD fails preflight for insufficient disk space before boot.
- [ ] Verify cold first launch, language choice, process restart persistence, registration/login account precedence, authenticated switch, and phone/tablet responsive layout. Blocked by the same unavailable Android runtime.
- [ ] Inspect `adb logcat`, process/package state, SQLite integrity, and screenshots; record exact device/API facts. Blocked because no Android device reaches `adb`.
- [x] Attempt the applicable iOS simulator build/run only when Xcode and generated iOS platform state are available; report an external tool/device blocker instead of claiming coverage.

### Task 11: Synchronize canonical documentation

**Files:**

- Modify: `docs/requirements.md`
- Modify: `docs/architecture.md`
- Modify: `docs/frontend.md`
- Modify: `docs/localization.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/testing.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/progress.md`

- [x] Add a stable requirement or extend `sys-user-002` with first-run/device/account persistence and exact evidence.
- [x] Document locale precedence, encrypted cookie scope, reusable UI, flags, mobile persistence, accessibility, and query delta.
- [ ] Append exact focused/full/browser/native verification results, limitations, files, decisions, commit, and push facts; preserve prior progress history. Verification facts are current; commit/push facts are added after delivery.
- [x] Run Prettier/Markdown link review and `git diff --check`.

### Task 12: Full gates, review, and delivery

- [x] Run Pint, Larastan, the full sequential Pest suite, isolated fresh migration/seeding/repeat seeding/integrity, and relevant query-budget checks.
- [x] Run all frontend tests, Vue types, ESLint, Prettier verification, npm audit, and production Vite build.
- [x] Run Composer strict validation/audit/platform checks and Laravel config/route/view cache plus HTTP smoke checks.
- [x] Review the implementation against the design for security, accessibility, localization, mobile behavior, query count, and future-language extensibility.
- [ ] Inspect complete and staged diffs, secrets, generated artifacts, and preservation of the concurrent light/Signal Orange/native work.
- [ ] Stage only attributable language-selection files/hunks, validate a semantic commit message, commit on `main`, fetch, prove linear ancestry, push normally, and verify local/remote equality.

## Self-Review

- Every repository-controlled design requirement maps to Tasks 1–12; Android runtime and Apple simulator checks retain explicit environmental blockers.
- Server and TypeScript names are consistent: `localization.current`, `localization.requires_selection`, `localization.options`, and `language`.
- The plan adds no database migration or dependency and explicitly covers guest, registration, login, authenticated, browser, phone, and tablet paths.
- The concurrent dirty worktree is a delivery risk, not permission to include unrelated staged changes; final publication waits for an attributable staged slice.
