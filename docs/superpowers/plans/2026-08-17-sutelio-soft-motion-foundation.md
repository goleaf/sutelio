# Sutelio Soft Motion Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. Do not delegate unless repository policy explicitly authorizes it.

**Goal:** Replace generic 480 ms page/Card/list choreography and broad/layout-heavy transitions with a measured CSS-first motion foundation that is soft, interruptible, reduced-motion safe, and ready for later domain-specific animation packets.

**Architecture:** `resources/css/app.css` owns duration/easing and low-level control/state contracts. Shared primitives own their transition properties; generic surfaces remain static. Domain journeys may add later state-specific motion, but this packet removes unclassified entrances first and leaves no consumer dependent on hidden-on-entry content.

**Tech Stack:** Tailwind CSS 4.3, Vue 3.5, TypeScript 6, Reka UI 2.10, `tw-animate-css` 1.2, Pest 5.1, Vite 8.2, isolated Chrome DevTools MCP, and isolated Playwright MCP.

---

## Entry Gate

Do not edit source until all conditions are true:

- Package 1 P1 accessibility and request-outcome changes have attributable commits, or the master program records an exact non-overlapping exception;
- responsive CSS/page-frame work has one attributable commit or an explicitly recorded owner boundary;
- gradient-control work has settled Button/Input/Textarea/Select/Checkbox/OTP classes;
- global-operation feedback has settled `GlobalBusyOverlay.vue` and its CSS hooks;
- localized-timezone work has settled overlapping onboarding/settings files;
- current `main`, staged/unstaged ownership, and remote ancestry are recorded;
- the current motion/icon test is reconciled so icon semantics remain and obsolete generic-motion expectations can change independently.

If any condition fails, stop before Task 1 and update `docs/progress.md` with the exact blocker. Do not use a temporary index to conceal overlapping edits.

## Current Inventory To Refresh At Execution

The planning snapshot contains:

- eight `transition-all` files;
- eight layout-transition files plus in-progress `GlobalBusyOverlay.vue`;
- 27 generic `ui-enter`/`ui-surface`/`ui-page-surface`/`ui-stagger` files;
- 95 motion/transition consumers and 61 explicit reduced-motion consumers.

Refresh with:

```bash
rg -n -g '*.vue' -g '*.ts' -- 'transition-all' resources/js
rg -n -g '*.vue' -g '*.ts' -- 'transition-\[(width|height|max-height|grid-template|top|right|bottom|left|margin|padding)' resources/js
rg -n -g '*.vue' -g '*.ts' -- 'ui-enter|ui-surface|ui-page-surface|ui-stagger' resources/js
rg -l -g '*.vue' -g '*.ts' -- 'motion-reduce:|prefers-reduced-motion' resources/js | sort
```

Any changed dataset must be explained by a commit/diff, not silently replaced.

### Task 1: Rebaseline And Lock The Failing Motion Contract

**Files:**

- Modify append-only: `docs/progress.md`
- Modify: `tests/Feature/FrontendMotionIconSystemTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Record the execution snapshot**

Run:

```bash
git status --short
git log --oneline --decorate -15
git rev-list --left-right --count origin/main...main
```

Append current HEAD, origin, ownership, inventory counts, and exact overlapping files to `docs/progress.md`.

- [ ] **Step 2: Replace obsolete generic-motion assertions**

Keep all IconTile, heading, accessible-icon, fixed-light, and raw-brand-color assertions. Replace assertions requiring `ui-enter`, `ui-surface`, `ui-page-surface`, or generic `ui-stagger` with this contract:

```php
test('the soft motion foundation owns named duration and easing roles', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('--motion-snap: 90ms;')
        ->toContain('--motion-feedback: 130ms;')
        ->toContain('--motion-state: 190ms;')
        ->toContain('--motion-spatial: 260ms;')
        ->toContain('--motion-signature: 340ms;')
        ->toContain('--ease-standard: cubic-bezier(0.2, 0, 0, 1);')
        ->toContain('--ease-emphasized: cubic-bezier(0.16, 1, 0.3, 1);')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('generic surfaces do not animate merely because they render', function () {
    $files = [
        resource_path('js/components/ui/card/Card.vue'),
        resource_path('js/components/shared/WorkspacePageHeader.vue'),
        resource_path('js/components/ui/sidebar/SidebarInset.vue'),
        resource_path('js/layouts/auth/AuthSimpleLayout.vue'),
    ];

    foreach ($files as $file) {
        expect(File::get($file), $file)
            ->not->toContain('ui-enter')
            ->not->toContain('ui-surface')
            ->not->toContain('ui-page-surface');
    }
});

test('first party controls do not use broad transitions', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true)) {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('transition-all');
    }
});
```

- [ ] **Step 3: Add an explicit geometry-transition allowlist**

Add a dataset containing only the final sidebar files whose geometry changes the actual layout/hit box. Progress/chart/busy fills are not allowlisted.

```php
dataset('measured layout transition exceptions', [
    'sidebar shell' => 'components/ui/sidebar/Sidebar.vue',
    'sidebar label' => 'components/ui/sidebar/SidebarGroupLabel.vue',
    'sidebar menu button' => 'components/ui/sidebar/index.ts',
]);
```

The corresponding test must assert named properties and reject `transition-all`.

- [ ] **Step 4: Run RED**

Run:

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php
```

Expected: failures for absent new tokens, existing generic surface classes, current `transition-all`, and unapproved layout transitions. Icon, localization, and brand assertions must remain green.

- [ ] **Step 5: Inspect the test diff**

Run:

```bash
git diff -- tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php
git diff --check -- tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php
```

Expected: only motion expectations change; no icon/accessibility coverage is weakened.

### Task 2: Add The Named Motion Tokens Without Changing Consumers

**Files:**

- Modify: `resources/css/app.css`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Replace the duration variables**

Merge this exact motion contract into the existing `:root`; preserve every unrelated theme variable already present in that block:

```css
:root {
    --motion-snap: 90ms;
    --motion-feedback: 130ms;
    --motion-state: 190ms;
    --motion-spatial: 260ms;
    --motion-signature: 340ms;
    --ease-standard: cubic-bezier(0.2, 0, 0, 1);
    --ease-emphasized: cubic-bezier(0.16, 1, 0.3, 1);
    --ease-exit: cubic-bezier(0.4, 0, 1, 1);
}
```

Do not map ordinary feedback to the old 480 ms token. Temporary aliases are allowed only inside this task and must be gone before its commit.

- [ ] **Step 2: Retune `ui-control`**

Keep the existing explicit property list and change duration to `var(--motion-feedback)`. Use `scale(0.985)` for active press. Keep hover transform inside the existing fine-hover media query and reduced motion at stable transform.

- [ ] **Step 3: Preserve reduced-motion safety**

Keep the global `0.01ms` safety clamp, one iteration, zero delay, and stable transforms. Do not add an opacity delay that can hide content.

- [ ] **Step 4: Run the token test**

Run:

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php --filter='soft motion foundation owns'
```

Expected: PASS.

- [ ] **Step 5: Verify the production compiler**

Run:

```bash
npm run build
```

Expected: production build passes without a new dependency or dynamic-class warning.

- [ ] **Step 6: Commit the token slice**

```bash
git add resources/css/app.css tests/Feature/FrontendMotionIconSystemTest.php
# Stage only this packet's append-only progress hunk through the repository's
# scoped-index workflow; never stage the complete shared docs/progress.md file.
git commit -m "refactor(ui): define soft motion tokens"
```

### Task 3: Remove Automatic Motion From Generic Surfaces

**Files:**

- Modify: `resources/js/components/ui/card/Card.vue`
- Modify: `resources/js/components/shared/WorkspacePageHeader.vue`
- Modify: `resources/js/components/ui/sidebar/SidebarInset.vue`
- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Remove consumer classes**

Remove only these class tokens:

```text
Card.vue: ui-surface
WorkspacePageHeader.vue: ui-enter
SidebarInset.vue: ui-page-surface
AuthSimpleLayout.vue: ui-page-surface
```

Do not alter spacing, border, shadow, safe area, layout, or auth behavior.

- [ ] **Step 2: Delete generic CSS ownership**

Remove `.ui-page-surface > *`, `.ui-enter`, and `.ui-surface` animation rules plus the obsolete `@keyframes ui-enter` only after no remaining intentional consumer requires that keyframe.

- [ ] **Step 3: Run focused GREEN**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php --filter='generic surfaces do not animate'
npm run types:check
npm run format:check
```

Expected: all pass.

- [ ] **Step 4: Browser-check stable first paint**

Using an isolated browser, reload login, dashboard, projects, and task detail with animations slowed 10×. Expected: content is visible immediately, no page-child sequence runs, focus is available without waiting, and layout does not shift.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css resources/js/components/ui/card/Card.vue resources/js/components/shared/WorkspacePageHeader.vue resources/js/components/ui/sidebar/SidebarInset.vue resources/js/layouts/auth/AuthSimpleLayout.vue tests/Feature/FrontendMotionIconSystemTest.php
git commit -m "refactor(ui): keep generic surfaces motionless"
```

### Task 4: Remove Generic Stagger From Static Collections

**Files:** refresh before editing; planning snapshot initially includes the 27 consumers listed by the inventory command.

- [ ] **Step 1: Add a failing no-generic-stagger test**

```php
test('static collections do not inherit a generic stagger', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'vue'], true)) {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('ui-stagger');
    }
});
```

- [ ] **Step 2: Remove `ui-stagger` from static initial collections**

Remove it from shell navigation, calendar initial views, dashboard queues, project/workspace indexes, task panels, workspace member/configuration lists, settings backup history, and initial notification groups. Remove the related boolean/class plumbing from `NotificationFeed.vue` when it exists only to animate initial data.

- [ ] **Step 3: Remove it from onboarding initial content**

Remove generic stagger from Welcome, Safety, Product Map, Results, Checklist, and first-run options. Later domain packets may introduce a bounded `ui-sequence` only when a user-triggered step change reveals at most six related children.

- [ ] **Step 4: Delete `.ui-stagger` CSS and delays**

Remove the selector, nth-child delays, and obsolete keyframe only after `rg` finds zero consumers.

- [ ] **Step 5: Verify**

```bash
rg -n -g '*.vue' -g '*.ts' -- 'ui-stagger' resources/js
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php --filter='static collections do not inherit'
npm run types:check
npm run lint:check
```

Expected: `rg` returns no matches; checks pass.

- [ ] **Step 6: Commit**

Stage only the files reported by the refreshed dataset and commit:

```bash
git commit -m "refactor(ui): remove generic collection stagger"
```

### Task 5: Replace Broad Control Transitions

**Files:** planning snapshot:

- Modify: `resources/js/components/TwoFactorRecoveryCodes.vue`
- Modify: `resources/js/components/localization/FirstRunLanguageDialog.vue`
- Modify: `resources/js/components/shared/WorkspaceSegmentedButton.vue`
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/input-otp/InputOTPSlot.vue`
- Modify: `resources/js/components/ui/sidebar/SidebarRail.vue`
- Modify: `resources/js/components/workspace/WorkspaceManagementNavigation.vue`
- Modify: `resources/js/layouts/settings/Layout.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Refresh the exact eight-file dataset**

Run:

```bash
rg -l -g '*.vue' -g '*.ts' -- 'transition-all' resources/js | sort
```

Stop if gradient/global-operation commits changed ownership; update paths from their committed diff first.

- [ ] **Step 2: Apply explicit property ownership**

Use these contracts:

```text
Button: remove transition-all; ui-control already owns the explicit list.
OTP: color, background-color, border-color, box-shadow.
First-run and segmented/navigation controls: color, background-color, border-color, box-shadow, transform.
Recovery-code reveal: opacity, transform.
Sidebar rail: color only; geometry is immediate and pointer-fine only.
```

Use `var(--motion-feedback)` for control feedback and `var(--motion-state)` for recovery/state reveal. Do not disturb settled gradient classes.

- [ ] **Step 3: Run GREEN**

```bash
rg -n -g '*.vue' -g '*.ts' -- 'transition-all' resources/js
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php --filter='first party controls do not use broad transitions'
npm run types:check
npm run lint:check
```

Expected: no `transition-all` match; all checks pass.

- [ ] **Step 4: Commit**

```bash
git commit -m "perf(ui): narrow control transition properties"
```

### Task 6: Replace Visual Progress Layout Motion And Audit Sidebar Exceptions

**Files:**

- Modify: `resources/js/components/dashboard/ProductivityChart.vue`
- Modify: `resources/js/components/onboarding/OnboardingShell.vue`
- Modify: `resources/js/components/project/ProjectPulse.vue`
- Modify: `resources/js/pages/settings/Profile.vue`
- Modify after its owner settles: `resources/js/components/shared/GlobalBusyOverlay.vue`
- Review/modify: `resources/js/components/ui/sidebar/Sidebar.vue`
- Review/modify: `resources/js/components/ui/sidebar/SidebarGroupLabel.vue`
- Review/modify: `resources/js/components/ui/sidebar/index.ts`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Convert horizontal progress fills**

Keep a fixed full-width inner fill and set a normalized CSS variable:

```vue
<div
    class="h-full origin-left scale-x-[var(--progress)] transition-transform duration-[var(--motion-state)] ease-[var(--ease-standard)] motion-reduce:transition-none"
    :style="{ '--progress': String(progress / 100) }"
/>
```

Clamp the existing computed percentage to `0..100`; keep visible/ARIA percentage truth independent of transform.

- [ ] **Step 2: Convert chart columns**

Keep the semantic data table unchanged. Use a full-height inner bar with `origin-bottom` and `scale-y-[var(--ratio)]`; zero values remain visually zero without deleting their accessible data.

- [ ] **Step 3: Reconcile the global busy bar**

After the global-operation owner commits, preserve its operation state and replace only visual width animation with the same transform-fill contract if its measured implementation still uses width.

- [ ] **Step 4: Measure sidebar geometry**

Record a browser Performance trace while collapsing/expanding the sidebar. Retain named width/left/right/margin/padding transitions only when transform would produce wrong layout or hit boxes. Remove any property that is not visibly changing.

- [ ] **Step 5: Run tests and browser checks**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php
npm run types:check
npm run build
```

At normal and reduced motion, verify 0%, partial, and 100% progress; 200% zoom; sidebar keyboard/touch hit boxes; zero animation-attributed layout shift.

- [ ] **Step 6: Commit**

```bash
git commit -m "perf(ui): keep progress motion composite safe"
```

### Task 7: Normalize Shared Overlay Timing

**Files:**

- Modify: `resources/js/components/ui/dialog/DialogContent.vue`
- Modify: `resources/js/components/ui/dialog/DialogScrollContent.vue`
- Modify: `resources/js/components/ui/dialog/DialogOverlay.vue`
- Modify: `resources/js/components/ui/sheet/SheetContent.vue`
- Modify: `resources/js/components/ui/sheet/SheetOverlay.vue`
- Modify: reachable dropdown/select/tooltip/popover content primitives only when their current source owns a competing duration.
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Add the overlay timing contract**

Assert open motion uses the spatial token, close motion uses approximately 75% of spatial duration, and every overlay has reduced-motion `animate-none`/`transition-none` behavior.

- [ ] **Step 2: Retune shared overlays**

Keep responsive `dvh`/safe-area/scroll/focus classes byte-equivalent. Replace the current 500 ms sheet entrance with the spatial token and a 12–24 px directional cue. Do not change Reka state, portals, focus trap, Escape, or close semantics.

- [ ] **Step 3: Test interruption**

In an isolated browser, rapidly open-close-open Dialog, Sheet, Dropdown, and Select. Navigate away while an overlay is closing. Expected: no stale transform, orphan overlay, scroll lock, focus loss, or console error.

- [ ] **Step 4: Run checks**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php
npm run types:check
npm run lint:check
npm run format:check
```

- [ ] **Step 5: Commit**

```bash
git commit -m "refactor(ui): normalize overlay motion timing"
```

### Task 8: Measure The Foundation And Synchronize Evidence

**Files:**

- Modify: `docs/design-system.md`
- Modify: `docs/frontend.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/testing.md`
- Modify: `docs/non-functional-requirements.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Run focused and static gates**

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php tests/Feature/BrandColorTest.php
composer types:check
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
npm run build:android
npm run build:ios
npm audit
composer validate --strict
composer audit --locked
```

- [ ] **Step 2: Run the representative browser matrix**

Use isolated sessions for login, dashboard, projects, task detail, onboarding, notifications, settings, Dialog, Sheet, and global busy overlay at 390×844 and 1440×1000 plus reduced motion, forced colors, coarse pointer, and 200% zoom.

- [ ] **Step 3: Capture motion performance**

For overlay open/close, navigation, progress update, and a collection refresh:

- inspect computed durations and `document.getAnimations()`;
- record a Performance trace;
- confirm feedback begins within 100 ms;
- confirm no animation-attributed layout shift;
- confirm no motion-caused long animation frame over 50 ms;
- compare CSS/JS gzip size with the pre-packet build;
- verify no new dependency or persistent `will-change`.

- [ ] **Step 4: Run full repository gates**

Run sequential full Pest, isolated migration/seed/health, query budgets, secret/diff review, and the repository-supported parallel suite only after the sequential SQLite run. Report every result exactly.

- [ ] **Step 5: Synchronize docs**

Mark only the foundation portion of `ui-motion-001` complete. Domain motion, P1/P2 closure, full route matrix, APK/emulator, rename, and Samsung remain pending until observed.

- [ ] **Step 6: Commit and push safely**

Fetch and verify ancestry. Stage only foundation-owned files, inspect staged diff, commit:

```bash
git commit -m "docs(ui): record soft motion foundation"
```

Push normally only when doing so will not publish unrelated local commits. Never force push.

## Foundation Completion Rule

The packet is complete only when:

- new tokens are the only ordinary duration source;
- generic page/Card/static-list entrances and generic stagger are absent;
- `transition-all` is absent;
- progress/chart motion is composite-safe;
- sidebar geometry exceptions are measured and allowlisted;
- shared overlay motion is short, interruptible, and reduced-motion safe;
- icon, responsive, gradient, loading, busy-state, localization, workspace, query, and email-verification behavior is unchanged;
- focused/full/browser/performance/build evidence is current;
- documentation, commit, and push status are factual.
