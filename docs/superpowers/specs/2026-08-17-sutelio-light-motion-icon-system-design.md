# Sutelio Light Motion And Icon System Design

**Date:** 2026-08-17

**Status:** Approved automatically by the product owner's standing recommendation rule

**Selected direction:** Living orchestration — expressive, purposeful motion without continuous visual noise

## Goal

Complete a coherent Sutelio interface system in which the exact logo orange and cobalt are visible throughout the product, the presentation is permanently light-only, repeated headings and actions use consistent icons, and motion makes navigation, hierarchy, loading, success, error, and state changes feel responsive. The implementation must remain accessible, local-first, workspace-safe, and maintainable through shared primitives instead of page-by-page styling drift.

The final Sutelio APK is installed on the already connected Samsung phone only after every source, documentation, browser, emulator, build, security, data-safety, and Git delivery gate is complete. Wi-Fi availability does not change the USB-first installation or local-data safety boundary.

## Existing Baseline

The repository already contains the correct foundations:

- Sutelio's master brand colors are cobalt `#123C8B`, deep cobalt `#0A285F`, Signal Orange `#FF6038`, and ivory `#FFF8E9`.
- The complete application orange ramp is anchored at exact Signal Orange; primary foregrounds use deep cobalt on exact orange or white on the accessible darker orange `#CD431F`.
- The web application is fixed light-only. Appearance persistence, dark/system selectors, and first-party `dark:` branches have already been removed and are guarded by Pest.
- Shared motion durations are `140ms`, `240ms`, and `480ms`; `ui-enter`, `ui-control`, `ui-stagger`, step transitions, and a global reduced-motion clamp already exist.
- The current inventory contains 244 Vue components, 117 components with Lucide imports, 75 components with motion utilities, and 57 components with explicit `motion-reduce:` handling.
- `LeadingIconHeading.vue`, `WorkspacePageHeader.vue`, `EmptyState.vue`, shared buttons, dialogs, sheets, menus, navigation, and status components already provide reusable seams. The leading-heading contract is top-aligned, non-wrapping at the row level, and safe for long EN/LT/RU text.

This phase deepens those foundations. It does not introduce a second theme, a second icon library, a motion dependency, per-page color constants, decorative gradients, or permanent looping animation.

## Product Decisions

### 1. Autonomous recommendation

When several safe, in-scope variants are possible, implementation uses the strongest evidence-backed recommendation and continues without interrupting the user. A question is reserved for a true blocker that requires new authority, unavailable credentials, or a materially different/irreversible product decision.

### 2. Light-only means one runtime contract

The document, browser chrome metadata, layouts, components, charts, overlays, dialogs, native splash, and screenshots use one light presentation. An operating-system dark preference must not switch palettes or expose dormant dark classes. Forced colors remains a separate accessibility adaptation, not a theme.

### 3. Orange and cobalt have distinct jobs

- Exact Signal Orange identifies the brand, active progress, selection, focus emphasis, highlights, and non-text decorative accents.
- Accessible orange-600 `#CD431F` carries white normal-size text on solid primary controls.
- Cobalt `#123C8B` anchors brand tiles, navigation identity, selected structural surfaces, and strong icon containers.
- Deep cobalt `#0A285F` carries prominent text/icons on exact orange and gives headings, links, and information hierarchy a recognizably Sutelio character.
- Destructive, success, warning, information, chart, and user/domain colors keep their semantic meaning and are not indiscriminately recolored.

### 4. Icons are systematic, not ornamental clutter

Lucide remains the only application icon set. Icons are added where they improve scanning or action recognition:

- page and section headings;
- primary and secondary actions;
- navigation, filters, sorting, search, import/export, backup, security, members, settings, and workspace controls;
- loading, empty, success, warning, error, offline, and permission states;
- compact metrics and metadata where an icon reinforces a visible label.

Icons do not replace required visible text, repeat an adjacent icon without purpose, appear inside dense prose, or create icon-only controls without an accessible name and tooltip. Decorative icons use `aria-hidden="true"`; state icons retain non-color text. Shared wrappers own size, stroke, tile, spacing, alignment, focus, forced-color, and motion behavior.

### 5. Motion is expressive but event-driven

The selected motion direction is “living orchestration”:

- page and major-surface entry uses one calm reveal;
- related lists/cards may use a bounded stagger;
- controls use hover, press, focus, and icon-response micro-interactions;
- dialogs, sheets, menus, collapsibles, filters, and onboarding steps animate spatially according to their origin/direction;
- mutations expose pending, optimistic, success, rollback, and error transitions;
- progress, skeleton, and spinner motion runs only while work is active;
- status changes may use a short one-shot accent, never an infinite decorative loop;
- layout movement avoids content jumps and does not delay interaction.

“Animation everywhere” therefore means every meaningful interaction/state transition is evaluated and receives a purposeful response when useful. It does not mean every visible element moves continuously.

Reduced-motion is a first-class behavior: all transform, entrance, stagger, spinner, pulse, and transition effects resolve to a stable near-zero-duration state without hiding content, delaying state, or removing feedback. Forced-colors keeps native semantics, visible focus, and readable labels.

## Architecture

### Brand and motion tokens

`resources/css/app.css` remains the single CSS-first Tailwind source for brand and motion tokens. New motion primitives extend the existing `ui-*` namespace and use the existing duration/easing variables. Raw Sutelio hex values are prohibited in Vue consumers except existing documented boundary cases that tests explicitly own.

### Shared presentation primitives

The implementation prefers a small reusable layer:

- `LeadingIconHeading` for icon + localized title/description blocks;
- `WorkspacePageHeader` for page identity and actions;
- shared icon-tile treatment for section/status icons;
- shared motion classes for reveal, stagger, lift, press, icon response, state change, and reduced-motion behavior;
- existing Button, Badge, Alert, Dialog, Sheet, Dropdown, Select, Sidebar, Skeleton, Spinner, EmptyState, and toast components as centralized consumers.

Primitive changes precede consumer changes. Each consumer is migrated only when its semantics match the primitive; special-purpose visualizations, calendar cells, dense data rows, and native controls are not forced into the wrong abstraction.

### Vue/Inertia boundaries

Components continue to use Vue Composition API with `<script setup lang="ts">`, typed props/emits, immutable Inertia props, existing Wayfinder routes/actions, and existing request flows. Visual work does not add queries, routes, API calls, persistence, packages, or client-side routers. State animation derives from existing reactive state; it does not create duplicate business state.

## Deep Audit Method

The implementation inventory covers all first-party Vue, CSS, Blade bootstrap, tests, and current documentation, excluding generated/vendor/native build output. Each surface is classified by:

1. hierarchy: page, section, card, row, detail, dialog, sheet, menu;
2. icon need: identity, action, status, metadata, or none;
3. motion need: enter, interaction, disclosure, mutation, progress, state change, or none;
4. accessibility: visible label, accessible name, keyboard/focus, reduced motion, forced colors, zoom, touch target;
5. reuse opportunity: existing primitive, safe extension, new focused primitive, or intentional exception.

The audit produces explicit inventories in Pest/frontend tests so later pages cannot silently lose the shared contracts.

## Error Handling And Safety

- Animation never masks server or validation errors, blocks form submission, or turns failure into a success-looking transition.
- Pending controls remain disabled according to existing request state; spinners and labels expose `aria-busy`/status semantics where applicable.
- Optimistic motion is used only where rollback is already safe and visible.
- No database, authorization, workspace-isolation, backup, email-verification, registration, or package-identity behavior changes.
- Email verification remains permanently disabled and registration continues directly to onboarding.
- Existing Samsung data and the old package sandbox are preserved. Only `com.goleaf.sutelio` may be replaced during the final physical-device installation.

## Verification Strategy

### Automated

- Failing-first Pest inventory tests for light-only, token ownership, icon consumers, semantic labels, motion primitives, reduced-motion coverage, and forbidden raw/legacy patterns.
- Existing focused feature/frontend tests after each component cluster.
- Vue type checking, ESLint, Prettier, production web/Android builds, Pint/Larastan/Pest, Composer/npm validation/audit/outdated, and isolated SQLite migration/seed/health gates.

### Browser

Use isolated Chrome DevTools MCP and Playwright MCP sessions only. Verify guest, authenticated, onboarding, dashboard, projects, tasks, calendar, activity, notifications, settings, workspace management, dialogs, sheets, empty/loading/error/success states at phone, tablet, and desktop widths. Check keyboard, 200% zoom, reduced motion, forced colors, OS dark preference while the app remains light, console/network errors, favicon/metadata, layout shift, and horizontal overflow.

### Native and physical device

Build and inspect the branded APK, verify it first in an emulator, and retain artifact hashes/manifest/resource evidence. Complete all GitHub/Herd rename and final documentation gates. Only then enumerate the connected Samsung serial, verify USB authorization and package boundaries, install the final APK, launch it, exercise smoke flows, inspect logcat/screenshots, and confirm the app survives a relaunch. The phone installation is the last mutating action in the entire program.

## Completion Contract

The work is complete only when:

- the current UI is consistently light-only and uses the approved orange/cobalt roles;
- audited meaningful headings/actions/states have reusable icons without accessibility regressions;
- audited meaningful interactions/states have purposeful motion and reduced-motion parity;
- no runtime, console, network, test, type, lint, format, audit, build, database-health, APK, emulator, or physical-device blocker remains unreported;
- canonical documentation and progress evidence match actual behavior;
- all attributable changes are committed and normally pushed on `main` without rewriting history;
- the final branded APK is installed and launched on the connected Samsung phone as the final action.
