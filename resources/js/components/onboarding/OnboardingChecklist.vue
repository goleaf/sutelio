<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    BellRing,
    Check,
    DatabaseBackup,
    FileArchive,
    ShieldCheck,
    Sparkles,
    UserPlus,
    X,
} from '@lucide/vue';
import { dismissChecklist } from '@/actions/App/Http/Controllers/OnboardingController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useUi } from '@/composables/useUi';
import { edit as editBackups } from '@/routes/backup';
import { edit as editExport } from '@/routes/export';
import { edit as editNotifications } from '@/routes/notifications';
import { edit as editSecurity } from '@/routes/security';
import { members as workspaceMembers } from '@/routes/workspaces';

export interface OnboardingChecklistState {
    show: boolean;
    workspace_id: string | null;
    can_invite: boolean;
    has_team_member: boolean;
    has_security_factor: boolean;
    can_manage_backups: boolean;
}

defineProps<{
    checklist: OnboardingChecklistState;
}>();

const { t } = useUi();
const dismissForm = useForm({});

function dismiss(): void {
    dismissForm.delete(dismissChecklist.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <section v-if="checklist.show" aria-labelledby="onboarding-checklist-title">
        <Card
            class="relative overflow-hidden border-orange-200/80 bg-gradient-to-br from-orange-50 via-background to-amber-50/70 shadow-sm dark:border-orange-900/60 dark:from-orange-950/35 dark:via-card dark:to-amber-950/20"
        >
            <div
                class="pointer-events-none absolute -top-20 -right-16 size-48 rounded-full bg-orange-200/30 blur-3xl dark:bg-orange-700/15"
                aria-hidden="true"
            />
            <CardHeader class="relative gap-3 pr-16 sm:pr-20">
                <div
                    class="flex size-11 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-sm"
                >
                    <Sparkles class="size-5" aria-hidden="true" />
                </div>
                <div class="max-w-3xl space-y-1.5">
                    <h2
                        id="onboarding-checklist-title"
                        class="text-xl font-semibold tracking-[-0.025em] sm:text-2xl"
                    >
                        {{ t('dashboard.onboarding.title') }}
                    </h2>
                    <p class="text-sm leading-6 text-muted-foreground">
                        {{ t('dashboard.onboarding.description') }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="absolute top-4 right-4 min-h-11 min-w-11 rounded-full"
                    :aria-label="t('dashboard.onboarding.dismiss')"
                    :disabled="dismissForm.processing"
                    @click="dismiss"
                >
                    <X class="size-4" aria-hidden="true" />
                </Button>
            </CardHeader>

            <CardContent class="relative">
                <ul class="grid gap-3 lg:grid-cols-2">
                    <li v-if="checklist.can_invite && checklist.workspace_id">
                        <Link
                            :href="workspaceMembers(checklist.workspace_id)"
                            class="group flex min-h-16 items-center gap-3 rounded-2xl border bg-background/85 p-3.5 transition-colors hover:border-orange-300 hover:bg-background focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none dark:hover:border-orange-800"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300"
                            >
                                <Check
                                    v-if="checklist.has_team_member"
                                    class="size-5"
                                    aria-hidden="true"
                                />
                                <UserPlus
                                    v-else
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium">{{
                                        t('dashboard.onboarding.invite_action')
                                    }}</span>
                                    <span
                                        v-if="checklist.has_team_member"
                                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
                                    >
                                        {{
                                            t('dashboard.onboarding.completed')
                                        }}
                                    </span>
                                </span>
                                <span
                                    class="mt-0.5 block text-sm leading-5 text-muted-foreground"
                                >
                                    {{
                                        t(
                                            checklist.has_team_member
                                                ? 'dashboard.onboarding.team_complete'
                                                : 'dashboard.onboarding.invite_description',
                                        )
                                    }}
                                </span>
                            </span>
                            <ArrowUpRight
                                class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </Link>
                    </li>

                    <li>
                        <Link
                            :href="editNotifications()"
                            class="group flex min-h-16 items-center gap-3 rounded-2xl border bg-background/85 p-3.5 transition-colors hover:border-orange-300 hover:bg-background focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none dark:hover:border-orange-800"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300"
                            >
                                <BellRing class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="font-medium">{{
                                    t(
                                        'dashboard.onboarding.notifications_action',
                                    )
                                }}</span>
                                <span
                                    class="mt-0.5 block text-sm leading-5 text-muted-foreground"
                                    >{{
                                        t(
                                            'dashboard.onboarding.notifications_description',
                                        )
                                    }}</span
                                >
                            </span>
                            <ArrowUpRight
                                class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </Link>
                    </li>

                    <li>
                        <Link
                            :href="editSecurity()"
                            class="group flex min-h-16 items-center gap-3 rounded-2xl border bg-background/85 p-3.5 transition-colors hover:border-orange-300 hover:bg-background focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none dark:hover:border-orange-800"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                            >
                                <Check
                                    v-if="checklist.has_security_factor"
                                    class="size-5"
                                    aria-hidden="true"
                                />
                                <ShieldCheck
                                    v-else
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium">{{
                                        t(
                                            'dashboard.onboarding.security_action',
                                        )
                                    }}</span>
                                    <span
                                        v-if="checklist.has_security_factor"
                                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
                                    >
                                        {{
                                            t('dashboard.onboarding.completed')
                                        }}
                                    </span>
                                </span>
                                <span
                                    class="mt-0.5 block text-sm leading-5 text-muted-foreground"
                                >
                                    {{
                                        t(
                                            checklist.has_security_factor
                                                ? 'dashboard.onboarding.security_complete'
                                                : 'dashboard.onboarding.security_description',
                                        )
                                    }}
                                </span>
                            </span>
                            <ArrowUpRight
                                class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </Link>
                    </li>

                    <li v-if="checklist.workspace_id">
                        <Link
                            :href="editExport()"
                            class="group flex min-h-16 items-center gap-3 rounded-2xl border bg-background/85 p-3.5 transition-colors hover:border-orange-300 hover:bg-background focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none dark:hover:border-orange-800"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300"
                            >
                                <FileArchive
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="font-medium">{{
                                    t('dashboard.onboarding.export_action')
                                }}</span>
                                <span
                                    class="mt-0.5 block text-sm leading-5 text-muted-foreground"
                                    >{{
                                        t(
                                            'dashboard.onboarding.export_description',
                                        )
                                    }}</span
                                >
                            </span>
                            <ArrowUpRight
                                class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </Link>
                    </li>

                    <li v-if="checklist.can_manage_backups">
                        <Link
                            :href="editBackups()"
                            class="group flex min-h-16 items-center gap-3 rounded-2xl border bg-background/85 p-3.5 transition-colors hover:border-orange-300 hover:bg-background focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none dark:hover:border-orange-800"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            >
                                <DatabaseBackup
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="font-medium">{{
                                    t('dashboard.onboarding.backup_action')
                                }}</span>
                                <span
                                    class="mt-0.5 block text-sm leading-5 text-muted-foreground"
                                    >{{
                                        t(
                                            'dashboard.onboarding.backup_description',
                                        )
                                    }}</span
                                >
                            </span>
                            <ArrowUpRight
                                class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </Link>
                    </li>
                </ul>

                <p
                    v-if="dismissForm.processing || dismissForm.hasErrors"
                    class="mt-3 text-sm text-muted-foreground"
                    :class="{ 'text-destructive': dismissForm.hasErrors }"
                    :role="dismissForm.hasErrors ? 'alert' : undefined"
                    aria-live="polite"
                >
                    {{
                        t(
                            dismissForm.hasErrors
                                ? 'dashboard.onboarding.dismiss_error'
                                : 'dashboard.onboarding.dismissing',
                        )
                    }}
                </p>
            </CardContent>
        </Card>
    </section>
</template>
