---
paths:
    - '**'
---

# General

## Keep email verification disabled

Email verification is intentionally not part of the product. Do not enable Fortify emailVerification, implement MustVerifyEmail, add verified middleware or verification routes/UI/notifications, or restore users.email_verified_at. Registration proceeds directly to onboarding; signed invitation token and invited-email matching remain required.
