# Documentation Index

This index defines the first-party documentation system reviewed on 2026-08-18. Source code, migrations, routes, tests, the live SQLite schema, and current canonical documents are executable evidence. Generated analysis output, copied agent-skill bundles, build output, and completed execution plans are not product requirements.

## Mandatory Reading Order

1. `AGENTS.md`
2. `docs/requirements.md`
3. `docs/non-functional-requirements.md`
4. `docs/architecture.md`
5. `docs/domain-model.md` and `docs/data-model.md`
6. `docs/security.md` and `docs/authorization.md`
7. `PRODUCT.md`, `docs/frontend.md`, `docs/design-system.md`, `docs/accessibility.md`, and `docs/localization.md`
8. `docs/testing.md` and `docs/seeding.md`
9. `docs/operations.md`, `docs/deployment.md`, and `docs/sqlite.md`
10. `docs/compliance-matrix.md`, `docs/implementation-plan.md`, and the latest `docs/progress.md` entry

## Canonical Documents

| Path                                  | Purpose                                                                                        |
| ------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `AGENTS.md`                           | Repository-wide engineering contract                                                           |
| `CLAUDE.md`                           | Generated Laravel Boost reference; not a replacement for product requirements                  |
| `README.md`                           | Contributor entry point                                                                        |
| `CHANGELOG.md`                        | Release history                                                                                |
| `PRODUCT.md`                          | Product purpose and design direction                                                           |
| `docs/index.md`                       | Documentation ownership and retention policy                                                   |
| `docs/requirements.md`                | Stable functional and system requirement IDs                                                   |
| `docs/product-requirements.md`        | Product outcomes and scope summary                                                             |
| `docs/non-functional-requirements.md` | Security, data, performance, accessibility, localization, quality, and operations requirements |
| `docs/architecture.md`                | Runtime and architectural boundaries                                                           |
| `docs/domain-model.md`                | Domain concepts, roles, workflows, and invariants                                              |
| `docs/data-model.md`                  | Tables, ownership, constraints, indexes, and lifecycle                                         |
| `docs/authorization.md`               | Owner/admin/member permission model                                                            |
| `docs/security.md`                    | Implemented controls and threat boundaries                                                     |
| `docs/api.md`                         | Versioned API and error contract                                                               |
| `docs/frontend.md`                    | Inertia/Vue/Tailwind architecture                                                              |
| `docs/design-system.md`               | Presentation tokens, components, states, and responsive matrix                                 |
| `docs/accessibility.md`               | Accessibility contract and verification evidence                                               |
| `docs/localization.md`                | EN/LT/RU catalog and locale/timezone workflow                                                  |
| `docs/i18n.md`                        | Compatibility pointer to `docs/localization.md`                                                |
| `docs/testing.md`                     | Test taxonomy, commands, and quality evidence                                                  |
| `docs/seeding.md`                     | Factory and deterministic seeder contract                                                      |
| `docs/sqlite.md`                      | SQLite runtime and migration constraints                                                       |
| `docs/performance.md`                 | Query, payload, pagination, and asset budgets                                                  |
| `docs/caching.md`                     | Cache ownership and invalidation rules                                                         |
| `docs/integrations.md`                | NativePHP and external integration boundaries                                                  |
| `docs/operations.md`                  | Scheduler, health, backup/restore, and incident checks                                         |
| `docs/deployment.md`                  | Web and mobile build/release procedure                                                         |
| `docs/current-state.md`               | Concise current snapshot                                                                       |
| `docs/current-state-audit.md`         | Retained dated modernization evidence synchronized with current status                         |
| `docs/ui-ux-audit-2026-08-17.md`      | Retained dated UI/UX diagnostic evidence                                                       |
| `docs/compliance-matrix.md`           | Requirement-to-code/test mapping                                                               |
| `docs/implementation-plan.md`         | Compact living status and pointer to the only unfinished execution plan                        |
| `docs/code-review.md`                 | Release review checklist and findings                                                          |
| `docs/known-limitations.md`           | Genuine external and environment limitations only                                              |
| `docs/progress.md`                    | Append-only execution and delivery evidence                                                    |

## Historical Evidence

- `docs/plans/*-design.md` retains unique implemented product/design rationale.
- `docs/superpowers/specs/*.md` retains unique system/design contracts.
- `docs/superpowers/plans/2026-08-17-sutelio-database-optimization.md` is the only detailed execution plan with unfinished product work; Task 1 is complete and Tasks 2-8 remain planned.
- Agent skills under `.agents/skills` and generated compatible copies under `.claude`, `.cursor`, `.factory`, and `.grok` remain development-tool instructions. Read the applicable primary skill when its domain changes.
- Completed execution plans, superseded July audits, obsolete tool plans, and generated graph output were removed from the working tree on 2026-08-18 after their durable decisions/evidence were retained here, in other canonical documents, or in `docs/progress.md`. Git history remains the recovery source.

## Retention And Cleanup Rules

- Delete generated analysis/build output from Git and ignore it when it is reproducible and has no runtime or test consumer.
- Delete a completed execution plan after its durable decisions and verification evidence exist in canonical documentation and `docs/progress.md`; Git history remains the recovery source.
- Preserve unique design/spec decisions while they still explain current behavior or constraints.
- Keep `docs/progress.md` append-only. Historical entries may name files later removed from the working tree; those paths remain resolvable through Git history.
- Do not duplicate an active requirement in another canonical file; link to its stable ID.
- A requirement is implemented and verified only when its listed command or runtime gate has passed against the applicable implementation.
- Update paths, counts, commands, dependency versions, and limitations from observed results rather than intention.
- Before UI/UX implementation, apply `ui-system-001`: inventory every affected location and state, then prefer the lowest coherent shared correction with the fewest practical consumer edits.
