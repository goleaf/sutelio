<script setup lang="ts">
import { Head, setLayoutProps, useHttp } from '@inertiajs/vue3';
import { Download, FileCheck2, Upload } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { exportMethod, importMethod } from '@/routes';
import { preview as importPreview } from '@/routes/import';
import type { SettingsLayoutProps } from '@/types';
import type { Workspace } from '@/types/models';

type ImportFormat = 'json' | 'csv';
type ImportPayload = { file: File | null; format: ImportFormat };
type ImportSummary = { version: number; projects: number; todos: number };
type ImportPreview = ImportSummary & { format: ImportFormat };
type ImportPreviewResponse = { preview: ImportPreview };
type ImportResponse = {
    imported: ImportSummary | { version: number; todos_imported: number };
};

const props = defineProps<{ workspace: Workspace }>();
const toast = useToast();
const { formatNumber, t } = useUi();
const importFormats: ImportFormat[] = ['json', 'csv'];
const selectedImportFile = ref<File | null>(null);
const selectedImportFormat = ref<ImportFormat | null>(null);
const preview = ref<ImportPreview | null>(null);
const previewRequest = useHttp<ImportPayload, ImportPreviewResponse>({
    file: null,
    format: 'json',
});
const importRequest = useHttp<ImportPayload, ImportResponse>({
    file: null,
    format: 'json',
});

setLayoutProps<SettingsLayoutProps>({
    settingsEyebrow: t('account.menu.settings'),
    settingsTitle: t('settings.export.title'),
    settingsDescription: t('settings.export.description'),
});

function exportData(format: string) {
    window.location.href = exportMethod([props.workspace.id, format]).url;
    toast.success(
        t('settings.export.exporting', { format: format.toUpperCase() }),
    );
}

function clearImportSelection(): void {
    selectedImportFile.value = null;
    selectedImportFormat.value = null;
    preview.value = null;
    previewRequest.resetAndClearErrors();
    importRequest.resetAndClearErrors();
}

async function handleImport(event: Event, format: ImportFormat): Promise<void> {
    const input = event.target as HTMLInputElement;

    if (
        !input.files?.length ||
        previewRequest.processing ||
        importRequest.processing
    ) {
        return;
    }

    const file = input.files[0];
    selectedImportFile.value = file;
    selectedImportFormat.value = format;
    preview.value = null;
    previewRequest.resetAndClearErrors();
    importRequest.resetAndClearErrors();
    previewRequest.file = file;
    previewRequest.format = format;
    input.value = '';

    try {
        const response = await previewRequest.post(
            importPreview(props.workspace.id).url,
        );
        preview.value = response.preview;
    } catch {
        if (!previewRequest.hasErrors) {
            toast.error(t('common.errors.generic'));
        }
    }
}

async function confirmImport(): Promise<void> {
    if (
        !selectedImportFile.value ||
        !selectedImportFormat.value ||
        !preview.value ||
        importRequest.processing
    ) {
        return;
    }

    importRequest.file = selectedImportFile.value;
    importRequest.format = selectedImportFormat.value;

    try {
        await importRequest.post(importMethod(props.workspace.id).url);
        toast.success(t('settings.export.import_success'));
        clearImportSelection();
    } catch {
        if (!importRequest.hasErrors) {
            toast.error(t('common.errors.generic'));
        }
    }
}
</script>

<template>
    <Head :title="t('settings.export.title')" />
    <div class="space-y-6">
        <Card>
            <CardHeader>
                <CardTitle>{{ t('settings.export.export_title') }}</CardTitle>
                <CardDescription>{{
                    t('settings.export.export_description')
                }}</CardDescription>
            </CardHeader>
            <CardContent class="grid gap-3 sm:grid-cols-3">
                <Button
                    v-for="format in ['json', 'csv', 'markdown']"
                    :key="format"
                    variant="outline"
                    class="min-h-20 justify-start rounded-2xl bg-muted/20 px-4 hover:border-orange-500/25 hover:bg-orange-500/[0.05]"
                    @click="exportData(format)"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-700 dark:text-orange-300"
                    >
                        <Download class="size-4" aria-hidden="true" />
                    </span>
                    <span class="font-semibold uppercase">{{ format }}</span>
                </Button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('settings.export.import_title') }}</CardTitle>
                <CardDescription>{{
                    t('settings.export.import_description')
                }}</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="format in importFormats"
                        :key="format"
                        class="inline-flex min-h-20 cursor-pointer items-center gap-3 rounded-2xl border border-border bg-muted/20 px-4 text-sm font-medium transition-colors focus-within:ring-2 focus-within:ring-orange-500 hover:border-orange-500/25 hover:bg-orange-500/[0.05] motion-reduce:transition-none"
                        :class="{
                            'pointer-events-none opacity-50':
                                previewRequest.processing ||
                                importRequest.processing,
                        }"
                        :aria-disabled="
                            previewRequest.processing ||
                            importRequest.processing
                        "
                        :aria-busy="
                            previewRequest.processing &&
                            selectedImportFormat === format
                        "
                    >
                        <input
                            type="file"
                            :accept="`.${format}`"
                            class="sr-only"
                            :disabled="
                                previewRequest.processing ||
                                importRequest.processing
                            "
                            @change="handleImport($event, format)"
                        />
                        <span
                            class="flex size-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-700 dark:text-orange-300"
                        >
                            <Spinner
                                v-if="
                                    previewRequest.processing &&
                                    selectedImportFormat === format
                                "
                            />
                            <Upload v-else class="size-4" aria-hidden="true" />
                        </span>
                        {{ t(`settings.export.import_${format}`) }}
                    </label>
                </div>

                <progress
                    v-if="previewRequest.progress || importRequest.progress"
                    class="h-2 w-full accent-orange-600"
                    :value="
                        previewRequest.progress?.percentage ??
                        importRequest.progress?.percentage
                    "
                    max="100"
                    :aria-label="t('settings.export.upload_progress')"
                />

                <InputError
                    :message="
                        previewRequest.errors.file || importRequest.errors.file
                    "
                />

                <section
                    v-if="preview"
                    class="rounded-2xl border border-orange-500/20 bg-orange-500/[0.05] p-4"
                    aria-live="polite"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/10 text-orange-700 dark:text-orange-300"
                        >
                            <FileCheck2 class="size-4" aria-hidden="true" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold">
                                {{ t('settings.export.preview_title') }}
                            </h3>
                            <p
                                class="mt-1 truncate text-sm text-muted-foreground"
                            >
                                {{ selectedImportFile?.name }}
                            </p>
                        </div>
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-xl bg-background/70 p-3">
                            <dt class="text-muted-foreground">
                                {{ t('settings.export.preview_projects') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold">
                                {{ formatNumber(preview.projects) }}
                            </dd>
                        </div>
                        <div class="rounded-xl bg-background/70 p-3">
                            <dt class="text-muted-foreground">
                                {{ t('settings.export.preview_tasks') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold">
                                {{ formatNumber(preview.todos) }}
                            </dd>
                        </div>
                        <div class="rounded-xl bg-background/70 p-3">
                            <dt class="text-muted-foreground">
                                {{ t('settings.export.preview_version') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold">
                                {{ formatNumber(preview.version) }}
                            </dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-sm text-muted-foreground">
                        {{ t('settings.export.preview_description') }}
                    </p>

                    <div
                        class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                    >
                        <Button
                            type="button"
                            variant="ghost"
                            :disabled="importRequest.processing"
                            @click="clearImportSelection"
                        >
                            {{ t('common.actions.cancel') }}
                        </Button>
                        <Button
                            type="button"
                            :disabled="importRequest.processing"
                            :aria-busy="importRequest.processing"
                            @click="confirmImport"
                        >
                            <Spinner v-if="importRequest.processing" />
                            <Upload v-else class="size-4" aria-hidden="true" />
                            {{ t('settings.export.confirm_import') }}
                        </Button>
                    </div>
                </section>
            </CardContent>
        </Card>
    </div>
</template>
