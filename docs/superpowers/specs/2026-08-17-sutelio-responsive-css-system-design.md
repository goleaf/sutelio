# Sutelio Responsive CSS System Design

## Problem

Sutelio must render the same Inertia/Vue interface in desktop browsers, narrow mobile browsers, and the NativePHP Mobile WebView. The current presentation uses Tailwind CSS 4.3 through its Vite plugin and one CSS-first entrypoint, while responsive behavior is distributed across shared components and 244 Vue files. A partial responsive pass already introduced fluid page gutters, dynamic viewport bounds, touch affordances, and a shared page frame, but it has not been fully verified or delivered.

The requested Sass migration conflicts with Tailwind CSS 4's supported architecture. Tailwind explicitly documents that version 4 is not designed for Sass, Less, or Stylus and already supplies build-time imports, nesting, variables, prefixing, and minification. Vite can compile SCSS only by adding a separate preprocessor dependency, which would create an unsupported second pipeline without improving the shipped CSS.

## Decision

Keep `resources/css/app.css` as the single CSS-first Tailwind entrypoint. Do not add Sass, SCSS, Less, Stylus, PostCSS duplication, a second CSS entrypoint, or compiled CSS to source control. Use modern standards processed by Tailwind and Lightning CSS: custom properties, native nesting where it reduces repetition, logical properties, `clamp()`, `min()`, `max()`, dynamic viewport units, and media-feature queries.

Official sources:

- Tailwind compatibility and build-time imports: https://tailwindcss.com/docs/compatibility
- Tailwind responsive and pointer variants: https://tailwindcss.com/docs/responsive-design and https://tailwindcss.com/docs/hover-focus-and-other-states
- Vite CSS processing and minification: https://main.vite.dev/guide/features
- NativePHP Mobile v4 WebView safe areas: https://nativephp.com/docs/mobile/4/edge-components/web-view

## Architecture

`app.css` remains the only styling build boundary and owns shared design tokens, motion primitives, safe-area normalization, fine/coarse pointer behavior, forced-colors behavior, and the shared page-frame contract. Vue components keep complete, statically discoverable Tailwind classes for component-local responsive layout. Repeated page-shell structure moves to `WorkspacePageFrame.vue`; component-specific state and business behavior stay in their existing owners.

Browser safe-area values (`env(safe-area-inset-*)`) and NativePHP values (`--inset-*`) are normalized into one set of application variables. Because NativePHP's `nativephp-safe-area` class already applies portrait or landscape body padding, the page frame uses only the residual safe area on those edges and keeps the normal fluid gutter. This prevents the partial implementation's possible double inset while retaining browser-notch support.

Overlays use `dvh`/`dvw` constraints, bounded widths, scroll containment, and mobile-first spacing. Horizontal boards and segmented controls retain intentional horizontal scrolling with `touch-pan-x` and `overscroll-x-contain`; the document itself must not overflow. Hover-only transforms run only on a fine hover pointer, while coarse pointers retain at least 44-pixel control bounds.

## Testing And Evidence

Pest source contracts verify the single CSS-first entrypoint, absence of unsupported preprocessors, NativePHP viewport/safe-area integration, residual inset behavior, shared page-frame use, header wrapping, overlay bounds, touch containment, coarse-pointer targets, forced colors, and reduced motion. Existing frontend tests continue to cover component contracts.

Verification then runs the focused Pest file, full sequential Pest suite, frontend tests, Vue type checking, ESLint, Prettier, production/web and Android/iOS-mode Vite builds, npm and Composer audits, Larastan, and isolated migration/seeding. Chrome DevTools and Playwright use disposable profiles to check guest and authenticated representative routes across phone, tablet, desktop, landscape, 200% zoom, reduced motion, forced colors, and console/network state. Bundle sizes and build time are compared with the recorded 150.72 kB CSS / 22.43 kB gzip baseline.

## Skill And Documentation

Create `.agents/skills/tailwind-native-responsive/SKILL.md` with concise project-specific procedures and UI metadata. The skill must trigger for CSS/Tailwind responsive, viewport, safe-area, touch, overlay, and NativePHP WebView work. Record the supported CSS-first boundary as a durable `resources/**` rule through Laravel Boost. Synchronize `docs/design-system.md`, `docs/frontend.md`, the implementation plan, and the append-only `docs/progress.md` with factual checks only.

## Out Of Scope

No route, query, schema, authorization, API, localization-copy, database, package-runtime, visual-theme, or product-workflow change belongs to this phase. Do not install Sass, remove Tailwind, change the fixed Warm Precision light design, touch personal browser profiles, or install an APK on a physical phone.
