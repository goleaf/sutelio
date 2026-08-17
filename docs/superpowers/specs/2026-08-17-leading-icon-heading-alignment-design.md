# Leading Icon Heading Alignment Design

## Status

Approved for implementation on 2026-08-17. The product owner selected the recommended shared-component direction and requested project-wide completion without another design checkpoint.

## Objective

Keep every semantic leading icon and its related title/subtitle block in one horizontal row. Vertically center the complete text stack against the icon while allowing long English, Lithuanian, and Russian copy to wrap inside the text column without creating horizontal overflow.

## Audited Baseline

- The first-run language dialog renders its icon, `DialogTitle`, and `DialogDescription` as direct children of the column-oriented `DialogHeader`; at 390, 820, and 1440 CSS pixels the icon therefore sits above “Welcome to Sutelio”.
- The 390-pixel live render has no horizontal overflow, but the icon begins at `y=166.5`, the title at `y=226.5`, and the description at `y=270.5`, proving that the related header content is vertically stacked.
- The same composition drift appears in authentication, dashboard, calendar, project, onboarding, workspace, and settings surfaces. The implementation scope is 28 heading clusters in 20 Vue files.
- Existing correct horizontal headings, KPI cards, list rows, empty-state illustrations, buttons, badges, and decorative visuals are intentionally outside this contract.

## Selected Architecture

Create `resources/js/components/shared/LeadingIconHeading.vue` as the single presentation primitive for this relationship.

The component has two slots:

- `icon` owns the fixed-size leading visual.
- The default slot owns the semantic title and optional subtitle supplied by the consumer.

The component does not create heading or paragraph elements. Each consumer retains its existing `h1`/`h2`/`CardTitle`/`DialogTitle` and description semantics.

## Layout Contract

- The root is a non-wrapping horizontal flex row with `min-w-0`, `flex-nowrap`, and `items-center`.
- The icon wrapper uses `shrink-0` so the icon keeps its intended geometry.
- The content wrapper uses `min-w-0 flex-1`; long localized title and subtitle text may wrap inside this column.
- The content wrapper remains a vertical grid with the established title-to-subtitle rhythm.
- Consumers may extend the root, icon, and content classes through typed class props, composed with the existing `cn()` helper.
- The contract centers the combined title/subtitle block against the icon. It does not independently center each text line.

## Audited Consumer Scope

| Area                                 | Files | Clusters |
| ------------------------------------ | ----: | -------: |
| Authentication and localization      |     2 |        2 |
| Dashboard, calendar, and project     |     3 |        3 |
| Onboarding                           |     4 |        7 |
| Shared settings and workspace panels |     5 |        9 |
| Settings pages                       |     6 |        7 |
| Total                                |    20 |       28 |

The target files are:

- `resources/js/layouts/auth/AuthSimpleLayout.vue`
- `resources/js/components/localization/FirstRunLanguageDialog.vue`
- `resources/js/components/dashboard/DashboardTaskQueue.vue`
- `resources/js/components/calendar/CalendarAttentionRail.vue`
- `resources/js/components/project/ProjectPulse.vue`
- `resources/js/components/onboarding/OnboardingChecklist.vue`
- `resources/js/components/onboarding/ProjectStep.vue`
- `resources/js/components/onboarding/TaskStep.vue`
- `resources/js/components/onboarding/WorkspaceStep.vue`
- `resources/js/components/settings/data/DataScopeBanner.vue`
- `resources/js/components/workspace/WorkspaceConfigurationPanel.vue`
- `resources/js/components/workspace/WorkspaceDangerPanel.vue`
- `resources/js/components/workspace/WorkspaceMembersPanel.vue`
- `resources/js/components/workspace/WorkspaceOverviewPanel.vue`
- `resources/js/pages/settings/Members.vue`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/settings/Preferences.vue`
- `resources/js/pages/settings/Backup.vue`
- `resources/js/pages/settings/Export.vue`
- `resources/js/pages/settings/Security.vue`

## Alternatives Rejected

1. Repeating one-off Tailwind fixes in every consumer would solve the current snapshots but allow the alignment contract to drift again.
2. Changing `CardHeader` or `DialogHeader` globally would alter unrelated title-only, action, metric, and dialog compositions through implicit selector behavior.
3. Forcing every title and subtitle onto one physical text line would break long localized copy, zoom, and the 390-pixel no-overflow requirement.

## Accessibility And Localization

- Preserve existing heading levels, dialog naming, reading order, labels, and decorative `aria-hidden` icon behavior.
- Preserve keyboard, touch, zoom, forced-colors, and reduced-motion behavior; the component introduces no interaction or animation.
- Verify English, Lithuanian, and Russian at 390, 820, and 1440 CSS pixels.
- Require zero horizontal overflow. Text wrapping within the content column is correct behavior.

## Verification Contract

- Failing-first Pest coverage inventories all 28 consumers and asserts the shared primitive's structural classes.
- Existing localization, onboarding, workspace, and frontend design contracts remain green.
- Vue type checking, ESLint, Prettier, frontend tests, npm audit, and the production Vite build pass.
- A live isolated Chromium render proves row direction, centered cross-axis alignment, icon-before-text geometry, localized wrapping, and zero horizontal overflow at phone, tablet, and desktop widths.
- Full backend/static/data/composer gates, complete diff review, semantic commit, and normal `origin/main` push are required before completion.

## Non-Goals

- No copy, translation-key, color, typography, route, query, schema, authorization, API, dependency, Blade, or Filament change.
- No global redesign of cards or dialogs.
- No restoration of email verification.
