# Frontend, Tailwind, Design System, And Accessibility

## Application Architecture

Inertia 3 and Vue 3 are the intentional frontend. Pages live in `resources/js/pages`; shared/feature UI lives in `resources/js/components`; reusable behavior lives in composables; Pinia is reserved for genuine cross-page/shared state. Components use `<script setup lang="ts">`, typed props/emits, immutable page props, identity-synchronized drafts, and generated Wayfinder imports.

The persistent authenticated shell owns the document `main` landmark through `SidebarInset`. Page components and the nested settings layout use neutral presentation wrappers, while `WorkspacePageHeader` owns the page `h1`; reusable detail bodies begin below that level. `FrontendDesignTest.php` guards both contracts.

Livewire, Volt, and Flux are not installed and are explicitly non-applicable. Reka UI/shadcn-style components remain the focus/dialog/menu/select/checkbox primitive layer. Inertia's request APIs handle application mutations; Axios/custom fetch is not added as a parallel mutation system.

## Warm Precision Design System

The fixed product language is a warm neutral canvas, semantic surface layers, orange editorial accent, strong page hierarchy, generous rounded corners, restrained depth, and one intentional light color mode. Runtime design-family or color-mode switching is prohibited.

`resources/css/app.css` is CSS-first Tailwind 4 configuration using `@import "tailwindcss"`, explicit sources, built-in state variants, semantic CSS variables, and theme mappings. The Vite Tailwind plugin is the compiler integration. Complete utility names must be statically discoverable; arbitrary values are one-off only and repeating values become tokens/components.

Current token domains include background/surface/foreground/muted, border/input/ring, primary/accent, destructive and chart colors, sidebar surfaces, radii, typography, spacing, shadows, and motion. Shared page, surface, control, stagger, and step transitions use the same motion tokens and collapse under `prefers-reduced-motion`. Logical start/end properties are preferred where they improve multilingual/RTL resilience.

## Interaction Contract

- Every server-backed action has action-specific processing, duplicate prevention, validation/error recovery, and confirmed success behavior.
- Data interfaces distinguish initial/loading, empty, filtered-empty, error, unauthorized, disabled, pending, and completed states where relevant.
- Dialogs/sheets/menus use Reka focus trap, Escape, accessible name, return focus, viewport-safe scrolling, and touch targets.
- Navigation remains functional as normal same-origin links through Inertia and Wayfinder. Custom integrations must initialize/teardown across Inertia navigation; no current long-lived third-party widget requires `@persist`-style behavior.
- Task/workspace identity changes reset local drafts and pending state. Array indexes are not durable keys for mutable lists.
- Notification filters are canonical URL state coordinated by the page, while focused filter/feed/row components consume typed immutable props. Partial visits cancel superseded requests, refresh the user-local day boundary, and request only inbox props; row/browser presentation shares one localized content resolver.
- Data Safety settings keep workspace transfer and application backup on separate authorized routes. Export remains available to workspace members, import controls are rendered only for policy-authorized managers, standalone HTTP validation results never trigger success effects, and destructive restore remains operator/password-confirmation gated.
- Guided onboarding uses one typed Inertia form coordinator and eight focused step components. Step props remain immutable, drafts resynchronize from server identity, generated Wayfinder actions own every request, superseded visits are cancelled, and the sticky save/status/action regions expose saving, saved, resumed, error, pending, and confirmation states without blocking unrelated controls.
- The global language control is owned by both primary shells and consumes one server-provided catalog. Its first-run dialog is intentionally non-dismissible until a valid choice is confirmed, previews all dialog copy in the highlighted language before submission, and uses the same Inertia locale mutation as the dropdown, Settings, and onboarding so persistent layout copy reacts without a duplicate client translation source.

## Accessibility And Responsive Requirements

Critical pages use semantic headings, labels/descriptions, associated errors, accessible names for icon controls, visible focus, natural DOM order, keyboard alternatives, status announcements, non-color cues, reduced motion, and forced-colors-aware critical controls. Native semantics are preferred to redundant ARIA.

Authenticated routes must expose exactly one shell-owned `main` and one logical page `h1`. A source contract prevents page/layout landmark duplication, and the desktop/mobile browser matrix verifies the composed DOM rather than relying on source inspection alone.

Mobile-first layouts are verified from 390 px through desktop/wide screens. Sidebars, dialogs, lists, filters, calendars, long names, and translated expansion must not create horizontal page overflow. Hover is never the sole affordance; touch targets and keyboard access coexist.

The language switcher remains a 44-pixel keyboard/touch target at the top of guest and authenticated shells. The mandatory first-run dialog traps focus, blocks Escape/outside dismissal until confirmation, exposes title/description/status semantics, uses local SVG flags only as decorative reinforcement, wraps translated labels, and suppresses nonessential animation under reduced motion.

Notification read-state and kind filters are named pressed-button groups, not tablists. The signal stream uses ordered Today/Earlier headings, explicit non-color read labels, one concise result live region, connected-node focus restoration, and 44-pixel controls at both tested widths.

Settings navigation uses a current-section dropdown below `lg` and the established vertical navigation at desktop. Long localized section names wrap, and Data Safety scope banners distinguish the current workspace from the application database with text and icons rather than color alone.

Guided onboarding uses a wide-screen progress rail and a compact sticky mobile progress header. At 390 px, Skip occupies its own row and Back/Continue share equal columns; all journey actions are at least 44 px, safe-area spacing is explicit, localized content wraps without overflow, and one server-backed validation summary receives focus before field-level links.

## Tailwind Feature Applicability

| Feature                                        | Candidate / decision                                       | Effect and evidence                                            |
| ---------------------------------------------- | ---------------------------------------------------------- | -------------------------------------------------------------- |
| CSS-first `@theme` and `@source`               | Used in main stylesheet                                    | Static discovery and semantic tokens; production build         |
| Data/ARIA/group/peer variants                  | Used through shared controls                               | State styling without dynamic class construction; design tests |
| Reduced-motion / forced-colors variants        | Used where critical                                        | Accessibility behavior; source/browser checks                  |
| Logical properties                             | Used where direction-independent layout helps              | Translation/RTL resilience                                     |
| Container queries                              | Use only for independently reusable constrained components | No measured candidate currently requires migration             |
| View transitions, masks, zoom, text shadows    | Not applied decoratively                                   | No navigation/orientation benefit proven                       |
| Sass/Less or broad `@apply` abstraction        | Not applicable                                             | Components and tokens are the abstraction layer                |

Localization workflow is in `docs/localization.md`; browser and source verification is in `docs/testing.md` and `docs/compliance-matrix.md`.

Detailed token/component/Tailwind decisions live in `docs/design-system.md`. Accessibility requirements and the final verified workflow matrix live in `docs/accessibility.md`.
