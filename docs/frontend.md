# Frontend, Tailwind, Design System, And Accessibility

## Application Architecture

Inertia 3 and Vue 3 are the intentional frontend. Pages live in `resources/js/pages`; shared/feature UI lives in `resources/js/components`; reusable behavior lives in composables; Pinia is reserved for genuine cross-page/shared state. Components use `<script setup lang="ts">`, typed props/emits, immutable page props, identity-synchronized drafts, and generated Wayfinder imports.

The persistent authenticated shell owns the document `main` landmark through `SidebarInset`. Mandatory onboarding resolves through its own layout with one `main`, the first-run language dialog, and notifications, but without `AppSidebar`, `AppSidebarHeader`, a workspace switcher, or any application navigation. Page components and the nested settings layout use neutral presentation wrappers, while `WorkspacePageHeader` owns the page `h1`; reusable detail bodies begin below that level. `FrontendDesignTest.php` and onboarding frontend coverage guard these contracts.

Livewire, Volt, and Flux are not installed and are explicitly non-applicable. Reka UI/shadcn-style components remain the focus/dialog/menu/select/checkbox primitive layer. Inertia's request APIs handle application mutations; Axios/custom fetch is not added as a parallel mutation system.

## Sutelio Identity Flow

`AppLogoIcon` consumes the deterministic `/favicon.svg` Signal Orange/custom ivory ribbon mark as a decorative image. `AppLogo` combines that mark with the unchanged Sutelio proper noun; `AppSidebar`, `AppSidebarHeader`, and the authentication layout reuse those components instead of embedding divergent artwork. The authentication logo link owns the accessible `Sutelio` name, while adjacent visible text names the product in shared navigation.

The Inertia Blade shell remains query-free and receives the canonical application name from server props for the document title, application metadata, and Apple mobile title. It links the deterministic favicon and touch icon outputs and declares cobalt as the browser theme color. `npm run brand:build` is the only supported way to regenerate these tracked assets.

## Warm Precision Design System

The fixed product language is a warm neutral canvas, semantic surface layers, orange editorial accent, strong page hierarchy, generous rounded corners, restrained depth, and one intentional light color mode. Runtime design-family or color-mode switching is prohibited.

`resources/css/app.css` is CSS-first Tailwind 4 configuration using `@import "tailwindcss"`, explicit sources, built-in state variants, semantic CSS variables, and theme mappings. The Vite Tailwind plugin is the compiler integration. Complete utility names must be statically discoverable; arbitrary values are one-off only and repeating values become tokens/components.

Current token domains include background/surface/foreground/muted, border/input/ring, primary/accent, chart colors, sidebar surfaces, four-channel success/warning/information/destructive statuses, radii, typography, spacing, shadows, and motion. Every status tone owns a shared surface, border, normal-text, and icon channel with at least `4.5:1` paired contrast; brand, identity avatars, project colors, chart series, and decorative gradients remain separate reviewed domains. Shared page, surface, control, stagger, and step transitions use the same motion tokens and collapse under `prefers-reduced-motion`. Logical start/end properties are preferred where they improve multilingual/RTL resilience.

## Interaction Contract

Authentication pages compose one shared `AuthEmailAssistant`. Login may prefill and list up to five successful Sutelio-only addresses with inline removal; registration starts with an empty address and never shows that history. Android NativePHP adds an explicit system chooser action, while web/iOS and every failure state retain the standard email field and platform autofill. Chooser progress, cancellation, success, timeout, and failure use localized inline status rather than a modal; all choices and destructive icons retain at least 48-pixel touch targets, visible focus, non-color state, and manual keyboard/screen-reader operation.

- Every server-backed action has action-specific processing, duplicate prevention, validation/error recovery, and confirmed success behavior.
- Every deliberate foreground Inertia navigation, form submission, router mutation, and configured standalone HTTP request inherits `globalBusy` from the application bootstrap. `GlobalBusyOverlay` renders one Signal Orange progress line and centered localized status, applies `aria-busy` plus `inert` to the application root, leaves only 30% of the page visible through `bg-background/70`, and has no close, cancel, outside-click, or Escape path. Overlapping requests are token-safe, so the lock ends only after every tracked foreground operation finishes.
- Prefetching, polling, deferred/partial background refreshes that explicitly set `showProgress: false`, infinite-scroll fetches, and Precognition validation remain non-blocking. They keep local contextual feedback; feature components must not add a second page-blocking overlay or bypass the configured HTTP client.
- Data interfaces distinguish initial/loading, empty, filtered-empty, error, unauthorized, disabled, pending, and completed states where relevant.
- Ordinary record create/edit/duplicate/detail flows use dedicated Inertia pages. Destructive record decisions use `PageConfirmPanel`, a labelled in-flow region with heading focus, scroll-into-view, typed confirmation, processing lock, and no focus trap. Reka dialogs remain only for first-run language and passkey/two-factor security ceremonies; sheets remain for mobile navigation and responsive filters; menus/selects and specialized package controls retain their native transient semantics.
- User-editable project, label, status, and priority colors compose the shared `ColorPickerField`. It uses the maintained `@zag-js/color-picker` state machine instead of native `input[type=color]`, keeps opaque six-digit HEX values, owns localized EN/LT/RU visible and assistive copy, exposes package-backed area/hue/HEX/preset controls, and keeps popup content inside an enclosing specialized dialog's accessible tree when applicable.
- Every due-date/date-time field composes `DatePickerField`. Day selection and Today update the canonical value and close the calendar; the phone panel fills the dynamic safe-area-aware viewport, tablet/desktop remain bounded, and locale-derived weekend headings/cells receive Warm Precision treatment without replacing selected/today/disabled semantics.
- Navigation remains functional as normal same-origin links through Inertia and Wayfinder. Custom integrations must initialize/teardown across Inertia navigation; no current long-lived third-party widget requires `@persist`-style behavior.
- Task/workspace identity changes reset local drafts and pending state. Array indexes are not durable keys for mutable lists.
- Notification filters are canonical URL state coordinated by the page, while focused filter/feed/row components consume typed immutable props. Partial visits cancel superseded requests, refresh the user-local day boundary, and request only inbox props; row/browser presentation shares one localized content resolver.
- Data Safety settings keep workspace transfer and application backup on separate authorized routes. Export remains available to workspace members, import controls are rendered only for policy-authorized managers, standalone HTTP validation results never trigger success effects, and destructive restore remains operator/password-confirmation gated.
- Guided onboarding uses one typed Inertia form coordinator, one typed eight-step icon registry, shared icon/field-label primitives, and eight focused step components inside a dedicated navigation-free layout. Step props remain immutable, drafts resynchronize from server identity, generated Wayfinder actions own every request, superseded visits are cancelled, and the sticky save/status/action regions expose saving, saved, resumed, error, pending, and confirmation states. Every field label, selector trigger/option, mode choice, action, validation state, and standalone notice pairs localized text with a meaningful decorative icon. Foreground server transitions also inherit the global non-dismissible operation lock; local status remains the action-specific explanation. Direct visits to application, settings, or authenticated recovery pages return to onboarding until Results completion, preventing stale persistent-layout navigation from becoming an escape path.
- The global language control is owned by both primary shells and consumes one server-provided catalog. Its first-run dialog is intentionally non-dismissible until a valid choice is confirmed, previews all dialog copy in the highlighted language before submission, and uses the same Inertia locale mutation as the dropdown, Settings, and onboarding so persistent layout copy reacts without a duplicate client translation source.

## Audit-First Shared Correction Contract

Requirement `ui-system-001` governs every UI/UX change. Before implementation, inventory the exact page, component, locale, viewport, input mode, and loading/empty/error/disabled/offline state affected. Correct an equivalent pattern at the lowest coherent layer—token, interaction primitive, shared presentation component, shell/state contract, then focused feature composition—without building a universal mega-component or touching unrelated consumers.

The 2026-08-17 system audit found contrast, focus-completion, touch-target, offline recovery, inline validation, motion, status-token, component-vocabulary, and dormant-UI gaps. The responsive program has now remediated shared status tokens and removed the three proven-unreachable presentation components; remaining findings still require their own evidence rather than a blanket completion claim. Detailed historical evidence lives in `docs/ui-ux-audit-2026-08-17.md`.

## Accessibility And Responsive Requirements

Critical pages use semantic headings, labels/descriptions, associated errors, accessible names for icon controls, visible focus, natural DOM order, keyboard alternatives, status announcements, non-color cues, reduced motion, and forced-colors-aware critical controls. Native semantics are preferred to redundant ARIA.

Authenticated routes must expose exactly one shell-owned `main` and one logical page `h1`. A source contract prevents page/layout landmark duplication, and the desktop/mobile browser matrix verifies the composed DOM rather than relying on source inspection alone.

Mobile-first layouts are verified from 390 px through desktop/wide screens. Sidebars, specialized dialogs/popovers, in-flow confirmation regions, lists, filters, calendars, long names, and translated expansion must not create horizontal page overflow. Hover is never the sole affordance; touch targets and keyboard access coexist.

The shared `WorkspacePageFrame` supplies fluid logical gutters and a shrink-safe bounded container from 320 px upward. It consumes normalized browser/NativePHP safe-area variables without doubling the body padding that NativePHP applies in portrait and landscape. Specialized dialogs/sheets and the phone date picker use `dvh` bounds with vertical scrolling, including 200% text reflow; date-picker wrapper positioning is neutralized below the shared 640 px breakpoint so its fixed content actually begins at viewport origin. Toast, dropdown, and switcher overlays remain inside safe inline viewport edges. `app.css` is the sole Tailwind CSS 4 entrypoint, and no Sass/SCSS pipeline or source CSS duplicate exists.

The language switcher remains a 44-pixel keyboard/touch target at the top of guest and authenticated shells. The mandatory first-run dialog traps focus, blocks Escape/outside dismissal until confirmation, exposes title/description/status semantics, uses local SVG flags only as decorative reinforcement, wraps translated labels, and suppresses nonessential animation under reduced motion.

`ColorPickerField` presents a 48/52-pixel trigger, full color area, 44/48-pixel slider thumbs, editable HEX field, and optional preset grid. Its positioned panel flips and slides within 16 pixels of the viewport, scrolls vertically when height is constrained, returns focus after Escape or selection, retains explicit boundaries in forced colors, and suppresses nonessential opening motion when reduced motion is requested.

Notification read-state and kind filters are named pressed-button groups, not tablists. The signal stream uses ordered Today/Earlier headings, explicit non-color read labels, one concise result live region, connected-node focus restoration, and 44-pixel controls at both tested widths.

Settings navigation uses a current-section dropdown below `lg` and the established vertical navigation at desktop. Long localized section names wrap, and Data Safety scope banners distinguish the current workspace from the application database with text and icons rather than color alone.

Guided onboarding uses a wide-screen progress rail and a compact sticky mobile progress header. Below 480 px, Skip, Back, and Continue stack as equal full-width actions so long translations and 200% text scaling cannot clip; from 480 px Skip occupies its own row and Back/Continue share equal columns, while `sm` and wider screens use the compact action row. All journey actions are at least 44 px, safe-area spacing is explicit, localized content wraps without overflow, and one server-backed validation summary receives focus before field-level links.

The onboarding step registry supplies the same semantic icon to the active heading, mobile progress header, and desktop progress rail, with completion retaining the explicit check mark. `OnboardingIcon` normalizes compact sizing/color/forced-colors behavior, while `OnboardingFieldLabel` owns icon-to-label alignment for both labels and legends. Existing language flags, project icons, color picker, date picker, and timezone semantics remain their domain-specific visual sources.

## Tailwind Feature Applicability

| Feature                                     | Candidate / decision                                       | Effect and evidence                                            |
| ------------------------------------------- | ---------------------------------------------------------- | -------------------------------------------------------------- |
| CSS-first `@theme` and `@source`            | Used in main stylesheet                                    | Static discovery and semantic tokens; production build         |
| Data/ARIA/group/peer variants               | Used through shared controls                               | State styling without dynamic class construction; design tests |
| Reduced-motion / forced-colors variants     | Used where critical                                        | Accessibility behavior; source/browser checks                  |
| Logical properties                          | Used where direction-independent layout helps              | Translation/RTL resilience                                     |
| Container queries                           | Use only for independently reusable constrained components | No measured candidate currently requires migration             |
| View transitions, masks, zoom, text shadows | Not applied decoratively                                   | No navigation/orientation benefit proven                       |
| Sass/Less or broad `@apply` abstraction     | Not applicable                                             | Components and tokens are the abstraction layer                |

Localization workflow is in `docs/localization.md`; browser and source verification is in `docs/testing.md` and `docs/compliance-matrix.md`.

Detailed token/component/Tailwind decisions live in `docs/design-system.md`. Accessibility requirements and the final verified workflow matrix live in `docs/accessibility.md`.

## Shared Soft-Motion And Icon Contract

`IconTile` is the only shared owner for structural/status icon tiles. `LeadingIconHeading` composes section headings with that primitive, and `WorkspacePageHeader` applies the cobalt structural role to page headings. Signal Orange remains action/completion emphasis; cobalt remains structure/navigation/information. Persisted project colors are domain data, not design-system replacements.

The CSS-first motion primitives are `ui-reveal`, `ui-lift`, `ui-status-pop`, `ui-icon-response`, and bounded `ui-stagger`. Consumers attach them to meaningful state transitions rather than every render. Infinite/refetched notification and activity streams must not replay initial motion; success animation is edge-triggered; reduced-motion and forced-colors behavior is owned by the shared stylesheet. Header and sidebar controls expose translated accessible names; the authenticated header intentionally contains no global search or command-palette affordance.
