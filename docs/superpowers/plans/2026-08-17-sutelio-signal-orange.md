# Sutelio Signal Orange Implementation Plan

> Execute on the existing `main`. Preserve concurrent work, use failing-first tests, stage only attributable files/hunks, verify every claim, and push without rewriting history.

**Goal:** Make the application's orange system derive from the exact logo signal orange `#FF6038`, with accessible foregrounds and no semantic-status collapse.

**Architecture:** Keep `resources/css/app.css` as the Tailwind 4 CSS-first source of truth. Override the complete `--color-orange-*` scale centrally, map semantic primary/sidebar/focus tokens to brand primitives, and make only the component/default-value edits that cannot be safely solved by central tokens.

**Stack:** Laravel 13, Pest 5, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Vite 8, SQLite.

---

## Task 1: Establish The Design Contract

**Files:**

- Create: `docs/superpowers/specs/2026-08-17-sutelio-signal-orange-design.md`
- Create: `docs/superpowers/plans/2026-08-17-sutelio-signal-orange.md`
- Modify: `docs/progress.md`

1. Record the 487-use/88-file baseline and exact brand sources.
2. Record measured contrast and semantic exclusions.
3. Record concurrent light-only ownership so overlapping files are not lost or misattributed.

## Task 2: Add Failing Brand-Color Coverage

**Files:**

- Create: `tests/Feature/BrandColorTest.php`

1. Parse the canonical CSS variables and assert every palette step.
2. Assert `orange-500` maps to exact `--brand-orange`.
3. Compute WCAG luminance and require signal-orange/deep-cobalt and orange-600/white to reach `4.5:1`.
4. Prove signal-orange/white is intentionally rejected for normal text.
5. Assert Button/Checkbox semantic classes.
6. Reject exact-orange/white class pairs and legacy `#f97316`, `rgba(249,115,22`, and `rgba(234,88,12` presentation literals.
7. Run the new file and observe expected failures before production edits.

## Task 3: Install The Canonical Palette

**Files:**

- Modify: `resources/css/app.css`

1. Add `--brand-orange-50` through `--brand-orange-950`, using `--brand-orange` as the 500 anchor.
2. Expose every step through `@theme inline --color-orange-*` mappings.
3. Point primary, primary foreground, ring, and sidebar tokens to the brand primitives.
4. Preserve neutral, destructive, success, warning, information, chart, radius, shadow, motion, and light-only contracts.
5. Re-run the focused test to reach GREEN.

## Task 4: Correct Interactive Foregrounds

**Files:**

- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/checkbox/Checkbox.vue`
- Modify: exact `bg-orange-500 text-white` consumers in onboarding, calendar, notifications, and Preferences.

1. Use the accessible `orange-600`/white pair on default Button and checked Checkbox while exact signal-orange semantic surfaces retain deep-cobalt foreground.
2. Replace every exact-orange/white normal-text pair with either deep-cobalt text or the darker `orange-600`/white control treatment.
3. Preserve hover, focus-visible, disabled, forced-colors, reduced-motion, and icon states.
4. Update intentional existing design assertions.

## Task 5: Remove Legacy Accent Literals

**Files:**

- Modify: `resources/js/components/ui/sonner/Sonner.vue`
- Modify: orange-shadow consumers in shared/project/workspace/task surfaces.

1. Replace old Tailwind RGB channels with `255,96,56` while preserving alpha and geometry.
2. Re-scan all first-party presentation source for legacy orange values.
3. Keep domain data values separate unless they are a UI default/fallback.

## Task 6: Align New Project Defaults

**Files:**

- Modify: `resources/js/components/project/ProjectCreateDialog.vue`
- Modify: `resources/js/pages/onboarding/Index.vue`
- Modify: `resources/js/components/onboarding/TaskStep.vue`
- Modify: `tests/Feature/WorkspaceManagementTest.php`
- Modify: `tests/Feature/OnboardingWorkflowTest.php`

1. Change new-project and presentation fallback orange to `#ff6038`.
2. Keep every other selectable project color unchanged.
3. Do not migrate existing persisted domain colors.
4. Run focused workspace/onboarding tests.

## Task 7: Focused And Frontend Verification

1. Run Pint and scoped Prettier.
2. Run BrandColor, FrontendDesign, WorkspaceManagement, and OnboardingWorkflow tests.
3. Run all Node frontend tests, Vue type checking, ESLint, Prettier verification, npm audit, and Vite production build.
4. Confirm all existing `orange-*` consumers compile through the new scale.

## Task 8: Live Browser Verification

1. Resolve the application URL through Laravel Boost, then verify the reachable Herd URL.
2. Use an isolated headless Chrome profile if shared MCP profiles are occupied.
3. Inspect exact runtime CSS variables and computed primary Button colors.
4. Check desktop and mobile layout, no horizontal overflow, console/runtime/network failures, keyboard focus, light-only scheme, and reduced motion.
5. Capture local screenshots without committing transient browser artifacts.

## Task 9: Synchronize Canonical Evidence

**Files:**

- Modify: `docs/design-system.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/progress.md`

1. Document the exact ramp and accessible foreground rule.
2. Record zero query/route/schema/dependency delta.
3. Preserve historical entries and report exact executed checks and limitations.

## Task 10: Complete Quality Gates

1. Run Pint, Larastan, and the complete sequential Pest suite.
2. Apply all migrations and seed twice against isolated fresh SQLite; check integrity and foreign keys without touching the real database.
3. Run frontend tests, types, ESLint, Prettier, npm audit, and production build again after final documentation/source changes.
4. Run Composer strict validation, locked audit, and platform checks.
5. Build config/route/view caches, inspect routes and schedule, then clear caches.
6. Confirm email verification remains absent.

## Task 11: Review And Deliver

1. Review tests first, then correctness, readability, architecture, security, performance, and accessibility.
2. Inspect complete and staged diffs and scan for secrets.
3. Stage only phase-owned files/hunks after concurrent commits land.
4. Validate `fix(ui): align accents with Sutelio orange`.
5. Commit on `main`, fetch, prove linear ancestry, push normally, and verify local/remote equality and a clean tree.
