<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { CheckCircle2, Clock3, ListChecks, Plus } from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import WorkspaceConfirmDialog from '@/components/shared/WorkspaceConfirmDialog.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import {
    activeTaskFilterCount,
    clearTaskFilters,
    restoreTaskFocus,
} from '@/components/task/task-focus';
import TaskCreateDialog from '@/components/task/TaskCreateDialog.vue';
import TaskDetail from '@/components/task/TaskDetail.vue';
import TaskWorkspacePanel from '@/components/task/TaskWorkspacePanel.vue';
import { Button } from '@/components/ui/button';
import { useBulkSelect } from '@/composables/useBulkSelect';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import {
    show as showThroughApi,
    update as updateThroughApi,
} from '@/routes/api/v1/tasks';
import {
    bulk,
    complete,
    destroy,
    index as tasksIndex,
    uncomplete,
} from '@/routes/todos';
import type { PaginatedResponse, TodoFilters } from '@/types/api';
import type {
    Project,
    TaskDefinitionCatalog,
    TaskStatusDefinition,
    Todo,
} from '@/types/models';

const props = defineProps<{
    todos: PaginatedResponse<Todo>;
    filters: TodoFilters;
    stats: { total: number; pending: number; completed: number };
    projects: { data: Project[] };
    workspace: { id: string };
    taskDefinitions: TaskDefinitionCatalog;
}>();
const bulkSelect = useBulkSelect<Todo>();
const toast = useToast();
const { formatNumber, t } = useUi();
const selectedTodo = ref<Todo | null>(null);
const taskDetailTrigger = ref<HTMLElement | null>(null);
const taskQueueFallback = ref<HTMLElement | null>(null);
const confirmationTrigger = ref<HTMLElement | null>(null);
const showCreateDialog = ref(false);
const todoToDelete = ref<Todo | null>(null);
const deletingTodo = ref(false);
const filtering = ref(false);
const busyTodoId = ref<string | null>(null);
const bulkProcessing = ref(false);
const confirmBulkDelete = ref(false);
const selectionMode = ref(false);
const detailRequest = useHttp<Record<string, never>, { data: Todo }>({});
const statusRequest = useHttp<{ status: string }, { data: Todo }>({
    status: '',
});
const selectedIds = computed(() => Array.from(bulkSelect.selectedIds.value));
const activeFilterCount = computed(() => activeTaskFilterCount(props.filters));
const allSelected = computed(
    () =>
        props.todos.data.length > 0 &&
        props.todos.data.every((todo) =>
            bulkSelect.selectedIds.value.has(todo.id),
        ),
);

function applyFilters(filters: TodoFilters): void {
    filtering.value = true;
    setSelectionMode(false);
    router.get(tasksIndex.url(), filters, {
        only: ['todos', 'filters', 'stats'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onFinish: () => {
            filtering.value = false;
        },
    });
}

function refreshIndex(): void {
    setSelectionMode(false);
    router.reload({ only: ['todos', 'filters', 'stats'] });
}

function setSelectionMode(enabled: boolean): void {
    selectionMode.value = enabled;

    if (!enabled) {
        bulkSelect.clearSelection();
    }
}

function handlePagination(processing: boolean): void {
    if (processing) {
        setSelectionMode(false);
    }

    filtering.value = processing;
}

function activeElement(): HTMLElement | null {
    return document.activeElement instanceof HTMLElement
        ? document.activeElement
        : null;
}

function restoreFocus(origin: HTMLElement | null): void {
    void nextTick(() => restoreTaskFocus(origin, taskQueueFallback.value));
}

async function selectTodo(
    todo: Todo,
    trigger: HTMLElement | null = activeElement(),
): Promise<void> {
    if (!props.workspace.id || detailRequest.processing) {
        return;
    }

    if (!selectedTodo.value) {
        taskDetailTrigger.value = trigger ?? activeElement();
    }

    try {
        const response = await detailRequest.get(
            showThroughApi([props.workspace.id, todo]).url,
        );
        selectedTodo.value = response.data;
    } catch {
        toast.error(t('common.errors.generic'));
    }
}

function closeTaskDetail(): void {
    selectedTodo.value = null;
    restoreFocus(taskDetailTrigger.value);
    taskDetailTrigger.value = null;
}

function updateSelectedTodo(todo: Todo): void {
    if (selectedTodo.value?.id === todo.id) {
        selectedTodo.value = { ...selectedTodo.value, ...todo };
    }

    refreshIndex();
}

function toggleCompletion(todo: Todo): void {
    if (busyTodoId.value) {
        return;
    }

    busyTodoId.value = todo.id;
    const trigger = activeElement();
    const target = todo.is_completed ? uncomplete(todo) : complete(todo);
    router.post(
        target.url,
        {},
        {
            only: ['todos', 'filters', 'stats'],
            preserveScroll: true,
            onSuccess: () => restoreFocus(trigger),
            onFinish: () => {
                busyTodoId.value = null;
            },
        },
    );
}

async function moveTodo(
    todo: Todo,
    status: TaskStatusDefinition,
): Promise<void> {
    if (busyTodoId.value) {
        return;
    }

    busyTodoId.value = todo.id;
    statusRequest.status = status.key;

    try {
        const response = await statusRequest.put(
            updateThroughApi([props.workspace.id, todo]).url,
        );
        updateSelectedTodo(response.data);
    } catch {
        toast.error(t('common.errors.generic'));
    } finally {
        busyTodoId.value = null;
    }
}

function selectPage(selected: boolean): void {
    bulkSelect.clearSelection();

    if (selected) {
        bulkSelect.selectAll(props.todos.data);
    }
}

function requestBulkAction(
    action: 'archive' | 'complete' | 'delete' | 'uncomplete',
): void {
    confirmationTrigger.value = activeElement();

    if (action === 'delete') {
        confirmBulkDelete.value = true;

        return;
    }

    performBulkAction(action);
}

function performBulkAction(
    action: 'archive' | 'complete' | 'delete' | 'uncomplete',
): void {
    if (bulkProcessing.value || selectedIds.value.length === 0) {
        return;
    }

    const count = selectedIds.value.length;
    bulkProcessing.value = true;
    router.post(
        bulk(props.workspace.id).url,
        { ids: selectedIds.value, action },
        {
            only: ['todos', 'filters', 'stats'],
            preserveScroll: true,
            onSuccess: () => {
                const message = {
                    archive: 'tasks.index.bulk_archived',
                    complete: 'tasks.index.bulk_completed',
                    delete: 'tasks.index.bulk_deleted',
                    uncomplete: 'tasks.index.bulk_reopened',
                }[action];

                toast.success(t(message, { count: formatNumber(count) }));
                setSelectionMode(false);
                confirmBulkDelete.value = false;
                restoreFocus(confirmationTrigger.value);
                confirmationTrigger.value = null;
            },
            onError: () => toast.error(t('common.errors.generic')),
            onFinish: () => {
                bulkProcessing.value = false;
            },
        },
    );
}

function requestDelete(
    todo: Todo,
    trigger: HTMLElement | null = activeElement(),
): void {
    confirmationTrigger.value = trigger ?? activeElement();
    todoToDelete.value = todo;
}

function closeConfirmation(target: 'bulk' | 'todo'): void {
    if (target === 'bulk') {
        confirmBulkDelete.value = false;
    } else {
        todoToDelete.value = null;
    }

    restoreFocus(confirmationTrigger.value);
    confirmationTrigger.value = null;
}

function deleteTodo(): void {
    if (!todoToDelete.value || deletingTodo.value) {
        return;
    }

    const todo = todoToDelete.value;
    deletingTodo.value = true;
    router.delete(destroy(todo).url, {
        only: ['todos', 'filters', 'stats'],
        preserveScroll: true,
        onSuccess: () => {
            toast.success(t('tasks.index.deleted'));
            todoToDelete.value = null;

            if (selectedTodo.value?.id === todo.id) {
                selectedTodo.value = null;
            }

            if (bulkSelect.selectedIds.value.has(todo.id)) {
                bulkSelect.toggle(todo.id);
            }

            restoreFocus(confirmationTrigger.value);
            confirmationTrigger.value = null;
        },
        onFinish: () => {
            deletingTodo.value = false;
        },
    });
}
</script>

<template>
    <div>
        <Head :title="t('tasks.index.title')" />
        <main class="min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8">
            <div class="mx-auto flex max-w-app flex-col gap-6">
                <WorkspacePageHeader
                    :eyebrow="t('tasks.board.to_do')"
                    :title="t('tasks.index.title')"
                    :description="
                        t('tasks.index.count', {
                            count: formatNumber(stats.total),
                        })
                    "
                >
                    <template #actions>
                        <Button
                            size="lg"
                            :disabled="!workspace.id"
                            @click="showCreateDialog = true"
                        >
                            <Plus class="size-4" aria-hidden="true" />
                            {{ t('tasks.create.new_task') }}
                        </Button>
                    </template>
                    <template #metrics>
                        <WorkspaceMetric
                            :label="t('tasks.stats.total')"
                            :value="formatNumber(stats.total)"
                            :icon="ListChecks"
                            tone="orange"
                        />
                        <WorkspaceMetric
                            :label="t('tasks.stats.pending')"
                            :value="formatNumber(stats.pending)"
                            :icon="Clock3"
                            tone="blue"
                        />
                        <WorkspaceMetric
                            :label="t('tasks.stats.completed')"
                            :value="formatNumber(stats.completed)"
                            :icon="CheckCircle2"
                            tone="emerald"
                        />
                    </template>
                </WorkspacePageHeader>

                <div
                    ref="taskQueueFallback"
                    tabindex="-1"
                    :aria-busy="filtering"
                    class="focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none"
                >
                    <TaskWorkspacePanel
                        :active-filter-count="activeFilterCount"
                        :all-selected="allSelected"
                        :bulk-processing="bulkProcessing"
                        :busy-todo-id="busyTodoId"
                        :filters="filters"
                        :filtering="filtering"
                        :projects="projects.data"
                        :selected-ids="selectedIds"
                        :selection-mode="selectionMode"
                        :task-definitions="taskDefinitions"
                        :todos="todos"
                        @bulk-action="requestBulkAction"
                        @clear-filters="applyFilters(clearTaskFilters(filters))"
                        @clear-selection="setSelectionMode(false)"
                        @create="showCreateDialog = true"
                        @delete="requestDelete"
                        @move="moveTodo"
                        @navigate="handlePagination"
                        @select="selectTodo"
                        @select-page="selectPage"
                        @toggle-completion="toggleCompletion"
                        @toggle-selection="bulkSelect.toggle($event.id)"
                        @update-filters="applyFilters"
                        @update-selection-mode="setSelectionMode"
                    />
                </div>
            </div>
        </main>

        <TaskDetail
            v-if="selectedTodo"
            :key="selectedTodo.id"
            :todo="selectedTodo"
            :open="Boolean(selectedTodo)"
            :task-definitions="taskDefinitions"
            @close="closeTaskDetail"
            @deleted="refreshIndex"
            @refresh="selectTodo(selectedTodo)"
            @updated="updateSelectedTodo"
        />
        <TaskCreateDialog
            :open="showCreateDialog"
            :workspace-id="workspace.id"
            :task-definitions="taskDefinitions"
            @close="showCreateDialog = false"
            @created="refreshIndex"
        />
        <WorkspaceConfirmDialog
            :open="todoToDelete !== null"
            :title="t('tasks.index.delete_confirm_title')"
            :description="
                t('tasks.index.delete_confirm_description', {
                    title: todoToDelete?.title ?? '',
                })
            "
            :confirm-label="t('common.actions.delete')"
            :cancel-label="t('common.actions.cancel')"
            :processing="deletingTodo"
            @update:open="!$event && closeConfirmation('todo')"
            @confirm="deleteTodo"
        />
        <WorkspaceConfirmDialog
            :open="confirmBulkDelete"
            :title="t('tasks.index.bulk_delete_confirm_title')"
            :description="
                t('tasks.index.bulk_delete_confirm_description', {
                    count: formatNumber(selectedIds.length),
                })
            "
            :confirm-label="t('common.actions.delete')"
            :cancel-label="t('common.actions.cancel')"
            :processing="bulkProcessing"
            @update:open="!$event && closeConfirmation('bulk')"
            @confirm="performBulkAction('delete')"
        />
    </div>
</template>
