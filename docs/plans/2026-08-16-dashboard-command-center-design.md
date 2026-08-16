# Dashboard Command Center Design

Date: 2026-08-16

## Goal

Turn the existing dashboard into a focused daily command center that helps workspace members decide what to do next. Preserve the current Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, Reka/shadcn-style component system, Wayfinder, translations, and SQLite-backed dashboard query contract.

## Direction

Use a refined editorial productivity aesthetic: warm orange is the primary accent, typography and spacing create the hierarchy, and restrained sky, emerald, and red tones communicate task state. The page should feel calm and purposeful rather than like a dense analytics console.

The memorable element is an asymmetric work queue. Overdue work receives the dominant surface, while today and upcoming work remain compact supporting queues. This removes the large empty columns produced by the current equal-height three-card grid.

## Information Architecture

1. Keep the shared workspace page header and its four trusted aggregate metrics.
2. Add a generated-Wayfinder action from the header into the complete task index.
3. Introduce a short localized section heading that frames the task lists as the user's current focus.
4. Use a responsive bento layout:
   - overdue tasks occupy the wider primary card;
   - today's and upcoming tasks stack in the supporting column;
   - the layout collapses into a single reading order on narrow screens.
5. Make each task row an Inertia link to the authorized task detail route, with project context, date/status context, visible focus, hover feedback, and a minimum 44-pixel target.
6. Keep empty states compact and descriptive instead of stretching them to the height of the longest neighboring queue.
7. Place the weekly activity surface below the work queue and pair the visual comparison with a real data table.

## Weekly Activity

The existing seven-day created-versus-completed data remains authoritative. No chart library or dependency is added.

The redesigned component provides:

- localized completed and created totals;
- a completion-to-creation balance summary that does not rely on color alone;
- paired CSS bars with zero values represented honestly;
- full-date accessible labels for every day;
- a semantic table containing date, completed count, and created count;
- responsive containment so the table never causes document-level horizontal overflow;
- transition behavior disabled for reduced-motion users.

## Components And Data Flow

- `Dashboard.vue` remains the page coordinator and consumes the existing `stats`, `todayTasks`, `overdueTasks`, `upcomingTasks`, and `weeklyData` props immutably.
- A focused task-queue component may be extracted if it removes meaningful repeated markup while keeping navigation and display logic independently testable.
- `ProductivityChart.vue` owns weekly derived totals, visual bars, legend, and the accessible table.
- Generated Wayfinder helpers provide task-index and task-detail navigation; no raw application URLs are introduced.
- Existing `useUi()` helpers provide every date and number format.

## Responsive And Interaction Contract

- Mobile-first single-column reading order: header, metrics, overdue queue, today queue, upcoming queue, weekly activity.
- Desktop uses an asymmetric two-column task layout and a visual-plus-table weekly layout.
- Interactive rows expose visible keyboard focus and stable color/border hover states without scale-based layout shifts.
- Controls and rows meet the 44-pixel touch-target minimum.
- Light and dark themes retain readable text, visible borders, and non-color status labels.
- No horizontal document overflow at 390, 768, 1024, or 1440 pixels.

## Localization And Accessibility

All new visible and assistive text uses semantic English, Lithuanian, and Russian translation keys with English fallback. The page maintains one `h1`, logical section headings, semantic lists, a named figure, and a captioned table. Color is reinforced by icons and text, and animations respect reduced-motion preferences.

## Testing And Verification

Test-first coverage will pin:

- generated task-index and task-detail navigation;
- the asymmetric responsive queue structure;
- compact empty-state behavior;
- the weekly totals and real table equivalent;
- EN/LT/RU key parity and absence of hardcoded visible copy;
- reduced-motion and accessible-label contracts.

Verification will run focused Pest coverage, Pint for touched PHP catalogs/tests, the full Pest suite, PHPStan, frontend tests, Vue type checking, ESLint, Prettier verification, the production build, `git diff --check`, and live desktop/mobile light/dark browser QA with current browser logs.

## Scope Boundaries

- No database, query, controller, route, package, or dependency change is planned.
- Calendar range/view work, workspace-switch cancellation, broader navigation redesign, and other Phase 10 modules remain separate coherent slices.
- Existing dependency-audit findings remain outside this visual phase unless a changed dependency directly causes a regression.
