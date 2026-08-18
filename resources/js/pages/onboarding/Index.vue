<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import {
    complete as completeOnboarding,
    exitReplay as exitOnboardingReplay,
    preferences as savePreferences,
    progress as saveProgress,
    project as saveProject,
    task as saveTask,
    workspace as saveWorkspace,
} from '@/actions/App/Http/Controllers/OnboardingController';
import type {
    OnboardingCopy,
    OnboardingMode,
    OnboardingOptions,
    OnboardingPreferences,
    OnboardingProgress,
    OnboardingRecovery,
    OnboardingState,
    OnboardingStep,
} from '@/components/onboarding/onboarding-types';
import { orderedOnboardingSteps } from '@/components/onboarding/onboarding-types';
import OnboardingShell from '@/components/onboarding/OnboardingShell.vue';
import OnboardingStepPanel from '@/components/onboarding/OnboardingStepPanel.vue';
import PreferencesStep from '@/components/onboarding/PreferencesStep.vue';
import ProductMapStep from '@/components/onboarding/ProductMapStep.vue';
import ProjectStep from '@/components/onboarding/ProjectStep.vue';
import ResultsStep from '@/components/onboarding/ResultsStep.vue';
import SafetyStep from '@/components/onboarding/SafetyStep.vue';
import TaskStep from '@/components/onboarding/TaskStep.vue';
import WelcomeStep from '@/components/onboarding/WelcomeStep.vue';
import WorkspaceStep from '@/components/onboarding/WorkspaceStep.vue';
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
import type { TimeZoneGroup } from '@/types/timezone';

const props = defineProps<{
    progress: OnboardingProgress;
    preferences: OnboardingPreferences;
    state: OnboardingState;
    recovery: OnboardingRecovery;
    options: OnboardingOptions;
    timezoneGroups: TimeZoneGroup[];
    copy: OnboardingCopy;
}>();

type SaveStatus = 'idle' | 'saving' | 'saved' | 'error' | 'resumed';

const saveStatus = ref<SaveStatus>(props.recovery ? 'resumed' : 'idle');
const form = useForm({
    target_step: '' as OnboardingStep | '',
    language: props.preferences.language,
    timezone: props.preferences.timezone,
    date_format: props.preferences.date_format,
    time_format: props.preferences.time_format,
    default_view: props.preferences.default_view,
    start_page: props.preferences.start_page,
    week_start: props.preferences.week_start,
    mode: 'select' as OnboardingMode,
    request_key: createRequestKey(),
    workspace_id: '',
    project_id: '',
    task_id: '',
    name: '',
    description: '',
    color: '#ff6038',
    icon: 'folder',
    title: '',
    status_id: '',
    priority_id: '',
    assigned_to: 'unassigned',
    due_date: '',
});

const activeStep = computed(() => props.progress.step);
const activeCopy = computed(() => props.copy.steps[activeStep.value]);
const primaryLabel = computed(() =>
    activeStep.value === 'results'
        ? props.copy.actions.finish
        : props.copy.actions.continue,
);
const selectedWorkspace = computed(() =>
    props.options.workspaces.find(
        (item) => item.id === props.state.workspace_id,
    ),
);
const selectedProject = computed(() =>
    props.options.projects.find((item) => item.id === props.state.project_id),
);
const selectedTask = computed(() =>
    props.options.tasks.find((item) => item.id === props.state.task_id),
);
const canManageWorkspace = computed(() =>
    ['owner', 'admin'].includes(selectedWorkspace.value?.role ?? ''),
);
const recoveryMessage = computed(() =>
    props.recovery ? (props.copy.errors[props.recovery] ?? null) : null,
);
const preferencesDraft = computed<OnboardingPreferences>(() => ({
    language: form.language,
    timezone: form.timezone,
    date_format: form.date_format,
    time_format: form.time_format,
    default_view: form.default_view,
    start_page: form.start_page,
    week_start: form.week_start,
}));
const errorLabels = computed<Record<string, string>>(() => ({
    target_step: activeCopy.value.title,
    language: props.copy.preferences.language,
    timezone: props.copy.preferences.timezone,
    date_format: props.copy.preferences.date_format,
    time_format: props.copy.preferences.time_format,
    default_view: props.copy.preferences.default_view,
    start_page: props.copy.preferences.start_page,
    week_start: props.copy.preferences.week_start,
    workspace_id: props.copy.workspace.existing_label,
    project_id: props.copy.project.existing_label,
    task_id: props.copy.task.existing_label,
    name:
        activeStep.value === 'workspace'
            ? props.copy.workspace.name
            : props.copy.project.name,
    description:
        activeStep.value === 'workspace'
            ? props.copy.workspace.details
            : activeStep.value === 'project'
              ? props.copy.project.details
              : props.copy.task.details,
    color: props.copy.project.color,
    icon: props.copy.project.icon,
    title: props.copy.task.title,
    status_id: props.copy.task.status,
    priority_id: props.copy.task.priority,
    assigned_to: props.copy.task.assignee,
    due_date: props.copy.task.due_date,
}));

watch(
    () =>
        [
            props.progress.step,
            props.state.workspace_id,
            props.state.project_id,
            props.state.task_id,
        ] as const,
    () => {
        synchronizeStepDraft();
        form.clearErrors();
        void focusStepHeading();
    },
    { immediate: true },
);

watch(
    () => props.recovery,
    (recovery) => {
        if (recovery) {
            saveStatus.value = 'resumed';
        }
    },
);

function createRequestKey(): string {
    if (typeof globalThis.crypto?.randomUUID === 'function') {
        return globalThis.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (token) => {
        const random = Math.floor(Math.random() * 16);
        const value = token === 'x' ? random : (random & 0x3) | 0x8;

        return value.toString(16);
    });
}

function synchronizeStepDraft(): void {
    form.target_step = '';
    form.request_key = createRequestKey();

    if (activeStep.value === 'preferences') {
        Object.assign(form, props.preferences);
    }

    if (activeStep.value === 'workspace') {
        form.workspace_id =
            props.state.workspace_id ?? props.options.workspaces[0]?.id ?? '';
        form.mode = form.workspace_id ? 'select' : 'create';
        form.name = '';
        form.description = '';
    }

    if (activeStep.value === 'project') {
        form.project_id =
            props.state.project_id ?? props.options.projects[0]?.id ?? '';
        form.mode = form.project_id ? 'select' : 'create';
        form.name = '';
        form.description = '';
        form.color = '#ff6038';
        form.icon = 'folder';
    }

    if (activeStep.value === 'task') {
        form.task_id = props.state.task_id ?? props.options.tasks[0]?.id ?? '';
        form.mode = form.task_id ? 'select' : 'create';
        form.title = '';
        form.description = '';
        form.status_id =
            props.options.statuses.find((item) => item.is_default)?.id ??
            props.options.statuses[0]?.id ??
            '';
        form.priority_id =
            props.options.priorities.find((item) => item.is_default)?.id ??
            props.options.priorities[0]?.id ??
            '';
        form.assigned_to = 'unassigned';
        form.due_date = '';
    }
}

function updatePreferences(draft: OnboardingPreferences): void {
    Object.assign(form, draft);
}

async function focusStepHeading(): Promise<void> {
    await nextTick();

    const heading = document.getElementById('onboarding-step-heading');

    if (heading instanceof HTMLElement && heading.isConnected) {
        heading.focus();
    }
}

async function focusValidationSummary(): Promise<void> {
    await nextTick();

    const summary = document.getElementById('validation-summary');

    if (summary instanceof HTMLElement && summary.isConnected) {
        summary.focus();
    }
}

const visitOptions = () => ({
    preserveScroll: true,
    onStart: () => {
        saveStatus.value = 'saving';
    },
    onSuccess: () => {
        saveStatus.value = 'saved';
    },
    onError: () => {
        saveStatus.value = 'error';
        void focusValidationSummary();
    },
});

function submit(): void {
    router.cancelAll();

    switch (activeStep.value) {
        case 'preferences':
            form.transform(() => ({ ...preferencesDraft.value }));
            void form.submit(savePreferences(), visitOptions());

            return;
        case 'workspace':
            form.transform(() => ({
                mode: form.mode,
                request_key: form.request_key,
                ...(form.mode === 'select'
                    ? { workspace_id: form.workspace_id }
                    : {
                          name: form.name,
                          description: form.description || null,
                      }),
            }));
            void form.submit(saveWorkspace(), visitOptions());

            return;
        case 'project':
            form.transform(() => ({
                mode: form.mode,
                request_key: form.request_key,
                ...(form.mode === 'select'
                    ? { project_id: form.project_id }
                    : {
                          name: form.name,
                          description: form.description || null,
                          color: form.color,
                          icon: form.icon,
                      }),
            }));
            void form.submit(saveProject(), visitOptions());

            return;
        case 'task':
            form.transform(() => ({
                mode: form.mode,
                request_key: form.request_key,
                ...(form.mode === 'select'
                    ? { task_id: form.task_id }
                    : {
                          title: form.title,
                          description: form.description || null,
                          status_id: form.status_id,
                          priority_id: form.priority_id,
                          assigned_to:
                              form.assigned_to === 'unassigned'
                                  ? null
                                  : form.assigned_to,
                          due_date: form.due_date || null,
                      }),
            }));
            void form.submit(saveTask(), visitOptions());

            return;
        case 'results':
            form.transform(() => ({}));
            void form.submit(completeOnboarding(), visitOptions());

            return;
        default:
            advanceTo(1);
    }
}

function advanceTo(direction: -1 | 1): void {
    const currentIndex = orderedOnboardingSteps.indexOf(activeStep.value);
    const target = orderedOnboardingSteps[currentIndex + direction];

    if (!target) {
        return;
    }

    router.cancelAll();
    form.target_step = target;
    form.transform(() => ({ target_step: target }));
    void form.submit(saveProgress(), visitOptions());
}

function exitReplay(): void {
    router.cancelAll();
    form.transform(() => ({}));
    void form.submit(exitOnboardingReplay(), visitOptions());
}
</script>

<template>
    <div>
        <Head :title="activeCopy.title">
            <meta name="description" :content="activeCopy.description" />
        </Head>

        <WorkspacePageFrame>
            <OnboardingShell
                :copy="copy"
                :progress="progress"
                :save-status="saveStatus"
                :can-back="progress.position > 1 && !recovery"
                :primary-label="primaryLabel"
                :processing="form.processing"
                @back="advanceTo(-1)"
                @exit-replay="exitReplay"
            >
                <OnboardingStepPanel
                    :eyebrow="copy.meta.eyebrow"
                    :title="activeCopy.title"
                    :description="activeCopy.description"
                    :errors="form.errors"
                    :error-labels="errorLabels"
                    :validation-title="copy.errors.validation_title"
                    :validation-description="copy.errors.validation_description"
                    :recovery-message="recoveryMessage"
                    :replay-badge="
                        progress.is_replay ? copy.meta.replay_badge : null
                    "
                >
                    <form id="onboarding-step-form" @submit.prevent="submit">
                        <Transition name="ui-step" mode="out-in">
                            <div :key="activeStep">
                                <WelcomeStep
                                    v-if="activeStep === 'welcome'"
                                    :copy="copy.welcome"
                                />
                                <PreferencesStep
                                    v-else-if="activeStep === 'preferences'"
                                    :copy="copy.preferences"
                                    :draft="preferencesDraft"
                                    :timezone-groups="timezoneGroups"
                                    :errors="form.errors"
                                    :processing="form.processing"
                                    @update:draft="updatePreferences"
                                />
                                <WorkspaceStep
                                    v-else-if="activeStep === 'workspace'"
                                    v-model:mode="form.mode"
                                    v-model:selected-id="form.workspace_id"
                                    v-model:name="form.name"
                                    v-model:description="form.description"
                                    :copy="copy.workspace"
                                    :workspaces="options.workspaces"
                                    :errors="form.errors"
                                    :processing="form.processing"
                                />
                                <ProjectStep
                                    v-else-if="activeStep === 'project'"
                                    v-model:mode="form.mode"
                                    v-model:selected-id="form.project_id"
                                    v-model:name="form.name"
                                    v-model:description="form.description"
                                    v-model:color="form.color"
                                    v-model:icon="form.icon"
                                    :copy="copy.project"
                                    :projects="options.projects"
                                    :errors="form.errors"
                                    :processing="form.processing"
                                />
                                <TaskStep
                                    v-else-if="activeStep === 'task'"
                                    v-model:mode="form.mode"
                                    v-model:selected-id="form.task_id"
                                    v-model:title="form.title"
                                    v-model:description="form.description"
                                    v-model:status-id="form.status_id"
                                    v-model:priority-id="form.priority_id"
                                    v-model:assignee-id="form.assigned_to"
                                    v-model:due-date="form.due_date"
                                    :copy="copy.task"
                                    :tasks="options.tasks"
                                    :members="options.members"
                                    :statuses="options.statuses"
                                    :priorities="options.priorities"
                                    :errors="form.errors"
                                    :processing="form.processing"
                                />
                                <ProductMapStep
                                    v-else-if="activeStep === 'product_map'"
                                    :copy="copy.product_map"
                                />
                                <SafetyStep
                                    v-else-if="activeStep === 'safety'"
                                    :copy="copy.safety"
                                    :can-manage-workspace="canManageWorkspace"
                                />
                                <ResultsStep
                                    v-else
                                    :copy="copy.results"
                                    :preferences="preferences"
                                    :workspace="selectedWorkspace"
                                    :project="selectedProject"
                                    :task="selectedTask"
                                />
                            </div>
                        </Transition>
                    </form>
                </OnboardingStepPanel>
            </OnboardingShell>
        </WorkspacePageFrame>
    </div>
</template>
