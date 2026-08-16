<script setup lang="ts">
import {
    Activity,
    Archive,
    Bot,
    CheckCircle2,
    CirclePlus,
    Link2,
    PackageOpen,
    PenLine,
    Pin,
    RotateCcw,
    Star,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import type { ActivityLog } from '@/types/models';

const props = defineProps<{
    activities: ActivityLog[];
    filtered: boolean;
}>();
const { copy, formatDate } = useWorkspaceUi();

const groupedActivities = computed(() => {
    const groups = new Map<string, ActivityLog[]>();

    props.activities.forEach((activity) => {
        const key = dateKey(activity.created_at);
        groups.set(key, [...(groups.get(key) ?? []), activity]);
    });

    return Array.from(groups.entries()).map(([key, activities]) => ({
        key,
        label: groupLabel(activities[0]?.created_at ?? ''),
        activities,
    }));
});

function dateKey(value: string | Date): string {
    return formatDate(value, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
}

function groupLabel(value: string): string {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (dateKey(value) === dateKey(today)) {
        return copy.value.activity.today;
    }

    if (dateKey(value) === dateKey(yesterday)) {
        return copy.value.activity.yesterday;
    }

    return formatDate(value, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    });
}

function eventLabel(event: ActivityLog['event']): string {
    switch (event) {
        case 'archived':
            return copy.value.activity.event_archived;
        case 'attached':
            return copy.value.activity.event_attached;
        case 'completed':
            return copy.value.activity.event_completed;
        case 'created':
            return copy.value.activity.event_created;
        case 'deleted':
            return copy.value.activity.event_deleted;
        case 'detached':
            return copy.value.activity.event_detached;
        case 'favorited':
            return copy.value.activity.event_favorited;
        case 'pinned':
            return copy.value.activity.event_pinned;
        case 'recurrence_generated':
            return copy.value.activity.event_recurrence_generated;
        case 'restored':
            return copy.value.activity.event_restored;
        case 'unarchived':
            return copy.value.activity.event_unarchived;
        case 'uncompleted':
            return copy.value.activity.event_uncompleted;
        case 'unfavorited':
            return copy.value.activity.event_unfavorited;
        case 'unpinned':
            return copy.value.activity.event_unpinned;
        case 'updated':
            return copy.value.activity.event_updated;
        default:
            return copy.value.activity.event_changed;
    }
}

function sentenceTemplate(event: ActivityLog['event']): string {
    switch (event) {
        case 'archived':
            return copy.value.activity.sentence_archived;
        case 'attached':
            return copy.value.activity.sentence_attached;
        case 'completed':
            return copy.value.activity.sentence_completed;
        case 'created':
            return copy.value.activity.sentence_created;
        case 'deleted':
            return copy.value.activity.sentence_deleted;
        case 'detached':
            return copy.value.activity.sentence_detached;
        case 'favorited':
            return copy.value.activity.sentence_favorited;
        case 'pinned':
            return copy.value.activity.sentence_pinned;
        case 'recurrence_generated':
            return copy.value.activity.sentence_recurrence_generated;
        case 'restored':
            return copy.value.activity.sentence_restored;
        case 'unarchived':
            return copy.value.activity.sentence_unarchived;
        case 'uncompleted':
            return copy.value.activity.sentence_uncompleted;
        case 'unfavorited':
            return copy.value.activity.sentence_unfavorited;
        case 'unpinned':
            return copy.value.activity.sentence_unpinned;
        case 'updated':
            return copy.value.activity.sentence_updated;
        default:
            return copy.value.activity.sentence_changed;
    }
}

function subjectLabel(subjectType: string): string {
    return (
        {
            Project: copy.value.activity.subject_project,
            Todo: copy.value.activity.subject_todo,
            Workspace: copy.value.activity.subject_workspace,
        }[subjectType] ?? copy.value.activity.subject_item
    );
}

function initials(name: string): string {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toLocaleUpperCase();
}

function activitySentence(activity: ActivityLog): string {
    return sentenceTemplate(activity.event)
        .replace(':actor', activity.user?.name ?? copy.value.common.system)
        .replace(':subject', subjectLabel(activity.subject_type))
        .replace(
            ':label',
            activity.subject_label ? ` “${activity.subject_label}”` : '',
        );
}

function eventIcon(event: ActivityLog['event']): Component {
    return (
        {
            archived: Archive,
            attached: Link2,
            completed: CheckCircle2,
            created: CirclePlus,
            deleted: Trash2,
            detached: Link2,
            favorited: Star,
            pinned: Pin,
            recurrence_generated: Bot,
            restored: RotateCcw,
            unarchived: PackageOpen,
            uncompleted: RotateCcw,
            unfavorited: Star,
            unpinned: Pin,
            updated: PenLine,
        }[event] ?? Activity
    );
}

function eventTone(event: ActivityLog['event']): string {
    if (event === 'created') {
        return 'bg-orange-500/12 text-orange-700 dark:text-orange-300';
    }

    if (['updated', 'attached', 'detached'].includes(event)) {
        return 'bg-sky-500/12 text-sky-700 dark:text-sky-300';
    }

    if (['completed', 'uncompleted'].includes(event)) {
        return 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300';
    }

    if (event === 'recurrence_generated') {
        return 'bg-violet-500/12 text-violet-700 dark:text-violet-300';
    }

    if (event === 'deleted') {
        return 'bg-red-500/12 text-red-700 dark:text-red-300';
    }

    return 'bg-foreground/6 text-foreground/70';
}
</script>

<template>
    <div v-if="groupedActivities.length" class="space-y-10">
        <section
            v-for="group in groupedActivities"
            :key="group.key"
            class="grid gap-4 md:grid-cols-[8rem_minmax(0,1fr)]"
        >
            <h2
                class="pt-2 text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
            >
                {{ group.label }}
            </h2>

            <div
                class="relative space-y-2 before:absolute before:top-5 before:bottom-5 before:left-5 before:w-px before:bg-border"
            >
                <article
                    v-for="activity in group.activities"
                    :key="activity.id"
                    class="group relative grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3 rounded-2xl border border-transparent p-2 transition-colors duration-200 hover:border-border hover:bg-muted/35 motion-reduce:transition-none sm:grid-cols-[2.5rem_minmax(0,1fr)_auto] sm:items-center"
                >
                    <div
                        :class="[
                            'relative z-10 flex size-10 items-center justify-center rounded-2xl ring-4 ring-card',
                            eventTone(activity.event),
                        ]"
                    >
                        <component
                            :is="eventIcon(activity.event)"
                            class="size-4.5"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="min-w-0 py-0.5">
                        <p
                            class="text-sm leading-6 break-words text-foreground/90"
                        >
                            {{ activitySentence(activity) }}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{
                                formatDate(activity.created_at, {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}
                        </p>
                    </div>

                    <div
                        class="col-start-2 flex items-center gap-2 sm:col-start-auto"
                    >
                        <span
                            class="rounded-full border border-border/80 bg-background px-2.5 py-1 text-[0.68rem] font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ eventLabel(activity.event) }}
                        </span>
                        <span
                            class="hidden size-7 items-center justify-center rounded-full bg-muted text-[0.65rem] font-semibold text-muted-foreground sm:flex"
                            :title="activity.user?.name ?? copy.common.system"
                        >
                            {{
                                initials(
                                    activity.user?.name ?? copy.common.system,
                                )
                            }}
                        </span>
                    </div>
                </article>
            </div>
        </section>
    </div>

    <EmptyState
        v-else
        :title="
            filtered
                ? copy.activity.filtered_empty_title
                : copy.activity.empty_title
        "
        :description="
            filtered
                ? copy.activity.filtered_empty_description
                : copy.activity.empty_description
        "
    >
        <template #icon>
            <Activity class="size-7" aria-hidden="true" />
        </template>
    </EmptyState>
</template>
