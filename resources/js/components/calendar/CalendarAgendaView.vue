<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarCheck2, CheckCircle2 } from '@lucide/vue';
import { computed } from 'vue';
import {
    groupTodosByDate,
    parseDateKey,
} from '@/components/calendar/calendar-date';
import type { CalendarTodo } from '@/components/calendar/calendar-types';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { show as todoShow } from '@/routes/todos';

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
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        {{ formatDate(group.date, { weekday: 'long' }) }}
                    </p>
                    <h3 class="mt-1 text-sm font-semibold capitalize">
                        {{
                            formatDate(group.date, {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                            })
                        }}
                    </h3>
                </div>
                <span
                    class="rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground tabular-nums md:mt-3 md:inline-flex"
                >
                    {{ formatNumber(group.todos.length) }}
                </span>
            </header>

            <ul class="grid gap-2">
                <li v-for="todo in group.todos" :key="todo.id">
                    <Link
                        :href="todoShow(todo)"
                        prefetch
                        class="group flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-border/80 bg-card px-3 py-2.5 transition-colors hover:border-orange-500/30 hover:bg-orange-500/[0.035] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none"
                        :aria-label="todo.title"
                    >
                        <span
                            class="size-2.5 shrink-0 rounded-full ring-4 ring-muted"
                            :style="{
                                backgroundColor: safeDefinitionColor(
                                    todo.priority_definition?.color,
                                ),
                            }"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold">
                                {{ todo.title }}
                            </span>
                            <span
                                class="mt-0.5 block truncate text-xs text-muted-foreground"
                            >
                                {{
                                    todo.project?.name ??
                                    todo.priority_definition?.name ??
                                    todo.priority
                                }}
                            </span>
                        </span>
                        <CheckCircle2
                            v-if="todo.is_completed"
                            class="size-4 shrink-0 text-emerald-600"
                            aria-hidden="true"
                        />
                    </Link>
                </li>
            </ul>
        </section>

        <div
            v-if="groups.length === 0"
            class="flex min-h-72 flex-col items-center justify-center rounded-2xl border border-dashed border-border px-6 text-center"
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
</template>
