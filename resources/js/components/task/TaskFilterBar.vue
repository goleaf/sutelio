<script setup lang="ts">
import {
    AlertTriangle,
    ArrowDownAZ,
    CheckCircle2,
    Columns3,
    List,
    Pin,
    Search,
    SlidersHorizontal,
    Star,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import WorkspaceSegmentedButton from '@/components/shared/WorkspaceSegmentedButton.vue';
import WorkspaceSegmentedControl from '@/components/shared/WorkspaceSegmentedControl.vue';
import {
    activeTaskFilterCount,
    clearTaskFilters,
    mergeTaskFilterState,
    taskPluralForm,
    toggleTaskFocusFilter,
} from '@/components/task/task-focus';
import type { TaskFocusFilter } from '@/components/task/task-focus';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTaskDefinitions } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import type { TodoFilters } from '@/types/api';
import type { Project, TaskDefinitionCatalog } from '@/types/models';

const props = defineProps<{
    filters: TodoFilters;
    projects: Project[];
    taskDefinitions: TaskDefinitionCatalog;
    processing: boolean;
}>();
const emit = defineEmits<{ update: [filters: TodoFilters] }>();
const { locale, t } = useUi();
const { statuses, priorities } = useTaskDefinitions(
    () => props.taskDefinitions,
);
const search = ref('');
const projectId = ref('all');
const status = ref('all');
const priority = ref('all');
const sort = ref('default');
const direction = ref<'asc' | 'desc'>('asc');
const perPage = ref<'100' | '25' | '50'>('50');
const view = ref<'board' | 'list'>('list');
const focusFilters = ref<Partial<Record<TaskFocusFilter, boolean>>>({});
const mobileFiltersOpen = ref(false);

const focusOptions = computed(() => [
    {
        icon: AlertTriangle,
        key: 'overdue' as const,
        label: t('tasks.filters.overdue'),
    },
    {
        icon: CheckCircle2,
        key: 'completed_today' as const,
        label: t('tasks.filters.completed_today'),
    },
    {
        icon: Pin,
        key: 'is_pinned' as const,
        label: t('tasks.filters.pinned'),
    },
    {
        icon: Star,
        key: 'is_favorite' as const,
        label: t('tasks.filters.favorites'),
    },
]);
const activeFilterCount = computed(() =>
    activeTaskFilterCount(currentFilters()),
);
const activeFilterLabel = computed(() =>
    t(
        `tasks.filters.active_count_${taskPluralForm(activeFilterCount.value, locale.value)}`,
        { count: activeFilterCount.value },
    ),
);

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search ?? '';
        projectId.value = filters.project_id ?? 'all';
        status.value = filters.status ?? 'all';
        priority.value = filters.priority ?? 'all';
        sort.value = filters.sort || 'default';
        direction.value = filters.direction ?? 'asc';
        perPage.value = String(filters.per_page ?? 50) as '100' | '25' | '50';
        view.value = filters.view ?? 'list';
        focusFilters.value = {
            completed_today: filters.completed_today,
            is_favorite: filters.is_favorite,
            is_pinned: filters.is_pinned,
            overdue: filters.overdue,
        };
    },
    { immediate: true, deep: true },
);

function currentFilters(): TodoFilters {
    return mergeTaskFilterState(props.filters, {
        search: search.value.trim() || undefined,
        project_id: projectId.value === 'all' ? undefined : projectId.value,
        status: status.value === 'all' ? undefined : status.value,
        priority: priority.value === 'all' ? undefined : priority.value,
        sort: sort.value === 'default' ? undefined : sort.value,
        direction: direction.value,
        per_page: Number(perPage.value) as 25 | 50 | 100,
        view: view.value,
        completed_today: focusFilters.value.completed_today || undefined,
        is_favorite: focusFilters.value.is_favorite || undefined,
        is_pinned: focusFilters.value.is_pinned || undefined,
        overdue: focusFilters.value.overdue || undefined,
    });
}

function apply(): void {
    mobileFiltersOpen.value = false;
    emit('update', currentFilters());
}

function toggleFocus(key: TaskFocusFilter): void {
    const filters = toggleTaskFocusFilter(currentFilters(), key);

    focusFilters.value = {
        completed_today: filters.completed_today,
        is_favorite: filters.is_favorite,
        is_pinned: filters.is_pinned,
        overdue: filters.overdue,
    };
    apply();
}

function clear(): void {
    const filters = clearTaskFilters(currentFilters());

    search.value = '';
    projectId.value = 'all';
    status.value = 'all';
    priority.value = 'all';
    sort.value = 'default';
    direction.value = filters.direction ?? 'asc';
    perPage.value = String(filters.per_page ?? 50) as '100' | '25' | '50';
    view.value = filters.view ?? 'list';
    focusFilters.value = {};
    apply();
}

function setView(nextView: 'board' | 'list'): void {
    view.value = nextView;
    apply();
}
</script>

<template>
    <div class="space-y-4 border-b border-border/70 pb-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <form
                class="relative min-w-0 flex-1"
                role="search"
                @submit.prevent="apply"
            >
                <label for="task-search" class="sr-only">
                    {{ t('tasks.filters.search') }}
                </label>
                <Search
                    class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                    aria-hidden="true"
                />
                <Input
                    id="task-search"
                    v-model="search"
                    type="search"
                    :placeholder="t('tasks.filters.search')"
                    class="min-h-11 pl-10 motion-reduce:transition-none"
                    :disabled="processing"
                />
            </form>

            <div class="flex items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11 flex-1 justify-between md:hidden"
                    :aria-label="
                        activeFilterCount
                            ? activeFilterLabel
                            : t('tasks.filters.filters')
                    "
                    aria-haspopup="dialog"
                    :aria-expanded="mobileFiltersOpen"
                    :disabled="processing"
                    @click="mobileFiltersOpen = true"
                >
                    <span class="flex items-center gap-2">
                        <SlidersHorizontal class="size-4" aria-hidden="true" />
                        {{ t('tasks.filters.filters') }}
                    </span>
                    <span
                        v-if="activeFilterCount"
                        class="rounded-full bg-orange-500/12 px-2 py-0.5 text-xs font-semibold text-orange-800 tabular-nums"
                    >
                        {{ activeFilterCount }}
                    </span>
                </Button>
                <div
                    class="ml-auto flex rounded-xl bg-muted p-1"
                    role="group"
                    :aria-label="t('tasks.filters.view')"
                >
                    <Button
                        type="button"
                        size="sm"
                        class="min-h-11"
                        :variant="view === 'list' ? 'secondary' : 'ghost'"
                        :aria-pressed="view === 'list'"
                        :disabled="processing"
                        @click="setView('list')"
                    >
                        <List class="size-4" aria-hidden="true" />
                        {{ t('tasks.filters.list') }}
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        class="min-h-11"
                        :variant="view === 'board' ? 'secondary' : 'ghost'"
                        :aria-pressed="view === 'board'"
                        :disabled="processing"
                        @click="setView('board')"
                    >
                        <Columns3 class="size-4" aria-hidden="true" />
                        {{ t('tasks.filters.board') }}
                    </Button>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <p
                class="text-xs font-semibold tracking-[0.1em] text-muted-foreground uppercase"
            >
                {{ t('tasks.filters.focus') }}
            </p>
            <WorkspaceSegmentedControl
                role="group"
                :label="t('tasks.filters.focus')"
                class="w-full flex-wrap overflow-visible bg-muted/60"
            >
                <WorkspaceSegmentedButton
                    v-for="option in focusOptions"
                    :key="option.key"
                    :active="Boolean(focusFilters[option.key])"
                    class="min-h-11 flex-1 sm:flex-none"
                    :aria-pressed="Boolean(focusFilters[option.key])"
                    :disabled="processing"
                    @click="toggleFocus(option.key)"
                >
                    <component
                        :is="option.icon"
                        class="size-4"
                        aria-hidden="true"
                    />
                    {{ option.label }}
                </WorkspaceSegmentedButton>
            </WorkspaceSegmentedControl>
        </div>

        <div class="hidden gap-3 md:grid md:grid-cols-3 xl:grid-cols-6">
            <Select
                v-model="projectId"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('tasks.filters.project')"
                >
                    <SelectValue :placeholder="t('tasks.filters.project')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('tasks.filters.all_projects')
                    }}</SelectItem>
                    <SelectItem
                        v-for="project in projects"
                        :key="project.id"
                        :value="project.id"
                    >
                        {{ project.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select
                v-model="status"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('tasks.filters.status')"
                >
                    <SelectValue :placeholder="t('tasks.filters.status')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('tasks.filters.all_statuses')
                    }}</SelectItem>
                    <SelectItem
                        v-for="item in statuses"
                        :key="item.id"
                        :value="item.key"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select
                v-model="priority"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('tasks.filters.priority')"
                >
                    <SelectValue :placeholder="t('tasks.filters.priority')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('tasks.filters.all_priorities')
                    }}</SelectItem>
                    <SelectItem
                        v-for="item in priorities"
                        :key="item.id"
                        :value="item.key"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select
                v-model="sort"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('tasks.filters.sort')"
                >
                    <SelectValue :placeholder="t('tasks.filters.sort')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="default">{{
                        t('tasks.filters.default_order')
                    }}</SelectItem>
                    <SelectItem value="due_date">{{
                        t('tasks.filters.due_date')
                    }}</SelectItem>
                    <SelectItem value="priority">{{
                        t('tasks.filters.priority')
                    }}</SelectItem>
                    <SelectItem value="status">{{
                        t('tasks.filters.status')
                    }}</SelectItem>
                    <SelectItem value="title">{{
                        t('tasks.filters.title')
                    }}</SelectItem>
                    <SelectItem value="created_at">{{
                        t('tasks.filters.created')
                    }}</SelectItem>
                </SelectContent>
            </Select>
            <Button
                type="button"
                variant="outline"
                class="min-h-11"
                :disabled="processing"
                @click="
                    direction = direction === 'asc' ? 'desc' : 'asc';
                    apply();
                "
            >
                <ArrowDownAZ class="size-4" aria-hidden="true" />
                {{
                    direction === 'asc'
                        ? t('tasks.filters.ascending')
                        : t('tasks.filters.descending')
                }}
            </Button>
            <div class="flex gap-2">
                <Select
                    v-model="perPage"
                    :disabled="processing"
                    @update:model-value="apply"
                >
                    <SelectTrigger
                        class="min-h-11"
                        :aria-label="t('tasks.filters.per_page')"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                        <SelectItem value="100">100</SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="min-h-11 min-w-11"
                    :aria-label="t('tasks.filters.clear')"
                    :disabled="processing || activeFilterCount === 0"
                    @click="clear"
                >
                    <X class="size-4" aria-hidden="true" />
                </Button>
            </div>
        </div>

        <Sheet
            :open="mobileFiltersOpen"
            @update:open="mobileFiltersOpen = $event"
        >
            <SheetContent
                side="bottom"
                class="max-h-[92vh] overflow-y-auto rounded-t-feature"
            >
                <SheetHeader>
                    <SheetTitle>{{ t('tasks.filters.filters') }}</SheetTitle>
                    <SheetDescription>{{
                        t('tasks.filters.description')
                    }}</SheetDescription>
                </SheetHeader>
                <div class="grid gap-4 px-4 pb-6">
                    <Select v-model="projectId">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('tasks.filters.project')"
                        >
                            <SelectValue
                                :placeholder="t('tasks.filters.project')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                t('tasks.filters.all_projects')
                            }}</SelectItem>
                            <SelectItem
                                v-for="project in projects"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="status">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('tasks.filters.status')"
                        >
                            <SelectValue
                                :placeholder="t('tasks.filters.status')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                t('tasks.filters.all_statuses')
                            }}</SelectItem>
                            <SelectItem
                                v-for="item in statuses"
                                :key="item.id"
                                :value="item.key"
                            >
                                {{ item.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="priority">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('tasks.filters.priority')"
                        >
                            <SelectValue
                                :placeholder="t('tasks.filters.priority')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                t('tasks.filters.all_priorities')
                            }}</SelectItem>
                            <SelectItem
                                v-for="item in priorities"
                                :key="item.id"
                                :value="item.key"
                            >
                                {{ item.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="sort">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('tasks.filters.sort')"
                        >
                            <SelectValue
                                :placeholder="t('tasks.filters.sort')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="default">{{
                                t('tasks.filters.default_order')
                            }}</SelectItem>
                            <SelectItem value="due_date">{{
                                t('tasks.filters.due_date')
                            }}</SelectItem>
                            <SelectItem value="priority">{{
                                t('tasks.filters.priority')
                            }}</SelectItem>
                            <SelectItem value="status">{{
                                t('tasks.filters.status')
                            }}</SelectItem>
                            <SelectItem value="title">{{
                                t('tasks.filters.title')
                            }}</SelectItem>
                            <SelectItem value="created_at">{{
                                t('tasks.filters.created')
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button
                        type="button"
                        variant="outline"
                        class="min-h-11"
                        @click="
                            direction = direction === 'asc' ? 'desc' : 'asc'
                        "
                    >
                        <ArrowDownAZ class="size-4" aria-hidden="true" />
                        {{
                            direction === 'asc'
                                ? t('tasks.filters.ascending')
                                : t('tasks.filters.descending')
                        }}
                    </Button>
                    <Select v-model="perPage">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('tasks.filters.per_page')"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="25">25</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                            <SelectItem value="100">100</SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="grid grid-cols-2 gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            class="min-h-11"
                            :disabled="activeFilterCount === 0"
                            @click="clear"
                        >
                            {{ t('tasks.filters.clear') }}
                        </Button>
                        <Button type="button" class="min-h-11" @click="apply">
                            {{ t('tasks.filters.apply') }}
                        </Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
