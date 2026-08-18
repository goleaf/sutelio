# Senior-Friendly Responsive System Design

## Status

Approved on 2026-08-18. The product owner's explicit request to complete a deep phone/tablet audit, create an unrestricted plan, and begin programming is treated as approval of the recommended comfort-first shared-system direction after the audit findings and alternatives were presented.

## Objective

Make the existing Sutelio web and NativePHP Mobile interface substantially easier to read, understand, and operate for older adults without creating a separate product mode. Preserve every route, workspace boundary, task workflow, onboarding requirement, locale, offline contract, and the fixed light-only Warm Precision identity while adapting the information hierarchy and interaction model for touch phones and tablets.

## Current Evidence

### Repository Inventory

- 24 Inertia pages, 236 Vue components, eight layouts, one CSS entry, three supported locales, and one shared authenticated sidebar shell.
- 271 responsive utility occurrences across 105 Vue files show broad mobile awareness, but the responsive contract is inconsistent rather than centrally enforced.
- Shared page frame/header, network status, busy overlay, reduced-motion, forced-colors, safe-area, Button, Input, Dialog, Sheet, FilterSheet, and responsive settings navigation primitives already exist and should be strengthened instead of replaced.
- Current decoration remains heavy: 59 `rounded-2xl`, 27 `rounded-panel`, 17 `shadow-panel`, 36 uppercase, and 40 truncation occurrences.
- Current status presentation is fragmented: 150 raw red/amber/blue/sky/emerald/green/slate/neutral utility occurrences across 35 files and no shared success/warning/information utility contract.
- Direct primitive escape hatches remain: 21 native buttons across 15 files and six native textareas across five files. Each requires review for semantics, touch size, disabled state, and translated labeling.
- `AppHeader.vue`, `NavFooter.vue`, and `TaskStats.vue` have no consumer and are legacy-removal candidates after a separate reference and behavior check.

### Fresh Runtime Evidence

- Guest pages have zero horizontal overflow at 320, 390, 430, 768, 820, and 1024 CSS pixels in portrait test conditions.
- Mobile Lighthouse reports accessibility 96, best practices 100, and one contrast failure: the selected language secondary label is 4.39:1 against its tinted surface instead of the required 4.5:1.
- The guest login trace records LCP 629 ms and CLS 0.00 with no console errors/warnings and no failed requests.
- Login and first-run surfaces contain extensive 11.2-14 px text: eyebrow, descriptions, labels, links, separator, button copy, and language secondary names.
- Main login/passkey/confirmation actions are 44 px high. The checkbox is visibly 18 px. Sidebar group/menu actions are 20 px and sub-navigation controls are 28 px, with only partial pseudo-element compensation on smaller screens.
- At 769-1023 px, the current JavaScript breakpoint can switch to the 256 px desktop sidebar, leaving a narrow content region on portrait tablets.
- The first-run language dialog is correctly constrained by `100dvh` in real mobile/touch emulation. In short landscape it requires internal scrolling before the primary confirmation is visible, which is operational but difficult to discover.

## Design Principles

### One Comfortable Default

Sutelio will not add a senior-mode toggle. Small text and small targets are usability defects, not preferences. The default interface will be comfortable for older adults and remain efficient for every other user.

### Readability Before Density

- Reading text starts at 16 px.
- Secondary/helper text starts at 15 px and uses a contrast target of at least 4.5:1.
- Uppercase microcopy, aggressive letter spacing, and 11-12 px eyebrow text are removed from primary workflows.
- Line height remains at least 1.5 for reading copy.
- Long English, Lithuanian, and Russian labels wrap before they truncate. Truncation is reserved for genuinely bounded repeated data with a full accessible name.

### Touch Confidence

- Coarse-pointer interactive targets are at least 48 by 48 px.
- Primary mobile actions are at least 52 px high.
- Adjacent destructive and primary actions maintain at least 8 px separation.
- Checkbox/radio visuals are at least 24 px on coarse pointers and their labels expose a 48 px activation region.
- Icon-only actions remain secondary, have translated accessible names, and are not the sole way to reach a critical operation.

### Context-Driven Responsive Layout

- Phone, 320-767 px: single-column content, full-width actions, drawer navigation, sheets for dense filters, no hover-only functionality.
- Tablet, 768-1023 px: drawer or compact rail navigation, two-column content only when each column remains readable, larger dialogs/sheets, touch-first controls in portrait and landscape.
- Desktop, 1024 px and wider: persistent collapsible sidebar, bounded content width, multi-column layouts where the information hierarchy benefits.
- Coarse-pointer rules remain independent of viewport width so touch laptops and tablets with keyboards remain usable.

### Calm Warm Precision

- Preserve the exact light-only brand palette and Signal Orange contract.
- Replace raw state colors with semantic success, warning, information, and destructive tokens before page-by-page visual polish.
- Reduce decorative circles, gradients, shadows, maximum radii, and card nesting where they compete with content.
- Use radius and elevation to communicate hierarchy, not to decorate every container.
- Motion remains short, purposeful, and disabled under reduced motion.

## Considered Directions

### Shared Comfort Foundation Then Vertical Slices — Selected

Raise typography, target sizes, dialog safety, and tablet navigation in shared primitives first. Then audit and adapt one workflow at a time: first run/authentication, onboarding, tasks, projects, dashboard/calendar/activity/notifications, workspaces/settings, and finally legacy cleanup.

This produces consistent behavior, minimizes duplicated class changes, and lets every slice be independently regression-tested and delivered.

### User-Selectable Large Interface Mode — Rejected

A preference would preserve the current difficult default, add state synchronization and localization work, double the browser/NativePHP matrix, and risk treating accessibility as optional. It may be reconsidered only for an additional extra-large presentation after the comfortable default is complete.

### Page-By-Page Visual Patching — Rejected

Local class edits can improve screenshots quickly but preserve conflicting type sizes, targets, status colors, and breakpoints. The repository already shows the drift caused by this approach.

## System Contract

### Shared Foundation

- `resources/css/app.css` owns coarse-pointer minimums, safe-area composition, short-landscape dialog layout, reflow helpers, reduced motion, and forced-color safeguards.
- Button, Input, Label, Checkbox, Dialog, Sheet, sidebar, and page-frame primitives own common size and interaction behavior.
- Page components may opt into a more spacious presentation but may not reduce targets or reading text below the shared contract in a primary workflow.

### First Run And Authentication

- The mandatory language choice remains one modal with immediate full-dialog translation previews.
- Portrait keeps a vertical, scan-friendly list. Short landscape switches to a two-column composition so all three language choices and the confirmation action are visible without a hidden primary action.
- Selected-language secondary copy uses sufficient contrast.
- Authentication headings, descriptions, labels, separators, help links, checkboxes, inputs, and primary actions use the comfort baseline.

### Navigation And Shell

- Drawer navigation remains active through 1023 px; persistent sidebar begins at 1024 px.
- The drawer closes on an Inertia visit, restores focus to the trigger, respects safe areas, and provides 48 px rows.
- Tablet drawers may widen up to 22 rem but always leave a dismissible viewport edge.
- Breadcrumbs wrap or progressively disclose; they do not force page overflow.

### Tasks And Projects

- Filters become a mobile sheet and a readable tablet control region; active filter state remains visible outside the sheet.
- List rows prioritize title, completion, due state, and project context. Secondary actions move behind a labeled menu without becoming hover-only.
- Board columns become horizontally deliberate, announce their scroll region, and preserve 48 px drag/action targets.
- Creation and edit dialogs remain scroll-safe above the virtual keyboard with a visible, sticky action region where the form length requires it.

### Onboarding, Settings, And Management

- Mandatory onboarding preserves one clear next action, progress text, recovery from validation/network failure, and no skip affordance.
- Settings navigation uses a drawer/select on phone, a readable two-column layout on tablet, and persistent navigation on desktop.
- Destructive settings actions remain visually separated and explicitly confirmed.
- Workspace/member/invitation management favors readable cards or responsive tables with no horizontally clipped action.

## State Matrix

Every affected workflow is verified in loading, empty, populated, validation-error, server-error, disabled/processing, success, offline, and restored-online states where those states exist. All three locales are checked with representative long content and user-controlled names.

## Accessibility Contract

- One page `h1`, sequential heading hierarchy, landmark preservation, native controls before ARIA, and no nested interactive elements.
- Keyboard and switch access follows DOM order. Dialog/sheet focus is trapped and restored. Every primary action remains reachable at 320 px, 200% zoom, and short landscape.
- Focus is visible at 3:1 or better against adjacent colors. Status never relies on color alone.
- Normal text contrast is at least 4.5:1; large text and essential graphical controls are at least 3:1.
- Reduced motion disables nonessential animation; forced colors retains borders, selection, focus, and status meaning.
- NativePHP safe-area insets are consumed once, not duplicated between body, shell, and fixed action regions.

## Verification Matrix

- Viewports: 320×568, 360×800, 390×844, 430×932, 768×1024, 820×1180, 1024×768, 844×390, 1180×820, 1440×900.
- Zoom/reflow: 200% and 400% where browser support permits; browser text scaling and Android font scaling receive physical-device checks.
- Input: keyboard, fine pointer, coarse pointer/touch, focus restoration, and screen-reader accessible-tree inspection.
- Preferences: reduced motion, forced colors, offline/online transition.
- Locales: English, Lithuanian, Russian with long task/project/workspace/user names.
- Runtimes: Herd web, isolated Chrome DevTools, isolated Playwright, Android emulator, and the explicitly resolved physical Samsung only after all prior gates pass.

## Delivery Boundaries

- Each vertical slice begins with a `docs/progress.md` start record and a failing regression.
- Each slice runs focused Pest/frontend tests, Vue type checking, ESLint, Prettier, and a production build as applicable.
- Final delivery additionally runs Pint, Larastan, complete Pest, fresh isolated migration/seed health, Composer validation/audit, npm audit, browser matrix, Android build/emulator inspection, diff review, semantic commit, normal `origin main` push, and only then the exact-hash Samsung APK replacement.

## Non-Goals

- No dark mode, runtime theme family, separate senior product, browser/device sniffing, separate mobile codebase, bottom navigation added without workflow evidence, swipe-only action, schema change, dependency change, Vue Router, Livewire, Filament, raw SQL, or remote synchronization requirement.
