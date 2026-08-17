# Global Operation Feedback Implementation Plan

Date: 2026-08-17
Design: `docs/superpowers/specs/2026-08-17-global-operation-feedback-design.md`

## Goal

Deliver one universal, accessible, non-dismissible busy-state system for deliberate page navigation, locale changes, saves, uploads, and all existing Inertia/standalone CRUD actions without duplicating implementation across feature pages or blocking background prefetch/poll/deferred work.

## Architecture Decisions

- Use one token-safe controller instead of a global boolean so overlapping requests cannot release the UI early.
- Bind router lifecycle events for all current Inertia visits, including the installed `useHttp`, and wrap only non-`X-Inertia` configured-client requests as a future-safe boundary without double counting.
- Keep local button/field spinners and validation states; the global overlay is an additional application-wide interaction lock.
- Mount one teleported overlay at the Vue bootstrap boundary so every layout receives the same behavior.
- Disable Inertia's default progress renderer and let the shared component own both the top line and centered overlay.
- Block only deliberate foreground operations. Exclude prefetch, `showProgress: false` background work, and Precognition validation.
- Use existing semantic translations and the single CSS-first Tailwind entrypoint; add no dependency or parallel style/translation system.

## Dependency Graph

```text
documented behavior and source inventory
    -> failing controller/client tests
        -> busy controller and HTTP wrapper
            -> failing root/source/localization contract
                -> router binding and root overlay
                    -> CSS/a11y/localization integration
                        -> focused quality gates
                            -> browser matrix
                                -> canonical evidence and scoped delivery
```

## Phase 1 — Baseline And Written Contract

### Task 1.1 — Protect the shared worktree

Acceptance criteria:

- Record branch, HEAD, origin divergence, staged/unstaged/untracked paths, and existing changes in every overlapping file.
- Do not reset, stash, reformat, stage, or commit unrelated responsive, motion, timezone, database, constructor, audit, or documentation work.
- Use a scoped temporary index for the final attributable commit.

Verification:

- `git status --short --branch`
- `git diff --stat`
- `git diff --cached --stat`

### Task 1.2 — Complete the occurrence inventory

Acceptance criteria:

- Count Vue/TypeScript consumers of `useForm`, `<Form>`, `useHttp`, router methods, processing state, and spinners.
- Confirm there is no first-party parallel fetch/Axios/XHR mutation path outside the configured adapter.
- Record foreground and background request-state boundaries and all supported locales/viewports/input modes.

Verification:

- targeted `rg` inventories under `resources/js`, `lang`, and `resources/css`
- installed Inertia 3.6.1 types/source and Boost version-specific event/progress documentation

### Task 1.3 — Publish and self-review the design

Acceptance criteria:

- Compare page-by-page, router-only, and router-plus-HTTP-wrapper approaches.
- Resolve double counting, concurrency, cancellation, upload progress, prefetch/poll/deferred exclusions, focus, `inert`, reduced motion, forced colors, NativePHP viewport, and rollback.
- Contain no `TODO`, `TBD`, contradictory scope, or ambiguous cancellation promise.

Verification:

- `rg -n "TODO|TBD|to be decided" docs/superpowers/specs/2026-08-17-global-operation-feedback-design.md`
- `npx prettier --check docs/superpowers/specs/2026-08-17-global-operation-feedback-design.md`

## Phase 2 — Failing-First Contracts

### Task 2.1 — Add the pure TypeScript behavior test

Acceptance criteria:

- Test one operation, overlapping operations, out-of-order completion, and idempotent completion.
- Test contextual kind, percentage clamping/reset, and latest-operation presentation.
- Test Inertia/Precognition exclusion plus standalone upload forwarding and cleanup on resolve/reject.

Verification:

- Run the new Node test alone and observe expected failure because the production module does not exist.

### Task 2.2 — Add the focused Pest source contract

Acceptance criteria:

- Require a single root `GlobalBusyOverlay` mount and custom router/HTTP bindings.
- Require default Inertia progress to be disabled so two top bars cannot render.
- Require 70% backdrop, top line, centered status, no dismiss control, root `inert`/`aria-busy`, body lock, polite live status, EN/LT/RU keys, reduced-motion and forced-colors behavior.

Verification:

- `php artisan test --compact tests/Feature/GlobalOperationFeedbackTest.php`
- Observe an expected missing-file/contract failure before production edits.

## Phase 3 — Shared Busy Foundation

### Task 3.1 — Implement the token-safe controller

Acceptance criteria:

- `begin(kind)` returns an idempotent handle with `finish`, `setKind`, and `setProgress`.
- State remains busy until every active handle finishes.
- Percentage is finite, clamped to 0–100, and reset with the completed operation.

Verification:

- focused Node behavior test passes its controller cases
- `npm run types:check`

### Task 3.2 — Implement router lifecycle binding

Acceptance criteria:

- Track visit IDs only when `showProgress` is true and the visit is not prefetch.
- Infer opening versus processing from HTTP method.
- Forward upload percentage and always release completed/interrupted/cancelled visits.
- Returned teardown releases listeners and active handles.

Verification:

- focused Node classification/overlap tests
- focused Pest bootstrap contract

### Task 3.3 — Implement non-Inertia configured-client wrapping

Acceptance criteria:

- Ignore `X-Inertia` requests to prevent router double counting.
- Ignore Precognition validation.
- Track all other configured requests, forward upload callbacks, and release in `finally` for every terminal result.
- Preserve the installed Axios adapter and do not introduce a dependency or direct mutation client. Current `useHttp` traffic remains router-owned because it carries `X-Inertia`; the wrapper covers only traffic that does not.

Verification:

- focused Node resolve/reject/upload/header tests
- TypeScript and ESLint checks

## Phase 4 — Universal Presentation

### Task 4.1 — Implement the root overlay

Acceptance criteria:

- Teleport once to `body` from the application bootstrap root.
- Render one fixed top progress line and centered spinner/status surface over `bg-background/70`.
- Expose contextual localized opening/loading/processing/uploading text and an automatic-completion hint.
- Render no close/cancel action and intercept pointer input across the viewport.

Verification:

- focused Pest contract
- component source inspection
- production build

### Task 4.2 — Enforce the temporary interaction lock safely

Acceptance criteria:

- Set the Inertia root `inert` and `aria-busy="true"` only while foreground operations exist.
- Lock body scrolling and stop Escape from closing an underlying dialog while busy.
- Restore every previous attribute/class/listener on completion or component teardown without stealing focus.

Verification:

- Node/Pest source contract
- browser click, Escape, focus, and cleanup checks

### Task 4.3 — Add CSS-first motion and media behavior

Acceptance criteria:

- Keep `resources/css/app.css` as the only stylesheet.
- Indeterminate top progress travels without layout animation; determinate width reflects upload percentage.
- Reduced motion leaves a static visible progress cue and text; forced colors retains boundary and Highlight cue.
- Overlay fits 320px through wide desktop and dynamic NativePHP WebViews without document overflow.

Verification:

- focused responsive/design contract
- reduced-motion/forced-colors browser emulation
- web/Android/iOS Vite builds and bundle comparison

## Phase 5 — Localization And Future Governance

### Task 5.1 — Add semantic EN/LT/RU state copy

Acceptance criteria:

- Add identical nested keys and placeholder shapes to all three locales.
- Use complete sentences and preserve English fallback.
- Render no raw key in the overlay.

Verification:

- focused global feedback and localization tests
- representative rendered EN/LT/RU checks

### Task 5.2 — Record the durable shared-component rule

Acceptance criteria:

- Future Inertia foreground operations inherit the global contract automatically.
- Future standalone HTTP actions must use the configured Inertia client path; background work must opt out explicitly and retain local non-blocking feedback.
- Page-specific global overlays and cancel controls are prohibited unless a new approved product requirement supersedes this contract.

Verification:

- Laravel Boost `record-rule`
- `.ai/rules` keyword lookup

### Task 5.3 — Synchronize canonical Markdown

Acceptance criteria:

- Update frontend, design-system, accessibility, localization, testing, implementation-plan, compliance, and append-only progress evidence where the behavior changes their contract.
- Distinguish implemented source from actually executed verification.
- Record zero query/route/schema/policy/API/dependency delta.

Verification:

- targeted Markdown review and links
- Prettier and `git diff --check`

## Phase 6 — Focused Automated Verification

### Task 6.1 — Run the red/green regression gate

Verification:

- new Node test file directly
- `php artisan test --compact tests/Feature/GlobalOperationFeedbackTest.php`
- `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/FrontendDesignTest.php`

### Task 6.2 — Run frontend static and build gates

Verification:

- `npm run test:frontend`
- `npm run types:check`
- `npm run lint:check`
- `npm run format:check`
- `npm audit`
- `npm run build`
- `npm run build:android`
- `npm run build:ios`

### Task 6.3 — Run repository PHP gates

Verification:

- `vendor/bin/pint --dirty --format agent`
- `composer run types:check`
- `php artisan test --compact`
- `composer validate --strict`
- `composer audit --locked`

No database migration or seeding gate is required by behavior, but the existing mandatory release gate remains applicable before delivery and must use an isolated database.

## Phase 7 — Browser And Accessibility Matrix

### Task 7.1 — Establish the isolated local browser smoke

Acceptance criteria:

- Resolve the Herd URL through Laravel Boost.
- Chrome DevTools and Playwright each navigate using disposable isolated state.
- Do not attach to, kill, or modify personal/shared profiles or use personal accounts.

### Task 7.2 — Verify foreground navigation and mutation

Acceptance criteria:

- Exercise at least one Inertia page visit, locale update, standard form write, and `useHttp` CRUD action; confirm the installed `useHttp` path publishes router lifecycle events and is not double-counted by the configured-client wrapper.
- Observe one overlay, one top line, contextual text, 70% computed backdrop, pointer/keyboard lock, and clean release on success and validation/network failure where safely reproducible.
- Confirm the server request is sent once and receives the expected response.

### Task 7.3 — Verify presentation modes

Acceptance criteria:

- Check EN/LT/RU at 320x568, 390x844, 820x1180, 1440x1000, and landscape phone.
- Check 200% reflow, keyboard focus, coarse pointer, reduced motion, forced colors, and dark OS preference with unchanged light-only output.
- Confirm overlay bounds equal the viewport, no document overflow, status exists in the accessibility tree, and console/network logs are clean.

## Phase 8 — Final Evidence And Delivery

### Task 8.1 — Reconcile documentation with observed results

Acceptance criteria:

- Append exact test counts, build sizes/times, browser routes/modes, warnings, failures, limitations, and corrections.
- Do not call an unexecuted or interrupted gate passing.

### Task 8.2 — Review attributable diff

Acceptance criteria:

- Inspect complete worktree and phase-only diffs.
- Verify no secret, credential, generated artifact, dependency drift, unrelated formatting, or user-owned change enters the phase commit.
- Use a temporary scoped index because the shared worktree is already staged and dirty.

### Task 8.3 — Commit and push on main

Acceptance criteria:

- Create one coherent semantic commit for the global operation-feedback phase.
- Push `origin main` without force or history rewriting.
- Record exact commit and push result; if the remote rejects, preserve the commit and report the precise blocker.

## Risks And Mitigations

| Risk                                           | Impact                            | Mitigation                                                                                      |
| ---------------------------------------------- | --------------------------------- | ----------------------------------------------------------------------------------------------- |
| Router and HTTP wrapper both count one request | Overlay stays active too long     | Standalone wrapper excludes `X-Inertia` case-insensitively                                      |
| Overlapping requests release early             | Duplicate actions become possible | Token-safe handles and visit-ID map                                                             |
| Background requests freeze the page            | Poor polling/infinite-scroll UX   | Respect `showProgress: false`, prefetch, and Precognition exclusions                            |
| Fast requests flash harshly                    | Visual distraction                | Short opacity transition; no artificial request delay; reduced-motion static cue                |
| Error or cancellation leaves `inert` behind    | Application becomes unusable      | `finally`, finish events, idempotent cleanup, teardown restoration, error tests                 |
| Overlay hides action-specific context          | Ambiguous user feedback           | Contextual global kind plus existing local button/error states                                  |
| Screen reader loses focus                      | Accessibility regression          | Keep status unfocused; restore only a connected origin when no later focus owner has taken over |
| Dirty shared files absorb unrelated edits      | Unreviewable commit               | New focused files, precise patches, temporary scoped index, staged-diff inspection              |
| Native safe areas or zoom clip the status      | Mobile blocker                    | Fixed dynamic viewport geometry, normalized existing safe-area contract, browser/native builds  |

## Completion Rule

The phase is complete only when the controller, router/HTTP coverage, root overlay, localization, CSS, governance docs, focused/full tests, static checks, web/native builds, both browser tools, diff review, scoped commit, and normal push are all factually synchronized. Any failed or unexecuted gate remains explicitly open.
