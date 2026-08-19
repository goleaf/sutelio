<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ListPlus } from '@lucide/vue';
import { computed } from 'vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import TaskCreateForm from '@/components/task/TaskCreateForm.vue';
import { useUi } from '@/composables/useUi';
import { show as projectShow } from '@/routes/projects';
import { index as tasksIndex } from '@/routes/todos';
import type { Project, TaskDefinitionCatalog } from '@/types/models';

const props = defineProps<{
    workspace: { id: string; name: string };
    projects: { data: Project[] };
    selectedProjectId: string | null;
    taskDefinitions: TaskDefinitionCatalog;
}>();
const { t } = useUi();
const cancelHref = computed(() =>
    props.selectedProjectId
        ? projectShow([props.workspace.id, props.selectedProjectId]).url
        : tasksIndex().url,
);
</script>

<template>
    <div>
        <Head :title="t('tasks.create.new_task')" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="t('tasks.create.new_task')"
                :description="t('tasks.create.page_description')"
            >
                <template #icon><ListPlus aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <TaskCreateForm
                :workspace-id="workspace.id"
                :projects="projects.data"
                :selected-project-id="selectedProjectId"
                :task-definitions="taskDefinitions"
                :cancel-href="cancelHref"
            />
        </WorkspacePageFrame>
    </div>
</template>
