<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { Plus, Save, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import ProjectIconPicker from '@/components/project/ProjectIconPicker.vue';
import { Button } from '@/components/ui/button';
import { ColorPickerField } from '@/components/ui/color-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/composables/useToast';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import {
    show as projectShow,
    store as projectStore,
    update as projectUpdate,
} from '@/routes/projects';
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
    workspaceId: string;
    cancelHref: string;
    project?: Project;
}>();

const toast = useToast();
const { copy } = useWorkspaceUi();
const form = useHttp<ProjectForm, ProjectResponse>({
    name: props.project?.name ?? '',
    description: props.project?.description ?? '',
    color: props.project?.color ?? '#ff6038',
    icon: props.project?.icon ?? 'folder',
});
const editing = Boolean(props.project);

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

async function submit(): Promise<void> {
    if (!form.name.trim()) {
        form.setError('name', copy.value.projects.name_required);

        return;
    }

    form.name = form.name.trim();
    form.description = form.description.trim();

    try {
        const response = editing
            ? await form.submit(
                  projectUpdate([props.workspaceId, props.project as Project]),
              )
            : await form.submit(projectStore(props.workspaceId));
        toast.success(
            editing ? copy.value.projects.updated : copy.value.projects.created,
        );
        router.visit(projectShow([props.workspaceId, response.project.id]).url);
    } catch {
        if (!form.hasErrors) {
            toast.error(
                editing
                    ? copy.value.projects.update_failed
                    : copy.value.projects.create_failed,
            );
        }
    }
}
</script>

<template>
    <form
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        @submit.prevent="submit"
    >
        <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-2">
            <div class="space-y-2 lg:col-span-2">
                <Label for="project-name">{{ copy.projects.name }}</Label>
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

            <div class="space-y-2 lg:col-span-2">
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
                    <X class="size-4" aria-hidden="true" />
                    {{ copy.projects.cancel }}
                </Link>
            </Button>
            <Button
                type="submit"
                size="lg"
                :loading="form.processing"
                :loading-label="
                    editing ? copy.projects.saving : copy.projects.creating
                "
            >
                <Save v-if="editing" aria-hidden="true" />
                <Plus v-else aria-hidden="true" />
                {{ editing ? copy.projects.save : copy.projects.create }}
            </Button>
        </div>
    </form>
</template>
