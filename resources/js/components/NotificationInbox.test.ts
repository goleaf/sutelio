import assert from 'node:assert/strict';
import test from 'node:test';
import {
    buildNotificationQuery,
    groupNotifications,
    notificationContent,
    notificationPluralForm,
    notificationPresentation,
} from './notification/notification-inbox.ts';
import type {
    NotificationFilters,
    NotificationItem,
} from './notification/notification-inbox.ts';

const baseNotification: NotificationItem = {
    id: '0198db17-d802-7123-97bc-b0d39c53fca0',
    kind: 'general',
    title: 'Workspace update',
    body: 'Something changed.',
    task_title: null,
    browser_delivery: false,
    is_read: false,
    read_at: null,
    created_at: '2026-08-16T12:00:00.000000Z',
    created_date: '2026-08-16',
    url: null,
};

test('notification filters omit defaults and serialize non-defaults deterministically', () => {
    const defaults: NotificationFilters = {
        status: 'all',
        kind: 'all',
        per_page: 20,
    };

    assert.deepEqual(buildNotificationQuery(defaults), {});
    assert.deepEqual(
        buildNotificationQuery({
            status: 'unread',
            kind: 'reminders',
            per_page: 50,
        }),
        { status: 'unread', kind: 'reminders', per_page: 50 },
    );
});

test('notifications group into today and earlier without changing server order', () => {
    const earlier = {
        ...baseNotification,
        id: '0198db17-d802-7123-97bc-b0d39c53fca1',
        created_date: '2026-08-15',
    };
    const groups = groupNotifications(
        [baseNotification, earlier],
        '2026-08-16',
    );

    assert.deepEqual(
        groups.map((group) => group.key),
        ['today', 'earlier'],
    );
    assert.deepEqual(
        groups.flatMap((group) => group.items.map(({ id }) => id)),
        [baseNotification.id, earlier.id],
    );
});

test('semantic presentation and count plurals have stable fallbacks', () => {
    assert.equal(notificationPresentation('reminder').icon, 'clock');
    assert.equal(notificationPresentation('completion').tone, 'emerald');
    assert.deepEqual(notificationPresentation('unknown'), {
        icon: 'bell',
        tone: 'blue',
    });
    assert.equal(notificationPluralForm(1, 'en-US'), 'one');
    assert.equal(notificationPluralForm(2, 'lt-LT'), 'few');
    assert.equal(notificationPluralForm(10, 'lt-LT'), 'other');
    assert.equal(notificationPluralForm(21, 'ru-RU'), 'one');
    assert.equal(notificationPluralForm(25, 'ru-RU'), 'many');
});

test('browser and row presentation share useful reminder and fallback copy', () => {
    const presentationCopy = {
        reminderTitle: 'Task reminder',
        reminderBody: 'Review ":task".',
        fallbackTitle: 'Workspace notification',
        fallbackBody: 'There is an update in your workspace.',
    };

    assert.deepEqual(
        notificationContent(
            {
                ...baseNotification,
                kind: 'reminder',
                title: null,
                body: null,
                task_title: 'Launch report',
            },
            presentationCopy,
        ),
        {
            title: 'Task reminder',
            body: 'Review "Launch report".',
        },
    );
    assert.deepEqual(
        notificationContent(
            { ...baseNotification, title: null, body: null },
            presentationCopy,
        ),
        {
            title: 'Workspace notification',
            body: 'There is an update in your workspace.',
        },
    );
});
