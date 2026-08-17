<script setup lang="ts">
import { ArrowDownAZ, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTaskDefinitions } from '@/composables/useTaskDefinitions';
import { useUi } from '@/composables/useUi';
import type { Project, TaskDefinitionCatalog } from '@/types/models';

const props = withDefaults(
    defineProps<{
        clearDisabled?: boolean;
        disabled?: boolean;
        mode?: 'desktop' | 'mobile';
        projects: Project[];
        taskDefinitions: TaskDefinitionCatalog;
    }>(),
    {
        clearDisabled: false,
        disabled: false,
        mode: 'desktop',
    },
);

const emit = defineEmits<{
    clear: [];
    commit: [];
}>();

const projectId = defineModel<string>('projectId', { required: true });
const status = defineModel<string>('status', { required: true });
const priority = defineModel<string>('priority', { required: true });
const sort = defineModel<string>('sort', { required: true });
const direction = defineModel<'asc' | 'desc'>('direction', { required: true });
const perPage = defineModel<'100' | '25' | '50'>('perPage', {
    required: true,
});

const { t } = useUi();
const { priorities, statuses } = useTaskDefinitions(
    () => props.taskDefinitions,
);

function commitOnDesktop(): void {
    if (props.mode === 'desktop') {
        emit('commit');
    }
}

function toggleDirection(): void {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    commitOnDesktop();
}
</script>

<template>
    <div
        data-slot="task-filter-fields"
        :class="
            props.mode === 'desktop'
                ? 'hidden gap-3 md:grid md:grid-cols-3 xl:grid-cols-6'
                : 'grid gap-4'
        "
    >
        <Select
            v-model="projectId"
            :disabled="props.disabled"
            @update:model-value="commitOnDesktop"
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
                    v-for="project in props.projects"
                    :key="project.id"
                    :value="project.id"
                >
                    {{ project.name }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            v-model="status"
            :disabled="props.disabled"
            @update:model-value="commitOnDesktop"
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
            :disabled="props.disabled"
            @update:model-value="commitOnDesktop"
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
            :disabled="props.disabled"
            @update:model-value="commitOnDesktop"
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
            :disabled="props.disabled"
            @click="toggleDirection"
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
                :disabled="props.disabled"
                @update:model-value="commitOnDesktop"
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
                v-if="props.mode === 'desktop'"
                type="button"
                variant="ghost"
                size="icon"
                class="min-h-11 min-w-11"
                :aria-label="t('tasks.filters.clear')"
                :disabled="props.disabled || props.clearDisabled"
                @click="emit('clear')"
            >
                <X class="size-4" aria-hidden="true" />
            </Button>
        </div>
    </div>
</template>
