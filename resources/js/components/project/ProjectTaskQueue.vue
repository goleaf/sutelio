<script setup lang="ts">
import { InfiniteScroll } from '@inertiajs/vue3';
import {
    ArrowUp,
    CalendarClock,
    CircleUserRound,
    ListChecks,
    LoaderCircle,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import {
    hasProjectFilters,
    isProjectTaskOverdue,
    projectResultPluralForm,
} from '@/components/project/project-operations';
import type {
    ProjectFilters,
    ProjectTask,
    ProjectTaskPaginator,
} from '@/components/project/project-operations';
import EmptyState from '@/components/shared/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';

const props = defineProps<{
    archived: boolean;
    busyTaskId: string | null;
    filters: ProjectFilters;
    processing: boolean;
    today: string;
    todos: ProjectTaskPaginator;
}>();

const emit = defineEmits<{
    clear: [];
    create: [];
    delete: [task: ProjectTask];
    select: [task: ProjectTask];
    toggle: [task: ProjectTask];
}>();

const { formatDate, formatNumber, locale, t } = useUi();
const isFiltered = computed(() => hasProjectFilters(props.filters));
const resultSummary = computed(() => {
    const form = projectResultPluralForm(props.todos.meta.total, locale.value);

    return t(`projects.show.results.summary_${form}`, {
        count: formatNumber(props.todos.meta.total),
        visible: formatNumber(props.todos.data.length),
    });
});

function dueLabel(task: ProjectTask): string {
    if (!task.due_date) {
        return '';
    }

    const date = formatDate(task.due_date, {
        day: 'numeric',
        month: 'short',
    });

    return isProjectTaskOverdue(task.due_date, task.is_completed, props.today)
        ? t('projects.show.pulse.overdue', { date })
        : t('projects.show.pulse.due_on', { date });
}
</script>

<template>
    <section
        class="min-w-0 overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        :aria-busy="processing"
    >
        <header
            class="flex min-h-18 flex-wrap items-center justify-between gap-3 border-b border-border/70 px-4 py-4 sm:px-6"
        >
            <div aria-live="polite">
                <h2 class="text-base font-semibold">
                    {{ t('projects.show.results.title') }}
                </h2>
                <p
                    class="mt-0.5 text-[0.9375rem] leading-6 text-muted-foreground tabular-nums"
                >
                    {{ resultSummary }}
                </p>
            </div>
            <div
                v-if="processing"
                class="flex items-center gap-2 text-[0.9375rem] leading-6 font-medium text-muted-foreground"
                role="status"
            >
                <LoaderCircle
                    class="size-4 animate-spin motion-reduce:animate-none"
                    aria-hidden="true"
                />
                {{ t('projects.show.loading.results') }}
            </div>
            <div
                v-else
                class="flex items-center gap-2 text-[0.9375rem] leading-6 text-muted-foreground"
            >
                <span
                    class="size-2 rounded-full bg-status-success-icon"
                    aria-hidden="true"
                />
                {{ t('projects.show.results.up_to_date') }}
            </div>
        </header>

        <InfiniteScroll data="todos" manual>
            <template #previous="{ loading, fetch, hasMore }">
                <div
                    v-if="hasMore"
                    class="flex justify-center border-b border-border/70 px-4 py-3"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        class="min-h-12 motion-reduce:transition-none pointer-coarse:min-h-13"
                        :loading="loading"
                        :loading-label="t('projects.show.loading.more')"
                        @click="fetch"
                    >
                        <ArrowUp class="size-4" aria-hidden="true" />
                        {{ t('projects.show.pagination.load_previous') }}
                    </Button>
                </div>
            </template>

            <div v-if="todos.data.length" class="divide-y divide-border/65">
                <article
                    v-for="task in todos.data"
                    :key="task.id"
                    data-slot="project-task-row"
                    class="group grid grid-cols-[auto_minmax(0,1fr)_auto] items-start gap-3 px-4 py-4 transition-colors hover:bg-muted/35 motion-reduce:transition-none sm:gap-4 sm:px-6"
                >
                    <div
                        class="flex size-12 items-center justify-center pointer-coarse:size-13"
                    >
                        <LoaderCircle
                            v-if="busyTaskId === task.id"
                            class="size-4 animate-spin text-muted-foreground motion-reduce:animate-none"
                            aria-hidden="true"
                        />
                        <label
                            v-else
                            data-slot="project-task-checkbox-target"
                            :for="`project-task-toggle-${task.id}`"
                            class="flex size-12 cursor-pointer items-center justify-center pointer-coarse:size-13"
                        >
                            <Checkbox
                                :id="`project-task-toggle-${task.id}`"
                                :model-value="task.is_completed"
                                class="size-5 data-[state=checked]:border-orange-600 data-[state=checked]:bg-orange-600"
                                :aria-label="
                                    task.is_completed
                                        ? t(
                                              'projects.show.actions.reopen_task',
                                              {
                                                  title: task.title,
                                              },
                                          )
                                        : t(
                                              'projects.show.actions.complete_task',
                                              {
                                                  title: task.title,
                                              },
                                          )
                                "
                                @update:model-value="emit('toggle', task)"
                            />
                        </label>
                    </div>

                    <button
                        type="button"
                        class="min-h-12 min-w-0 rounded-lg text-left focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:ring-offset-4 focus-visible:outline-none pointer-coarse:min-h-13"
                        :aria-label="
                            t('projects.show.actions.open_task', {
                                title: task.title,
                            })
                        "
                        @click="emit('select', task)"
                    >
                        <span
                            class="line-clamp-2 text-base font-semibold tracking-tight wrap-anywhere"
                            :class="
                                task.is_completed
                                    ? 'text-muted-foreground line-through'
                                    : 'group-hover:text-orange-800'
                            "
                        >
                            {{ task.title }}
                        </span>
                    </button>

                    <div class="flex items-center">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="min-h-12 min-w-12 text-muted-foreground opacity-75 hover:text-destructive sm:opacity-0 sm:group-focus-within:opacity-100 sm:group-hover:opacity-100 pointer-coarse:min-h-13 pointer-coarse:min-w-13"
                            :aria-label="
                                t('projects.show.actions.delete_task', {
                                    title: task.title,
                                })
                            "
                            :disabled="busyTaskId === task.id"
                            @click="emit('delete', task)"
                        >
                            <Trash2 class="size-4" aria-hidden="true" />
                        </Button>
                    </div>

                    <div
                        data-slot="project-task-metadata"
                        class="col-span-3 row-start-2 flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1.5 text-[0.9375rem] leading-6 text-muted-foreground sm:col-start-2 sm:col-end-3"
                    >
                        <span
                            data-slot="project-task-status"
                            class="inline-flex items-center gap-1.5"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :style="{
                                    backgroundColor: safeDefinitionColor(
                                        task.status_definition?.color,
                                    ),
                                }"
                                aria-hidden="true"
                            />
                            {{ task.status_definition?.name ?? task.status }}
                        </span>
                        <span
                            data-slot="project-task-priority"
                            class="inline-flex items-center gap-1.5"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :style="{
                                    backgroundColor: safeDefinitionColor(
                                        task.priority_definition?.color,
                                    ),
                                }"
                                aria-hidden="true"
                            />
                            {{
                                task.priority_definition?.name ?? task.priority
                            }}
                        </span>
                        <span
                            v-if="task.assignee"
                            class="inline-flex items-center gap-1.5"
                        >
                            <CircleUserRound
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            {{ task.assignee.name }}
                        </span>
                        <span
                            v-if="task.due_date"
                            class="inline-flex items-center gap-1.5"
                            :class="
                                isProjectTaskOverdue(
                                    task.due_date,
                                    task.is_completed,
                                    today,
                                )
                                    ? 'font-medium text-destructive'
                                    : ''
                            "
                        >
                            <CalendarClock
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            {{ dueLabel(task) }}
                        </span>
                        <span
                            v-else-if="!task.assignee"
                            class="inline-flex items-center gap-1.5"
                        >
                            <CircleUserRound
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            {{ t('projects.show.attention.unassigned') }}
                        </span>
                        <span
                            v-for="label in task.labels.slice(0, 2)"
                            :key="label.id"
                            class="inline-flex items-center gap-1.5"
                        >
                            <span
                                class="size-2 rounded-full"
                                :style="{
                                    backgroundColor: safeDefinitionColor(
                                        label.color,
                                    ),
                                }"
                                aria-hidden="true"
                            />
                            {{ label.name }}
                        </span>
                    </div>
                </article>
            </div>

            <EmptyState
                v-else
                compact
                :title="
                    isFiltered
                        ? t('projects.show.empty_filtered')
                        : t('projects.show.empty')
                "
                :description="
                    isFiltered
                        ? t('projects.show.empty_filtered_description')
                        : t('projects.show.empty_description')
                "
                :action-label="
                    isFiltered
                        ? t('projects.show.filters.clear')
                        : archived
                          ? undefined
                          : t('projects.show.actions.new_task')
                "
                @action="isFiltered ? emit('clear') : emit('create')"
            >
                <template #icon>
                    <ListChecks class="size-7" aria-hidden="true" />
                </template>
            </EmptyState>

            <template #next="{ loading, fetch, hasMore }">
                <div
                    v-if="todos.data.length"
                    class="flex min-h-20 items-center justify-center border-t border-border/70 px-4 py-4"
                >
                    <Button
                        v-if="hasMore"
                        type="button"
                        variant="outline"
                        class="min-h-12 min-w-44 motion-reduce:transition-none pointer-coarse:min-h-13"
                        :loading="loading"
                        :loading-label="t('projects.show.loading.more')"
                        @click="fetch"
                    >
                        {{ t('projects.show.pagination.load_more') }}
                    </Button>
                    <p
                        v-else
                        class="text-[0.9375rem] leading-6 font-medium text-muted-foreground"
                    >
                        {{ t('projects.show.pagination.end') }}
                    </p>
                </div>
            </template>
        </InfiniteScroll>
    </section>
</template>
