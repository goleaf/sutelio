# Sutelio Soft Motion Master Design

**Date:** 2026-08-17

**Status:** Approved by the product owner's explicit request for maximum soft motion and the repository's autonomous recommendation rule

**Selected direction:** High coverage, low amplitude

## Goal

Make Sutelio feel continuously responsive and alive without making it theatrical, restless, or slow. Almost every meaningful interaction, state transition, disclosure, mutation outcome, and spatial relationship should receive a brief visual response. Static content must become stable immediately, and nothing decorative may move indefinitely.

This design refines and supersedes the motion portion of `2026-08-17-sutelio-light-motion-icon-system-design.md`. It preserves that document's light-only brand, icon, accessibility, local-first, and final-device boundaries while replacing generic 480 ms page/card/list entrances with a denser system of shorter event-driven transitions.

## Current Evidence

The current shared-worktree planning snapshot contains:

- 260 first-party Vue files;
- 95 Vue/TypeScript files with a motion or transition signal;
- 61 files with an explicit reduced-motion utility;
- eight files with `transition-all`;
- eight files with layout-property transitions, plus an in-progress global busy overlay using a width transition;
- 27 files coupled to generic `ui-enter`, `ui-surface`, `ui-page-surface`, or `ui-stagger` behavior;
- generic page, Card, and list entrances using the 480 ms emphasized token;
- active progress bars animating `width` or `height` and sidebar geometry animating layout properties;
- a permanent decorative pulse in the first-run language dialog;
- a shared reduced-motion clamp that is already a sound foundation.

These values describe a moving, uncommitted integration snapshot and are not completion evidence. Responsive implementation is verified but not yet published; global operation feedback, gradient controls, localized timezone presentation, motion/icons, and documentation are concurrent owners. Every implementation packet must refresh its exact inventory after those owners settle.

The UI/UX audit also records five P1 accessibility/recovery defects and six P2 system-quality findings. Motion cannot be expanded independently of contrast, focus return, touch targets, offline outcomes, inline validation, surface hierarchy, semantic status colors, shared controls, and reachability because those states determine what the animation must communicate.

## Considered Directions

### A. High coverage, low amplitude — selected

Every meaningful event is considered for motion, but the visual displacement, scale, duration, and repetition are tightly bounded. Feedback is frequent, local, and reversible. Static page-load choreography is removed.

This direction best satisfies the request for the maximum amount of gentle motion while preserving task speed, accessibility, Android WebView performance, and the product-tool character.

### B. Universal ambient motion

Most surfaces would continuously float, pulse, shimmer, or react to pointer movement. It creates a visibly animated product but adds visual noise, competes with content, increases motion sensitivity risk, and wastes mobile resources. Rejected.

### C. Sparse cinematic moments

Only onboarding, task completion, and major navigation would receive polished animation. It would be calmer but would not satisfy the requested breadth of responsive feedback. Rejected.

## Motion Principles

1. **Animate causality, not existence.** Motion follows a user action, a state change, new data, or a spatial disclosure. A page, Card, row, or heading does not animate merely because it rendered.
2. **High coverage, low intensity.** Coverage is measured by classified meaningful states, not by the number of moving elements. Most responses use one to four pixels of travel, a one-to-two percent scale range, and 90–260 ms duration.
3. **Stable first paint.** Route content is readable and actionable immediately. Navigation may preserve context with a progress cue or targeted crossfade, but there is no sequential page reveal.
4. **One signature moment.** Completing a task may use one restrained check flourish and row-resolution transition. There is no confetti, bounce, elastic spring, particle effect, or celebratory loop.
5. **Feedback survives reduced motion.** Removing transform and travel must not remove state communication. Labels, icons, live regions, focus, disabled state, and semantic color remain sufficient.
6. **Motion never owns business truth.** Animation reflects existing reactive/request state. It cannot invent optimistic success, conceal failure, delay authorization, or duplicate domain state.
7. **Interruption is normal.** Every transition must tolerate rapid repeat interaction, navigation, cancellation, rollback, and component unmount without leaving stale inline styles or blocked controls.

## Token Contract

`resources/css/app.css` remains the single CSS-first owner. The implementation replaces the old three-duration model with named roles:

| Role                 |         Target | Typical use                                               |
| -------------------- | -------------: | --------------------------------------------------------- |
| `--motion-snap`      |          90 ms | release, deselection, focus-color cleanup, immediate exit |
| `--motion-feedback`  |         130 ms | press, hover on fine pointer, checkbox, icon response     |
| `--motion-state`     |         190 ms | validation, selection, pending/success/error, row state   |
| `--motion-spatial`   |         260 ms | menu, popover, dialog, collapsible, filter disclosure     |
| `--motion-signature` | 340 ms maximum | one-shot task completion or onboarding milestone          |

The current `480ms` emphasized duration is removed from ordinary UI. A longer interval is allowed only for an explicitly named, one-shot signature state and never for route entry, generic Cards, lists, hover, focus, or form feedback.

Easing roles:

- standard enter/change: `cubic-bezier(0.2, 0, 0, 1)`;
- emphasized settle: `cubic-bezier(0.16, 1, 0.3, 1)`;
- exit: a faster non-bouncing ease-in curve;
- no spring, overshoot, elastic, or bounce easing.

Amplitude limits:

- control press: scale no lower than `0.985`;
- hover lift on a fine pointer: at most two pixels;
- decorative icon response: one to two pixels or at most two degrees rotation;
- disclosure indicators may rotate 90 or 180 degrees when rotation directly represents expanded/collapsed state;
- local reveal: at most four pixels;
- overlay directional cue: normally 12–24 pixels, not a dramatic full-screen flight;
- stagger: 16–24 ms between at most six newly revealed related children, capped at 100 ms total and never applied to an entire route or a static initial collection.

## Motion Eligibility And Attention Budget

Every reachable state change is classified before implementation:

| Classification        | Rule                                                                   | Examples                                                              |
| --------------------- | ---------------------------------------------------------------------- | --------------------------------------------------------------------- |
| Required feedback     | The user otherwise cannot tell the input was accepted or state changed | press, selection, validation, pending, success, rollback              |
| Spatial clarification | Motion explains origin, destination, hierarchy, or reordering          | dialog, sheet, menu, disclosure, board reorder                        |
| Progress reassurance  | Motion is active only while real work is active                        | spinner, skeleton, determinate progress                               |
| Signature             | One restrained product-defining result                                 | confirmed task completion                                             |
| Immediate/static      | Motion adds no information or would replay existing content            | route first paint, static Card, unchanged list, decorative background |

The default for an unclassified element is static. “Maximum motion” means 100% of meaningful states receive this decision, not that every classified state must animate.

Only one attention-bearing state animation may run in a major region at a time. Local press/focus feedback may coexist because it does not compete for attention. A new signature, error, or global-operation state cancels or supersedes lower-priority flourish in the same region.

## Quantitative Quality Budget

The motion system is not accepted by visual taste alone:

- 100% of reachable interactive primitives have default, hover where supported, focus, active, disabled, loading where applicable, error, success, and reduced-motion decisions;
- 100% of intentional animation owners have an immediate reduced-motion equivalent;
- zero generic route/page/Card/static-list entrances;
- zero permanent decorative loops;
- zero unexplained `transition-all` uses;
- zero animation-attributed cumulative layout shift;
- representative interaction feedback begins within 100 ms of input;
- representative browser flows meet INP at or below 200 ms and add no motion-caused long animation frame above 50 ms;
- CSS/JS motion remains interruptible during rapid repeat, route change, cancellation, and unmount;
- the motion-only bundle delta stays at zero new runtime dependencies, no new JavaScript animation library, and at most 2 kB gzip CSS growth unless measured evidence justifies an exception;
- target-device traces remain visually smooth at 60 Hz, with no repeated missed-frame pattern during the audited animation.

Browser evidence uses computed styles, `document.getAnimations()`, Performance traces, layout-shift observation, and real interaction paths. Source tests enforce ownership and prohibitions but cannot prove smoothness or perceived quality.

## Motion Layers

### 1. Feedback layer

Apply to Buttons, links, icon actions, checkbox/radio/switch controls, pressed buttons, segmented controls, input focus, copy/reveal actions, drag handles, and calendar navigation.

- fine-pointer hover may shift color, border, shadow, icon, or a maximum two-pixel transform;
- coarse pointers receive press/focus feedback without hover-only movement;
- active press resolves within the control and never shifts surrounding layout;
- disabled and pending controls do not react as if actionable;
- visible focus is immediate and must not wait for an animation.

### 2. State layer

Apply to validation errors, selection, pending work, save success, failure, rollback, unread/read state, filter activation, offline/reconnected state, empty-to-populated transitions, and inline status changes.

- errors appear beside their field with a short opacity/transform reveal and a stable live-region relationship;
- pending labels and spinners use the existing request truth and preserve button width where practical;
- success accents run once and settle into the persistent semantic state;
- rollback reverses the local state transition and keeps the error explanation visible;
- color is always paired with text, an icon, shape, or native state.

### 3. Spatial layer

Apply to Dialog, Sheet, Dropdown, Select, Tooltip, Popover, collapsible regions, responsive filter panels, sidebar states, onboarding steps, and navigation context changes.

- direction explains origin: bottom sheets settle upward, side sheets settle from their edge, menus scale/fade from their trigger origin;
- overlay enter uses the spatial duration; exit uses 70–80% of enter duration;
- focus trap, initial focus, return/forward focus, Escape policy, and scroll containment are never delayed by animation;
- 200% zoom and narrow viewports must not convert motion into overflow.

### 4. Collection and direct-manipulation layer

Apply only when items are inserted, removed, reordered, filtered, expanded, or updated while the user is viewing the collection.

- no list-wide reanimation after route load or background refetch;
- Vue `TransitionGroup` or a small Web Animations FLIP helper may animate transform/opacity for genuine reorder only;
- pagination and filtering retain positional context instead of replaying all rows;
- board/calendar movement must remain touch-scrollable and keyboard operable.

### 5. Progress and data layer

Apply to active spinners, skeletons, upload/export/import/backup progress, onboarding progress, project progress, profile completion, and dashboard charts.

- infinite movement is allowed only while an operation is genuinely pending;
- skeleton pulse stops when content resolves and becomes static under reduced motion;
- progress bars and chart bars use transform-based fill where geometry permits;
- genuine sidebar geometry may use a narrowly named, measured exception when a transform would break hit testing or layout semantics;
- `transition-all` is prohibited.

### 6. Signature layer

Sutelio has one restrained signature animation: task completion.

- the completion control confirms immediately;
- the check mark draws or scales once;
- the row resolves with a subtle opacity/position change without disappearing before the server result is known;
- success completes within 240–340 ms;
- failure/rollback reverses cleanly and shows the translated error;
- reduced motion replaces the flourish with an immediate icon/state swap.

## Surface-by-Surface Coverage

### Global shell and navigation

- route progress, active navigation marker, sidebar expand/collapse, workspace switch, notification indicator, language change, and responsive navigation disclosure;
- no route-wide fade or page-child stagger;
- dormant shell components are not animated until reachability is proved.

### Authentication and first run

- field focus, validation, password reveal, two-factor method switch, recovery-code reveal, language selection, confirm pending/success, and deliberate forward focus after the mandatory dialog;
- remove the permanent decorative orange pulse.

### Onboarding

- mode/option selection, step direction, progress, create/select results, validation, skip/continue processing, recovery/replay, and final result confirmation;
- step motion is short, directional, and bounded; no decorative background movement.

### Dashboard, projects, and workspaces

- queue state, metric changes, chart fill, project/workspace selection, filter change, disclosure, status change, create/update/delete request outcomes, and member/role operations;
- static Cards do not animate on route entry.

### Tasks

- create/update validation, completion signature, checklist actions, taxonomy, attachments, reminders, comments, assignee/due-date selection, inline edit, board movement, filters, optimistic success, rollback, and offline-safe draft state;
- the inline comment edit must render and announce its validation error before any decorative polish.

### Calendar, activity, and notifications

- period navigation, view selection, filter disclosure, loading-more state, row insertion/read state, and local status changes;
- date grids do not replay entrance animation after every period change.

### Settings and data safety

- section navigation, preferences save, security/passkey operations, backup/export/import progress, confirmation, failure, and recovery;
- destructive states retain their semantics and do not borrow brand-orange success motion.

## Reduced Motion Contract

Under `prefers-reduced-motion: reduce`:

- transform travel, scale, stagger, caret blink, pulse, spinner rotation, and signature flourish are removed;
- ordinary state changes are immediate or use a maximum 80 ms opacity-only crossfade when needed to avoid a visual flash;
- content is never hidden by a removed entrance animation;
- progress remains understandable through text, percentage, native state, or a static icon;
- focus movement and live announcements still occur;
- automated and browser tests exercise the same tasks, not only the CSS media query.

## Forced Colors, Contrast, and Touch

Motion expansion is blocked on a surface until its persistent state is accessible:

- normal text reaches WCAG 2.2 AA contrast;
- focus and selection survive forced colors;
- every coarse-pointer action reaches 44×44 CSS pixels or equivalent non-overlapping target spacing;
- motion does not become the only distinction between states;
- long EN/LT/RU labels, 200% zoom, landscape phones, and NativePHP safe areas remain stable.

## Technical Architecture

- Reuse Tailwind CSS 4 theme variables, `motion-safe`, `motion-reduce`, `starting`, and explicit transition-property utilities.
- Reuse the installed `tw-animate-css` integration only through shared Reka primitives; do not add a motion package.
- Prefer CSS transitions and keyframes for local primitives.
- Use Vue `Transition`/`TransitionGroup` only when component lifecycle or collection identity is required.
- Use a focused Web Animations/FLIP helper only after a measured list/reorder case proves CSS alone insufficient.
- Animate `transform` and `opacity` by default. Named color, border, shadow, and outline transitions are allowed when their paint cost is bounded.
- Do not animate `width`, `height`, margin, padding, top/right/bottom/left, blur, or shadow spread as a generic pattern.
- Do not leave persistent `will-change` hints.
- Keep all class names statically discoverable by Tailwind.

## Integration With Earlier Plans

The new master execution plan combines only unfinished presentation work from:

- the light motion, icon, browser, APK, rename, and final-device plan;
- the responsive CSS plan;
- the system-wide UI/UX audit remediation gate;
- the still-open portions of the UI constructor plan.

Committed constructor slices remain complete and are not reimplemented: shared `Field`/`Textarea`, task title/description compositions, `DialogBody`/`DialogActions`, and the already migrated dialog consumers represented by commits `8477eee`, `2b848a6`, `4d5ece0`, `cf0a7a4`, and `848998d`.

Localized timezone preferences and SQLite optimization remain independent plans. The master plan treats their touched files as coordination boundaries, not as UI work to absorb or overwrite.

### Concurrent ownership boundaries

| Concern                                                                                                                                      | Authoritative owner                                 | Soft-motion responsibility                                              |
| -------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------- | ----------------------------------------------------------------------- |
| Responsive frame, safe areas, pointer sizing, viewport bounds                                                                                | responsive CSS plan and its verified worktree slice | consume the final contract; do not reimplement it                       |
| Button/input gradients and ghost/outline visual parity                                                                                       | gradient-control plan                               | preserve gradients while narrowing transition properties and timing     |
| Global foreground busy overlay, operation counter, root `inert`/`aria-busy`                                                                  | global-operation-feedback plan                      | use shared progress/spatial tokens after that owner settles             |
| Offline/error/retry/draft truth                                                                                                              | a separate request-outcome packet                   | never infer success or queued sync from the busy overlay                |
| Localized timezone catalog and preference controls                                                                                           | localized-timezone plan                             | animate only the final committed control states                         |
| Shared Button loading, Field/Textarea, InlineState, ColorSwatch, SearchField, ResultSummary, PaginationBar, SurfacePanel, DialogBody/Actions | delivered UI-constructor commits                    | reuse; do not recreate or widen their APIs for animation convenience    |
| IconTile and icon-bearing headings                                                                                                           | motion/icon worktree until committed                | preserve semantic icon/accessibility ownership; motion remains optional |

Implementation order follows file ownership rather than visual convenience. A motion packet cannot edit a file actively owned by another stream; it waits for the owner to commit, refreshes the source/test inventory, and then changes only motion semantics.

## Execution Quality Model

The program is delivered through reviewable packets rather than one repository-wide motion commit:

- one packet owns one shared contract or one domain journey;
- target approximately 100 changed lines and keep a normal packet below 300 reviewable changed lines;
- separate refactoring from new behavior;
- begin with a failing behavioral/source contract, implement the smallest shared owner, migrate one representative consumer, verify, then expand in a later packet;
- keep every intermediate commit functional and independently revertible;
- regenerate exact line locations from current committed `main`; historical line numbers and unchecked boxes are evidence only;
- never push unrelated local commits as an accidental side effect of a documentation or motion slice; the quality-review snapshot contained twelve such ahead commits, so ancestry must always be refreshed.

## Acceptance Contract

The design is fulfilled only when:

- every reachable interaction/state has been classified as animate, immediate, or intentionally static;
- meaningful states receive frequent but low-amplitude feedback;
- generic page/Card/list entrance choreography is absent;
- no permanent decorative loop or first-run pulse remains;
- `transition-all` is absent from first-party product controls;
- layout-property exceptions are explicit, measured, and regression-tested;
- reduced motion, forced colors, keyboard, screen reader, touch, zoom, EN/LT/RU, offline/error/rollback, browser, NativePHP, emulator, and physical-device evidence are current;
- P1 UI/UX findings are closed before P2 polish is claimed complete;
- the implementation uses the lowest coherent shared owner and reduces rather than expands repeated consumer markup;
- documentation, tests, commits, push state, APK/emulator result, rename, and final Samsung verification remain factual.
