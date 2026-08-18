<script setup lang="ts">
import { Head, Link, setLayoutProps, useHttp, usePage } from '@inertiajs/vue3';
import {
    Braces,
    Check,
    CircleAlert,
    Download,
    FileCheck2,
    FileText,
    LockKeyhole,
    Table2,
    Upload,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed, nextTick, ref, watchEffect } from 'vue';
import type { ComponentPublicInstance } from 'vue';
import InputError from '@/components/InputError.vue';
import {
    formatDataSize,
    hasSuccessfulHttpResponse,
    importStage,
} from '@/components/settings/data/data-safety';
import type {
    ExportFormat,
    ImportFormat,
} from '@/components/settings/data/data-safety';
import DataScopeBanner from '@/components/settings/data/DataScopeBanner.vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { exportMethod, importMethod } from '@/routes';
import { edit as editBackup } from '@/routes/backup';
import { preview as importPreview } from '@/routes/import';
import type { SettingsLayoutProps } from '@/types';
import type { Workspace } from '@/types/models';

type ImportPayload = { file: File | null; format: ImportFormat };
type ImportSummary = { version: number; projects: number; todos: number };
type ImportPreview = ImportSummary & { format: ImportFormat };
type ImportPreviewResponse = { preview: ImportPreview };
type ImportResponse = {
    imported: ImportSummary | { version: number; todos_imported: number };
};

const props = defineProps<{ canImport: boolean; workspace: Workspace }>();
const page = usePage();
const toast = useToast();
const { formatNumber, t } = useUi();
const exportFormats: Array<{
    description: string;
    icon: LucideIcon;
    label: string;
    value: ExportFormat;
}> = [
    {
        value: 'json',
        label: t('settings.export.formats.json_label'),
        description: t('settings.export.formats.json_description'),
        icon: Braces,
    },
    {
        value: 'csv',
        label: t('settings.export.formats.csv_label'),
        description: t('settings.export.formats.csv_description'),
        icon: Table2,
    },
    {
        value: 'markdown',
        label: t('settings.export.formats.markdown_label'),
        description: t('settings.export.formats.markdown_description'),
        icon: FileText,
    },
];
const importFormats: Array<{
    description: string;
    label: string;
    value: ImportFormat;
}> = [
    {
        value: 'json',
        label: t('settings.export.import_json'),
        description: t('settings.export.import_json_description'),
    },
    {
        value: 'csv',
        label: t('settings.export.import_csv'),
        description: t('settings.export.import_csv_description'),
    },
];
const selectedImportFile = ref<File | null>(null);
const selectedImportFormat = ref<ImportFormat | null>(null);
const primaryImportInput = ref<HTMLInputElement | null>(null);
const preview = ref<ImportPreview | null>(null);
const previewRequest = useHttp<ImportPayload, ImportPreviewResponse>({
    file: null,
    format: 'json',
});
const importRequest = useHttp<ImportPayload, ImportResponse>({
    file: null,
    format: 'json',
});
const currentImportStage = computed(() =>
    importStage({
        previewing: previewRequest.processing,
        importing: importRequest.processing,
        hasPreview: preview.value !== null,
    }),
);

watchEffect(() => {
    setLayoutProps<SettingsLayoutProps>({
        settingsEyebrow: t('account.menu.settings'),
        settingsTitle: t('settings.export.title'),
        settingsDescription: t('settings.export.description'),
    });
});

function announceExport(format: ExportFormat): void {
    toast.success(
        t('settings.export.exporting', { format: format.toUpperCase() }),
    );
}

function capturePrimaryImportInput(
    element: Element | ComponentPublicInstance | null,
): void {
    if (element instanceof HTMLInputElement) {
        primaryImportInput.value = element;
    }
}

async function clearImportSelection(restoreFocus = false): Promise<void> {
    selectedImportFile.value = null;
    selectedImportFormat.value = null;
    preview.value = null;
    previewRequest.resetAndClearErrors();
    importRequest.resetAndClearErrors();

    if (restoreFocus) {
        await nextTick();
        primaryImportInput.value?.focus();
    }
}

function importStepState(index: number): 'complete' | 'current' | 'pending' {
    const currentIndex =
        currentImportStage.value === 'select'
            ? 0
            : currentImportStage.value === 'importing'
              ? 2
              : 1;

    if (index < currentIndex) {
        return 'complete';
    }

    return index === currentIndex ? 'current' : 'pending';
}

async function handleImport(event: Event, format: ImportFormat): Promise<void> {
    const input = event.target as HTMLInputElement;

    if (
        !input.files?.length ||
        !props.canImport ||
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

        if (!hasSuccessfulHttpResponse(response, previewRequest.hasErrors)) {
            if (!previewRequest.hasErrors) {
                toast.error(t('common.errors.generic'));
            }

            return;
        }

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
        !props.canImport ||
        importRequest.processing
    ) {
        return;
    }

    importRequest.file = selectedImportFile.value;
    importRequest.format = selectedImportFormat.value;

    try {
        const response = await importRequest.post(
            importMethod(props.workspace.id).url,
        );

        if (!hasSuccessfulHttpResponse(response, importRequest.hasErrors)) {
            if (!importRequest.hasErrors) {
                toast.error(t('common.errors.generic'));
            }

            return;
        }

        toast.success(t('settings.export.import_success'));
        await clearImportSelection(true);
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
        <DataScopeBanner
            scope="workspace"
            :label="t('settings.export.scope_label')"
            :title="t('settings.export.scope_title')"
            :description="
                t('settings.export.scope_description', {
                    workspace: workspace.name,
                })
            "
        >
            <template #status>{{ t('settings.export.scope_status') }}</template>
        </DataScopeBanner>

        <Card>
            <CardHeader>
                <LeadingIconHeading tile tile-tone="brand">
                    <template #icon>
                        <Download />
                    </template>

                    <CardTitle as="h2">{{
                        t('settings.export.export_title')
                    }}</CardTitle>
                    <CardDescription>{{
                        t('settings.export.export_description')
                    }}</CardDescription>
                </LeadingIconHeading>
            </CardHeader>
            <CardContent class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <Button
                    v-for="format in exportFormats"
                    :key="format.value"
                    :as-child="true"
                    variant="outline"
                    class="ui-lift h-auto min-h-24 justify-start rounded-2xl bg-muted/20 p-0 text-left whitespace-normal hover:border-orange-500/25 hover:bg-orange-500/[0.05]"
                >
                    <a
                        :href="
                            exportMethod([props.workspace.id, format.value]).url
                        "
                        class="flex h-full w-full items-start gap-3 px-4 py-4"
                        @click="announceExport(format.value)"
                    >
                        <IconTile tone="brand" size="sm">
                            <component :is="format.icon" />
                        </IconTile>
                        <span class="min-w-0">
                            <span class="block font-semibold">{{
                                format.label
                            }}</span>
                            <span
                                class="mt-1 block text-[0.9375rem] leading-5 text-muted-foreground"
                                >{{ format.description }}</span
                            >
                        </span>
                    </a>
                </Button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <LeadingIconHeading tile tile-tone="information">
                    <template #icon>
                        <Upload />
                    </template>

                    <CardTitle as="h2">{{
                        t('settings.export.import_title')
                    }}</CardTitle>
                    <CardDescription>{{
                        t('settings.export.import_description')
                    }}</CardDescription>
                </LeadingIconHeading>
            </CardHeader>
            <CardContent v-if="canImport" class="space-y-5">
                <ol
                    class="grid grid-cols-1 gap-2 sm:grid-cols-3"
                    :aria-label="t('settings.export.import_title')"
                >
                    <li
                        v-for="(step, index) in [
                            t('settings.export.steps.select'),
                            t('settings.export.steps.review'),
                            t('settings.export.steps.confirm'),
                        ]"
                        :key="step"
                        class="min-w-0"
                    >
                        <div
                            :class="[
                                'flex min-h-12 flex-row items-center justify-start gap-2 rounded-xl border px-3 py-2 text-left text-base leading-6 font-medium transition-colors motion-reduce:transition-none pointer-coarse:min-h-13',
                                importStepState(index) === 'current'
                                    ? 'border-orange-500/30 bg-orange-500/[0.07] text-orange-900'
                                    : importStepState(index) === 'complete'
                                      ? 'border-status-success-border bg-status-success-surface text-status-success-text'
                                      : 'border-border/70 bg-muted/30 text-muted-foreground',
                            ]"
                            :aria-current="
                                importStepState(index) === 'current'
                                    ? 'step'
                                    : undefined
                            "
                        >
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-background text-[0.9375rem] font-semibold ring-1 ring-border/70"
                            >
                                <Check
                                    v-if="importStepState(index) === 'complete'"
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                                <span v-else>{{ index + 1 }}</span>
                            </span>
                            <span class="break-words">{{ step }}</span>
                        </div>
                    </li>
                </ol>

                <div
                    class="flex gap-3 rounded-xl border border-orange-500/20 bg-orange-500/[0.05] p-3 text-base leading-6 text-muted-foreground"
                >
                    <CircleAlert
                        class="mt-0.5 size-4 shrink-0 text-orange-700"
                        aria-hidden="true"
                    />
                    <p>{{ t('settings.export.import_limitations') }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="format in importFormats"
                        :key="format.value"
                        class="ui-lift inline-flex min-h-24 cursor-pointer items-start gap-3 rounded-2xl border border-border bg-muted/20 px-4 py-4 text-base transition-colors focus-within:ring-2 focus-within:ring-orange-500 hover:border-orange-500/25 hover:bg-orange-500/[0.05] motion-reduce:transition-none"
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
                            selectedImportFormat === format.value
                        "
                    >
                        <input
                            :ref="
                                format.value === 'json'
                                    ? capturePrimaryImportInput
                                    : undefined
                            "
                            type="file"
                            :accept="`.${format.value}`"
                            class="sr-only"
                            :disabled="
                                previewRequest.processing ||
                                importRequest.processing
                            "
                            @change="handleImport($event, format.value)"
                        />
                        <IconTile tone="information" size="sm">
                            <Spinner
                                v-if="
                                    previewRequest.processing &&
                                    selectedImportFormat === format.value
                                "
                            />
                            <Upload v-else class="size-4" aria-hidden="true" />
                        </IconTile>
                        <span class="min-w-0">
                            <span class="block font-semibold">{{
                                format.label
                            }}</span>
                            <span
                                class="mt-1 block text-[0.9375rem] leading-5 text-muted-foreground"
                                >{{ format.description }}</span
                            >
                        </span>
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

                <p class="sr-only" aria-live="polite">
                    <template v-if="currentImportStage === 'previewing'">
                        {{ t('settings.export.previewing') }}
                    </template>
                    <template v-else-if="currentImportStage === 'importing'">
                        {{ t('settings.export.importing') }}
                    </template>
                </p>

                <InputError
                    :message="
                        previewRequest.errors.file || importRequest.errors.file
                    "
                />

                <section
                    v-if="selectedImportFile"
                    class="rounded-2xl border border-border/80 bg-background p-4"
                    :aria-labelledby="
                        preview ? 'import-preview-title' : undefined
                    "
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                        <IconTile tone="success" size="sm">
                            <FileCheck2 />
                        </IconTile>
                        <dl
                            class="grid min-w-0 flex-1 gap-3 text-base sm:grid-cols-3"
                        >
                            <div class="min-w-0">
                                <dt
                                    class="text-[0.9375rem] leading-5 text-muted-foreground"
                                >
                                    {{ t('settings.export.file_name') }}
                                </dt>
                                <dd class="mt-1 font-medium wrap-anywhere">
                                    {{ selectedImportFile?.name }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-[0.9375rem] leading-5 text-muted-foreground"
                                >
                                    {{ t('settings.export.file_size') }}
                                </dt>
                                <dd class="mt-1 font-medium">
                                    {{
                                        formatDataSize(
                                            selectedImportFile.size,
                                            formatNumber,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-[0.9375rem] leading-5 text-muted-foreground"
                                >
                                    {{ t('settings.export.file_format') }}
                                </dt>
                                <dd class="mt-1 font-medium uppercase">
                                    {{ selectedImportFormat }}
                                </dd>
                            </div>
                        </dl>
                        <Button
                            type="button"
                            variant="ghost"
                            class="min-h-12 shrink-0 pointer-coarse:min-h-13"
                            :disabled="
                                previewRequest.processing ||
                                importRequest.processing
                            "
                            @click="clearImportSelection(true)"
                        >
                            {{ t('settings.export.clear_file') }}
                        </Button>
                    </div>

                    <div
                        v-if="preview"
                        class="mt-4 border-t border-border/70 pt-4"
                    >
                        <h3 id="import-preview-title" class="font-semibold">
                            {{ t('settings.export.preview_title') }}
                        </h3>
                        <p
                            class="mt-1 text-base leading-6 text-muted-foreground"
                        >
                            {{ t('settings.export.preview_description') }}
                        </p>

                        <dl class="mt-4 grid gap-3 text-base sm:grid-cols-3">
                            <div class="rounded-xl bg-muted/45 p-3">
                                <dt class="text-muted-foreground">
                                    {{ t('settings.export.preview_projects') }}
                                </dt>
                                <dd class="mt-1 text-lg font-semibold">
                                    {{ formatNumber(preview.projects) }}
                                </dd>
                            </div>
                            <div class="rounded-xl bg-muted/45 p-3">
                                <dt class="text-muted-foreground">
                                    {{ t('settings.export.preview_tasks') }}
                                </dt>
                                <dd class="mt-1 text-lg font-semibold">
                                    {{ formatNumber(preview.todos) }}
                                </dd>
                            </div>
                            <div class="rounded-xl bg-muted/45 p-3">
                                <dt class="text-muted-foreground">
                                    {{ t('settings.export.preview_version') }}
                                </dt>
                                <dd class="mt-1 text-lg font-semibold">
                                    {{ formatNumber(preview.version) }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end"
                        >
                            <Button
                                type="button"
                                variant="ghost"
                                class="min-h-12 pointer-coarse:min-h-13"
                                :disabled="importRequest.processing"
                                @click="clearImportSelection(true)"
                            >
                                {{ t('common.actions.cancel') }}
                            </Button>
                            <Button
                                type="button"
                                class="min-h-12 pointer-coarse:min-h-13"
                                :loading="importRequest.processing"
                                :loading-label="
                                    t('settings.export.confirm_import')
                                "
                                @click="confirmImport"
                            >
                                <Upload class="size-4" aria-hidden="true" />
                                {{ t('settings.export.confirm_import') }}
                            </Button>
                        </div>
                    </div>
                </section>
            </CardContent>
            <CardContent v-else>
                <LeadingIconHeading
                    tile
                    tile-tone="muted"
                    class="rounded-xl border border-border/80 bg-muted/35 p-4"
                    content-class="gap-0"
                >
                    <template #icon>
                        <LockKeyhole />
                    </template>

                    <h3 class="font-semibold">
                        {{ t('settings.export.import_restricted_title') }}
                    </h3>
                    <p class="mt-1 text-base leading-6 text-muted-foreground">
                        {{ t('settings.export.import_restricted_description') }}
                    </p>
                </LeadingIconHeading>
            </CardContent>
        </Card>

        <Card v-if="page.props.capabilities.manageDatabaseBackups">
            <CardHeader>
                <CardTitle as="h2">{{
                    t('settings.export.operator_backup_title')
                }}</CardTitle>
                <CardDescription>{{
                    t('settings.export.operator_backup_description')
                }}</CardDescription>
            </CardHeader>
            <CardContent>
                <Button
                    :as-child="true"
                    variant="outline"
                    class="min-h-12 pointer-coarse:min-h-13"
                >
                    <Link :href="editBackup.url()">
                        <Download class="size-4" aria-hidden="true" />
                        {{ t('settings.export.operator_backup_action') }}
                    </Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
