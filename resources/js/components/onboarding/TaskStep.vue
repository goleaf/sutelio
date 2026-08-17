<script setup lang="ts">
import { CalendarClock, ListChecks } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import type {
    OnboardingCopy,
    OnboardingDefinition,
    OnboardingMember,
    OnboardingMode,
    OnboardingTask,
} from '@/components/onboarding/onboarding-types';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    copy: OnboardingCopy['task'];
    tasks: OnboardingTask[];
    members: OnboardingMember[];
    statuses: OnboardingDefinition[];
    priorities: OnboardingDefinition[];
    mode: OnboardingMode;
    selectedId: string;
    title: string;
    description: string;
    statusId: string;
    priorityId: string;
    assigneeId: string;
    dueDate: string;
    errors: Record<string, string>;
    processing: boolean;
}>();

const emit = defineEmits<{
    'update:mode': [mode: OnboardingMode];
    'update:selectedId': [id: string];
    'update:title': [title: string];
    'update:description': [description: string];
    'update:statusId': [id: string];
    'update:priorityId': [id: string];
    'update:assigneeId': [id: string];
    'update:dueDate': [date: string];
}>();

const selected = computed(() =>
    props.tasks.find((item) => item.id === props.selectedId),
);
const selectedPriority = computed(() =>
    props.priorities.find((item) => item.id === props.priorityId),
);
const selectedId = computed({
    get: () => props.selectedId,
    set: (value: string) => emit('update:selectedId', value),
});
const title = computed({
    get: () => props.title,
    set: (value: string | number) => emit('update:title', String(value)),
});
const description = computed({
    get: () => props.description,
    set: (value: string) => emit('update:description', value),
});
const statusId = computed({
    get: () => props.statusId,
    set: (value: string) => emit('update:statusId', value),
});
const priorityId = computed({
    get: () => props.priorityId,
    set: (value: string) => emit('update:priorityId', value),
});
const assigneeId = computed({
    get: () => props.assigneeId,
    set: (value: string) => emit('update:assigneeId', value),
});
const dueDate = computed({
    get: () => props.dueDate,
    set: (value: string | number) => emit('update:dueDate', String(value)),
});
</script>

<template>
    <div class="space-y-5">
        <p class="text-sm leading-6 text-muted-foreground">
            {{ copy.description }}
        </p>
        <div
            class="grid grid-cols-2 gap-2 rounded-2xl bg-muted/55 p-1.5"
            role="group"
        >
            <Button
                type="button"
                variant="ghost"
                class="min-h-11"
                :aria-pressed="mode === 'select'"
                :disabled="processing || tasks.length === 0"
                :class="mode === 'select' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'select')"
                >{{ copy.choose_existing }}</Button
            >
            <Button
                type="button"
                variant="ghost"
                class="min-h-11"
                :aria-pressed="mode === 'create'"
                :disabled="processing"
                :class="mode === 'create' ? 'bg-background shadow-sm' : ''"
                @click="emit('update:mode', 'create')"
                >{{ copy.create_new }}</Button
            >
        </div>

        <div
            v-if="mode === 'select' && tasks.length"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)]"
        >
            <div class="space-y-2">
                <Label for="task_id">{{ copy.existing_label }}</Label>
                <Select v-model="selectedId" :disabled="processing">
                    <SelectTrigger
                        id="task_id"
                        :aria-invalid="Boolean(errors.task_id)"
                        ><SelectValue
                    /></SelectTrigger>
                    <SelectContent
                        ><SelectItem
                            v-for="task in tasks"
                            :key="task.id"
                            :value="task.id"
                            >{{ task.title }}</SelectItem
                        ></SelectContent
                    >
                </Select>
                <InputError :message="errors.task_id" />
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <LeadingIconHeading content-class="gap-2">
                    <template #icon>
                        <ListChecks
                            class="size-5 text-orange-600"
                            aria-hidden="true"
                        />
                    </template>

                    <h2 class="font-semibold">{{ copy.preview_title }}</h2>
                    <p class="text-sm font-medium break-words">
                        {{ selected?.title }}
                    </p>
                    <p
                        v-if="selected?.due_date"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CalendarClock class="size-3.5" aria-hidden="true" />{{
                            selected.due_date
                        }}
                    </p>
                </LeadingIconHeading>
            </aside>
        </div>

        <div
            v-else-if="mode === 'create'"
            class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,0.45fr)]"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="title">{{ copy.title }}</Label>
                    <Input
                        id="title"
                        v-model="title"
                        :placeholder="copy.title_placeholder"
                        :disabled="processing"
                        :aria-invalid="Boolean(errors.title)"
                        autocomplete="off"
                    />
                    <InputError :message="errors.title" />
                </div>
                <div class="space-y-2">
                    <Label for="description">{{ copy.details }}</Label>
                    <textarea
                        id="description"
                        v-model="description"
                        rows="3"
                        :placeholder="copy.details_placeholder"
                        :disabled="processing"
                        :aria-invalid="Boolean(errors.description)"
                        class="min-h-24 w-full resize-y rounded-xl border border-input bg-background px-3.5 py-3 text-sm transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-orange-500 focus-visible:ring-3 focus-visible:ring-orange-500/20 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none"
                    />
                    <InputError :message="errors.description" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="status_id">{{ copy.status }}</Label>
                        <Select v-model="statusId" :disabled="processing">
                            <SelectTrigger
                                id="status_id"
                                :aria-invalid="Boolean(errors.status_id)"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent
                                ><SelectItem
                                    v-for="status in statuses"
                                    :key="status.id"
                                    :value="status.id"
                                    >{{ status.name }}</SelectItem
                                ></SelectContent
                            >
                        </Select>
                        <InputError :message="errors.status_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="priority_id">{{ copy.priority }}</Label>
                        <Select v-model="priorityId" :disabled="processing">
                            <SelectTrigger
                                id="priority_id"
                                :aria-invalid="Boolean(errors.priority_id)"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent
                                ><SelectItem
                                    v-for="priority in priorities"
                                    :key="priority.id"
                                    :value="priority.id"
                                    >{{ priority.name }}</SelectItem
                                ></SelectContent
                            >
                        </Select>
                        <InputError :message="errors.priority_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="assigned_to">{{ copy.assignee }}</Label>
                        <Select v-model="assigneeId" :disabled="processing">
                            <SelectTrigger
                                id="assigned_to"
                                :aria-invalid="Boolean(errors.assigned_to)"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="unassigned">{{
                                    copy.unassigned
                                }}</SelectItem>
                                <SelectItem
                                    v-for="member in members"
                                    :key="member.id"
                                    :value="member.id"
                                    >{{ member.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.assigned_to" />
                    </div>
                    <div class="space-y-2">
                        <Label for="due_date">{{ copy.due_date }}</Label>
                        <Input
                            id="due_date"
                            v-model="dueDate"
                            type="date"
                            :disabled="processing"
                            :aria-invalid="Boolean(errors.due_date)"
                        />
                        <InputError :message="errors.due_date" />
                    </div>
                </div>
            </div>
            <aside
                class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
            >
                <LeadingIconHeading content-class="gap-2">
                    <template #icon>
                        <span
                            class="block size-3 rounded-full ring-4 ring-background"
                            :style="{
                                backgroundColor:
                                    selectedPriority?.color ?? '#ff6038',
                            }"
                            aria-hidden="true"
                        />
                    </template>

                    <h2 class="font-semibold">{{ copy.preview_title }}</h2>
                    <p class="text-sm font-medium break-words">
                        {{ title || copy.title_placeholder }}
                    </p>
                    <p
                        class="text-xs leading-5 break-words text-muted-foreground"
                    >
                        {{ description || copy.details_placeholder }}
                    </p>
                    <p
                        v-if="dueDate"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CalendarClock class="size-3.5" aria-hidden="true" />{{
                            dueDate
                        }}
                    </p>
                </LeadingIconHeading>
            </aside>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-border p-6 text-center"
        >
            <h2 class="font-semibold">{{ copy.empty_title }}</h2>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                {{ copy.empty_description }}
            </p>
        </div>
    </div>
</template>
