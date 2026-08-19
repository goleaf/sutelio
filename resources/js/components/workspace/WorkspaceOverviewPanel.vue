<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import {
    Building2,
    CalendarDays,
    CheckCircle2,
    CheckSquare,
    Folder,
    LockKeyhole,
    Pencil,
    RefreshCw,
    UserRound,
    Users,
} from '@lucide/vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Badge } from '@/components/ui/badge';
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
import { edit, switchMethod } from '@/routes/workspaces';
import type { Workspace } from '@/types/models';

interface WorkspaceResponse {
    workspace: Workspace;
}

const props = defineProps<{ workspace: Workspace }>();

const toast = useToast();
const { formatDate, formatNumber, t } = useUi();
const switchRequest = useHttp<Record<string, never>, WorkspaceResponse>({});

async function switchWorkspace(): Promise<void> {
    if (props.workspace.is_current) {
        return;
    }

    try {
        await switchRequest.submit(switchMethod(props.workspace));
        toast.success(t('workspaces.switched', { name: props.workspace.name }));
        router.reload({ only: ['workspace', 'navigation'] });
    } catch {
        toast.error(t('workspaces.switch_failed'));
    }
}
</script>

<template>
    <section class="space-y-6" aria-labelledby="workspace-overview-title">
        <div class="grid gap-4 sm:grid-cols-3">
            <Card
                class="overflow-hidden border-orange-500/15 bg-orange-500/[0.04]"
            >
                <CardHeader class="pb-3">
                    <IconTile tone="brand" size="sm">
                        <Users />
                    </IconTile>
                    <CardDescription>
                        {{ t('workspaces.members') }}
                    </CardDescription>
                    <CardTitle as="div" class="text-3xl tabular-nums">
                        {{ formatNumber(workspace.members_count ?? 0) }}
                    </CardTitle>
                </CardHeader>
            </Card>
            <Card
                class="overflow-hidden border-status-information-border bg-status-information-surface/50"
            >
                <CardHeader class="pb-3">
                    <IconTile tone="information" size="sm">
                        <Folder />
                    </IconTile>
                    <CardDescription>
                        {{ t('workspaces.projects') }}
                    </CardDescription>
                    <CardTitle as="div" class="text-3xl tabular-nums">
                        {{ formatNumber(workspace.projects_count ?? 0) }}
                    </CardTitle>
                </CardHeader>
            </Card>
            <Card
                class="overflow-hidden border-status-success-border bg-status-success-surface/50"
            >
                <CardHeader class="pb-3">
                    <IconTile tone="success" size="sm">
                        <CheckSquare />
                    </IconTile>
                    <CardDescription>
                        {{ t('workspaces.tasks') }}
                    </CardDescription>
                    <CardTitle as="div" class="text-3xl tabular-nums">
                        {{ formatNumber(workspace.todos_count ?? 0) }}
                    </CardTitle>
                </CardHeader>
            </Card>
        </div>

        <div
            class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(19rem,0.7fr)]"
        >
            <Card>
                <CardHeader>
                    <div
                        class="flex items-start justify-between gap-4 border-b border-border/70 pb-5"
                    >
                        <div class="space-y-1.5">
                            <CardTitle as="h2" id="workspace-overview-title">
                                {{
                                    t(
                                        'workspaces.management.overview.details_title',
                                    )
                                }}
                            </CardTitle>
                            <CardDescription>
                                {{
                                    t(
                                        'workspaces.management.overview.details_description',
                                    )
                                }}
                            </CardDescription>
                        </div>
                        <Badge
                            v-if="workspace.is_current"
                            variant="outline"
                            class="border-status-success-border bg-status-success-surface text-[0.9375rem] whitespace-normal text-status-success-text"
                        >
                            <CheckCircle2 aria-hidden="true" />
                            {{ t('workspaces.current') }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div
                            class="rounded-xl border border-border/70 bg-muted/25 p-4"
                        >
                            <dt
                                class="flex items-center gap-2 text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                            >
                                <Building2 class="size-4" aria-hidden="true" />
                                {{ t('workspaces.name') }}
                            </dt>
                            <dd class="mt-2 font-semibold">
                                {{ workspace.name }}
                            </dd>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-muted/25 p-4"
                        >
                            <dt
                                class="flex items-center gap-2 text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                            >
                                <UserRound class="size-4" aria-hidden="true" />
                                {{ t('workspaces.management.overview.owner') }}
                            </dt>
                            <dd class="mt-2 min-w-0">
                                <p class="font-semibold wrap-anywhere">
                                    {{ workspace.owner?.name ?? '—' }}
                                </p>
                                <p
                                    class="text-[0.9375rem] leading-5 wrap-anywhere text-muted-foreground"
                                >
                                    {{ workspace.owner?.email ?? '—' }}
                                </p>
                            </dd>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-muted/25 p-4"
                        >
                            <dt
                                class="text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                            >
                                {{ t('workspaces.management.overview.slug') }}
                            </dt>
                            <dd
                                class="mt-2 font-mono text-[0.9375rem] wrap-anywhere"
                            >
                                {{ workspace.slug }}
                            </dd>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-muted/25 p-4"
                        >
                            <dt
                                class="flex items-center gap-2 text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                            >
                                <CalendarDays
                                    class="size-4"
                                    aria-hidden="true"
                                />
                                {{
                                    t('workspaces.management.overview.created')
                                }}
                            </dt>
                            <dd class="mt-2 text-base font-medium">
                                {{
                                    formatDate(workspace.created_at, {
                                        dateStyle: 'medium',
                                    })
                                }}
                            </dd>
                        </div>
                    </dl>
                    <div
                        class="mt-5 rounded-xl border border-border/70 bg-muted/25 p-4"
                    >
                        <p
                            class="text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                        >
                            {{ t('workspaces.description') }}
                        </p>
                        <p
                            class="mt-2 text-base leading-6 text-muted-foreground"
                        >
                            {{
                                workspace.description ??
                                t('workspaces.no_description')
                            }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="xl:sticky xl:top-6">
                <CardHeader>
                    <LeadingIconHeading tile tile-tone="brand">
                        <template #icon>
                            <Pencil />
                        </template>

                        <CardTitle as="h2">
                            {{
                                t(
                                    'workspaces.management.overview.actions_title',
                                )
                            }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                t(
                                    'workspaces.management.overview.actions_description',
                                )
                            }}
                        </CardDescription>
                    </LeadingIconHeading>
                </CardHeader>
                <CardContent class="space-y-3">
                    <Button
                        v-if="workspace.permissions?.update"
                        as-child
                        size="lg"
                        class="w-full"
                    >
                        <Link :href="edit(workspace).url">
                            <Pencil aria-hidden="true" />
                            {{ t('workspaces.actions.edit') }}
                        </Link>
                    </Button>
                    <div
                        v-else
                        class="flex items-start gap-3 rounded-xl border bg-muted/35 p-4 text-base leading-6 text-muted-foreground"
                    >
                        <LockKeyhole
                            class="mt-0.5 size-4 shrink-0"
                            aria-hidden="true"
                        />
                        <span>
                            {{ t('workspaces.management.overview.read_only') }}
                        </span>
                    </div>
                    <Button
                        variant="outline"
                        size="lg"
                        class="w-full"
                        :disabled="
                            workspace.is_current || switchRequest.processing
                        "
                        @click="switchWorkspace"
                    >
                        <Spinner v-if="switchRequest.processing" />
                        <CheckCircle2
                            v-else-if="workspace.is_current"
                            aria-hidden="true"
                        />
                        <RefreshCw v-else aria-hidden="true" />
                        {{
                            workspace.is_current
                                ? t('workspaces.current')
                                : switchRequest.processing
                                  ? t('workspaces.switching')
                                  : t('workspaces.actions.switch')
                        }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </section>
</template>
