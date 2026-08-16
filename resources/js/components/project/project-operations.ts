import type {
    Project,
    TaskPriorityDefinition,
    TaskStatusDefinition,
    User,
} from '@/types/models';

export type ProjectAttention = 'all' | 'due_soon' | 'overdue' | 'unassigned';
export type ProjectSort = 'due_date' | 'position' | 'priority' | 'updated';

export interface ProjectFilters {
    search: string | null;
    status: string | null;
    priority: string | null;
    assignee: string | null;
    attention: ProjectAttention;
    sort: ProjectSort;
}

export interface ProjectMetrics {
    total: number;
    open: number;
    completed: number;
    completion_rate: number;
    attention: number;
    overdue: number;
    due_soon: number;
    unassigned: number;
}

export type ProjectAssignee = Pick<User, 'id' | 'name'>;

export interface ProjectPriorityDistribution {
    id: string;
    key: string;
    name: string;
    color: string;
    count: number;
}

export interface ProjectTaskStatusDefinition extends Pick<
    TaskStatusDefinition,
    'color' | 'id' | 'is_completed' | 'key' | 'name' | 'position'
> {
    translation_key?: string | null;
}

export interface ProjectTaskPriorityDefinition extends Pick<
    TaskPriorityDefinition,
    'color' | 'id' | 'key' | 'name' | 'position'
> {
    translation_key?: string | null;
}

export interface ProjectTask {
    id: string;
    title: string;
    assigned_to: string | null;
    assignee: ProjectAssignee | null;
    status: string;
    status_id: string | null;
    status_definition: ProjectTaskStatusDefinition | null;
    priority: string;
    priority_id: string | null;
    priority_definition: ProjectTaskPriorityDefinition | null;
    labels: Array<{ id: string; name: string; color: string }>;
    is_completed: boolean;
    due_date: string | null;
    position: number;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface ProjectTaskPaginator {
    data: ProjectTask[];
    links: {
        first: string | null;
        last: string | null;
        next: string | null;
        prev: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
}

export interface ProjectAttentionTasks {
    data: ProjectTask[];
    total: number;
}

export interface ProjectFilterableTask {
    id: string;
    title: string;
    description?: string | null;
    status_id: string | null;
    priority_id: string | null;
    assigned_to: string | null;
    completed_at: string | null;
    due_date: string | null;
}

export interface ProjectSortableTask {
    id: string;
    due_date: string | null;
    position: number;
    updated_at: string;
    priority_definition?: { position: number } | null;
}

export interface ProjectOperationsProps {
    project: { data: Project };
    filters: ProjectFilters;
    metrics: ProjectMetrics;
    todos: ProjectTaskPaginator;
}

export type ProjectPluralForm = 'few' | 'many' | 'one' | 'other';

export function projectResultPluralForm(
    count: number,
    locale: string,
): ProjectPluralForm {
    const category = new Intl.PluralRules(locale).select(count);

    switch (category) {
        case 'few':
        case 'many':
        case 'one':
            return category;
        default:
            return 'other';
    }
}

export function buildProjectQuery(
    filters: ProjectFilters,
): Record<string, string> {
    const query: Record<string, string> = {};
    const search = filters.search?.trim() ?? '';

    if (search !== '') {
        query.search = search;
    }

    if (filters.status !== null) {
        query.status = filters.status;
    }

    if (filters.priority !== null) {
        query.priority = filters.priority;
    }

    if (filters.assignee !== null) {
        query.assignee = filters.assignee;
    }

    if (filters.attention !== 'all') {
        query.attention = filters.attention;
    }

    if (filters.sort !== 'position') {
        query.sort = filters.sort;
    }

    return query;
}

export function hasProjectFilters(filters: ProjectFilters): boolean {
    return Object.keys(buildProjectQuery(filters)).length > 0;
}

export function countProjectFilters(filters: ProjectFilters): number {
    return Object.keys(buildProjectQuery(filters)).length;
}

export function isProjectTaskOverdue(
    dueDate: string | null,
    isCompleted: boolean,
    today: string,
): boolean {
    return !isCompleted && dueDate !== null && dueDate < today;
}

export function projectTaskMatchesFilters(
    task: ProjectFilterableTask,
    filters: ProjectFilters,
    today: string,
): boolean {
    const search = filters.search?.trim() ?? '';
    const isOpen = task.completed_at === null;

    if (
        search !== '' &&
        !sqliteLikeContains(task.title, search) &&
        !sqliteLikeContains(task.description ?? '', search)
    ) {
        return false;
    }

    if (filters.status !== null && task.status_id !== filters.status) {
        return false;
    }

    if (filters.priority !== null && task.priority_id !== filters.priority) {
        return false;
    }

    if (filters.assignee !== null && task.assigned_to !== filters.assignee) {
        return false;
    }

    switch (filters.attention) {
        case 'overdue':
            return isOpen && task.due_date !== null && task.due_date < today;
        case 'due_soon':
            return (
                isOpen &&
                task.due_date !== null &&
                task.due_date >= today &&
                task.due_date <= addDays(today, 7)
            );
        case 'unassigned':
            return isOpen && task.assigned_to === null;
        default:
            return true;
    }
}

export function sortProjectTasks<T extends ProjectSortableTask>(
    tasks: T[],
    sort: ProjectSort,
): T[] {
    return [...tasks].sort((left, right) => {
        if (sort === 'due_date') {
            const dueDateComparison = compareNullableAscending(
                left.due_date,
                right.due_date,
            );

            if (dueDateComparison !== 0) {
                return dueDateComparison;
            }
        }

        if (sort === 'priority') {
            const priorityComparison =
                (left.priority_definition?.position ??
                    Number.MAX_SAFE_INTEGER) -
                (right.priority_definition?.position ??
                    Number.MAX_SAFE_INTEGER);

            if (priorityComparison !== 0) {
                return priorityComparison;
            }
        }

        if (sort === 'updated') {
            const updatedComparison = compareStrings(
                right.updated_at,
                left.updated_at,
            );

            return updatedComparison !== 0
                ? updatedComparison
                : compareStrings(right.id, left.id);
        }

        const positionComparison = left.position - right.position;

        return positionComparison !== 0
            ? positionComparison
            : compareStrings(left.id, right.id);
    });
}

function addDays(date: string, days: number): string {
    const value = new Date(`${date}T00:00:00Z`);
    value.setUTCDate(value.getUTCDate() + days);

    return value.toISOString().slice(0, 10);
}

function compareNullableAscending(
    left: string | null,
    right: string | null,
): number {
    if (left === null) {
        return right === null ? 0 : 1;
    }

    return right === null ? -1 : compareStrings(left, right);
}

function compareStrings(left: string, right: string): number {
    return left < right ? -1 : left > right ? 1 : 0;
}

function sqliteLikeContains(value: string, pattern: string): boolean {
    const expression = Array.from(pattern, (character) => {
        if (character === '%') {
            return '[\\s\\S]*';
        }

        if (character === '_') {
            return '[\\s\\S]';
        }

        if (/^[A-Za-z]$/.test(character)) {
            return `[${character.toLowerCase()}${character.toUpperCase()}]`;
        }

        return character.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }).join('');

    return new RegExp(expression).test(value);
}

export function projectAttentionContinuation(
    metrics: Pick<ProjectMetrics, 'overdue'>,
    tasks: Array<Pick<ProjectTask, 'due_date' | 'is_completed'>>,
    today: string,
): Extract<ProjectAttention, 'due_soon' | 'overdue'> {
    const displayedOverdue = tasks.filter((task) =>
        isProjectTaskOverdue(task.due_date, task.is_completed, today),
    ).length;

    return metrics.overdue > displayedOverdue ? 'overdue' : 'due_soon';
}
