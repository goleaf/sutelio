# Sutelio Signal Orange Design

## Status

Approved and implemented on 2026-08-17. The product owner requested that the application's orange match the Sutelio logo and authorized completion using the recommended design direction.

## Objective

Replace the unrelated Tailwind default orange family with a Sutelio-owned family anchored exactly at the logo's signal orange, `#FF6038`. Preserve the fixed Warm Precision language, current light-only presentation, semantic status colors, layout, typography, accessibility, and data behavior.

## Audited Baseline

- Logo SVG/raster assets, Android themes, the brand generator, and the Inertia progress indicator already use `#FF6038`.
- `resources/css/app.css` declares `--brand-orange: #ff6038`, but primary, ring, sidebar, and 487 `orange-*` references across 88 first-party presentation files previously resolved to unrelated HSL or Tailwind values.
- Shadows and Sonner focus treatments encoded Tailwind's old orange RGB channels directly.
- New projects defaulted to Tailwind orange `#f97316`.
- Exact signal orange has only `3.01:1` contrast against white and `2.84:1` against warm ivory. It reaches `4.71:1` against Sutelio deep cobalt `#0A285F`.

## Selected Palette

The canonical CSS-first Tailwind theme owns this perceptual ramp:

| Step | Value     | Intended role                         |
| ---- | --------- | ------------------------------------- |
| 50   | `#FFF5F1` | very light wash                       |
| 100  | `#FFE7DF` | selected/hover wash                   |
| 200  | `#FFCDBD` | soft border                           |
| 300  | `#FFAA90` | strong border or inverse-support tone |
| 400  | `#FF7F5C` | hover accent                          |
| 500  | `#FF6038` | exact logo signal orange              |
| 600  | `#CD431F` | white-text AA action surface          |
| 700  | `#A63416` | light-surface accent text             |
| 800  | `#832C16` | strong accent text                    |
| 900  | `#652617` | deep warm support                     |
| 950  | `#36130A` | deepest warm support                  |

`orange-500` maps directly to `--brand-orange`, keeping one exact source. Existing static `orange-*` utilities remain valid and change atomically through Tailwind's `@theme inline` contract.

## Semantic Contract

- Exact signal-orange semantic surfaces use deep-cobalt foreground.
- Foreground-bearing controls, including the default Button, checked Checkbox, and default Badge, use the accessible orange-600/white pair.
- `ring` and `sidebar-ring` use orange-600 for stronger boundary contrast.
- Editorial rails, progress markers, active navigation, selection, and restrained washes keep their established shade roles through the new palette.
- Raw Tailwind-orange shadow/focus channels become exact signal-orange channels.
- Newly created projects offer `#ff6038` as their default orange; existing persisted project, label, priority, and status colors are not rewritten.

## Accessibility

- Never pair exact `#FF6038` with white normal-size text.
- Deep cobalt on signal orange must remain at least `4.5:1`; the measured value is `4.71:1`.
- White text is allowed on orange-600 or darker; `#CD431F` against white is `4.75:1`.
- Focus, selected, hover, and active states retain borders, rings, rails, underlines, labels, icons, or geometry so color is not the only cue.
- The fixed light `color-scheme`, keyboard navigation, zoom, touch targets, forced-colors support, and reduced-motion clamp remain intact.

## Semantic Boundaries

The phase does not recolor amber warning/pending, red destructive/error, green success, blue information, categorical charts, or persisted user/domain colors. This prevents the brand accent from erasing application meaning.

## Verification Contract

- Failing-first Pest coverage asserts the full ramp, exact anchor, semantic mappings, absence of legacy orange literals, and computed WCAG ratios.
- Existing project/onboarding/frontend design contracts must stay green.
- A production Vite build and isolated real Chrome render must prove the runtime tokens and computed Button colors.
- Full backend/frontend/static-analysis/audit/migration/seed/cache checks, diff review, semantic commit, and normal push are required before completion.

## Non-Goals

- No new theme family or dark-mode restoration.
- No route, query, schema, authorization, localization, API, or dependency change.
- No bulk migration of existing domain colors.
- No email-verification restoration.
