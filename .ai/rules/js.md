---
paths:
    - 'resources/js/**'
---

# Js

## Use global foreground operation feedback

All deliberate Inertia navigation, form, router, `useHttp`, and configured-client actions inherit `GlobalBusyOverlay` from the app bootstrap. Current `useHttp` requests publish the Inertia router lifecycle; non-`X-Inertia` requests are covered by the configured-client wrapper. Do not add page-level blocking overlays or bypass that client boundary. Keep prefetch, polling, deferred/infinite-scroll work, and Precognition non-blocking through `showProgress: false` or the established header exclusion, with local feedback where needed.

## Use StatusNotice for compact operation feedback

Use the shared StatusNotice component for visible compact information, loading, success, and error lifecycle or helper messages. Keep ordinary descriptions and result counts in their existing semantic owners; do not duplicate live-region, icon, gradient, tone, forced-colors, or reduced-motion mappings.

## Keep the authenticated header free of global search

Do not add a global search button, command palette, command-search overlay, or dedicated global-search state to the authenticated header or application shell. Domain-scoped search and filters inside task, project, workspace, member, and preference workflows remain allowed.
