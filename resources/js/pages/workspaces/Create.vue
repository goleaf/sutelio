<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2 } from '@lucide/vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import WorkspaceForm from '@/components/workspace/WorkspaceForm.vue';
import { useUi } from '@/composables/useUi';
import { index } from '@/routes/workspaces';

const { t } = useUi();
</script>

<template>
    <div>
        <Head :title="t('workspaces.new')" />
        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="t('workspaces.eyebrow')"
                :title="t('workspaces.new')"
                :description="t('workspaces.create_description')"
            >
                <template #icon><Building2 aria-hidden="true" /></template>
            </WorkspacePageHeader>
            <WorkspaceForm :cancel-href="index().url" />
        </WorkspacePageFrame>
    </div>
</template>
