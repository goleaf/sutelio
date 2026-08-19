<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Copy } from '@lucide/vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import WorkspaceDuplicateForm from '@/components/workspace/WorkspaceDuplicateForm.vue';
import { useUi } from '@/composables/useUi';
import { show } from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

const props = defineProps<{ workspace: Workspace }>();
const { t } = useUi();
</script>

<template>
    <div>
        <Head :title="t('workspaces.actions.duplicate')" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="workspace.name"
                :title="t('workspaces.actions.duplicate')"
                :description="t('workspaces.duplicate_description')"
            >
                <template #icon><Copy aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <WorkspaceDuplicateForm
                :workspace="props.workspace"
                :cancel-href="show(props.workspace).url"
            />
        </WorkspacePageFrame>
    </div>
</template>
