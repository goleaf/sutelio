<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { Copy, X } from '@lucide/vue';
import ProjectIcon from '@/components/project/ProjectIcon.vue';
import IconTile from '@/components/shared/IconTile.vue';
import { Button } from '@/components/ui/button';
import { useToast } from '@/composables/useToast';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import { duplicate, show as projectShow } from '@/routes/projects';
import type { Project } from '@/types/models';

interface ProjectResponse {
    project: Project;
}

const props = defineProps<{
    workspaceId: string;
    project: Project;
    cancelHref: string;
}>();
const toast = useToast();
const { copy } = useWorkspaceUi();
const request = useHttp<Record<string, never>, ProjectResponse>({});

async function submit(): Promise<void> {
    try {
        const response = await request.post(
            duplicate([props.workspaceId, props.project.id]).url,
        );
        toast.success(copy.value.projects.duplicated);
        router.visit(projectShow([props.workspaceId, response.project.id]).url);
    } catch {
        toast.error(copy.value.projects.duplicate_failed);
    }
}
</script>

<template>
    <form
        class="overflow-hidden rounded-panel border border-border/80 bg-card shadow-panel"
        @submit.prevent="submit"
    >
        <div class="flex min-w-0 items-start gap-4 p-4 sm:p-6">
            <IconTile tone="brand" size="lg">
                <ProjectIcon :value="project.icon" />
            </IconTile>
            <div class="min-w-0 space-y-1.5">
                <h2 class="text-xl font-semibold tracking-tight wrap-anywhere">
                    {{ project.name }}
                </h2>
                <p
                    class="text-base leading-6 wrap-anywhere text-muted-foreground"
                >
                    {{ project.description || copy.projects.no_description }}
                </p>
            </div>
        </div>
        <div
            class="flex flex-col-reverse gap-2 border-t border-border/70 bg-muted/20 p-4 min-[30rem]:flex-row min-[30rem]:justify-end sm:px-6"
        >
            <Button as-child variant="outline" size="lg">
                <Link :href="cancelHref">
                    <X aria-hidden="true" />
                    {{ copy.projects.cancel }}
                </Link>
            </Button>
            <Button
                type="submit"
                size="lg"
                :loading="request.processing"
                :loading-label="copy.projects.duplicating"
            >
                <Copy aria-hidden="true" />
                {{ copy.projects.duplicate }}
            </Button>
        </div>
    </form>
</template>
