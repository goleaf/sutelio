<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowUpRight, CalendarCheck2 } from '@lucide/vue';
import type { CalendarTodo } from '@/components/calendar/calendar-types';
import CalendarTaskItem from '@/components/calendar/CalendarTaskItem.vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { index as todoIndex } from '@/routes/todos';

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
        <header>
            <LeadingIconHeading tile tile-tone="warning" content-class="gap-0">
                <template #icon>
                    <AlertTriangle />
                </template>

                <div class="flex items-center justify-between gap-3">
                    <h2 id="calendar-attention-heading" class="font-semibold">
                        {{ copy.calendar.attention }}
                    </h2>
                    <Badge variant="secondary" class="tabular-nums">
                        {{ formatNumber(count) }}
                    </Badge>
                </div>
                <p
                    class="mt-1 text-[0.9375rem] leading-5 text-muted-foreground"
                >
                    {{ copy.calendar.attention_description }}
                </p>
            </LeadingIconHeading>
        </header>

        <ul v-if="todos.length" class="mt-4 grid min-w-0 gap-2">
            <li v-for="todo in todos" :key="todo.id" class="min-w-0">
                <CalendarTaskItem
                    :todo="todo"
                    :secondary="
                        todo.due_date
                            ? formatDate(
                                  new Date(`${todo.due_date}T12:00:00`),
                                  {
                                      month: 'short',
                                      day: 'numeric',
                                  },
                              )
                            : null
                    "
                    show-arrow
                    class="hover:border-orange-500/35 hover:bg-orange-500/[0.04]"
                />
            </li>
        </ul>

        <div
            v-else
            class="mt-4 flex min-h-40 flex-col items-center justify-center rounded-xl border border-dashed border-border/80 bg-card/50 px-4 text-center"
        >
            <IconTile tone="success" size="lg">
                <CalendarCheck2 />
            </IconTile>
            <p class="mt-3 text-base leading-6 text-muted-foreground">
                {{ copy.calendar.no_overdue }}
            </p>
        </div>

        <Button
            v-if="count > 0"
            as-child
            variant="outline"
            class="mt-4 min-h-12 w-full cursor-pointer rounded-xl bg-card focus-visible:ring-orange-500 motion-reduce:transition-none pointer-coarse:min-h-13"
        >
            <Link :href="todoIndex({ query: { overdue: true } })" prefetch>
                {{ copy.calendar.view_all_overdue }}
                <ArrowUpRight class="size-4" aria-hidden="true" />
            </Link>
        </Button>
    </aside>
</template>
