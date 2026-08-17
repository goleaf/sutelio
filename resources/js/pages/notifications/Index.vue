<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Bell, BellRing, CheckCheck, Inbox, MailOpen } from '@lucide/vue';
import { nextTick, ref, watch } from 'vue';
import {
    buildNotificationQuery,
    notificationContent,
    notificationPluralForm,
} from '@/components/notification/notification-inbox';
import type {
    NotificationFilters as NotificationFilterState,
    NotificationItem,
    NotificationPaginator,
    NotificationStats,
} from '@/components/notification/notification-inbox';
import NotificationFeed from '@/components/notification/NotificationFeed.vue';
import NotificationFilters from '@/components/notification/NotificationFilters.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import {
    index as notificationsIndex,
    markAllRead as markAllReadRoute,
    markRead as markReadRoute,
} from '@/routes/notifications';

const props = defineProps<{
    notifications: NotificationPaginator;
    stats: NotificationStats;
    filters: NotificationFilterState;
    today: string;
}>();

const toast = useToast();
const { copy, formatNumber, locale } = useWorkspaceUi();
const processingIds = ref<Set<string>>(new Set());
const markingAll = ref(false);
const markingAllSucceeded = ref(false);
const filtering = ref(false);
const filterRequest = ref(0);
const visibleNotifications = ref<NotificationItem[]>([
    ...props.notifications.data,
]);

watch(
    () => props.notifications.data,
    (notifications) => {
        visibleNotifications.value = [...notifications];
    },
);

function resultSummary(): string {
    const count = props.notifications.meta.total;
    const plural = notificationPluralForm(count, locale.value);
    const template = (() => {
        switch (plural) {
            case 'one':
                return copy.value.notifications.result_summary_one;
            case 'few':
                return copy.value.notifications.result_summary_few;
            case 'many':
                return copy.value.notifications.result_summary_many;
            default:
                return copy.value.notifications.result_summary_other;
        }
    })();

    return template.replace(
        ':count',
        formatNumber(count, { useGrouping: true }),
    );
}

function filterUrl(filters: NotificationFilterState): string {
    return notificationsIndex.url({ query: buildNotificationQuery(filters) });
}

function visitResults(url: string): void {
    const request = filterRequest.value + 1;
    filterRequest.value = request;
    router.cancelAll();
    router.visit(url, {
        only: ['notifications', 'stats', 'filters', 'today'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onStart: () => {
            filtering.value = true;
        },
        onFinish: () => {
            if (filterRequest.value === request) {
                filtering.value = false;
            }
        },
    });
}

function updateFilters(filters: NotificationFilterState): void {
    const nextUrl = filterUrl(filters);

    if (nextUrl !== filterUrl(props.filters)) {
        visitResults(nextUrl);
    }
}

function focusAfterRemoval(preferredId: string | null): void {
    void nextTick(() => {
        const preferred = preferredId
            ? document.querySelector<HTMLElement>(
                  `[data-notification-id="${preferredId}"]`,
              )
            : null;
        const fallback = document.querySelector<HTMLElement>(
            '[data-notification-feed-heading]',
        );

        (preferred?.isConnected ? preferred : fallback)?.focus();
    });
}

function markRead(
    notification: NotificationItem,
    destination: string | null = null,
): void {
    if (processingIds.value.has(notification.id)) {
        return;
    }

    const currentIndex = visibleNotifications.value.findIndex(
        ({ id }) => id === notification.id,
    );
    const preferredFocusId =
        visibleNotifications.value[currentIndex + 1]?.id ??
        visibleNotifications.value[currentIndex - 1]?.id ??
        null;

    processingIds.value = new Set([...processingIds.value, notification.id]);

    router.post(
        markReadRoute({ id: notification.id }).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['notifications', 'stats', 'filters', 'today'],
            onSuccess: () => {
                if (destination) {
                    router.visit(destination);

                    return;
                }

                if (props.filters.status === 'unread') {
                    visibleNotifications.value =
                        visibleNotifications.value.filter(
                            ({ id }) => id !== notification.id,
                        );
                    focusAfterRemoval(preferredFocusId);
                }
            },
            onError: () =>
                toast.error(copy.value.notifications.mark_read_failed),
            onFinish: () => {
                const next = new Set(processingIds.value);
                next.delete(notification.id);
                processingIds.value = next;
            },
        },
    );
}

function openNotification(notification: NotificationItem): void {
    if (!notification.url) {
        if (!notification.is_read) {
            markRead(notification);
        }

        return;
    }

    if (notification.is_read) {
        router.visit(notification.url);

        return;
    }

    markRead(notification, notification.url);
}

function markAllRead(): void {
    if (markingAll.value || props.stats.unread === 0) {
        return;
    }

    markingAll.value = true;
    markingAllSucceeded.value = false;
    router.post(
        markAllReadRoute().url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['notifications', 'stats', 'filters', 'today'],
            onSuccess: () => {
                markingAllSucceeded.value = true;
                toast.success(copy.value.notifications.marked_all);

                if (props.filters.status === 'unread') {
                    visibleNotifications.value = [];
                    focusAfterRemoval(null);
                }
            },
            onError: () =>
                toast.error(copy.value.notifications.mark_all_failed),
            onFinish: () => {
                markingAll.value = false;
            },
        },
    );
}

function showBrowserNotifications(notifications: NotificationItem[]): void {
    if (
        typeof window === 'undefined' ||
        !('Notification' in window) ||
        window.Notification.permission !== 'granted'
    ) {
        return;
    }

    notifications.forEach((notification) => {
        if (notification.is_read || !notification.browser_delivery) {
            return;
        }

        const storageKey = `sutelio:browser-reminder:${notification.id}`;

        if (window.localStorage.getItem(storageKey)) {
            return;
        }

        const content = notificationContent(notification, {
            reminderTitle: copy.value.notifications.reminder_title,
            reminderBody: copy.value.notifications.reminder_body,
            fallbackTitle: copy.value.notifications.fallback_title,
            fallbackBody: copy.value.notifications.fallback_body,
        });
        const browserNotification = new window.Notification(content.title, {
            body: content.body,
            tag: notification.id,
        });
        window.localStorage.setItem(storageKey, 'shown');
        browserNotification.onclick = () => {
            window.focus();
            openNotification(notification);
            browserNotification.close();
        };
    });
}

watch(() => props.notifications.data, showBrowserNotifications, {
    immediate: true,
});
</script>

<template>
    <Head :title="copy.notifications.title" />

    <WorkspacePageFrame>
        <WorkspacePageHeader
            :eyebrow="copy.common.workspace_intelligence"
            :title="copy.notifications.title"
            :description="copy.notifications.description"
        >
            <template #icon>
                <BellRing aria-hidden="true" />
            </template>

            <template #actions>
                <Button
                    type="button"
                    size="lg"
                    class="motion-reduce:transition-none"
                    :disabled="markingAll || stats.unread === 0"
                    @click="markAllRead"
                >
                    <Spinner v-if="markingAll" />
                    <CheckCheck
                        v-else
                        :class="[
                            'size-4',
                            markingAllSucceeded ? 'ui-status-pop' : '',
                        ]"
                        aria-hidden="true"
                    />
                    {{ copy.notifications.mark_all }}
                </Button>
            </template>

            <template #metrics>
                <WorkspaceMetric
                    :label="copy.notifications.total"
                    :value="formatNumber(stats.total)"
                    :icon="Inbox"
                    tone="orange"
                />
                <WorkspaceMetric
                    :label="copy.notifications.unread"
                    :value="formatNumber(stats.unread)"
                    :icon="Bell"
                    tone="blue"
                />
                <WorkspaceMetric
                    :label="copy.notifications.cleared"
                    :value="formatNumber(stats.read)"
                    :icon="MailOpen"
                    tone="emerald"
                />
            </template>
        </WorkspacePageHeader>

        <NotificationFilters
            :filters="filters"
            :stats="stats"
            :processing="filtering"
            :result-summary="resultSummary()"
            @update="updateFilters"
        />

        <NotificationFeed
            :filters="filters"
            :items="visibleNotifications"
            :notifications="notifications"
            :processing-ids="processingIds"
            :filtering="filtering"
            :today-date="today"
            @navigate="visitResults"
            @open="openNotification"
            @mark-read="markRead"
        />
    </WorkspacePageFrame>
</template>
