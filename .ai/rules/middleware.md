---
paths:
    - app/Http/Middleware/EnsureOnboardingIsComplete.php
---

# Middleware

## Refresh onboarding state in persistent runtimes

The onboarding gate must reload the authenticated user's preferences before deciding. NativePHP keeps the authenticated model alive across requests, so loadMissing can retain the pre-completion row and create an onboarding/product redirect loop.
