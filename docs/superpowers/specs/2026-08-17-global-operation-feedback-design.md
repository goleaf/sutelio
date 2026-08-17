# Global Operation Feedback Design

Date: 2026-08-17
Requirements: `ui-system-001`, `ui-accessibility-001`, `ui-responsive-001`, `ui-motion-001`, `i18n-001`, `test-feature-001`, `test-static-001`

## Problem And Inventory

Sutelio currently has action-specific feedback in many places, but no single blocking contract for user-initiated server work. The source inventory contains 259 Vue files, 22 files using Inertia forms, 21 files using standalone `useHttp`, 19 files invoking the Inertia router directly, 76 files with local loading/processing state, and 47 files with a spinner or rotating loader. There are no first-party custom `fetch`, Axios, or `XMLHttpRequest` mutation paths outside the configured Inertia HTTP adapter. `resources/js/app.ts` configures only Inertia's delayed top progress line and has no global busy-state listener.

This creates an inconsistent experience: a button may show a small spinner, but the rest of the screen can still look interactive while a page, locale, save, delete, upload, or other CRUD operation is in flight. The requested behavior is a universal, non-dismissible signal with a top progress line, a centered spinner, a 70% opaque backdrop that leaves 30% of the application visible, and blocked duplicate interaction.

## Considered Approaches

### 1. Patch every page and CRUD component

This could provide action-specific copy, but it would repeat overlay and lifecycle behavior across dozens of consumers, leave future actions easy to miss, and create inconsistent accessibility cleanup. It is rejected.

### 2. Subscribe only to Inertia router events

This covers navigation, `<Form>`, `useForm`, router mutations, and the installed Inertia 3.6.1 `useHttp` implementation: browser verification confirms those requests carry `X-Inertia` and publish router `start`/`finish`. It would not cover a future non-Inertia request sent through the configured client boundary, so it is strong current coverage but not the requested future-universal contract.

### 3. One busy controller, router binding, and standalone HTTP adapter wrapper

This is the selected design. Router `start`, `progress`, and `finish` events own all current Inertia visits, including `useHttp`. A small `HttpClient` wrapper owns only any non-`X-Inertia` request that may use the configured adapter now or in the future, avoiding double counting. Both publish to one token-safe controller consumed by one root overlay. Existing local button and field feedback remains as the action-specific secondary signal.

## Tracking Boundary

The global blocking layer tracks:

- ordinary user-initiated Inertia navigation with `showProgress` enabled;
- Inertia `<Form>` and `useForm` submissions;
- router-backed create, update, archive, duplicate, delete, restore, locale, and other mutations;
- `useHttp` reads and writes initiated by product controls through their observed Inertia router lifecycle;
- any configured-client request without `X-Inertia` or Precognition headers;
- upload percentage when Inertia or the non-Inertia client boundary exposes it.

The layer intentionally does not block for:

- hover prefetch;
- polling, deferred props, infinite-scroll fetching, or other requests explicitly configured with `showProgress: false`;
- Precognition field validation;
- initial server rendering before Vue is interactive.

Those exclusions prevent background work from freezing unrelated controls while preserving the universal contract for deliberate user actions.

## Architecture And Data Flow

```text
Inertia router start/progress/finish ─┐
                                     ├─> global busy controller ─> GlobalBusyOverlay
non-Inertia configured client wrapper ┘            │
                                                  ├─ active operation kind
                                                  ├─ optional upload percentage
                                                  └─ overlap-safe completion handle
```

The controller stores active operations, not a single boolean. Every `begin()` call returns an idempotent handle so overlapping work cannot hide the overlay early and duplicate cleanup cannot underflow state. The most recently started operation supplies the visible kind and percentage:

- Inertia `GET`: opening;
- standalone `GET`: loading;
- write method: processing;
- request with upload progress: uploading.

`bindGlobalBusyToRouter()` tracks visits by Inertia's stable visit ID. `createGlobalBusyHttpClient()` ignores requests carrying the `X-Inertia` or `Precognition` header, wraps every other configured HTTP request, forwards upload callbacks unchanged, and closes its handle in `finally` on success, validation failure, cancellation, HTTP exception, or network failure.

## Presentation And Interaction

`GlobalBusyOverlay.vue` is mounted once beside the Inertia root and teleported to `body`, so it covers authentication, onboarding, settings, and authenticated workspace layouts equally.

- A fixed top line uses Signal Orange and becomes determinate when percentage exists.
- The viewport backdrop uses `bg-background/70`; the underlying application therefore remains visible at 30%.
- A centered Warm Precision status surface contains the shared spinner, localized operation title, and calm automatic-completion hint.
- The overlay has no close, cancel, Escape, or outside-dismiss action.
- The Inertia root receives `inert` and `aria-busy="true"`; body scrolling is locked and the overlay intercepts pointer input.
- The status uses `role="status"`, `aria-live="polite"`, and `aria-atomic="true"` and is never focused. Because applying `inert` can move browser focus to `body`, the component records the initiating control and restores it only when it remains connected and no more specific flow has already claimed focus. Navigation and unmounted-dialog origins remain owned by their destination focus contract.
- Reduced motion keeps the text and static progress signal while disabling rotation and indeterminate travel. Forced colors retains a visible status boundary and Highlight progress line.
- Complete static Tailwind class names remain in the Vue source; the single CSS-first entrypoint owns only the reusable progress animation and body lock.

## Localization

The shared `lang/{en,lt,ru}/ui.php` catalog receives semantic `opening`, `processing`, `uploading`, and `processing_hint` state keys. Existing `common.states.loading` is reused. No client-only translation catalog is introduced.

## Failure And Cancellation Behavior

The overlay describes work, not success. Existing validation, toast, inline error, and page error contracts remain responsible for the outcome. Any terminal path releases its operation handle. Interrupted or cancelled requests cannot leave the page inert. The interface does not expose a cancel affordance while the request is active; browser-level reload or operating-system termination remains outside the application guarantee.

## Verification Contract

1. A Node test proves overlap, idempotent completion, progress clamping/reset, visit classification, header classification, upload forwarding, and success/error cleanup.
2. A focused Pest source contract proves the root component, router binding, HTTP wrapper, no default duplicate progress indicator, 70% backdrop, inert/ARIA semantics, non-dismissible structure, reduced-motion/forced-colors CSS, and EN/LT/RU key parity.
3. Vue type checking, ESLint, Prettier, production/Android/iOS builds, and relevant PHP quality gates pass.
4. Isolated Chrome DevTools and Playwright verify a real local navigation and locale or form mutation at phone/tablet/desktop sizes, including overlay geometry, 70% backdrop style, blocked interaction, live status semantics, reduced motion, forced colors, no horizontal overflow, clean console, and successful requests.

## Scope And Rollback

There is no schema, route, policy, authorization, query, API response, package, lock-file, or persistent-data change. Rollback removes the root overlay, router binding, HTTP wrapper, four translation keys, shared CSS utility, and documentation contract; existing local spinners and Inertia behavior remain intact.
