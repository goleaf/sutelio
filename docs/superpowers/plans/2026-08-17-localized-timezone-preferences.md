# Localized Timezone Preferences Implementation Plan

> **For Codex:** Execute this plan in thin RED/GREEN slices with the `test-driven-development`, `incremental-implementation`, and `verification-before-completion` skills.

**Goal:** Make timezone selection searchable, region-grouped, and localized in onboarding and account preferences while applying locale-specific week-start defaults and browser-native timezone detection.

**Architecture:** `UserLanguage` remains the source of truth for locale defaults. A new `TimeZoneRegion` enum owns stable IANA-region grouping and translated group labels, while a focused `TimeZoneCatalog` service turns PHP's canonical timezone identifiers into localized ICU display options. One shared Reka UI combobox consumes the same typed catalog in onboarding and settings. The browser reports only its system IANA identifier; Laravel validates every persisted value.

**Tech Stack:** Laravel 13, PHP 8.5 with optional ICU, Inertia 3, Vue 3, TypeScript, Reka UI 2.10, Tailwind CSS 4, Pest 5, Node test runner.

**Execution status:** Tasks 1-6 are implemented through verified RED/GREEN slices. Task 7 final complete gates, rendered browser matrix, documentation evidence, scoped commit, and push remain in progress.

---

## Acceptance Matrix

| Surface                | EN                                         | LT                                | RU                                | Keyboard / screen reader                                             | Phone / tablet / desktop                                    |
| ---------------------- | ------------------------------------------ | --------------------------------- | --------------------------------- | -------------------------------------------------------------------- | ----------------------------------------------------------- |
| Onboarding preferences | Sunday default; Monday selectable          | Monday default; Sunday selectable | Monday default; Sunday selectable | Search input, listbox navigation, visible group labels, empty result | No horizontal overflow; touch target and scrollable results |
| Settings preferences   | Same defaults when language changes        | Same                              | Same                              | Same shared combobox                                                 | Same shared combobox                                        |
| Registration           | Browser IANA timezone submitted when valid | Same                              | Same                              | Hidden progressive enhancement only                                  | Browser/NativePHP system timezone                           |
| Global language switch | Persist locale week default                | Same                              | Same                              | Existing switcher behavior retained                                  | Existing responsive behavior retained                       |

## Task 1: Lock enum and catalog contracts with failing Pest coverage

**Files:**

- Create: `tests/Feature/LocalizedTimezonePreferencesTest.php`
- Modify: `tests/Feature/LanguageSelectionTest.php`
- Modify: `tests/Feature/Auth/RegistrationTest.php`
- Create later in GREEN: `app/Enums/TimeZoneRegion.php`
- Create later in GREEN: `app/Services/TimeZoneCatalog.php`
- Modify later in GREEN: `app/Enums/UserLanguage.php`

- [x] Assert `en => sunday`, `lt => monday`, and `ru => monday` through `UserLanguage::defaultWeekStart()`.
- [x] Assert the timezone catalog contains a localized Europe group and `Europe/Vilnius` option in all three languages.
- [x] Assert every canonical PHP timezone appears exactly once and `UTC` has a translated explicit group.
- [x] Assert an authenticated locale change persists both language and its locale default week start.
- [x] Assert registration accepts a valid detected timezone and creates locale-specific week defaults.
- [x] Run the focused tests and observe failures caused by missing contracts.

## Task 2: Implement enum-owned defaults and the localized server catalog

**Files:**

- Modify: `app/Enums/UserLanguage.php`
- Create: `app/Enums/TimeZoneRegion.php`
- Create: `app/Services/TimeZoneCatalog.php`
- Modify: `app/Http/Controllers/LocaleController.php`
- Modify: `app/Http/Requests/UpdateLocaleRequest.php`
- Modify: `app/Actions/Fortify/CreateNewUser.php`

- [x] Add `UserLanguage::defaultWeekStart()` and expose it as `default_week_start` in the existing frontend enum catalog.
- [x] Map all `DateTimeZone::listIdentifiers()` values through `TimeZoneRegion`, including the top-level `UTC` identifier.
- [x] Generate translated region labels from EN/LT/RU catalogs and translated timezone display names through `IntlTimeZone::DISPLAY_GENERIC_LOCATION`, with a safe humanized fallback.
- [x] Include a current UTC offset and the original IANA identifier as search/display metadata without changing the persisted value.
- [x] Save the locale week default on authenticated language changes and new registration.
- [x] Keep Laravel's `timezone:all` validation as the server-authoritative boundary.
- [x] Run the focused Pest tests to GREEN, then Pint on dirty PHP files.

## Task 3: Lock browser detection and UI source contracts with failing tests

**Files:**

- Create: `resources/js/lib/timezone.ts`
- Create: `resources/js/lib/timezone.test.ts`
- Modify: `tests/Feature/FrontendLocalizationTest.php`
- Create later in GREEN: `resources/js/components/preferences/TimezoneCombobox.vue`

- [x] Test that browser detection accepts a canonical IANA identifier and rejects missing or invalid values.
- [x] Add source-level regression coverage requiring the shared timezone component to use the Reka combobox/listbox primitives, groups, labels, empty state, and translated search copy.
- [x] Run Node/Pest focused tests and observe the intended failures.

## Task 4: Implement one shared accessible grouped autocomplete

**Files:**

- Create: `resources/js/components/preferences/TimezoneCombobox.vue`
- Create: `resources/js/types/timezone.ts`
- Modify: `resources/js/components/onboarding/PreferencesStep.vue`
- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/components/onboarding/onboarding-types.ts`
- Modify: `resources/js/pages/onboarding/Index.vue`

- [x] Use controlled `ComboboxRoot`/`ComboboxInput` with full keyboard navigation and Reka's built-in locale-insensitive filtering.
- [x] Render visible `ComboboxGroup` and `ComboboxLabel` headings; show localized empty/search hints and the technical IANA identifier as secondary text.
- [x] Preserve the selected string identifier and surface `aria-invalid`, disabled, focus, reduced-motion, and bounded-scroll states.
- [x] Replace both raw timezone selects with the shared component.
- [x] Apply the selected language option's enum-provided `default_week_start` to the local onboarding/settings draft.
- [x] Run frontend tests, Vue type checking, scoped ESLint, Prettier, and focused Pest tests.

## Task 5: Add browser-native timezone detection without an IP dependency

**Files:**

- Modify: `resources/js/pages/auth/Register.vue`
- Modify: `resources/js/components/onboarding/PreferencesStep.vue`
- Modify: `resources/js/composables/useLanguagePreference.ts` only if callback coordination is needed.

- [x] Resolve `Intl.DateTimeFormat().resolvedOptions().timeZone` in a side-effect-free utility.
- [x] Submit it as a hidden registration field and report a matching detected value during onboarding without replacing persisted preferences.
- [x] Never overwrite a user's explicit timezone selection and keep `UTC` as the no-JavaScript/invalid-detection fallback.
- [x] Run registration/onboarding/frontend focused tests to GREEN.

## Task 6: Translate and expose the grouped catalog

**Files:**

- Modify: `app/Http/Controllers/OnboardingController.php`
- Modify: `app/Http/Controllers/Settings/PreferencesController.php`
- Modify: `lang/en/ui.php`, `lang/lt/ui.php`, `lang/ru/ui.php`
- Modify: `resources/js/types/localization.ts`

- [x] Replace raw timezone arrays with the typed localized catalog on both pages.
- [x] Add parity-checked search, empty, detected, and region-label translations for EN/LT/RU.
- [x] Confirm a locale redirect regenerates catalog labels in the newly selected language.
- [x] Run localization parity and Inertia prop tests.

## Task 7: Synchronize canonical docs and verify delivery

**Files:**

- Modify: `docs/requirements.md`
- Modify: `docs/architecture.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Append: `docs/progress.md`

- [x] Record native device detection, server validation, enum defaults, ICU localization, shared combobox ownership, and no external IP/API dependency.
- [ ] Run focused tests after each slice, then Pint, Larastan, full sequential Pest, frontend tests, Vue types, ESLint check, Prettier check, npm audit, production build, Composer validation/audit, and isolated SQLite migrate/seed/health gates.
- [ ] Verify onboarding and settings in isolated Chrome DevTools and Playwright sessions for EN/LT/RU at phone, tablet, and desktop widths, including keyboard search, no-results, 200% zoom/reflow, reduced motion, forced colors, and console/network errors.
- [ ] Inspect the complete and staged diffs, stage only this phase through a temporary index if concurrent edits remain, commit semantically on `main`, fetch/recheck linearity, push normally, and verify remote equality.

## Rollback

Revert the feature commit. No schema, stored identifier format, route, policy, external API, package, or production database change is introduced; existing timezone and week-start columns remain compatible.
