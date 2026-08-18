<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import ProjectIconPicker from '@/components/project/ProjectIconPicker.vue';
import DialogActions from '@/components/shared/DialogActions.vue';
import DialogBody from '@/components/shared/DialogBody.vue';
import WorkspaceDialogContent from '@/components/shared/WorkspaceDialogContent.vue';
import { Button } from '@/components/ui/button';
import { ColorPickerField } from '@/components/ui/color-picker';
import { Dialog } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { store as projectStore } from '@/routes/projects';
import type { Project } from '@/types/models';

interface ProjectForm {
    name: string;
    description: string;
    color: string;
    icon: string;
}

interface ProjectResponse {
    project: Project;
}

const props = defineProps<{
    open: boolean;
    workspaceId: string;
}>();
const emit = defineEmits<{ close: []; created: [] }>();
const toast = useToast();
const { copy } = useWorkspaceUi();
const form = useHttp<ProjectForm, ProjectResponse>({
    name: '',
    description: '',
    color: '#ff6038',
    icon: 'folder',
});

const colors = [
    '#ff6038',
    '#ef4444',
    '#eab308',
    '#14b8a6',
    '#0ea5e9',
    '#6366f1',
    '#a855f7',
    '#ec4899',
];
watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.resetAndClearErrors();
        form.color = '#ff6038';
        form.icon = 'folder';
    },
);

async function submit(): Promise<void> {
    if (!form.name.trim()) {
        form.setError('name', copy.value.projects.name_required);

        return;
    }

    try {
        await form.submit(projectStore({ workspace: props.workspaceId }), {
            onSuccess: () => {
                toast.success(copy.value.projects.created);
                emit('created');
                emit('close');
                router.reload({ only: ['projects'] });
            },
            onHttpException: () => {
                toast.error(copy.value.projects.create_failed);
            },
            onNetworkError: () => {
                toast.error(copy.value.projects.create_failed);
            },
        });
    } catch {
        if (!form.hasErrors) {
            toast.error(copy.value.projects.create_failed);
        }
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <WorkspaceDialogContent
            :title="copy.projects.create_title"
            :description="copy.projects.create_description"
            :close-label="copy.projects.cancel"
            max-width-class="sm:max-w-xl"
        >
            <form @submit.prevent="submit">
                <DialogBody>
                    <div class="space-y-2">
                        <Label for="project-name">{{
                            copy.projects.name
                        }}</Label>
                        <Input
                            id="project-name"
                            v-model="form.name"
                            :placeholder="copy.projects.name_placeholder"
                            autocomplete="off"
                            autofocus
                            :disabled="form.processing"
                            :aria-invalid="Boolean(form.errors.name)"
                            @input="form.clearErrors('name')"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="project-description">
                            {{ copy.projects.description_label }}
                        </Label>
                        <Input
                            id="project-description"
                            v-model="form.description"
                            :placeholder="copy.projects.description_placeholder"
                            :disabled="form.processing"
                            :aria-invalid="Boolean(form.errors.description)"
                            @input="form.clearErrors('description')"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <fieldset class="space-y-3">
                        <legend class="text-base font-medium">
                            {{ copy.projects.color }}
                        </legend>
                        <ColorPickerField
                            id="project-color"
                            v-model="form.color"
                            :presets="colors"
                            :disabled="form.processing"
                            :invalid="Boolean(form.errors.color)"
                        />
                        <InputError :message="form.errors.color" />
                    </fieldset>

                    <fieldset class="space-y-3">
                        <legend class="text-base font-medium">
                            {{ copy.projects.icon }}
                        </legend>
                        <ProjectIconPicker
                            v-model="form.icon"
                            :label="copy.projects.icon"
                            :disabled="form.processing"
                            :invalid="Boolean(form.errors.icon)"
                        />
                        <InputError :message="form.errors.icon" />
                    </fieldset>
                </DialogBody>
                <DialogActions>
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        :disabled="form.processing"
                        @click="emit('close')"
                    >
                        {{ copy.projects.cancel }}
                    </Button>
                    <Button type="submit" size="lg" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        {{
                            form.processing
                                ? copy.projects.creating
                                : copy.projects.create
                        }}
                    </Button>
                </DialogActions>
            </form>
        </WorkspaceDialogContent>
    </Dialog>
</template>
