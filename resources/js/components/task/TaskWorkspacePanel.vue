<script setup lang="ts">
import { ListChecks } from '@lucide/vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import BoardView from '@/components/task/BoardView.vue';
import BulkActions from '@/components/task/BulkActions.vue';
import TaskFilterBar from '@/components/task/TaskFilterBar.vue';
import TaskList from '@/components/task/TaskList.vue';
import TaskPagination from '@/components/task/TaskPagination.vue';
import TaskResultsBar from '@/components/task/TaskResultsBar.vue';
import { useUi } from '@/composables/useUi';
import type { PaginatedResponse, TodoFilters } from '@/types/api';
import type {
    Project,
    TaskDefinitionCatalog,
    TaskStatusDefinition,
    Todo,
} from '@/types/models';

const props = defineProps<{
    activeFilterCount: number;
    allSelected: boolean;
    bulkProcessing: boolean;
    busyTodoId: string | null;
    filters: TodoFilters;
    filtering: boolean;
    projects: Project[];
    selectedIds: string[];
    selectionMode: boolean;
    taskDefinitions: TaskDefinitionCatalog;
    todos: PaginatedResponse<Todo>;
}>();

const emit = defineEmits<{
    bulkAction: [action: 'archive' | 'complete' | 'delete' | 'uncomplete'];
    clearFilters: [];
    clearSelection: [];
    create: [];
    delete: [todo: Todo, trigger?: HTMLElement | null];
    move: [todo: Todo, status: TaskStatusDefinition];
    navigate: [processing: boolean];
    select: [todo: Todo, trigger?: HTMLElement | null];
    selectPage: [selected: boolean];
    toggleCompletion: [todo: Todo];
    toggleSelection: [todo: Todo];
    updateFilters: [filters: TodoFilters];
    updateSelectionMode: [enabled: boolean];
}>();
const { t } = useUi();

function handleEmptyAction(): void {
    if (props.activeFilterCount > 0) {
        emit('clearFilters');

        return;
    }

    emit('create');
}

function forwardMove(todo: Todo, status: TaskStatusDefinition): void {
    emit('move', todo, status);
}

function forwardSelect(todo: Todo, trigger?: HTMLElement | null): void {
    emit('select', todo, trigger);
}

function forwardDelete(todo: Todo, trigger?: HTMLElement | null): void {
    emit('delete', todo, trigger);
}
</script>

<template>
    <section
        class="rounded-panel border border-border/80 bg-card p-4 shadow-panel sm:p-6"
    >
        <TaskFilterBar
            :filters="filters"
            :projects="projects"
            :task-definitions="taskDefinitions"
            :processing="filtering"
            @update="emit('updateFilters', $event)"
        />
        <TaskResultsBar
            :active-filter-count="activeFilterCount"
            :all-selected="allSelected"
            :pagination="todos"
            :processing="filtering"
            :selected-count="selectedIds.length"
            :selection-mode="selectionMode"
            :view="filters.view ?? 'list'"
            @select-page="emit('selectPage', $event)"
            @update-selection-mode="emit('updateSelectionMode', $event)"
        />
        <BulkActions
            v-if="selectionMode && selectedIds.length"
            :selected-ids="selectedIds"
            :processing="bulkProcessing"
            @action="emit('bulkAction', $event)"
            @clear="emit('clearSelection')"
        />

        <div class="mt-5">
            <BoardView
                v-if="todos.data.length && filters.view === 'board'"
                :todos="todos.data"
                :task-definitions="taskDefinitions"
                :busy-todo-id="busyTodoId"
                @move="forwardMove"
                @select="emit('select', $event)"
            />
            <TaskList
                v-else-if="todos.data.length"
                :todos="todos.data"
                :selected-ids="selectedIds"
                :busy-todo-id="busyTodoId"
                :selection-mode="selectionMode"
                @delete="forwardDelete"
                @select="forwardSelect"
                @toggle-completion="emit('toggleCompletion', $event)"
                @toggle-selection="emit('toggleSelection', $event)"
            />
            <TaskPagination
                v-if="todos.data.length"
                :pagination="todos"
                :processing="filtering"
                @navigate="emit('navigate', $event)"
            />
            <EmptyState
                v-else
                compact
                :title="
                    activeFilterCount
                        ? t('tasks.index.filtered_empty_title')
                        : t('tasks.index.empty_title')
                "
                :description="
                    activeFilterCount
                        ? t('tasks.index.filtered_empty_description')
                        : t('tasks.index.empty_description')
                "
                :action-label="
                    activeFilterCount
                        ? t('tasks.filters.clear')
                        : t('tasks.create.new_task')
                "
                @action="handleEmptyAction"
            >
                <template #icon>
                    <ListChecks class="size-7" aria-hidden="true" />
                </template>
            </EmptyState>
        </div>
    </section>
</template>
