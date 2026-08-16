<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowUpRight, CalendarCheck2 } from '@lucide/vue';
import type { CalendarTodo } from '@/components/calendar/calendar-types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { index as todoIndex, show as todoShow } from '@/routes/todos';

defineProps<{
    todos: CalendarTodo[];
    count: number;
}>();

const { copy, formatDate, formatNumber } = useWorkspaceUi();
</script>

<template>
    <aside
        class="min-w-0 overflow-hidden rounded-2xl border border-orange-500/20 bg-orange-500/[0.035] p-4 xl:sticky xl:top-6 xl:self-start"
        :aria-labelledby="'calendar-attention-heading'"
    >
        <header class="flex items-start gap-3">
            <div
                class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-orange-500/12 text-orange-700 dark:text-orange-300"
            >
                <AlertTriangle class="size-4.5" aria-hidden="true" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="calendar-attention-heading" class="font-semibold">
                        {{ copy.calendar.attention }}
                    </h2>
                    <Badge variant="secondary" class="tabular-nums">
                        {{ formatNumber(count) }}
                    </Badge>
                </div>
                <p class="mt-1 text-xs leading-5 text-muted-foreground">
                    {{ copy.calendar.attention_description }}
                </p>
            </div>
        </header>

        <ul v-if="todos.length" class="mt-4 grid min-w-0 gap-2">
            <li v-for="todo in todos" :key="todo.id" class="min-w-0">
                <Link
                    :href="todoShow(todo)"
                    prefetch
                    class="group flex min-h-11 max-w-full min-w-0 cursor-pointer items-center gap-3 overflow-hidden rounded-xl border border-border/80 bg-card px-3 py-2.5 transition-colors hover:border-orange-500/35 hover:bg-orange-500/[0.04] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none"
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
                            v-if="todo.due_date"
                            class="mt-0.5 block text-xs text-muted-foreground"
                        >
                            {{
                                formatDate(
                                    new Date(`${todo.due_date}T12:00:00`),
                                    {
                                        month: 'short',
                                        day: 'numeric',
                                    },
                                )
                            }}
                        </span>
                    </span>
                    <ArrowUpRight
                        class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                        aria-hidden="true"
                    />
                </Link>
            </li>
        </ul>

        <div
            v-else
            class="mt-4 flex min-h-40 flex-col items-center justify-center rounded-xl border border-dashed border-border/80 bg-card/50 px-4 text-center"
        >
            <CalendarCheck2
                class="size-7 text-emerald-600 dark:text-emerald-400"
                aria-hidden="true"
            />
            <p class="mt-3 text-sm leading-5 text-muted-foreground">
                {{ copy.calendar.no_overdue }}
            </p>
        </div>

        <Button
            v-if="count > 0"
            as-child
            variant="outline"
            class="mt-4 min-h-11 w-full cursor-pointer rounded-xl bg-card focus-visible:ring-orange-500 motion-reduce:transition-none"
        >
            <Link :href="todoIndex({ query: { overdue: true } })" prefetch>
                {{ copy.calendar.view_all_overdue }}
                <ArrowUpRight class="size-4" aria-hidden="true" />
            </Link>
        </Button>
    </aside>
</template>
