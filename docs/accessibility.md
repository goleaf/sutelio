# Accessibility

Accessibility is a release requirement, not a decorative review. The current interface targets keyboard, screen reader semantics, touch, zoom/responsive reflow, reduced motion, and forced-colors/high-contrast operation.

## Implementation Contract

- The persistent authenticated application shell owns the sole `main` landmark. Authenticated pages and nested settings layouts use neutral wrappers so landmarks never nest.
- Each page has one logical `h1`; headings remain nested by section.
- Native elements provide semantics before ARIA is added. Inputs have labels, descriptions/errors, autocomplete, and `aria-invalid` where needed.
- Icon-only controls have translated accessible names. Status is communicated by text/icon as well as color.
- The clean-S image used by `AppLogoIcon` is decorative (`alt=""` and `aria-hidden="true"`); product links and surrounding navigation expose the visible or explicit `Sutelio` accessible name. Standalone meaningful logo use must receive an equivalent accessible name from its context rather than relying on SVG paths or color.
- Dialogs and sheets use Reka focus trapping, Escape dismissal, return focus, accessible title/description, viewport-safe scroll, and at least 44px primary touch targets. The mandatory first-run language dialog is the documented exception: Escape, outside click, and a close control are disabled until a valid language is explicitly confirmed.
- Destructive actions remain policy-authorized and use localized confirmation; confirmation never replaces server authorization.
- Focus rings are visible in the fixed light mode. Keyboard order follows the DOM, and mutable lists use stable entity keys.
- Exact signal-orange surfaces use deep-cobalt text (`4.71:1`); white normal text is reserved for orange-600 or darker because white on `#FF6038` reaches only `3.01:1`.
- Motion is restrained and critical behavior remains available under `prefers-reduced-motion`; forced-colors mode retains control identity.
- Forced-colors rendering may simplify or replace brand fills, so the mark is never the sole product label. Logo links retain visible focus, and the fixed light interface keeps semantic text, native control boundaries, and accessible names when author colors are overridden.
- Loading and filtering expose action-specific `aria-busy`/status feedback; empty and filtered-empty states remain descriptive.

## Verified Workflows

On 2026-08-16, Chromium automation verified guest login, password confirmation, dashboard/tasks/projects/calendar/activity/notifications/workspaces/profile/preferences/security pages, repeated keyboard-activated Inertia navigation, activity category filtering, and the mobile activity filter sheet.

The route matrix ran at 1440x1000 and 390x844. Every checked page returned 200 (security intentionally rendered password confirmation until confirmed), contained one `h1`, had zero horizontal overflow, and produced no captured console/page error. Reduced-motion and forced-colors emulation were active in the final dashboard check. The activity filter exposed `aria-pressed=true` on the selected category at desktop and inside the mobile dialog.

The Notification Command Center follow-up verified one `main` and one `h1`, semantic pressed-button groups rather than invalid tab semantics, keyboard Space activation, selected-state exposure, a complete filtered-empty state, 44-pixel controls, reduced-motion transition suppression, focus fallback, and zero overflow at 1,440 and 390 pixels. The browser captured no current console, page, or failed-request error during filter and clear interactions.

The Data Safety Center verification covered the current-section settings menu, wrapped English/Lithuanian/Russian labels, one logical heading, 44-pixel controls, workspace/application scope text, Select/Review/Confirm import state, validation-error context retention, cancel/success focus restoration, DOM/visual action order, fixed-light and reduced-motion mobile presentation, forced colors, and zero horizontal overflow. Read-only members receive a text explanation instead of unauthorized upload controls.

The authenticated landmark-integrity pass repeated dashboard, task index/detail, project index/detail, calendar, activity, notifications, workspace index/detail, and profile settings at 1440x1000 and 390x844. All 22 route/viewport checks exposed exactly one `main` and one `h1`, had zero horizontal overflow, and captured no console, page, failed-request, or HTTP error. The reusable task-detail body now starts its internal hierarchy at `h2`, leaving page and sheet shells responsible for their own top-level titles.

The guided-onboarding pass verified a complete new-user journey, logout/login resume, invited-member adaptation, required skip, replay/exit, and Dashboard continuation. Desktop and 390x844 mobile rendering expose one `main` and one `h1`, connected heading focus after each step, a keyboard-reachable 44-pixel action sequence, focused validation summary and field links, no horizontal overflow, and usable fixed-light, reduced-motion, and forced-colors presentation. The mobile footer keeps Skip separate and Back/Continue in a stable equal-width row.

The global-language pass verifies the mandatory first-run dialog and persistent shell dropdown at desktop, 390x844 phone, and tablet widths. The dialog retains focus, cannot be dismissed with Escape or outside interaction, translates its title/description/language names/action immediately as the highlighted language changes, exposes a live saving state, fits without horizontal overflow, and clamps decorative motion under `prefers-reduced-motion`. Flags are local decorative assets; native names and localized text carry the meaning.

Automated evidence is provided by `FrontendDesignTest.php`, `FrontendLocalizationTest.php`, `OnboardingFrontendTest.php`, `ActivityIntelligenceFrontendTest.php`, `NotificationCommandCenterFrontendTest.php`, `DataSafetyCenterFrontendTest.php`, direct TypeScript behavior tests, Vue type checking, ESLint, the production build, and the browser checks recorded in `docs/progress.md`.

## Manual Release Review

A production release should still repeat keyboard traversal and screen-reader spot checks with the target OS/browser combinations, because automation cannot judge announcement quality, reading cadence, contrast perception, or touch ergonomics completely. This is an ongoing release activity, not an unresolved repository defect.
