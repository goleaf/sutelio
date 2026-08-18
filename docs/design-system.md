# Design System And Tailwind 4

`resources/css/app.css` is the canonical CSS-first Tailwind 4 configuration. Vite uses `@tailwindcss/vite`; there is no JavaScript Tailwind configuration, Sass/Less layer, PostCSS Tailwind bridge, broad `@apply` component system, or runtime design-family switch.

## Warm Precision Tokens

The fixed Warm Precision language uses warm neutral canvases, explicit surface/foreground/border/focus semantics, orange editorial accents, non-color status text/icons, restrained depth, and one light color mode. `@theme` maps the application variables into discoverable Tailwind utilities.

| Token domain | Canonical examples                                                                                                                   | Usage rule                                                                         |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------- |
| Color        | background, card, muted, primary, accent, sidebar, and `status-{success,warning,information,destructive}-{surface,border,text,icon}` | Use semantic intent; never expose raw state by color alone                         |
| Radius       | `rounded-panel` (1.5rem), `rounded-feature` (1.75rem), standard sm/md/lg                                                             | Repeated surface radii use tokens; one-off geometry requires a documented reason   |
| Shadow       | `shadow-panel`, `shadow-dialog`                                                                                                      | Shared depth uses tokens; avoid decorative stacking                                |
| Container    | `max-w-app`                                                                                                                          | Main workspaces share the bounded 92.5rem container                                |
| Typography   | Instrument Sans font variables and Tailwind type scale                                                                               | Preserve readable line length, hierarchy, and translated expansion                 |
| Motion       | fast/standard/emphasized durations, shared easing, `ui-*` primitives, `motion-reduce:*`                                              | Orientation and interaction feedback only; reduced-motion remains fully functional |

## Sutelio Brand Geometry

The master brand palette has exactly four identity tokens: cobalt `#123C8B`, deep cobalt `#0A285F`, Signal Orange `#FF6038`, and ivory `#FFF8E9`. The clean-S mark places a solid Signal Orange circle at 70% of the 512-unit artboard diameter on cobalt and uses one solid, stripe-free ivory S. Gradients, strokes, line decoration, and text elements are prohibited in the master mark.

The Sutelio wordmark is one color, deep cobalt, and is exported as deterministic paths rather than live font text. Its final `o` is not orange. The darker accessible orange `#CD431F` is a solid-control surface derivative for white foreground text, not a fifth master-logo token. The runtime product remains light-only; no brand token establishes a dark/system mode or a switchable design family.

### Sutelio Signal Orange

The complete Tailwind `orange-50` through `orange-950` scale is application-owned and anchored at the logo's exact signal orange `#FF6038`. `orange-500` maps directly to `--brand-orange`; lighter and darker steps form a perceptual brand ramp rather than inheriting Tailwind's default orange hue.

Exact signal-orange semantic surfaces use deep-cobalt `#0A285F` foreground, a pair measuring `4.71:1`. Foreground-bearing primary controls such as the default Button, checked Checkbox, and default Badge use `orange-600` (`#CD431F`) with white at `4.75:1`; white on exact signal orange reaches only `3.01:1` and is prohibited for normal text. Warning, destructive, success, information, chart, and persisted user/domain colors remain separate semantic systems.

Boxed controls use restrained, statically discoverable Tailwind 4 diagonal ramps rather than flat or invisible surfaces. Default, destructive, and secondary buttons stay inside their semantic hue; `ghost` intentionally shares the light orange outlined surface, hover feedback, and focus ring of `outline`, while `link` remains unboxed. Shared Input, Textarea, Select, Checkbox, and OTP primitives use the same very light orange surface ramp. These product-control gradients never apply to the master mark or wordmark.

Static complete class names are required. String interpolation such as `bg-${status}` is prohibited and guarded by `ArchitectureContractTest.php`; controlled maps must contain complete class literals. `@source` covers first-party Vue/Blade/PHP paths that automatic discovery cannot infer.

Success, warning, information, and destructive presentation each expose one shared surface, border, text, and icon channel. Normal text and meaningful status icons meet at least `4.5:1` against their paired surface; status remains reinforced by localized copy and icons. Brand orange, user-selected project colors, identity avatar palettes, chart series, and decorative gradients are separate reviewed domains and are not aliases for application state.

## Component Hierarchy

- Reka/shadcn-style primitives own dialogs, sheets, menus, selects, checkboxes, focus traps, and keyboard semantics.
- `ColorPickerField` is the sole user-editable color primitive. It composes `@zag-js/color-picker` and `@zag-js/vue` for non-native color-area, hue, HEX, preset, positioning, touch, and keyboard behavior while Sutelio owns fixed-light styling and EN/LT/RU visible/assistive labels. Consumers pass opaque six-digit HEX values and optional presets rather than rendering `input[type=color]` or local swatches.
- Shared workspace components own page headers, metrics, segmented controls, empty states, dialog surfaces, and confirmations.
- Feature components own task, project, calendar, dashboard, activity, and workspace composition.
- Onboarding components own the Warm Guided Route shell plus one semantic component per step; they reuse shared buttons, controls, previews, confirmation dialogs, and application layout ownership rather than creating a parallel design system.
- Settings Data Safety uses the shared current-section menu and scope banner, then composes workspace transfer and operator backup as separate permission-aware pages.
- Tailwind utilities and tokens style components; a static visual fragment does not become a server component.

The hierarchy is also the required correction order under `ui-system-001`. Fix shared CSS tokens before primitives, primitives before shared product components, and shared product components before feature consumers when semantics match. Page-specific patches are reserved for genuinely page-specific behavior. Minimal file count is an optimization after semantic correctness, not permission to force unrelated controls into one abstraction.

## Current System Audit Baseline

The 2026-08-17 audit recorded low-contrast muted/sidebar pairs; overly broad 480 ms entrance animation; raw status-color utilities bypassing `success`, `warning`, and `information`; repeated large radii, border-plus-shadow surfaces, uppercase eyebrow labels, decorative accents; incomplete Field/Textarea and action vocabulary; and dense touch layouts. The 2026-08-18 responsive program resolves the selected-language, shared muted/navigation, destructive-navigation, and task-badge text contrast defects; the shared coarse-pointer target baseline; guest/auth and workflow reading size; short-landscape first-run composition; the premature desktop-sidebar boundary; fixed-palette application statuses; and mobile/tablet density across every primary workflow. Repeated decoration, remaining feature-local microcopy, direct-control escape hatches, and non-critical visual simplification remain later review findings.

Future remediation must enumerate every matching consumer before changing tokens or primitives, verify the visual effect in EN/LT/RU and supported viewport/input states, and preserve the exact Sutelio identity roles. See `docs/ui-ux-audit-2026-08-17.md`; do not silently weaken the audit by changing this document alone.

## Responsive And State Contract

Base utilities target the smallest viewport. Layout expands progressively for large mobile, tablet, laptop, desktop, and wide desktop. Horizontal page overflow is prohibited. Filters switch to sheets where a sidebar cannot remain usable; dialogs use dynamic viewport bounds; long and localized content can wrap; touch targets coexist with keyboard focus.

The comfort baseline uses 16 px reading text and 15 px secondary/helper text in primary workflows. Coarse-pointer controls expose at least 48×48 px targets, while primary large actions expose at least 52 px height. Drawer navigation remains active through 1023 px and the persistent sidebar begins at 1024 px. The mandatory first-run dialog uses a two-column composition only on short landscape screens wide enough to preserve readable columns; smaller viewports retain bounded internal scrolling.

`WorkspacePageFrame` is the shared page-shell owner. Its CSS-first `ui-page-frame` and `ui-page-container` utilities combine fluid logical gutters, the bounded application container, and shrink-safe content across the primary workspace, onboarding, and settings surfaces. Intentional horizontal regions such as boards, category rails, and segmented controls own their own `touch-pan-x` plus overscroll containment; they never transfer overflow to the document.

Browser `env(safe-area-inset-*)` values and NativePHP Mobile `--inset-*` values normalize into one four-edge contract. NativePHP already applies portrait top/bottom or landscape left/right body padding through `nativephp-safe-area`, so the page frame subtracts that package-owned inset and applies only its normal gutter or any residual browser inset. Overlay heights use dynamic viewport units, and the mobile toast viewport is bounded by the same physical/native inline safe area.

Tailwind CSS 4 is the preprocessor and `resources/css/app.css` remains the only source stylesheet. Sass, SCSS, Less, Stylus, a parallel PostCSS bridge, and checked-in compiled CSS are prohibited. Shared CSS uses custom properties, logical properties, `clamp()`, `min()`, `max()`, dynamic viewport units, and input/media queries; component-local responsive behavior remains statically discoverable in Vue classes.

Every data surface distinguishes the applicable initial/loading, empty, filtered-empty, recoverable-error, unauthorized, disabled, pending, and completed states. Local `aria-live`, textual labels, icons, and action-scoped spinners retain contextual feedback. Deliberate foreground server operations additionally use the shared `GlobalBusyOverlay`: a Signal Orange top progress line, centered spinner/status surface, 70% semantic-background veil, and an `inert`/`aria-busy` application root until every overlapping operation finishes. Background prefetch, polling, deferred/partial refresh, infinite scroll, and validation remain local and non-blocking.

## Tailwind 4 Applicability Matrix

| Feature                                           | Decision and candidate                                | Responsive / accessibility / browser effect                | Evidence                       |
| ------------------------------------------------- | ----------------------------------------------------- | ---------------------------------------------------------- | ------------------------------ |
| CSS-first `@theme` and `@source`                  | Used in `app.css`                                     | Stable discovery and semantic light utilities              | production build               |
| Data/ARIA/group/peer/has variants                 | Used where primitives expose state                    | State is synchronized with semantics                       | design and component tests     |
| Reduced motion / forced colors                    | Used on critical interaction/motion paths             | Controls remain usable in emulated modes                   | browser smoke and source tests |
| Logical properties                                | Preferred for direction-independent spacing/alignment | Better multilingual/RTL resilience                         | component review               |
| Dynamic viewport units                            | Used for viewport-safe overlays                       | Prevents mobile browser clipping                           | dialog/sheet source contracts  |
| Container queries                                 | Not currently applied                                 | No independently constrained component has a measured need | applicability review           |
| View transitions, masks, zoom, text shadows       | Not applied decoratively                              | No proven orientation/usability benefit                    | applicability review           |
| Sass/Less, broad `@apply`, unsafe dynamic classes | Prohibited/not applicable                             | Keeps source discovery and review deterministic            | architecture test              |

The final integrated responsive build transforms 3,597 modules. Main application CSS is 184.85 kB (26.85 kB gzip), and the application entry is 137.73 kB (33.83 kB gzip). The CSS remains below the 50 kB gzip budget; the delta from the 150.72 kB / 22.43 kB responsive preflight includes the concurrent motion, component-constructor, timezone, onboarding, workspace-management, and semantic-status work as well as the responsive program, so it is not attributed to responsive CSS alone.

The current onboarding-icon build transforms 3,714 modules. Main application CSS is 191.68 kB (27.54 kB gzip), and the application entry is 131.70 kB (31.62 kB gzip); the CSS remains below the 50 kB gzip budget.

## Soft Motion And Icon Ownership

Signal Orange identifies primary action, completion, and the Sutelio mark. Cobalt identifies structure, navigation, information, and neutral product context. Status colors remain semantic and never replace text or icon meaning. User-configurable task-definition colors may decorate a badge border but never own its text foreground, because arbitrary entity colors cannot guarantee readable contrast. The product remains fixed light-only even when the operating system requests dark appearance.

`IconTile` owns the shared compact icon surface and its semantic tone/size map. `LeadingIconHeading` composes a tile with section copy, while `WorkspacePageHeader` owns the primary page title and its cobalt structural tile. Feature components select a semantic icon and tone; they do not reproduce tile geometry or raw brand colors.

Mandatory onboarding additionally owns a typed step-icon registry and the compact `OnboardingIcon`/`OnboardingFieldLabel` pair. Every step identity, field label, selector trigger and option, action, validation state, and standalone notice carries a meaningful Lucide or established domain icon beside complete localized text. Icons reinforce rather than replace names, selected/check state, validation copy, or color-independent meaning; decorative instances remain `aria-hidden`.

Motion is high-coverage and low-amplitude. `ui-reveal` is reserved for newly presented content, `ui-lift` for fine-pointer interactive surfaces, `ui-status-pop` for one-shot success/status feedback, `ui-icon-response` for local control response, and `ui-stagger` only for bounded initial collections. Static route content and infinite/refetched feeds do not replay entrance animation. No first-party motion loops indefinitely. Reduced-motion collapses these effects without removing state, and forced-colors keeps borders, focus, and non-color meaning available.
