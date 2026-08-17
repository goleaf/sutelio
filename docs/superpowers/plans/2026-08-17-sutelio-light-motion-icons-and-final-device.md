# Sutelio Light Motion, Icons, And Final Device Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish Sutelio as a fixed-light, orange/cobalt, icon-rich and purposefully animated product, close every existing verification/documentation gap, complete web/native/repository delivery, and install the final verified APK on the connected Samsung phone as the last phase.

**Architecture:** Extend the existing CSS-first Tailwind `ui-*` motion layer and existing Lucide/shared-heading seams before touching consumers. Migrate the UI in small domain clusters with failing-first Pest/frontend contracts, preserve reduced-motion and forced-colors behavior, then run isolated browser, SQLite, APK/emulator, GitHub/Herd, and physical-device gates in that order. No new frontend dependency, theme family, database behavior, route, authorization rule, or email-verification behavior is introduced.

**Tech Stack:** Laravel 13, PHP 8.5/8.4-compatible application code, Inertia 3, Vue 3 Composition API, TypeScript 6, Pinia 4, Tailwind CSS 4 CSS-first configuration, Reka UI/shadcn-style components, Lucide Vue, Pest 5, Vite 8, SQLite, NativePHP Mobile 4.2, Android SDK/build-tools 37, Chrome DevTools MCP, Playwright MCP, GitHub CLI, Laravel Herd, ADB over USB.

---

## Locked Decisions

- Use the approved “living orchestration” direction: expressive one-shot entry, stagger, disclosure, hover, press, pending, success, rollback, and error motion; no permanent decorative loops.
- Keep one fixed light runtime presentation. An OS dark preference must still render light Sutelio.
- Anchor application orange at exact logo Signal Orange `#FF6038`; use `#CD431F` for white-text primary control surfaces; use cobalt `#123C8B` and deep cobalt `#0A285F` for brand/navigation/information hierarchy.
- Keep semantic destructive/success/warning/information/chart/domain colors distinct.
- Use Lucide only. Icons reinforce visible labels and statuses; decorative icons are hidden from assistive technology.
- Keep email verification disabled and registration directly connected to onboarding.
- Preserve the old Android package and all user data. The final physical-device phase may install/replace only `com.goleaf.sutelio`.
- Do not install on the connected Samsung until every preceding task is complete, committed, pushed, and reviewed.

## File Map

### Motion and icon foundations

- Modify: `resources/css/app.css`
- Create: `resources/js/components/shared/IconTile.vue`
- Modify: `resources/js/components/shared/LeadingIconHeading.vue`
- Modify: `resources/js/components/shared/WorkspacePageHeader.vue`
- Modify: `resources/js/components/shared/EmptyState.vue`
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/badge/index.ts`
- Modify: `resources/js/components/ui/card/Card.vue`
- Modify: `resources/js/components/ui/dialog/DialogContent.vue`
- Modify: `resources/js/components/ui/sheet/SheetContent.vue`
- Modify: `resources/js/components/ui/dropdown-menu/DropdownMenuContent.vue`
- Modify: `resources/js/components/ui/select/SelectContent.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`
- Test: `tests/Feature/FrontendDesignTest.php`

### Consumer clusters

- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/components/localization/FirstRunLanguageDialog.vue`
- Modify: `resources/js/components/onboarding/OnboardingShell.vue`
- Modify: `resources/js/components/onboarding/WelcomeStep.vue`
- Modify: `resources/js/components/onboarding/WorkspaceStep.vue`
- Modify: `resources/js/components/onboarding/ProjectStep.vue`
- Modify: `resources/js/components/onboarding/TaskStep.vue`
- Modify: `resources/js/components/onboarding/PreferencesStep.vue`
- Modify: `resources/js/components/onboarding/ProductMapStep.vue`
- Modify: `resources/js/components/onboarding/SafetyStep.vue`
- Modify: `resources/js/components/onboarding/ResultsStep.vue`
- Modify: `resources/js/pages/Dashboard.vue`
- Modify: `resources/js/pages/projects/Index.vue`
- Modify: `resources/js/pages/projects/Show.vue`
- Modify: `resources/js/pages/tasks/Index.vue`
- Modify: `resources/js/pages/tasks/Show.vue`
- Modify: `resources/js/pages/calendar/Index.vue`
- Modify: `resources/js/pages/activity/Index.vue`
- Modify: `resources/js/pages/notifications/Index.vue`
- Modify: `resources/js/pages/workspaces/Index.vue`
- Modify: focused components under `resources/js/components/dashboard/**`, `calendar/**`, `activity/**`, `notification/**`, `project/**`, `task/**`, and `workspace/**` listed in their tasks below.
- Modify: settings pages under `resources/js/pages/settings/**` and focused settings components listed below.
- Modify: navigation/shell components `AppHeader.vue`, `AppSidebar.vue`, `NavMain.vue`, `NavFooter.vue`, `NavUser.vue`, `UserMenuContent.vue`, `Breadcrumbs.vue`, and `shared/CommandPalette.vue`.

### Evidence and delivery

- Modify: `docs/design-system.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/frontend.md`
- Modify: `docs/testing.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/current-state.md`
- Modify: `docs/deployment.md`
- Modify: `docs/current-state-audit.md`
- Modify append-only: `docs/progress.md`
- Generated/ignored: `nativephp/android/**`, browser profiles/screenshots, emulator screenshots/logs
- Generated/ignored artifact: `storage/app/native-build/sutelio-android-debug.apk`

## Task 0: Close The Existing Task 9 Evidence Gap

**Files:**

- Modify: `docs/non-functional-requirements.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/known-limitations.md`
- Modify: `docs/implementation-plan.md`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Preserve the already prepared factual correction**

Keep the exact active facts:

```text
Sequential Pest: 831 tests / 32,899 assertions.
Parallel Pest: 831 tests / 32,899 assertions.
Frontend: 45 tests passed.
Coverage: command exited before tests because Xdebug/PCOV is unavailable; no percentage is claimed.
Task 9 complete; Tasks 10, 11, and 12 remain pending until superseded by this plan.
```

- [ ] **Step 2: Verify the correction**

Run:

```bash
npx prettier --check docs/non-functional-requirements.md docs/compliance-matrix.md docs/known-limitations.md docs/implementation-plan.md docs/progress.md
php artisan test --compact tests/Feature/BrandIdentityTest.php
git diff --check
```

Expected: Prettier and diff checks pass; BrandIdentityTest passes; no current document calls Task 9 pending or reports 808/21,930 as the latest result.

- [ ] **Step 3: Publish only the five owned documentation files**

Use a temporary index if `docs/progress.md` contains another owner's append. Commit:

```text
docs: synchronize Sutelio quality evidence
```

Expected: normal push to `origin/main`; no foreign UI/MCP/design block enters the commit.

## Task 1: Establish The Failing Motion And Icon Inventory

**Files:**

- Create: `tests/Feature/FrontendMotionIconSystemTest.php`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Generate the Pest test shell**

Run:

```bash
php artisan make:test --pest FrontendMotionIconSystemTest --no-interaction
```

- [ ] **Step 2: Add the initial failing contracts**

Use this structure:

```php
<?php

use Illuminate\Support\Facades\File;

dataset('primary Sutelio pages', [
    'dashboard' => 'pages/Dashboard.vue',
    'projects' => 'pages/projects/Index.vue',
    'project detail' => 'pages/projects/Show.vue',
    'tasks' => 'pages/tasks/Index.vue',
    'task detail' => 'pages/tasks/Show.vue',
    'calendar' => 'pages/calendar/Index.vue',
    'activity' => 'pages/activity/Index.vue',
    'notifications' => 'pages/notifications/Index.vue',
    'workspaces' => 'pages/workspaces/Index.vue',
]);

test('the shared Sutelio motion and icon primitives are available', function () {
    expect(File::get(resource_path('css/app.css')))
        ->toContain('@keyframes ui-reveal')
        ->toContain('@keyframes ui-status-pop')
        ->toContain('.ui-reveal')
        ->toContain('.ui-lift')
        ->toContain('.ui-status-pop')
        ->toContain('@media (prefers-reduced-motion: reduce)');

    expect(File::get(resource_path('js/components/shared/IconTile.vue')))
        ->toContain('data-slot="icon-tile"')
        ->toContain("tone?: 'brand' | 'cobalt' | 'muted' | 'success' | 'warning' | 'destructive' | 'information'")
        ->toContain('aria-hidden="true"');
});

test('primary pages compose the shared icon bearing header', function (string $path) {
    expect(File::get(resource_path("js/{$path}")))
        ->toContain('WorkspacePageHeader')
        ->toContain('<template #icon>');
})->with('primary Sutelio pages');

test('first party presentation remains fixed light and free of raw brand colors', function () {
    foreach ([resource_path('js'), resource_path('views')] as $root) {
        foreach (File::allFiles($root) as $file) {
            if (! in_array($file->getExtension(), ['js', 'ts', 'vue', 'php'], true)) {
                continue;
            }

            $source = File::get($file->getPathname());

            expect($source, $file->getRelativePathname())
                ->not->toContain('dark:', '#123C8B', '#0A285F', '#FF6038', '#CD431F');
        }
    }
});
```

Add a narrow allowlist for `resources/js/app.ts` because the Inertia progress API requires a concrete string rather than a CSS variable.

- [ ] **Step 3: Prove the contract is red**

Run:

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php
```

Expected: failure because `IconTile.vue`, `ui-reveal`, `ui-status-pop`, and page-header icon slots do not yet exist.

- [ ] **Step 4: Commit the red contract**

Commit only the test and append-only preflight:

```text
test(ui): define Sutelio motion icon contract
```

## Task 2: Expand The Shared Motion Language

**Files:**

- Modify: `resources/css/app.css`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Add bounded keyframes**

Add after `ui-enter`:

```css
@keyframes ui-reveal {
    from {
        opacity: 0;
        transform: translateY(0.625rem);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ui-status-pop {
    0% {
        opacity: 0;
        transform: scale(0.88);
    }

    70% {
        opacity: 1;
        transform: scale(1.04);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}
```

- [ ] **Step 2: Add reusable utilities**

Add to `@layer utilities`:

```css
.ui-reveal {
    animation: ui-reveal var(--motion-emphasized) var(--ease-emphasized) both;
}

.ui-lift {
    transition:
        border-color var(--motion-standard) var(--ease-emphasized),
        box-shadow var(--motion-standard) var(--ease-emphasized),
        transform var(--motion-standard) var(--ease-emphasized);
}

.ui-lift:hover {
    transform: translateY(-0.125rem);
}

.ui-status-pop {
    animation: ui-status-pop var(--motion-standard) var(--ease-emphasized) both;
}

.ui-icon-response > svg {
    transition: transform var(--motion-standard) var(--ease-emphasized);
}

.ui-icon-response:hover > svg {
    transform: scale(1.08) rotate(-3deg);
}
```

Extend the existing reduced-motion block with:

```css
.ui-lift:hover,
.ui-icon-response:hover > svg {
    transform: none;
}
```

- [ ] **Step 3: Verify CSS and the focused contract**

Run:

```bash
npx prettier --check resources/css/app.css
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php --filter='shared Sutelio motion'
npm run build
```

Expected: the motion primitive test passes; Vite completes without a Tailwind unknown-utility error.

- [ ] **Step 4: Commit**

```text
feat(ui): expand Sutelio motion primitives
```

## Task 3: Add The Shared Icon Tile Primitive

**Files:**

- Create: `resources/js/components/shared/IconTile.vue`
- Modify: `resources/js/components/shared/LeadingIconHeading.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Implement `IconTile.vue`**

Create:

```vue
<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type IconTileTone =
    | 'brand'
    | 'cobalt'
    | 'muted'
    | 'success'
    | 'warning'
    | 'destructive'
    | 'information';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        tone?: IconTileTone;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { tone: 'brand', size: 'md' },
);

const toneClass = computed(
    () =>
        ({
            brand: 'border-orange-500/15 bg-orange-500/10 text-orange-800',
            cobalt: 'border-brand-cobalt/15 bg-brand-cobalt text-brand-ivory',
            muted: 'border-border/80 bg-muted text-muted-foreground',
            success: 'border-emerald-500/15 bg-emerald-500/10 text-emerald-700',
            warning: 'border-amber-500/20 bg-amber-500/10 text-amber-800',
            destructive:
                'border-destructive/15 bg-destructive/10 text-destructive',
            information: 'border-blue-500/15 bg-blue-500/10 text-blue-700',
        })[props.tone],
);

const sizeClass = computed(
    () =>
        ({
            sm: 'size-9 rounded-xl [&_svg]:size-4',
            md: 'size-11 rounded-2xl [&_svg]:size-5',
            lg: 'size-12 rounded-2xl [&_svg]:size-6',
        })[props.size],
);
</script>

<template>
    <span
        data-slot="icon-tile"
        aria-hidden="true"
        :class="
            cn(
                'ui-icon-response flex shrink-0 items-center justify-center border shadow-sm',
                toneClass,
                sizeClass,
                props.class,
            )
        "
    >
        <slot />
    </span>
</template>
```

- [ ] **Step 2: Let `LeadingIconHeading` opt into the tile without breaking existing consumers**

Add `tile?: boolean`, `tileTone?: IconTileTone`, and `tileSize?: 'sm' | 'md' | 'lg'` props. Render the icon slot through `IconTile` only when `tile` is true; retain the current raw slot path otherwise so the 28 existing uses do not change unexpectedly.

- [ ] **Step 3: Verify red becomes green**

Run:

```bash
npm run types:check
npm run lint:check
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='icon tile|leading icon'
```

Expected: Vue/ESLint pass and the focused Pest tests pass.

- [ ] **Step 4: Commit**

```text
feat(ui): add shared Sutelio icon tile
```

## Task 4: Add Icon Identity To Shared Page Headers

**Files:**

- Modify: `resources/js/components/shared/WorkspacePageHeader.vue`
- Modify: `resources/js/pages/Dashboard.vue`
- Modify: `resources/js/pages/projects/Index.vue`
- Modify: `resources/js/pages/projects/Show.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Add the optional header icon slot**

In `WorkspacePageHeader.vue`, import `LeadingIconHeading`; its `tile` mode owns `IconTile`. Replace the current eyebrow/title/description block with:

```vue
<LeadingIconHeading v-if="$slots.icon" tile tile-tone="cobalt" tile-size="lg">
    <template #icon><slot name="icon" /></template>
    <p class="text-[0.7rem] font-semibold tracking-[0.22em] text-orange-700 uppercase">
        {{ eyebrow }}
    </p>
    <h1 class="text-3xl font-semibold tracking-[-0.04em] text-balance text-brand-deep-cobalt sm:text-4xl">
        {{ title }}
    </h1>
    <p class="max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base">
        {{ description }}
    </p>
</LeadingIconHeading>
```

Keep the existing no-icon branch temporarily so consumers can migrate in bounded commits.

- [ ] **Step 2: Add domain icons**

Use Lucide slots:

```vue
<template #icon><LayoutDashboard aria-hidden="true" /></template>
<template #icon><FolderKanban aria-hidden="true" /></template>
```

Use `LayoutDashboard` for Dashboard, `FolderKanban` for project index/detail. Do not add separate accessible names because each icon is adjacent to the visible `h1`.

- [ ] **Step 3: Verify the first page cluster**

Run:

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='primary pages|workspace header|project operations'
npm run types:check
npm run test:frontend
```

- [ ] **Step 4: Commit**

```text
feat(ui): add icons to primary Sutelio headers
```

## Task 5: Complete Primary Page Header Icons

**Files:**

- Modify: `resources/js/pages/tasks/Index.vue`
- Modify: `resources/js/pages/tasks/Show.vue`
- Modify: `resources/js/pages/calendar/Index.vue`
- Modify: `resources/js/pages/activity/Index.vue`
- Modify: `resources/js/pages/notifications/Index.vue`
- Modify: `resources/js/pages/workspaces/Index.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Add exact icon slots**

Use:

```text
tasks index/detail: ListChecks
calendar: CalendarDays
activity: History
notifications: BellRing
workspaces: Building2
```

Every slot uses `<Icon aria-hidden="true" />`; every import comes from `@lucide/vue` and is merged with the file's existing import.

- [ ] **Step 2: Remove the temporary no-icon header branch**

After all nine primary consumers provide `#icon`, make `WorkspacePageHeader` require the icon slot visually while retaining Vue slot typing compatibility. The title remains the single page `h1`.

- [ ] **Step 3: Verify all primary headers**

Run:

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='primary pages|primary workspace pages'
npm run types:check
npm run lint:check
```

- [ ] **Step 4: Commit**

```text
feat(ui): complete Sutelio page header icons
```

## Task 6: Centralize Surface And Overlay Motion

**Files:**

- Modify: `resources/js/components/ui/card/Card.vue`
- Modify: `resources/js/components/ui/dialog/DialogContent.vue`
- Modify: `resources/js/components/ui/sheet/SheetContent.vue`
- Modify: `resources/js/components/ui/dropdown-menu/DropdownMenuContent.vue`
- Modify: `resources/js/components/ui/select/SelectContent.vue`
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/badge/index.ts`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Make motion ownership explicit**

Use these contracts:

```text
Card: ui-surface; add ui-lift only at consumer opt-in, never globally.
DialogContent: state open/closed fade+zoom with motion-reduce animate-none.
SheetContent: state open/closed directional motion with motion-reduce animate-none.
DropdownMenuContent and SelectContent: origin-aware fade/zoom/slide with motion-reduce animate-none.
Button: retain ui-control; icon response stays centralized and disabled under reduced motion.
Badge: add ui-status-pop only when a consumer explicitly opts in via class.
```

- [ ] **Step 2: Add source assertions for each primitive**

The test must assert each overlay has both open/closed animation classes and matching `motion-reduce:` overrides; it must assert Card does not globally contain `ui-lift`.

- [ ] **Step 3: Run focused UI gates**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='surface|overlay|control|motion'
npm run types:check
npm run lint:check
npm run build
```

- [ ] **Step 4: Commit**

```text
feat(ui): centralize Sutelio surface motion
```

## Task 7: Polish Authentication And Language Entry

**Files:**

- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/pages/auth/Login.vue`
- Modify: `resources/js/pages/auth/Register.vue`
- Modify: `resources/js/components/localization/FirstRunLanguageDialog.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`
- Test: `tests/Feature/FrontendLocalizationTest.php`

- [ ] **Step 1: Standardize existing auth icons**

Replace manual orange/cobalt icon containers with `IconTile` using `cobalt` for the auth shell and `brand` for contextual login/register actions. Keep visible button text and existing `BadgeCheck`, `LogIn`, and `UserPlus` semantics.

- [ ] **Step 2: Replace the permanent language-dialog pulse**

Remove `motion-safe:animate-pulse` from the decorative ring. Use `ui-status-pop` once on the icon tile and `ui-stagger` on language options; keep the existing global reduced-motion clamp and focus behavior.

- [ ] **Step 3: Verify registration still bypasses email verification**

Run:

```bash
php artisan test --compact tests/Feature/Auth/RegistrationTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/FrontendMotionIconSystemTest.php --filter='registration|language|auth'
npm run types:check
```

Expected: registration authenticates and redirects to onboarding; no verification route/UI/email appears.

- [ ] **Step 4: Commit**

```text
feat(ui): orchestrate Sutelio entry flows
```

## Task 8: Orchestrate The Onboarding Journey

**Files:**

- Modify: `resources/js/components/onboarding/OnboardingShell.vue`
- Modify: `resources/js/components/onboarding/WelcomeStep.vue`
- Modify: `resources/js/components/onboarding/WorkspaceStep.vue`
- Modify: `resources/js/components/onboarding/ProjectStep.vue`
- Modify: `resources/js/components/onboarding/TaskStep.vue`
- Test: `tests/Feature/OnboardingFrontendTest.php`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Apply shared icon tiles to the first onboarding cluster**

Use `IconTile` for `Route`, `Sparkles`, `UsersRound`, `Building2`, `FolderKanban`, `ListChecks`, and `CalendarClock` where they introduce a visible heading/preview. Preserve project/domain colors only for the small entity marker inside content.

- [ ] **Step 2: Apply bounded step motion**

Use the existing `ui-step-*` transition for step replacement, `ui-stagger` for choice/result lists, and `ui-status-pop` once for successful completion. No new interval or timer is added.

- [ ] **Step 3: Verify the cluster**

```bash
php artisan test --compact tests/Feature/OnboardingFrontendTest.php tests/Feature/FrontendMotionIconSystemTest.php
npm run types:check
npm run test:frontend
```

- [ ] **Step 4: Commit**

```text
feat(ui): animate Sutelio onboarding flow
```

## Task 9: Complete Onboarding Results And Safety Motion

**Files:**

- Modify: `resources/js/components/onboarding/PreferencesStep.vue`
- Modify: `resources/js/components/onboarding/ProductMapStep.vue`
- Modify: `resources/js/components/onboarding/SafetyStep.vue`
- Modify: `resources/js/components/onboarding/ResultsStep.vue`
- Test: `tests/Feature/OnboardingFrontendTest.php`

- [ ] **Step 1: Standardize remaining icon/heading clusters**

Use `IconTile` tones: brand for preferences/results, cobalt for product map, success/information/warning for safety guarantees according to visible text. Keep all visible labels and translation keys unchanged.

- [ ] **Step 2: Add one-shot result emphasis**

Use `ui-status-pop` for the ready-state icon and `ui-stagger` for next-step cards. Loading or progress animation exists only while `processing` is true.

- [ ] **Step 3: Verify and commit**

Run:

```bash
php artisan test --compact tests/Feature/OnboardingFrontendTest.php tests/Feature/FrontendMotionIconSystemTest.php
npm run types:check
npm run lint:check
```

Commit:

```text
feat(ui): complete Sutelio onboarding motion
```

## Task 10: Polish Dashboard, Project, And Workspace Surfaces

**Files:**

- Modify: `resources/js/components/dashboard/DashboardTaskQueue.vue`
- Modify: `resources/js/components/dashboard/ProductivityChart.vue`
- Modify: `resources/js/components/project/ProjectPulse.vue`
- Modify: `resources/js/components/project/ProjectTaskQueue.vue`
- Modify: `resources/js/components/workspace/WorkspaceOverviewPanel.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Replace manual repeated icon tiles**

Use `IconTile` for queue, pulse, and workspace overview headings. Keep dynamic domain colors on progress bars and entity swatches, not on shared structural icons.

- [ ] **Step 2: Apply meaningful motion**

Use `ui-lift` on clickable project/workspace cards only, `ui-stagger` on bounded dashboard queues, and existing width transitions for charts/progress. Add `motion-reduce:transition-none` to every width/transform transition touched.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/FrontendMotionIconSystemTest.php --filter='dashboard|project|workspace|productivity'
npm run types:check
npm run build
```

Commit:

```text
feat(ui): polish Sutelio workspace surfaces
```

## Task 11: Polish Task Interaction Surfaces

**Files:**

- Modify: `resources/js/components/task/TaskDetailContent.vue`
- Modify: `resources/js/components/task/TaskOverviewPanel.vue`
- Modify: `resources/js/components/task/TaskChecklistPanel.vue`
- Modify: `resources/js/components/task/TaskAttachmentsPanel.vue`
- Modify: `resources/js/components/task/TaskRemindersPanel.vue`
- Modify: `resources/js/components/task/TaskCommentsPanel.vue`
- Modify: `resources/js/components/task/TaskTaxonomyPanel.vue`
- Modify: `resources/js/components/task/TaskList.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Add consistent section icons**

Use `User`, `ListChecks`, `Paperclip`, `Bell`, `MessageSquare`, and `Tags` through `IconTile` beside the existing visible section headings. Keep destructive `Trash2` buttons destructive and visibly labelled or accessibly named.

- [ ] **Step 2: Add state-aware motion**

Use `ui-status-pop` after checklist completion/attachment upload/reminder creation, `ui-stagger` for bounded checklist and attachment rows, and `ui-lift` for clickable task cards. Pending spinners remain tied to request processing.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='task|checklist|attachment|reminder|comment'
npm run types:check
npm run lint:check
```

Commit:

```text
feat(ui): animate Sutelio task interactions
```

## Task 12: Polish Calendar, Activity, And Notification States

**Files:**

- Modify: `resources/js/components/calendar/CalendarAttentionRail.vue`
- Modify: `resources/js/components/calendar/CalendarAgendaView.vue`
- Modify: `resources/js/components/calendar/CalendarWeekView.vue`
- Modify: `resources/js/components/activity/ActivityTimeline.vue`
- Modify: `resources/js/components/activity/ActivityFilterPanel.vue`
- Modify: `resources/js/components/notification/NotificationFeed.vue`
- Modify: `resources/js/components/notification/NotificationRow.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`
- Test: `tests/Feature/ActivityIntelligenceFrontendTest.php`

- [ ] **Step 1: Standardize headings and status icons**

Use `CalendarCheck2`, `Clock3`, `History`, `Filter`, `BellRing`, `MailOpen`, and `CheckCheck` through shared icon tiles where adjacent visible labels exist. Unread state keeps text/weight in addition to color.

- [ ] **Step 2: Add bounded list and state motion**

Use `ui-stagger` only on the first rendered bounded group; use one-shot `ui-status-pop` when a notification becomes read or all notifications become read. Infinite/paginated feeds do not animate every historical row on refetch.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/ActivityIntelligenceFrontendTest.php tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='calendar|activity|notification'
npm run types:check
npm run test:frontend
```

Commit:

```text
feat(ui): animate Sutelio planning states
```

## Task 13: Polish Account And Preference Settings

**Files:**

- Modify: `resources/js/pages/settings/Profile.vue`
- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/pages/settings/Notifications.vue`
- Modify: `resources/js/pages/settings/Security.vue`
- Modify: `resources/js/components/ManagePasskeys.vue`
- Modify: `resources/js/components/TwoFactorRecoveryCodes.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`
- Test: `tests/Feature/Settings/PreferencesTest.php`

- [ ] **Step 1: Standardize section icons and action icons**

Use shared tiles for profile/avatar, preferences, notifications, password, two-factor, passkeys, and recovery codes. Add Lucide icons to save/enable/disable/regenerate/reveal actions when missing; retain visible text.

- [ ] **Step 2: Add request-state feedback**

Tie spinner/icon swaps to existing `processing` flags. Use one-shot success motion only after `recentlySuccessful`/existing success state. Errors remain role alert and do not shake continuously.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/Settings/PreferencesTest.php tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='settings|profile|security|passkey|recovery'
npm run types:check
npm run lint:check
```

Commit:

```text
feat(ui): polish Sutelio account settings
```

## Task 14: Polish Data, Member, And Workspace Administration

**Files:**

- Modify: `resources/js/pages/settings/Backup.vue`
- Modify: `resources/js/pages/settings/Export.vue`
- Modify: `resources/js/pages/settings/Members.vue`
- Modify: `resources/js/components/settings/data/DataScopeBanner.vue`
- Modify: `resources/js/components/workspace/WorkspaceConfigurationPanel.vue`
- Modify: `resources/js/components/workspace/WorkspaceMembersPanel.vue`
- Modify: `resources/js/components/workspace/WorkspaceDangerPanel.vue`
- Test: `tests/Feature/DataSafetyCenterFrontendTest.php`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Use shared icon tiles without weakening danger semantics**

Use cobalt/information for data scope, brand for export/download, success for verified backups, users for membership, settings for configuration, and destructive for irreversible workspace actions. Destructive tiles and buttons retain visible warning text and confirmation dialogs.

- [ ] **Step 2: Add safe operation motion**

Animate progress width with reduced-motion parity, use one-shot completion icons, and use shared dialog/sheet transitions. Do not animate physical path data or expose backup filenames beyond existing safe public values.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/DataSafetyCenterFrontendTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/FrontendMotionIconSystemTest.php --filter='backup|export|member|workspace|data'
npm run types:check
npm run build
```

Commit:

```text
feat(ui): polish Sutelio administration states
```

## Task 15: Complete Navigation, Menus, Empty States, And Shells

**Files:**

- Modify: `resources/js/components/AppHeader.vue`
- Modify: `resources/js/components/AppSidebar.vue`
- Modify: `resources/js/components/NavMain.vue`
- Modify: `resources/js/components/NavFooter.vue`
- Modify: `resources/js/components/NavUser.vue`
- Modify: `resources/js/components/UserMenuContent.vue`
- Modify: `resources/js/components/Breadcrumbs.vue`
- Modify: `resources/js/components/shared/CommandPalette.vue`
- Modify: `resources/js/components/shared/EmptyState.vue`
- Test: `tests/Feature/FrontendMotionIconSystemTest.php`

- [ ] **Step 1: Normalize interactive icon behavior**

All icon-bearing navigation/actions use the existing control/focus contract; icon-only buttons have an accessible name and tooltip. Active navigation uses text/weight/indicator in addition to orange. Breadcrumb separators remain decorative.

- [ ] **Step 2: Replace the inline empty-state SVG**

Use Lucide `PackageOpen` as the default empty icon through `IconTile`. Keep `LoaderCircle`, `AlertTriangle`, visible titles/descriptions, action text, `role`, and `aria-busy` behavior.

- [ ] **Step 3: Verify and commit**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php --filter='navigation|menu|empty|shell|icon'
npm run types:check
npm run lint:check
npm run test:frontend
```

Commit:

```text
feat(ui): complete Sutelio shell interactions
```

## Task 16: Run The Repository-Wide Accessibility And Drift Audit

**Files:**

- Modify: `tests/Feature/FrontendMotionIconSystemTest.php`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: any exact consumer found non-compliant by the audit

- [ ] **Step 1: Audit all first-party Vue files**

Run inventories:

```bash
rg --files resources/js -g '*.vue'
rg -L '@lucide/vue' resources/js -g '*.vue'
rg -L 'motion-reduce:' resources/js -g '*.vue'
rg -n 'dark:|#[fF][fF]6038|#123[cC]8[bB]|#0[aA]285[fF]|#(?:[cC][dD])431[fF]' resources/js resources/views
rg -n '<button|<Button|<h[1-4]|CardTitle|DialogTitle|SheetTitle' resources/js -g '*.vue'
```

Classify every match using the design's icon/motion/no-motion criteria. Do not force icons into prose, decorative separators, dense calendar labels, or semantically complete native controls.

- [ ] **Step 2: Add exact regression datasets for all intentional consumers**

The test must list page-header icons, shared tile headings, bounded stagger containers, reduced-motion components, and icon-only accessible controls. It must assert that any excluded component is covered by a named data row explaining its intentional no-icon/no-motion status.

- [ ] **Step 3: Run full frontend static gates**

```bash
php artisan test --compact tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendLocalizationTest.php
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
```

- [ ] **Step 4: Commit**

```text
test(ui): guard Sutelio motion icon coverage
```

## Task 17: Synchronize Current Design And Delivery Documentation

**Files:**

- Modify: `docs/design-system.md`
- Modify: `docs/accessibility.md`
- Modify: `docs/frontend.md`
- Modify: `docs/testing.md`
- Modify: `docs/implementation-plan.md`
- Modify: `docs/compliance-matrix.md`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Record current contracts**

Document the exact orange/cobalt role split, fixed-light rule, `IconTile`/`LeadingIconHeading`/`WorkspacePageHeader` ownership, bounded motion classes, no-loop policy, reduced-motion behavior, forced-colors expectations, and exact automated/browser evidence.

- [ ] **Step 2: Preserve history**

Do not rewrite old dark-mode, Xiaomi Mimo, APK, hash, or earlier suite evidence. Add successor/current-state notes and keep audit/history exclusions intact.

- [ ] **Step 3: Verify and commit**

```bash
npx prettier --check docs/design-system.md docs/accessibility.md docs/frontend.md docs/testing.md docs/implementation-plan.md docs/compliance-matrix.md docs/progress.md
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/FrontendMotionIconSystemTest.php
git diff --check
```

Commit:

```text
docs: document Sutelio motion icon system
```

## Task 18: Verify The Complete Web Experience In Real Browsers

**Files:**

- Modify append-only: `docs/progress.md`
- Store ignored/outside Git: screenshots, accessibility snapshots, console/network logs

- [ ] **Step 1: Resolve the current Herd URL**

Use Laravel Boost `get-absolute-url` when exposed; otherwise run:

```bash
herd sites
```

Do not start a development server. Use the currently linked Herd site.

- [ ] **Step 2: Use isolated browser sessions**

Use both Chrome DevTools MCP and Playwright MCP with disposable profiles. Cover:

```text
login
registration and direct onboarding
first-run language dialog
dashboard
projects index/detail
tasks index/detail
calendar
activity
notifications
settings profile/preferences/notifications/security/backup/export/members
workspace overview/configuration/members/danger
dialog, sheet, menu, empty, loading, success, validation error, permission error
```

- [ ] **Step 3: Cover viewports and accessibility modes**

Verify `390x844`, `820x1180`, `1024x768`, and `1440x1000`; keyboard-only focus; 200% zoom; reduced motion; forced colors; OS dark preference while computed `color-scheme` remains light; touch targets; one `main`/`h1`; no horizontal overflow.

- [ ] **Step 4: Inspect motion and brand behavior**

Measure entry/control/status durations, confirm bounded one-shot animation, and prove reduced-motion clamps them. Confirm exact computed Signal Orange/cobalt roles, readable white-on-orange-600 controls, favicon/logo/wordmark, and no legacy name/cube/dark UI.

- [ ] **Step 5: Record evidence**

Record routes, viewports, screenshots, computed colors/durations, accessibility observations, and exact console/page/network failures. Fix every first-party defect, repeat the affected check, and commit each attributable correction separately.

## Task 19: Run Full Application, Dependency, And Data-Safety Gates

**Files:**

- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Backend and PHP gates**

```bash
vendor/bin/pint --dirty --format agent
composer types:check
php artisan test --compact tests/Feature/BrandIdentityTest.php tests/Feature/NativePhpMobileTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendMotionIconSystemTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/DatabaseBackupTest.php
php artisan test --compact
./vendor/bin/pest --parallel --compact
```

Run sequential and parallel suites separately; do not overlap SQLite or Vite work.

- [ ] **Step 2: Frontend and package gates**

```bash
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm audit
npm outdated
npm run build
npm run build:android
composer validate --strict
composer audit --locked --no-interaction
composer outdated --direct --strict
composer check-platform-reqs --no-dev
```

Treat `npm outdated` as informational when only foreign-platform optional binaries or incompatible future majors are listed. Do not update outside peer/platform constraints merely to force exit zero.

- [ ] **Step 3: Fresh isolated SQLite**

Create one exact file under `storage/framework/testing`, set the exact allowed directory, run all migrations, seed twice, application health, `PRAGMA integrity_check`, and `PRAGMA foreign_key_check`. Build/clear config, route, and view caches. Move only the exact DB/WAL/SHM siblings recoverably to Trash; never touch the real DB.

- [ ] **Step 4: Coverage attempt**

```bash
php artisan test --coverage --compact
```

If Xdebug/PCOV remains unavailable, record exit 1 before tests and claim no percentage; behavioral proof remains the fresh full suite.

- [ ] **Step 5: Fix and repeat**

Any first-party failure receives a failing regression test, minimal fix, focused rerun, and then the relevant complete gate. Baseline/tooling limitations are recorded exactly and never called pass.

## Task 20: Build, Inspect, And Exercise The Final APK In An Emulator

**Files:**

- Generated/ignored: `nativephp/android/**`
- Generated/ignored artifact: `storage/app/native-build/sutelio-android-debug.apk`
- Modify: `docs/deployment.md`
- Modify: `docs/current-state.md`
- Modify: `docs/current-state-audit.md`
- Modify append-only: `docs/progress.md`

- [ ] **Step 1: Build with the known toolchain**

```bash
export JAVA_HOME=/opt/homebrew/Cellar/openjdk@17/17.0.16/libexec/openjdk.jdk/Contents/Home
export ANDROID_HOME=/Users/andrejprus/Library/Android/sdk
export ANDROID_SDK_ROOT=/Users/andrejprus/Library/Android/sdk
npm run build:android
DB_DATABASE=:memory: php artisan native:run android codex-build-only --build=debug --no-tty --no-interaction
npm run brand:native
cd nativephp/android
./gradlew assembleDebug --no-daemon
```

The second Gradle build is required after deterministic native branding so the inspected APK contains final resources.

- [ ] **Step 2: Copy and inspect the named artifact**

Copy to `storage/app/native-build/sutelio-android-debug.apk`. Run `aapt dump badging`, `apkanalyzer manifest print`, `apksigner verify --verbose --print-certs`, `zipalign -c -v 4`, and archive/resource inspection. Assert:

```text
package com.goleaf.sutelio
label Sutelio
scheme sutelio with no host
brand resources and splash hashes match sources
no legacy package/name
no private SQLite, .env, mail credentials, tokens, keys, or certificates
```

- [ ] **Step 3: Install on the emulator only**

Start the existing approved AVD, resolve its exact emulator serial, uninstall only emulator `com.goleaf.sutelio` when required, install the final APK, launch, relaunch, inspect logcat, screenshots, package/activity state, and core local flows. Never address the physical Samsung serial in this task.

- [ ] **Step 4: Fix and rebuild until clean**

Any APK/emulator failure is fixed in source, followed by relevant focused/full gates and a fresh branded rebuild. Record the final APK SHA-256, size, package, signature, emulator serial/API, screenshots, and logs.

- [ ] **Step 5: Commit current evidence**

Commit only current deployment/state/audit/progress documents:

```text
docs: record Sutelio Android verification
```

## Task 21: Complete Final Repository And Herd Rename

**Files:**

- Modify canonical current docs and append-only progress as required
- External: GitHub repository name, Git remote URL, Herd link, local checkout directory

- [ ] **Step 1: Final source and secret audit**

Run active legacy identity scans, credential/binary/generated-output scans, complete diff review, full test/build evidence review, and confirm the named APK remains ignored.

- [ ] **Step 2: Rename GitHub safely**

Verify `goleaf/sutelio` does not already exist, then rename `goleaf/xiaomi-mimo` to `goleaf/sutelio` with GitHub CLI. Update `origin` to the canonical new URL and verify fetch/push/`gh repo view` without force.

- [ ] **Step 3: Rename Herd checkout safely**

Verify `/Users/andrejprus/Herd/sutelio` is absent, unlink only the old Herd site, move the exact checkout directory to `/Users/andrejprus/Herd/sutelio`, link it through Herd, update only ignored local `APP_URL`, and verify `https://sutelio.test` boot/browser smoke. Do not recursively delete or overwrite either path.

- [ ] **Step 4: Publish final evidence before phone installation**

Commit/push canonical repository/Herd facts, verify `HEAD = origin/main = remote`, clean tracked tree, and confirm no source/doc/Git mutation remains after this point.

## Task 22: Install And Verify On The Connected Samsung Last

**Files:**

- Read-only artifact: `/Users/andrejprus/Herd/sutelio/storage/app/native-build/sutelio-android-debug.apk`
- External target: one already connected physical Samsung Android device over USB
- Store screenshots/logs outside Git

- [ ] **Step 1: Prove every preceding gate is complete**

Confirm Tasks 0-21 complete, `HEAD` synchronized, tracked tree clean, final APK hash matches Task 20, no emulator build is still running, and no further source/document/Git mutation is planned.

- [ ] **Step 2: Resolve the physical device without ambiguity**

```bash
adb devices -l
adb -d get-state
adb -d shell getprop ro.product.manufacturer
adb -d shell getprop ro.product.model
adb -d shell getprop ro.build.version.sdk
```

Require state `device` and manufacturer `samsung` case-insensitively. Wi-Fi is not used for installation; USB remains the addressed transport. If USB authorization is absent or more than one physical target cannot be resolved by `adb -d`, stop without installing to any device.

- [ ] **Step 3: Preserve package boundaries**

Record whether `com.goleaf.xiaomimimo` and `com.goleaf.sutelio` are installed. Do not uninstall, clear, back up, migrate, or modify `com.goleaf.xiaomimimo`. Do not clear Sutelio data.

- [ ] **Step 4: Install the verified APK**

```bash
adb -d install -r /Users/andrejprus/Herd/sutelio/storage/app/native-build/sutelio-android-debug.apk
```

Expected: `Success`. If Android reports a signature mismatch, do not uninstall automatically because that would destroy existing Sutelio sandbox data; record the exact blocker.

- [ ] **Step 5: Launch and exercise the final app**

Resolve the launcher activity from the installed package, clear logcat only for the new observation window, launch Sutelio, capture a screenshot, inspect current focus/activity/process, exercise login/registration-to-onboarding/local navigation as available, background/foreground, and relaunch. Read filtered fatal/ANR/NativePHP/WebView logs and confirm the visible Sutelio icon, splash, title, fixed-light colors, and usable layout.

- [ ] **Step 6: Finish with read-only evidence**

Record outside Git: Samsung model/API/serial redacted to a stable suffix, APK SHA-256/size, install result, package/version/activity, screenshots, log findings, and preserved legacy-package status. Do not make any repository, documentation, build, remote, Herd, package-delete, or app-data mutation after the physical installation. Report the result directly to the user.

## Final Acceptance Checklist

- [ ] Safe choices were made autonomously; no preference prompt interrupted execution.
- [ ] Email verification remains absent and registration auto-authenticates into onboarding.
- [ ] Fixed light-only UI survives an OS dark preference.
- [ ] Signal Orange/cobalt roles match the Sutelio logo and accessible contrast rules.
- [ ] Meaningful headings/actions/states have systematic Lucide icons; no icon-only accessibility regression exists.
- [ ] Meaningful transitions have purposeful motion; no permanent decorative loop exists; reduced-motion and forced-colors work.
- [ ] All focused, full, parallel, frontend, type, lint, format, build, audit, migration, seed, health, browser, and APK/emulator gates are factual and green or precisely documented as an external limitation.
- [ ] GitHub repository, remote, local checkout, and Herd site are Sutelio.
- [ ] The final APK is installed and launched on the connected Samsung only after every other task.
