<script setup lang="ts">
import { ArrowUpDown } from '@lucide/vue';
import type {
    ProjectAssignee,
    ProjectAttention,
    ProjectSort,
} from '@/components/project/project-operations';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTaskDefinitions } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import type { TaskDefinitionCatalog } from '@/types/models';

const props = withDefaults(
    defineProps<{
        assignees: ProjectAssignee[];
        disabled?: boolean;
        mode?: 'desktop' | 'mobile';
        taskDefinitions: TaskDefinitionCatalog;
    }>(),
    {
        disabled: false,
        mode: 'desktop',
    },
);

const emit = defineEmits<{ commit: [] }>();
const status = defineModel<string>('status', { required: true });
const priority = defineModel<string>('priority', { required: true });
const assignee = defineModel<string>('assignee', { required: true });
const attention = defineModel<ProjectAttention>('attention', {
    required: true,
});
const sort = defineModel<ProjectSort>('sort', { required: true });

const { t } = useUi();
const { priorities, statuses } = useTaskDefinitions(
    () => props.taskDefinitions,
);
const attentionOptions = [
    { label: 'projects.show.attention.all', value: 'all' as const },
    { label: 'projects.show.attention.overdue', value: 'overdue' as const },
    { label: 'projects.show.attention.due_soon', value: 'due_soon' as const },
    {
        label: 'projects.show.attention.unassigned',
        value: 'unassigned' as const,
    },
] as const;

function commitOnDesktop(): void {
    if (props.mode === 'desktop') {
        emit('commit');
    }
}
</script>

<template>
    <div
        data-slot="project-task-filter-fields"
        :class="
            props.mode === 'desktop'
                ? 'mt-4 hidden gap-3 lg:grid lg:grid-cols-4'
                : 'grid gap-5'
        "
    >
        <div :class="props.mode === 'mobile' ? 'grid gap-2' : 'contents'">
            <span v-if="props.mode === 'mobile'" class="text-sm font-medium">
                {{ t('projects.show.filters.status') }}
            </span>
            <Select
                v-model="status"
                :disabled="props.disabled"
                @update:model-value="commitOnDesktop"
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
        </div>

        <div
            :class="
                props.mode === 'mobile'
                    ? 'grid gap-2 sm:grid-cols-2'
                    : 'contents'
            "
        >
            <Select
                v-model="priority"
                :disabled="props.disabled"
                @update:model-value="commitOnDesktop"
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
                :disabled="props.disabled"
                @update:model-value="commitOnDesktop"
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
                        v-for="member in props.assignees"
                        :key="member.id"
                        :value="member.id"
                    >
                        {{ member.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div
            v-if="props.mode === 'mobile'"
            class="grid grid-cols-2 gap-2"
            role="group"
            :aria-label="t('projects.show.filters.attention')"
        >
            <button
                v-for="option in attentionOptions"
                :key="option.value"
                type="button"
                :aria-pressed="attention === option.value"
                :disabled="props.disabled"
                class="min-h-11 rounded-xl border px-3 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:outline-none disabled:opacity-50 motion-reduce:transition-none"
                :class="
                    attention === option.value
                        ? 'border-orange-500/25 bg-orange-500/10 text-orange-800'
                        : 'border-border/80 text-muted-foreground'
                "
                @click="attention = option.value"
            >
                {{ t(option.label) }}
            </button>
        </div>

        <Select
            v-model="sort"
            :disabled="props.disabled"
            @update:model-value="commitOnDesktop"
        >
            <SelectTrigger
                class="min-h-11"
                :aria-label="t('projects.show.filters.sort')"
            >
                <ArrowUpDown
                    v-if="props.mode === 'desktop'"
                    class="size-4"
                    aria-hidden="true"
                />
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
</template>
