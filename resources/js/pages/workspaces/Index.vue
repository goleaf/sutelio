<script setup lang="ts">
import { Head, Link, router, useHttp, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    CheckSquare,
    Copy,
    Folder,
    MoreHorizontal,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Settings2,
    Trash2,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import IconTile from '@/components/shared/IconTile.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
    filterAndSortWorkspacePortfolio,
    workspacePortfolioTotals,
} from '@/components/workspace/workspace-stewardship';
import type { WorkspacePortfolioSort } from '@/components/workspace/workspace-stewardship';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import {
    copy,
    create,
    danger,
    edit,
    show as showWorkspace,
    switchMethod,
} from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

interface WorkspaceResponse {
    workspace: Workspace;
}

const props = defineProps<{ workspaces: { data: Workspace[] } }>();

const page = usePage();
const toast = useToast();
const { formatNumber, locale, t } = useUi();
const searchQuery = ref('');
const sortOrder = ref<WorkspacePortfolioSort>('name_asc');
const switchingWorkspaceId = ref<string | null>(null);
const switchRequest = useHttp<Record<string, never>, WorkspaceResponse>({});

const filteredWorkspaces = computed(() =>
    filterAndSortWorkspacePortfolio(
        props.workspaces.data,
        searchQuery.value,
        sortOrder.value,
        locale.value,
    ),
);
const portfolioTotals = computed(() =>
    workspacePortfolioTotals(props.workspaces.data),
);

function isCurrentWorkspace(workspace: Workspace): boolean {
    return (
        workspace.is_current ??
        page.props.navigation.currentWorkspace?.id === workspace.id
    );
}

function canUpdateWorkspace(workspace: Workspace): boolean {
    return workspace.permissions?.update ?? false;
}

function canDuplicateWorkspace(workspace: Workspace): boolean {
    return workspace.permissions?.duplicate ?? false;
}

function canDeleteWorkspace(workspace: Workspace): boolean {
    return workspace.permissions?.delete ?? false;
}

function reloadPortfolio(): void {
    router.reload({ only: ['workspaces', 'navigation'] });
}

async function switchWorkspace(
    workspace: Workspace,
    reload = true,
): Promise<boolean> {
    if (isCurrentWorkspace(workspace)) {
        return true;
    }

    switchingWorkspaceId.value = workspace.id;

    try {
        await switchRequest.submit(switchMethod(workspace));
        toast.success(t('workspaces.switched', { name: workspace.name }));

        if (reload) {
            reloadPortfolio();
        }

        return true;
    } catch {
        toast.error(t('workspaces.switch_failed'));

        return false;
    } finally {
        switchingWorkspaceId.value = null;
    }
}

function manageWorkspace(workspace: Workspace): void {
    router.visit(showWorkspace(workspace).url);
}

function editWorkspace(workspace: Workspace): void {
    router.visit(edit(workspace).url);
}

function copyWorkspace(workspace: Workspace): void {
    router.visit(copy(workspace).url);
}

function manageWorkspaceDanger(workspace: Workspace): void {
    router.visit(danger(workspace).url);
}
</script>

<template>
    <div>
        <Head :title="t('workspaces.title')" />

        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="t('workspaces.eyebrow')"
                :title="t('workspaces.title')"
                :description="t('workspaces.page_description')"
            >
                <template #icon>
                    <Building2 aria-hidden="true" />
                </template>

                <template #actions>
                    <Button as-child size="lg" data-workspace-create>
                        <Link :href="create().url">
                            <Plus class="size-4" aria-hidden="true" />
                            {{ t('workspaces.new') }}
                        </Link>
                    </Button>
                </template>

                <template #metrics>
                    <WorkspaceMetric
                        :label="t('workspaces.title')"
                        :value="formatNumber(portfolioTotals.workspaces)"
                        :icon="Building2"
                        tone="orange"
                    />
                    <WorkspaceMetric
                        :label="t('workspaces.members')"
                        :value="formatNumber(portfolioTotals.members)"
                        :icon="Users"
                        tone="emerald"
                    />
                    <WorkspaceMetric
                        :label="t('workspaces.projects')"
                        :value="formatNumber(portfolioTotals.projects)"
                        :icon="Folder"
                        tone="blue"
                    />
                    <WorkspaceMetric
                        :label="t('workspaces.tasks')"
                        :value="formatNumber(portfolioTotals.tasks)"
                        :icon="CheckSquare"
                        tone="violet"
                    />
                </template>
            </WorkspacePageHeader>

            <section
                class="rounded-panel border border-border/80 bg-card p-4 shadow-panel sm:p-6"
            >
                <div
                    v-if="workspaces.data.length"
                    class="grid gap-3 border-b border-border/70 pb-5 sm:grid-cols-[minmax(0,1fr)_13rem]"
                >
                    <div class="relative">
                        <Label for="workspace-search" class="sr-only">
                            {{ t('workspaces.search_label') }}
                        </Label>
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            id="workspace-search"
                            v-model="searchQuery"
                            type="search"
                            :placeholder="t('workspaces.search_placeholder')"
                            class="pl-10"
                        />
                    </div>
                    <div>
                        <Label for="workspace-sort" class="sr-only">
                            {{ t('workspaces.sort_label') }}
                        </Label>
                        <Select v-model="sortOrder">
                            <SelectTrigger
                                id="workspace-sort"
                                class="min-h-12 w-full pointer-coarse:min-h-13"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="name_asc">
                                    {{ t('workspaces.sort.name_asc') }}
                                </SelectItem>
                                <SelectItem value="name_desc">
                                    {{ t('workspaces.sort.name_desc') }}
                                </SelectItem>
                                <SelectItem value="newest">
                                    {{ t('workspaces.sort.newest') }}
                                </SelectItem>
                                <SelectItem value="oldest">
                                    {{ t('workspaces.sort.oldest') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <p
                    v-if="workspaces.data.length"
                    class="mt-4 text-[0.9375rem] leading-5 text-muted-foreground"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    {{
                        t('workspaces.result_summary', {
                            visible: formatNumber(filteredWorkspaces.length),
                            total: formatNumber(portfolioTotals.workspaces),
                        })
                    }}
                </p>

                <div
                    v-if="filteredWorkspaces.length"
                    class="mt-5 grid items-start gap-4 lg:grid-cols-2"
                >
                    <Card
                        v-for="(workspace, index) in filteredWorkspaces"
                        :key="workspace.id"
                        class="group relative flex flex-col overflow-hidden bg-background transition-[border-color,box-shadow,transform] hover:-translate-y-0.5 hover:border-orange-500/25 hover:shadow-[0_24px_50px_-38px_rgba(255,96,56,0.5)] motion-reduce:transform-none"
                        :class="
                            isCurrentWorkspace(workspace)
                                ? 'border-status-success-border ring-1 ring-status-success-border/40 lg:col-span-2'
                                : ''
                        "
                    >
                        <span
                            class="absolute inset-y-0 left-0 w-1.5"
                            :class="
                                isCurrentWorkspace(workspace)
                                    ? 'bg-status-success-icon'
                                    : 'bg-orange-500'
                            "
                            aria-hidden="true"
                        />
                        <span
                            class="absolute -right-4 -bottom-9 text-8xl leading-none font-semibold tracking-[-0.1em] text-foreground/[0.025] select-none"
                            aria-hidden="true"
                        >
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>
                        <CardHeader class="relative gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <IconTile tone="brand" size="md">
                                    <Building2 />
                                </IconTile>
                                <Badge
                                    v-if="isCurrentWorkspace(workspace)"
                                    variant="outline"
                                    class="border-status-success-border bg-status-success-surface text-[0.9375rem] whitespace-normal text-status-success-text"
                                >
                                    <CheckCircle2 aria-hidden="true" />
                                    {{ t('workspaces.current') }}
                                </Badge>
                            </div>
                            <div class="space-y-2">
                                <CardTitle
                                    as="h2"
                                    class="tracking-[-0.02em] break-words"
                                >
                                    {{ workspace.name }}
                                </CardTitle>
                                <p
                                    class="line-clamp-3 text-base leading-6 break-words text-muted-foreground"
                                >
                                    {{
                                        workspace.description ??
                                        t('workspaces.no_description')
                                    }}
                                </p>
                                <p
                                    v-if="workspace.owner?.name"
                                    class="flex flex-wrap items-center gap-1.5 text-[0.9375rem] leading-5 text-muted-foreground"
                                >
                                    <span class="font-medium text-foreground">
                                        {{ t('workspaces.owner') }}:
                                    </span>
                                    <span class="break-words">
                                        {{ workspace.owner.name }}
                                    </span>
                                </p>
                            </div>
                        </CardHeader>
                        <CardContent class="relative mt-auto space-y-4">
                            <div
                                data-workspace-portfolio-metrics
                                class="grid grid-cols-1 gap-2 rounded-xl border border-border/70 bg-muted/25 p-2 sm:grid-cols-3"
                            >
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-background/70 px-3 py-3 text-base sm:flex-col sm:justify-center sm:text-center"
                                    :title="t('workspaces.members')"
                                >
                                    <Users
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <span class="font-medium tabular-nums">
                                        {{
                                            formatNumber(
                                                workspace.members_count ?? 0,
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="ml-auto text-[0.9375rem] leading-5 text-muted-foreground sm:ml-0"
                                    >
                                        {{ t('workspaces.members') }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-background/70 px-3 py-3 text-base sm:flex-col sm:justify-center sm:text-center"
                                    :title="t('workspaces.projects')"
                                >
                                    <Folder
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <span class="font-medium tabular-nums">
                                        {{
                                            formatNumber(
                                                workspace.projects_count ?? 0,
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="ml-auto text-[0.9375rem] leading-5 text-muted-foreground sm:ml-0"
                                    >
                                        {{ t('workspaces.projects') }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg bg-background/70 px-3 py-3 text-base sm:flex-col sm:justify-center sm:text-center"
                                    :title="t('workspaces.tasks')"
                                >
                                    <CheckSquare
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <span class="font-medium tabular-nums">
                                        {{
                                            formatNumber(
                                                workspace.todos_count ?? 0,
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="ml-auto text-[0.9375rem] leading-5 text-muted-foreground sm:ml-0"
                                    >
                                        {{ t('workspaces.tasks') }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-2"
                                :aria-label="
                                    t('workspaces.actions_label', {
                                        name: workspace.name,
                                    })
                                "
                            >
                                <Button
                                    size="sm"
                                    class="min-h-12 flex-1 sm:flex-none pointer-coarse:min-h-13"
                                    :disabled="switchRequest.processing"
                                    @click="manageWorkspace(workspace)"
                                >
                                    <Settings2 aria-hidden="true" />
                                    {{ t('workspaces.actions.manage') }}
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="min-h-12 flex-1 sm:flex-none pointer-coarse:min-h-13"
                                    :disabled="
                                        switchRequest.processing ||
                                        isCurrentWorkspace(workspace)
                                    "
                                    @click="switchWorkspace(workspace)"
                                >
                                    <Spinner
                                        v-if="
                                            switchingWorkspaceId ===
                                            workspace.id
                                        "
                                    />
                                    <CheckCircle2
                                        v-else-if="
                                            isCurrentWorkspace(workspace)
                                        "
                                        aria-hidden="true"
                                    />
                                    <RefreshCw v-else aria-hidden="true" />
                                    {{
                                        isCurrentWorkspace(workspace)
                                            ? t('workspaces.current')
                                            : switchingWorkspaceId ===
                                                workspace.id
                                              ? t('workspaces.switching')
                                              : t('workspaces.actions.switch')
                                    }}
                                </Button>
                                <DropdownMenu
                                    v-if="
                                        canUpdateWorkspace(workspace) ||
                                        canDuplicateWorkspace(workspace) ||
                                        canDeleteWorkspace(workspace)
                                    "
                                >
                                    <DropdownMenuTrigger :as-child="true">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="size-12 shrink-0 pointer-coarse:size-13"
                                            :aria-label="
                                                t('workspaces.actions_label', {
                                                    name: workspace.name,
                                                })
                                            "
                                        >
                                            <MoreHorizontal
                                                aria-hidden="true"
                                            />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            v-if="canUpdateWorkspace(workspace)"
                                            @select="editWorkspace(workspace)"
                                        >
                                            <Pencil aria-hidden="true" />
                                            {{ t('workspaces.actions.edit') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                canDuplicateWorkspace(workspace)
                                            "
                                            @select="copyWorkspace(workspace)"
                                        >
                                            <Copy aria-hidden="true" />
                                            {{
                                                t(
                                                    'workspaces.actions.duplicate',
                                                )
                                            }}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator
                                            v-if="
                                                canDeleteWorkspace(workspace) &&
                                                (canUpdateWorkspace(
                                                    workspace,
                                                ) ||
                                                    canDuplicateWorkspace(
                                                        workspace,
                                                    ))
                                            "
                                        />
                                        <DropdownMenuItem
                                            v-if="canDeleteWorkspace(workspace)"
                                            class="text-destructive focus:text-destructive"
                                            @select="
                                                manageWorkspaceDanger(workspace)
                                            "
                                        >
                                            <Trash2 aria-hidden="true" />
                                            {{ t('workspaces.actions.delete') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <EmptyState
                    v-else-if="workspaces.data.length"
                    compact
                    :title="t('workspaces.no_results')"
                    :description="t('workspaces.no_results_description')"
                >
                    <template #icon>
                        <Search class="size-7" aria-hidden="true" />
                    </template>
                </EmptyState>

                <EmptyState
                    v-else
                    :title="t('workspaces.empty')"
                    :description="t('workspaces.empty_description')"
                    :action-label="t('workspaces.create')"
                    @action="router.visit(create().url)"
                >
                    <template #icon>
                        <Building2 class="size-7" aria-hidden="true" />
                    </template>
                </EmptyState>
            </section>
        </WorkspacePageFrame>
    </div>
</template>
