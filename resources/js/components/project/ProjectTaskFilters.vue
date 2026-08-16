<script setup lang="ts">
import {
    ArrowUpDown,
    Filter,
    RotateCcw,
    Search,
    SlidersHorizontal,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { hasProjectFilters } from '@/components/project/project-operations';
import type {
    ProjectAssignee,
    ProjectAttention,
    ProjectFilters,
    ProjectSort,
} from '@/components/project/project-operations';
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
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTaskDefinitions } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import type { TaskDefinitionCatalog } from '@/types/models';

const props = defineProps<{
    assignees: ProjectAssignee[];
    filters: ProjectFilters;
    processing: boolean;
    taskDefinitions: TaskDefinitionCatalog;
}>();

const emit = defineEmits<{ update: [filters: ProjectFilters] }>();
const { t } = useUi();
const { priorities, statuses } = useTaskDefinitions(
    () => props.taskDefinitions,
);
const mobileOpen = ref(false);
const search = ref('');
const status = ref('all');
const priority = ref('all');
const assignee = ref('all');
const attention = ref<ProjectAttention>('all');
const sort = ref<ProjectSort>('position');

const attentionOptions = computed(() => [
    { label: t('projects.show.attention.all'), value: 'all' as const },
    {
        label: t('projects.show.attention.overdue'),
        value: 'overdue' as const,
    },
    {
        label: t('projects.show.attention.due_soon'),
        value: 'due_soon' as const,
    },
    {
        label: t('projects.show.attention.unassigned'),
        value: 'unassigned' as const,
    },
]);
const activeFilterCount = computed(() => {
    const filters = currentFilters();

    return [
        filters.search,
        filters.status,
        filters.priority,
        filters.assignee,
        filters.attention !== 'all' ? filters.attention : null,
        filters.sort !== 'position' ? filters.sort : null,
    ].filter(Boolean).length;
});

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search ?? '';
        status.value = filters.status ?? 'all';
        priority.value = filters.priority ?? 'all';
        assignee.value = filters.assignee ?? 'all';
        attention.value = filters.attention;
        sort.value = filters.sort;
    },
    { deep: true, immediate: true },
);

function currentFilters(): ProjectFilters {
    return {
        search: search.value.trim() || null,
        status: status.value === 'all' ? null : status.value,
        priority: priority.value === 'all' ? null : priority.value,
        assignee: assignee.value === 'all' ? null : assignee.value,
        attention: attention.value,
        sort: sort.value,
    };
}

function apply(): void {
    mobileOpen.value = false;
    emit('update', currentFilters());
}

function applyAttention(value: ProjectAttention): void {
    attention.value = value;
    apply();
}

function clear(): void {
    search.value = '';
    status.value = 'all';
    priority.value = 'all';
    assignee.value = 'all';
    attention.value = 'all';
    sort.value = 'position';
    apply();
}
</script>

<template>
    <section
        class="rounded-panel border border-border/80 bg-card p-4 shadow-panel sm:p-5"
        :aria-label="t('projects.show.filters.title')"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <form
                id="project-task-filter-form"
                class="min-w-0 flex-1"
                role="search"
                @submit.prevent="apply"
            >
                <label
                    for="project-task-search"
                    class="mb-2 block text-xs font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                >
                    {{ t('projects.show.filters.search_label') }}
                </label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        id="project-task-search"
                        v-model="search"
                        type="search"
                        class="min-h-11 pl-10 motion-reduce:transition-none"
                        :placeholder="
                            t('projects.show.filters.search_placeholder')
                        "
                        :disabled="processing"
                    />
                </div>
            </form>

            <div class="flex items-center gap-2 lg:hidden">
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-11 flex-1 justify-between motion-reduce:transition-none"
                    :disabled="processing"
                    aria-haspopup="dialog"
                    :aria-expanded="mobileOpen"
                    @click="mobileOpen = true"
                >
                    <span class="flex items-center gap-2">
                        <SlidersHorizontal class="size-4" aria-hidden="true" />
                        {{ t('projects.show.filters.title') }}
                    </span>
                    <span
                        v-if="activeFilterCount"
                        class="rounded-full bg-orange-500/12 px-2 py-0.5 text-xs font-semibold text-orange-800 tabular-nums dark:text-orange-200"
                        >{{ activeFilterCount }}</span
                    >
                </Button>
                <Button
                    type="button"
                    class="min-h-11"
                    :disabled="processing"
                    @click="apply"
                >
                    {{ t('projects.show.filters.apply') }}
                </Button>
            </div>

            <Button
                type="submit"
                form="project-task-filter-form"
                class="hidden min-h-11 lg:inline-flex"
                :disabled="processing"
            >
                {{ t('projects.show.filters.apply') }}
            </Button>
        </div>

        <div class="mt-4 hidden gap-3 lg:grid lg:grid-cols-4">
            <Select
                v-model="status"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('projects.show.filters.status')"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('projects.show.filters.all_statuses')
                    }}</SelectItem>
                    <SelectItem
                        v-for="item in statuses"
                        :key="item.id"
                        :value="item.id"
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
                    :aria-label="t('projects.show.filters.priority')"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('projects.show.filters.all_priorities')
                    }}</SelectItem>
                    <SelectItem
                        v-for="item in priorities"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select
                v-model="assignee"
                :disabled="processing"
                @update:model-value="apply"
            >
                <SelectTrigger
                    class="min-h-11"
                    :aria-label="t('projects.show.filters.assignee')"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{
                        t('projects.show.filters.all_assignees')
                    }}</SelectItem>
                    <SelectItem
                        v-for="member in assignees"
                        :key="member.id"
                        :value="member.id"
                    >
                        {{ member.name }}
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
                    :aria-label="t('projects.show.filters.sort')"
                >
                    <ArrowUpDown class="size-4" aria-hidden="true" />
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="position">{{
                        t('projects.show.filters.task_order')
                    }}</SelectItem>
                    <SelectItem value="due_date">{{
                        t('projects.show.filters.due_date')
                    }}</SelectItem>
                    <SelectItem value="priority">{{
                        t('projects.show.filters.priority_order')
                    }}</SelectItem>
                    <SelectItem value="updated">{{
                        t('projects.show.filters.updated')
                    }}</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div
            class="mt-4 hidden items-center gap-2 border-t border-border/60 pt-4 lg:flex"
            role="group"
            :aria-label="t('projects.show.filters.attention')"
        >
            <Filter
                class="mr-1 size-4 text-muted-foreground"
                aria-hidden="true"
            />
            <button
                v-for="option in attentionOptions"
                :key="option.value"
                type="button"
                :aria-pressed="attention === option.value"
                :disabled="processing"
                class="min-h-11 rounded-xl px-3 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none disabled:opacity-50 motion-reduce:transition-none"
                :class="
                    attention === option.value
                        ? 'bg-orange-500/10 text-orange-800 dark:text-orange-200'
                        : 'text-muted-foreground hover:bg-muted/70 hover:text-foreground'
                "
                @click="applyAttention(option.value)"
            >
                {{ option.label }}
            </button>
            <Button
                type="button"
                variant="ghost"
                class="ml-auto min-h-11 motion-reduce:transition-none"
                :disabled="processing || !hasProjectFilters(currentFilters())"
                @click="clear"
            >
                <RotateCcw class="size-4" aria-hidden="true" />
                {{ t('projects.show.filters.clear') }}
            </Button>
        </div>

        <p class="sr-only" aria-live="polite">
            {{
                processing
                    ? t('projects.show.loading.results')
                    : hasProjectFilters(filters)
                      ? t('projects.show.filters.description')
                      : t('projects.show.attention.all')
            }}
        </p>

        <Sheet v-model:open="mobileOpen">
            <SheetContent
                side="bottom"
                class="max-h-[92vh] overflow-y-auto rounded-t-feature"
            >
                <SheetHeader>
                    <SheetTitle>{{
                        t('projects.show.filters.title')
                    }}</SheetTitle>
                    <SheetDescription>{{
                        t('projects.show.filters.description')
                    }}</SheetDescription>
                </SheetHeader>

                <div class="grid gap-5 px-4 py-5">
                    <div class="grid gap-2">
                        <span class="text-sm font-medium">{{
                            t('projects.show.filters.status')
                        }}</span>
                        <Select v-model="status">
                            <SelectTrigger class="min-h-11">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{
                                    t('projects.show.filters.all_statuses')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="item in statuses"
                                    :key="item.id"
                                    :value="item.id"
                                    >{{ item.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <Select v-model="priority">
                            <SelectTrigger
                                class="min-h-11"
                                :aria-label="
                                    t('projects.show.filters.priority')
                                "
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{
                                    t('projects.show.filters.all_priorities')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="item in priorities"
                                    :key="item.id"
                                    :value="item.id"
                                    >{{ item.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <Select v-model="assignee">
                            <SelectTrigger
                                class="min-h-11"
                                :aria-label="
                                    t('projects.show.filters.assignee')
                                "
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{
                                    t('projects.show.filters.all_assignees')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="member in assignees"
                                    :key="member.id"
                                    :value="member.id"
                                    >{{ member.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2"
                        role="group"
                        :aria-label="t('projects.show.filters.attention')"
                    >
                        <button
                            v-for="option in attentionOptions"
                            :key="option.value"
                            type="button"
                            :aria-pressed="attention === option.value"
                            class="min-h-11 rounded-xl border px-3 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none motion-reduce:transition-none"
                            :class="
                                attention === option.value
                                    ? 'border-orange-500/25 bg-orange-500/10 text-orange-800 dark:text-orange-200'
                                    : 'border-border/80 text-muted-foreground'
                            "
                            @click="attention = option.value"
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <Select v-model="sort">
                        <SelectTrigger
                            class="min-h-11"
                            :aria-label="t('projects.show.filters.sort')"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="position">{{
                                t('projects.show.filters.task_order')
                            }}</SelectItem>
                            <SelectItem value="due_date">{{
                                t('projects.show.filters.due_date')
                            }}</SelectItem>
                            <SelectItem value="priority">{{
                                t('projects.show.filters.priority_order')
                            }}</SelectItem>
                            <SelectItem value="updated">{{
                                t('projects.show.filters.updated')
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <SheetFooter class="grid grid-cols-2 gap-2 sm:grid-cols-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="min-h-11"
                        :disabled="!hasProjectFilters(currentFilters())"
                        @click="clear"
                    >
                        {{ t('projects.show.filters.clear') }}
                    </Button>
                    <Button type="button" class="min-h-11" @click="apply">
                        {{ t('projects.show.filters.apply') }}
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </section>
</template>
