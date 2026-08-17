<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    CalendarClock,
    CheckCircle2,
    LayoutDashboard,
    ListChecks,
} from '@lucide/vue';
import DashboardTaskQueue from '@/components/dashboard/DashboardTaskQueue.vue';
import ProductivityChart from '@/components/dashboard/ProductivityChart.vue';
import OnboardingChecklist from '@/components/onboarding/OnboardingChecklist.vue';
import type { OnboardingChecklistState } from '@/components/onboarding/OnboardingChecklist.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { Button } from '@/components/ui/button';
import { useUi } from '@/composables/useUi';
import { index as todoIndex } from '@/routes/todos';
import type { Todo } from '@/types/models';

defineProps<{
    stats: {
        today_count: number;
        overdue_count: number;
        completed_today: number;
        total_tasks: number;
        completed_total: number;
        completion_rate: number;
    };
    todayTasks: Todo[];
    overdueTasks: Todo[];
    upcomingTasks: Todo[];
    weeklyData: Array<{ date: string; completed: number; created: number }>;
    onboardingChecklist: OnboardingChecklistState;
}>();
const { formatNumber, t } = useUi();
</script>

<template>
    <div>
        <Head :title="t('dashboard.title')" />

        <WorkspacePageFrame>
            <WorkspacePageHeader
                :eyebrow="t('dashboard.weekly_productivity')"
                :title="t('dashboard.title')"
                :description="
                    t('dashboard.welcome', {
                        completed: formatNumber(stats.completed_total),
                        total: formatNumber(stats.total_tasks),
                        rate: formatNumber(stats.completion_rate),
                    })
                "
            >
                <template #icon>
                    <LayoutDashboard aria-hidden="true" />
                </template>

                <template #actions>
                    <Button as-child size="lg">
                        <Link :href="todoIndex()" prefetch>
                            {{ t('dashboard.review_tasks') }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </Button>
                </template>
                <template #metrics>
                    <WorkspaceMetric
                        :label="t('tasks.stats.total')"
                        :value="formatNumber(stats.total_tasks)"
                        :icon="ListChecks"
                        tone="orange"
                    />
                    <WorkspaceMetric
                        :label="t('dashboard.due_today')"
                        :value="formatNumber(stats.today_count)"
                        :icon="CalendarClock"
                        tone="blue"
                    />
                    <WorkspaceMetric
                        :label="t('dashboard.completed_today')"
                        :value="formatNumber(stats.completed_today)"
                        :icon="CheckCircle2"
                        tone="emerald"
                    />
                    <WorkspaceMetric
                        :label="t('tasks.stats.overdue')"
                        :value="formatNumber(stats.overdue_count)"
                        :icon="AlertTriangle"
                        tone="slate"
                    />
                </template>
            </WorkspacePageHeader>

            <OnboardingChecklist :checklist="onboardingChecklist" />

            <section aria-labelledby="dashboard-focus-title">
                <div class="mb-4 max-w-2xl">
                    <h2
                        id="dashboard-focus-title"
                        class="text-xl font-semibold tracking-[-0.025em] sm:text-2xl"
                    >
                        {{ t('dashboard.focus_title') }}
                    </h2>
                    <p class="mt-1.5 text-sm leading-6 text-muted-foreground">
                        {{ t('dashboard.focus_description') }}
                    </p>
                </div>

                <div
                    class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.75fr)]"
                >
                    <DashboardTaskQueue
                        :title="t('dashboard.overdue_tasks')"
                        :description="t('dashboard.overdue_guidance')"
                        :empty-message="t('dashboard.no_overdue')"
                        :todos="overdueTasks"
                        :count="stats.overdue_count"
                        tone="overdue"
                        :featured="true"
                    />

                    <div class="grid gap-5">
                        <DashboardTaskQueue
                            :title="t('dashboard.today_tasks')"
                            :description="t('dashboard.today_guidance')"
                            :empty-message="t('dashboard.no_today')"
                            :todos="todayTasks"
                            :count="stats.today_count"
                            tone="today"
                        />
                        <DashboardTaskQueue
                            :title="t('dashboard.upcoming_tasks')"
                            :description="t('dashboard.upcoming_guidance')"
                            :empty-message="t('dashboard.no_upcoming')"
                            :todos="upcomingTasks"
                            :count="upcomingTasks.length"
                            tone="upcoming"
                        />
                    </div>
                </div>
            </section>

            <ProductivityChart :data="weeklyData" />
        </WorkspacePageFrame>
    </div>
</template>
