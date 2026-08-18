---
paths:
  - 'app/Actions/**'
---

# Actions

## Keep registration workspace-ready
Web/API registration and required onboarding exit must use EnsureUserHasWorkspace so the user has one authorized current workspace with owner membership and canonical task definitions. The bootstrap is idempotent, localized, and must never fabricate a project or task. Do not enforce this by writing from ordinary read middleware or by recreating a workspace after a deliberate deletion.
