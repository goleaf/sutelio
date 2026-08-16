<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, CalendarDays, Clock3 } from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    groupTodosByDate,
    parseDateKey,
} from '@/components/calendar/calendar-date';
import type {
    CalendarState,
    CalendarTodo,
    CalendarView,
} from '@/components/calendar/calendar-types';
import CalendarAgendaView from '@/components/calendar/CalendarAgendaView.vue';
import CalendarAttentionRail from '@/components/calendar/CalendarAttentionRail.vue';
import CalendarMonthGrid from '@/components/calendar/CalendarMonthGrid.vue';
import CalendarPeriodNavigator from '@/components/calendar/CalendarPeriodNavigator.vue';
import CalendarWeekView from '@/components/calendar/CalendarWeekView.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { calendar as calendarRoute } from '@/routes';

const props = defineProps<{
    calendar: CalendarState;
    todos: CalendarTodo[];
    overdueTodos: CalendarTodo[];
    overdueCount: number;
}>();

const { copy, formatDate, formatNumber } = useWorkspaceUi();
const isNavigating = ref(false);
const todosByDate = computed(() => groupTodosByDate(props.todos));
const todayCount = computed(
    () => todosByDate.value.get(props.calendar.today_date)?.length ?? 0,
);
const periodLabel = computed(() => {
    if (props.calendar.view === 'month') {
        return formatDate(parseDateKey(props.calendar.anchor_date), {
            month: 'long',
            year: 'numeric',
        });
    }

    return `${formatDate(parseDateKey(props.calendar.start_date), {
        month: 'short',
        day: 'numeric',
    })} — ${formatDate(parseDateKey(props.calendar.end_date), {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })}`;
});

function navigate(view: CalendarView, anchorDate: string): void {
    router.visit(
        calendarRoute({
            query: {
                view,
                date: anchorDate,
            },
        }),
        {
            preserveScroll: true,
            replace: true,
            onStart: () => {
                isNavigating.value = true;
            },
            onFinish: () => {
                isNavigating.value = false;
            },
        },
    );
}
</script>

<template>
    <div>
        <Head :title="copy.calendar.title" />

        <div class="min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8">
            <div class="mx-auto flex max-w-app flex-col gap-6">
                <WorkspacePageHeader
                    :eyebrow="copy.common.workspace_intelligence"
                    :title="copy.calendar.title"
                    :description="copy.calendar.description"
                >
                    <template #metrics>
                        <WorkspaceMetric
                            :label="copy.calendar.visible_tasks"
                            :value="formatNumber(todos.length)"
                            :icon="CalendarDays"
                            tone="orange"
                        />
                        <WorkspaceMetric
                            :label="copy.calendar.due_today"
                            :value="formatNumber(todayCount)"
                            :icon="Clock3"
                            tone="blue"
                        />
                        <WorkspaceMetric
                            :label="copy.calendar.overdue"
                            :value="formatNumber(overdueCount)"
                            :icon="AlertCircle"
                            tone="slate"
                        />
                    </template>
                </WorkspacePageHeader>

                <section
                    class="rounded-panel border border-border/80 bg-card p-3 shadow-panel sm:p-4"
                >
                    <CalendarPeriodNavigator
                        :calendar="calendar"
                        :period-label="periodLabel"
                        :processing="isNavigating"
                        @navigate="navigate"
                    />

                    <div
                        class="grid min-w-0 gap-4 pt-4 xl:grid-cols-[minmax(0,1fr)_19rem] xl:gap-5"
                    >
                        <div class="min-w-0">
                            <CalendarMonthGrid
                                v-if="calendar.view === 'month'"
                                :calendar="calendar"
                                :todos="todos"
                            />
                            <CalendarWeekView
                                v-else-if="calendar.view === 'week'"
                                :calendar="calendar"
                                :todos="todos"
                            />
                            <CalendarAgendaView v-else :todos="todos" />
                        </div>

                        <CalendarAttentionRail
                            :todos="overdueTodos"
                            :count="overdueCount"
                        />
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
