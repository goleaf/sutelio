---
paths:
  - '{app/Http/Middleware/EnsureOnboardingIsComplete.php,routes/**,resources/js/app.ts,resources/js/layouts/OnboardingLayout.vue}'
---

# Layouts

## Required onboarding owns route and shell containment
While onboarding is required, only the exact named onboarding flow actions, locale selection, sign-out, and signed invitation acceptance may bypass the gate. Do not use an onboarding-prefix exemption because the completion-only checklist dismissal route shares that namespace. The gate must run before throttling and route-model binding so pending users cannot observe protected route existence or consume product-route limits. All other web requests redirect to onboarding and authenticated API product requests return 409 onboarding_required. Onboarding pages must resolve through the dedicated navigation-free layout; never mount the application sidebar or header there.
