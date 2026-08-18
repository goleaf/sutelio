<script setup lang="ts">
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import {
    LockKeyhole,
    Mail,
    Search,
    ShieldCheck,
    Trash2,
    UserCog,
    UserPlus,
} from '@lucide/vue';
import { computed, ref, watchEffect } from 'vue';
import InputError from '@/components/InputError.vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import WorkspaceDialogContent from '@/components/shared/WorkspaceDialogContent.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Dialog, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/composables/useToast';
import {
    invite as inviteWorkspaceMember,
    removeMember as removeWorkspaceMember,
} from '@/routes/workspaces';
import type { SettingsLayoutProps } from '@/types';

type WorkspaceRole = 'owner' | 'admin' | 'member';

interface WorkspaceSummary {
    id: string;
    name: string;
    slug: string;
    owner_id: string;
}

interface WorkspaceMember {
    id: string;
    name: string;
    email: string;
    role: WorkspaceRole;
    is_current_user: boolean;
    can_remove: boolean;
}

interface MembersCopy {
    page_title: string;
    eyebrow: string;
    title: string;
    description: string;
    total_members: string;
    managers: string;
    roster_title: string;
    roster_description: string;
    search_label: string;
    search_placeholder: string;
    no_results_title: string;
    no_results_description: string;
    current_user: string;
    invite_title: string;
    invite_description: string;
    email_label: string;
    email_placeholder: string;
    role_label: string;
    invite_action: string;
    inviting: string;
    invite_success: string;
    read_only_title: string;
    read_only_description: string;
    remove_member: string;
    remove_title: string;
    remove_description: string;
    cancel: string;
    remove_action: string;
    removing: string;
    remove_success: string;
    roles: Record<WorkspaceRole, string>;
    role_descriptions: Record<WorkspaceRole, string>;
}

const props = defineProps<{
    workspace: WorkspaceSummary;
    members: WorkspaceMember[];
    can_manage_members: boolean;
    locale: string;
    copy: MembersCopy;
}>();

const toast = useToast();
const searchQuery = ref('');
const memberToRemove = ref<WorkspaceMember | null>(null);
const inviteForm = useForm<{ email: string; role: 'admin' | 'member' }>({
    email: '',
    role: 'member',
});
const removeForm = useForm({});

const managerCount = computed(
    () => props.members.filter((member) => member.role !== 'member').length,
);

watchEffect(() => {
    setLayoutProps<SettingsLayoutProps>({
        settingsEyebrow: props.copy.eyebrow,
        settingsTitle: props.copy.title.replace(
            ':workspace',
            props.workspace.name,
        ),
        settingsDescription: props.copy.description,
        settingsMetrics: [
            {
                label: props.copy.total_members,
                value: props.members.length,
                icon: 'users',
                tone: 'orange',
            },
            {
                label: props.copy.managers,
                value: managerCount.value,
                icon: 'shield',
                tone: 'emerald',
            },
        ],
    });
});

const filteredMembers = computed(() => {
    const query = searchQuery.value.trim().toLocaleLowerCase(props.locale);

    if (!query) {
        return props.members;
    }

    return props.members.filter((member) =>
        `${member.name} ${member.email} ${props.copy.roles[member.role]}`
            .toLocaleLowerCase(props.locale)
            .includes(query),
    );
});

const avatarTones = [
    'bg-amber-100 text-amber-950',
    'bg-sky-100 text-sky-950',
    'bg-emerald-100 text-emerald-950',
];

const roleClasses: Record<WorkspaceRole, string> = {
    owner: 'border-amber-200 bg-amber-50 text-amber-800',
    admin: 'border-sky-200 bg-sky-50 text-sky-800',
    member: 'border-border bg-muted/50 text-muted-foreground',
};

function initials(name: string): string {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toLocaleUpperCase(props.locale);
}

function avatarTone(memberId: string): string {
    const characterCode = memberId.charCodeAt(memberId.length - 1) || 0;

    return avatarTones[characterCode % avatarTones.length];
}

function invite(): void {
    inviteForm.submit(
        inviteWorkspaceMember({ workspace: props.workspace.id }),
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(props.copy.invite_success);
                inviteForm.reset('email');
            },
        },
    );
}

function openRemoveDialog(member: WorkspaceMember): void {
    memberToRemove.value = member;
}

function handleRemoveDialogOpen(open: boolean): void {
    if (!open && !removeForm.processing) {
        memberToRemove.value = null;
    }
}

function removeMember(): void {
    if (!memberToRemove.value) {
        return;
    }

    removeForm.submit(
        removeWorkspaceMember({
            workspace: props.workspace.id,
            userId: memberToRemove.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(props.copy.remove_success);
                memberToRemove.value = null;
            },
        },
    );
}
</script>

<template>
    <Head :title="copy.page_title" />

    <div class="space-y-6 pb-8">
        <div
            class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(18rem,0.75fr)]"
        >
            <Card class="gap-0 overflow-hidden py-0">
                <CardHeader class="gap-5 border-b py-5 sm:py-6">
                    <div class="space-y-1.5">
                        <CardTitle as="h2">{{ copy.roster_title }}</CardTitle>
                        <CardDescription>
                            {{ copy.roster_description }}
                        </CardDescription>
                    </div>

                    <div class="relative sm:max-w-sm">
                        <Label for="member-search" class="sr-only">
                            {{ copy.search_label }}
                        </Label>
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            id="member-search"
                            v-model="searchQuery"
                            type="search"
                            :placeholder="copy.search_placeholder"
                            class="pl-9"
                        />
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <ul
                        v-if="filteredMembers.length"
                        class="divide-y"
                        role="list"
                    >
                        <li
                            v-for="member in filteredMembers"
                            :key="member.id"
                            class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-3 gap-y-3 px-5 py-4 transition-colors hover:bg-muted/35 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:items-center sm:px-6"
                        >
                            <Avatar class="size-11 border shadow-xs">
                                <AvatarFallback
                                    :class="[
                                        'text-base font-semibold',
                                        avatarTone(member.id),
                                    ]"
                                >
                                    {{ initials(member.name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="text-base leading-6 font-semibold break-words"
                                    >
                                        {{ member.name }}
                                    </p>
                                    <Badge
                                        v-if="member.is_current_user"
                                        variant="secondary"
                                        class="px-2 py-0.5 text-[0.9375rem]"
                                    >
                                        {{ copy.current_user }}
                                    </Badge>
                                </div>
                                <a
                                    :href="`mailto:${member.email}`"
                                    class="mt-0.5 block text-[0.9375rem] leading-5 wrap-anywhere text-muted-foreground underline-offset-4 transition-colors hover:text-orange-800 hover:underline focus-visible:rounded-md focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none"
                                >
                                    {{ member.email }}
                                </a>
                            </div>

                            <div
                                class="col-span-2 flex items-center justify-between gap-3 pl-14 sm:col-span-1 sm:justify-end sm:pl-0"
                            >
                                <div class="min-w-0 text-right">
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            'text-[0.9375rem] whitespace-normal',
                                            roleClasses[member.role],
                                        ]"
                                    >
                                        {{ copy.roles[member.role] }}
                                    </Badge>
                                    <p
                                        class="mt-1 max-w-64 text-[0.9375rem] leading-5 break-words text-muted-foreground sm:max-w-40 sm:text-right"
                                    >
                                        {{
                                            copy.role_descriptions[member.role]
                                        }}
                                    </p>
                                </div>

                                <Button
                                    v-if="member.can_remove"
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    class="min-h-12 min-w-12 text-muted-foreground hover:bg-destructive/10 hover:text-destructive pointer-coarse:min-h-13 pointer-coarse:min-w-13"
                                    :aria-label="
                                        copy.remove_member.replace(
                                            ':name',
                                            member.name,
                                        )
                                    "
                                    @click="openRemoveDialog(member)"
                                >
                                    <Trash2 aria-hidden="true" />
                                </Button>
                            </div>
                        </li>
                    </ul>

                    <div
                        v-else
                        class="flex min-h-56 flex-col items-center justify-center px-6 py-12 text-center"
                    >
                        <IconTile tone="muted" size="md">
                            <Search />
                        </IconTile>
                        <p class="mt-4 text-base font-semibold">
                            {{ copy.no_results_title }}
                        </p>
                        <p
                            class="mt-1 max-w-sm text-base leading-6 text-muted-foreground"
                        >
                            {{ copy.no_results_description }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="can_manage_members" class="xl:sticky xl:top-6">
                <CardHeader>
                    <LeadingIconHeading tile tile-tone="brand">
                        <template #icon>
                            <UserPlus />
                        </template>

                        <CardTitle as="h2">{{ copy.invite_title }}</CardTitle>
                        <CardDescription>
                            {{ copy.invite_description }}
                        </CardDescription>
                    </LeadingIconHeading>
                </CardHeader>
                <CardContent>
                    <form class="space-y-4" @submit.prevent="invite">
                        <div class="space-y-2">
                            <Label for="invite-email">
                                {{ copy.email_label }}
                            </Label>
                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <Input
                                    id="invite-email"
                                    v-model="inviteForm.email"
                                    type="email"
                                    autocomplete="email"
                                    :placeholder="copy.email_placeholder"
                                    class="pl-9"
                                    :disabled="inviteForm.processing"
                                    :aria-invalid="
                                        Boolean(inviteForm.errors.email)
                                    "
                                    required
                                    @input="inviteForm.clearErrors('email')"
                                />
                            </div>
                            <InputError :message="inviteForm.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="invite-role">
                                {{ copy.role_label }}
                            </Label>
                            <Select
                                v-model="inviteForm.role"
                                :disabled="inviteForm.processing"
                            >
                                <SelectTrigger
                                    id="invite-role"
                                    class="min-h-12 w-full pointer-coarse:min-h-13"
                                    :aria-invalid="
                                        Boolean(inviteForm.errors.role)
                                    "
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="member">
                                        {{ copy.roles.member }}
                                    </SelectItem>
                                    <SelectItem value="admin">
                                        {{ copy.roles.admin }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="inviteForm.errors.role" />
                        </div>

                        <Button
                            type="submit"
                            size="lg"
                            class="w-full"
                            :loading="inviteForm.processing"
                            :loading-label="copy.inviting"
                        >
                            <UserPlus aria-hidden="true" />
                            {{ copy.invite_action }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-else class="bg-muted/25 xl:sticky xl:top-6">
                <CardHeader>
                    <LeadingIconHeading tile tile-tone="muted">
                        <template #icon>
                            <LockKeyhole />
                        </template>

                        <CardTitle as="h2">{{
                            copy.read_only_title
                        }}</CardTitle>
                        <CardDescription>
                            {{ copy.read_only_description }}
                        </CardDescription>
                    </LeadingIconHeading>
                </CardHeader>
                <CardContent>
                    <div
                        class="flex items-center gap-3 rounded-xl border bg-background p-3 text-base"
                    >
                        <ShieldCheck
                            class="size-5 shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <span>{{ workspace.name }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog
            :open="memberToRemove !== null"
            @update:open="handleRemoveDialogOpen"
        >
            <WorkspaceDialogContent
                :title="copy.remove_title"
                :description="
                    memberToRemove
                        ? `${memberToRemove.name} — ${copy.remove_description}`
                        : copy.remove_description
                "
                :close-label="copy.cancel"
                accent="red"
                max-width-class="sm:max-w-md"
            >
                <div class="space-y-6 px-6 py-6 sm:px-8">
                    <IconTile tone="destructive" size="md">
                        <UserCog />
                    </IconTile>
                    <DialogFooter
                        class="gap-2 border-t border-border/70 pt-5 sm:gap-2"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            size="lg"
                            :disabled="removeForm.processing"
                            @click="memberToRemove = null"
                        >
                            {{ copy.cancel }}
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            size="lg"
                            :loading="removeForm.processing"
                            :loading-label="copy.removing"
                            @click="removeMember"
                        >
                            <Trash2 aria-hidden="true" />
                            {{ copy.remove_action }}
                        </Button>
                    </DialogFooter>
                </div>
            </WorkspaceDialogContent>
        </Dialog>
    </div>
</template>
