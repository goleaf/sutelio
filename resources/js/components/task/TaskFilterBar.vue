<script setup lang="ts">
import {
    AlertTriangle,
    CheckCircle2,
    Columns3,
    List,
    Pin,
    Search,
    SlidersHorizontal,
    Star,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import FilterSheet from '@/components/shared/FilterSheet.vue';
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
import TaskFilterFields from '@/components/task/TaskFilterFields.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

watch(() => props.filters, synchronizeFilters, { immediate: true, deep: true });

function synchronizeFilters(filters: TodoFilters): void {
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
}

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

function handleMobileFiltersOpenChange(value: boolean): void {
    mobileFiltersOpen.value = value;

    if (!value) {
        synchronizeFilters(props.filters);
    }
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
                    class="min-h-12 pl-10 motion-reduce:transition-none pointer-coarse:min-h-13"
                    :disabled="processing"
                />
            </form>

            <div class="flex items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-12 flex-1 justify-between md:hidden pointer-coarse:min-h-13"
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
                        class="rounded-full bg-orange-500/12 px-2 py-0.5 text-[0.9375rem] leading-5 font-semibold text-orange-800 tabular-nums"
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
                        class="min-h-12 pointer-coarse:min-h-13"
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
                        class="min-h-12 pointer-coarse:min-h-13"
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
                class="text-[0.9375rem] leading-5 font-semibold text-muted-foreground"
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
                    class="min-h-12 flex-1 sm:flex-none pointer-coarse:min-h-13"
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

        <TaskFilterFields
            v-model:project-id="projectId"
            v-model:status="status"
            v-model:priority="priority"
            v-model:sort="sort"
            v-model:direction="direction"
            v-model:per-page="perPage"
            mode="desktop"
            :projects="projects"
            :task-definitions="taskDefinitions"
            :disabled="processing"
            :clear-disabled="activeFilterCount === 0"
            @commit="apply"
            @clear="clear"
        />

        <FilterSheet
            :model-value="mobileFiltersOpen"
            :title="t('tasks.filters.filters')"
            :description="t('tasks.filters.description')"
            :clear-label="t('tasks.filters.clear')"
            :apply-label="t('tasks.filters.apply')"
            :clear-disabled="activeFilterCount === 0"
            :processing="processing"
            @update:model-value="handleMobileFiltersOpenChange"
            @clear="clear"
            @apply="apply"
        >
            <TaskFilterFields
                v-model:project-id="projectId"
                v-model:status="status"
                v-model:priority="priority"
                v-model:sort="sort"
                v-model:direction="direction"
                v-model:per-page="perPage"
                mode="mobile"
                :projects="projects"
                :task-definitions="taskDefinitions"
            />
        </FilterSheet>
    </div>
</template>
