<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    buildProjectQuery,
    projectTaskMatchesFilters,
    sortProjectTasks,
} from '@/components/project/project-operations';
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
import PageConfirmPanel from '@/components/shared/PageConfirmPanel.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import {
    complete as completeThroughApi,
    destroy as destroyThroughApi,
    uncomplete as uncompleteThroughApi,
} from '@/routes/api/v1/tasks';
import {
    archive,
    copy as copyProject,
    edit as editProjectPage,
    index as projectsIndex,
    restore,
    show as showProject,
} from '@/routes/projects';
import { create as createTodo, show as showTodo } from '@/routes/todos';
import type { Project, TaskDefinitionCatalog, Todo } from '@/types/models';

type ProjectHeaderAction = 'archive' | 'restore';
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
const todoToDelete = ref<ProjectTask | null>(null);
const hiddenTaskIds = ref<Set<string>>(new Set());
const taskOverrides = ref<Map<string, ProjectTask>>(new Map());
const pendingTotalAdjustmentIds = ref<Set<string>>(new Set());
const filtering = ref(false);
const busyTaskId = ref<string | null>(null);
const deletingTodo = ref(false);
const processingAction = ref<ProjectHeaderAction | null>(null);
const completionRequest = useHttp<Record<string, never>, { data: Todo }>({});
const deleteRequest = useHttp<Record<string, never>, undefined>({});
const projectActionRequest = useHttp<
    Record<string, never>,
    ProjectActionResponse
>({});
const queueTodos = computed<ProjectTaskPaginator>(() => {
    const visibleTasks = props.todos.data
        .map((task) => taskOverrides.value.get(task.id) ?? task)
        .filter((task) => !hiddenTaskIds.value.has(task.id));

    return {
        ...props.todos,
        data: sortProjectTasks(visibleTasks, props.filters.sort),
        meta: {
            ...props.todos.meta,
            total: Math.max(
                0,
                props.todos.meta.total - pendingTotalAdjustmentIds.value.size,
            ),
        },
    };
});

watch(
    () => props.todos,
    () => {
        pendingTotalAdjustmentIds.value = new Set();
    },
);

function applyFilters(filters: ProjectFilters): void {
    if (projectFilterUrl(filters) === projectFilterUrl(props.filters)) {
        return;
    }

    router.cancelAll();
    filtering.value = true;
    hiddenTaskIds.value = new Set();
    taskOverrides.value = new Map();
    pendingTotalAdjustmentIds.value = new Set();

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

    router.reload({
        only,
        onSuccess: () => {
            if (includeTodos) {
                pendingTotalAdjustmentIds.value = new Set();
            }
        },
    });
}

function selectTodo(task: Pick<ProjectTask, 'id'>): void {
    router.visit(showTodo(task.id).url);
}

function openCreatePage(): void {
    router.visit(
        createTodo(props.workspace.id, {
            query: { project_id: project.value.id },
        }).url,
    );
}

function synchronizeTask(todo: Todo): void {
    const hiddenIds = new Set(hiddenTaskIds.value);
    const overrides = new Map(taskOverrides.value);
    const totalAdjustmentIds = new Set(pendingTotalAdjustmentIds.value);
    const currentTask =
        overrides.get(todo.id) ??
        props.todos.data.find((task) => task.id === todo.id);

    if (!projectTaskMatchesFilters(todo, props.filters, props.today)) {
        if (currentTask && !hiddenIds.has(todo.id)) {
            totalAdjustmentIds.add(todo.id);
        }

        hiddenIds.add(todo.id);
        overrides.delete(todo.id);
    } else {
        hiddenIds.delete(todo.id);
        totalAdjustmentIds.delete(todo.id);

        if (currentTask) {
            overrides.set(todo.id, projectTaskFromTodo(todo, currentTask));
        }
    }

    hiddenTaskIds.value = hiddenIds;
    taskOverrides.value = overrides;
    pendingTotalAdjustmentIds.value = totalAdjustmentIds;
}

function projectTaskFromTodo(todo: Todo, current: ProjectTask): ProjectTask {
    const statusDefinition = todo.status_definition
        ? todo.status_definition
        : todo.status_id === current.status_id
          ? current.status_definition
          : null;
    const priorityDefinition = todo.priority_definition
        ? todo.priority_definition
        : todo.priority_id === current.priority_id
          ? current.priority_definition
          : null;

    return {
        ...current,
        title: todo.title,
        assigned_to: todo.assigned_to,
        assignee: todo.assignee
            ? { id: todo.assignee.id, name: todo.assignee.name }
            : todo.assigned_to === current.assigned_to
              ? current.assignee
              : null,
        status: todo.status,
        status_id: todo.status_id,
        status_definition: statusDefinition,
        priority: todo.priority,
        priority_id: todo.priority_id,
        priority_definition: priorityDefinition,
        labels:
            todo.labels?.map((label) => ({
                id: label.id,
                name: label.name,
                color: label.color,
            })) ?? current.labels,
        is_completed: todo.is_completed,
        due_date: todo.due_date,
        position: todo.position,
        completed_at: todo.completed_at,
        created_at: todo.created_at,
        updated_at: todo.updated_at,
    };
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
        const response = await completionRequest.post(
            target([props.workspace.id, task.id]).url,
        );
        synchronizeTask(response.data);
        refreshOperations();
    } catch {
        toast.error(t('common.errors.generic'));
    } finally {
        busyTaskId.value = null;
    }
}

function handleDeletedTodo(taskId: string): void {
    const overrides = new Map(taskOverrides.value);
    const totalAdjustmentIds = new Set(pendingTotalAdjustmentIds.value);

    if (
        props.todos.data.some((task) => task.id === taskId) &&
        !hiddenTaskIds.value.has(taskId)
    ) {
        totalAdjustmentIds.add(taskId);
    }

    overrides.delete(taskId);
    hiddenTaskIds.value = new Set([...hiddenTaskIds.value, taskId]);
    taskOverrides.value = overrides;
    pendingTotalAdjustmentIds.value = totalAdjustmentIds;
    refreshOperations(false);
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
        handleDeletedTodo(task.id);
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
    router.visit(copyProject([props.workspace.id, project.value.id]).url);
}

function editProject(): void {
    router.visit(editProjectPage([props.workspace.id, project.value.id]).url);
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
        await projectActionRequest.post(url);
        toast.success(successMessage);

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

        <WorkspacePageFrame>
            <ProjectOperationsHeader
                :project="project"
                :metrics="metrics"
                :processing-action="processingAction"
                @back="router.visit(projectsIndex(workspace.id).url)"
                @new-task="openCreatePage"
                @edit="editProject"
                @duplicate="duplicateProject"
                @archive="archiveProject"
                @restore="restoreProject"
            />

            <div
                class="grid min-w-0 grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]"
            >
                <div class="xl:sticky xl:top-6 xl:col-start-2 xl:row-start-1">
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
                    tabindex="-1"
                    :aria-label="t('projects.show.results.title')"
                    class="min-w-0 space-y-6 rounded-panel focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none xl:col-start-1 xl:row-start-1"
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
                        @create="openCreatePage"
                        @clear="clearFilters"
                    />
                </div>
            </div>

            <PageConfirmPanel
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
        </WorkspacePageFrame>
    </div>
</template>
