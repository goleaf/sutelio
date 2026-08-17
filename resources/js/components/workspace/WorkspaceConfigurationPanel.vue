<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import {
    ListChecks,
    MoreHorizontal,
    Palette,
    Pencil,
    Plus,
    Tag as TagIcon,
    Trash2,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import SearchField from '@/components/shared/SearchField.vue';
import WorkspaceConfirmDialog from '@/components/shared/WorkspaceConfirmDialog.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label as FormLabel } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { workspaceTaxonomyCounts } from '@/components/workspace/workspace-stewardship';
import type { WorkspaceTaxonomySection } from '@/components/workspace/workspace-stewardship';
import WorkspaceTaskDefinitionsPanel from '@/components/workspace/WorkspaceTaskDefinitionsPanel.vue';
import WorkspaceTaxonomySwitcher from '@/components/workspace/WorkspaceTaxonomySwitcher.vue';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import type {
    Label,
    Tag,
    TaskPriorityDefinition,
    TaskStatusDefinition,
    Workspace,
    WorkspaceMetadataRouteUrls,
} from '@/types/models';

type DeleteTarget = { kind: 'label'; item: Label } | { kind: 'tag'; item: Tag };

const props = defineProps<{
    workspace: Workspace;
    labels: Label[];
    tags: Tag[];
    taskStatuses: TaskStatusDefinition[];
    taskPriorities: TaskPriorityDefinition[];
    locale: string;
    routes: WorkspaceMetadataRouteUrls;
}>();

const toast = useToast();
const { formatNumber, t } = useUi();
const searchQuery = ref('');
const activeSection = ref<WorkspaceTaxonomySection>('statuses');
const editingLabel = ref<Label | null>(null);
const editingTag = ref<Tag | null>(null);
const deleteTarget = ref<DeleteTarget | null>(null);
const metadataDialogTrigger = ref<HTMLElement | null>(null);
const labelForm = useHttp<{ name: string; color: string }>({
    name: '',
    color: '#6366f1',
});
const tagForm = useHttp<{ name: string }>({ name: '' });
const editLabelForm = useHttp<{ name: string; color: string }>({
    name: '',
    color: '#6366f1',
});
const editTagForm = useHttp<{ name: string }>({ name: '' });
const deleteRequest = useHttp<Record<string, never>>({});

const canManage = computed(
    () => props.workspace.permissions?.manage_task_configuration === true,
);
const taxonomyCounts = computed(() =>
    workspaceTaxonomyCounts({
        statuses: props.taskStatuses,
        priorities: props.taskPriorities,
        labels: props.labels,
        tags: props.tags,
    }),
);
const searchPlaceholder = computed(() =>
    t('workspaces.management.configuration.search_active', {
        section: t(
            `workspaces.management.configuration.${activeSection.value}.title`,
        ),
    }),
);
const normalizedSearch = computed(() =>
    searchQuery.value.trim().toLocaleLowerCase(props.locale),
);
const filteredLabels = computed(() => {
    if (!normalizedSearch.value) {
        return props.labels;
    }

    return props.labels.filter((label) =>
        label.name
            .toLocaleLowerCase(props.locale)
            .includes(normalizedSearch.value),
    );
});
const filteredTags = computed(() => {
    if (!normalizedSearch.value) {
        return props.tags;
    }

    return props.tags.filter((tag) =>
        tag.name
            .toLocaleLowerCase(props.locale)
            .includes(normalizedSearch.value),
    );
});
const deleteTitle = computed(() =>
    deleteTarget.value?.kind === 'label'
        ? t('workspaces.management.configuration.labels.delete_title')
        : t('workspaces.management.configuration.tags.delete_title'),
);
const deleteDescription = computed(() => {
    const target = deleteTarget.value;

    if (!target) {
        return '';
    }

    return t(
        `workspaces.management.configuration.${target.kind === 'label' ? 'labels' : 'tags'}.delete_description`,
        {
            name: target.item.name,
            count: formatNumber(target.item.todos_count ?? 0),
        },
    );
});

function activeSectionHeadingId(): string {
    return {
        statuses: 'workspace-definition-status-title',
        priorities: 'workspace-definition-priority-title',
        labels: 'workspace-labels-title',
        tags: 'workspace-tags-title',
    }[activeSection.value];
}

function focusActiveSectionHeading(): void {
    void nextTick(() => {
        document.getElementById(activeSectionHeadingId())?.focus({
            preventScroll: true,
        });
    });
}

function reloadMetadata(onSuccess?: () => void): void {
    router.reload({ only: ['workspace', 'labels', 'tags'], onSuccess });
}

function captureMetadataDialogTrigger(event: MouseEvent): void {
    metadataDialogTrigger.value =
        event.currentTarget instanceof HTMLElement ? event.currentTarget : null;
}

function restoreMetadataDialogFocus(): void {
    const trigger = metadataDialogTrigger.value;
    metadataDialogTrigger.value = null;

    void nextTick(() => {
        const fallback = document.getElementById(activeSectionHeadingId());
        const target = trigger?.isConnected ? trigger : fallback;

        target?.focus({ preventScroll: true });
    });
}

async function selectSection(section: WorkspaceTaxonomySection): Promise<void> {
    activeSection.value = section;
    searchQuery.value = '';
    cancelEditingLabel();
    cancelEditingTag();

    if (!deleteRequest.processing) {
        deleteTarget.value = null;
    }

    await nextTick();
    document.getElementById(activeSectionHeadingId())?.focus({
        preventScroll: true,
    });
}

async function createLabel(): Promise<void> {
    labelForm.name = labelForm.name.trim();

    if (!labelForm.name) {
        return;
    }

    try {
        await labelForm.post(props.routes.storeLabel);

        if (!labelForm.wasSuccessful) {
            return;
        }

        toast.success(t('workspaces.management.configuration.labels.created'));
        labelForm.resetAndClearErrors();
        labelForm.name = '';
        labelForm.color = '#6366f1';
        reloadMetadata();
    } catch {
        toast.error(
            t('workspaces.management.configuration.labels.create_failed'),
        );
    }
}

async function createTag(): Promise<void> {
    tagForm.name = tagForm.name.trim();

    if (!tagForm.name) {
        return;
    }

    try {
        await tagForm.post(props.routes.storeTag);

        if (!tagForm.wasSuccessful) {
            return;
        }

        toast.success(t('workspaces.management.configuration.tags.created'));
        tagForm.resetAndClearErrors();
        tagForm.name = '';
        reloadMetadata();
    } catch {
        toast.error(
            t('workspaces.management.configuration.tags.create_failed'),
        );
    }
}

function startEditingLabel(label: Label): void {
    editingTag.value = null;
    editingLabel.value = label;
    editLabelForm.name = label.name;
    editLabelForm.color = label.color;
    editLabelForm.clearErrors();
}

function cancelEditingLabel(): void {
    editingLabel.value = null;
    editLabelForm.resetAndClearErrors();
}

async function updateLabel(): Promise<void> {
    const label = editingLabel.value;

    if (!label) {
        return;
    }

    editLabelForm.name = editLabelForm.name.trim();

    try {
        await editLabelForm.put(props.routes.updateLabel(label.id));

        if (!editLabelForm.wasSuccessful) {
            return;
        }

        toast.success(t('workspaces.management.configuration.labels.updated'));
        cancelEditingLabel();
        reloadMetadata();
    } catch {
        toast.error(
            t('workspaces.management.configuration.labels.save_failed'),
        );
    }
}

function startEditingTag(tag: Tag): void {
    editingLabel.value = null;
    editingTag.value = tag;
    editTagForm.name = tag.name;
    editTagForm.clearErrors();
}

function cancelEditingTag(): void {
    editingTag.value = null;
    editTagForm.resetAndClearErrors();
}

async function updateTag(): Promise<void> {
    const tag = editingTag.value;

    if (!tag) {
        return;
    }

    editTagForm.name = editTagForm.name.trim();

    try {
        await editTagForm.put(props.routes.updateTag(tag.id));

        if (!editTagForm.wasSuccessful) {
            return;
        }

        toast.success(t('workspaces.management.configuration.tags.updated'));
        cancelEditingTag();
        reloadMetadata();
    } catch {
        toast.error(t('workspaces.management.configuration.tags.save_failed'));
    }
}

function setDeleteConfirmation(open: boolean): void {
    if (!open && !deleteRequest.processing) {
        deleteTarget.value = null;
        restoreMetadataDialogFocus();
    }
}

async function deleteMetadata(): Promise<void> {
    const target = deleteTarget.value;

    if (!target) {
        return;
    }

    const url =
        target.kind === 'label'
            ? props.routes.deleteLabel(target.item.id)
            : props.routes.deleteTag(target.item.id);

    try {
        await deleteRequest.delete(url);

        if (!deleteRequest.wasSuccessful) {
            toast.error(
                t(
                    `workspaces.management.configuration.${target.kind === 'label' ? 'labels' : 'tags'}.delete_failed`,
                ),
            );

            return;
        }

        toast.success(
            t(
                `workspaces.management.configuration.${target.kind === 'label' ? 'labels' : 'tags'}.deleted`,
            ),
        );
        deleteTarget.value = null;
        metadataDialogTrigger.value = null;
        reloadMetadata(focusActiveSectionHeading);
    } catch {
        toast.error(
            t(
                `workspaces.management.configuration.${target.kind === 'label' ? 'labels' : 'tags'}.delete_failed`,
            ),
        );
    }
}
</script>

<template>
    <section class="space-y-6" aria-labelledby="workspace-configuration-title">
        <div
            class="flex flex-col gap-4 rounded-2xl border border-orange-500/15 bg-orange-500/[0.04] p-5 sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="max-w-3xl">
                <LeadingIconHeading tile tile-tone="brand">
                    <template #icon>
                        <ListChecks />
                    </template>

                    <h2
                        id="workspace-configuration-title"
                        class="text-xl font-semibold tracking-tight"
                    >
                        {{ t('workspaces.management.configuration.title') }}
                    </h2>
                    <p class="text-sm leading-6 text-muted-foreground">
                        {{
                            t('workspaces.management.configuration.description')
                        }}
                    </p>
                </LeadingIconHeading>
            </div>
            <SearchField
                id="metadata-search"
                v-model="searchQuery"
                class="w-full sm:max-w-sm"
                :label="t('workspaces.management.configuration.search_label')"
                :placeholder="searchPlaceholder"
            />
        </div>

        <Alert v-if="!canManage">
            <ListChecks aria-hidden="true" />
            <AlertTitle>
                {{ t('workspaces.management.configuration.title') }}
            </AlertTitle>
            <AlertDescription>
                {{ t('workspaces.management.configuration.read_only') }}
            </AlertDescription>
        </Alert>

        <WorkspaceTaxonomySwitcher
            :active-section="activeSection"
            :counts="taxonomyCounts"
            @update:active-section="selectSection"
        />

        <WorkspaceTaskDefinitionsPanel
            v-if="
                activeSection === 'statuses' || activeSection === 'priorities'
            "
            :active-kind="activeSection === 'statuses' ? 'status' : 'priority'"
            :workspace="workspace"
            :statuses="taskStatuses"
            :priorities="taskPriorities"
            :search="searchQuery"
            :locale="locale"
            :routes="routes"
        />

        <Card v-else-if="activeSection === 'labels'" class="border-sky-500/15">
            <CardHeader>
                <LeadingIconHeading tile tile-tone="information">
                    <template #icon>
                        <Palette />
                    </template>

                    <CardTitle>
                        <span
                            id="workspace-labels-title"
                            tabindex="-1"
                            class="block rounded-sm focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-background focus:outline-none"
                        >
                            {{
                                t(
                                    'workspaces.management.configuration.labels.title',
                                )
                            }}
                        </span>
                    </CardTitle>
                    <CardDescription>
                        {{
                            t(
                                'workspaces.management.configuration.labels.description',
                            )
                        }}
                    </CardDescription>
                </LeadingIconHeading>
            </CardHeader>
            <CardContent class="space-y-5">
                <form
                    v-if="canManage"
                    class="grid gap-3 rounded-xl border bg-muted/20 p-4 sm:grid-cols-[minmax(0,1fr)_4rem_auto] sm:items-end"
                    @submit.prevent="createLabel"
                >
                    <div class="space-y-2">
                        <FormLabel for="new-label-name">
                            {{
                                t(
                                    'workspaces.management.configuration.labels.name',
                                )
                            }}
                        </FormLabel>
                        <Input
                            id="new-label-name"
                            v-model="labelForm.name"
                            :placeholder="
                                t(
                                    'workspaces.management.configuration.labels.name_placeholder',
                                )
                            "
                            :disabled="labelForm.processing"
                            :aria-invalid="Boolean(labelForm.errors.name)"
                            @input="labelForm.clearErrors('name')"
                        />
                        <InputError :message="labelForm.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <FormLabel for="new-label-color">
                            {{
                                t(
                                    'workspaces.management.configuration.labels.color',
                                )
                            }}
                        </FormLabel>
                        <Input
                            id="new-label-color"
                            v-model="labelForm.color"
                            type="color"
                            class="h-10 w-full cursor-pointer p-1"
                            :disabled="labelForm.processing"
                        />
                    </div>
                    <Button
                        type="submit"
                        :disabled="
                            labelForm.processing || !labelForm.name.trim()
                        "
                    >
                        <Spinner v-if="labelForm.processing" />
                        <Plus v-else aria-hidden="true" />
                        {{
                            t(
                                'workspaces.management.configuration.labels.create',
                            )
                        }}
                    </Button>
                </form>

                <ul
                    v-if="filteredLabels.length"
                    class="divide-y rounded-xl border"
                >
                    <li
                        v-for="label in filteredLabels"
                        :key="label.id"
                        class="p-4"
                    >
                        <form
                            v-if="editingLabel?.id === label.id"
                            class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_4rem_auto] sm:items-start"
                            @submit.prevent="updateLabel"
                        >
                            <div class="space-y-2">
                                <FormLabel :for="`edit-label-${label.id}`">
                                    {{
                                        t(
                                            'workspaces.management.configuration.labels.name',
                                        )
                                    }}
                                </FormLabel>
                                <Input
                                    :id="`edit-label-${label.id}`"
                                    v-model="editLabelForm.name"
                                    :disabled="editLabelForm.processing"
                                    :aria-invalid="
                                        Boolean(editLabelForm.errors.name)
                                    "
                                    @input="editLabelForm.clearErrors('name')"
                                />
                                <InputError
                                    :message="editLabelForm.errors.name"
                                />
                            </div>
                            <div class="space-y-2">
                                <FormLabel
                                    :for="`edit-label-color-${label.id}`"
                                >
                                    {{
                                        t(
                                            'workspaces.management.configuration.labels.color',
                                        )
                                    }}
                                </FormLabel>
                                <Input
                                    :id="`edit-label-color-${label.id}`"
                                    v-model="editLabelForm.color"
                                    type="color"
                                    class="h-10 w-full cursor-pointer p-1"
                                    :disabled="editLabelForm.processing"
                                />
                            </div>
                            <div class="flex gap-2 sm:pt-7">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="editLabelForm.processing"
                                    @click="cancelEditingLabel"
                                >
                                    {{ t('common.actions.cancel') }}
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="
                                        editLabelForm.processing ||
                                        !editLabelForm.name.trim()
                                    "
                                >
                                    <Spinner v-if="editLabelForm.processing" />
                                    {{ t('common.actions.save') }}
                                </Button>
                            </div>
                        </form>
                        <div
                            v-else
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="size-4 shrink-0 rounded-full border border-black/10 shadow-sm"
                                    :style="{
                                        backgroundColor: label.color,
                                    }"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0">
                                    <p class="font-medium break-words">
                                        {{ label.name }}
                                    </p>
                                    <Badge variant="outline" class="mt-1">
                                        {{
                                            t(
                                                'workspaces.management.configuration.tasks_count',
                                                {
                                                    count: formatNumber(
                                                        label.todos_count ?? 0,
                                                    ),
                                                },
                                            )
                                        }}
                                    </Badge>
                                </div>
                            </div>
                            <DropdownMenu
                                v-if="
                                    label.permissions?.update ||
                                    label.permissions?.delete
                                "
                            >
                                <DropdownMenuTrigger :as-child="true">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-11 shrink-0"
                                        :aria-label="
                                            t('workspaces.actions_label', {
                                                name: label.name,
                                            })
                                        "
                                        @click="captureMetadataDialogTrigger"
                                    >
                                        <MoreHorizontal aria-hidden="true" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        v-if="label.permissions?.update"
                                        @select="startEditingLabel(label)"
                                    >
                                        <Pencil aria-hidden="true" />
                                        {{ t('common.actions.edit') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator
                                        v-if="
                                            label.permissions?.update &&
                                            label.permissions?.delete
                                        "
                                    />
                                    <DropdownMenuItem
                                        v-if="label.permissions?.delete"
                                        class="text-destructive focus:text-destructive"
                                        @select="
                                            deleteTarget = {
                                                kind: 'label',
                                                item: label,
                                            }
                                        "
                                    >
                                        <Trash2 aria-hidden="true" />
                                        {{ t('common.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </li>
                </ul>
                <p
                    v-else
                    class="rounded-xl border border-dashed px-4 py-10 text-center text-sm text-muted-foreground"
                >
                    {{ t('workspaces.management.configuration.labels.empty') }}
                </p>
            </CardContent>
        </Card>

        <Card v-else-if="activeSection === 'tags'" class="border-violet-500/15">
            <CardHeader>
                <LeadingIconHeading tile tile-tone="information">
                    <template #icon>
                        <TagIcon />
                    </template>

                    <CardTitle>
                        <span
                            id="workspace-tags-title"
                            tabindex="-1"
                            class="block rounded-sm focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-background focus:outline-none"
                        >
                            {{
                                t(
                                    'workspaces.management.configuration.tags.title',
                                )
                            }}
                        </span>
                    </CardTitle>
                    <CardDescription>
                        {{
                            t(
                                'workspaces.management.configuration.tags.description',
                            )
                        }}
                    </CardDescription>
                </LeadingIconHeading>
            </CardHeader>
            <CardContent class="space-y-5">
                <form
                    v-if="canManage"
                    class="grid gap-3 rounded-xl border bg-muted/20 p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end"
                    @submit.prevent="createTag"
                >
                    <div class="space-y-2">
                        <FormLabel for="new-tag-name">
                            {{
                                t(
                                    'workspaces.management.configuration.tags.name',
                                )
                            }}
                        </FormLabel>
                        <Input
                            id="new-tag-name"
                            v-model="tagForm.name"
                            :placeholder="
                                t(
                                    'workspaces.management.configuration.tags.name_placeholder',
                                )
                            "
                            :disabled="tagForm.processing"
                            :aria-invalid="Boolean(tagForm.errors.name)"
                            @input="tagForm.clearErrors('name')"
                        />
                        <InputError :message="tagForm.errors.name" />
                    </div>
                    <Button
                        type="submit"
                        :disabled="tagForm.processing || !tagForm.name.trim()"
                    >
                        <Spinner v-if="tagForm.processing" />
                        <Plus v-else aria-hidden="true" />
                        {{
                            t('workspaces.management.configuration.tags.create')
                        }}
                    </Button>
                </form>

                <ul
                    v-if="filteredTags.length"
                    class="divide-y rounded-xl border"
                >
                    <li v-for="tag in filteredTags" :key="tag.id" class="p-4">
                        <form
                            v-if="editingTag?.id === tag.id"
                            class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start"
                            @submit.prevent="updateTag"
                        >
                            <div class="space-y-2">
                                <FormLabel :for="`edit-tag-${tag.id}`">
                                    {{
                                        t(
                                            'workspaces.management.configuration.tags.name',
                                        )
                                    }}
                                </FormLabel>
                                <Input
                                    :id="`edit-tag-${tag.id}`"
                                    v-model="editTagForm.name"
                                    :disabled="editTagForm.processing"
                                    :aria-invalid="
                                        Boolean(editTagForm.errors.name)
                                    "
                                    @input="editTagForm.clearErrors('name')"
                                />
                                <InputError
                                    :message="editTagForm.errors.name"
                                />
                            </div>
                            <div class="flex gap-2 sm:pt-7">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="editTagForm.processing"
                                    @click="cancelEditingTag"
                                >
                                    {{ t('common.actions.cancel') }}
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="
                                        editTagForm.processing ||
                                        !editTagForm.name.trim()
                                    "
                                >
                                    <Spinner v-if="editTagForm.processing" />
                                    {{ t('common.actions.save') }}
                                </Button>
                            </div>
                        </form>
                        <div
                            v-else
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="font-medium break-words">
                                    #{{ tag.name }}
                                </p>
                                <Badge variant="outline" class="mt-1">
                                    {{
                                        t(
                                            'workspaces.management.configuration.tasks_count',
                                            {
                                                count: formatNumber(
                                                    tag.todos_count ?? 0,
                                                ),
                                            },
                                        )
                                    }}
                                </Badge>
                            </div>
                            <DropdownMenu
                                v-if="
                                    tag.permissions?.update ||
                                    tag.permissions?.delete
                                "
                            >
                                <DropdownMenuTrigger :as-child="true">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-11 shrink-0"
                                        :aria-label="
                                            t('workspaces.actions_label', {
                                                name: tag.name,
                                            })
                                        "
                                        @click="captureMetadataDialogTrigger"
                                    >
                                        <MoreHorizontal aria-hidden="true" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        v-if="tag.permissions?.update"
                                        @select="startEditingTag(tag)"
                                    >
                                        <Pencil aria-hidden="true" />
                                        {{ t('common.actions.edit') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator
                                        v-if="
                                            tag.permissions?.update &&
                                            tag.permissions?.delete
                                        "
                                    />
                                    <DropdownMenuItem
                                        v-if="tag.permissions?.delete"
                                        class="text-destructive focus:text-destructive"
                                        @select="
                                            deleteTarget = {
                                                kind: 'tag',
                                                item: tag,
                                            }
                                        "
                                    >
                                        <Trash2 aria-hidden="true" />
                                        {{ t('common.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </li>
                </ul>
                <p
                    v-else
                    class="rounded-xl border border-dashed px-4 py-10 text-center text-sm text-muted-foreground"
                >
                    {{ t('workspaces.management.configuration.tags.empty') }}
                </p>
            </CardContent>
        </Card>

        <WorkspaceConfirmDialog
            :open="Boolean(deleteTarget)"
            :title="deleteTitle"
            :description="deleteDescription"
            :confirm-label="t('common.actions.delete')"
            :cancel-label="t('common.actions.cancel')"
            :processing="deleteRequest.processing"
            destructive
            @update:open="setDeleteConfirmation"
            @confirm="deleteMetadata"
        />
    </section>
</template>
