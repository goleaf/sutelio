# Design System And Tailwind 4

`resources/css/app.css` is the canonical CSS-first Tailwind 4 configuration. Vite uses `@tailwindcss/vite`; there is no JavaScript Tailwind configuration, Sass/Less layer, PostCSS Tailwind bridge, broad `@apply` component system, or runtime design-family switch.

## Warm Precision Tokens

The fixed Warm Precision language uses warm neutral canvases, explicit surface/foreground/border/focus semantics, orange editorial accents, non-color status text/icons, restrained depth, and light/dark/system color modes. `@theme` maps the application variables into discoverable Tailwind utilities.

| Token domain | Canonical examples                                                                            | Usage rule                                                                       |
| ------------ | --------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| Color        | background, card, muted, primary, accent, destructive, success, warning, information, sidebar | Use semantic intent; never expose raw state by color alone                       |
| Radius       | `rounded-panel` (1.5rem), `rounded-feature` (1.75rem), standard sm/md/lg                      | Repeated surface radii use tokens; one-off geometry requires a documented reason |
| Shadow       | `shadow-panel`, `shadow-dialog`                                                               | Shared depth uses tokens; avoid decorative stacking                              |
| Container    | `max-w-app`                                                                                   | Main workspaces share the bounded 92.5rem container                              |
| Typography   | Instrument Sans font variables and Tailwind type scale                                        | Preserve readable line length, hierarchy, and translated expansion               |
| Motion       | standard easing/duration plus `motion-reduce:*`                                               | Interaction feedback only; reduced-motion remains fully functional               |

Static complete class names are required. String interpolation such as `bg-${status}` is prohibited and guarded by `ArchitectureContractTest.php`; controlled maps must contain complete class literals. `@source` covers first-party Vue/Blade/PHP paths that automatic discovery cannot infer.

## Component Hierarchy

- Reka/shadcn-style primitives own dialogs, sheets, menus, selects, checkboxes, focus traps, and keyboard semantics.
- Shared workspace components own page headers, metrics, segmented controls, empty states, dialog surfaces, and confirmations.
- Feature components own task, project, calendar, dashboard, activity, and workspace composition.
- Tailwind utilities and tokens style components; a static visual fragment does not become a server component.

## Responsive And State Contract

Base utilities target the smallest viewport. Layout expands progressively for large mobile, tablet, laptop, desktop, and wide desktop. Horizontal page overflow is prohibited. Filters switch to sheets where a sidebar cannot remain usable; dialogs use dynamic viewport bounds; long and localized content can wrap; touch targets coexist with keyboard focus.

Every data surface distinguishes the applicable initial/loading, empty, filtered-empty, recoverable-error, unauthorized, disabled, pending, and completed states. `aria-live`, `aria-busy`, textual labels, icons, and action-scoped spinners communicate state without blocking unrelated regions.

## Tailwind 4 Applicability Matrix

| Feature                                           | Decision and candidate                                | Responsive / accessibility / browser effect                | Evidence                       |
| ------------------------------------------------- | ----------------------------------------------------- | ---------------------------------------------------------- | ------------------------------ |
| CSS-first `@theme`, `@source`, custom variants    | Used in `app.css`                                     | Stable discovery and semantic light/dark utilities         | production build               |
| Data/ARIA/group/peer/has variants                 | Used where primitives expose state                    | State is synchronized with semantics                       | design and component tests     |
| Reduced motion / forced colors                    | Used on critical interaction/motion paths             | Controls remain usable in emulated modes                   | browser smoke and source tests |
| Logical properties                                | Preferred for direction-independent spacing/alignment | Better multilingual/RTL resilience                         | component review               |
| Dynamic viewport units                            | Used for viewport-safe overlays                       | Prevents mobile browser clipping                           | dialog/sheet source contracts  |
| Container queries                                 | Not currently applied                                 | No independently constrained component has a measured need | applicability review           |
| View transitions, masks, zoom, text shadows       | Not applied decoratively                              | No proven orientation/usability benefit                    | applicability review           |
| Sass/Less, broad `@apply`, unsafe dynamic classes | Prohibited/not applicable                             | Keeps source discovery and review deterministic            | architecture test              |

Final `npm run build` transformed 3,477 modules in 4.11 seconds. Main application CSS is 159.22 kB (23.65 kB gzip), the application entry is 87.25 kB (22.73 kB gzip), and the project-detail chunk is 90.58 kB (15.93 kB gzip), remaining reviewable while adding the responsive Project Operations Workspace.
