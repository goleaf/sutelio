<script setup lang="ts">
import { FilterX, Layers3, ListFilter } from '@lucide/vue';
import { computed } from 'vue';
import WorkspaceSegmentedButton from '@/components/shared/WorkspaceSegmentedButton.vue';
import WorkspaceSegmentedControl from '@/components/shared/WorkspaceSegmentedControl.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { hasNotificationFilters } from './notification-inbox';
import type {
    NotificationFilters,
    NotificationKindFilter,
    NotificationStats,
    NotificationStatusFilter,
} from './notification-inbox';

const props = defineProps<{
    filters: NotificationFilters;
    stats: NotificationStats;
    resultSummary: string;
    processing: boolean;
}>();
const emit = defineEmits<{ update: [filters: NotificationFilters] }>();
const { copy, formatNumber } = useWorkspaceUi();

const statusOptions = computed<
    Array<{ label: string; value: NotificationStatusFilter }>
>(() => [
    { value: 'all', label: copy.value.notifications.all_tab },
    { value: 'unread', label: copy.value.notifications.unread_tab },
    { value: 'read', label: copy.value.notifications.read_tab },
]);

const kindOptions = computed<
    Array<{ label: string; value: NotificationKindFilter }>
>(() => [
    { value: 'all', label: copy.value.notifications.all_kinds },
    { value: 'reminders', label: copy.value.notifications.reminders_kind },
    { value: 'updates', label: copy.value.notifications.updates_kind },
]);

const perPageModel = computed({
    get: () => String(props.filters.per_page),
    set: (value: string) => {
        updateFilters({ per_page: value === '50' ? 50 : 20 });
    },
});

function updateFilters(changes: Partial<NotificationFilters>): void {
    emit('update', { ...props.filters, ...changes });
}

function clearFilters(): void {
    emit('update', { status: 'all', kind: 'all', per_page: 20 });
}
</script>

<template>
    <section
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        :aria-label="copy.common.filters"
    >
        <div
            class="grid gap-5 border-b border-border/70 px-4 py-5 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end"
        >
            <div class="min-w-0 space-y-2">
                <p
                    class="flex items-center gap-2 text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                >
                    <ListFilter class="size-4" aria-hidden="true" />
                    {{ copy.notifications.status_label }}
                </p>
                <WorkspaceSegmentedControl
                    role="group"
                    :label="copy.notifications.status_label"
                    class="w-full"
                >
                    <WorkspaceSegmentedButton
                        v-for="option in statusOptions"
                        :key="option.value"
                        :active="filters.status === option.value"
                        :aria-pressed="filters.status === option.value"
                        :disabled="processing"
                        class="min-h-11 flex-1 justify-center px-3 motion-reduce:transition-none"
                        @click="updateFilters({ status: option.value })"
                    >
                        {{ option.label }}
                        <span
                            v-if="option.value === 'unread' && stats.unread > 0"
                            class="rounded-full bg-orange-500 px-1.5 py-0.5 text-[0.65rem] font-semibold text-white tabular-nums"
                        >
                            {{ formatNumber(stats.unread) }}
                        </span>
                    </WorkspaceSegmentedButton>
                </WorkspaceSegmentedControl>
            </div>

            <div class="min-w-0 space-y-2">
                <p
                    class="flex items-center gap-2 text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                >
                    <Layers3 class="size-4" aria-hidden="true" />
                    {{ copy.notifications.kind_label }}
                </p>
                <WorkspaceSegmentedControl
                    role="group"
                    :label="copy.notifications.kind_label"
                    class="w-full"
                >
                    <WorkspaceSegmentedButton
                        v-for="option in kindOptions"
                        :key="option.value"
                        :active="filters.kind === option.value"
                        :aria-pressed="filters.kind === option.value"
                        :disabled="processing"
                        class="min-h-11 flex-1 justify-center px-3 motion-reduce:transition-none"
                        @click="updateFilters({ kind: option.value })"
                    >
                        {{ option.label }}
                    </WorkspaceSegmentedButton>
                </WorkspaceSegmentedControl>
            </div>

            <div
                class="grid grid-cols-[minmax(8rem,1fr)_auto] gap-2 lg:grid-cols-1"
            >
                <Select v-model="perPageModel" :disabled="processing">
                    <SelectTrigger
                        class="min-h-11 w-full min-w-36"
                        :aria-label="copy.notifications.page_size_label"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="20">
                            {{
                                copy.notifications.page_size_option.replace(
                                    ':count',
                                    formatNumber(20),
                                )
                            }}
                        </SelectItem>
                        <SelectItem value="50">
                            {{
                                copy.notifications.page_size_option.replace(
                                    ':count',
                                    formatNumber(50),
                                )
                            }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="size-11 cursor-pointer rounded-xl motion-reduce:transition-none"
                    :aria-label="copy.notifications.clear_filters"
                    :disabled="processing || !hasNotificationFilters(filters)"
                    @click="clearFilters"
                >
                    <FilterX class="size-4" aria-hidden="true" />
                </Button>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-xs text-muted-foreground sm:px-6"
        >
            <p aria-live="polite" aria-atomic="true">
                {{
                    processing
                        ? copy.notifications.updating_results
                        : resultSummary
                }}
            </p>
            <p class="sr-only">
                {{
                    hasNotificationFilters(filters)
                        ? copy.notifications.active_filters_status
                        : copy.notifications.no_active_filters_status
                }}
            </p>
        </div>
    </section>
</template>
