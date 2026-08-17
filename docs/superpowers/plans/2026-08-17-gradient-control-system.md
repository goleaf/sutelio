# Sutelio Gradient Control System Implementation Plan

> **Execution note:** Implement task by task with failing-first coverage and verify each shared primitive before browser QA.

**Goal:** Make the onboarding “Skip introduction” action as visible as “Back”, replace every subtle ghost button with the same outlined treatment, and give buttons and form controls a restrained one-hue diagonal Tailwind gradient across desktop and mobile.

**Architecture:** The shared `Button` CVA remains the single owner of button appearance. `ghost` and `outline` intentionally share one visible outlined orange-surface recipe, while semantic variants keep their own same-hue light-to-dark ramps. Shared Input, Textarea, Select, Checkbox, and OTP primitives own the form-control gradient; only active raw text-entry exceptions receive the same static Tailwind recipe. Onboarding keeps business behavior unchanged and adjusts only the skip variant plus phone layout.

**Tech stack:** Vue 3 Composition API, Inertia 3, Tailwind CSS 4.3, Reka UI, Pest 5, Vitest, Chrome DevTools MCP, Playwright MCP.

---

## Task 1: Lock the visual contract with a failing Pest test

**Files:**

- Create: `tests/Feature/GradientControlSystemTest.php`
- Inspect: `resources/js/components/ui/button/index.ts`
- Inspect: `resources/js/components/onboarding/OnboardingShell.vue`
- Inspect: shared form-control primitives under `resources/js/components/ui/`

1. Generate a Pest feature test with Artisan.
2. Assert that `ghost` and `outline` use the same named class recipe, that every boxed semantic button uses `bg-linear-to-br`, and that focus/reduced-motion behavior remains present.
3. Assert that Input, Textarea, SelectTrigger, Checkbox, and InputOTPSlot use the shared diagonal surface recipe.
4. Assert that onboarding Skip explicitly uses the outline variant and is full-width on phone while becoming auto-width from `sm` upward.
5. Run `php artisan test --compact tests/Feature/GradientControlSystemTest.php` and record the expected RED result.

## Task 2: Implement the shared button treatment

**Files:**

- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/onboarding/OnboardingShell.vue`

1. Extract one static `outlinedButtonSurface` recipe.
2. Apply that exact recipe to both `outline` and `ghost` variants so no old invisible ghost treatment remains.
3. Add accessible same-hue diagonal gradients to default, destructive, and secondary boxed variants; keep the semantic link variant unboxed.
4. Preserve keyboard rings, disabled/loading behavior, reduced motion, and the existing fine-pointer `ui-control` lift.
5. Change onboarding Skip to `outline` and make it full-width only on the smallest layout.

## Task 3: Implement the shared form-control gradient

**Files:**

- Modify: `resources/js/components/ui/input/Input.vue`
- Modify: `resources/js/components/ui/textarea/Textarea.vue`
- Modify: `resources/js/components/ui/select/SelectTrigger.vue`
- Modify: `resources/js/components/ui/checkbox/Checkbox.vue`
- Modify: `resources/js/components/ui/input-otp/InputOTPSlot.vue`
- Modify only proven active raw text-entry exceptions discovered by the source inventory.

1. Add statically discoverable Tailwind 4 `bg-linear-to-br` stops using the orange surface family from light to darker light.
2. Use the darker orange ramp for checked/selected controls while retaining accessible foreground contrast.
3. Preserve invalid, focus-visible, placeholder, disabled, forced-colors, and touch-target behavior.
4. Run the focused Pest test until GREEN, then run the existing frontend design/constructor/onboarding contracts.

## Task 4: Frontend and browser verification

**Files:**

- Modify: `docs/progress.md`

1. Run scoped Prettier, ESLint, Vue type checking, Vitest, and the production Vite build.
2. Run focused Pest tests, Pint for the new PHP test, Larastan, then the complete sequential Pest suite.
3. Resolve the project URL through Laravel Boost.
4. Verify the onboarding controls with disposable isolated Chrome DevTools and Playwright sessions at phone, tablet, and desktop widths.
5. Inspect default, hover, keyboard focus, disabled, reduced-motion, forced-colors, long Russian copy, touch-sized controls, and horizontal overflow.
6. Append exact results, limitations, query delta, and delivery status to `docs/progress.md`.

## Task 5: Scoped delivery

1. Re-read status and both unstaged/staged diffs; preserve every unrelated concurrent change.
2. Format owned files and run `git diff --check` against the owned slice.
3. Build a temporary scoped index containing only attributable files.
4. Commit with a semantic message on `main`.
5. Fetch and push only if the remote is a safe linear ancestor; never force-push or publish unrelated ancestors.
