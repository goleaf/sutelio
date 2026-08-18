<script setup lang="ts">
import { CalendarCheck2, Clock3 } from '@lucide/vue';
import { computed } from 'vue';
import {
    groupTodosByDate,
    parseDateKey,
} from '@/components/calendar/calendar-date';
import type { CalendarTodo } from '@/components/calendar/calendar-types';
import CalendarTaskItem from '@/components/calendar/CalendarTaskItem.vue';
import IconTile from '@/components/shared/IconTile.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

const props = defineProps<{ todos: CalendarTodo[] }>();
const { copy, formatDate, formatNumber } = useWorkspaceUi();
const groups = computed(() =>
    Array.from(groupTodosByDate(props.todos).entries()).map(
        ([dateKey, todos]) => ({
            dateKey,
            date: parseDateKey(dateKey),
            todos,
        }),
    ),
);
</script>

<template>
    <div class="space-y-4">
        <section
            v-for="group in groups"
            :key="group.dateKey"
            class="grid gap-3 rounded-2xl border border-border/80 bg-background p-3 md:grid-cols-[11rem_minmax(0,1fr)] md:p-4"
        >
            <header class="flex items-center justify-between gap-3 md:block">
                <div class="flex items-center gap-3 md:items-start">
                    <IconTile tone="muted" size="sm">
                        <Clock3 />
                    </IconTile>
                    <div>
                        <p
                            class="text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                        >
                            {{ formatDate(group.date, { weekday: 'long' }) }}
                        </p>
                        <h3 class="mt-1 text-base font-semibold capitalize">
                            {{
                                formatDate(group.date, {
                                    month: 'long',
                                    day: 'numeric',
                                    year: 'numeric',
                                })
                            }}
                        </h3>
                    </div>
                </div>
                <span
                    class="rounded-full bg-muted px-2.5 py-1 text-[0.9375rem] font-semibold text-muted-foreground tabular-nums md:mt-3 md:inline-flex"
                >
                    {{ formatNumber(group.todos.length) }}
                </span>
            </header>

            <ul class="grid gap-2">
                <li v-for="todo in group.todos" :key="todo.id">
                    <CalendarTaskItem
                        :todo="todo"
                        :secondary="
                            todo.project?.name ??
                            todo.priority_definition?.name ??
                            todo.priority
                        "
                    />
                </li>
            </ul>
        </section>

        <div
            v-if="groups.length === 0"
            class="flex min-h-72 flex-col items-center justify-center rounded-2xl border border-dashed border-border px-6 text-center"
        >
            <IconTile tone="muted" size="lg">
                <CalendarCheck2 />
            </IconTile>
            <p class="mt-4 text-base text-muted-foreground">
                {{ copy.calendar.no_tasks }}
            </p>
        </div>
    </div>
</template>
