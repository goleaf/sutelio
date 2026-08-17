# Onboarding Choice Coherence Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the workspace, project, and task onboarding steps show only choices that are actually available, while preserving select-existing behavior for invited, populated, and replay journeys.

**Architecture:** `OnboardingQuery` remains the server-authoritative owner of bounded, authorized option sets. Each focused Vue step derives its presentation from the corresponding option-array cardinality: an empty array renders a create-only explanation and form, while a non-empty array renders the existing select-or-create segmented control. `Index.vue` continues to normalize the submitted mode to `create` when no current or first option exists, so the request payload cannot claim an unavailable selection path.

**Tech Stack:** Laravel 13, Inertia 3, Vue 3 Composition API, TypeScript, Tailwind CSS 4, semantic EN/LT/RU PHP translation catalogs, Pest 5, Chrome DevTools MCP, Playwright MCP.

---

## Analysis And Decision Record

- Live Russian registration on `https://xiaomi-mimo.test/onboarding` reproduced the same contradiction at three locations: `WorkspaceStep.vue`, `ProjectStep.vue`, and `TaskStep.vue` render a disabled “choose existing” action when their authorized option list is empty.
- The server query is already correct: it returns only authorized workspaces, active projects in the selected workspace, and active tasks in the selected project, each bounded to 100 entries. No query, route, policy, schema, migration, or authorization change is required.
- The current page state is also safe: `synchronizeStepDraft()` selects the first option when present and otherwise sets `mode` to `create`. The defect is presentation coherence, including copy that describes an unavailable branch.
- The historical onboarding design explicitly requires workspace creation when the user has no accessible workspace and selection only when a safe existing entity is available. Conditional presentation therefore aligns the UI with the existing product and security contract.
- The lowest coherent correction is the three sibling step components plus their shared copy shape. A new universal component would obscure different fields and previews without reducing meaningful duplication.

## Task 1: Lock The Empty-Option Contract With A Failing Regression

**Files:**

- Modify: `tests/Feature/OnboardingFrontendTest.php`

- [x] Add one focused Pest source-contract test that reads all three entity-step components and requires the select-or-create group to be guarded by option availability.
- [x] Require each component to select between `copy.description` and `copy.create_description` from the same availability condition.
- [x] Add a locale regression requiring mode-neutral entity-step headings in EN, LT, and RU.
- [x] Run both focused filters and confirm RED before implementation: the first failed on missing `hasExistingOptions`; the second failed on the previous English “Choose your workspace” heading.

## Task 2: Implement Create-Only And Select-Or-Create Presentation

**Files:**

- Modify: `resources/js/components/onboarding/WorkspaceStep.vue`
- Modify: `resources/js/components/onboarding/ProjectStep.vue`
- Modify: `resources/js/components/onboarding/TaskStep.vue`
- Modify: `resources/js/components/onboarding/onboarding-types.ts`

- [x] Add a typed `hasExistingOptions` computed value to each step.
- [x] Render `copy.create_description` when the authorized array is empty and retain `copy.description` when selection is possible.
- [x] Render the segmented control only when `hasExistingOptions` is true; the create form remains the only visible branch otherwise.
- [x] Preserve selected previews, processing locks, validation wiring, keyboard semantics, and the current `Index.vue` mode normalization.
- [x] Run the focused Pest test; full `npm run types:check` is recorded under the moving-worktree gate because its current failures are confined to concurrent `TaskFilterBar.vue` work.

## Task 3: Keep All Locales Semantically Complete

**Files:**

- Modify: `lang/en/onboarding.php`
- Modify: `lang/lt/onboarding.php`
- Modify: `lang/ru/onboarding.php`

- [x] Add complete `create_description` messages for workspace, project, and task in all three locale catalogs.
- [x] Replace entity-step headings with truthful mode-neutral wording while keeping the selection labels needed by populated/invited/replay flows.
- [x] Run `php artisan test --compact tests/Feature/FrontendLocalizationTest.php tests/Feature/OnboardingFrontendTest.php`.

## Task 4: Record The Durable Product Invariant

**Files:**

- Modify through Laravel Boost `record-rule`: `.ai/rules/onboarding.md` and `.ai/rules/index.md`
- Modify: `docs/requirements.md`
- Modify: `docs/non-functional-requirements.md`
- Modify: `docs/compliance-matrix.md`
- Modify: `docs/implementation-plan.md`
- Append only: `docs/progress.md`

- [x] Record that unavailable actions, modes, and explanatory copy must not be rendered as disabled dead ends; empty, populated, invited, and replay states must each expose only truthful branches.
- [x] Require future discovered workflow incoherence to receive a canonical requirement update and a regression in the same delivery slice.
- [x] Update `sys-onboarding-001` and add stable `ui-coherence-001` traceability without renumbering existing IDs.
- [ ] Record exact verification, limitations, query/route/schema deltas, commit, and push status in the append-only progress journal.

## Task 5: Verify Runtime Behavior And Deliver An Attributable Slice

**Files:**

- Review only the files listed above; do not stage or attribute concurrent responsive, motion, timezone, gradient, global-operation, database, or documentation work.

- [x] Run focused Pest and frontend tests, scoped ESLint, Prettier verification, and the production Vite build; keep the moving-worktree Vue type failure explicit until the concurrent task-filter slice settles.
- [x] Run applicable Laravel/PHP checks and the complete Pest suite; record the current four moving-worktree failures exactly and rerun after the concurrent files settle.
- [x] In Chrome DevTools and Playwright disposable profiles, verify a new Russian account has no existing-choice controls on empty workspace/project/task steps.
- [x] Verify a populated fixture still exposes select-existing controls, keyboard focus reaches the current heading, mobile width has no overflow, and the final browser sessions have no console errors.
- [ ] Inspect full and scoped diffs, check for secrets, use a temporary scoped index if concurrent staging remains, commit semantically, and push `origin main` only when ancestry and required gates are safe.

## Acceptance Criteria

- A newly registered user with zero authorized options sees a direct create form and create-only explanatory text on all three entity steps.
- “Choose existing” is absent, not merely disabled, when its option array is empty.
- Invited, populated, and replay users retain select-existing behavior whenever the server supplies at least one authorized option.
- EN/LT/RU catalogs remain shape-compatible and no raw key appears.
- No backend query, route, schema, policy, authorization, dependency, or stored-data behavior changes.
- The durable rule and canonical traceability prevent the same dead-branch pattern from being reintroduced without a failing regression.

## Risks And Mitigations

| Risk                                                                           | Mitigation                                                                                                                                                              |
| ------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A stale `select` mode survives after options disappear                         | `Index.vue` already resynchronizes mode from current state/first option on every step identity change; focused source and browser checks verify create-only submission. |
| The fix removes valid replay or invitation selection                           | The condition uses server-authorized array length, not account age or registration source. Non-empty option fixtures and browser replay verify preservation.            |
| Concurrent staged UI work is accidentally published                            | Review index/worktree separately and stage this slice through a temporary index or wait for the foreign owner to settle; never reset or force-push.                     |
| “Automatic logic detection” is interpreted as unsafe runtime self-modification | Encode the invariant in canonical requirements, a durable Boost rule, and regressions. Application code does not rewrite its own source or documentation at runtime.    |
