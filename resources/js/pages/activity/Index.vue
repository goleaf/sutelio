<script setup lang="ts">
import { Head, InfiniteScroll, router } from '@inertiajs/vue3';
import { History, LoaderCircle, Sparkles, UsersRound } from '@lucide/vue';
import { ref } from 'vue';
import {
    activityPluralForm,
    buildActivityQuery,
    hasActivityFilters,
} from '@/components/activity/activity-types';
import type {
    ActivityCategory,
    ActivityContributor,
    ActivityFilters,
    ActivityMetrics,
    ActivityPaginator,
} from '@/components/activity/activity-types';
import ActivityFilterPanel from '@/components/activity/ActivityFilterPanel.vue';
import ActivityTimeline from '@/components/activity/ActivityTimeline.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { activity as currentActivity } from '@/routes';
import { index as workspaceActivity } from '@/routes/activity';

const props = defineProps<{
    activities: ActivityPaginator;
    metrics: ActivityMetrics;
    filters: ActivityFilters;
    categories: Exclude<ActivityCategory, 'all'>[];
    contributors: ActivityContributor[];
    workspace: { id: string; name: string } | null;
}>();
const { copy, formatNumber, locale } = useWorkspaceUi();
const filtering = ref(false);

function filterUrl(filters: ActivityFilters): string {
    const options = { query: buildActivityQuery(filters) };

    return props.workspace
        ? workspaceActivity.url(props.workspace.id, options)
        : currentActivity.url(options);
}

function updateFilters(filters: ActivityFilters): void {
    if (filterUrl(filters) === filterUrl(props.filters)) {
        return;
    }

    router.cancelAll();
    router.visit(filterUrl(filters), {
        only: ['activities', 'filters'],
        reset: ['activities'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onStart: () => {
            filtering.value = true;
        },
        onFinish: () => {
            filtering.value = false;
        },
    });
}

function resultSummary(): string {
    const plural = activityPluralForm(
        props.activities.meta.total,
        locale.value,
    );
    const template = (() => {
        switch (plural) {
            case 'one':
                return copy.value.activity.result_summary_one;
            case 'few':
                return copy.value.activity.result_summary_few;
            case 'many':
                return copy.value.activity.result_summary_many;
            default:
                return copy.value.activity.result_summary_other;
        }
    })();

    return template.replace(
        ':count',
        formatNumber(props.activities.meta.total, { useGrouping: true }),
    );
}
</script>

<template>
    <Head :title="copy.activity.title" />

    <main class="min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8">
        <div class="mx-auto flex max-w-app flex-col gap-6">
            <WorkspacePageHeader
                :eyebrow="copy.common.workspace_intelligence"
                :title="copy.activity.title"
                :description="copy.activity.description"
            >
                <template #metrics>
                    <WorkspaceMetric
                        :label="copy.activity.total_actions"
                        :value="formatNumber(metrics.recorded_actions)"
                        :icon="History"
                        tone="orange"
                    />
                    <WorkspaceMetric
                        :label="copy.activity.contributors"
                        :value="formatNumber(metrics.contributors)"
                        :icon="UsersRound"
                        tone="blue"
                    />
                    <WorkspaceMetric
                        :label="copy.activity.recent_changes"
                        :value="formatNumber(metrics.recent_changes)"
                        :icon="Sparkles"
                        tone="emerald"
                    />
                </template>
            </WorkspacePageHeader>

            <div
                class="grid min-w-0 grid-cols-1 items-start gap-6 lg:grid-cols-[17rem_minmax(0,1fr)]"
            >
                <ActivityFilterPanel
                    :filters="filters"
                    :categories="categories"
                    :contributors="contributors"
                    :processing="filtering"
                    @update="updateFilters"
                />

                <section
                    class="min-w-0 overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
                    :aria-busy="filtering"
                >
                    <div
                        class="flex min-h-18 flex-wrap items-center justify-between gap-3 border-b border-border/70 px-4 py-4 sm:px-6"
                        aria-live="polite"
                    >
                        <div>
                            <p class="text-sm font-semibold">
                                {{ copy.activity.result_count }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ resultSummary() }}
                            </p>
                        </div>
                        <div
                            v-if="filtering"
                            class="flex items-center gap-2 text-xs font-medium text-muted-foreground"
                            role="status"
                        >
                            <LoaderCircle
                                class="size-4 animate-spin motion-reduce:animate-none"
                                aria-hidden="true"
                            />
                            {{ copy.activity.updating_results }}
                        </div>
                        <div
                            v-else
                            class="flex items-center gap-2 text-xs text-muted-foreground"
                        >
                            <span
                                class="size-2 rounded-full bg-emerald-500"
                                aria-hidden="true"
                            />
                            {{ copy.activity.workspace_ledger }}
                        </div>
                    </div>

                    <InfiniteScroll data="activities" manual>
                        <div class="px-4 py-6 sm:px-6 sm:py-7">
                            <ActivityTimeline
                                :activities="activities.data"
                                :filtered="hasActivityFilters(filters)"
                            />
                        </div>

                        <template #next="{ loading, fetch, hasMore }">
                            <div
                                v-if="activities.data.length"
                                class="flex min-h-20 items-center justify-center border-t border-border/70 px-4 py-4"
                            >
                                <Button
                                    v-if="hasMore"
                                    type="button"
                                    variant="outline"
                                    class="min-w-40 motion-reduce:transition-none"
                                    :disabled="loading"
                                    @click="fetch"
                                >
                                    <LoaderCircle
                                        v-if="loading"
                                        class="size-4 animate-spin motion-reduce:animate-none"
                                        aria-hidden="true"
                                    />
                                    {{
                                        loading
                                            ? copy.activity.loading_older
                                            : copy.activity.load_older
                                    }}
                                </Button>
                                <p
                                    v-else
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    {{ copy.activity.end_of_activity }}
                                </p>
                            </div>
                        </template>
                    </InfiniteScroll>
                </section>
            </div>
        </div>
    </main>
</template>
