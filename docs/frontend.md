# Frontend, Tailwind, Design System, And Accessibility

## Application Architecture

Inertia 3 and Vue 3 are the intentional frontend. Pages live in `resources/js/pages`; shared/feature UI lives in `resources/js/components`; reusable behavior lives in composables; Pinia is reserved for genuine cross-page/shared state. Components use `<script setup lang="ts">`, typed props/emits, immutable page props, identity-synchronized drafts, and generated Wayfinder imports.

Livewire, Volt, and Flux are not installed and are explicitly non-applicable. Reka UI/shadcn-style components remain the focus/dialog/menu/select/checkbox primitive layer. Inertia's request APIs handle application mutations; Axios/custom fetch is not added as a parallel mutation system.

## Warm Precision Design System

The fixed product language is a warm neutral canvas, semantic surface layers, orange editorial accent, strong page hierarchy, generous rounded corners, restrained depth, and clear light/dark/system color modes. Runtime design-family switching is prohibited.

`resources/css/app.css` is CSS-first Tailwind 4 configuration using `@import "tailwindcss"`, explicit sources, custom variants, semantic CSS variables, and theme mappings. The Vite Tailwind plugin is the compiler integration. Complete utility names must be statically discoverable; arbitrary values are one-off only and repeating values become tokens/components.

Current token domains include background/surface/foreground/muted, border/input/ring, primary/accent, destructive and chart colors, sidebar surfaces, radii, typography, spacing, shadows, and motion. Logical start/end properties are preferred where they improve multilingual/RTL resilience.

## Interaction Contract

- Every server-backed action has action-specific processing, duplicate prevention, validation/error recovery, and confirmed success behavior.
- Data interfaces distinguish initial/loading, empty, filtered-empty, error, unauthorized, disabled, pending, and completed states where relevant.
- Dialogs/sheets/menus use Reka focus trap, Escape, accessible name, return focus, viewport-safe scrolling, and touch targets.
- Navigation remains functional as normal same-origin links through Inertia and Wayfinder. Custom integrations must initialize/teardown across Inertia navigation; no current long-lived third-party widget requires `@persist`-style behavior.
- Task/workspace identity changes reset local drafts and pending state. Array indexes are not durable keys for mutable lists.
- Notification filters are canonical URL state coordinated by the page, while focused filter/feed/row components consume typed immutable props. Partial visits cancel superseded requests, refresh the user-local day boundary, and request only inbox props; row/browser presentation shares one localized content resolver.
- Data Safety settings keep workspace transfer and application backup on separate authorized routes. Export remains available to workspace members, import controls are rendered only for policy-authorized managers, standalone HTTP validation results never trigger success effects, and destructive restore remains operator/password-confirmation gated.

## Accessibility And Responsive Requirements

Critical pages use semantic headings, labels/descriptions, associated errors, accessible names for icon controls, visible focus, natural DOM order, keyboard alternatives, status announcements, non-color cues, reduced motion, and forced-colors-aware critical controls. Native semantics are preferred to redundant ARIA.

Mobile-first layouts are verified from 390 px through desktop/wide screens. Sidebars, dialogs, lists, filters, calendars, long names, and translated expansion must not create horizontal page overflow. Hover is never the sole affordance; touch targets and keyboard access coexist.

Notification read-state and kind filters are named pressed-button groups, not tablists. The signal stream uses ordered Today/Earlier headings, explicit non-color read labels, one concise result live region, connected-node focus restoration, and 44-pixel controls at both tested widths.

Settings navigation uses a current-section dropdown below `lg` and the established vertical navigation at desktop. Long localized section names wrap, and Data Safety scope banners distinguish the current workspace from the application database with text and icons rather than color alone.

## Tailwind Feature Applicability

| Feature                                        | Candidate / decision                                       | Effect and evidence                                            |
| ---------------------------------------------- | ---------------------------------------------------------- | -------------------------------------------------------------- |
| CSS-first `@theme`, `@source`, custom variants | Used in main stylesheet                                    | Static discovery and semantic tokens; production build         |
| Data/ARIA/group/peer variants                  | Used through shared controls                               | State styling without dynamic class construction; design tests |
| Reduced-motion / forced-colors variants        | Used where critical                                        | Accessibility behavior; source/browser checks                  |
| Logical properties                             | Used where direction-independent layout helps              | Translation/RTL resilience                                     |
| Container queries                              | Use only for independently reusable constrained components | No measured candidate currently requires migration             |
| View transitions, masks, zoom, text shadows    | Not applied decoratively                                   | No navigation/orientation benefit proven                       |
| Sass/Less or broad `@apply` abstraction        | Not applicable                                             | Components and tokens are the abstraction layer                |

Localization workflow is in `docs/localization.md`; browser and source verification is in `docs/testing.md` and `docs/compliance-matrix.md`.

Detailed token/component/Tailwind decisions live in `docs/design-system.md`. Accessibility requirements and the final verified workflow matrix live in `docs/accessibility.md`.
