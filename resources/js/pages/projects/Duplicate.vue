<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Copy } from '@lucide/vue';
import ProjectDuplicateForm from '@/components/project/ProjectDuplicateForm.vue';
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
        <Head :title="copy.projects.duplicate_title" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="copy.projects.duplicate_title"
                :description="copy.projects.duplicate_description"
            >
                <template #icon><Copy aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <ProjectDuplicateForm
                :workspace-id="workspace.id"
                :project="project"
                :cancel-href="projectShow([workspace.id, project.id]).url"
            />
        </WorkspacePageFrame>
    </div>
</template>
