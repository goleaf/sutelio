# Sutelio Responsive Style Optimization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Execute inline when repository policy does not permit delegation.

**Goal:** Deliver one supported Tailwind CSS 4 presentation system that is mobile-first, overflow-safe, touch-safe, zoom-safe, NativePHP-safe, and motion-accessible from 320px phones through wide desktops without Sass or a second styling pipeline.

**Architecture:** Keep `resources/css/app.css` as the only CSS-first Tailwind 4 entrypoint and let Tailwind/Lightning CSS own build-time imports, nesting, prefixing, and minification. Normalize browser `env(safe-area-inset-*)` and NativePHP `--inset-*` values without doubling the package's `nativephp-safe-area` body padding. Put viewport, pointer, wrapping, and motion behavior in shared primitives; migrate repeated page wrappers to one Vue presentation component; then correct only audited consumers whose intrinsic sizing, overlay bounds, or interaction targets violate the shared contract.

**Tech Stack:** Laravel 13, Inertia 3, Vue 3 with `<script setup lang="ts">`, Tailwind CSS 4.3, Lightning CSS, Reka UI, Lucide Vue, NativePHP Mobile 4.2, Pest 5, Vite 8, Chrome DevTools MCP, Playwright MCP.

---

## Execution Status — 2026-08-17

- Tasks 1-7 are implemented and verified: responsive source contracts, the CSS-first safe-area/page-frame foundation, 12 frame consumers, shared header/overlay hardening, audited horizontal regions and viewport bounds, coarse-pointer sidebar correction, the validated project skill/rule, and the complete browser matrix.
- Task 8 quality gates pass on the integrated worktree: scoped Pint, Larastan, focused/full Pest, frontend tests, Vue types, ESLint, Prettier, npm/Composer audits, isolated migration plus idempotent seeding, web build, and Android/iOS Vite builds.
- Publication is intentionally pending because local `main` currently contains separately owned commits ahead of `origin/main` plus active concurrent localization/database/UI documentation work. The responsive slice must be committed with a scoped index and pushed only after those ancestors are independently published or otherwise reconciled without attributing foreign work to this phase.
- Final integrated verification passes 182 focused design tests / 2,983 assertions, 950 full Pest tests / 37,603 assertions, and 56 frontend tests, with zero Larastan, TypeScript, ESLint, Prettier, Composer-audit, or npm-audit failures. Pest reports one warning without warning detail and zero test failures.
- Final integrated web output at the last check transformed 3,581 modules in 3.63 seconds; application CSS was 182.08 kB / 26.54 kB gzip and the application entry was 110.29 kB / 29.30 kB gzip. Android and iOS transformed 3,581 and 3,582 modules. The increase from the 3,543-module baseline includes concurrent timezone, constructor, global-operation feedback, gradient-control, motion/icon, and database work and is therefore not claimed as a responsive-only delta.

---

## Baseline And Scope

- There are no `.scss` or `.sass` files. The only stylesheet is `resources/css/app.css`; this is intentional because Tailwind CSS 4 officially does not support Sass preprocessors. Responsive styles also live as statically discoverable Tailwind classes in 244 Vue files.
- Current inventory: 99 responsive-class consumers, 80 motion consumers, 57 explicit reduced-motion consumers, 27 arbitrary-size consumers, 6 horizontal-scroll consumers, and 4 nowrap consumers.
- Twelve authenticated/onboarding/settings surfaces duplicate the same page gutter and container structure.
- The fresh baseline build transforms 3,543 modules in 6.64 seconds; main application CSS is 150.72 kB / 22.43 kB gzip and the application entry is 98.71 kB / 24.99 kB gzip.
- No query, route, schema, migration, authorization, API, localization-copy, dependency, or database change is in scope.

### Task 1: Lock The Responsive Contract With Failing Tests

**Files:**

- Modify: `tests/Feature/FrontendDesignTest.php`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] Add a source contract proving `app.css` is the only CSS-first entrypoint, unsupported preprocessors are absent, and it owns all four browser/NativePHP safe-area insets, residual inset handling, fluid page gutters, fine-pointer-only lift/icon hover, coarse-pointer 44px control bounds, reduced-motion suppression, and forced-colors-safe focus.
- [ ] Prove the Blade bootstrap retains `viewport-fit=cover` and `nativephp-safe-area` while Vite loads exactly `resources/css/app.css` as the stylesheet entrypoint.
- [ ] Add a contract for a shared `WorkspacePageFrame.vue` with one responsive frame/container implementation and an audited consumer dataset covering Dashboard, activity, calendar, notifications, projects, tasks, workspaces, onboarding, and settings.
- [ ] Add contracts proving `WorkspacePageHeader` has shrink-safe, anywhere-wrapping copy and phone-first full-width actions.
- [ ] Add contracts proving Dialog, Sheet, and workspace dialog surfaces use dynamic viewport bounds and mobile padding without raw `92vh`/unsafe `100vw` sizing.
- [ ] Run `php artisan test --compact tests/Feature/FrontendDesignTest.php` and confirm failures name only the missing responsive contracts.

### Task 2: Strengthen The Canonical CSS-First Foundation

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/js/components/ui/button/index.ts`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] Normalize top/right/bottom/left browser `env()` and NativePHP `--inset-*` values and define fluid inline/block page spacing in `:root`.
- [ ] Subtract the NativePHP-applied body inset on portrait block edges and landscape inline edges so the shared page frame adds only the residual safe area plus its normal gutter.
- [ ] Add shared page-frame utilities with logical padding, a bounded full-width container, `min-width: 0`, and safe-area-aware edges.
- [ ] Restrict lift and icon-rotation hover effects to `(hover: hover) and (pointer: fine)` while keeping press/focus feedback available.
- [ ] Give `ui-control` consumers at least 44px block/inline bounds on coarse pointers and retain the existing visual sizes on fine pointers.
- [ ] Preserve the global reduced-motion clamp and add forced-colors focus/control boundaries without creating a theme.
- [ ] Use supported native CSS nesting only where it reduces repeated control selectors without hiding cascade order.
- [ ] Run the focused design test, `npm run types:check`, and `npm run build`; record CSS size and build-time delta against the baseline.

### Task 3: Replace Repeated Page Wrappers With One Mobile-First Frame

**Files:**

- Create: `resources/js/components/shared/WorkspacePageFrame.vue`
- Modify: `resources/js/pages/Dashboard.vue`
- Modify: `resources/js/pages/activity/Index.vue`
- Modify: `resources/js/pages/calendar/Index.vue`
- Modify: `resources/js/pages/notifications/Index.vue`
- Modify: `resources/js/pages/projects/Index.vue`
- Modify: `resources/js/pages/projects/Show.vue`
- Modify: `resources/js/pages/tasks/Index.vue`
- Modify: `resources/js/pages/tasks/Show.vue`
- Modify: `resources/js/pages/workspaces/Index.vue`
- Modify: `resources/js/pages/workspaces/Show.vue`
- Modify: `resources/js/pages/onboarding/Index.vue`
- Modify: `resources/js/layouts/settings/Layout.vue`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] Implement a presentation-only single-root component with typed class props and no application state.
- [ ] Migrate the exact repeated wrapper inventory while preserving each page's existing content, headings, props, and layout ownership.
- [ ] Keep onboarding's intentional content shape through a class override instead of a parallel frame.
- [ ] Prove the legacy wrapper literal no longer exists in audited consumers.
- [ ] Run the focused Pest test, Vue type checking, ESLint, and scoped Prettier verification.

### Task 4: Harden Shared Headers And Overlays

**Files:**

- Modify: `resources/js/components/shared/WorkspacePageHeader.vue`
- Modify: `resources/js/components/shared/LeadingIconHeading.vue`
- Modify: `resources/js/components/shared/WorkspaceDialogContent.vue`
- Modify: `resources/js/components/ui/dialog/DialogContent.vue`
- Modify: `resources/js/components/ui/dialog/DialogScrollContent.vue`
- Modify: `resources/js/components/ui/sheet/SheetContent.vue`
- Modify: `resources/js/components/AppHeader.vue`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] Add `min-w-0` and `wrap-anywhere` at shared title/description boundaries so long EN/LT/RU strings participate correctly in intrinsic sizing.
- [ ] Make header actions a full-width single-column phone layout that becomes content-sized and wrapping at larger widths.
- [ ] Replace unsafe fixed viewport expressions with `dvh`/available-height constraints, safe-area padding, and width bounds that remain usable at 320px and 200% zoom.
- [ ] Keep close controls at 44px and prevent them from obscuring dialog copy at the smallest viewport.
- [ ] Run the focused design test and all frontend static gates.

### Task 5: Correct The Audited High-Risk Consumers

**Files:**

- Modify: `resources/js/components/activity/ActivityFilterPanel.vue`
- Modify: `resources/js/components/project/ProjectTaskFilters.vue`
- Modify: `resources/js/components/task/TaskFilterBar.vue`
- Modify: `resources/js/components/task/BoardView.vue`
- Modify: `resources/js/components/calendar/CalendarPeriodNavigator.vue`
- Modify: `resources/js/components/shared/WorkspaceSegmentedControl.vue`
- Modify only additional first-party Vue consumers demonstrated by the inventory/browser tests.
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] Replace `92vh` mobile-sheet bounds with the shared dynamic viewport contract.
- [ ] Preserve intentional horizontal scrolling for boards and segmented controls, add touch pan/overscroll affordances, and prevent the document itself from overflowing.
- [ ] Recheck fixed/minimum widths and nowrap use; keep only intentional bounded cases and add `min-w-0`, max-width, or wrapping where the intrinsic size can escape its parent.
- [ ] Re-run the complete source inventory and record every retained arbitrary width as an intentional overlay/chart/board constraint.
- [ ] Run focused Pest, frontend tests, type checking, lint, format, and production build.

### Task 6: Create The Reusable Project Skill And Durable Rule

**Files:**

- Create: `.agents/skills/tailwind-native-responsive/SKILL.md`
- Create: `.agents/skills/tailwind-native-responsive/agents/openai.yaml`
- Modify through Laravel Boost: `.ai/rules/resources.md`
- Test: `/Users/andrejprus/.codex/skills/.system/skill-creator/scripts/quick_validate.py`

- [ ] Initialize the skill with the official skill-creator script and project-local `.agents/skills` destination.
- [ ] Teach the single-entry CSS-first boundary, required docs/rules lookup, responsive inventory, NativePHP residual safe-area rule, TDD sequence, browser matrix, performance comparison, and delivery gates without duplicating generic Tailwind documentation.
- [ ] Generate matching UI metadata and validate the skill folder with `quick_validate.py`.
- [ ] Use Laravel Boost `record-rule` to preserve the no-Sass Tailwind 4 boundary for `resources/**`.

### Task 7: Verify Every Resolution And Accessibility Mode In Real Browsers

**Files:**

- Modify: `docs/progress.md`
- Modify only if current evidence changes: `docs/design-system.md`, `docs/frontend.md`, `docs/accessibility.md`, `docs/performance.md`

- [ ] Use isolated Chrome DevTools MCP and Playwright MCP against the Herd URL; do not reuse personal browser profiles.
- [ ] Verify representative guest/authenticated routes at 320x568, 360x800, 390x844, 430x932, 768x1024, 820x1180, 1024x768, 1280x800, 1440x1000, and 1920x1080.
- [ ] At each relevant viewport check document width versus viewport width, one `main`, one `h1`, page-frame gutters, overlay bounds, focus visibility, action wrapping, and fresh console/network errors.
- [ ] Repeat critical routes at 200% zoom, reduced motion, forced colors, coarse pointer/touch emulation, landscape phone orientation, and dark OS preference while confirming the application remains light-only.
- [ ] Compare final CSS/JS/build time with the recorded baseline and reject unexplained asset growth.

### Task 8: Complete Quality, Documentation, And Delivery Gates

**Files:**

- Modify: `docs/progress.md`
- Modify: `docs/design-system.md`
- Modify: `docs/frontend.md`
- Modify affected canonical documents only when implementation evidence requires it.

- [ ] Run `vendor/bin/pint --dirty --format agent`, Larastan, focused and full Pest, frontend tests, Vue types, ESLint, Prettier, npm audit, Composer validation/audit, isolated migration/seed checks, and final web build as applicable.
- [ ] Run `npm run build:android` and `npm run build:ios` to verify both NativePHP Vite modes without generating or installing a physical-device APK.
- [ ] Inspect `git status`, the complete diff, staged diff, generated assets, lock files, and secrets; preserve unrelated `docs/progress.md` work with scoped staging if it remains present.
- [ ] Commit coherent, independently revertible slices on `main` using semantic messages; fetch before each normal push and never rewrite history.
- [ ] Record exact test counts, bundle sizes, browser matrix results, commit hashes, push result, limitations, and next work without presenting unexecuted checks as passing.
