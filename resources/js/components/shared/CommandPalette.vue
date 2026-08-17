<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, Folder, CheckSquare, Settings, LogOut } from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useUi } from '@/composables/useUi';
import { dashboard, logout, projects } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { index as tasks } from '@/routes/todos';
import { useUiStore } from '@/stores/ui';

const ui = useUiStore();
const { t } = useUi();
const query = ref('');
const inputRef = ref<InstanceType<typeof Input> | null>(null);

interface CommandItem {
    id: string;
    label: string;
    icon: typeof Search;
    action: () => void;
    section: string;
}

const commands = computed<CommandItem[]>(() => [
    {
        id: 'dashboard',
        label: t('commands.go_dashboard'),
        icon: CheckSquare,
        action: () => router.visit(dashboard().url),
        section: t('commands.navigation'),
    },
    {
        id: 'tasks',
        label: t('commands.go_tasks'),
        icon: CheckSquare,
        action: () => router.visit(tasks().url),
        section: t('commands.navigation'),
    },
    {
        id: 'projects',
        label: t('commands.go_projects'),
        icon: Folder,
        action: () => router.visit(projects().url),
        section: t('commands.navigation'),
    },
    {
        id: 'settings',
        label: t('commands.go_settings'),
        icon: Settings,
        action: () => router.visit(editProfile().url),
        section: t('commands.navigation'),
    },
    {
        id: 'logout',
        label: t('commands.logout'),
        icon: LogOut,
        action: () => router.post(logout().url),
        section: t('commands.account'),
    },
]);

const filteredCommands = computed(() => {
    if (!query.value) {
        return commands.value;
    }

    return commands.value.filter((c) =>
        c.label.toLowerCase().includes(query.value.toLowerCase()),
    );
});

const groupedCommands = computed(() => {
    const groups: Record<string, CommandItem[]> = {};
    filteredCommands.value.forEach((cmd) => {
        if (!groups[cmd.section]) {
            groups[cmd.section] = [];
        }

        groups[cmd.section].push(cmd);
    });

    return groups;
});

watch(
    () => ui.commandPaletteOpen,
    (open) => {
        if (open) {
            query.value = '';
            setTimeout(() => inputRef.value?.$el?.focus(), 100);
        }
    },
);

function executeCommand(command: CommandItem) {
    command.action();
    ui.closeCommandPalette();
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        ui.closeCommandPalette();
    }
}

function handleOpenChange(open: boolean): void {
    if (!open) {
        ui.closeCommandPalette();
    }
}
</script>

<template>
    <Dialog :open="ui.commandPaletteOpen" @update:open="handleOpenChange">
        <DialogContent class="gap-0 overflow-hidden p-0 sm:max-w-md">
            <DialogHeader class="sr-only">
                <DialogTitle>{{ t('commands.placeholder') }}</DialogTitle>
                <DialogDescription>
                    {{ t('commands.navigation') }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex items-center border-b px-4 pr-14">
                <Search
                    class="size-4 text-muted-foreground"
                    aria-hidden="true"
                />
                <Input
                    ref="inputRef"
                    v-model="query"
                    :aria-label="t('commands.placeholder')"
                    :placeholder="t('commands.placeholder')"
                    class="border-0 shadow-none focus-visible:ring-0"
                    @keydown="handleKeydown"
                />
            </div>
            <div class="max-h-[300px] overflow-y-auto p-2">
                <template
                    v-for="(items, section) in groupedCommands"
                    :key="section"
                >
                    <p
                        class="px-2 py-1 text-xs font-medium text-muted-foreground"
                    >
                        {{ section }}
                    </p>
                    <Button
                        v-for="cmd in items"
                        :key="cmd.id"
                        type="button"
                        variant="ghost"
                        class="h-auto min-h-11 w-full justify-start gap-3 px-3 py-2 font-normal"
                        @click="executeCommand(cmd)"
                    >
                        <IconTile tone="muted" size="sm">
                            <component :is="cmd.icon" />
                        </IconTile>
                        {{ cmd.label }}
                    </Button>
                </template>
                <p
                    v-if="filteredCommands.length === 0"
                    class="px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    {{ t('commands.empty') }}
                </p>
            </div>
        </DialogContent>
    </Dialog>
</template>
