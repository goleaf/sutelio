<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ListChecks } from '@lucide/vue';
import { ref, watch } from 'vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import TaskDetailContent from '@/components/task/TaskDetailContent.vue';
import { Button } from '@/components/ui/button';
import { useUi } from '@/composables/useUi';
import { index as tasksIndex } from '@/routes/todos';
import type {
    Label as TaskLabel,
    TaskDefinitionCatalog,
    Todo,
} from '@/types/models';

const props = defineProps<{
    todo: { data: Todo };
    availableLabels: { data: TaskLabel[] };
    labels: Record<string, unknown>;
    taskDefinitions: TaskDefinitionCatalog;
}>();
const { t } = useUi();
const currentTodo = ref(props.todo.data);

watch(
    () => props.todo.data,
    (todo) => {
        currentTodo.value = todo;
    },
);

function refresh(): void {
    router.reload({ only: ['todo', 'taskDefinitions'] });
}

function updated(todo: Todo): void {
    currentTodo.value = { ...currentTodo.value, ...todo };
    refresh();
}

function deleted(): void {
    router.visit(tasksIndex.url());
}
</script>

<template>
    <div>
        <Head :title="currentTodo.title" />

        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="t('tasks.detail.title')"
                :title="currentTodo.title"
                :description="t('tasks.detail.page_description')"
            >
                <template #icon>
                    <ListChecks aria-hidden="true" />
                </template>

                <template #actions>
                    <Button as-child variant="outline" size="lg">
                        <Link :href="tasksIndex.url()">
                            <ArrowLeft class="size-4" aria-hidden="true" />
                            {{ t('common.actions.back') }}
                        </Link>
                    </Button>
                </template>
            </WorkspacePageHeader>

            <TaskDetailContent
                :todo="currentTodo"
                :task-definitions="taskDefinitions"
                @deleted="deleted"
                @refresh="refresh"
                @updated="updated"
            />
        </WorkspacePageFrame>
    </div>
</template>
