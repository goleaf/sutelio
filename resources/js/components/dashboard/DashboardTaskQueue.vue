<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    CalendarCheck2,
    CalendarClock,
} from '@lucide/vue';
import { computed } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import { show as showTodo } from '@/routes/todos';
import type { Todo } from '@/types/models';

type QueueTone = 'overdue' | 'today' | 'upcoming';

const props = withDefaults(
    defineProps<{
        title: string;
        description: string;
        emptyMessage: string;
        todos: Todo[];
        count: number;
        tone: QueueTone;
        featured?: boolean;
    }>(),
    {
        featured: false,
    },
);

const { formatDate, formatNumber, t } = useUi();

const queueDesign = computed(() => {
    if (props.tone === 'overdue') {
        return {
            icon: AlertTriangle,
            tileTone: 'destructive' as const,
            badgeVariant: 'destructive' as const,
        };
    }

    if (props.tone === 'today') {
        return {
            icon: CalendarClock,
            tileTone: 'information' as const,
            badgeVariant: 'secondary' as const,
        };
    }

    return {
        icon: CalendarCheck2,
        tileTone: 'brand' as const,
        badgeVariant: 'outline' as const,
    };
});

function formattedDueDate(todo: Todo): string {
    if (!todo.due_date) {
        return t('common.states.not_set');
    }

    return formatDate(todo.due_date, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Card class="@container gap-0 overflow-hidden py-0">
        <header class="border-b border-border/70 px-5 py-5 sm:px-6">
            <LeadingIconHeading
                tile
                :tile-tone="queueDesign.tileTone"
                content-class="gap-0"
            >
                <template #icon>
                    <component :is="queueDesign.icon" />
                </template>

                <div class="flex items-center gap-2">
                    <h3 class="font-semibold tracking-tight">{{ title }}</h3>
                    <Badge :variant="queueDesign.badgeVariant">
                        {{ formatNumber(count) }}
                    </Badge>
                </div>
                <p class="mt-1 text-sm leading-5 text-muted-foreground">
                    {{ description }}
                </p>
            </LeadingIconHeading>
        </header>

        <div
            v-if="todos.length === 0"
            class="flex min-h-36 items-center gap-3 px-5 py-6 text-sm text-muted-foreground sm:px-6"
        >
            <IconTile tone="muted" size="sm">
                <CalendarCheck2 />
            </IconTile>
            {{ emptyMessage }}
        </div>

        <ul
            v-else
            class="grid gap-2 p-3 sm:p-4"
            :class="featured ? '@2xl:grid-cols-2' : 'grid-cols-1'"
        >
            <li v-for="todo in todos" :key="todo.id" class="min-w-0">
                <Link
                    :href="showTodo(todo)"
                    prefetch
                    class="ui-lift group flex min-h-11 cursor-pointer items-center gap-3 rounded-2xl border border-border/80 bg-background px-3 py-3 transition-colors hover:border-orange-500/30 hover:bg-orange-500/[0.045] focus-visible:ring-2 focus-visible:ring-orange-500/35 focus-visible:outline-none motion-reduce:transition-none"
                    :aria-label="
                        t('dashboard.open_task', { title: todo.title })
                    "
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
                        <span class="block truncate text-sm font-medium">
                            {{ todo.title }}
                        </span>
                        <span
                            class="mt-0.5 flex min-w-0 items-center gap-1.5 text-xs text-muted-foreground"
                        >
                            <span class="truncate">
                                {{
                                    todo.project?.name ??
                                    t('common.states.unassigned')
                                }}
                            </span>
                            <span aria-hidden="true">·</span>
                            <span class="shrink-0 tabular-nums">
                                {{ formattedDueDate(todo) }}
                            </span>
                        </span>
                    </span>
                    <Badge
                        v-if="tone === 'overdue'"
                        variant="destructive"
                        class="hidden sm:inline-flex"
                    >
                        {{ t('dashboard.overdue') }}
                    </Badge>
                    <ArrowUpRight
                        class="size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-orange-700 group-focus-visible:text-orange-700 motion-reduce:transition-none"
                        aria-hidden="true"
                    />
                </Link>
            </li>
        </ul>
    </Card>
</template>
