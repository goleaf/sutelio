export type WorkspacePortfolioSort =
    'name_asc' | 'name_desc' | 'newest' | 'oldest';

export type WorkspaceTaxonomySection =
    'statuses' | 'priorities' | 'labels' | 'tags';

export interface WorkspacePortfolioItem {
    id: string;
    name: string;
    description?: string | null;
    created_at: string;
    members_count?: number;
    projects_count?: number;
    todos_count?: number;
    owner?: { name?: string | null } | null;
}

interface Identifiable {
    id: string;
}

interface WorkspaceTaxonomyCollections {
    statuses: readonly Identifiable[];
    priorities: readonly Identifiable[];
    labels: readonly Identifiable[];
    tags: readonly Identifiable[];
}

const taxonomySections: readonly WorkspaceTaxonomySection[] = [
    'statuses',
    'priorities',
    'labels',
    'tags',
];

export function filterAndSortWorkspacePortfolio<
    T extends WorkspacePortfolioItem,
>(
    workspaces: readonly T[],
    search: string,
    sort: WorkspacePortfolioSort,
    locale: string,
): T[] {
    const query = search.trim().toLocaleLowerCase(locale);
    const filtered = workspaces.filter((workspace) => {
        if (!query) {
            return true;
        }

        return [
            workspace.name,
            workspace.description ?? '',
            workspace.owner?.name ?? '',
        ]
            .join(' ')
            .toLocaleLowerCase(locale)
            .includes(query);
    });

    return filtered.toSorted((first, second) => {
        if (sort === 'newest') {
            return Date.parse(second.created_at) - Date.parse(first.created_at);
        }

        if (sort === 'oldest') {
            return Date.parse(first.created_at) - Date.parse(second.created_at);
        }

        const direction = sort === 'name_desc' ? -1 : 1;

        return (
            first.name.localeCompare(second.name, locale, {
                sensitivity: 'base',
            }) * direction
        );
    });
}

export function workspacePortfolioTotals(
    workspaces: readonly WorkspacePortfolioItem[],
): { workspaces: number; members: number; projects: number; tasks: number } {
    return workspaces.reduce(
        (totals, workspace) => ({
            workspaces: totals.workspaces + 1,
            members: totals.members + (workspace.members_count ?? 0),
            projects: totals.projects + (workspace.projects_count ?? 0),
            tasks: totals.tasks + (workspace.todos_count ?? 0),
        }),
        { workspaces: 0, members: 0, projects: 0, tasks: 0 },
    );
}

export function isWorkspaceTaxonomySection(
    value: unknown,
): value is WorkspaceTaxonomySection {
    return (
        typeof value === 'string' &&
        taxonomySections.includes(value as WorkspaceTaxonomySection)
    );
}

export function workspaceTaxonomyCounts(
    collections: WorkspaceTaxonomyCollections,
): Record<WorkspaceTaxonomySection, number> {
    return {
        statuses: collections.statuses.length,
        priorities: collections.priorities.length,
        labels: collections.labels.length,
        tags: collections.tags.length,
    };
}
