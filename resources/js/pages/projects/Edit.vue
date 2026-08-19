<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import ProjectForm from '@/components/project/ProjectForm.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { show as projectShow } from '@/routes/projects';
import type { Project, Workspace } from '@/types/models';

defineProps<{ workspace: Workspace; project: Project }>();
const { copy } = useWorkspaceUi();
</script>

<template>
    <div>
        <Head :title="copy.projects.edit_title" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="copy.projects.edit_title"
                :description="copy.projects.edit_description"
            >
                <template #icon><Pencil aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <ProjectForm
                :workspace-id="workspace.id"
                :project="project"
                :cancel-href="projectShow([workspace.id, project.id]).url"
            />
        </WorkspacePageFrame>
    </div>
</template>
