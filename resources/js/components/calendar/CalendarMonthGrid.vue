<script setup lang="ts">
import { CalendarCheck2 } from '@lucide/vue';
import { computed } from 'vue';
import { buildCalendarDays } from '@/components/calendar/calendar-date';
import type {
    CalendarState,
    CalendarTodo,
} from '@/components/calendar/calendar-types';
import CalendarTaskItem from '@/components/calendar/CalendarTaskItem.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

const props = defineProps<{
    calendar: CalendarState;
    todos: CalendarTodo[];
}>();

const { copy, formatDate, formatNumber } = useWorkspaceUi();
const weekdays = computed(() => {
    const labels = [...copy.value.calendar.weekdays];
    const sunday = labels[0];

    return props.calendar.week_start === 'monday' && sunday !== undefined
        ? [...labels.slice(1), sunday]
        : labels;
});
const days = computed(() =>
    buildCalendarDays(
        props.calendar.start_date,
        props.calendar.end_date,
        props.calendar.anchor_date,
        props.todos,
    ),
);
const mobileDays = computed(() =>
    days.value.filter(
        (day) =>
            day.todos.length > 0 || day.dateKey === props.calendar.today_date,
    ),
);

function taskCountLabel(date: Date, count: number): string {
    return copy.value.calendar.tasks_on_date
        .replace(':count', formatNumber(count))
        .replace(
            ':date',
            formatDate(date, {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
            }),
        );
}

function priorityLabel(todo: CalendarTodo): string {
    return todo.priority_definition?.name ?? todo.priority;
}
</script>

<template>
    <div>
        <div class="space-y-3 md:hidden">
            <section
                v-for="day in mobileDays"
                :key="day.dateKey"
                class="overflow-hidden rounded-2xl border border-border/80 bg-background"
                :aria-label="taskCountLabel(day.date, day.todos.length)"
            >
                <header
                    class="flex items-center justify-between gap-3 border-b border-border/70 bg-muted/30 px-4 py-3"
                >
                    <div class="flex items-center gap-3">
                        <span
                            :class="[
                                'flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-semibold tabular-nums',
                                day.dateKey === calendar.today_date
                                    ? 'bg-orange-600 text-white shadow-sm'
                                    : 'bg-card text-foreground shadow-sm ring-1 ring-border',
                            ]"
                        >
                            {{ day.date.getDate() }}
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold capitalize">
                                {{
                                    formatDate(day.date, {
                                        weekday: 'long',
                                        month: 'long',
                                        day: 'numeric',
                                    })
                                }}
                            </h3>
                            <p
                                v-if="!day.isCurrentMonth"
                                class="mt-0.5 text-xs text-muted-foreground"
                            >
                                {{ copy.calendar.outside_month }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground tabular-nums"
                    >
                        {{ formatNumber(day.todos.length) }}
                    </span>
                </header>

                <ul v-if="day.todos.length" class="grid gap-2 p-3">
                    <li v-for="todo in day.todos" :key="todo.id">
                        <CalendarTaskItem
                            :todo="todo"
                            :secondary="
                                todo.project?.name ?? priorityLabel(todo)
                            "
                        />
                    </li>
                </ul>

                <p v-else class="px-4 py-5 text-sm text-muted-foreground">
                    {{ copy.calendar.no_tasks }}
                </p>
            </section>

            <div
                v-if="mobileDays.length === 0"
                class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-dashed border-border px-6 text-center"
            >
                <CalendarCheck2
                    class="size-8 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-4 text-sm text-muted-foreground">
                    {{ copy.calendar.no_tasks }}
                </p>
            </div>
        </div>

        <div
            class="hidden overflow-hidden rounded-2xl border border-border/80 md:block"
            role="grid"
            :aria-label="copy.calendar.planning_period"
        >
            <div
                class="grid grid-cols-7 border-b border-border/80 bg-muted/45"
                role="row"
            >
                <div
                    v-for="weekday in weekdays"
                    :key="weekday"
                    class="px-1 py-3 text-center text-[0.68rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                    role="columnheader"
                >
                    {{ weekday }}
                </div>
            </div>

            <div class="hidden grid-cols-7 gap-px bg-border/80 md:grid">
                <section
                    v-for="day in days"
                    :key="day.dateKey"
                    :class="[
                        'min-h-32 min-w-0 bg-card p-2 lg:min-h-36',
                        day.isCurrentMonth
                            ? ''
                            : 'bg-muted/35 text-muted-foreground',
                    ]"
                    role="gridcell"
                    :aria-label="taskCountLabel(day.date, day.todos.length)"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span
                            :class="[
                                'flex size-8 items-center justify-center rounded-full text-xs font-semibold tabular-nums',
                                day.dateKey === calendar.today_date
                                    ? 'bg-orange-600 text-white shadow-sm'
                                    : '',
                            ]"
                            :aria-current="
                                day.dateKey === calendar.today_date
                                    ? 'date'
                                    : undefined
                            "
                        >
                            {{ day.date.getDate() }}
                        </span>
                        <span
                            v-if="day.todos.length"
                            class="text-[0.65rem] font-semibold text-muted-foreground tabular-nums"
                        >
                            {{ formatNumber(day.todos.length) }}
                        </span>
                    </div>

                    <ul class="mt-2 grid gap-1.5">
                        <li
                            v-for="todo in day.todos.slice(0, 2)"
                            :key="todo.id"
                            class="min-w-0"
                        >
                            <CalendarTaskItem
                                :todo="todo"
                                density="dense"
                                class="border-border/70 bg-background"
                            />
                        </li>
                    </ul>

                    <p
                        v-if="day.todos.length > 2"
                        class="mt-1.5 px-1 text-[0.68rem] font-medium text-muted-foreground"
                    >
                        +{{ formatNumber(day.todos.length - 2) }}
                        {{ copy.calendar.more }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
