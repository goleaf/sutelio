---
name: tailwind-native-responsive
description: Audit, implement, test, and optimize Sutelio responsive presentation with Tailwind CSS 4 and NativePHP Mobile WebViews. Use for changes to resources/css/app.css, responsive Vue classes or page frames, viewport and safe-area handling, dialogs and sheets, touch targets, horizontal overflow, reduced motion, forced colors, web or NativePHP layout verification, and CSS bundle performance.
---

# Tailwind Native Responsive

## Establish The Boundary

1. Read `AGENTS.md`, the canonical docs in their mandated order, `.ai/rules/index.md`, and every matching rule before planning or editing.
2. Inspect `package.json`, `vite.config.ts`, `resources/views/app.blade.php`, and nearby Vue components. Use Laravel Boost `application-info` and `search-docs` for the installed versions.
3. Keep `resources/css/app.css` as the single CSS-first Tailwind 4 entrypoint. Do not add Sass, SCSS, Less, Stylus, another CSS entrypoint, a PostCSS Tailwind bridge, or checked-in compiled CSS. Tailwind 4 does not support Sass preprocessors.
4. Keep complete Tailwind class names statically discoverable in Vue. Do not construct responsive classes dynamically and do not add component `<style>` blocks when shared utilities or tokens fit.

## Inventory Before Changing

Search all first-party presentation files, not only the reported page:

```bash
rg -n "(?:^|[[:space:]'\"])(?:sm|md|lg|xl|2xl):" resources/js --glob '*.vue'
rg -n "100vw|100svw|100vh|92vh|min-w-|w-\[|whitespace-nowrap|overflow-x-auto" resources/js resources/css
rg -n "motion-reduce|forced-colors|pointer-coarse|touch-pan|overscroll" resources/js resources/css
```

Record exact consumers and distinguish intentional horizontal regions from document overflow. Prefer the lowest shared token, primitive, or component that corrects every matching consumer.

## Lock Behavior With TDD

1. Add or update focused Pest source contracts in `tests/Feature/FrontendDesignTest.php` before production edits.
2. Run `php artisan test --compact tests/Feature/FrontendDesignTest.php` and confirm the failure names the missing responsive contract.
3. Implement the smallest shared correction.
4. Re-run the focused test and keep it green before expanding scope.
5. Add frontend behavioral tests when state or interaction changes; do not replace browser verification with string assertions.

## Apply The Responsive Contract

- Start at 320px with unprefixed mobile styles; layer `sm`, `md`, `lg`, `xl`, and `2xl` changes upward.
- Give flex/grid text owners `min-w-0`; use `wrap-anywhere` for user or localized text that may exceed intrinsic width.
- Use logical properties, `clamp()`, `min()`, `max()`, CSS custom properties, and supported native nesting in shared CSS.
- Use `dvh`/`dvw` for dynamic WebView bounds. Do not use `100vw` for overlays because scrollbar width can escape the document.
- Keep boards, segmented controls, and category rails horizontally scrollable only when that interaction is intentional. Add `touch-pan-x` and `overscroll-x-contain` to those regions.
- Restrict lift, rotation, and hover-only feedback to `(hover: hover) and (pointer: fine)`.
- Preserve at least 44 by 44 CSS pixels for coarse-pointer actions without enlarging fine-pointer controls unnecessarily.
- Preserve one fixed Warm Precision light mode, visible focus, reduced-motion parity, forced-colors boundaries, and non-color state cues.

## Handle NativePHP Safe Areas Once

Keep `viewport-fit=cover` and `nativephp-safe-area` on the Blade bootstrap. Normalize both value sources:

- browser: `env(safe-area-inset-top/right/bottom/left, 0px)`
- NativePHP: `--inset-top/right/bottom/left`

NativePHP applies portrait top/bottom or landscape left/right padding through `body.nativephp-safe-area`. On those edges, make the page frame use only the residual safe area so it does not double the package padding. Keep the full safe inset on the other edges and in ordinary browsers.

## Measure And Verify

Run the smallest relevant gates after each slice, then complete:

```bash
php artisan test --compact tests/Feature/FrontendDesignTest.php
npm run test:frontend
npm run types:check
npm run lint:check
npm run format:check
npm run build
npm run build:android
npm run build:ios
```

Compare production CSS and JavaScript raw/gzip sizes plus build time with the recorded baseline. Reject unexplained growth; do not introduce abstractions or dependencies for unmeasured micro-optimizations.

Use both isolated Chrome DevTools MCP and Playwright MCP against the Laravel Boost absolute Herd URL. Check representative guest and authenticated routes at 320x568, 390x844, 430x932, 768x1024, 820x1180, 1024x768, 1440x1000, and 1920x1080. Include landscape phone, 200% zoom/reflow, keyboard focus, reduced motion, forced colors, dark OS preference with light-only output, document overflow, overlay bounds, touch targets, accessibility tree, console errors, and failed network requests.

## Deliver Factual Evidence

Run the repository's remaining Pint, Larastan, full Pest, audit, Composer, and isolated migration/seeding gates required by `AGENTS.md`. Inspect the complete and staged diffs, preserve unrelated changes, update `docs/progress.md` with exact evidence, and commit/push only the attributable phase on `main`. Never claim an unexecuted browser, native build, test, or audit as passing.
