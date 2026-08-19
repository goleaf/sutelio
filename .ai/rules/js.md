---
paths:
    - 'resources/js/**'
    - 'resources/js/**/*.vue'
    - 'resources/js/**/*.{vue,ts}'
---

# Js

## Use global foreground operation feedback

All deliberate Inertia navigation, form, router, `useHttp`, and configured-client actions inherit `GlobalBusyOverlay` from the app bootstrap. Current `useHttp` requests publish the Inertia router lifecycle; non-`X-Inertia` requests are covered by the configured-client wrapper. Do not add page-level blocking overlays or bypass that client boundary. Keep prefetch, polling, deferred/infinite-scroll work, and Precognition non-blocking through `showProgress: false` or the established header exclusion, with local feedback where needed.

## Use StatusNotice for compact operation feedback

Use the shared StatusNotice component for visible compact information, loading, success, and error lifecycle or helper messages. Keep ordinary descriptions and result counts in their existing semantic owners; do not duplicate live-region, icon, gradient, tone, forced-colors, or reduced-motion mappings.

## Keep the authenticated header free of global search

Do not add a global search button, command palette, command-search overlay, or dedicated global-search state to the authenticated header or application shell. Domain-scoped search and filters inside task, project, workspace, member, and preference workflows remain allowed.

## Use the shared package-backed color picker

All user-editable colors must compose resources/js/components/ui/color-picker/ColorPickerField.vue. Do not add native input[type=color] or local picker markup. Keep values opaque six-digit HEX, localize every visible and assistive label in EN/LT/RU, preserve keyboard/touch behavior, and pass presets through the shared component when useful.

## Localize typed client errors and accessible names

Never render third-party or browser error.message strings directly. Map stable typed errors to semantic EN/LT/RU catalog keys and use a localized safe fallback for unknown failures. Bind translated accessible names explicitly when a component library derives aria-label from DOM text, because that derived value may not react to Inertia locale changes.

## Use the shared localized date picker

Every first-party date or date-time entry must compose ui/date-picker/DatePickerField; do not add native date, datetime-local, month, or week inputs. Preserve YYYY-MM-DD for day values and YYYY-MM-DDTHH:mm for minute values, honor locale/week start, portal inside the nearest dialog, and give every calendar control type="button" so it cannot submit a surrounding form.

## Reset page scroll after navigation

Successful foreground Inertia visits reset the document and every [scroll-region] through the shared pageScroll binding unless preserveScroll is explicitly true. Use preserveScroll only for same-page mutations or filters; onboarding step changes and pagination must return to the page start.

## Use pages for record management

Create, edit, duplicate, and detail flows for application records use dedicated Inertia pages, not Dialog or Sheet overlays. Destructive record confirmations use the in-flow PageConfirmPanel. Do not change onboarding surfaces or remove GlobalBusyOverlay; specialized security and first-run language ceremonies remain separate exceptions.
