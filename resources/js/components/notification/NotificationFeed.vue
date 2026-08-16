<script setup lang="ts">
import { BellOff, ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import NotificationRow from '@/components/notification/NotificationRow.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { groupNotifications } from './notification-inbox';
import type {
    NotificationFilters,
    NotificationItem,
    NotificationPaginator,
} from './notification-inbox';

const props = defineProps<{
    notifications: NotificationPaginator;
    items: NotificationItem[];
    filters: NotificationFilters;
    todayDate: string;
    processingIds: Set<string>;
    filtering: boolean;
}>();
const emit = defineEmits<{
    markRead: [notification: NotificationItem];
    open: [notification: NotificationItem];
    navigate: [url: string];
}>();
const { copy, formatNumber } = useWorkspaceUi();

const groups = computed(() => groupNotifications(props.items, props.todayDate));

const emptyCopy = computed(() => {
    if (props.filters.kind === 'reminders') {
        return {
            title: copy.value.notifications.empty_reminders_title,
            description: copy.value.notifications.empty_reminders_description,
        };
    }

    if (props.filters.kind === 'updates') {
        return {
            title: copy.value.notifications.empty_updates_title,
            description: copy.value.notifications.empty_updates_description,
        };
    }

    if (props.filters.status === 'unread') {
        return {
            title: copy.value.notifications.empty_unread_title,
            description: copy.value.notifications.empty_unread_description,
        };
    }

    if (props.filters.status === 'read') {
        return {
            title: copy.value.notifications.empty_read_title,
            description: copy.value.notifications.empty_read_description,
        };
    }

    return {
        title: copy.value.notifications.empty_title,
        description: copy.value.notifications.empty_description,
    };
});

function groupLabel(key: 'earlier' | 'today'): string {
    return key === 'today'
        ? copy.value.notifications.today_group
        : copy.value.notifications.earlier_group;
}
</script>

<template>
    <section
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        :aria-label="copy.notifications.feed_label"
        :aria-busy="filtering"
    >
        <h2
            id="notification-feed-heading"
            data-notification-feed-heading
            tabindex="-1"
            class="sr-only"
        >
            {{ copy.notifications.feed_label }}
        </h2>

        <div
            v-if="groups.length"
            class="space-y-7 p-3 transition-opacity duration-200 motion-reduce:transition-none sm:p-5"
            :class="filtering ? 'opacity-55' : 'opacity-100'"
        >
            <section v-for="group in groups" :key="group.key">
                <div class="flex items-center gap-3 px-2 py-2">
                    <h3
                        class="text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                    >
                        {{ groupLabel(group.key) }}
                    </h3>
                    <span class="h-px flex-1 bg-border/70" aria-hidden="true" />
                    <span class="text-xs text-muted-foreground tabular-nums">
                        {{ formatNumber(group.items.length) }}
                    </span>
                </div>

                <div class="grid gap-2" role="list">
                    <NotificationRow
                        v-for="notification in group.items"
                        :key="notification.id"
                        role="listitem"
                        :notification="notification"
                        :processing="processingIds.has(notification.id)"
                        @mark-read="emit('markRead', $event)"
                        @open="emit('open', $event)"
                    />
                </div>
            </section>
        </div>

        <EmptyState
            v-else
            :title="emptyCopy.title"
            :description="emptyCopy.description"
        >
            <template #icon>
                <BellOff class="size-7" aria-hidden="true" />
            </template>
        </EmptyState>

        <nav
            v-if="notifications.meta.last_page > 1"
            class="flex flex-col gap-3 border-t border-border/70 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
            :aria-label="copy.notifications.pagination_label"
        >
            <p class="text-sm text-muted-foreground">
                {{
                    copy.notifications.pagination_range
                        .replace(
                            ':from',
                            formatNumber(notifications.meta.from ?? 0),
                        )
                        .replace(
                            ':to',
                            formatNumber(notifications.meta.to ?? 0),
                        )
                        .replace(
                            ':total',
                            formatNumber(notifications.meta.total),
                        )
                }}
            </p>
            <div class="grid grid-cols-2 gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="min-h-11 rounded-xl"
                    :disabled="!notifications.links.prev || filtering"
                    @click="
                        notifications.links.prev &&
                        emit('navigate', notifications.links.prev)
                    "
                >
                    <ChevronLeft class="size-4" aria-hidden="true" />
                    {{ copy.notifications.previous }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="min-h-11 rounded-xl"
                    :disabled="!notifications.links.next || filtering"
                    @click="
                        notifications.links.next &&
                        emit('navigate', notifications.links.next)
                    "
                >
                    {{ copy.notifications.next }}
                    <ChevronRight class="size-4" aria-hidden="true" />
                </Button>
            </div>
        </nav>
    </section>
</template>
