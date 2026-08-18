<script lang="ts">
export type StatusNoticeStatus =
    'information' | 'loading' | 'success' | 'error';
</script>

<script setup lang="ts">
import { BadgeCheck, CircleAlert, Info, LoaderCircle } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import type { IconTileTone } from '@/components/shared/IconTile.vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        message: string;
        status?: StatusNoticeStatus;
        class?: HTMLAttributes['class'];
    }>(),
    {
        status: 'information',
    },
);

const role = computed(() => (props.status === 'error' ? 'alert' : 'status'));
const ariaLive = computed(() =>
    props.status === 'error' ? 'assertive' : 'polite',
);
const icon = computed(
    () =>
        ({
            information: Info,
            loading: LoaderCircle,
            success: BadgeCheck,
            error: CircleAlert,
        })[props.status],
);
const iconTone = computed<IconTileTone>(
    () =>
        ({
            information: 'information',
            loading: 'brand',
            success: 'success',
            error: 'destructive',
        })[props.status] as IconTileTone,
);
const statusClass = computed(
    () =>
        ({
            information:
                'border-status-information-border bg-linear-to-br from-status-information-surface via-background to-status-information-border/35 text-status-information-text',
            loading:
                'border-orange-500/25 bg-linear-to-br from-orange-50/90 via-background to-orange-100/75 text-orange-950',
            success:
                'motion-safe:ui-status-pop border-status-success-border bg-linear-to-br from-status-success-surface via-background to-status-success-border/35 text-status-success-text',
            error: 'border-status-destructive-border bg-linear-to-br from-status-destructive-surface via-background to-status-destructive-border/35 text-status-destructive-text',
        })[props.status],
);
</script>

<template>
    <div
        data-slot="status-notice"
        :data-status="props.status"
        :role="role"
        :aria-live="ariaLive"
        aria-atomic="true"
        :aria-busy="props.status === 'loading' ? 'true' : undefined"
        :class="
            cn(
                'flex min-w-0 items-start gap-3 rounded-xl border px-3.5 py-3 text-sm leading-5 wrap-anywhere shadow-[0_14px_34px_-30px_rgba(15,23,42,0.7)] transition-[border-color,background-color,color,box-shadow] motion-reduce:transition-none sm:items-center forced-colors:border-[CanvasText] forced-colors:bg-[Canvas] forced-colors:text-[CanvasText]',
                statusClass,
                props.class,
            )
        "
    >
        <IconTile :tone="iconTone" size="sm" class="shadow-none">
            <component
                :is="icon"
                :class="{
                    'animate-spin motion-reduce:animate-none':
                        props.status === 'loading',
                }"
            />
        </IconTile>
        <p class="min-w-0 flex-1 font-medium">
            {{ props.message }}
        </p>
    </div>
</template>
