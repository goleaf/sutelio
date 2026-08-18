<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Archive,
    Boxes,
    FolderKanban,
    Megaphone,
    Palette,
    Plus,
    Rocket,
    Server,
    Smartphone,
    Sparkles,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import type { Component } from 'vue';
import ProjectCreateDialog from '@/components/project/ProjectCreateDialog.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import IconTile from '@/components/shared/IconTile.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import WorkspaceSegmentedButton from '@/components/shared/WorkspaceSegmentedButton.vue';
import WorkspaceSegmentedControl from '@/components/shared/WorkspaceSegmentedControl.vue';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { show as projectShow } from '@/routes/projects';
import type { Project, Workspace } from '@/types/models';

type ProjectFilter = 'all' | 'active' | 'archived';
type ProjectIndexItem = Pick<
    Project,
    | 'id'
    | 'workspace_id'
    | 'name'
    | 'description'
    | 'color'
    | 'icon'
    | 'is_archived'
    | 'todos_count'
    | 'updated_at'
>;

const props = defineProps<{
    projects: { data: ProjectIndexItem[] };
    workspace: Workspace;
}>();

const { copy, formatDate, formatNumber } = useWorkspaceUi();
const showCreateDialog = ref(false);
const activeFilter = ref<ProjectFilter>('all');

const activeProjects = computed(() =>
    props.projects.data.filter((project) => !project.is_archived),
);
const archivedProjects = computed(() =>
    props.projects.data.filter((project) => project.is_archived),
);
const totalTasks = computed(() =>
    props.projects.data.reduce(
        (total, project) => total + (project.todos_count ?? 0),
        0,
    ),
);
const visibleProjects = computed(() => {
    if (activeFilter.value === 'active') {
        return activeProjects.value;
    }

    if (activeFilter.value === 'archived') {
        return archivedProjects.value;
    }

    return props.projects.data;
});
const filters = computed(() => [
    {
        value: 'all' as const,
        label: copy.value.common.all,
        count: props.projects.data.length,
    },
    {
        value: 'active' as const,
        label: copy.value.projects.active,
        count: activeProjects.value.length,
    },
    {
        value: 'archived' as const,
        label: copy.value.projects.archived,
        count: archivedProjects.value.length,
    },
]);

function projectIcon(icon: string): Component {
    return (
        {
            rocket: Rocket,
            palette: Palette,
            smartphone: Smartphone,
            megaphone: Megaphone,
            server: Server,
            archive: Archive,
        }[icon] ?? FolderKanban
    );
}

function openCreateDialog(): void {
    if (props.workspace.id) {
        showCreateDialog.value = true;
    }
}
</script>

<template>
    <div>
        <Head :title="copy.projects.title" />

        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="copy.projects.collection"
                :title="copy.projects.title"
                :description="copy.projects.description"
            >
                <template #icon>
                    <FolderKanban aria-hidden="true" />
                </template>

                <template #actions>
                    <Button
                        size="lg"
                        :disabled="!workspace.id"
                        @click="openCreateDialog"
                    >
                        <Plus class="size-4" aria-hidden="true" />
                        {{ copy.projects.new_project }}
                    </Button>
                </template>

                <template #metrics>
                    <WorkspaceMetric
                        :label="copy.projects.total"
                        :value="formatNumber(projects.data.length)"
                        :icon="Boxes"
                        tone="orange"
                    />
                    <WorkspaceMetric
                        :label="copy.projects.active"
                        :value="formatNumber(activeProjects.length)"
                        :icon="Sparkles"
                        tone="emerald"
                    />
                    <WorkspaceMetric
                        :label="copy.projects.task_count"
                        :value="formatNumber(totalTasks)"
                        :icon="FolderKanban"
                        tone="blue"
                    />
                </template>
            </WorkspacePageHeader>

            <section
                class="rounded-panel border border-border/80 bg-card p-4 shadow-panel sm:p-6"
            >
                <div
                    class="flex flex-col gap-4 border-b border-border/70 pb-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-[0.9375rem] leading-5 font-semibold text-orange-700"
                        >
                            {{ copy.projects.workspace }}
                        </p>
                        <h2 class="mt-1.5 text-lg font-semibold tracking-tight">
                            {{ workspace.name }}
                        </h2>
                    </div>

                    <WorkspaceSegmentedControl :label="copy.common.filters">
                        <WorkspaceSegmentedButton
                            v-for="filter in filters"
                            :key="filter.value"
                            role="tab"
                            :aria-selected="activeFilter === filter.value"
                            :active="activeFilter === filter.value"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                            <span
                                class="text-[0.9375rem] leading-5 tabular-nums opacity-65"
                            >
                                {{ formatNumber(filter.count) }}
                            </span>
                        </WorkspaceSegmentedButton>
                    </WorkspaceSegmentedControl>
                </div>

                <div
                    v-if="visibleProjects.length"
                    class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <Link
                        v-for="(project, index) in visibleProjects"
                        :key="project.id"
                        data-slot="project-card"
                        :href="projectShow({ workspace, project })"
                        prefetch
                        class="group relative min-h-64 cursor-pointer overflow-hidden rounded-[1.35rem] border border-border/80 bg-background p-5 transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-orange-500/30 hover:shadow-[0_24px_50px_-38px_rgba(255,96,56,0.5)] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none sm:p-6"
                        :aria-label="`${copy.projects.open_project}: ${project.name}`"
                    >
                        <span
                            class="absolute inset-y-0 left-0 w-1.5"
                            :style="{ backgroundColor: project.color }"
                            aria-hidden="true"
                        />
                        <span
                            class="absolute -right-5 -bottom-11 text-[8.5rem] leading-none font-semibold tracking-[-0.1em] text-foreground/[0.025] select-none"
                            aria-hidden="true"
                        >
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>

                        <div class="relative flex h-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <IconTile tone="brand" size="lg">
                                    <component
                                        :is="projectIcon(project.icon)"
                                    />
                                </IconTile>
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-1 text-[0.9375rem] leading-5 font-semibold',
                                        project.is_archived
                                            ? 'bg-muted text-muted-foreground'
                                            : 'bg-emerald-500/10 text-emerald-700',
                                    ]"
                                >
                                    {{
                                        project.is_archived
                                            ? copy.projects.archived
                                            : copy.projects.active
                                    }}
                                </span>
                            </div>

                            <div class="mt-7">
                                <h3
                                    class="text-lg font-semibold tracking-[-0.02em] group-hover:text-orange-700"
                                >
                                    {{ project.name }}
                                </h3>
                                <p
                                    class="mt-2 line-clamp-3 text-base leading-7 text-muted-foreground"
                                >
                                    {{
                                        project.description ??
                                        copy.projects.no_description
                                    }}
                                </p>
                            </div>

                            <div
                                class="mt-auto flex items-end justify-between gap-4 pt-7"
                            >
                                <div>
                                    <p
                                        class="text-2xl font-semibold tracking-tight tabular-nums"
                                    >
                                        {{
                                            formatNumber(
                                                project.todos_count ?? 0,
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-[0.9375rem] leading-6 text-muted-foreground"
                                    >
                                        {{ copy.common.tasks }}
                                    </p>
                                </div>
                                <p
                                    class="text-right text-[0.9375rem] leading-6 text-muted-foreground"
                                >
                                    {{
                                        formatDate(project.updated_at, {
                                            month: 'short',
                                            day: 'numeric',
                                            year: 'numeric',
                                        })
                                    }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>

                <EmptyState
                    v-else
                    :title="copy.projects.empty_title"
                    :description="copy.projects.empty_description"
                    :action-label="
                        activeFilter === 'all'
                            ? copy.projects.create_first
                            : undefined
                    "
                    @action="openCreateDialog"
                >
                    <template #icon>
                        <FolderKanban class="size-8" aria-hidden="true" />
                    </template>
                </EmptyState>
            </section>
        </WorkspacePageFrame>

        <ProjectCreateDialog
            :open="showCreateDialog"
            :workspace-id="workspace.id"
            @close="showCreateDialog = false"
            @created="showCreateDialog = false"
        />
    </div>
</template>
