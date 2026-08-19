<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import WorkspaceForm from '@/components/workspace/WorkspaceForm.vue';
import { useUi } from '@/composables/useUi';
import { show } from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

const props = defineProps<{ workspace: Workspace }>();
const { t } = useUi();
</script>

<template>
    <div>
        <Head :title="t('workspaces.actions.edit')" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="t('workspaces.actions.edit')"
                :description="t('workspaces.edit_description')"
            >
                <template #icon><Pencil aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <WorkspaceForm
                :workspace="props.workspace"
                :cancel-href="show(props.workspace).url"
            />
        </WorkspacePageFrame>
    </div>
</template>
