<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
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
import WorkspacePageFrame from '@/components/shared/WorkspacePageFrame.vue';
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
    <WorkspacePageFrame>
        <WorkspacePageHeader
            :eyebrow="pageEyebrow"
            :title="pageTitle"
            :description="pageDescription"
        >
            <template #icon>
                <component
                    :is="activeNavItem?.icon ?? User"
                    aria-hidden="true"
                />
            </template>

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
                :label="props.navigationLabel ?? t('account.menu.settings')"
                :current-label="t('settings.navigation.current_section')"
                :open-label="t('settings.navigation.open_sections')"
            />
            <div class="settings-page min-w-0 flex-1">
                <slot />
            </div>
        </div>
    </WorkspacePageFrame>
</template>
