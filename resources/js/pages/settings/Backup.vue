<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import {
    CalendarDays,
    CheckCircle2,
    Download,
    HardDrive,
    RotateCcw,
    TriangleAlert,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    dataSafetyPluralForm,
    formatDataSize,
} from '@/components/settings/data/data-safety';
import DataScopeBanner from '@/components/settings/data/DataScopeBanner.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import WorkspaceConfirmDialog from '@/components/shared/WorkspaceConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import {
    create as createBackupRoute,
    download,
    restore,
} from '@/routes/backup';
import type { SettingsLayoutProps } from '@/types';

interface Backup {
    id: string;
    size: number;
    created_at: number;
}

const props = defineProps<{ backups: Backup[] }>();
const toast = useToast();
const { formatDate: formatLocalizedDate, formatNumber, locale, t } = useUi();
const creating = ref(false);
const restoring = ref(false);
const selectedBackup = ref<Backup | null>(null);
const availableSummary = computed(() => {
    const plural = dataSafetyPluralForm(props.backups.length, locale.value);

    return t(`settings.backup.available_${plural}`, {
        count: formatNumber(props.backups.length),
    });
});

setLayoutProps<SettingsLayoutProps>({
    settingsEyebrow: t('account.menu.settings'),
    settingsTitle: t('settings.backup.title'),
    settingsDescription: t('settings.backup.description'),
});

function createBackup(): void {
    creating.value = true;
    router.post(
        createBackupRoute().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(t('settings.backup.created'));
            },
            onError: () => {
                toast.error(t('settings.backup.failed'));
            },
            onFinish: () => {
                creating.value = false;
            },
        },
    );
}

function restoreBackup(backup: Backup): void {
    selectedBackup.value = backup;
}

function confirmRestore(): void {
    if (!selectedBackup.value) {
        return;
    }

    restoring.value = true;
    router.post(
        restore(selectedBackup.value.id).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(t('settings.backup.restored'));
                selectedBackup.value = null;
            },
            onError: () => toast.error(t('settings.backup.restore_failed')),
            onFinish: () => {
                restoring.value = false;
            },
        },
    );
}

function formatDate(timestamp: number): string {
    return formatLocalizedDate(timestamp * 1000, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Head :title="t('settings.navigation.backup')" />

    <div class="space-y-6">
        <DataScopeBanner
            scope="application"
            :label="t('settings.backup.scope_label')"
            :title="t('settings.backup.scope_title')"
            :description="t('settings.backup.scope_description')"
        >
            <template #status>{{ t('settings.backup.scope_status') }}</template>
        </DataScopeBanner>

        <Card>
            <CardHeader>
                <CardTitle>{{ t('settings.backup.list_title') }}</CardTitle>
                <CardDescription>
                    {{ availableSummary }}
                    <span class="mt-1 block">
                        {{ t('settings.backup.inventory_description') }}
                    </span>
                </CardDescription>
                <CardAction>
                    <Button
                        size="lg"
                        class="min-h-11"
                        :disabled="creating"
                        :aria-busy="creating"
                        @click="createBackup"
                    >
                        <Spinner v-if="creating" />
                        <Download v-else class="size-4" aria-hidden="true" />
                        {{
                            creating
                                ? t('settings.backup.creating')
                                : t('settings.backup.create')
                        }}
                    </Button>
                </CardAction>
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="backups.length === 0"
                    compact
                    :title="t('settings.backup.empty')"
                    :description="t('settings.backup.empty_description')"
                >
                    <template #icon>
                        <Download class="size-7" aria-hidden="true" />
                    </template>
                </EmptyState>

                <ol v-else class="space-y-3" role="list">
                    <li v-for="backup in backups" :key="backup.id">
                        <article
                            class="rounded-2xl border border-border/70 bg-background p-4 sm:p-5"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            {{
                                                t('settings.backup.snapshot', {
                                                    date: formatDate(
                                                        backup.created_at,
                                                    ),
                                                })
                                            }}
                                        </h3>
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-1 text-[11px] font-medium text-emerald-800 ring-1 ring-emerald-500/20"
                                        >
                                            <CheckCircle2
                                                class="size-3"
                                                aria-hidden="true"
                                            />
                                            {{ t('settings.backup.verified') }}
                                        </span>
                                    </div>
                                    <dl
                                        class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-muted-foreground"
                                    >
                                        <div class="flex items-center gap-1.5">
                                            <CalendarDays
                                                class="size-3.5"
                                                aria-hidden="true"
                                            />
                                            <dt class="sr-only">
                                                {{
                                                    t(
                                                        'settings.backup.created_at',
                                                    )
                                                }}
                                            </dt>
                                            <dd>
                                                {{
                                                    formatDate(
                                                        backup.created_at,
                                                    )
                                                }}
                                            </dd>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <HardDrive
                                                class="size-3.5"
                                                aria-hidden="true"
                                            />
                                            <dt class="sr-only">
                                                {{
                                                    t(
                                                        'settings.backup.size_label',
                                                    )
                                                }}
                                            </dt>
                                            <dd>
                                                {{
                                                    formatDataSize(
                                                        backup.size,
                                                        formatNumber,
                                                    )
                                                }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        :as-child="true"
                                        variant="outline"
                                        class="min-h-11"
                                    >
                                        <a :href="download(backup.id).url">
                                            <Download
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                            {{ t('settings.backup.download') }}
                                        </a>
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="min-h-11"
                                        @click="restoreBackup(backup)"
                                    >
                                        <RotateCcw
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                        {{ t('settings.backup.restore') }}
                                    </Button>
                                </div>
                            </div>
                        </article>
                    </li>
                </ol>
            </CardContent>
        </Card>

        <section
            class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/[0.06] p-4 text-sm text-amber-950"
            aria-labelledby="backup-restore-risk-title"
        >
            <TriangleAlert class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <div>
                <h2 id="backup-restore-risk-title" class="font-semibold">
                    {{ t('settings.backup.restore_title') }}
                </h2>
                <p class="mt-1 leading-6">
                    {{ t('settings.backup.restore_risk') }}
                </p>
            </div>
        </section>

        <WorkspaceConfirmDialog
            :open="selectedBackup !== null"
            :title="t('settings.backup.restore_title')"
            :description="
                t('settings.backup.restore_confirm', {
                    date: selectedBackup
                        ? formatDate(selectedBackup.created_at)
                        : '',
                })
            "
            :confirm-label="
                restoring
                    ? t('settings.backup.restoring')
                    : t('settings.backup.restore')
            "
            :cancel-label="t('common.actions.cancel')"
            :processing="restoring"
            @update:open="!$event && !restoring && (selectedBackup = null)"
            @confirm="confirmRestore"
        >
            <template #icon>
                <RotateCcw class="size-5" aria-hidden="true" />
            </template>
        </WorkspaceConfirmDialog>
    </div>
</template>
