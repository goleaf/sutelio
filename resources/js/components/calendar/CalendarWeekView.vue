<script setup lang="ts">
import { CalendarCheck2 } from '@lucide/vue';
import { computed } from 'vue';
import { buildCalendarDays } from '@/components/calendar/calendar-date';
import type {
    CalendarState,
    CalendarTodo,
} from '@/components/calendar/calendar-types';
import CalendarTaskItem from '@/components/calendar/CalendarTaskItem.vue';
import IconTile from '@/components/shared/IconTile.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

const props = defineProps<{
    calendar: CalendarState;
    todos: CalendarTodo[];
}>();

const { copy, formatDate, formatNumber } = useWorkspaceUi();
const days = computed(() =>
    buildCalendarDays(
        props.calendar.start_date,
        props.calendar.end_date,
        props.calendar.anchor_date,
        props.todos,
    ),
);
</script>

<template>
    <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-7">
        <section
            v-for="day in days"
            :key="day.dateKey"
            :class="[
                'min-w-0 rounded-2xl border p-3',
                day.dateKey === calendar.today_date
                    ? 'border-orange-500/40 bg-orange-500/[0.035]'
                    : 'border-border/80 bg-background',
            ]"
        >
            <header class="flex items-center justify-between gap-2">
                <div>
                    <p
                        class="text-[0.65rem] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        {{ formatDate(day.date, { weekday: 'short' }) }}
                    </p>
                    <h3 class="mt-1 text-xl font-semibold tabular-nums">
                        {{ day.date.getDate() }}
                    </h3>
                </div>
                <span
                    class="rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground tabular-nums"
                >
                    {{ formatNumber(day.todos.length) }}
                </span>
            </header>

            <ul v-if="day.todos.length" class="mt-4 grid gap-2">
                <li v-for="todo in day.todos" :key="todo.id" class="min-w-0">
                    <CalendarTaskItem
                        :todo="todo"
                        density="compact"
                        :secondary="todo.project?.name"
                    />
                </li>
            </ul>

            <div
                v-else
                class="mt-4 flex min-h-28 flex-col items-center justify-center rounded-xl border border-dashed border-border/80 px-3 text-center"
            >
                <IconTile tone="muted" size="sm">
                    <CalendarCheck2 />
                </IconTile>
                <p class="mt-2 text-xs leading-5 text-muted-foreground">
                    {{ copy.calendar.no_tasks }}
                </p>
            </div>
        </section>
    </div>
</template>
