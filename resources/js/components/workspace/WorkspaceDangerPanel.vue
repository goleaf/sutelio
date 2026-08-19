<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import { Crown, LockKeyhole, ShieldAlert, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import PageConfirmPanel from '@/components/shared/PageConfirmPanel.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { store as transferWorkspaceOwnership } from '@/routes/api/v1/workspace-ownership';
import { destroy, index } from '@/routes/workspaces';
import type { Workspace, WorkspaceManagementMember } from '@/types/models';

interface WorkspaceResponse {
    data: Workspace;
}

const props = defineProps<{
    workspace: Workspace;
    members: WorkspaceManagementMember[];
}>();

const toast = useToast();
const { formatNumber, t } = useUi();
const showTransferConfirmation = ref(false);
const showDeleteConfirmation = ref(false);
const transferForm = useHttp<{ user_id: string }, WorkspaceResponse>({
    user_id: '',
});
const deleteRequest = useHttp<Record<string, never>>({});

const ownershipCandidates = computed(() =>
    props.members.filter(
        (member) =>
            member.role !== 'owner' && member.permissions.transfer_ownership,
    ),
);

watch(
    ownershipCandidates,
    (members) => {
        if (!members.some((member) => member.id === transferForm.user_id)) {
            transferForm.user_id = '';
        }
    },
    { immediate: true },
);

function requestOwnershipTransfer(): void {
    if (!transferForm.user_id) {
        transferForm.setError(
            'user_id',
            t('workspaces.management.danger.transfer_placeholder'),
        );

        return;
    }

    showTransferConfirmation.value = true;
}

function setTransferConfirmation(open: boolean): void {
    if (!open && !transferForm.processing) {
        showTransferConfirmation.value = false;
    }
}

async function transferOwnership(): Promise<void> {
    try {
        await transferForm.submit(transferWorkspaceOwnership(props.workspace));

        if (!transferForm.wasSuccessful) {
            toast.error(t('workspaces.management.danger.transfer_failed'));

            return;
        }

        toast.success(t('workspaces.management.danger.transferred'));
        showTransferConfirmation.value = false;
        transferForm.resetAndClearErrors();
        router.reload({ only: ['workspace', 'members', 'navigation'] });
    } catch {
        if (!transferForm.hasErrors) {
            toast.error(t('workspaces.management.danger.transfer_failed'));
        }
    }
}

function setDeleteConfirmation(open: boolean): void {
    if (!open && !deleteRequest.processing) {
        showDeleteConfirmation.value = false;
    }
}

async function deleteWorkspace(): Promise<void> {
    try {
        await deleteRequest.submit(destroy(props.workspace));

        if (!deleteRequest.wasSuccessful) {
            toast.error(t('workspaces.delete_failed'));

            return;
        }

        toast.success(t('workspaces.deleted'));
        router.visit(index());
    } catch {
        toast.error(t('workspaces.delete_failed'));
    }
}
</script>

<template>
    <section class="space-y-6" aria-labelledby="workspace-danger-title">
        <Alert
            variant="destructive"
            class="border-destructive/20 bg-destructive/[0.04]"
        >
            <ShieldAlert aria-hidden="true" />
            <AlertTitle id="workspace-danger-title">
                {{ t('workspaces.management.danger.title') }}
            </AlertTitle>
            <AlertDescription>
                {{ t('workspaces.management.danger.description') }}
            </AlertDescription>
        </Alert>

        <div
            v-if="
                workspace.permissions?.transfer_ownership ||
                workspace.permissions?.delete
            "
            class="grid items-start gap-6 xl:grid-cols-2"
        >
            <Card
                v-if="workspace.permissions?.transfer_ownership"
                class="border-status-warning-border"
            >
                <CardHeader>
                    <LeadingIconHeading tile tile-tone="warning">
                        <template #icon>
                            <Crown />
                        </template>

                        <CardTitle as="h2">
                            {{
                                t('workspaces.management.danger.transfer_title')
                            }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                t(
                                    'workspaces.management.danger.transfer_description',
                                )
                            }}
                        </CardDescription>
                    </LeadingIconHeading>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="new-workspace-owner">
                            {{
                                t('workspaces.management.danger.transfer_label')
                            }}
                        </Label>
                        <Select
                            v-model="transferForm.user_id"
                            :disabled="transferForm.processing"
                            @update:model-value="
                                transferForm.clearErrors('user_id')
                            "
                        >
                            <SelectTrigger
                                id="new-workspace-owner"
                                class="w-full"
                                :aria-invalid="
                                    Boolean(transferForm.errors.user_id)
                                "
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'workspaces.management.danger.transfer_placeholder',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="member in ownershipCandidates"
                                    :key="member.id"
                                    :value="member.id"
                                >
                                    <span>{{ member.name }}</span>
                                    <span class="text-muted-foreground">
                                        {{
                                            t(
                                                `workspaces.management.members.roles.${member.role}`,
                                            )
                                        }}
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="transferForm.errors.user_id" />
                    </div>
                    <Button
                        variant="outline"
                        size="lg"
                        class="w-full border-status-warning-border text-status-warning-text hover:bg-status-warning-surface"
                        :disabled="transferForm.processing"
                        @click="requestOwnershipTransfer"
                    >
                        <Crown aria-hidden="true" />
                        {{ t('workspaces.management.danger.transfer_action') }}
                    </Button>
                </CardContent>
            </Card>

            <Card
                v-if="workspace.permissions?.delete"
                class="border-destructive/20"
            >
                <CardHeader>
                    <LeadingIconHeading tile tile-tone="destructive">
                        <template #icon>
                            <Trash2 />
                        </template>

                        <CardTitle as="h2">
                            {{ t('workspaces.management.danger.delete_title') }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                t(
                                    'workspaces.management.danger.delete_description',
                                )
                            }}
                        </CardDescription>
                    </LeadingIconHeading>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        class="grid grid-cols-1 gap-3 rounded-xl border border-destructive/15 bg-destructive/[0.035] p-3 text-center sm:grid-cols-3"
                    >
                        <div>
                            <p class="text-lg font-semibold tabular-nums">
                                {{ formatNumber(workspace.members_count ?? 0) }}
                            </p>
                            <p
                                class="text-[0.9375rem] leading-5 text-muted-foreground"
                            >
                                {{ t('workspaces.members') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-lg font-semibold tabular-nums">
                                {{
                                    formatNumber(workspace.projects_count ?? 0)
                                }}
                            </p>
                            <p
                                class="text-[0.9375rem] leading-5 text-muted-foreground"
                            >
                                {{ t('workspaces.projects') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-lg font-semibold tabular-nums">
                                {{ formatNumber(workspace.todos_count ?? 0) }}
                            </p>
                            <p
                                class="text-[0.9375rem] leading-5 text-muted-foreground"
                            >
                                {{ t('workspaces.tasks') }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="destructive"
                        size="lg"
                        class="w-full"
                        @click="showDeleteConfirmation = true"
                    >
                        <Trash2 aria-hidden="true" />
                        {{ t('workspaces.actions.delete') }}
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Card v-else class="bg-muted/25">
            <CardHeader>
                <LeadingIconHeading tile tile-tone="muted">
                    <template #icon>
                        <LockKeyhole />
                    </template>

                    <CardTitle as="h2">
                        {{ t('workspaces.management.danger.owner_title') }}
                    </CardTitle>
                    <CardDescription>
                        {{
                            t('workspaces.management.danger.owner_description')
                        }}
                    </CardDescription>
                </LeadingIconHeading>
            </CardHeader>
            <CardContent>
                <Badge
                    variant="outline"
                    class="text-[0.9375rem] whitespace-normal"
                >
                    <Crown aria-hidden="true" />
                    {{ workspace.owner?.name ?? '—' }}
                </Badge>
            </CardContent>
        </Card>

        <PageConfirmPanel
            :open="showTransferConfirmation"
            :title="t('workspaces.management.danger.transfer_title')"
            :description="
                t('workspaces.management.danger.transfer_description')
            "
            :confirm-label="t('workspaces.management.danger.transfer_action')"
            :cancel-label="t('common.actions.cancel')"
            :processing="transferForm.processing"
            :confirmation-text="workspace.name"
            :confirmation-label="
                t('workspaces.management.danger.transfer_confirmation', {
                    workspace: workspace.name,
                })
            "
            destructive
            @update:open="setTransferConfirmation"
            @confirm="transferOwnership"
        />

        <PageConfirmPanel
            :open="showDeleteConfirmation"
            :title="t('workspaces.management.danger.delete_title')"
            :description="
                t('workspaces.delete_description', {
                    name: workspace.name,
                    members: formatNumber(workspace.members_count ?? 0),
                    projects: formatNumber(workspace.projects_count ?? 0),
                    tasks: formatNumber(workspace.todos_count ?? 0),
                })
            "
            :confirm-label="t('workspaces.actions.delete')"
            :cancel-label="t('common.actions.cancel')"
            :processing="deleteRequest.processing"
            :confirmation-text="workspace.name"
            :confirmation-label="
                t('workspaces.delete_confirmation', {
                    name: workspace.name,
                })
            "
            destructive
            @update:open="setDeleteConfirmation"
            @confirm="deleteWorkspace"
        />
    </section>
</template>
