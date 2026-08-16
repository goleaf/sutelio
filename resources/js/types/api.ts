export interface PaginatedResponse<T> {
    data: T[];
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

export interface ApiResponse<T> {
    data?: T;
    message?: string;
}

export interface CursorPaginatedResponse<T> {
    data: T[];
    links: {
        next: string | null;
        prev: string | null;
    };
    meta: {
        next_cursor: string | null;
        prev_cursor: string | null;
        per_page: number;
        request_id?: string;
    };
}

export interface TodoFilters extends Record<
    string,
    string | boolean | number | undefined
> {
    search?: string;
    project_id?: string;
    status?: string;
    priority?: string;
    assigned_to?: string;
    label_id?: string;
    tag_id?: string;
    is_pinned?: boolean;
    is_favorite?: boolean;
    overdue?: boolean;
    completed_today?: boolean;
    due_date_from?: string;
    due_date_to?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    per_page?: 25 | 50 | 100;
    view?: 'board' | 'list';
}
