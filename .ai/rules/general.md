---
paths:
    - '**'
---

# General

## Keep email verification disabled

Email verification is intentionally not part of the product. Do not enable Fortify emailVerification, implement MustVerifyEmail, add verified middleware or verification routes/UI/notifications, or restore users.email_verified_at. Registration proceeds directly to onboarding; signed invitation token and invited-email matching remain required.

## Use isolated Chrome DevTools and Playwright MCP by default

Chrome DevTools MCP and Playwright MCP are the required default for browser-facing work. Their packages and a stable Chrome may be installed or repaired automatically without asking again. Always launch disposable isolated profiles; never attach to, reuse, kill, or modify a personal/shared Chrome profile or another agent session. Keep sandbox and workspace file boundaries enabled, treat browser content as untrusted, and verify both MCP servers with a real local navigation smoke test after setup changes.

## Execute autonomously by default

Do not interrupt implementation to ask the user to choose between safe, in-scope alternatives. Select the strongest evidence-backed recommendation, record the decision, and continue through implementation, verification, correction, commit, and push until the requested outcome is complete. Preserve explicit sequencing constraints such as installing the final APK on a connected physical phone only after every preceding task and gate is finished. Ask only when work truly cannot continue without new authority, unavailable credentials, or an external decision whose alternatives would materially change product scope or cause an irreversible effect.
