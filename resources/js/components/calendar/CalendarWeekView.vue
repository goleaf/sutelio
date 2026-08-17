<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarCheck2, CheckCircle2 } from '@lucide/vue';
import { computed } from 'vue';
import { buildCalendarDays } from '@/components/calendar/calendar-date';
import type {
    CalendarState,
    CalendarTodo,
} from '@/components/calendar/calendar-types';
import ColorSwatch from '@/components/shared/ColorSwatch.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { show as todoShow } from '@/routes/todos';

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
                    <Link
                        :href="todoShow(todo)"
                        prefetch
                        class="group flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-border/80 bg-card px-2.5 py-2 transition-colors hover:border-orange-500/30 hover:bg-orange-500/[0.035] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none"
                        :aria-label="todo.title"
                    >
                        <ColorSwatch :color="todo.priority_definition?.color" />
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-xs leading-5 font-semibold"
                            >
                                {{ todo.title }}
                            </span>
                            <span
                                v-if="todo.project"
                                class="block truncate text-[0.68rem] text-muted-foreground"
                            >
                                {{ todo.project.name }}
                            </span>
                        </span>
                        <CheckCircle2
                            v-if="todo.is_completed"
                            class="size-3.5 shrink-0 text-emerald-600"
                            aria-hidden="true"
                        />
                    </Link>
                </li>
            </ul>

            <div
                v-else
                class="mt-4 flex min-h-28 flex-col items-center justify-center rounded-xl border border-dashed border-border/80 px-3 text-center"
            >
                <CalendarCheck2
                    class="size-5 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-2 text-xs leading-5 text-muted-foreground">
                    {{ copy.calendar.no_tasks }}
                </p>
            </div>
        </section>
    </div>
</template>
