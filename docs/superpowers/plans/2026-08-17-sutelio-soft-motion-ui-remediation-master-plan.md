# Sutelio Soft Motion And UI Remediation Master Program Plan

> **For agentic workers:** This file is the dependency and ownership program, not a single implementation batch. Execute only a focused child plan after its entry gate is green. Child plans use superpowers:executing-plans inline unless repository policy explicitly authorizes delegation.

**Goal:** Deliver a quiet, high-coverage motion system and close every remaining UI/UX audit finding through small, measurable, independently revertible packets without duplicating delivered constructor/responsive work or colliding with active parallel streams.

**Architecture:** The program corrects the lowest coherent owner in this order: accessibility/recovery truth, CSS motion tokens, interaction/overlay primitives, focused domain journeys, then browser/native release evidence. One master program preserves complete scope; focused child plans provide exact TDD steps against the committed source snapshot that will actually be edited.

**Tech Stack:** Laravel 13.25, PHP 8.5, Inertia Laravel 3.3, Inertia Vue 3.6, Vue 3.5, TypeScript 6, Tailwind CSS 4.3, `tw-animate-css` 1.2, Reka UI 2.10, Lucide Vue 1.31, NativePHP Mobile 4.2, Pest 5.1, Vite 8.2, SQLite, isolated Chrome DevTools MCP, and isolated Playwright MCP.

---

## Program Role

This document replaces the previous 644-line pseudo-implementation plan. The earlier version had correct coverage but failed execution quality in four ways:

1. twenty-eight tasks mixed program scope, feature design, implementation, browser QA, release, rename, and physical-device mutation;
2. tasks were too large to review and did not contain the bite-sized RED/GREEN/commit steps required of an implementation plan;
3. it repeated components that were delivered by later constructor commits;
4. it overlapped active global-operation, gradient-control, responsive, timezone, and icon work without exact file ownership.

The optimized structure keeps this file stable as the cross-program source of truth and moves executable source changes into focused child plans. The first authored implementation-ready packet is:

- `docs/superpowers/plans/2026-08-17-sutelio-soft-motion-foundation.md`

Its execution starts only after Package 1 and its active file owners have settled. Later domain packets are written only after the foundation is committed and their current source owners settle. This is deliberate just-in-time planning, not an omitted requirement: exact code and line locations must be derived from the committed code that a packet will edit, not from the current shared dirty worktree.

## Authority And Scope

For unfinished presentation work this program supersedes:

- `2026-08-17-sutelio-light-motion-icons-and-final-device.md`;
- `2026-08-17-sutelio-responsive-style-optimization.md` after its verified implementation is published;
- unresolved work in `2026-08-17-sutelio-ui-constructor-system.md`;
- the remediation planning gate in `docs/implementation-plan.md`.

Historical documents remain evidence. An unchecked historical checkbox is never stronger than current source, reachability, tests, commits, and canonical progress.

Excluded domain logic remains owned by:

- `2026-08-17-localized-timezone-preferences.md`;
- `2026-08-17-sutelio-database-optimization.md`;
- `2026-08-17-global-operation-feedback.md`;
- `2026-08-17-gradient-control-system.md`.

The program integrates their committed presentation boundaries but does not absorb their data, request, gradient, busy-state, schema, or verification logic.

Email verification remains permanently disabled. No verification route, middleware, model contract, notification, interface, or test may be restored.

## Current Planning Snapshot

At the quality-review snapshot:

- local `main`: `272a2a9`;
- `origin/main`: `ff8d416`;
- local branch: twelve commits ahead, zero behind;
- worktree: extensive staged and unstaged responsive, motion/icon, timezone, global-operation, gradient, UI audit, test, CSS, component, and documentation work;
- presentation inventory: 260 Vue files, 95 motion/transition consumers, 61 explicit reduced-motion consumers, eight `transition-all` files, eight layout-transition files plus the in-progress busy overlay, and 27 generic entrance/stagger consumers.

These figures are a coordination snapshot, not a delivery claim. Package 0 refreshes them after active owners settle.

## Delivered Work That Must Not Be Reimplemented

| Contract                            | Commit    |
| ----------------------------------- | --------- |
| Shared Field/Textarea               | `8477eee` |
| Task title/description compositions | `2b848a6` |
| DialogBody/DialogActions            | `4d5ece0` |
| Workspace confirmation composition  | `cf0a7a4` |
| Account/workspace dialog consumers  | `848998d` |
| Button loading/async contract       | `375babf` |
| InlineState                         | `b8e1036` |
| ColorSwatch                         | `c2d8eb7` |
| Workspace SearchField consumers     | `1562c69` |
| ResultSummary/PaginationBar         | `fc2e6f1` |
| SurfacePanel                        | `272a2a9` |
| Relation indexes                    | `4357ff5` |

Responsive source and browser verification are complete in the worktree but remain unpublished. They become delivered only after an attributable commit/push record exists.

## Ownership And Conflict Matrix

| Shared write set                                          | Current owner                                                  | Motion program rule                                                        |
| --------------------------------------------------------- | -------------------------------------------------------------- | -------------------------------------------------------------------------- |
| `resources/css/app.css`                                   | responsive, global-operation, gradient, then motion foundation | no simultaneous editing; motion runs last                                  |
| Button/Input/Textarea/Select/Checkbox/OTP                 | gradient-control plus delivered Button loading                 | preserve final gradient/loading API; motion only narrows properties/timing |
| App root, Inertia lifecycle, global overlay               | global-operation-feedback                                      | busy counter/lock stays there; motion consumes its final state hooks       |
| Preferences/onboarding timezone controls and translations | localized-timezone                                             | settings/onboarding motion waits for committed props/types/copy            |
| IconTile, headings, page icons, motion test               | motion/icon worktree                                           | reconcile into foundation; do not duplicate icon semantics                 |
| Page frame, safe areas, overlays, pointer rules           | responsive worktree                                            | publish first; motion preserves verified geometry                          |
| Database/index/query files                                | database plan                                                  | presentation query delta remains zero                                      |

If two active packets need the same file, the later packet waits. Staging tricks do not make overlapping source edits independent.

## Dependency Graph

```text
Package 0: settle owners and rebaseline
    ├── responsive publication
    ├── gradient-control publication
    ├── global-operation-feedback publication
    ├── localized-timezone publication where files overlap
    └── motion/icon reconciliation
            ↓
Package 1: P1 persistent accessibility and recovery truth
            ↓
Package 2: soft-motion foundation
            ↓
Package 3: primitive and overlay motion
            ↓
Package 4: domain journey rollouts
            ↓
Package 5: P2 consolidation and reachability closure
            ↓
Package 6: browser, accessibility, and performance proof
            ↓
Package 7: full gates → APK/emulator → rename → Samsung last
```

Package 1 may run in independent files while active streams settle, but only when its exact write set has no overlap.

## Packet Quality Contract

Every executable child packet must:

- own one shared contract or one domain journey;
- target approximately 100 changed lines and normally stay below 300 reviewable changed lines;
- separate refactoring from behavior;
- list exact paths from current committed `main`;
- show the failing test, exact command, expected RED reason, minimal implementation, GREEN command, and commit message;
- migrate one representative consumer before any larger expansion;
- keep each intermediate commit functional and independently revertible;
- record query delta, accessibility states, reduced-motion behavior, bundle/performance delta, diff review, commit, and push truth;
- stop when a parallel owner changes the same file and rebaseline instead of resolving by guesswork.

No packet may claim quality because a source scan passes. Rendered geometry, focus, computed contrast, actual animation timing, interruptibility, and device smoothness require browser/native evidence.

## Package 0: Settle Active Owners And Freeze An Executable Baseline

**Entry:** documentation-only work may proceed; source implementation is blocked by overlapping dirty files.

**Actions:**

- publish or explicitly abandon the verified responsive slice;
- finish or isolate global-operation feedback and gradient-control work;
- finish localized-timezone files before settings/onboarding motion;
- reconcile current motion/icon work with the optimized design;
- fetch and record local/remote ancestry without force push;
- regenerate reachability, motion, reduced-motion, transition, raw-tone, direct-control, async-state, and dormant-component inventories;
- update child-plan line locations from the committed snapshot.

**Exit:** every file in the next packet has one owner, staged/unstaged provenance is known, and no unrelated ancestor would be pushed accidentally.

**Rollback:** inventory/docs only.

## Package 1: Close P1 Persistent Accessibility And Recovery Truth

P1 behavior is fixed before motion can decorate its states.

### P1-A Contrast

Owner: semantic foreground tokens first, then only consumers not solved by tokens.

Exit: audited normal text reaches 4.5:1, large text 3:1, forced colors stays readable, and no raw per-page brand color is introduced.

### P1-B First-run focus

Owner: mandatory language dialog plus guest shell completion target.

Exit: confirmation never returns focus to `body`; idle decorative pulse is absent; registration still enters onboarding without email verification.

### P1-C Coarse-pointer targets

Owner: shared interaction-size contract, sidebar actions, pressed/inline controls, then dense checklist/taxonomy composition.

Exit: actionable targets reach 44×44 CSS px or equivalent non-overlapping spacing at coarse pointer; 320/390 px layouts remain readable and overflow-free.

### P1-D Inline comment validation

Owner: `TaskCommentsPanel.vue` through delivered Field/Textarea/InputError contracts.

Exit: failed edits retain drafts, render/announce the translated error, expose `aria-invalid`/`aria-describedby`, and never show success motion.

### P1-E Offline/request outcome

This is a separate child specification/plan after global-operation feedback lands. The busy overlay owns only foreground activity and interaction lock. The request-outcome layer owns offline, network failure, validation, authorization, conflict, retry safety, confirmed success, and draft retention. It may not claim queued local sync unless a durable queue exists.

Exit: representative create/edit/delete flows explain truth and recovery without duplicating busy state or domain drafts.

## Package 2: Deliver The Soft-Motion Foundation

Executable owner: `2026-08-17-sutelio-soft-motion-foundation.md`.

Scope:

- new duration/easing tokens and measurable budgets;
- remove generic page/Card/static-list entrances;
- replace `transition-all` with named properties;
- replace visual progress width/height motion with transform fill where correct;
- retain only measured sidebar geometry exceptions;
- align shared overlay enter/exit timing;
- enforce reduced-motion parity and no new dependency;
- preserve responsive geometry, gradients, Button loading, global busy truth, and icon semantics.

Exit:

- zero generic route/page/Card/static-list entrance;
- zero unexplained `transition-all`;
- zero animation-induced layout shift;
- foundation source and browser checks pass;
- motion-only gzip CSS growth is no more than 2 kB unless evidence approves an exception.

## Package 3: Primitive And Overlay Motion

Write one focused child plan per primitive family after Package 2:

1. controls: Button, link, checkbox, OTP, segmented/pressed controls;
2. fields: Input, Textarea, Select, validation, focus, disabled state;
3. overlays: Dialog, Sheet, Dropdown, Select content, Tooltip/Popover, toast;
4. collection helpers: insert/remove/reorder only, never initial mount;
5. progress/status: Spinner, Skeleton, InlineState, global busy overlay, semantic state accents.

Each family starts from a state matrix: default, hover where supported, focus, active, disabled, loading, error, success, reduced motion, forced colors, coarse pointer, rapid repeat, cancellation, and unmount.

Exit: shared primitives own timing/property/accessibility behavior; consumers no longer carry equivalent recipes.

## Package 4: Domain Journey Rollouts

Motion migrates by user journey, not by a repository-wide class replacement:

1. authentication and first run;
2. onboarding;
3. dashboard;
4. projects;
5. workspaces and members;
6. tasks, including the sole signature completion moment;
7. calendar;
8. activity and notifications;
9. settings/security/data safety.

Each journey packet inventories its reachable screens and states, reuses Package 3 primitives, adds only domain-specific state triggers, and browser-tests EN/LT/RU plus phone/tablet/desktop/reduced-motion paths before commit.

Task completion is the only signature animation. Other domain motion stays within feedback/state/spatial/collection/progress roles.

Exit: every reachable meaningful state is classified required/spatial/progress/signature/static and has reduced-motion parity.

## Package 5: Close P2 System Drift And Reachability

Close the audit after behavioral motion is stable:

- simplify over-framed surface hierarchy without flattening meaningful boundaries;
- finish semantic status-tone migration without turning warnings/success/domain identity orange;
- finish only constructor abstractions with proven equivalent consumers;
- classify `CommandPalette`, `AppHeader`, `TaskStats`, `NavFooter`, and refreshed candidates as active, compatibility, planned, or dead;
- remove confirmed dead presentation only in a separate reversible commit with explicit evidence;
- split `WorkspaceConfigurationPanel` only after characterization tests and only if the current file remains oversized after concurrent work.

Exit: UI-06, UI-08, UI-09, UI-10, and UI-11 are closed with source, test, and browser evidence.

## Package 6: Browser, Accessibility, And Performance Proof

Use disposable isolated Chrome DevTools and Playwright MCP profiles.

Required dimensions:

- routes: guest, auth, onboarding, dashboard, projects/detail, tasks/detail, calendar, activity, notifications, settings/security/data safety, workspaces/detail, dialogs/sheets/menus/filters;
- locales: EN/LT/RU;
- viewports: 320×568, 360×800, 390×844, 430×932, 768×1024, 820×1180, 1024×768, 1280×800, 1440×1000, 1920×1080, and phone landscape;
- modes: 200% zoom/reflow, keyboard, coarse pointer, reduced motion, forced colors, OS dark preference while app stays light;
- states: idle, hover/focus/active, loading, empty, populated, validation, pending, success, failure, rollback, offline, reconnect, disabled, rapid repeat, cancellation, and unmount.

Performance evidence:

- computed transition/animation durations and `document.getAnimations()` inventory;
- Performance trace around representative overlay, list update, task completion, navigation, and global operation;
- INP at or below 200 ms in representative local flows;
- no motion-caused long animation frame over 50 ms;
- zero animation-attributed layout shift;
- no repeated missed-frame pattern on target Android emulator/device;
- final bundle comparison with no new motion dependency.

Exit: all five P1 and six P2 findings have current rendered evidence; automation is not used as a substitute for visual/interaction review.

## Package 7: Full Gates And Irreversible Delivery Sequence

Order is fixed:

1. Pint, Larastan, focused/full sequential Pest, supported parallel Pest, frontend tests, Vue types, ESLint, Prettier, Composer/npm validation/audit, isolated SQLite migration/seed/health, web/Android/iOS Vite builds, query budgets, diff/secret review;
2. build the final APK and record path, size, SHA-256, package ID, version, permissions, SDK bounds, signature/alignment/archive evidence;
3. install and exercise that exact APK hash in the emulator;
4. complete repository/remote/checkout/Herd rename and re-run smoke from the renamed site;
5. prove local `HEAD`, `origin/main`, and remote `main` synchronization;
6. resolve the connected Samsung serial explicitly;
7. install the exact verified APK and perform final read-only smoke/logcat/screenshot evidence.

The Samsung installation is the final mutating action. No force push, history rewrite, broad package deletion, personal browser profile, real-database `migrate:fresh`, or ambiguous multi-device target is allowed.

## Historical Task Coverage Map

| Previous tasks                             | Optimized owner                                   |
| ------------------------------------------ | ------------------------------------------------- |
| 0–6: baseline and P1                       | Packages 0–1                                      |
| 7–10: motion foundation/properties         | Package 2 child plan                              |
| 11–17: tones and domain motion             | Packages 3–5                                      |
| 18–20: responsive/constructor/reachability | Packages 0, 3, and 5 with delivered work excluded |
| 21–24: static/browser/full gates           | Package 6 and Package 7 step 1                    |
| 25–27: APK/rename/Samsung                  | Package 7 steps 2–7                               |

Nothing from the previous scope is dropped; duplicated or already delivered implementation is removed.

## Program Acceptance

- Every meaningful reachable state is classified; quantity is measured by coverage, not moving-node count.
- Most transitions use 90–260 ms; the task-completion signature is bounded at 340 ms maximum.
- Generic route/page/Card/static-list entrances, decorative loops, and unexplained `transition-all` are absent.
- Reduced motion communicates the same truth immediately.
- P1 findings close before P2 polish is claimed.
- Shared primitives own repeated behavior; domain components own business state.
- Global busy, offline outcome, responsive geometry, gradients, timezone, and motion have non-overlapping owners.
- Presentation query delta is zero unless a separately approved, budget-tested feature proves otherwise.
- Full browser/native/performance evidence is current and factual.
- Email verification remains absent.
- No unrelated local commit is pushed accidentally.
- Final Samsung installation happens only after every prior gate and rename is complete.
