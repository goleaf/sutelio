<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { toast } from 'vue-sonner';
import { useUi } from '@/composables/useUi';

const networkStatusToastId = 'network-status';
const { t } = useUi();
let connectionInterrupted = false;
let removeRouterListeners: VoidFunction[] = [];

function showOffline(): void {
    connectionInterrupted = true;
    toast.warning(t('common.toast.connection_offline'), {
        duration: Number.POSITIVE_INFINITY,
        id: networkStatusToastId,
    });
}

function showRestored(): void {
    if (!connectionInterrupted) {
        return;
    }

    connectionInterrupted = false;
    toast.success(t('common.toast.connection_restored'), {
        duration: 5_000,
        id: networkStatusToastId,
    });
}

onMounted(() => {
    removeRouterListeners = [
        router.on('networkError', (event) => {
            event.preventDefault();

            if (!navigator.onLine) {
                showOffline();

                return;
            }

            connectionInterrupted = true;
            toast.error(t('common.toast.network_error'), {
                duration: 8_000,
                id: networkStatusToastId,
            });
        }),
        router.on('success', showRestored),
    ];

    window.addEventListener('offline', showOffline);
    window.addEventListener('online', showRestored);

    if (!navigator.onLine) {
        showOffline();
    }
});

onUnmounted(() => {
    for (const removeListener of removeRouterListeners) {
        removeListener();
    }

    removeRouterListeners = [];
    window.removeEventListener('offline', showOffline);
    window.removeEventListener('online', showRestored);
});
</script>

<template>
    <span
        data-slot="network-status-notifier"
        class="hidden"
        aria-hidden="true"
    />
</template>
