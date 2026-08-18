# Senior-Friendly Responsive System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver one substantially more readable, touch-confident, and responsive Sutelio interface for older adults across web, Android phones, and tablets without introducing a separate accessibility mode.

**Architecture:** Strengthen shared typography, target-size, dialog, sheet, page-frame, and sidebar contracts first; then adapt complete user workflows in vertical slices. Preserve Inertia/Wayfinder requests, immutable props, workspace authorization, EN/LT/RU localization, NativePHP safe areas, and the fixed light-only Warm Precision system.

**Tech Stack:** Laravel 13.25, PHP 8.5, Inertia 3.6, Vue 3.5, TypeScript, Pinia, Tailwind CSS 4.3, Reka UI, Pest 5, Vite, NativePHP Mobile 4.2, SQLite.

---

## Audit Baseline

- 24 pages, 236 Vue components, eight layouts, 105 responsive Vue files, and one CSS entry.
- Fresh guest matrix: no horizontal overflow from 320 through 1024 px; Lighthouse accessibility 96, best practices 100; LCP 629 ms, CLS 0.00; no console warning/error and no failed request.
- Confirmed gaps: 11.2-14 px primary-workflow text, 44 px primary actions, 18 px checkbox, 20-28 px sidebar actions, desktop sidebar beginning immediately above 768 px, one selected-language contrast failure at 4.39:1, 150 raw status-color utilities, and excessive radius/shadow/uppercase/truncation use.
- The apparent short-landscape `dvh` failure was a Playwright resize artifact. Mobile Chrome correctly constrains and internally scrolls the dialog; the real defect is a hidden-below-fold primary action and avoidable scroll burden.

## File Map

- `resources/css/app.css`: shared comfort variables, coarse-pointer targets, safe reflow, and short-landscape layout.
- `resources/js/components/ui/{button,input,label,checkbox,dialog,sheet,sidebar}/**`: reusable control and overlay behavior.
- `resources/js/components/localization/FirstRunLanguageDialog.vue`, `resources/js/layouts/auth/AuthSimpleLayout.vue`, `resources/js/pages/auth/**`: first-run and authentication comfort.
- `resources/js/components/shared/{WorkspacePageFrame,WorkspacePageHeader,FilterSheet,ResponsiveSectionNavigation,StatusNotice}.vue`: page-level responsive contracts.
- `resources/js/components/{AppSidebar,AppSidebarHeader,NavMain,NavUser}.vue`: authenticated phone/tablet/desktop shell.
- `resources/js/components/onboarding/**`, `resources/js/pages/onboarding/Index.vue`: mandatory onboarding.
- `resources/js/components/{task,project}/**`, `resources/js/pages/{tasks,projects}/**`: core daily work.
- `resources/js/pages/{Dashboard,calendar,activity,notifications,workspaces,settings}/**`: remaining product surfaces.
- `lang/{en,lt,ru}/**`: semantic copy added by each vertical slice.
- `tests/Feature/{FrontendDesignTest,FrontendMotionIconSystemTest,LanguageSelectionFrontendTest,OnboardingPageTest}.php` and focused workflow tests: source and behavior contracts.
- `resources/js/**/*.test.ts`: pure responsive state/control behavior where a state helper is introduced.
- `docs/plans/2026-08-18-senior-friendly-responsive-system-design.md`, canonical UI/accessibility docs, and `docs/progress.md`: decisions and factual evidence.

### Task 1: Freeze The Audit And Design Contract

**Files:**

- Create: `docs/plans/2026-08-18-senior-friendly-responsive-system-design.md`
- Create: `docs/superpowers/plans/2026-08-18-senior-friendly-responsive-system.md`
- Modify: `docs/progress.md`

- [x] **Step 1: Record repository and browser evidence**

Record exact inventory counts, viewport geometry, text/target measurements, Lighthouse failure, performance trace, console/network state, responsive breakpoint behavior, and the corrected `dvh` diagnosis.

- [x] **Step 2: Record the selected design and rejected alternatives**

Specify one comfortable default, 16/15 px type floors, 48/52 px target floors, 1024 px desktop-navigation boundary, semantic states, less truncation/decoration, full EN/LT/RU coverage, and the complete verification matrix.

- [x] **Step 3: Verify documentation structure and formatting**

Run:

```bash
npx prettier --check docs/plans/2026-08-18-senior-friendly-responsive-system-design.md docs/superpowers/plans/2026-08-18-senior-friendly-responsive-system.md docs/progress.md
git diff --check
```

Expected: both commands exit zero.

### Task 2: Establish Shared Reading And Touch Primitives

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/input/Input.vue`
- Modify: `resources/js/components/ui/label/Label.vue`
- Modify: `resources/js/components/ui/checkbox/Checkbox.vue`
- Modify: `resources/js/components/PasswordInput.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [x] **Step 1: Write failing shared-contract coverage**

Add a Pest test that reads the six production files and requires these exact contracts:

```php
test('shared controls provide the comfort touch and reading baseline', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('min-height: 3rem')
        ->toContain('min-width: 3rem')
        ->and(File::get(resource_path('js/components/ui/button/index.ts')))
        ->toContain('h-12 px-6 text-base pointer-coarse:min-h-13')
        ->and(File::get(resource_path('js/components/ui/input/Input.vue')))
        ->toContain('h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('md:text-sm')
        ->and(File::get(resource_path('js/components/ui/label/Label.vue')))
        ->toContain('text-base')
        ->and(File::get(resource_path('js/components/ui/checkbox/Checkbox.vue')))
        ->toContain('size-5')
        ->toContain('pointer-coarse:size-6')
        ->and(File::get(resource_path('js/components/PasswordInput.vue')))
        ->toContain('min-w-12')
        ->toContain('pointer-coarse:min-w-13');
});
```

- [x] **Step 2: Run RED**

Run:

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php --filter='shared controls provide the comfort'
```

Expected: failure on the old 44 px, 14 px, 18 px, or responsive-downscaled contracts.

- [x] **Step 3: Implement the minimal shared baseline**

Apply these exact production changes:

```css
@media (pointer: coarse) {
    .ui-control {
        min-width: 3rem;
        min-height: 3rem;
    }
}
```

Use `h-12 px-6 text-base pointer-coarse:min-h-13` for large buttons, `h-12 ... text-base pointer-coarse:min-h-13` for inputs, `text-base` for labels, `size-5 pointer-coarse:size-6` for checkbox visuals, and `min-w-12 pointer-coarse:min-w-13` for the password visibility action. Preserve existing focus rings, disabled/loading behavior, colors, and semantic elements.

- [x] **Step 4: Run GREEN and primitive regressions**

Run:

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/FrontendMotionIconSystemTest.php
npm run types:check
npm run lint
```

Expected: all commands pass with no warning/error.

### Task 3: Adapt First Run And Authentication

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/js/components/localization/FirstRunLanguageDialog.vue`
- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/pages/auth/Login.vue`
- Modify: `resources/js/components/PasskeyVerify.vue`
- Modify: `tests/Feature/LanguageSelectionFrontendTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [x] **Step 1: Write failing first-run/auth contracts**

Require `data-slot="first-run-language-dialog"`, header/form slots, `text-base` descriptions, 15 px language secondary copy, `min-h-13` confirmation, a 48 px login checkbox label, 16 px auth links/footer, and the `@media (orientation: landscape) and (max-height: 32rem)` two-column layout.

- [x] **Step 2: Run RED**

Run:

```bash
php artisan test --compact tests/Feature/LanguageSelectionFrontendTest.php tests/Feature/FrontendDesignTest.php --filter='first run|authentication comfort'
```

Expected: failure because the current dialog is vertical in short landscape and guest copy remains 11.2-14 px.

- [x] **Step 3: Implement the short-landscape composition**

Add stable slots and this shared CSS contract:

```css
@media (orientation: landscape) and (max-height: 32rem) and (min-width: 40rem) {
    [data-slot='first-run-language-dialog'] {
        grid-template-columns: minmax(14rem, 0.8fr) minmax(22rem, 1.2fr);
        width: min(48rem, calc(100% - 1rem));
        max-width: min(48rem, calc(100% - 1rem));
        gap: 0;
    }

    [data-slot='first-run-language-header'] {
        border-right: 1px solid var(--color-border);
        border-bottom: 0;
        padding: 1rem 1.25rem;
    }

    [data-slot='first-run-language-form'] {
        align-content: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
    }

    [data-slot='first-run-language-option'] {
        min-height: 3.25rem;
        padding-block: 0.5rem;
    }
}
```

Use `text-base` for dialog/auth descriptions and auth links, `text-[0.9375rem]` for secondary/eyebrow/separator copy, `min-h-13` for confirmation, and `min-h-12` on the login checkbox label. Remove uppercase from guest eyebrow/separator presentation.

- [x] **Step 4: Run GREEN and real-browser matrix**

Run the two focused Pest files, then use isolated Chrome DevTools and Playwright at 320×568, 390×844, 430×932, 768×1024, 820×1180, 1024×768, and 844×390. Assert zero horizontal overflow, every action reachable, no clipped focus ring, full keyboard operation, no console warning/error, and selected secondary language contrast at least 4.5:1.

### Task 4: Move Persistent Navigation To The Desktop Boundary

**Files:**

- Modify: `resources/js/components/ui/sidebar/SidebarProvider.vue`
- Modify: `resources/js/components/ui/sidebar/utils.ts`
- Modify: `resources/js/components/ui/sidebar/index.ts`
- Modify: `resources/js/components/ui/sidebar/SidebarGroupAction.vue`
- Modify: `resources/js/components/ui/sidebar/SidebarMenuAction.vue`
- Modify: `resources/js/components/ui/sidebar/SidebarMenuSubButton.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [x] **Step 1: Add failing breakpoint and touch-target regressions**

Require `(max-width: 1023px)`, `SIDEBAR_WIDTH_MOBILE = "min(22rem, calc(100dvi - 2rem))"`, 48 px coarse-pointer menu/submenu rows, and no hover-only action visibility on coarse pointers. Preserve the existing Inertia `start` listener and unmount cleanup.

- [x] **Step 2: Run RED**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php --filter='tablet navigation'
```

Expected: the current 768 px JavaScript boundary and 20-28 px action sizes fail.

- [x] **Step 3: Implement the tablet drawer contract**

Set `useMediaQuery("(max-width: 1023px)")`, widen the drawer with the exact bounded `min()` expression, add `pointer-coarse:min-h-12 pointer-coarse:min-w-12` to action primitives, add `pointer-coarse:h-12 pointer-coarse:text-base` to submenu rows, and disable hover-only opacity hiding under coarse input while preserving desktop collapsed tooltips.

- [x] **Step 4: Verify authenticated navigation**

Use a disposable product-created account. Verify phone and tablet drawer open/dismiss/navigation/focus behavior at 390, 768, 820, and 1023 px; verify persistent desktop collapse behavior at 1024 and 1440 px; delete the account through the product UI and confirm no residue with a read-only Boost query.

- [x] **Step 5: Review and deliver the responsive foundation**

Review the complete shared/auth/navigation diff, run the focused and complete source gates, commit `feat(ui): establish responsive comfort foundation`, push normally, and prove local/remote equality before beginning onboarding implementation.

### Task 5: Adapt Mandatory Onboarding

**Files:**

- Modify: `resources/js/pages/onboarding/Index.vue`
- Modify: `resources/js/components/onboarding/OnboardingShell.vue`
- Modify: `resources/js/components/onboarding/*Step.vue`
- Modify: `resources/js/components/preferences/TimezoneCombobox.vue`
- Modify: `resources/js/components/ui/select/{SelectTrigger,SelectItem}.vue`
- Modify: `tests/Feature/OnboardingFrontendTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `tests/Feature/GradientControlSystemTest.php`

- [x] **Step 1: Add failing flow contracts**

Require one visible primary action, 52 px coarse target, 16 px body copy, 15 px helper copy, wrap-safe translated labels, a fixed mobile action region with canonical `--safe-area-inset-bottom` padding, validation focus movement after the global busy state releases, complete offline/error notices, and no required-flow skip affordance.

- [x] **Step 2: Run RED, implement shared onboarding shell behavior, and run GREEN**

Use the existing `StatusNotice`, Button, Field, and page-frame primitives; keep all mutations in the existing Inertia path and preserve mandatory completion middleware. Run focused onboarding Pest, EN/LT/RU source contracts, frontend tests, type checking, lint, and 320/390/768/820 browser flows.

- [x] **Step 3: Commit the mandatory-onboarding slice**

After Task 5 passes its full diff/browser gates:

```bash
git add resources/css/app.css resources/js/components resources/js/layouts/auth resources/js/pages/auth resources/js/pages/onboarding tests docs
git commit -m "feat(onboarding): adapt mandatory setup for touch"
git push origin main
```

Stage only attributable files, inspect the staged diff, and verify local/remote equality after the normal push.

### Task 6: Adapt Tasks And Projects

**Files:**

- Modify: `resources/js/pages/tasks/{Index,Show}.vue`
- Modify: `resources/js/components/task/**`
- Modify: `resources/js/pages/projects/{Index,Show}.vue`
- Modify: `resources/js/components/project/**`
- Modify: focused task/project Pest and frontend tests

- [x] **Step 1: Inventory every task/project state and write failing contracts**

Cover list, board, search, filters, pagination, creation, editing, detail, comments, checklist, members, labels/tags, validation, empty, loading, processing, offline, and destructive confirmation in EN/LT/RU.

- [x] **Step 2: Implement mobile-first daily-work layouts**

Use one-column phone rows with two-line titles, visible status text/icon, 48 px completion and menu targets, a labeled mobile filter Sheet, visible active-filter summary, tablet two-column layouts only above a readable container width, scroll-safe dialogs, and sticky mobile form actions. Keep every request, Wayfinder route, policy, workspace scope, and query count unchanged.

- [x] **Step 3: Verify and deliver the daily-work slice**

Run task/project focused Pest, query budgets, frontend tests, type checking, lint, Prettier, Vite build, authenticated browser matrix, diff review, then commit `feat(ui): adapt task and project workflows` and push normally.

### Task 7: Adapt Dashboard, Calendar, Activity, And Notifications

**Files:**

- Modify: `resources/js/pages/Dashboard.vue`
- Modify: `resources/js/pages/calendar/Index.vue`
- Modify: `resources/js/pages/activity/Index.vue`
- Modify: `resources/js/pages/notifications/Index.vue`
- Modify: related feature components and focused tests

- [x] **Step 1: Write failing readable-summary and interaction contracts**

Require 16/15 px text, 48 px filters/actions, wrap-safe metric labels, phone single-column priority order, tablet two-column composition, readable calendar event access without hover, explicit unread/status text, and complete empty/loading/error/offline states.

- [x] **Step 2: Implement with existing page/header/metric/filter primitives**

Do not add queries or change API/Inertia payloads. Reduce nested panels, repeated shadows, and decorative rings where they do not convey hierarchy.

- [x] **Step 3: Verify and deliver**

Run focused endpoint/query-budget tests, frontend gates, the authenticated viewport/input/locale matrix, then commit `feat(ui): adapt planning and activity surfaces` and push normally.

### Task 8: Adapt Workspaces And Settings

**Files:**

- Modify: `resources/js/pages/workspaces/{Index,Show}.vue`
- Modify: `resources/js/components/workspace/**`
- Modify: `resources/js/layouts/settings/Layout.vue`
- Modify: `resources/js/pages/settings/**`
- Modify: related dialogs, responsive navigation, translations, and focused tests

- [ ] **Step 1: Write failing management contracts**

Require accessible member/invitation controls, wrap-safe roles/names/emails, 48/52 px actions, phone card/reflow behavior, tablet two-column settings layout, responsive section navigation, separated destructive actions, and authorization-preserving confirmation.

- [ ] **Step 2: Implement without changing data/security boundaries**

Reuse `WorkspaceDialogContent`, `WorkspaceConfirmDialog`, `ResponsiveSectionNavigation`, Field, StatusNotice, and current policies/actions. No inline request, query, authorization, or business logic moves into Vue.

- [ ] **Step 3: Verify and deliver**

Run focused workspace/settings/security tests, query budgets, frontend/browser gates, then commit `feat(ui): adapt workspace and settings management` and push normally.

### Task 9: Consolidate Semantic Statuses And Remove Confirmed Legacy UI

**Files:**

- Modify: `resources/css/app.css`
- Modify: status-consuming Vue files identified by the inventory
- Delete only after zero-consumer proof: `resources/js/components/AppHeader.vue`, `resources/js/components/NavFooter.vue`, `resources/js/components/task/TaskStats.vue`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: design-system/accessibility/current-state documentation

- [ ] **Step 1: Add failing semantic-color and reachability contracts**

Require shared success/warning/information/destructive surface/text/border classes, text/icon status meaning, 4.5:1 normal-text contrast, and zero imports/templates/routes/tests referencing each deletion candidate.

- [ ] **Step 2: Replace raw state colors workflow by workflow**

Keep brand orange and user-chosen project colors distinct from status tokens. Run the focused page/browser contract after each replacement group to catch accidental semantic changes.

- [ ] **Step 3: Delete only proven unreachable components**

Use exact-path removal after `rg` proves zero consumer outside each file. Keep recoverability through Git history and record the deleted paths in `docs/progress.md`.

- [ ] **Step 4: Verify and deliver**

Run the complete frontend/Pest/browser matrix, then commit `refactor(ui): consolidate responsive status system` and push normally.

### Task 10: Final Quality, NativePHP, And Physical Samsung Delivery

**Files:**

- Modify: `docs/requirements.md`
- Modify: `docs/non-functional-requirements.md`
- Modify: `docs/architecture.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/design-system.md`
- Modify: `docs/progress.md`

- [ ] **Step 1: Run all source and dependency gates**

```bash
vendor/bin/pint --dirty --format agent
composer run types:check
php artisan test --compact
npm run test:frontend
npm run types:check
npm run lint
npx prettier --check resources docs
composer validate --strict
composer audit
npm audit
npm run build
git diff --check
```

Expected: every available gate exits zero; unavailable coverage tooling is reported as unavailable rather than passing.

- [ ] **Step 2: Verify a fresh isolated SQLite lifecycle**

Create an allowlisted temporary SQLite file, apply all migrations, seed twice, run application database health, and move only the exact temporary database plus WAL/SHM siblings recoverably to Trash. Never migrate, seed, reset, or replace the configured real database.

- [ ] **Step 3: Run the complete browser matrix**

Use disposable isolated Chrome DevTools and Playwright profiles for guest, registration, mandatory onboarding, dashboard, tasks, projects, calendar, activity, notifications, workspaces, and settings. Cover the design viewport matrix, 200% zoom/reflow, keyboard, touch, reduced motion, forced colors, offline/recovery, EN/LT/RU, long content, console/network, Lighthouse accessibility, and representative performance traces.

- [ ] **Step 4: Build and inspect Android before touching hardware**

Prepare the NativePHP bundle, reapply deterministic branding/hardening, clean Gradle output, assemble the ARM64 debug APK, restore the normal web build last, and verify package identity, SDK floor/target, signer, alignment, migrations, asset manifest, archive integrity, forbidden payloads, and sensitive literals.

- [ ] **Step 5: Validate the exact APK on a clean emulator**

Cold-install, complete first-run/registration/onboarding/task creation in Russian and one alternate locale, test portrait/landscape and large Android font scaling, relaunch, inspect SQLite integrity/migrations and process-scoped fatal/ANR/sensitive logs, then stop the emulator.

- [ ] **Step 6: Review, commit, push, and prove equality**

Perform five-axis review, inspect complete/staged diffs, commit remaining canonical evidence with a semantic message, push `origin main` normally, fetch, and prove `HEAD`, `origin/main`, and remote advertisement equality with a clean tracked tree.

- [ ] **Step 7: Install on exactly one authorized physical Samsung as the final mutation**

Resolve exactly one physical device and exactly one Samsung in ADB `device` state, assign that validated value to `SUTELIO_DEVICE_SERIAL`, and re-read the final artifact hash. Run `adb -s "$SUTELIO_DEVICE_SERIAL" install -r --no-streaming`, cold-launch twice, verify resumed activity, exact installed APK hash, database integrity/migrations, and process-scoped logs. Preserve application data unless the product owner explicitly requests a reset, touch no other package, and leave Sutelio running.

## Completion Criteria

The program is complete only when every task checkbox is factually closed, canonical documentation matches current behavior, no P0/P1 responsive or accessibility defect remains, every supported locale and state is verified, the final source is pushed to `origin/main`, and the exact verified APK is running on the explicitly resolved physical Samsung.
