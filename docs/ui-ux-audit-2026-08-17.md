# System-Wide UI/UX Audit — 2026-08-17

This report is a diagnostic snapshot of the current Sutelio presentation layer. It does not authorize or claim the later remediation plan or application fixes. The audit follows `ui-system-001`: inventory first, then correct the lowest coherent shared layer with the fewest practical consumer changes.

## Executive Verdict

Sutelio is structurally strong but visually and behaviorally inconsistent. The application has a recognizable brand, sound responsive foundations, localized copy, shared Reka primitives, and extensive focus/reduced-motion utilities. It does not yet meet its own claim of a fully verified system-wide UI/UX contract.

The most important problems are not isolated page polish. They are shared-contract gaps: insufficient contrast in several semantic combinations, incomplete focus recovery, inconsistent touch ergonomics outside the main button primitive, missing system-wide offline recovery, hidden inline validation feedback, excessive entrance motion, fragmented status colors, and repeated decorative surface patterns that reduce information density.

### Quality score

| Axis                         |     Score | Evidence                                                                                                                                                                                               |
| ---------------------------- | --------: | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Accessibility                |       2/4 | Strong semantics and focus utilities, but verified contrast, focus-return, touch-target, and validation-recovery gaps remain.                                                                          |
| Performance                  |       3/4 | Lean local imagery and a healthy guest load; widespread layout-property and entrance animations add avoidable visual/rendering work.                                                                   |
| Responsive behavior          |       3/4 | The guest EN/LT/RU viewport matrix has no page overflow; shared responsive work is in progress, but dense phone controls remain.                                                                       |
| Theming and visual semantics |       2/4 | Brand pairs are defined correctly, while semantic status tokens are largely bypassed by raw color utilities.                                                                                           |
| Anti-pattern resistance      |       0/4 | Five or more repeated design-generator tells are present: oversized radii, border-plus-shadow cards, tiny uppercase eyebrows, accent stripes/rings, card-heavy composition, and uniform reveal motion. |
| **Total**                    | **10/20** | **Acceptable foundation; substantial system-level remediation is required.**                                                                                                                           |

No P0 blocker was found. Five P1 defects and six P2 system-quality findings remain.

## Scope And Method

- Quantitative inventory was frozen at local `main` commit `8477eee` plus the concurrently visible responsive worktree. Later shared-form and unrelated feature commits are intentionally not folded into these counts; the execution plan must re-baseline against its own committed starting point.
- Audited 247 first-party Vue files: 24 pages, 215 components, and 8 layouts, plus `resources/css/app.css`, shared stores/composables, translations, frontend tests, and canonical UI documentation.
- Traced shared primitives, page headers/frames, dialogs/sheets, navigation, forms, status presentation, motion, responsive utilities, loading/error states, and dormant UI paths.
- Reviewed the live guest and mandatory first-run flow in isolated Chromium for EN/LT/RU at 390×844, 820×1180, and 1440×1000, plus phone/desktop login after language confirmation.
- Checked horizontal overflow, landmarks/headings, console/network failures, focus behavior, reduced motion, target geometry, and representative screenshots.
- Calculated representative contrast pairs from the current CSS variables rather than judging by appearance alone.
- Preserved concurrent responsive/UI-constructor work. Findings below describe the current working tree and do not claim ownership of those changes.

## P1 — Must Fix Before Claiming System-Wide UI/UX Compliance

### UI-01 — Several normal-text color pairs miss WCAG AA contrast

**Locations:** `resources/js/components/workspace/WorkspaceSwitcher.vue:83`, `resources/js/components/AppSidebar.vue:149`, `resources/js/components/ui/sidebar/SidebarGroupLabel.vue:19`, `resources/js/pages/projects/Index.vue:221`, `resources/js/components/calendar/CalendarWeekView.vue:54`, `CalendarMonthGrid.vue:103`, `CalendarAgendaView.vue:52`, `resources/js/components/activity/ActivityTimeline.vue:280`, `resources/js/components/notification/NotificationRow.vue:69`, `:122`, `:133`, and `resources/js/components/localization/FirstRunLanguageDialog.vue:48`, `:90`.

Sidebar foreground at 50%, 55%, and 70% opacity is approximately 2.57:1, 2.90:1, and 4.17:1 against the sidebar surface. Muted foreground on the full muted surface is approximately 4.35:1. Muted foreground on the first-run orange-tinted surface is approximately 4.42:1. All miss the 4.5:1 threshold for normal text.

**Impact:** workspace identity, navigation context, dates, activity/notification metadata, and first-run explanations can be difficult to read for low-vision users.

**Shared correction seam:** strengthen semantic sidebar/muted foreground roles in `app.css`, then reserve opacity-reduced text for decorative/nonessential content. Add computed-color regression coverage for each supported semantic surface.

**Acceptance:** every normal-text token/surface pair is at least 4.5:1 in default and forced-color-compatible presentation; large text is at least 3:1; information is not encoded by color alone.

### UI-02 — Focus is lost after the mandatory first-run dialog closes

**Location:** `resources/js/components/localization/FirstRunLanguageDialog.vue` and its shell ownership.

The dialog initially focuses the selected language correctly. After confirmation closes the mandatory overlay, live Chromium reports `document.activeElement` as `body`, not the invoking language control, first actionable field, or a documented page-heading target.

**Impact:** keyboard and screen-reader users lose their location at the first product interaction.

**Shared correction seam:** define one explicit first-run completion focus target at shell level and test return/forward focus through the shared dialog contract.

**Acceptance:** confirmation moves focus predictably to the language trigger or first login control, with no focus loss to `body`, in all three locales and guest shells.

### UI-03 — Touch ergonomics are not consistently owned by shared primitives

**Locations:** `resources/js/components/ui/sidebar/index.ts`, `SidebarMenuSubButton.vue`, `SidebarMenuAction.vue`, `SidebarGroupAction.vue`, `resources/js/components/task/TaskChecklistPanel.vue`, `TaskTaxonomyPanel.vue`, and `resources/js/components/project/ProjectTaskQueue.vue`.

The in-progress `.ui-control` coarse-pointer rule correctly raises shared Button targets to 44 px, and current dropdown/select/toast changes extend that improvement. Several direct/native or sidebar-specific controls still expose 20–32 px geometry, and checklist rows combine a compact input with several adjacent icon actions. Pseudo-elements enlarge some sidebar hit areas but do not establish one auditable 44 px contract everywhere.

**Impact:** increased missed taps, accidental adjacent actions, and mobile row crowding.

**Shared correction seam:** one interactive-size token/mixin contract applied by Button, sidebar actions, pressed-button primitives, inline row actions, and form controls; dense checklist composition should switch layout at narrow container widths.

**Acceptance:** every actionable target is at least 44×44 CSS px on coarse pointers or has equivalent non-overlapping target spacing; 390 px layouts do not overflow or compress labels below readability.

### UI-04 — Offline and network-failure recovery is not a system contract

**Locations:** application shell/store/request layer and all mutation-owning forms. The source currently contains 72 `post`/`put`/`patch`/`delete` call sites but only three explicit `onNetworkError` handlers.

There is no global offline indicator, central network state, pending-sync explanation, or consistent retry/cancel behavior. Many actions fall back to a generic toast, which does not tell a local-first/NativePHP user whether work is queued, lost, safe to retry, or already committed.

**Impact:** ambiguous state and possible duplicate retries during connectivity loss, directly conflicting with the local-first product promise.

**Shared correction seam:** shell-owned network state plus one request-outcome contract for offline, timeout, retryable, validation, authorization, conflict, and confirmed-success states. Domain forms should consume the shared contract without duplicating transport decisions.

**Acceptance:** representative create/edit/delete flows expose translated offline and retry behavior, prevent duplicate submission, retain safe drafts, and work consistently on web and NativePHP.

### UI-05 — Inline comment editing suppresses a validation error it does not render

**Location:** `resources/js/components/task/TaskCommentsPanel.vue:258`.

The create textarea exposes `aria-invalid` and `InputError`; the inline edit textarea does not. `saveComment` suppresses the generic error toast when the request contains validation errors, leaving the failed edit without visible or announced feedback.

**Impact:** the user cannot understand or recover from an invalid edit, and assistive technology receives no field-error relationship.

**Shared correction seam:** finish migration to the shared Field/Textarea error contract so create and edit paths bind the same error identifier, `aria-invalid`, description, and live message.

**Acceptance:** a failing edit keeps the draft, focuses or links to the field as appropriate, visibly renders the translated error, announces it once, and does not emit a contradictory success state.

## P2 — System Quality And Consistency

### UI-06 — The visual language is over-decorated and under-differentiated

Current source includes 79 `rounded-2xl` uses, 27 `rounded-panel` uses, 32 root Card compositions, 18 `shadow-panel` uses, 36 uppercase treatments, 43 truncations, and recurring orange stripes/rings. Borders and wide shadows are frequently stacked on the same surface. The login desktop view leaves a large decorative canvas around a comparatively small task surface.

This creates a recognizable but generator-like “soft SaaS” style: too many nested containers compete at the same visual level, tiny tracked eyebrow labels recur, and decorative geometry consumes space without improving task completion.

**Shared correction seam:** reduce the radius/depth scale in `app.css` and Card/dialog/page-header primitives; define when a surface is structural, interactive, or merely grouped; remove decorative accent stripes/rings unless they encode a real state or brand moment.

**Acceptance:** each page has one dominant hierarchy, fewer nested framed surfaces, denser useful desktop composition, and no loss of mobile readability or brand recognition.

### UI-07 — Global entrance motion is too broad and too slow

`ui-page-surface`, `ui-enter`, `ui-surface`, and `ui-stagger` apply the emphasized 480 ms entrance animation to page children, Cards, and list items. This exceeds the documented 150–250 ms product-interaction range and animates content simply because it exists, not because the user caused a meaningful state transition.

Reduced-motion collapse is implemented correctly, but the default experience still delays perceived stability and makes repeated navigation feel theatrical.

**Shared correction seam:** remove automatic animation ownership from generic page/Card primitives; retain short state-specific transitions for disclosure, selection, confirmation, orientation, and direct manipulation.

**Acceptance:** no generic page/card/list reveal; purposeful transitions use the fast/standard tokens; reduced-motion provides equivalent immediate state communication.

### UI-08 — Status colors bypass the semantic token system

The current Vue source contains 166 raw red/amber/blue/sky/emerald/green/slate/neutral text/background/border/ring utilities across 38 files. `success`, `warning`, and `information` tokens exist but have zero component uses, while `destructive` has 79 uses.

Representative drift appears in activity, notifications, dashboard charts/queues, onboarding, calendar, projects/tasks, workspace administration, EmptyState, and settings data-safety views.

**Shared correction seam:** one typed semantic tone map consumed by Badge, Alert, status chips, metrics, timelines, and charts; brand orange remains separate from warning and domain color identities.

**Acceptance:** status meaning maps to semantic tokens and a text/icon label, contrast is verified per surface, and feature components no longer carry duplicate color maps.

### UI-09 — Form and action vocabulary is fragmented

A shared Textarea primitive is now present, but six native textarea consumers remain. Twenty-two direct native button elements exist outside the Button primitive; several are valid Reka/pressed-control exceptions, while others duplicate focus, size, disabled, and motion rules. Member-role tone maps and management presentation are duplicated between `resources/js/pages/settings/Members.vue` and `resources/js/components/workspace/WorkspaceMembersPanel.vue`.

**Shared correction seam:** finish the Field/Textarea composition, classify direct-button exceptions, extract a pressed-button/inline-action primitive where semantics match, and centralize workspace member role/status presentation.

**Acceptance:** equivalent controls share semantics and states; exceptions are explicit; no schema-driven mega-component or universal page builder is introduced.

### UI-10 — Broad and layout-property transitions remain

Seven `transition-all` uses remain. Dashboard bars animate `height`, project/onboarding/profile progress animates `width`, and sidebar layout transitions width/height/padding. These can trigger layout work and make the affected property contract unclear.

**Shared correction seam:** transition only named paint/composite properties; use transforms where the visual can preserve correct accessibility/geometry; keep genuine sidebar geometry transitions tightly scoped.

**Acceptance:** `transition-all` is absent from first-party product controls, state transitions name their properties, and no animation causes layout instability at 200% zoom or reduced motion.

### UI-11 — Dormant UI paths create unverified design debt

`resources/js/components/shared/CommandPalette.vue` has no mounted consumer. Its custom Teleport dialog lacks the proven shared Reka dialog/focus-return contract. `AppHeader.vue`, `TaskStats.vue`, and `NavFooter.vue` also appear unreferenced by the active shell.

**Shared correction seam:** remove unreachable presentation after usage proof, or deliberately integrate it through existing shared primitives and full accessibility/state tests. Do not wire dormant UI merely to justify its existence.

**Acceptance:** every shipped interactive component has a reachable owner and regression coverage; unreachable legacy UI is deleted only with explicit approval and proof.

## Cross-System Pattern Map

| Layer                   | Current weakness                                        | Lowest shared correction point                                                 |
| ----------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------ |
| Tokens                  | Contrast, radii, shadows, motion, semantic status drift | `resources/css/app.css`                                                        |
| Interaction primitives  | Touch sizes, direct buttons, field errors               | Button, Field/Textarea, sidebar actions, menu/select items                     |
| Presentation primitives | Repeated card/header/empty/status treatments            | Card, `WorkspacePageHeader`, `LeadingIconHeading`, EmptyState, shared tone map |
| Overlays                | First-run focus completion and viewport ownership       | shared Dialog/Sheet plus shell focus target                                    |
| Application state       | Offline/network ambiguity                               | shell/store/composable request-outcome contract                                |
| Dense domain UI         | Checklist and member-management duplication             | task row composition and shared workspace-member presentation                  |
| Reachability            | Dormant command/header/stats/footer components          | import graph plus removal/integration decision                                 |

This map is intentionally not the implementation plan. The later unlimited-depth plan must enumerate every occurrence, dependency, migration wave, regression test, browser state, acceptance criterion, rollback boundary, and exact file owner before code changes begin.

## What Is Already Strong

- Exact Signal Orange/deep-cobalt and orange-600/white foreground pairs pass at approximately 4.71:1 and 4.75:1.
- Guest EN/LT/RU checks at phone, tablet, and desktop widths return one `main`, one `h1`, no horizontal page overflow, and no console or failed-network errors.
- Shared Reka dialogs/sheets/menus provide a strong semantic and keyboard foundation.
- The source has 226 `focus-visible` and 121 `motion-reduce` occurrences; the global reduced-motion clamp is a useful safety net.
- Current concurrent responsive work centralizes safe-area/page gutters, dynamic viewport bounds, coarse-pointer Button targets, fine-pointer-only hover motion, and wrapping page frames.
- User-facing copy is organized through EN/LT/RU semantic catalogs, and the audit found no design need to introduce another frontend framework or styling pipeline.

## Verification Limits

- Fresh live browser evidence covers guest/first-run/login surfaces only. The local database has no generic demo account, so the audit did not expose personal account identifiers, impersonate an existing user, mutate sessions, create/seed users in the real database, or reuse a personal browser profile.
- Authenticated pages were audited through complete static source inventory, current automated contracts, and existing repository evidence; they were not freshly traversed with an authenticated browser in this phase.
- Screen-reader cadence, physical touch ergonomics, NativePHP devices, and OS high-contrast rendering still require release-device checks after remediation.
- Concurrent responsive and constructor changes are uncommitted at this snapshot. The later plan must re-baseline findings against committed `main` before assigning implementation work.

## Next Decision

After the product owner accepts or adjusts this report, create a separate unlimited-depth remediation plan. It must prioritize P1 defects, preserve the fixed Sutelio identity, select one universal correction point per pattern, list every consumer and test, and minimize implementation file count without hiding necessary exceptions.
