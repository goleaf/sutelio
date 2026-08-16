export type NotificationKind =
    'comment' | 'completion' | 'general' | 'overdue' | 'reminder';

export type NotificationStatusFilter = 'all' | 'read' | 'unread';
export type NotificationKindFilter = 'all' | 'reminders' | 'updates';

export interface NotificationFilters {
    status: NotificationStatusFilter;
    kind: NotificationKindFilter;
    per_page: 20 | 50;
}

export interface NotificationItem {
    id: string;
    kind: NotificationKind;
    title: string | null;
    body: string | null;
    task_title: string | null;
    browser_delivery: boolean;
    is_read: boolean;
    read_at: string | null;
    created_at: string;
    created_date: string;
    url: string | null;
}

export interface NotificationStats {
    total: number;
    unread: number;
    read: number;
}

export interface NotificationPaginator {
    data: NotificationItem[];
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

export interface NotificationGroup {
    key: 'earlier' | 'today';
    items: NotificationItem[];
}

export type NotificationPluralForm = 'few' | 'many' | 'one' | 'other';
export type NotificationIcon = 'alert' | 'bell' | 'check' | 'clock' | 'message';
export type NotificationTone = 'blue' | 'emerald' | 'orange' | 'red';

export function buildNotificationQuery(
    filters: NotificationFilters,
): Record<string, number | string> {
    const query: Record<string, number | string> = {};

    if (filters.status !== 'all') {
        query.status = filters.status;
    }

    if (filters.kind !== 'all') {
        query.kind = filters.kind;
    }

    if (filters.per_page !== 20) {
        query.per_page = filters.per_page;
    }

    return query;
}

export function hasNotificationFilters(filters: NotificationFilters): boolean {
    return Object.keys(buildNotificationQuery(filters)).length > 0;
}

export function groupNotifications(
    notifications: NotificationItem[],
    todayDate: string,
): NotificationGroup[] {
    const today: NotificationItem[] = [];
    const earlier: NotificationItem[] = [];

    notifications.forEach((notification) => {
        (notification.created_date === todayDate ? today : earlier).push(
            notification,
        );
    });

    return [
        ...(today.length ? [{ key: 'today' as const, items: today }] : []),
        ...(earlier.length
            ? [{ key: 'earlier' as const, items: earlier }]
            : []),
    ];
}

export function currentDateKey(timezone: string, date = new Date()): string {
    const parts = new Intl.DateTimeFormat('en-US', {
        day: '2-digit',
        month: '2-digit',
        timeZone: timezone,
        year: 'numeric',
    }).formatToParts(date);
    const value = (type: Intl.DateTimeFormatPartTypes): string =>
        parts.find((part) => part.type === type)?.value ?? '';

    return `${value('year')}-${value('month')}-${value('day')}`;
}

export function notificationPluralForm(
    count: number,
    locale: string,
): NotificationPluralForm {
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

export function notificationPresentation(kind: string): {
    icon: NotificationIcon;
    tone: NotificationTone;
} {
    switch (kind) {
        case 'reminder':
            return { icon: 'clock', tone: 'orange' };
        case 'comment':
            return { icon: 'message', tone: 'blue' };
        case 'completion':
            return { icon: 'check', tone: 'emerald' };
        case 'overdue':
            return { icon: 'alert', tone: 'red' };
        default:
            return { icon: 'bell', tone: 'blue' };
    }
}
