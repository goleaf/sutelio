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

- [ ] Assert first guest visit resolves `Accept-Language` for presentation but shares `localization.requires_selection = true`.
- [ ] Assert a valid guest selection stores session locale, returns an encrypted long-lived cookie, and suppresses the next first-run prompt.
- [ ] Assert invalid/unsupported codes do not change session, cookie, or database state.
- [ ] Assert authenticated selection updates only the current user's preference and that account language outranks a conflicting device cookie.
- [ ] Assert registration inherits the explicit selected language and successful login synchronizes the device choice to the saved account language.
- [ ] Run `php artisan test --compact tests/Feature/LanguageSelectionTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/AuthenticationTest.php` and confirm failures identify the missing endpoint/shared contract/persistence.

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

- [ ] Add enum metadata for native names and `/images/flags/{region}.svg` paths plus a bounded frontend option array.
- [ ] Implement `LocalePreference::resolve()`, `requiresSelection()`, `remember()`, and encrypted cookie construction with the documented precedence.
- [ ] Delegate middleware locale choice to the service and share `localization.current`, `requires_selection`, `options`, and generated endpoint URL.
- [ ] Add an allowlisted `language` Form Request and a throttled `PUT /locale` route grouped with prefix/name/controller conventions.
- [ ] Persist authenticated changes through `UpdateUserPreferences`; persist all choices in session/cookie; return `back()` without accepting redirect input.
- [ ] Validate registration language with `Rule::enum(UserLanguage::class)` and use it when creating preferences.
- [ ] Attach the account locale cookie to successful Fortify login responses, including JSON responses without changing their body contract.
- [ ] Re-run the focused Task 1 Pest command until green.

### Task 3: Add owned flag assets and typed frontend contract

**Files:**

- Create: `public/images/flags/gb.svg`
- Create: `public/images/flags/lt.svg`
- Create: `public/images/flags/ru.svg`
- Create: `resources/js/types/localization.ts`
- Modify: `resources/js/types/global.d.ts`
- Create: `resources/js/components/language/LanguageFlag.vue`

- [ ] Add compact, project-owned, viewBox-consistent SVG flags with no scripts, external references, embedded raster content, or text.
- [ ] Define `SupportedLanguage` and `LocalizationState` types matching the exact shared server shape.
- [ ] Type the global Inertia `locale` and `localization` props.
- [ ] Render flag URLs only from the typed server option and keep images decorative when adjacent text supplies the accessible name.
- [ ] Run scoped Prettier and `npm run types` (or the repository's actual equivalent confirmed from `package.json`).

### Task 4: Build the reusable dropdown and first-run dialog failing-first

**Files:**

- Create: `resources/js/components/language/LanguageSwitcher.test.ts`
- Create: `resources/js/components/language/FirstRunLanguageDialog.vue`
- Create: `resources/js/components/language/LanguageSwitcher.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`

- [ ] Add behavior/source tests for the shared server option list, generated Wayfinder route, required dialog, native-name labels, processing/error announcements, focus/escape/outside-click behavior, 44 px targets, and reduced-motion classes.
- [ ] Run the new focused frontend/Pest tests and observe the expected missing-component failures.
- [ ] Implement the blocking Reka dialog with selected-card state, explicit Continue action, focus trap, no close affordance, and `prevent` handlers for Escape/outside interaction.
- [ ] Implement the compact dropdown with current flag/code, radio semantics, immediate server submission, spinner/disabled state, and one concise failure live region.
- [ ] Use Inertia `useForm` plus the generated `locale.update` Wayfinder function; cancel superseded requests and do not mutate document language optimistically.
- [ ] Re-run the focused tests until green.

### Task 5: Place one switcher in every page shell

**Files:**

- Modify: `resources/js/components/AppSidebarHeader.vue`
- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/pages/auth/Register.vue`

- [ ] Place the switcher at the trailing edge of the authenticated sticky header without crowding breadcrumbs or the mobile sidebar trigger.
- [ ] Add a safe-area-aware top row to the auth shell so login, registration, reset, confirmation, and two-factor pages all expose it.
- [ ] Submit the current shared language as a hidden registration field so Fortify receives the server-authoritative selected code.
- [ ] Verify onboarding inherits the authenticated shell and does not render a duplicate switcher.
- [ ] Run auth, frontend design, localization, onboarding frontend, and type checks.

### Task 6: Align settings and onboarding language controls

**Files:**

- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/components/onboarding/PreferencesStep.vue`

- [ ] Replace local duplicated language arrays with `page.props.localization.options`.
- [ ] Render the same flag/native-name presentation in settings and onboarding selects.
- [ ] Preserve the existing full-preferences save transaction and onboarding step transition while ensuring the global switcher remains the immediate language-change path.
- [ ] Confirm no stale local catalog remains with `rg` and run preferences/onboarding/localization tests.

### Task 7: Add complete localized copy

**Files:**

- Modify: `lang/en/ui.php`
- Modify: `lang/lt/ui.php`
- Modify: `lang/ru/ui.php`

- [ ] Add semantic keys for switcher label, first-run eyebrow/title/description, native/localized language labels, selection status, Continue, saving, and failure.
- [ ] Keep key shapes and placeholders identical across all three locales and use complete sentences.
- [ ] Run `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/LanguageSelectionTest.php`.

### Task 8: Focused code quality and regression checkpoint

- [ ] Run `vendor/bin/pint --dirty --format agent` for phase-owned PHP.
- [ ] Run scoped Prettier for phase-owned Vue/TypeScript/SVG/Markdown.
- [ ] Run focused Pest: language, auth, preferences, onboarding, localization, design, navigation, and query-budget suites.
- [ ] Run the exact frontend test/type/lint scripts found in `package.json`.
- [ ] Confirm the locale endpoint adds no guest Eloquent query and no additional authenticated page query.

### Task 9: Live responsive browser verification

- [ ] Resolve the Herd URL with Laravel Boost and use an isolated browser profile.
- [ ] Verify first visit, English/Lithuanian/Russian selection, reload persistence, login, registration inheritance, authenticated change, logout/relaunch, and invalid request recovery.
- [ ] Verify 1440 desktop, 390x844 phone, and representative 768/820 tablet viewports with no horizontal overflow.
- [ ] Verify keyboard-only operation, initial/final focus, Escape/outside-click blocking, 44 px targets, one `main`/`h1`, reduced motion, forced colors, and long translated labels.
- [ ] Inspect current console, network, and Laravel/browser logs; capture screenshots as untracked evidence only.

### Task 10: NativePHP phone and tablet verification

- [ ] Confirm generated NativePHP platform state and preserve concurrent regeneration outputs.
- [ ] Build the current Android debug artifact using the repository's documented NativePHP/Gradle workflow.
- [ ] Install cleanly on available phone and tablet Android emulators without touching unrelated device packages/data.
- [ ] Verify cold first launch, language choice, process restart persistence, registration/login account precedence, authenticated switch, and phone/tablet responsive layout.
- [ ] Inspect `adb logcat`, process/package state, SQLite integrity, and screenshots; record exact device/API facts.
- [ ] Attempt the applicable iOS simulator build/run only when Xcode and generated iOS platform state are available; report an external tool/device blocker instead of claiming coverage.

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

- [ ] Add a stable requirement or extend `sys-user-002` with first-run/device/account persistence and exact evidence.
- [ ] Document locale precedence, encrypted cookie scope, reusable UI, flags, mobile persistence, accessibility, and query delta.
- [ ] Append exact focused/full/browser/native verification results, limitations, files, decisions, commit, and push facts; preserve prior progress history.
- [ ] Run Prettier/Markdown link review and `git diff --check`.

### Task 12: Full gates, review, and delivery

- [ ] Run Pint, Larastan, the full sequential Pest suite, isolated fresh migration/seeding/repeat seeding/integrity, and relevant query-budget checks.
- [ ] Run all frontend tests, Vue types, ESLint, Prettier verification, npm audit, and production Vite build.
- [ ] Run Composer strict validation/audit/platform checks and Laravel config/route/view cache plus HTTP smoke checks.
- [ ] Review the implementation against the design for security, accessibility, localization, mobile behavior, query count, and future-language extensibility.
- [ ] Inspect complete and staged diffs, secrets, generated artifacts, and preservation of the concurrent light/Signal Orange/native work.
- [ ] Stage only attributable language-selection files/hunks, validate a semantic commit message, commit on `main`, fetch, prove linear ancestry, push normally, and verify local/remote equality.

## Self-Review

- Every design requirement maps to Tasks 1–12; no placeholder or deferred implementation remains.
- Server and TypeScript names are consistent: `localization.current`, `localization.requires_selection`, `localization.options`, and `language`.
- The plan adds no database migration or dependency and explicitly covers guest, registration, login, authenticated, browser, phone, and tablet paths.
- The concurrent dirty worktree is a delivery risk, not permission to include unrelated staged changes; final publication waits for an attributable staged slice.
