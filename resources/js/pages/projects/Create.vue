<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FolderPlus } from '@lucide/vue';
import ProjectForm from '@/components/project/ProjectForm.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { index as projectsIndex } from '@/routes/projects';
import type { Workspace } from '@/types/models';

const props = defineProps<{ workspace: Workspace }>();
const { copy } = useWorkspaceUi();
</script>

<template>
    <div>
        <Head :title="copy.projects.create_title" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="copy.projects.create_title"
                :description="copy.projects.create_description"
            >
                <template #icon><FolderPlus aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <ProjectForm
                :workspace-id="props.workspace.id"
                :cancel-href="projectsIndex(props.workspace.id).url"
            />
        </WorkspacePageFrame>
    </div>
</template>
