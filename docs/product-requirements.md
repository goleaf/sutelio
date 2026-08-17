# Product Requirements

Sutelio is a trustworthy local-first workspace command center for planning and completing projects and tasks across web and NativePHP Mobile. It is a serious multi-workspace application, not tutorial CRUD.

## Product Outcomes

1. A user can securely manage identity, preferences, and multiple workspaces with explicit owner/admin/member permissions.
2. A workspace can manage projects, tasks, statuses, priorities, hierarchy, checklists, comments, labels, tags, recurrence, reminders, attachments, activity, and notifications without cross-workspace leakage.
3. Task list, dashboard, calendar, project, detail, settings, import/export, and backup workflows remain useful in empty, normal, failure, and high-volume states.
4. External API consumers receive versioned, least-privilege, stable JSON contracts equivalent to the web domain rules.
5. English, Lithuanian, and Russian users receive equivalent, locale/timezone-aware and accessible behavior.
6. Operators can migrate, seed, diagnose, back up, restore, schedule, build, and deploy the SQLite application safely and reproducibly.

Detailed testable requirements and stable IDs live in `docs/requirements.md`; cross-cutting quality requirements live in `docs/non-functional-requirements.md`; implementation and verification traceability lives in `docs/compliance-matrix.md`.

## Explicit Product Boundaries

- Preserve the installed Laravel/Inertia/Vue/Tailwind/Wayfinder architecture and fixed Warm Precision design language.
- SQLite is the only relational database and NativePHP Mobile is an intentional supported runtime.
- Do not introduce Livewire, Volt, Flux, Vue Router, React, jQuery, Filament, Nova, a second SQL database, or infrastructure-heavy services without a new explicit product requirement.
- Private workspace data, files, invitations, tokens, and backups are security boundaries, never convenience shortcuts.
