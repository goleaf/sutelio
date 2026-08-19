<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import TaskDescriptionField from '@/components/task/TaskDescriptionField.vue';
import TaskTitleField from '@/components/task/TaskTitleField.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePickerField } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTaskDefinitions } from '@/composables/useTaskDefinitions';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { show, store } from '@/routes/todos';
import type { Project, TaskDefinitionCatalog, Todo } from '@/types/models';

interface TaskResponse {
    todo: Todo;
}

const props = defineProps<{
    workspaceId: string;
    projects: Project[];
    selectedProjectId?: string | null;
    taskDefinitions: TaskDefinitionCatalog;
    cancelHref: string;
}>();
const toast = useToast();
const { t } = useUi();
const { statuses, priorities, defaultStatus, defaultPriority } =
    useTaskDefinitions(() => props.taskDefinitions);
const form = useHttp<
    {
        title: string;
        description: string;
        status: string;
        priority: string;
        due_date: string;
        project_id: string;
        is_recurring: boolean;
        recurring_rule: string;
    },
    TaskResponse
>({
    title: '',
    description: '',
    status: defaultStatus.value?.key ?? 'pending',
    priority: defaultPriority.value?.key ?? 'none',
    due_date: '',
    project_id: props.selectedProjectId ?? 'none',
    is_recurring: false,
    recurring_rule: 'none',
});

async function submit(): Promise<void> {
    if (!form.title.trim()) {
        form.setError('title', t('tasks.create.title_required'));

        return;
    }

    form.title = form.title.trim();
    form.description = form.description.trim();
    form.transform((data) => ({
        ...data,
        project_id: data.project_id === 'none' ? '' : data.project_id,
    }));

    try {
        const response = await form.submit(store(props.workspaceId));
        toast.success(t('tasks.create.created'));
        router.visit(show(response.todo).url);
    } catch {
        if (!form.hasErrors) {
            toast.error(t('tasks.create.create_failed'));
        }
    }
}
</script>

<template>
    <form
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        @submit.prevent="submit"
    >
        <div class="space-y-6 p-4 sm:p-6">
            <TaskTitleField
                v-model="form.title"
                :label="t('tasks.create.title')"
                :error="form.errors.title"
                :placeholder="t('tasks.create.title_placeholder')"
                :disabled="form.processing"
                autofocus
                @input="form.clearErrors('title')"
            />
            <TaskDescriptionField
                v-model="form.description"
                :label="t('tasks.create.description')"
                :error="form.errors.description"
                :placeholder="t('tasks.create.description_placeholder')"
                :disabled="form.processing"
                @input="form.clearErrors('description')"
            />

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-2">
                    <Label for="task-project">{{
                        t('tasks.create.project')
                    }}</Label>
                    <Select
                        v-model="form.project_id"
                        :disabled="form.processing"
                    >
                        <SelectTrigger
                            id="task-project"
                            :aria-invalid="Boolean(form.errors.project_id)"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">{{
                                t('tasks.create.no_project')
                            }}</SelectItem>
                            <SelectItem
                                v-for="project in projects"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.project_id" />
                </div>

                <div class="space-y-2">
                    <Label for="task-status">{{
                        t('tasks.create.status')
                    }}</Label>
                    <Select v-model="form.status" :disabled="form.processing">
                        <SelectTrigger
                            id="task-status"
                            :aria-invalid="Boolean(form.errors.status)"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="status in statuses"
                                :key="status.id"
                                :value="status.key"
                            >
                                {{ status.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="space-y-2">
                    <Label for="task-priority">{{
                        t('tasks.create.priority')
                    }}</Label>
                    <Select v-model="form.priority" :disabled="form.processing">
                        <SelectTrigger
                            id="task-priority"
                            :aria-invalid="Boolean(form.errors.priority)"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="priority in priorities"
                                :key="priority.id"
                                :value="priority.key"
                            >
                                {{ priority.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.priority" />
                </div>

                <div class="space-y-2">
                    <Label for="task-due-date">{{
                        t('tasks.create.due_date')
                    }}</Label>
                    <DatePickerField
                        id="task-due-date"
                        v-model="form.due_date"
                        :label="t('tasks.create.due_date')"
                        :disabled="form.processing"
                        :invalid="Boolean(form.errors.due_date)"
                        :described-by="
                            form.errors.due_date
                                ? 'task-due-date-error'
                                : undefined
                        "
                    />
                    <InputError
                        id="task-due-date-error"
                        :message="form.errors.due_date"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="task-recurring-rule">{{
                    t('tasks.create.repeat')
                }}</Label>
                <Select
                    v-model="form.recurring_rule"
                    :disabled="!form.is_recurring || form.processing"
                >
                    <SelectTrigger
                        id="task-recurring-rule"
                        :aria-invalid="Boolean(form.errors.recurring_rule)"
                    >
                        <SelectValue
                            :placeholder="
                                form.is_recurring
                                    ? t('tasks.create.frequency_placeholder')
                                    : t('tasks.create.no_repeat')
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">{{
                            t('tasks.create.no_repeat')
                        }}</SelectItem>
                        <SelectItem value="FREQ=DAILY">{{
                            t('tasks.recurring.daily')
                        }}</SelectItem>
                        <SelectItem value="FREQ=WEEKLY">{{
                            t('tasks.recurring.weekly')
                        }}</SelectItem>
                        <SelectItem value="FREQ=MONTHLY">{{
                            t('tasks.recurring.monthly')
                        }}</SelectItem>
                        <SelectItem value="FREQ=YEARLY">{{
                            t('tasks.recurring.yearly')
                        }}</SelectItem>
                        <SelectItem value="FREQ=DAILY;INTERVAL=2">{{
                            t('tasks.recurring.every_2_days')
                        }}</SelectItem>
                        <SelectItem value="FREQ=WEEKLY;INTERVAL=2">{{
                            t('tasks.recurring.every_2_weeks')
                        }}</SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.recurring_rule" />
                <div
                    class="mt-3 flex min-h-12 items-center gap-3 rounded-xl border border-border/70 bg-muted/25 px-3.5 pointer-coarse:min-h-13"
                >
                    <Checkbox
                        id="task-is-recurring"
                        :model-value="form.is_recurring"
                        class="size-4.5 data-[state=checked]:border-orange-600 data-[state=checked]:bg-orange-600"
                        :disabled="form.processing"
                        @update:model-value="
                            form.is_recurring = Boolean($event)
                        "
                    />
                    <Label
                        for="task-is-recurring"
                        class="cursor-pointer text-base font-normal text-muted-foreground"
                    >
                        {{ t('tasks.create.repeat_task') }}
                    </Label>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-2 border-t border-border/70 bg-muted/20 p-4 min-[30rem]:flex-row min-[30rem]:justify-end sm:px-6"
        >
            <Button
                as-child
                variant="outline"
                size="lg"
                :disabled="form.processing"
            >
                <Link :href="cancelHref">
                    <X aria-hidden="true" />
                    {{ t('common.actions.cancel') }}
                </Link>
            </Button>
            <Button
                type="submit"
                size="lg"
                :loading="form.processing"
                :loading-label="t('tasks.create.creating')"
            >
                <Plus aria-hidden="true" />
                {{ t('tasks.create.submit') }}
            </Button>
        </div>
    </form>
</template>
