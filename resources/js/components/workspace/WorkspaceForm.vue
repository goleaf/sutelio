<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { Save, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { show, store, update } from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

interface WorkspaceResponse {
    workspace: Workspace;
}

const props = defineProps<{
    workspace?: Workspace;
    cancelHref: string;
}>();
const toast = useToast();
const { t } = useUi();
const form = useHttp<{ name: string; description: string }, WorkspaceResponse>({
    name: props.workspace?.name ?? '',
    description: props.workspace?.description ?? '',
});
const editing = Boolean(props.workspace);

async function submit(): Promise<void> {
    if (!form.name.trim()) {
        form.setError('name', t('workspaces.name_required'));

        return;
    }

    form.name = form.name.trim();
    form.description = form.description.trim();

    try {
        const response = editing
            ? await form.submit(update(props.workspace as Workspace))
            : await form.submit(store());

        toast.success(t(editing ? 'workspaces.updated' : 'workspaces.created'));
        router.visit(show(response.workspace).url);
    } catch {
        if (!form.hasErrors) {
            toast.error(
                t(
                    editing
                        ? 'workspaces.update_failed'
                        : 'workspaces.create_failed',
                ),
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
        <div class="space-y-6 p-4 sm:p-6">
            <div class="space-y-2">
                <Label for="workspace-name">{{ t('workspaces.name') }}</Label>
                <Input
                    id="workspace-name"
                    v-model="form.name"
                    autofocus
                    autocomplete="organization"
                    :disabled="form.processing"
                    :aria-invalid="Boolean(form.errors.name)"
                    @input="form.clearErrors('name')"
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="space-y-2">
                <Label for="workspace-description">
                    {{ t('workspaces.description') }}
                </Label>
                <Textarea
                    id="workspace-description"
                    v-model="form.description"
                    rows="5"
                    :disabled="form.processing"
                    :aria-invalid="Boolean(form.errors.description)"
                    @input="form.clearErrors('description')"
                />
                <InputError :message="form.errors.description" />
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
                :loading-label="t('common.actions.saving')"
            >
                <Save aria-hidden="true" />
                {{ t('common.actions.save') }}
            </Button>
        </div>
    </form>
</template>
