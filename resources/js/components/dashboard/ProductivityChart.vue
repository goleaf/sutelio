<script setup lang="ts">
import { Activity, CheckCircle2, PlusCircle } from '@lucide/vue';
import { computed } from 'vue';
import { Card } from '@/components/ui/card';
import { useUi } from '@/composables/useUi';

const props = defineProps<{
    data: Array<{ date: string; completed: number; created: number }>;
}>();
const { formatDate: formatLocalizedDate, formatNumber, t } = useUi();

const completedTotal = computed(() =>
    props.data.reduce((total, day) => total + day.completed, 0),
);
const createdTotal = computed(() =>
    props.data.reduce((total, day) => total + day.created, 0),
);
const hasActivity = computed(
    () => completedTotal.value > 0 || createdTotal.value > 0,
);
const maxValue = computed(() =>
    Math.max(
        ...props.data.map((day) => Math.max(day.completed, day.created)),
        1,
    ),
);

function barHeight(value: number): string {
    return value === 0
        ? '0%'
        : `${Math.max((value / maxValue.value) * 100, 6)}%`;
}

function formatWeekday(date: string): string {
    return formatLocalizedDate(date, { weekday: 'short' });
}

function formatFullDate(date: string): string {
    return formatLocalizedDate(date, {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <section aria-labelledby="weekly-activity-title">
        <Card class="gap-0 overflow-hidden py-0">
            <header
                class="flex flex-col gap-5 border-b border-border/70 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="max-w-2xl">
                    <p
                        class="flex items-center gap-2 text-[0.7rem] font-semibold tracking-[0.2em] text-orange-700 uppercase dark:text-orange-300"
                    >
                        <Activity class="size-3.5" aria-hidden="true" />
                        {{ t('dashboard.weekly_productivity') }}
                    </p>
                    <h2
                        id="weekly-activity-title"
                        class="mt-2 text-xl font-semibold tracking-[-0.025em] sm:text-2xl"
                    >
                        {{ t('dashboard.weekly_overview') }}
                    </h2>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        {{
                            t('dashboard.weekly_totals', {
                                completed: formatNumber(completedTotal),
                                created: formatNumber(createdTotal),
                            })
                        }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:min-w-72">
                    <div
                        class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.06] px-4 py-3"
                    >
                        <p
                            class="flex items-center gap-1.5 text-xs text-orange-800 dark:text-orange-200"
                        >
                            <CheckCircle2 class="size-3.5" aria-hidden="true" />
                            {{ t('tasks.stats.completed') }}
                        </p>
                        <p class="mt-1 text-xl font-semibold tabular-nums">
                            {{ formatNumber(completedTotal) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-sky-500/15 bg-sky-500/[0.06] px-4 py-3"
                    >
                        <p
                            class="flex items-center gap-1.5 text-xs text-sky-800 dark:text-sky-200"
                        >
                            <PlusCircle class="size-3.5" aria-hidden="true" />
                            {{ t('dashboard.created') }}
                        </p>
                        <p class="mt-1 text-xl font-semibold tabular-nums">
                            {{ formatNumber(createdTotal) }}
                        </p>
                    </div>
                </div>
            </header>

            <div
                class="grid gap-6 px-4 py-6 sm:px-7 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.7fr)] lg:items-stretch lg:gap-8"
            >
                <figure
                    class="rounded-2xl border border-border/70 bg-muted/20 p-4 sm:p-5"
                >
                    <figcaption class="sr-only">
                        {{ t('dashboard.weekly_table_caption') }}
                    </figcaption>

                    <div
                        v-if="!hasActivity"
                        class="flex min-h-56 flex-col items-center justify-center gap-3 text-center"
                    >
                        <span
                            class="flex size-12 items-center justify-center rounded-2xl border border-orange-500/15 bg-orange-500/[0.06] text-orange-700 dark:text-orange-300"
                        >
                            <Activity class="size-5" aria-hidden="true" />
                        </span>
                        <p class="max-w-xs text-sm text-muted-foreground">
                            {{ t('dashboard.no_weekly_activity') }}
                        </p>
                    </div>

                    <template v-else>
                        <div
                            class="grid grid-cols-7 items-end gap-1.5 sm:gap-3"
                            role="list"
                        >
                            <div
                                v-for="day in data"
                                :key="day.date"
                                class="flex min-w-0 flex-col items-center gap-2"
                                role="listitem"
                            >
                                <span class="sr-only">
                                    {{
                                        t('dashboard.weekly_day_summary', {
                                            date: formatFullDate(day.date),
                                            completed: formatNumber(
                                                day.completed,
                                            ),
                                            created: formatNumber(day.created),
                                        })
                                    }}
                                </span>
                                <div
                                    class="flex h-40 w-full items-end justify-center gap-1 sm:gap-2"
                                    aria-hidden="true"
                                >
                                    <div
                                        class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1"
                                    >
                                        <div
                                            class="text-[0.65rem] font-medium text-muted-foreground tabular-nums"
                                        >
                                            {{ formatNumber(day.completed) }}
                                        </div>
                                        <div
                                            class="w-full max-w-5 rounded-t-md bg-orange-500/85 transition-[height] duration-300 motion-reduce:transition-none"
                                            :style="{
                                                height: barHeight(
                                                    day.completed,
                                                ),
                                            }"
                                        />
                                    </div>
                                    <div
                                        class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1"
                                    >
                                        <span
                                            class="text-[0.65rem] font-medium text-muted-foreground tabular-nums"
                                        >
                                            {{ formatNumber(day.created) }}
                                        </span>
                                        <div
                                            class="w-full max-w-5 rounded-t-md bg-sky-500/70 transition-[height] duration-300 motion-reduce:transition-none"
                                            :style="{
                                                height: barHeight(day.created),
                                            }"
                                        />
                                    </div>
                                </div>
                                <span
                                    class="truncate text-[0.7rem] text-muted-foreground"
                                    aria-hidden="true"
                                >
                                    {{ formatWeekday(day.date) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap items-center gap-4 text-xs text-muted-foreground"
                        >
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="size-2.5 rounded-sm bg-orange-500/85"
                                    aria-hidden="true"
                                />
                                {{ t('tasks.stats.completed') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="size-2.5 rounded-sm bg-sky-500/70"
                                    aria-hidden="true"
                                />
                                {{ t('dashboard.created') }}
                            </span>
                        </div>
                    </template>
                </figure>

                <div
                    class="overflow-hidden rounded-2xl border border-border/70"
                >
                    <table class="w-full table-fixed text-left text-sm">
                        <caption class="sr-only">
                            {{
                                t('dashboard.weekly_table_caption')
                            }}
                        </caption>
                        <thead
                            class="bg-muted/55 text-xs text-muted-foreground"
                        >
                            <tr>
                                <th
                                    scope="col"
                                    class="w-1/2 px-3 py-3 font-medium"
                                >
                                    {{ t('dashboard.date') }}
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-3 text-right font-medium"
                                >
                                    {{ t('tasks.stats.completed') }}
                                </th>
                                <th
                                    scope="col"
                                    class="px-3 py-3 text-right font-medium"
                                >
                                    {{ t('dashboard.created') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/70">
                            <tr v-for="day in data" :key="day.date">
                                <th
                                    scope="row"
                                    class="truncate px-3 py-3 font-medium"
                                    :title="formatFullDate(day.date)"
                                >
                                    {{ formatWeekday(day.date) }}
                                </th>
                                <td class="px-2 py-3 text-right tabular-nums">
                                    {{ formatNumber(day.completed) }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums">
                                    {{ formatNumber(day.created) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Card>
    </section>
</template>
