# Data Safety Center Design

## Status

Approved on 2026-08-16 from the recommended coordinated two-route direction. The user's repeated instruction to update both code and design is treated as approval to proceed with the recommended repository-grounded option.

## Objective

Turn the existing data-transfer and backup settings into one coherent data-safety experience without mixing their authorization scopes. Preserve the implemented import, export, snapshot, download, and restore security boundaries while making scope, sequence, risk, and current location immediately understandable on desktop and mobile.

## Current Evidence

- `/settings/export` already provides streamed JSON/CSV/Markdown export and bounded JSON/CSV Select -> Preview -> Confirm import.
- `/settings/backup` already provides operator-only opaque snapshot inventory, creation, private download, and guarded restore.
- The live 1,440-pixel Export/Import page has no overflow or runtime error and follows Warm Precision, but treats transfer actions as equal undifferentiated cards.
- At 390 pixels the shared settings navigation shows only its first items; the active Export/Import destination is offscreen, so users cannot see where they are without horizontally searching.
- The demo user correctly receives 403 from the operator-only backup route, confirming that workspace transfer and application backup must remain separate capabilities.

## Considered Directions

### Coordinated Two-Route Data Safety Center — Selected

Keep `/settings/export` workspace-scoped and `/settings/backup` operator-only. Give both pages a shared custody-chain visual language, scope labels, step hierarchy, status treatment, and responsive settings navigation. Operators receive a clear bridge between workspace transfer and system snapshots without exposing backup inventory or actions to ordinary users.

This preserves security, limits migration risk, and resolves both the information hierarchy and mobile navigation defect.

### Single Combined Route — Rejected

A single page could appear simpler, but workspace members and configured backup operators have different authorization and password-confirmation boundaries. Combining the controllers or props would increase the chance of capability leakage, unnecessary privileged queries, and confusing partial states.

### Visual-Only Card Refresh — Rejected

Restyling the existing cards would not expose data scope, clarify the import sequence, distinguish reversible downloads from destructive restore, or make the active mobile settings destination visible.

## Experience Direction: Warm Custody Chain

The feature remains inside the fixed Warm Precision system. Its distinctive motif is a calm custody chain: each operation states what data it covers, where it moves, and whether it can change current state.

- Orange identifies the active path and primary workspace action.
- Blue communicates portable export and preview information.
- Emerald communicates verified/read-only readiness.
- Red is reserved for the restore boundary and is always paired with explicit risk text and an icon.
- Instrument Sans, semantic tokens, rounded panels, restrained shadows, Lucide icons, and the existing light/dark/system themes remain unchanged.
- Decorative motion is avoided. Only short color/opacity transitions are used, with reduced-motion fallbacks.

## Information Architecture

### Shared Settings Navigation

- Desktop retains the current vertical settings navigation.
- Mobile replaces the horizontally clipped strip with a 44-pixel current-section menu built from the existing accessible dropdown primitives.
- The trigger exposes the current section name and icon. Menu items remain real Inertia/Wayfinder links with `aria-current="page"` on the active destination.
- Focus returns to the trigger after dismissal, Escape closes the menu, and long Lithuanian/Russian labels wrap safely.

### Workspace Data Transfer

The Export/Import page becomes the workspace-level Data Transfer center.

1. A scope strip identifies the active workspace and states that operations affect workspace data rather than the whole application database.
2. Export is presented as a read-only portability lane with JSON, CSV, and Markdown choices. Each format includes a concise best-use label rather than only an uppercase extension.
3. Import is presented as an explicit three-stage lane: Select file, Review preview, Confirm import.
4. The selected file has a stable summary with name, format, size, clear action, and upload progress.
5. Preview metrics remain projects, tasks, and schema version. The confirm action appears only after a successful preview and is described as a transactional workspace change.
6. Operators receive a small system-backup bridge linking to `/settings/backup`; ordinary users receive no backup link or privileged metadata.
7. Every workspace member can keep using exports, while only owners and administrators receive import controls. Read-only members see an explicit role explanation instead of a file picker that would fail after upload.
8. Format guidance states the actual transfer boundary: import recreates projects and core task fields, while relationship/reference data and other workspace domains are not recreated.

### System Backup And Restore

The Backup page remains operator-only and application-scoped.

1. An application-scope warning distinguishes snapshots from workspace exports.
2. Snapshot creation is the primary safe action; restore is visually secondary until a snapshot is selected.
3. Inventory becomes an ordered snapshot timeline/list with localized date, size, verified-state text, download, and restore actions.
4. Restore confirmation repeats the selected snapshot date, states that the application database will be replaced, and keeps the existing guarded server workflow authoritative.
5. Empty, creating, restoring, failure, and completed states remain explicit and action-scoped.

## Component And Code Boundaries

- `resources/js/layouts/settings/Layout.vue` owns responsive section navigation only.
- `resources/js/pages/settings/Export.vue` remains the coordinator for transfer requests and immutable workspace props.
- `resources/js/pages/settings/Backup.vue` remains the coordinator for backup mutations and inventory props.
- Focused feature components under `resources/js/components/settings/data/` own reusable scope, format, import-progress, and snapshot presentation when a fragment is used by more than one page or materially reduces page responsibility.
- A pure TypeScript helper owns file-size formatting, selected-format metadata, and import-step derivation so behavior can be tested without mounting Vue.
- Existing `useHttp`, router mutations, Wayfinder routes, confirmation dialog, shared header/cards/buttons/spinner/empty state, translations, and backend actions/services remain the foundation.
- Controllers stay thin. No resource, Vue component, policy, or loop may introduce a query.

## Data And Interaction Flow

### Export

1. The user chooses a documented format.
2. The browser navigates to the existing authorized streamed export route generated by Wayfinder.
3. The page announces that preparation started without claiming the download completed.

### Import

1. File selection resets any stale preview or import response.
2. The preview request uploads through Inertia's HTTP client with action-specific progress and errors.
3. Only a successful server preview advances the UI to Review.
4. Confirmation submits the same file and format through the existing transactional import endpoint.
5. Inertia HTTP validation responses are treated as failures even though HTTP 422 resolves through the request helper; success clears the file, preview, progress, and errors, while failure preserves the review context for correction/retry.

### Backup

1. The operator creates a snapshot through the existing action and receives server-authoritative inventory on redirect.
2. Download uses the existing private route and opaque identifier.
3. Restore opens the existing accessible confirmation boundary, disables duplicate submission, and delegates locking, integrity verification, rollback, and maintenance guards to the server action.

## Accessibility And Responsive Contract

- One `h1` per page and ordered `h2`/`h3` section hierarchy.
- Native links/buttons/labels before ARIA; icon-only actions have localized accessible names.
- Every interactive target is at least 44 by 44 pixels.
- Mobile settings navigation exposes the current destination without horizontal searching.
- Import stages use text and structure, not color alone. Progress has a label; preview uses a concise polite status; the entire page is not live.
- File inputs remain associated with visible labels and expose accepted format, maximum size, errors, and disabled/busy state.
- Restore risk is explicit in text and confirmation; color is supplemental.
- Focus remains visible and returns predictably after menus/dialogs. Keyboard order follows visual/DOM order.
- Long translations, long filenames, 390-pixel mobile, 1,440-pixel desktop, light/dark, reduced motion, and forced colors must not create document overflow or hidden controls.

## Error And Empty States

- Unsupported, oversized, malformed, mixed-workspace, or over-limit imports retain server-localized errors and never advance to confirmation.
- A failed preview or execution retains the selected file when retry is useful and never implies partial success.
- Backup creation/restore errors retain the inventory and selected context while clearing processing state.
- An empty backup inventory uses the shared empty state and keeps snapshot creation available.
- A user without backup capability sees neither backup navigation nor a backup bridge; direct access remains a server 403.

## Verification

- Pest covers transfer authorization, preview/execution separation, invalid boundaries, transactional rollback, export scope/formula safety, backup operator/password boundaries, opaque inventory, and guarded restore.
- Frontend behavior tests cover format metadata, file-size formatting, import stages/reset, and mobile active-navigation selection.
- Source/design tests cover Wayfinder, Inertia HTTP state, real links, accessible menu/dialog semantics, 44-pixel controls, semantic copy, and no unsafe dynamic Tailwind classes.
- English, Lithuanian, and Russian key/placeholder parity and complete-sentence copy are required.
- Pint, Larastan, focused/full Pest, frontend tests, Vue type checking, ESLint, Prettier, production build, Composer/npm audits, and `git diff --check` must pass.
- Live Herd QA covers export/import at desktop/mobile, active mobile settings navigation, file preview/cancel/error behavior with safe fixtures, operator-only backup denial for ordinary users, authorized backup presentation through tests, light/dark/reduced-motion/forced-colors states, focus order, overflow, and current browser logs.

## Non-Goals

- No database schema, dependency, queue, storage-disk, retention-policy, import-format, export-format, or restore-protocol change.
- No exposure of server paths, backup filenames, private files, database health internals, or operator identity.
- No merge of workspace authorization with application-operator authorization.
- No drag-and-drop-only upload, automatic restore, destructive bulk backup action, remote cloud storage, or runtime design-family switch.
