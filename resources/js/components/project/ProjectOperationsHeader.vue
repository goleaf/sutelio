<script setup lang="ts">
import {
    Archive,
    ArrowLeft,
    CheckCircle2,
    CircleDotDashed,
    Copy,
    FolderKanban,
    ListChecks,
    MoreHorizontal,
    Plus,
    RotateCcw,
    TriangleAlert,
} from '@lucide/vue';
import type { ProjectMetrics } from '@/components/project/project-operations';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Spinner } from '@/components/ui/spinner';
import { useUi } from '@/composables/useUi';
import type { Project } from '@/types/models';

type ProjectHeaderAction = 'archive' | 'duplicate' | 'restore';

defineProps<{
    metrics: ProjectMetrics;
    processingAction: ProjectHeaderAction | null;
    project: Project;
}>();

const emit = defineEmits<{
    archive: [];
    back: [];
    duplicate: [];
    newTask: [];
    restore: [];
}>();

const { formatNumber, t } = useUi();
</script>

<template>
    <WorkspacePageHeader
        :eyebrow="t('projects.show.results.title')"
        :title="project.name"
        :description="project.description ?? t('projects.show.no_description')"
    >
        <template #icon>
            <FolderKanban aria-hidden="true" />
        </template>

        <template #back>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="-ml-3 min-h-12 text-muted-foreground pointer-coarse:min-h-13"
                :disabled="Boolean(processingAction)"
                @click="emit('back')"
            >
                <ArrowLeft class="size-4" aria-hidden="true" />
                {{ t('projects.show.actions.back') }}
            </Button>
        </template>

        <template v-if="project.is_archived" #badges>
            <Badge variant="secondary">
                <Archive class="size-3" aria-hidden="true" />
                {{ t('projects.show.archived') }}
            </Badge>
        </template>

        <template #actions>
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        class="min-h-12 pointer-coarse:min-h-13"
                        :disabled="Boolean(processingAction)"
                        :aria-label="t('projects.show.actions.more')"
                    >
                        <MoreHorizontal class="size-4" aria-hidden="true" />
                        <span class="hidden sm:inline">{{
                            t('projects.show.actions.more')
                        }}</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-60">
                    <DropdownMenuItem
                        :disabled="Boolean(processingAction)"
                        @select="emit('duplicate')"
                    >
                        <Spinner v-if="processingAction === 'duplicate'" />
                        <Copy v-else class="size-4" aria-hidden="true" />
                        {{ t('projects.show.actions.duplicate') }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        v-if="!project.is_archived"
                        :disabled="Boolean(processingAction)"
                        @select="emit('archive')"
                    >
                        <Spinner v-if="processingAction === 'archive'" />
                        <Archive v-else class="size-4" aria-hidden="true" />
                        {{ t('projects.show.actions.archive') }}
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-else
                        :disabled="Boolean(processingAction)"
                        @select="emit('restore')"
                    >
                        <Spinner v-if="processingAction === 'restore'" />
                        <RotateCcw v-else class="size-4" aria-hidden="true" />
                        {{ t('projects.show.actions.restore') }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <Button
                type="button"
                size="lg"
                class="min-h-12 pointer-coarse:min-h-13"
                :disabled="Boolean(processingAction) || project.is_archived"
                @click="emit('newTask')"
            >
                <Plus class="size-4" aria-hidden="true" />
                {{ t('projects.show.actions.new_task') }}
            </Button>
        </template>

        <template #metrics>
            <WorkspaceMetric
                :label="t('projects.show.metrics.total')"
                :value="formatNumber(metrics.total)"
                :icon="ListChecks"
                tone="orange"
            />
            <WorkspaceMetric
                :label="t('projects.show.metrics.open')"
                :value="formatNumber(metrics.open)"
                :icon="CircleDotDashed"
                tone="blue"
            />
            <WorkspaceMetric
                :label="t('projects.show.metrics.completion')"
                :value="`${formatNumber(metrics.completion_rate)}%`"
                :icon="CheckCircle2"
                tone="emerald"
            />
            <WorkspaceMetric
                :label="t('projects.show.metrics.attention')"
                :value="formatNumber(metrics.attention)"
                :icon="TriangleAlert"
                :tone="metrics.attention > 0 ? 'orange' : 'slate'"
            />
        </template>
    </WorkspacePageHeader>
</template>
