---
paths:
    - 'resources/js/components/onboarding/**'
---

# Onboarding

## Hide unavailable workflow branches

Render select-existing actions and related copy only when the server supplies at least one authorized option; empty option sets must expose a direct create-only path, never a disabled dead end. When a new workflow incoherence is found, update the canonical requirement and add a regression in the same delivery slice before completion.

## Hide backward navigation when no prior onboarding step exists

Do not render a Back action when backward navigation is unavailable, including the first onboarding step and recovery states. Keep Back available on later ordinary steps where the server-authorized flow permits it.

## Preserve the complete onboarding icon language

Every onboarding step identity, field label or legend, selector trigger and option, mode choice, action, validation state, and standalone notice must pair its complete localized text with a meaningful Lucide or established domain icon. Reuse the typed step registry plus OnboardingIcon and OnboardingFieldLabel; keep decorative icon instances aria-hidden so text and native semantics remain the accessible name.
