---
paths:
  - '{app/Http/Controllers/OnboardingController.php,routes/web.php,resources/js/pages/onboarding/**,resources/js/components/onboarding/**}'
---

# Js Components Onboarding

## Mandatory onboarding has no skip path
A pending onboarding run may leave the completion gate only through the Results step and onboarding.complete. Do not add a skip route, action, UI control, or lifecycle bypass. onboarding.replay.exit is only for a completed user's active optional replay and must reject pending users.
