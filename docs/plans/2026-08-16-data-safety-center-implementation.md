# Data Safety Center Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Execute this plan task-by-task with red-green-refactor checkpoints and review before delivery.

**Goal:** Build the approved coordinated workspace-transfer and operator-backup experience while fixing mobile settings navigation and preserving every authorization/storage boundary.

**Architecture:** Keep the existing Export and Backup routes/controllers/actions separate. Add one focused responsive settings-navigation component, one shared data-scope component, and one pure TypeScript behavior module; the two page coordinators retain their current Inertia request ownership and compose the new presentation.

**Tech Stack:** Laravel 13, PHP 8.4-compatible syntax, Inertia 3, Vue 3 Composition API, TypeScript, Tailwind CSS 4, Reka dropdown primitives, Wayfinder, Pest, Node test runner, SQLite.

---

### Task 1: Record The Phase And Establish Failing Contracts

**Files:**
- Modify: `docs/progress.md`
- Create: `tests/Feature/DataSafetyCenterFrontendTest.php`
- Create: `resources/js/components/DataSafety.test.ts`
- Modify: `tests/Feature/FrontendDesignTest.php`
- Modify: `tests/Feature/FrontendLocalizationTest.php`

- [ ] **Step 1: Append the clean baseline, approved design/plan paths, protected constraints, and pending checks to `docs/progress.md`.**

- [ ] **Step 2: Create the focused Pest test with Artisan.**

Run: `php artisan make:test --pest DataSafetyCenterFrontendTest --no-interaction`

- [ ] **Step 3: Add failing source contracts for the responsive settings menu and page boundaries.**

```php
test('settings use a visible mobile current-section menu and desktop navigation', function () {
    $layout = File::get(resource_path('js/layouts/settings/Layout.vue'));
    $menu = File::get(resource_path('js/components/settings/SettingsSectionMenu.vue'));

    expect($layout)
        ->toContain('SettingsSectionMenu')
        ->toContain('hidden lg:block')
        ->and($menu)
        ->toContain('DropdownMenuTrigger')
        ->toContain('aria-current')
        ->toContain('min-h-11');
});
```

- [ ] **Step 4: Add failing contracts for workspace/application scope, staged import, real export links, backup timeline, 44-pixel controls, and EN/LT/RU key parity.**

- [ ] **Step 5: Add failing pure behavior tests.**

```ts
assert.equal(importStage({ previewing: true, importing: false, hasPreview: false }), 'previewing');
assert.equal(importStage({ previewing: false, importing: false, hasPreview: true }), 'review');
assert.equal(formatDataSize(1536, (value) => String(value)), '1.5 KB');
```

- [ ] **Step 6: Run the red gate.**

Run: `php artisan test --compact tests/Feature/DataSafetyCenterFrontendTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendLocalizationTest.php`

Expected: failure because the new components/copy are absent.

Run: `node --experimental-strip-types --test resources/js/components/DataSafety.test.ts`

Expected: failure because `data-safety.ts` is absent.

### Task 2: Add Pure Data-Safety Behavior

**Files:**
- Create: `resources/js/components/settings/data/data-safety.ts`
- Modify: `resources/js/components/DataSafety.test.ts`

- [ ] **Step 1: Define stable format/stage contracts.**

```ts
export type ExportFormat = 'csv' | 'json' | 'markdown';
export type ImportFormat = 'csv' | 'json';
export type ImportStage = 'importing' | 'previewing' | 'review' | 'select';
```

- [ ] **Step 2: Implement deterministic import-stage selection.**

```ts
export function importStage(state: {
    previewing: boolean;
    importing: boolean;
    hasPreview: boolean;
}): ImportStage {
    if (state.importing) return 'importing';
    if (state.previewing) return 'previewing';
    return state.hasPreview ? 'review' : 'select';
}
```

- [ ] **Step 3: Implement locale-injected binary-size formatting without browser-global state.**

```ts
export function formatDataSize(
    bytes: number,
    formatNumber: (value: number, options?: Intl.NumberFormatOptions) => string,
): string {
    const units = ['B', 'KB', 'MB', 'GB'] as const;
    let value = Math.max(0, bytes);
    let unit = 0;
    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit += 1;
    }
    return `${formatNumber(value, { maximumFractionDigits: value < 10 && unit > 0 ? 1 : 0 })} ${units[unit]}`;
}
```

- [ ] **Step 4: Run the direct Node test.**

Run: `node --experimental-strip-types --test resources/js/components/DataSafety.test.ts`

Expected: all Data Safety behavior cases pass.

### Task 3: Fix Responsive Settings Navigation

**Files:**
- Create: `resources/js/components/settings/SettingsSectionMenu.vue`
- Modify: `resources/js/layouts/settings/Layout.vue`
- Modify: `tests/Feature/DataSafetyCenterFrontendTest.php`

- [ ] **Step 1: Move the settings item shape to a typed local contract containing `label`, `href`, `icon`, and `active`.**

- [ ] **Step 2: Build a mobile Reka dropdown with a 44-pixel current-section trigger and real Wayfinder/Inertia links.**

```vue
<DropdownMenuItem v-for="item in items" :key="item.href" :as-child="true">
    <Link :href="item.href" :aria-current="item.active ? 'page' : undefined">
        <component :is="item.icon" aria-hidden="true" />
        {{ item.label }}
    </Link>
</DropdownMenuItem>
```

- [ ] **Step 3: Retain the desktop vertical link navigation at `lg`, remove the clipped mobile horizontal strip, and keep the existing navigation label.**

- [ ] **Step 4: Run the focused frontend contract.**

Run: `php artisan test --compact tests/Feature/DataSafetyCenterFrontendTest.php`

Expected: responsive navigation contracts pass.

### Task 4: Build Workspace Data Transfer Presentation

**Files:**
- Create: `resources/js/components/settings/data/DataScopeBanner.vue`
- Modify: `resources/js/pages/settings/Export.vue`
- Modify: `lang/en/ui.php`
- Modify: `lang/lt/ui.php`
- Modify: `lang/ru/ui.php`
- Modify: `resources/js/types/ui.ts`
- Modify: `tests/Feature/DataSafetyCenterFrontendTest.php`

- [ ] **Step 1: Add full semantic copy for workspace scope, format guidance, file metadata, three import stages, safe preview, retry/cancel, and the optional operator backup bridge in every locale.**

- [ ] **Step 2: Build `DataScopeBanner.vue` with typed `workspace` and `application` variants, static semantic class maps, Lucide icon slot, and complete text/non-color scope labels.**

- [ ] **Step 3: Replace imperative export buttons with ordinary authorized anchors using Wayfinder URLs.**

```vue
<Button as-child variant="outline" class="min-h-24 ...">
    <a :href="exportMethod([workspace.id, format.value]).url" @click="announceExport(format.value)">
        <!-- icon, label, best-use description -->
    </a>
</Button>
```

- [ ] **Step 4: Present import as Select, Review, Confirm stages while retaining `useHttp`, progress, server errors, and the existing preview/execution separation. Show file name/size/format with a clear action and preserve useful context on failure.**

- [ ] **Step 5: Render the backup bridge only when `page.props.capabilities.manageDatabaseBackups` is true; use the generated backup edit route and expose no inventory data.**

- [ ] **Step 6: Run transfer, localization, design, and focused Data Safety tests.**

Run: `php artisan test --compact tests/Feature/DataTransferTest.php tests/Feature/DataSafetyCenterFrontendTest.php tests/Feature/FrontendLocalizationTest.php tests/Feature/FrontendDesignTest.php`

Expected: all cases pass.

### Task 5: Build Operator Backup Presentation

**Files:**
- Modify: `resources/js/pages/settings/Backup.vue`
- Modify: `lang/en/ui.php`
- Modify: `lang/lt/ui.php`
- Modify: `lang/ru/ui.php`
- Modify: `tests/Feature/DataSafetyCenterFrontendTest.php`
- Modify: `tests/Feature/DatabaseBackupTest.php`

- [ ] **Step 1: Add complete application-scope, verified snapshot, inventory, and restore-risk copy with count-aware messages in all locales.**

- [ ] **Step 2: Reuse `DataScopeBanner` with application scope and place snapshot creation as the only primary page action.**

- [ ] **Step 3: Render ordered snapshots as semantic articles/list items with localized date, binary size, verified state text, 44-pixel Download and Restore controls, and no raw filename/path.**

- [ ] **Step 4: Strengthen the existing confirmation description with selected date and application-database replacement text while preserving the server policy, password confirmation, lock, integrity, rollback, and redirect behavior.**

- [ ] **Step 5: Run backup/security tests.**

Run: `php artisan test --compact tests/Feature/DatabaseBackupTest.php tests/Feature/DataSafetyCenterFrontendTest.php`

Expected: all backup and UI contracts pass.

### Task 6: Verify, Review, Document, And Deliver

**Files:**
- Modify: `docs/progress.md`
- Modify canonical docs only when source/evidence changed: `docs/frontend.md`, `docs/design-system.md`, `docs/accessibility.md`, `docs/compliance-matrix.md`, `CHANGELOG.md`

- [ ] **Step 1: Format and run focused checks.**

Run: `vendor/bin/pint --dirty --format agent`

Run: `php artisan test --compact tests/Feature/DataTransferTest.php tests/Feature/DatabaseBackupTest.php tests/Feature/DataSafetyCenterFrontendTest.php tests/Feature/FrontendDesignTest.php tests/Feature/FrontendLocalizationTest.php`

- [ ] **Step 2: Run static and frontend gates.**

Run: `composer run types:check -- --memory-limit=1G`

Run: `npm run test:frontend && npm run types:check && npm run lint:check && npm run format:check && npm run build`

- [ ] **Step 3: Run the complete suite and audits.**

Run: `php artisan test --compact`

Run: `composer validate --strict --no-check-publish && composer audit --locked --no-interaction && npm audit --audit-level=low`

- [ ] **Step 4: Verify the live Herd pages at 1,440x1,000 and 390x844 in light/dark/reduced-motion/forced-colors states. Check the active mobile section, export links, safe import fixture preview/cancel, ordinary-user backup denial, keyboard/focus order, 44-pixel targets, long text, overflow, console/page/request errors, and recent Boost logs.**

- [ ] **Step 5: Apply independent code review; resolve every Critical, High, and Medium finding and rerun affected gates.**

- [ ] **Step 6: Append exact files, decisions, checks, limitations, commits, and push results to `docs/progress.md`.**

- [ ] **Step 7: Inspect and deliver only phase files.**

Run: `git diff --check`, `git diff --cached --check`, and `git status --short --branch`.

Commit implementation: `feat: build data safety center`

Commit completion record: `docs: record data safety center`

Push: `git push origin main`

Expected: `main` and `origin/main` are synchronized with no phase-owned worktree changes.

## Plan Self-Review

- Spec coverage: every design section maps to Tasks 2-6; authorization/storage protocols remain explicitly unchanged.
- Placeholder scan: no deferred implementation placeholder remains; each code-bearing task names its exact files, contract, and verification command.
- Type consistency: `ExportFormat`, `ImportFormat`, `ImportStage`, `formatDataSize`, `DataScopeBanner`, and `SettingsSectionMenu` names are consistent across tests and implementation tasks.
