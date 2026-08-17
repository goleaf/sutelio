# Leading Icon Heading Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep every audited leading icon and its title/subtitle block in one horizontal row, vertically center the complete text stack against the icon, and preserve localized wrapping without horizontal overflow.

**Architecture:** Add one non-semantic `LeadingIconHeading` Vue presentation primitive with named icon and default content slots, then migrate only the 28 audited heading clusters. Keep heading/description semantics and visual tokens in each consumer; enforce the shared structural contract and exact consumer inventory through failing-first Pest source coverage.

**Tech Stack:** Laravel 13, Pest 5, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Vite 8, Reka UI.

---

### Task 1: Add The Failing Structural Contract

**Files:**

- Modify: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Add a dataset for every audited consumer**

Add this exact inventory near the other shared frontend design contracts:

```php
dataset('leading icon heading consumers', [
    'auth shell' => ['layouts/auth/AuthSimpleLayout.vue', 1],
    'first-run language dialog' => ['components/localization/FirstRunLanguageDialog.vue', 1],
    'dashboard task queue' => ['components/dashboard/DashboardTaskQueue.vue', 1],
    'calendar attention rail' => ['components/calendar/CalendarAttentionRail.vue', 1],
    'project pulse' => ['components/project/ProjectPulse.vue', 1],
    'onboarding checklist' => ['components/onboarding/OnboardingChecklist.vue', 1],
    'onboarding project step' => ['components/onboarding/ProjectStep.vue', 2],
    'onboarding task step' => ['components/onboarding/TaskStep.vue', 2],
    'onboarding workspace step' => ['components/onboarding/WorkspaceStep.vue', 2],
    'data scope banner' => ['components/settings/data/DataScopeBanner.vue', 1],
    'workspace configuration' => ['components/workspace/WorkspaceConfigurationPanel.vue', 2],
    'workspace danger panel' => ['components/workspace/WorkspaceDangerPanel.vue', 3],
    'workspace members panel' => ['components/workspace/WorkspaceMembersPanel.vue', 2],
    'workspace overview panel' => ['components/workspace/WorkspaceOverviewPanel.vue', 1],
    'settings members page' => ['pages/settings/Members.vue', 2],
    'settings profile page' => ['pages/settings/Profile.vue', 1],
    'settings preferences page' => ['pages/settings/Preferences.vue', 1],
    'settings backup page' => ['pages/settings/Backup.vue', 1],
    'settings export page' => ['pages/settings/Export.vue', 1],
    'settings security page' => ['pages/settings/Security.vue', 1],
]);
```

- [ ] **Step 2: Assert the shared primitive and exact usage count**

Add these tests:

```php
test('leading icon headings keep the icon beside a vertically centered wrapping text stack', function () {
    expect(File::get(resource_path('js/components/shared/LeadingIconHeading.vue')))
        ->toContain('data-slot="leading-icon-heading"')
        ->toContain('flex-nowrap')
        ->toContain('items-center')
        ->toContain('data-slot="leading-icon-heading-icon"')
        ->toContain('shrink-0')
        ->toContain('data-slot="leading-icon-heading-content"')
        ->toContain('min-w-0 flex-1')
        ->not->toContain('whitespace-nowrap');
});

test('every audited icon title and subtitle cluster uses the shared alignment contract', function (string $file, int $expectedCount) {
    $source = File::get(resource_path("js/{$file}"));

    expect($source)
        ->toContain("import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue'")
        ->and(substr_count($source, '<LeadingIconHeading'))
        ->toBe($expectedCount);
})->with('leading icon heading consumers');
```

- [ ] **Step 3: Run the test and observe RED**

Run:

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php --filter='leading icon|audited icon'
```

Expected: failure because `LeadingIconHeading.vue` and all consumer imports/usages do not exist.

- [ ] **Step 4: Commit the attributable failing test**

```bash
git add tests/Feature/FrontendDesignTest.php
git commit -m "test(ui): cover leading icon heading alignment"
```

### Task 2: Implement The Shared Presentation Primitive

**Files:**

- Create: `resources/js/components/shared/LeadingIconHeading.vue`
- Test: `tests/Feature/FrontendDesignTest.php`

- [ ] **Step 1: Create the typed component**

Create the file with this implementation:

```vue
<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    iconClass?: HTMLAttributes['class'];
    contentClass?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        data-slot="leading-icon-heading"
        :class="cn('flex min-w-0 flex-nowrap items-center gap-3', props.class)"
    >
        <div
            data-slot="leading-icon-heading-icon"
            :class="cn('shrink-0', props.iconClass)"
        >
            <slot name="icon" />
        </div>

        <div
            data-slot="leading-icon-heading-content"
            :class="cn('grid min-w-0 flex-1 gap-1.5', props.contentClass)"
        >
            <slot />
        </div>
    </div>
</template>
```

- [ ] **Step 2: Run the primitive contract**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php --filter='leading icon headings'
npm run types:check
```

Expected: the primitive contract passes; the consumer inventory test remains red until migration is complete; Vue type checking passes.

### Task 3: Migrate Authentication And Localization

**Files:**

- Modify: `resources/js/layouts/auth/AuthSimpleLayout.vue`
- Modify: `resources/js/components/localization/FirstRunLanguageDialog.vue`
- Test: `tests/Feature/FrontendDesignTest.php`
- Test: `tests/Feature/LanguageSelectionFrontendTest.php`

- [ ] **Step 1: Import the primitive in both consumers**

Use the same import in both files:

```ts
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
```

- [ ] **Step 2: Wrap the icon and semantic text without moving semantics**

In `AuthSimpleLayout.vue`, replace the old `relative flex items-start gap-4` wrapper with this exact composition:

```vue
<LeadingIconHeading class="relative gap-4">
    <template #icon>
        <Link
            :href="home()"
            aria-label="Sutelio"
            class="flex size-12 items-center justify-center rounded-2xl focus-visible:ring-2 focus-visible:ring-brand-cobalt focus-visible:ring-offset-4 focus-visible:outline-hidden"
        >
            <AppLogoIcon class-name="size-12" />
        </Link>
    </template>

    <p
        class="text-[0.7rem] font-semibold tracking-[0.16em] text-orange-700 uppercase"
    >
        {{ t('auth.common.eyebrow') }}
    </p>
    <h1 class="text-2xl font-semibold tracking-[-0.035em]">
        {{ title }}
    </h1>
    <p class="max-w-sm text-sm leading-6 text-muted-foreground">
        {{ description }}
    </p>
</LeadingIconHeading>
```

In `FirstRunLanguageDialog.vue`, keep `DialogHeader` and replace its three direct children with this exact composition:

```vue
<LeadingIconHeading>
    <template #icon>
        <span
            class="flex size-12 items-center justify-center rounded-2xl bg-orange-600 text-white shadow-lg shadow-orange-500/20"
            aria-hidden="true"
        >
            <Languages class="size-6" />
        </span>
    </template>

    <DialogTitle class="text-2xl tracking-[-0.035em]">
        {{ previewCopy.first_run.title }}
    </DialogTitle>
    <DialogDescription class="max-w-md leading-6">
        {{ previewCopy.first_run.description }}
    </DialogDescription>
</LeadingIconHeading>
```

- [ ] **Step 3: Run focused contracts**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/LanguageSelectionFrontendTest.php
npm run types:check
```

Expected: authentication/localization tests and type checking pass; the full consumer inventory still reports only the not-yet-migrated files.

- [ ] **Step 4: Commit the vertical slice**

```bash
git add resources/js/components/shared/LeadingIconHeading.vue resources/js/layouts/auth/AuthSimpleLayout.vue resources/js/components/localization/FirstRunLanguageDialog.vue
git commit -m "fix(ui): align authentication icon headings"
```

### Task 4: Migrate Dashboard, Calendar, Project, And Onboarding

**Files:**

- Modify: `resources/js/components/dashboard/DashboardTaskQueue.vue`
- Modify: `resources/js/components/calendar/CalendarAttentionRail.vue`
- Modify: `resources/js/components/project/ProjectPulse.vue`
- Modify: `resources/js/components/onboarding/OnboardingChecklist.vue`
- Modify: `resources/js/components/onboarding/ProjectStep.vue`
- Modify: `resources/js/components/onboarding/TaskStep.vue`
- Modify: `resources/js/components/onboarding/WorkspaceStep.vue`
- Test: `tests/Feature/FrontendDesignTest.php`
- Test: `tests/Feature/OnboardingWorkflowTest.php`

- [ ] **Step 1: Import the primitive in all seven files**

```ts
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
```

- [ ] **Step 2: Replace the audited heading wrappers**

Use exactly one `LeadingIconHeading` in `DashboardTaskQueue`, `CalendarAttentionRail`, `ProjectPulse`, and `OnboardingChecklist`; use exactly two in each preview pair in `ProjectStep`, `TaskStep`, and `WorkspaceStep`.

For every audited cluster, replace only the current icon-plus-text parent with `LeadingIconHeading`, move the unchanged icon node into its `#icon` slot, and leave the existing heading and subtitle nodes as default-slot children. Do not migrate task rows, progress rows, result lists, metric values, or illustrations. If the old wrapper also owns actions, keep those actions in their current outer container and use `LeadingIconHeading` only around the icon plus text relationship.

- [ ] **Step 3: Run the focused area gates**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/OnboardingWorkflowTest.php
npm run types:check
```

Expected: frontend design and onboarding contracts pass for migrated files; types pass.

- [ ] **Step 4: Commit the vertical slice**

```bash
git add resources/js/components/dashboard/DashboardTaskQueue.vue resources/js/components/calendar/CalendarAttentionRail.vue resources/js/components/project/ProjectPulse.vue resources/js/components/onboarding/OnboardingChecklist.vue resources/js/components/onboarding/ProjectStep.vue resources/js/components/onboarding/TaskStep.vue resources/js/components/onboarding/WorkspaceStep.vue
git commit -m "fix(ui): align workspace icon headings"
```

### Task 5: Migrate Workspace And Settings Surfaces

**Files:**

- Modify: `resources/js/components/settings/data/DataScopeBanner.vue`
- Modify: `resources/js/components/workspace/WorkspaceConfigurationPanel.vue`
- Modify: `resources/js/components/workspace/WorkspaceDangerPanel.vue`
- Modify: `resources/js/components/workspace/WorkspaceMembersPanel.vue`
- Modify: `resources/js/components/workspace/WorkspaceOverviewPanel.vue`
- Modify: `resources/js/pages/settings/Members.vue`
- Modify: `resources/js/pages/settings/Profile.vue`
- Modify: `resources/js/pages/settings/Preferences.vue`
- Modify: `resources/js/pages/settings/Backup.vue`
- Modify: `resources/js/pages/settings/Export.vue`
- Modify: `resources/js/pages/settings/Security.vue`
- Test: `tests/Feature/FrontendDesignTest.php`
- Test: `tests/Feature/WorkspaceManagementTest.php`

- [ ] **Step 1: Import the primitive in all eleven files**

```ts
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
```

- [ ] **Step 2: Replace the exact audited clusters**

Use these exact usage counts:

```text
DataScopeBanner 1
WorkspaceConfigurationPanel 2
WorkspaceDangerPanel 3
WorkspaceMembersPanel 2
WorkspaceOverviewPanel 1
settings/Members 2
settings/Profile 1
settings/Preferences 1
settings/Backup 1
settings/Export 1
settings/Security 1
```

For every counted cluster, replace only the icon-plus-text parent with `LeadingIconHeading`, move the unchanged icon node into the `#icon` slot, and leave the current semantic title and subtitle nodes as default-slot children. For card headers with actions, keep action controls as siblings outside the primitive. For `Security.vue`, move the existing 2FA description into the primitive's content column with its title while preserving the `CardTitle` and `CardDescription` elements. Do not migrate workspace metrics, member/list rows, or destructive buttons.

- [ ] **Step 3: Reach GREEN on the complete inventory**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/WorkspaceManagementTest.php
npm run types:check
```

Expected: all focused tests pass and the source contract counts exactly 28 component usages across the 20 audited consumers.

- [ ] **Step 4: Commit the vertical slice**

```bash
git add resources/js/components/settings/data/DataScopeBanner.vue resources/js/components/workspace/WorkspaceConfigurationPanel.vue resources/js/components/workspace/WorkspaceDangerPanel.vue resources/js/components/workspace/WorkspaceMembersPanel.vue resources/js/components/workspace/WorkspaceOverviewPanel.vue resources/js/pages/settings/Members.vue resources/js/pages/settings/Profile.vue resources/js/pages/settings/Preferences.vue resources/js/pages/settings/Backup.vue resources/js/pages/settings/Export.vue resources/js/pages/settings/Security.vue tests/Feature/FrontendDesignTest.php
git commit -m "fix(ui): align settings icon headings"
```

### Task 6: Format And Run Focused Frontend Verification

**Files:**

- Modify only formatting in the files owned by Tasks 1–5.

- [ ] **Step 1: Format the owned Vue, PHP, and Markdown files**

```bash
vendor/bin/pint --dirty --format agent
./node_modules/.bin/prettier --write resources/js/components/shared/LeadingIconHeading.vue resources/js/layouts/auth/AuthSimpleLayout.vue resources/js/components/localization/FirstRunLanguageDialog.vue resources/js/components/dashboard/DashboardTaskQueue.vue resources/js/components/calendar/CalendarAttentionRail.vue resources/js/components/project/ProjectPulse.vue resources/js/components/onboarding/OnboardingChecklist.vue resources/js/components/onboarding/ProjectStep.vue resources/js/components/onboarding/TaskStep.vue resources/js/components/onboarding/WorkspaceStep.vue resources/js/components/settings/data/DataScopeBanner.vue resources/js/components/workspace/WorkspaceConfigurationPanel.vue resources/js/components/workspace/WorkspaceDangerPanel.vue resources/js/components/workspace/WorkspaceMembersPanel.vue resources/js/components/workspace/WorkspaceOverviewPanel.vue resources/js/pages/settings/Members.vue resources/js/pages/settings/Profile.vue resources/js/pages/settings/Preferences.vue resources/js/pages/settings/Backup.vue resources/js/pages/settings/Export.vue resources/js/pages/settings/Security.vue
```

- [ ] **Step 2: Run focused PHP/frontend gates**

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php tests/Feature/LanguageSelectionFrontendTest.php tests/Feature/OnboardingWorkflowTest.php tests/Feature/WorkspaceManagementTest.php
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
```

Expected: every command exits 0.

### Task 7: Verify The Live Layout In Chromium

**Files:**

- No tracked file change; screenshots and isolated profile stay outside the repository.

- [ ] **Step 1: Open the reachable Herd URL in an isolated Chromium profile**

Verify `https://xiaomi-mimo.test` without starting a development server.

- [ ] **Step 2: Measure the first-run header at phone, tablet, and desktop widths**

At 390×844, 820×1180, and 1440×1000 assert:

```text
root flex-direction = row
root align-items = center
icon left edge < text left edge
abs(icon centerY - content centerY) <= 1 CSS pixel
document.scrollWidth <= document.clientWidth
```

- [ ] **Step 3: Verify localization and accessibility**

Preview English, Lithuanian, and Russian; allow title/description wrapping inside the text column; confirm dialog naming/description, focus visibility, 200% zoom behavior, reduced motion, and zero console/page/network errors.

- [ ] **Step 4: Remove transient screenshots/profile**

Remove only the explicitly created `/tmp/sutelio-leading-icon-*` artifacts after recording measurements.

### Task 8: Run Complete Project Gates

**Files:**

- No intended source change except formatter corrections and final progress evidence.

- [ ] **Step 1: Run backend and static gates**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --memory-limit=1G
php artisan test --compact
```

- [ ] **Step 2: Verify fresh SQLite migrations and deterministic seeding**

Create a repository-contained isolated SQLite file, point the command process to it, run all migrations, seed twice, verify application health, SQLite integrity, and foreign keys, then remove only that isolated database and its WAL/SHM companions. Never refresh the real local database.

- [ ] **Step 3: Run frontend and dependency gates**

```bash
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm audit
npm run build
composer validate --strict
composer audit --locked
composer check-platform-reqs
```

- [ ] **Step 4: Verify framework state**

Build configuration, route, and view caches; inspect routes and schedule; clear the caches; confirm email verification remains absent.

### Task 9: Review, Synchronize Evidence, And Deliver

**Files:**

- Modify: `docs/progress.md`
- Modify only if current contract status changed: `docs/compliance-matrix.md`
- Modify only if current contract status changed: `docs/implementation-plan.md`

- [ ] **Step 1: Perform the final six-axis review**

Review tests, correctness, readability, architecture, security, performance, and accessibility. Re-run any affected focused gate after a correction.

- [ ] **Step 2: Record exact evidence**

Append actual test/assertion totals, browser measurements, formatting/static/build/audit results, zero query/route/schema/dependency delta, limitations, commit, and push status to `docs/progress.md`. Do not claim an unexecuted check.

- [ ] **Step 3: Inspect the complete delivery diff**

```bash
git status --short
git diff --check
git diff HEAD~4..HEAD --stat
git diff HEAD~4..HEAD
```

Confirm no secrets, debug markers, generated build artifacts, databases, environment files, or unrelated changes entered the slice.

- [ ] **Step 4: Commit final evidence**

```bash
git add docs/progress.md docs/superpowers/plans/2026-08-17-leading-icon-heading-alignment.md
git commit -m "docs(ui): record icon heading alignment delivery"
```

- [ ] **Step 5: Fetch and push normally**

```bash
git fetch origin main
git merge-base --is-ancestor origin/main HEAD
git rev-list --left-right --count origin/main...HEAD
git push --verbose origin main
git rev-parse HEAD
git rev-parse origin/main
git ls-remote origin refs/heads/main
git status --short --branch
```

Expected: `origin/main` is a direct ancestor before push; local HEAD, tracking ref, and remote ref are identical after push; the worktree is clean.
