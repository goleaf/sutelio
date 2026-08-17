<script setup lang="ts">
import {
    Check,
    Circle,
    MoreHorizontal,
    PanelRightOpen,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import { isTodoOverdue } from '@/lib/todoDates';
import type { Todo } from '@/types/models';

const props = defineProps<{
    todos: Todo[];
    selectedIds: string[];
    busyTodoId: string | null;
    selectionMode: boolean;
}>();
const emit = defineEmits<{
    delete: [todo: Todo, trigger: HTMLElement | null];
    select: [todo: Todo, trigger: HTMLElement | null];
    toggleCompletion: [todo: Todo];
    toggleSelection: [todo: Todo];
}>();
const { formatDate, t } = useUi();
const selected = computed(() => new Set(props.selectedIds));
const actionTriggers = new Map<string, HTMLElement>();

const isOverdue = (todo: Todo): boolean =>
    isTodoOverdue(todo.due_date, todo.is_completed);

const priorityColor = (todo: Todo): string =>
    safeDefinitionColor(todo.priority_definition?.color);

const statusColor = (todo: Todo): string =>
    safeDefinitionColor(todo.status_definition?.color);

function dueLabel(todo: Todo): string {
    if (!todo.due_date) {
        return '';
    }

    const date = formatDate(todo.due_date, {
        month: 'short',
        day: 'numeric',
    });

    return isOverdue(todo)
        ? t('tasks.index.due_overdue', { date })
        : t('tasks.index.due_on', { date });
}

function eventTarget(event: Event): HTMLElement | null {
    return event.currentTarget instanceof HTMLElement
        ? event.currentTarget
        : null;
}

function selectFromRow(todo: Todo, event: Event): void {
    emit('select', todo, eventTarget(event));
}

function rememberActionTrigger(todo: Todo, open: boolean): void {
    if (open && document.activeElement instanceof HTMLElement) {
        actionTriggers.set(todo.id, document.activeElement);
    }
}

function selectFromActions(todo: Todo): void {
    emit('select', todo, actionTriggers.get(todo.id) ?? null);
}

function deleteFromActions(todo: Todo): void {
    emit('delete', todo, actionTriggers.get(todo.id) ?? null);
}
</script>

<template>
    <div class="ui-stagger space-y-2.5">
        <article
            v-for="todo in todos"
            :key="todo.id"
            class="group grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2 rounded-xl border border-border/80 bg-background p-3 transition-[border-color,box-shadow] hover:border-orange-500/25 hover:shadow-[0_16px_36px_-30px_rgba(234,88,12,0.55)] motion-reduce:transition-none sm:gap-3 sm:p-4"
            :class="[
                selected.has(todo.id)
                    ? 'border-orange-500/30 bg-orange-500/[0.035]'
                    : '',
                isOverdue(todo) ? 'border-red-500/40 bg-red-500/[0.025]' : '',
            ]"
            :aria-busy="busyTodoId === todo.id"
        >
            <div class="flex size-11 items-center justify-center">
                <Checkbox
                    v-if="selectionMode"
                    :model-value="selected.has(todo.id)"
                    :aria-label="
                        t('tasks.index.select_task', { title: todo.title })
                    "
                    :disabled="busyTodoId !== null"
                    @update:model-value="emit('toggleSelection', todo)"
                />
                <Button
                    v-else
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="min-h-11 min-w-11"
                    :aria-label="
                        todo.is_completed
                            ? t('tasks.index.mark_pending', {
                                  title: todo.title,
                              })
                            : t('tasks.index.mark_complete', {
                                  title: todo.title,
                              })
                    "
                    :disabled="busyTodoId !== null"
                    @click="emit('toggleCompletion', todo)"
                >
                    <Check
                        v-if="todo.is_completed"
                        class="size-4 text-emerald-600"
                        aria-hidden="true"
                    />
                    <Circle
                        v-else
                        class="size-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                </Button>
            </div>

            <button
                type="button"
                class="min-h-11 min-w-0 cursor-pointer rounded-lg py-1 text-left focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none"
                :aria-label="t('tasks.index.open_task', { title: todo.title })"
                @click="selectFromRow(todo, $event)"
            >
                <span
                    :class="[
                        'line-clamp-2 block text-sm font-semibold tracking-tight sm:truncate',
                        todo.is_completed
                            ? 'text-muted-foreground line-through'
                            : '',
                    ]"
                >
                    {{ todo.title }}
                </span>
                <span
                    class="mt-1.5 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1.5 text-xs text-muted-foreground"
                >
                    <span v-if="todo.project" class="max-w-full truncate">
                        {{ todo.project.name }}
                    </span>
                    <Badge
                        v-if="todo.status_definition || todo.status"
                        class="inline-flex w-auto max-w-full shrink overflow-visible text-left leading-snug break-all whitespace-normal"
                        variant="outline"
                        :style="{
                            borderColor: statusColor(todo),
                            color: statusColor(todo),
                        }"
                    >
                        {{ todo.status_definition?.name ?? todo.status }}
                    </Badge>
                    <Badge
                        v-if="todo.priority_definition || todo.priority"
                        class="inline-flex w-auto max-w-full shrink overflow-visible text-left leading-snug break-all whitespace-normal"
                        variant="outline"
                        :style="{
                            borderColor: priorityColor(todo),
                            color: priorityColor(todo),
                        }"
                    >
                        {{ todo.priority_definition?.name ?? todo.priority }}
                    </Badge>
                    <Badge
                        v-if="todo.due_date"
                        class="inline-flex w-auto max-w-full shrink overflow-visible text-left leading-snug break-all whitespace-normal"
                        :class="
                            isOverdue(todo)
                                ? 'border-red-500/50 text-red-700'
                                : ''
                        "
                        variant="outline"
                    >
                        {{ dueLabel(todo) }}
                    </Badge>
                </span>
            </button>

            <DropdownMenu @update:open="rememberActionTrigger(todo, $event)">
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="min-h-11 min-w-11 text-muted-foreground"
                        :aria-label="
                            t('tasks.index.row_actions', {
                                title: todo.title,
                            })
                        "
                        :disabled="busyTodoId !== null"
                    >
                        <MoreHorizontal class="size-4" aria-hidden="true" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-52">
                    <DropdownMenuItem
                        class="min-h-11"
                        @select="selectFromActions(todo)"
                    >
                        <PanelRightOpen class="size-4" aria-hidden="true" />
                        {{ t('tasks.index.open') }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        variant="destructive"
                        class="min-h-11"
                        @select="deleteFromActions(todo)"
                    >
                        <Trash2 class="size-4" aria-hidden="true" />
                        {{ t('common.actions.delete') }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </article>
    </div>
</template>
