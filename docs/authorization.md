# Authorization

Workspace membership is the tenant boundary. The `WorkspaceRole` enum is the role source, but authorization is always evaluated by policies, authorized Form Requests, and aggregate-scoped actions. A hidden/disabled frontend control is never authorization.

## Workspace Capability Matrix

| Capability                                          | Owner                                                     | Admin                              | Member                          |
| --------------------------------------------------- | --------------------------------------------------------- | ---------------------------------- | ------------------------------- |
| View workspace, projects, tasks, collaboration data | Yes                                                       | Yes                                | Yes                             |
| Create/update normal projects and tasks             | Yes                                                       | Yes                                | Yes, subject to resource policy |
| Update workspace settings                           | Yes                                                       | Yes                                | No                              |
| Invite/cancel/resend invitations                    | Yes                                                       | Yes, cannot escalate beyond policy | No                              |
| Manage non-owner membership roles/removal           | Yes                                                       | Yes, owner protected               | No                              |
| Manage task statuses/priorities                     | Yes                                                       | Yes                                | No                              |
| Duplicate workspace                                 | Yes                                                       | No                                 | No                              |
| Transfer ownership                                  | Yes, to eligible member                                   | No                                 | No                              |
| Delete workspace                                    | Yes with confirmation                                     | No                                 | No                              |
| Create/download/restore/delete database backups     | Owner-only policy; restore requires password confirmation | No                                 | No                              |

Project, task, comment, reminder, attachment, and invitation policies additionally enforce membership, resource ownership where relevant, and resource state. Every protected API action applies the same policy plus a least-privilege Sanctum ability.

## Identifier Rules

- Route and payload workspace IDs must resolve through the authenticated user's memberships.
- Project/task children resolve through the authorized parent, never a global child lookup followed by a visual check.
- Related project, assignee, parent, definition, label, tag, reminder-user, bulk, and reorder IDs must belong to the same workspace.
- Exact submitted sets compare requested and authorized counts before a transaction. Missing/foreign IDs reject the full operation.
- Policies do not trust locked/client state and Livewire is not used.

Positive owner/member cases and negative non-owner, wrong-role, wrong-workspace, missing-relationship, and invalid-state cases are covered across the workspace, project, todo, child, backup, and API suites listed in `docs/compliance-matrix.md`.
