import type { TodoFilters } from '@/types/api';

export type TaskFocusFilter =
    'completed_today' | 'is_favorite' | 'is_pinned' | 'overdue';

export interface TaskFocusTarget {
    focus: () => void;
    isConnected: boolean;
}

const narrowingFilterKeys = [
    'assigned_to',
    'completed_today',
    'due_date_from',
    'due_date_to',
    'is_favorite',
    'is_pinned',
    'label_id',
    'overdue',
    'priority',
    'project_id',
    'search',
    'status',
    'tag_id',
] as const satisfies readonly (keyof TodoFilters)[];

export function activeTaskFilterCount(filters: TodoFilters): number {
    const narrowed = narrowingFilterKeys.filter((key) => {
        const value = filters[key];

        return value !== undefined && value !== false && value !== '';
    }).length;

    return narrowed + (filters.sort ? 1 : 0);
}

export function toggleTaskFocusFilter(
    filters: TodoFilters,
    key: TaskFocusFilter,
): TodoFilters {
    const nextFilters = { ...filters };

    if (nextFilters[key]) {
        delete nextFilters[key];
    } else {
        nextFilters[key] = true;
    }

    return nextFilters;
}

export function clearTaskFilters(filters: TodoFilters): TodoFilters {
    return {
        direction: 'asc',
        per_page: 50,
        view: filters.view ?? 'list',
    };
}

export function restoreTaskFocus(
    origin: TaskFocusTarget | null,
    fallback: TaskFocusTarget | null,
): void {
    const target = origin?.isConnected
        ? origin
        : fallback?.isConnected
          ? fallback
          : null;

    target?.focus();
}
