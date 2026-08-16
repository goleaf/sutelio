<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    User,
    Shield,
    ShieldCheck,
    Bell,
    Building2,
    Download,
    UsersRound,
    Database,
    Globe,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import { edit as editProfile } from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SettingsSectionMenu from '@/components/settings/SettingsSectionMenu.vue';
import WorkspaceMetric from '@/components/shared/WorkspaceMetric.vue';
import WorkspacePageHeader from '@/components/shared/WorkspacePageHeader.vue';
import { useUi } from '@/composables/useUi';
import { edit as editBackup } from '@/routes/backup';
import { edit as editExport } from '@/routes/export';
import { edit as editNotifications } from '@/routes/notifications';
import { edit as editPreferences } from '@/routes/preferences';
import { edit as editSecurity } from '@/routes/security';
import { show as showWorkspace } from '@/routes/workspaces';
import type { SettingsLayoutProps } from '@/types';

type SettingsNavItem = {
    active: boolean;
    href: string;
    icon: LucideIcon;
    label: string;
};

const page = usePage();
const currentUrl = computed(() => page.url);
const { t } = useUi();

const props = defineProps<SettingsLayoutProps>();

const navItems = computed<SettingsNavItem[]>(() =>
    [
        {
            label: t('settings.navigation.profile'),
            href: editProfile.url(),
            icon: User,
        },
        {
            label: t('settings.navigation.security'),
            href: editSecurity.url(),
            icon: Shield,
        },
        {
            label: t('settings.navigation.preferences'),
            href: editPreferences.url(),
            icon: Globe,
        },
        {
            label: t('settings.navigation.notifications'),
            href: editNotifications.url(),
            icon: Bell,
        },
        ...(page.props.navigation.currentWorkspace
            ? [
                  {
                      label: t('settings.navigation.workspace_management'),
                      href: showWorkspace.url(
                          page.props.navigation.currentWorkspace,
                      ),
                      icon: Building2,
                  },
              ]
            : []),
        {
            label: t('settings.navigation.export'),
            href: editExport.url(),
            icon: Download,
        },
        ...(page.props.capabilities.manageDatabaseBackups
            ? [
                  {
                      label: t('settings.navigation.backup'),
                      href: editBackup.url(),
                      icon: Database,
                  },
              ]
            : []),
    ].map((item) => ({
        ...item,
        active: currentUrl.value.startsWith(item.href),
    })),
);

const activeNavItem = computed(() =>
    navItems.value.find((item) => item.active),
);

const pageEyebrow = computed(
    () => props.settingsEyebrow ?? t('account.menu.settings'),
);
const pageTitle = computed(
    () =>
        props.settingsTitle ??
        activeNavItem.value?.label ??
        t('account.menu.settings'),
);
const pageDescription = computed(() => props.settingsDescription ?? '');

const metricIcons = {
    shield: ShieldCheck,
    users: UsersRound,
};
</script>

<template>
    <main class="min-h-full bg-muted/20 px-4 py-5 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-app space-y-6">
            <WorkspacePageHeader
                :eyebrow="pageEyebrow"
                :title="pageTitle"
                :description="pageDescription"
            >
                <template v-if="settingsMetrics?.length" #metrics>
                    <WorkspaceMetric
                        v-for="metric in settingsMetrics"
                        :key="`${metric.label}-${metric.value}`"
                        :label="metric.label"
                        :value="metric.value"
                        :icon="metricIcons[metric.icon]"
                        :tone="metric.tone"
                    />
                </template>
            </WorkspacePageHeader>

            <div
                class="flex flex-col gap-6 rounded-panel border border-border/80 bg-card p-4 shadow-panel sm:p-6 lg:flex-row lg:gap-8"
            >
                <SettingsSectionMenu
                    :items="navItems"
                    :current-label="t('settings.navigation.current_section')"
                    :open-label="t('settings.navigation.open_sections')"
                />
                <div class="hidden w-52 shrink-0 self-start lg:block">
                    <nav
                        :aria-label="
                            props.navigationLabel ?? t('account.menu.settings')
                        "
                        class="flex flex-col gap-1 rounded-xl bg-muted/55 p-1"
                    >
                        <Link
                            v-for="item in navItems"
                            :key="item.href"
                            :href="item.href"
                            :aria-current="item.active ? 'page' : undefined"
                            :class="[
                                'flex min-h-11 shrink-0 items-center gap-3 rounded-lg px-3 py-2 text-sm whitespace-nowrap transition-all focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none',
                                item.active
                                    ? 'bg-card font-medium text-orange-800 shadow-sm dark:text-orange-200'
                                    : 'text-muted-foreground hover:bg-card/70 hover:text-foreground',
                            ]"
                        >
                            <component
                                :is="item.icon"
                                class="size-4"
                                aria-hidden="true"
                            />
                            {{ item.label }}
                        </Link>
                    </nav>
                </div>
                <div class="settings-page min-w-0 flex-1">
                    <slot />
                </div>
            </div>
        </div>
    </main>
</template>
