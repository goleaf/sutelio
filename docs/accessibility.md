# Accessibility

Accessibility is a release requirement, not a decorative review. The current interface targets keyboard, screen reader semantics, touch, zoom/responsive reflow, reduced motion, and forced-colors/high-contrast operation.

## Implementation Contract

- Each page has one logical `h1`; headings remain nested by section.
- Native elements provide semantics before ARIA is added. Inputs have labels, descriptions/errors, autocomplete, and `aria-invalid` where needed.
- Icon-only controls have translated accessible names. Status is communicated by text/icon as well as color.
- Dialogs and sheets use Reka focus trapping, Escape dismissal, return focus, accessible title/description, viewport-safe scroll, and at least 44px primary touch targets.
- Destructive actions remain policy-authorized and use localized confirmation; confirmation never replaces server authorization.
- Focus rings are visible in light/dark modes. Keyboard order follows the DOM, and mutable lists use stable entity keys.
- Motion is restrained and critical behavior remains available under `prefers-reduced-motion`; forced-colors mode retains control identity.
- Loading and filtering expose action-specific `aria-busy`/status feedback; empty and filtered-empty states remain descriptive.

## Verified Workflows

On 2026-08-16, Chromium automation verified guest login, password confirmation, dashboard/tasks/projects/calendar/activity/notifications/workspaces/profile/preferences/security pages, repeated keyboard-activated Inertia navigation, activity category filtering, and the mobile activity filter sheet.

The route matrix ran at 1440x1000 and 390x844. Every checked page returned 200 (security intentionally rendered password confirmation until confirmed), contained one `h1`, had zero horizontal overflow, and produced no captured console/page error. Reduced-motion, dark media, and forced-colors emulation were active in the final dashboard check. The activity filter exposed `aria-pressed=true` on the selected category at desktop and inside the mobile dialog.

The Notification Command Center follow-up verified one `main` and one `h1`, semantic pressed-button groups rather than invalid tab semantics, keyboard Space activation, selected-state exposure, a complete filtered-empty state, 44-pixel controls, reduced-motion transition suppression, focus fallback, and zero overflow at 1,440 and 390 pixels. The browser captured no current console, page, or failed-request error during filter and clear interactions.

Automated evidence is provided by `FrontendDesignTest.php`, `FrontendLocalizationTest.php`, `ActivityIntelligenceFrontendTest.php`, `NotificationCommandCenterFrontendTest.php`, `NotificationInbox.test.ts`, Vue type checking, ESLint, the production build, and the browser checks recorded in `docs/progress.md`.

## Manual Release Review

A production release should still repeat keyboard traversal and screen-reader spot checks with the target OS/browser combinations, because automation cannot judge announcement quality, reading cadence, contrast perception, or touch ergonomics completely. This is an ongoing release activity, not an unresolved repository defect.
