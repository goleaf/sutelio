<script setup lang="ts">
import {
    AlertTriangle,
    ArrowUpRight,
    Bell,
    Check,
    CheckCircle2,
    Clock3,
    MessageSquareText,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import type { Component } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import type { IconTileTone } from '@/components/shared/IconTile.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import {
    notificationContent,
    notificationPresentation,
} from './notification-inbox';
import type { NotificationItem, NotificationTone } from './notification-inbox';

const props = defineProps<{
    notification: NotificationItem;
    processing: boolean;
}>();
const emit = defineEmits<{
    markRead: [notification: NotificationItem];
    open: [notification: NotificationItem];
}>();
const { copy, formatDate } = useWorkspaceUi();
const justMarkedRead = ref(false);
const iconTones: Record<NotificationTone, IconTileTone> = {
    blue: 'information',
    emerald: 'success',
    orange: 'brand',
    red: 'destructive',
};

watch(
    () => props.notification.is_read,
    (isRead, wasRead) => {
        justMarkedRead.value = isRead && !wasRead;
    },
);

const content = computed(() =>
    notificationContent(props.notification, {
        reminderTitle: copy.value.notifications.reminder_title,
        reminderBody: copy.value.notifications.reminder_body,
        fallbackTitle: copy.value.notifications.fallback_title,
        fallbackBody: copy.value.notifications.fallback_body,
    }),
);

const kindLabel = computed(
    () =>
        ({
            comment: copy.value.notifications.comment_kind,
            completion: copy.value.notifications.completion_kind,
            general: copy.value.notifications.general_kind,
            overdue: copy.value.notifications.overdue_kind,
            reminder: copy.value.notifications.reminder_kind,
        })[props.notification.kind],
);

const presentation = computed(() =>
    notificationPresentation(props.notification.kind),
);

const icon = computed<Component>(
    () =>
        ({
            alert: AlertTriangle,
            bell: Bell,
            check: CheckCircle2,
            clock: Clock3,
            message: MessageSquareText,
        })[presentation.value.icon],
);

const iconTone = computed<IconTileTone>(() => {
    if (props.notification.is_read) {
        return 'muted';
    }

    return iconTones[presentation.value.tone];
});
</script>

<template>
    <article
        data-notification-row
        :data-notification-id="notification.id"
        tabindex="-1"
        :class="[
            'group relative grid scroll-mt-6 grid-cols-[2.75rem_minmax(0,1fr)] gap-3 rounded-2xl border p-4 transition-colors duration-200 outline-none focus-visible:ring-2 focus-visible:ring-orange-500/45 motion-reduce:transition-none sm:grid-cols-[3rem_minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:p-5',
            notification.is_read
                ? 'border-border/70 bg-card hover:bg-muted/30'
                : 'border-orange-500/20 bg-orange-500/[0.045] hover:bg-orange-500/[0.075]',
        ]"
    >
        <span
            v-if="!notification.is_read"
            class="absolute inset-y-3 left-0 w-1 rounded-r-full bg-orange-500"
            aria-hidden="true"
        />

        <IconTile
            :tone="iconTone"
            size="md"
            :class="{ 'ui-status-pop': justMarkedRead }"
        >
            <component :is="icon" />
        </IconTile>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h4
                    :class="[
                        'text-base leading-6 break-words',
                        notification.is_read ? 'font-medium' : 'font-semibold',
                    ]"
                >
                    {{ content.title }}
                </h4>
                <span
                    class="rounded-full border px-2 py-0.5 text-[0.9375rem] font-semibold tracking-wide uppercase"
                    :class="
                        notification.is_read
                            ? 'border-border bg-muted text-muted-foreground'
                            : 'border-orange-500/20 bg-orange-500/10 text-orange-800'
                    "
                >
                    {{
                        notification.is_read
                            ? copy.notifications.read_status
                            : copy.notifications.unread_status
                    }}
                </span>
                <span
                    class="rounded-full bg-muted px-2 py-0.5 text-[0.9375rem] font-medium text-muted-foreground"
                >
                    {{ kindLabel }}
                </span>
            </div>
            <p
                class="mt-1 max-w-3xl text-base leading-6 break-words text-muted-foreground"
            >
                {{ content.body }}
            </p>
            <p class="mt-2 text-[0.9375rem] leading-5 text-muted-foreground/85">
                {{
                    formatDate(notification.created_at, {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    })
                }}
            </p>
        </div>

        <div
            class="col-start-2 flex flex-wrap items-center gap-2 sm:col-start-auto sm:justify-end"
        >
            <Button
                v-if="notification.url"
                type="button"
                variant="ghost"
                size="sm"
                class="min-h-12 cursor-pointer rounded-xl focus-visible:ring-2 focus-visible:ring-orange-500/45 motion-reduce:transition-none pointer-coarse:min-h-13"
                :disabled="processing"
                @click="emit('open', notification)"
            >
                <ArrowUpRight class="size-4" aria-hidden="true" />
                {{ copy.notifications.view_task }}
            </Button>
            <Button
                v-if="!notification.is_read"
                type="button"
                variant="outline"
                size="sm"
                class="min-h-12 cursor-pointer rounded-xl border-orange-500/25 text-orange-800 hover:bg-orange-500/10 hover:text-orange-900 focus-visible:ring-2 focus-visible:ring-orange-500/45 motion-reduce:transition-none pointer-coarse:min-h-13"
                :disabled="processing"
                @click="emit('markRead', notification)"
            >
                <Spinner v-if="processing" />
                <Check v-else class="size-4" aria-hidden="true" />
                {{
                    processing
                        ? copy.notifications.marking_read
                        : copy.notifications.mark_read
                }}
            </Button>
        </div>
    </article>
</template>
