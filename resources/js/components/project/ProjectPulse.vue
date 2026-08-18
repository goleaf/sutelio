<script setup lang="ts">
import {
    ArrowRight,
    CalendarClock,
    CheckCircle2,
    CircleUserRound,
    Gauge,
} from '@lucide/vue';
import { computed } from 'vue';
import type {
    ProjectAttention,
    ProjectAttentionTasks,
    ProjectMetrics,
    ProjectPriorityDistribution,
    ProjectTask,
} from '@/components/project/project-operations';
import {
    isProjectTaskOverdue,
    projectAttentionContinuation,
} from '@/components/project/project-operations';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import { safeDefinitionColor } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';

const props = defineProps<{
    attentionTasks: ProjectAttentionTasks;
    metrics: ProjectMetrics;
    priorityDistribution: ProjectPriorityDistribution[];
    today: string;
}>();

const emit = defineEmits<{
    filter: [attention: ProjectAttention];
    select: [task: ProjectTask];
}>();

const { formatDate, formatNumber, t } = useUi();
const priorityMaximum = computed(() =>
    Math.max(
        ...props.priorityDistribution.map((priority) => priority.count),
        1,
    ),
);
const moreAttentionFilter = computed(() =>
    projectAttentionContinuation(
        props.metrics,
        props.attentionTasks.data,
        props.today,
    ),
);

function priorityScale(count: number): number {
    return Math.max(count / priorityMaximum.value, count > 0 ? 0.08 : 0);
}

function percentageScale(value: number): number {
    return Math.min(1, Math.max(0, value / 100));
}

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
    <aside
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        :aria-label="t('projects.show.pulse.title')"
    >
        <div class="border-b border-border/70 px-5 py-5 sm:px-6">
            <LeadingIconHeading tile tile-tone="brand" content-class="gap-0">
                <template #icon>
                    <Gauge />
                </template>

                <h2 class="font-semibold tracking-tight">
                    {{ t('projects.show.pulse.title') }}
                </h2>
                <p
                    class="mt-1 text-[0.9375rem] leading-6 text-muted-foreground"
                >
                    {{ t('projects.show.pulse.description') }}
                </p>
            </LeadingIconHeading>

            <div class="mt-5">
                <div
                    class="flex items-center justify-between gap-3 text-[0.9375rem] leading-5"
                >
                    <span class="font-medium text-muted-foreground">{{
                        t('projects.show.pulse.completion_label')
                    }}</span>
                    <span class="font-semibold tabular-nums">{{
                        t('projects.show.pulse.completion', {
                            rate: formatNumber(metrics.completion_rate),
                        })
                    }}</span>
                </div>
                <div
                    class="mt-2.5 h-2 overflow-hidden rounded-full bg-muted"
                    role="progressbar"
                    :aria-label="t('projects.show.pulse.completion_label')"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-valuenow="metrics.completion_rate"
                >
                    <div
                        class="h-full w-full origin-left scale-x-[var(--progress)] rounded-full bg-emerald-500 transition-transform duration-[var(--motion-state)] ease-[var(--ease-standard)] motion-reduce:transition-none"
                        :style="{
                            '--progress': percentageScale(
                                metrics.completion_rate,
                            ),
                        }"
                    />
                </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2">
                <button
                    type="button"
                    class="ui-lift min-h-12 rounded-xl border border-border/70 bg-background/60 px-2.5 py-2 text-left transition-colors hover:border-orange-500/25 hover:bg-orange-500/[0.06] focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none motion-reduce:transition-none pointer-coarse:min-h-13"
                    @click="emit('filter', 'overdue')"
                >
                    <span class="block text-base font-semibold tabular-nums">{{
                        formatNumber(metrics.overdue)
                    }}</span>
                    <span
                        class="mt-0.5 block text-[0.9375rem] leading-5 text-muted-foreground"
                        >{{ t('projects.show.attention.overdue') }}</span
                    >
                </button>
                <button
                    type="button"
                    class="ui-lift min-h-12 rounded-xl border border-border/70 bg-background/60 px-2.5 py-2 text-left transition-colors hover:border-orange-500/25 hover:bg-orange-500/[0.06] focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none motion-reduce:transition-none pointer-coarse:min-h-13"
                    @click="emit('filter', 'due_soon')"
                >
                    <span class="block text-base font-semibold tabular-nums">{{
                        formatNumber(metrics.due_soon)
                    }}</span>
                    <span
                        class="mt-0.5 block text-[0.9375rem] leading-5 text-muted-foreground"
                        >{{ t('projects.show.attention.due_soon') }}</span
                    >
                </button>
                <button
                    type="button"
                    class="ui-lift min-h-12 rounded-xl border border-border/70 bg-background/60 px-2.5 py-2 text-left transition-colors hover:border-orange-500/25 hover:bg-orange-500/[0.06] focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none motion-reduce:transition-none pointer-coarse:min-h-13"
                    @click="emit('filter', 'unassigned')"
                >
                    <span class="block text-base font-semibold tabular-nums">{{
                        formatNumber(metrics.unassigned)
                    }}</span>
                    <span
                        class="mt-0.5 block text-[0.9375rem] leading-5 text-muted-foreground"
                        >{{ t('projects.show.attention.unassigned') }}</span
                    >
                </button>
            </div>
        </div>

        <div class="border-b border-border/70 px-5 py-5 sm:px-6">
            <h3
                class="text-[0.9375rem] leading-5 font-semibold text-muted-foreground"
            >
                {{ t('projects.show.pulse.priorities') }}
            </h3>
            <div class="mt-4 space-y-3">
                <div
                    v-for="priority in priorityDistribution"
                    :key="priority.id"
                    class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-3 gap-y-1.5"
                >
                    <div class="flex min-w-0 items-center gap-2 text-base">
                        <span
                            class="size-2 shrink-0 rounded-full"
                            :style="{
                                backgroundColor: safeDefinitionColor(
                                    priority.color,
                                ),
                            }"
                            aria-hidden="true"
                        />
                        <span class="wrap-anywhere">{{ priority.name }}</span>
                    </div>
                    <span
                        class="text-[0.9375rem] leading-5 font-semibold tabular-nums"
                        >{{ formatNumber(priority.count) }}</span
                    >
                    <div
                        class="col-span-2 h-1.5 overflow-hidden rounded-full bg-muted"
                        aria-hidden="true"
                    >
                        <div
                            class="h-full w-full origin-left scale-x-[var(--progress)] rounded-full transition-transform duration-[var(--motion-state)] ease-[var(--ease-standard)] motion-reduce:transition-none"
                            :style="{
                                '--progress': priorityScale(priority.count),
                                backgroundColor: safeDefinitionColor(
                                    priority.color,
                                ),
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="px-5 py-5 sm:px-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold">
                        {{ t('projects.show.pulse.attention_title') }}
                    </h3>
                    <p
                        class="mt-1 text-[0.9375rem] leading-6 text-muted-foreground"
                    >
                        {{ t('projects.show.pulse.attention_description') }}
                    </p>
                </div>
                <span
                    class="rounded-full bg-orange-500/10 px-2.5 py-1 text-[0.9375rem] leading-5 font-semibold text-orange-800 tabular-nums"
                >
                    {{ formatNumber(attentionTasks.total) }}
                </span>
            </div>

            <div v-if="attentionTasks.data.length" class="mt-4 space-y-1.5">
                <button
                    v-for="task in attentionTasks.data"
                    :key="task.id"
                    type="button"
                    class="ui-lift group flex min-h-12 w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition-colors hover:bg-muted/60 focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none motion-reduce:transition-none pointer-coarse:min-h-13"
                    @click="emit('select', task)"
                >
                    <CalendarClock
                        class="size-4 shrink-0 text-orange-600"
                        aria-hidden="true"
                    />
                    <span class="min-w-0 flex-1">
                        <span
                            class="line-clamp-2 text-base font-medium wrap-anywhere group-hover:text-orange-800"
                        >
                            {{ task.title }}
                        </span>
                        <span
                            class="mt-0.5 flex flex-wrap items-center gap-1 text-[0.9375rem] leading-6 text-muted-foreground"
                        >
                            <CircleUserRound
                                class="size-3"
                                aria-hidden="true"
                            />
                            {{
                                task.assignee?.name ??
                                t('projects.show.attention.unassigned')
                            }}
                            <span aria-hidden="true">·</span>
                            <span
                                :class="
                                    isProjectTaskOverdue(
                                        task.due_date,
                                        task.is_completed,
                                        today,
                                    )
                                        ? 'font-medium text-destructive'
                                        : ''
                                "
                                >{{ dueLabel(task) }}</span
                            >
                        </span>
                    </span>
                    <ArrowRight
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </button>

                <Button
                    v-if="attentionTasks.total > attentionTasks.data.length"
                    type="button"
                    variant="ghost"
                    class="mt-2 w-full justify-between"
                    @click="emit('filter', moreAttentionFilter)"
                >
                    {{ t(`projects.show.attention.${moreAttentionFilter}`) }}
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Button>
            </div>
            <div
                v-else
                class="mt-4 flex items-start gap-3 rounded-xl border border-emerald-500/15 bg-emerald-500/[0.06] p-3 text-base text-emerald-900"
            >
                <CheckCircle2
                    class="mt-0.5 size-4 shrink-0"
                    aria-hidden="true"
                />
                <p class="leading-5">
                    {{ t('projects.show.pulse.no_attention') }}
                </p>
            </div>
        </div>
    </aside>
</template>
