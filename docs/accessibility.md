# Accessibility

Accessibility is a release requirement, not a decorative review. The current interface targets keyboard, screen reader semantics, touch, zoom/responsive reflow, reduced motion, and forced-colors/high-contrast operation.

## Implementation Contract

- The persistent authenticated application shell owns the sole `main` landmark. Authenticated pages and nested settings layouts use neutral wrappers so landmarks never nest.
- Each page has one logical `h1`; headings remain nested by section.
- Native elements provide semantics before ARIA is added. Inputs have labels, descriptions/errors, autocomplete, and `aria-invalid` where needed.
- Icon-only controls have translated accessible names. Status is communicated by text/icon as well as color.
- The clean-S image used by `AppLogoIcon` is decorative (`alt=""` and `aria-hidden="true"`); product links and surrounding navigation expose the visible or explicit `Sutelio` accessible name. Standalone meaningful logo use must receive an equivalent accessible name from its context rather than relying on SVG paths or color.
- Dialogs and sheets use Reka focus trapping, Escape dismissal, return focus, accessible title/description, and viewport-safe scroll. Coarse-pointer controls expose at least 48×48 px shared targets and primary large actions expose at least 52 px height. The mandatory first-run language dialog is the documented exception to dismissal: Escape, outside click, and a close control are disabled until a valid language is explicitly confirmed.
- Destructive actions remain policy-authorized and use localized confirmation; confirmation never replaces server authorization.
- Focus rings are visible in the fixed light mode. Keyboard order follows the DOM, and mutable lists use stable entity keys.
- Exact signal-orange surfaces use deep-cobalt text (`4.71:1`); white normal text is reserved for orange-600 or darker because white on `#FF6038` reaches only `3.01:1`.
- Motion is restrained and critical behavior remains available under `prefers-reduced-motion`; forced-colors mode retains control identity.
- Forced-colors rendering may simplify or replace brand fills, so the mark is never the sole product label. Logo links retain visible focus, and the fixed light interface keeps semantic text, native control boundaries, and accessible names when author colors are overridden.
- Loading and filtering expose action-specific status feedback; empty and filtered-empty states remain descriptive. Deliberate foreground server operations additionally expose one root-level `role="status"`, localized text, `aria-busy`, and an `inert` application tree through `GlobalBusyOverlay`. The overlay never focuses its spinner, blocks pointer and keyboard activation, cannot be dismissed with Escape or a close action, ends only when every tracked operation settles, and restores a still-connected initiating control only when no more specific flow has already claimed focus. Navigation or an unmounted initiator leaves post-request focus ownership to the destination/dialog flow; the existing first-run focus-completion audit gap remains open. Reduced motion suppresses indefinite travel, forced colors preserves a visible status boundary, and background refresh/validation remain non-blocking.

## Verified Workflows

On 2026-08-16, Chromium automation verified guest login, password confirmation, dashboard/tasks/projects/calendar/activity/notifications/workspaces/profile/preferences/security pages, repeated keyboard-activated Inertia navigation, activity category filtering, and the mobile activity filter sheet.

The route matrix ran at 1440x1000 and 390x844. Every checked page returned 200 (security intentionally rendered password confirmation until confirmed), contained one `h1`, had zero horizontal overflow, and produced no captured console/page error. Reduced-motion and forced-colors emulation were active in the final dashboard check. The activity filter exposed `aria-pressed=true` on the selected category at desktop and inside the mobile dialog.

The Notification Command Center follow-up verified one `main` and one `h1`, semantic pressed-button groups rather than invalid tab semantics, keyboard Space activation, selected-state exposure, a complete filtered-empty state, 44-pixel controls, reduced-motion transition suppression, focus fallback, and zero overflow at 1,440 and 390 pixels. The browser captured no current console, page, or failed-request error during filter and clear interactions.

The Data Safety Center verification covered the current-section settings menu, wrapped English/Lithuanian/Russian labels, one logical heading, 44-pixel controls, workspace/application scope text, Select/Review/Confirm import state, validation-error context retention, cancel/success focus restoration, DOM/visual action order, dark and reduced-motion mobile presentation, forced colors, and zero horizontal overflow. Read-only members receive a text explanation instead of unauthorized upload controls.

The authenticated landmark-integrity pass repeated dashboard, task index/detail, project index/detail, calendar, activity, notifications, workspace index/detail, and profile settings at 1440x1000 and 390x844. All 22 route/viewport checks exposed exactly one `main` and one `h1`, had zero horizontal overflow, and captured no console, page, failed-request, or HTTP error. The reusable task-detail body now starts its internal hierarchy at `h2`, leaving page and sheet shells responsible for their own top-level titles.

The guided-onboarding pass verifies a complete mandatory new-user journey, logout/login resume, invited-member adaptation, replay-specific exit, and Dashboard continuation. Desktop and 390x844 mobile rendering expose one `main` and one `h1`, connected heading focus after each step, keyboard-reachable 44-pixel actions, focused validation summary and field links, no horizontal overflow, and usable fixed-light, reduced-motion, and forced-colors presentation. Required runs expose only Back and Continue/Finish; the separate replay-exit action appears only during an already completed user's optional replay.

The semantic-status pass verified shared success, warning, information, and destructive surface/border/text/icon channels. Browser-computed WCAG contrast is `10.41:1` to `12.76:1` for normal text and `6.35:1` to `6.59:1` for icons against their paired surfaces. A live Lighthouse finding also moved shared muted navigation from `4.34:1` to a darker neutral and destructive navigation from `3.76:1` to the semantic destructive-text channel; the final authenticated snapshot scores 100 in accessibility, best practices, SEO, and agentic browsing with zero failed audits.

The Data Safety Center and guided-onboarding statements above preserve browser evidence exactly as observed before the light-only delivery. Sutelio is now light-only; Task 10 must freshly reverify the current fixed-light, reduced-motion, and forced-colors behavior instead of treating the historical dark-mode observations as current evidence.

The global-language pass verifies the mandatory first-run dialog and persistent shell dropdown at desktop, 390x844 phone, and tablet widths. The dialog retains focus, cannot be dismissed with Escape or outside interaction, translates its title/description/language names/action immediately as the highlighted language changes, exposes a live saving state, fits without horizontal overflow, and clamps decorative motion under `prefers-reduced-motion`. Flags are local decorative assets; native names and localized text carry the meaning.

Automated evidence is provided by `FrontendDesignTest.php`, `FrontendLocalizationTest.php`, `OnboardingFrontendTest.php`, `ActivityIntelligenceFrontendTest.php`, `NotificationCommandCenterFrontendTest.php`, `DataSafetyCenterFrontendTest.php`, direct TypeScript behavior tests, Vue type checking, ESLint, the production build, and the browser checks recorded in `docs/progress.md`.

## Manual Release Review

A production release should still repeat keyboard traversal and screen-reader spot checks with the target OS/browser combinations, because automation cannot judge announcement quality, reading cadence, contrast perception, or touch ergonomics completely. This is an ongoing release activity, not an unresolved repository defect.

## Open 2026-08-17 Audit Gaps

Requirement `ui-system-001` and `docs/ui-ux-audit-2026-08-17.md` currently supersede any blanket interpretation of “implemented and verified” for the whole presentation layer. The 2026-08-18 responsive program fixes the selected-language 4.39:1 contrast failure, shared muted/status navigation contrast, shared control sizing, guest/auth microcopy, tablet navigation boundary, and fixed-palette application statuses. Confirmed open gaps still include focus falling to `body` after first-run confirmation, direct-control escape hatches that do not yet inherit the coarse-pointer baseline, and an inline comment-edit validation error that is neither rendered nor announced.

Fresh isolated Chromium evidence covers the guest first-run/login flow in EN/LT/RU at 390×844, 820×1180, and 1440×1000 with no horizontal overflow, one `main`, one `h1`, and no console/network failure. Authenticated traversal was intentionally not performed with personal local accounts; authenticated pages therefore require a fresh non-personal test fixture during remediation. Physical screen-reader, touch-device, NativePHP, and OS forced-colors checks remain release gates rather than claimed results.

The 2026-08-18 responsive-foundation pass adds guest checks at 320×568, 430×932, 768×1024, 1024×768, and 844×390; the short-landscape dialog exposes every language and its primary action without initial scrolling. A disposable product-created account completed mandatory onboarding and verified drawer open/dismiss/navigation/focus at 390, 768, 820, and 1023 px plus persistent/collapsible sidebar behavior at 1024 and 1440 px. The account and its project/task data were deleted through product UI and read-only database checks found zero residue. Lighthouse accessibility increased from 96 to 100 for the checked guest state.

## Soft Motion And Icon Remediation Status

Shared icon surfaces now preserve a visible shape, text label, or accessible name independently of color. Icon-only shell controls have translated labels; decorative glyphs are hidden from assistive technology. The mounted command palette uses the shared Reka dialog primitive so focus trapping, Escape dismissal, labelling, and focus return remain primitive-owned.

Motion communicates appearance, interaction, or one-shot completion only. Initial list staggering is bounded, live/refetched feeds do not replay it, and reduced-motion removes nonessential transform and duration while leaving state changes visible. Forced-colors retains explicit tile/control boundaries and native focus. Source regressions cover shared ownership, icon-only names, allowed non-tile controls, bounded stagger, fixed-light brand roles, and the intentional entity-color exceptions. The final authenticated EN/LT/RU browser matrix, 200% reflow, reduced motion, forced colors, and dark-OS/fixed-light observations remain Task 18 release evidence until executed against the integrated tree.
