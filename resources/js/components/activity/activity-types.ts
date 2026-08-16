import type { ActivityLog } from '@/types/models';

export type ActivityCategory =
    | 'all'
    | 'automation'
    | 'changes'
    | 'completion'
    | 'creation'
    | 'organization';

export type ActivityPeriod = 'all' | '7d' | '30d' | '90d';

export interface ActivityFilters {
    category: ActivityCategory;
    actor: string | null;
    period: ActivityPeriod;
}

export interface ActivityContributor {
    id: string;
    name: string;
}

export interface ActivityMetrics {
    recorded_actions: number;
    contributors: number;
    recent_changes: number;
}

export interface ActivityPaginator {
    data: ActivityLog[];
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

export type ActivityPluralForm = 'few' | 'many' | 'one' | 'other';

export function activityPluralForm(
    count: number,
    locale: string,
): ActivityPluralForm {
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

export function buildActivityQuery(
    filters: ActivityFilters,
): Record<string, string> {
    const query: Record<string, string> = {};

    if (filters.category !== 'all') {
        query.category = filters.category;
    }

    if (filters.actor !== null) {
        query.actor = filters.actor;
    }

    if (filters.period !== 'all') {
        query.period = filters.period;
    }

    return query;
}

export function hasActivityFilters(filters: ActivityFilters): boolean {
    return Object.keys(buildActivityQuery(filters)).length > 0;
}
