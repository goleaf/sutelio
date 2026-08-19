<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { Copy, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { duplicate, show } from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

interface WorkspaceResponse {
    workspace: Workspace;
}

const props = defineProps<{
    workspace: Workspace;
    cancelHref: string;
}>();
const toast = useToast();
const { t } = useUi();
const form = useHttp<{ name: string }, WorkspaceResponse>({
    name: t('workspaces.copy_name', { name: props.workspace.name }),
});

async function submit(): Promise<void> {
    if (!form.name.trim()) {
        form.setError('name', t('workspaces.name_required'));

        return;
    }

    form.name = form.name.trim();

    try {
        const response = await form.submit(duplicate(props.workspace));
        toast.success(t('workspaces.duplicated'));
        router.visit(show(response.workspace).url);
    } catch {
        if (!form.hasErrors) {
            toast.error(t('workspaces.duplicate_failed'));
        }
    }
}
</script>

<template>
    <form
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        @submit.prevent="submit"
    >
        <div class="space-y-2 p-4 sm:p-6">
            <Label for="workspace-duplicate-name">
                {{ t('workspaces.name') }}
            </Label>
            <Input
                id="workspace-duplicate-name"
                v-model="form.name"
                :placeholder="t('workspaces.duplicate_name_placeholder')"
                autofocus
                autocomplete="organization"
                :disabled="form.processing"
                :aria-invalid="Boolean(form.errors.name)"
                @input="form.clearErrors('name')"
            />
            <InputError :message="form.errors.name" />
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
                :loading-label="t('workspaces.duplicating')"
            >
                <Copy aria-hidden="true" />
                {{ t('workspaces.actions.duplicate') }}
            </Button>
        </div>
    </form>
</template>
