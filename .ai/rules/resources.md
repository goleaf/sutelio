---
paths:
    - 'resources/**'
---

# Resources

## Audit UI globally and fix the lowest shared layer

Before UI or UX implementation, inventory every affected page, component, locale, viewport, and interaction state with exact locations and acceptance criteria. Prefer the lowest coherent shared token, primitive, or contract so all matching occurrences are corrected with the fewest consumer edits; do not patch pages individually when a shared abstraction fits. Reverify EN/LT/RU, phone/tablet/desktop, zoom/reflow, keyboard and screen-reader semantics, touch targets, reduced motion, forced colors, and loading/empty/error/disabled/offline states.

## Keep Tailwind 4 CSS first

Keep resources/css/app.css as the single Tailwind CSS 4 Vite entrypoint. Do not add Sass, SCSS, Less, Stylus, another CSS entrypoint, or checked-in compiled CSS; Tailwind 4 is the preprocessor. For NativePHP WebViews, normalize env(safe-area-inset-_) with --inset-_ and avoid doubling nativephp-safe-area body padding.

## Keep responsive presentation CSS-first

Keep resources/css/app.css as the only Tailwind CSS 4 stylesheet entrypoint; do not add Sass, SCSS, another preprocessor, or checked-in compiled CSS. Put mobile-first viewport, safe-area, pointer, overflow, motion, and forced-colors behavior in the lowest shared token or Vue primitive, then verify EN/LT/RU across phone, tablet, desktop, zoom/reflow, touch, and NativePHP build modes.
