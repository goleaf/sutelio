<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { buildProjectQuery } from '@/components/project/project-operations';
import type {
    ProjectAssignee,
    ProjectAttention,
    ProjectAttentionTasks,
    ProjectFilters,
    ProjectMetrics,
    ProjectPriorityDistribution,
    ProjectTask,
    ProjectTaskPaginator,
} from '@/components/project/project-operations';
import ProjectOperationsHeader from '@/components/project/ProjectOperationsHeader.vue';
import ProjectPulse from '@/components/project/ProjectPulse.vue';
import ProjectTaskFilters from '@/components/project/ProjectTaskFilters.vue';
import ProjectTaskQueue from '@/components/project/ProjectTaskQueue.vue';
import WorkspaceConfirmDialog from '@/components/shared/WorkspaceConfirmDialog.vue';
import TaskCreateDialog from '@/components/task/TaskCreateDialog.vue';
import TaskDetail from '@/components/task/TaskDetail.vue';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import {
    complete as completeThroughApi,
    destroy as destroyThroughApi,
    show as showThroughApi,
    uncomplete as uncompleteThroughApi,
} from '@/routes/api/v1/tasks';
import {
    archive,
    duplicate,
    index as projectsIndex,
    restore,
    show as showProject,
} from '@/routes/projects';
import type { Project, TaskDefinitionCatalog, Todo } from '@/types/models';

type ProjectHeaderAction = 'archive' | 'duplicate' | 'restore';
type ProjectActionResponse = { project: Project };

const props = defineProps<{
    assignees: ProjectAssignee[];
    attentionTasks: ProjectAttentionTasks;
    filters: ProjectFilters;
    metrics: ProjectMetrics;
    priorityDistribution: ProjectPriorityDistribution[];
    project: { data: Project };
    taskDefinitions: TaskDefinitionCatalog;
    today: string;
    todos: ProjectTaskPaginator;
    workspace: { id: string };
}>();

const project = computed(() => props.project.data);
const toast = useToast();
const { t } = useUi();
const selectedTodo = ref<Todo | null>(null);
const taskDetailTrigger = ref<HTMLElement | null>(null);
const showCreateDialog = ref(false);
const todoToDelete = ref<ProjectTask | null>(null);
const hiddenTaskIds = ref<Set<string>>(new Set());
const filtering = ref(false);
const busyTaskId = ref<string | null>(null);
const deletingTodo = ref(false);
const processingAction = ref<ProjectHeaderAction | null>(null);
const detailRequest = useHttp<Record<string, never>, { data: Todo }>({});
const completionRequest = useHttp<Record<string, never>, { data: Todo }>({});
const deleteRequest = useHttp<Record<string, never>, undefined>({});
const projectActionRequest = useHttp<
    Record<string, never>,
    ProjectActionResponse
>({});
const queueTodos = computed<ProjectTaskPaginator>(() => {
    const hiddenCount = props.todos.data.filter((task) =>
        hiddenTaskIds.value.has(task.id),
    ).length;

    return {
        ...props.todos,
        data: props.todos.data.filter(
            (task) => !hiddenTaskIds.value.has(task.id),
        ),
        meta: {
            ...props.todos.meta,
            total: Math.max(0, props.todos.meta.total - hiddenCount),
        },
    };
});

function applyFilters(filters: ProjectFilters): void {
    if (projectFilterUrl(filters) === projectFilterUrl(props.filters)) {
        return;
    }

    router.cancelAll();
    filtering.value = true;

    router.get(
        projectFilterUrl(filters),
        {},
        {
            only: ['todos', 'filters'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            reset: ['todos'],
            onFinish: () => {
                filtering.value = false;
            },
        },
    );
}

function projectFilterUrl(filters: ProjectFilters): string {
    return showProject([props.workspace.id, project.value.id], {
        query: buildProjectQuery(filters),
    }).url;
}

function clearFilters(): void {
    applyFilters({
        search: null,
        status: null,
        priority: null,
        assignee: null,
        attention: 'all',
        sort: 'position',
    });
}

function filterAttention(attention: ProjectAttention): void {
    applyFilters({ ...props.filters, attention });
}

function refreshOperations(includeTodos = true): void {
    const only = ['metrics', 'attentionTasks', 'priorityDistribution'];

    if (includeTodos) {
        only.unshift('todos', 'filters');
    }

    router.reload({ only });
}

async function selectTodo(task: Pick<ProjectTask, 'id'>): Promise<void> {
    if (detailRequest.processing) {
        return;
    }

    if (selectedTodo.value === null) {
        taskDetailTrigger.value =
            document.activeElement instanceof HTMLElement
                ? document.activeElement
                : null;
    }

    try {
        const response = await detailRequest.get(
            showThroughApi([props.workspace.id, task.id]).url,
        );
        selectedTodo.value = response.data;
    } catch {
        toast.error(t('common.errors.generic'));
    }
}

function closeTaskDetail(): void {
    selectedTodo.value = null;

    void nextTick(() => {
        taskDetailTrigger.value?.focus();
        taskDetailTrigger.value = null;
    });
}

function refreshSelectedTodo(): void {
    if (!selectedTodo.value) {
        return;
    }

    void selectTodo(selectedTodo.value);
}

function updateSelectedTodo(todo: Todo): void {
    if (selectedTodo.value?.id === todo.id) {
        selectedTodo.value = { ...selectedTodo.value, ...todo };
    }

    refreshOperations();
}

async function toggleCompletion(task: ProjectTask): Promise<void> {
    if (busyTaskId.value || completionRequest.processing) {
        return;
    }

    busyTaskId.value = task.id;
    const target = task.is_completed
        ? uncompleteThroughApi
        : completeThroughApi;

    try {
        await completionRequest.post(target([props.workspace.id, task.id]).url);
        refreshOperations();
    } catch {
        toast.error(t('common.errors.generic'));
    } finally {
        busyTaskId.value = null;
    }
}

async function deleteTodo(): Promise<void> {
    if (!todoToDelete.value || deletingTodo.value) {
        return;
    }

    const task = todoToDelete.value;
    deletingTodo.value = true;

    try {
        await deleteRequest.delete(
            destroyThroughApi([props.workspace.id, task.id]).url,
        );
        toast.success(t('tasks.index.deleted'));
        todoToDelete.value = null;
        hiddenTaskIds.value = new Set([...hiddenTaskIds.value, task.id]);

        if (selectedTodo.value?.id === task.id) {
            closeTaskDetail();
        }

        refreshOperations(false);
    } catch {
        toast.error(t('common.errors.generic'));
    } finally {
        deletingTodo.value = false;
    }
}

function archiveProject(): void {
    void submitProjectAction(
        'archive',
        archive([props.workspace.id, project.value.id]).url,
        t('projects.show.archived'),
    );
}

function restoreProject(): void {
    void submitProjectAction(
        'restore',
        restore([props.workspace.id, project.value.id]).url,
        t('projects.show.restored'),
    );
}

function duplicateProject(): void {
    void submitProjectAction(
        'duplicate',
        duplicate([props.workspace.id, project.value.id]).url,
        t('projects.show.duplicated'),
    );
}

async function submitProjectAction(
    action: ProjectHeaderAction,
    url: string,
    successMessage: string,
): Promise<void> {
    if (processingAction.value || projectActionRequest.processing) {
        return;
    }

    processingAction.value = action;

    try {
        const response = await projectActionRequest.post(url);
        toast.success(successMessage);

        if (action === 'duplicate') {
            router.visit(
                showProject([props.workspace.id, response.project.id]).url,
            );

            return;
        }

        router.reload({ only: ['project'] });
    } catch {
        toast.error(t('common.errors.generic'));
    } finally {
        processingAction.value = null;
    }
}
</script>

<template>
    <div>
        <Head :title="project.name" />

        <main class="min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8">
            <div class="mx-auto flex max-w-app flex-col gap-6">
                <ProjectOperationsHeader
                    :project="project"
                    :metrics="metrics"
                    :processing-action="processingAction"
                    @back="router.visit(projectsIndex(workspace.id).url)"
                    @new-task="showCreateDialog = true"
                    @duplicate="duplicateProject"
                    @archive="archiveProject"
                    @restore="restoreProject"
                />

                <div
                    class="grid min-w-0 grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]"
                >
                    <div
                        class="xl:sticky xl:top-6 xl:col-start-2 xl:row-start-1"
                    >
                        <ProjectPulse
                            :metrics="metrics"
                            :attention-tasks="attentionTasks"
                            :priority-distribution="priorityDistribution"
                            :today="today"
                            @select="selectTodo"
                            @filter="filterAttention"
                        />
                    </div>

                    <div
                        class="min-w-0 space-y-6 xl:col-start-1 xl:row-start-1"
                    >
                        <ProjectTaskFilters
                            :filters="filters"
                            :task-definitions="taskDefinitions"
                            :assignees="assignees"
                            :processing="filtering"
                            @update="applyFilters"
                        />
                        <ProjectTaskQueue
                            :archived="project.is_archived"
                            :todos="queueTodos"
                            :filters="filters"
                            :processing="filtering"
                            :busy-task-id="busyTaskId"
                            :today="today"
                            @select="selectTodo"
                            @toggle="toggleCompletion"
                            @delete="todoToDelete = $event"
                            @create="showCreateDialog = true"
                            @clear="clearFilters"
                        />
                    </div>
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
            @deleted="refreshOperations"
            @refresh="refreshSelectedTodo"
            @updated="updateSelectedTodo"
        />
        <TaskCreateDialog
            :open="showCreateDialog"
            :workspace-id="workspace.id"
            :project-id="project.id"
            :task-definitions="taskDefinitions"
            @close="showCreateDialog = false"
            @created="refreshOperations"
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
            @update:open="!$event && (todoToDelete = null)"
            @confirm="deleteTodo"
        />
    </div>
</template>
