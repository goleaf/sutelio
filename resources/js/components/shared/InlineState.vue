<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type InlineStateStatus =
    'empty' | 'loading' | 'error' | 'success' | 'information';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        status?: InlineStateStatus;
        class?: HTMLAttributes['class'];
    }>(),
    {
        title: undefined,
        description: undefined,
        status: 'empty',
    },
);

const role = computed(() => {
    if (props.status === 'error') {
        return 'alert';
    }

    return props.status === 'loading' || props.status === 'success'
        ? 'status'
        : undefined;
});

const statusClass = computed(
    () =>
        ({
            empty: 'border-dashed border-border/80 text-muted-foreground',
            loading: 'border-border/80 bg-muted/20 text-muted-foreground',
            error: 'border-status-destructive-border bg-status-destructive-surface text-status-destructive-text',
            success:
                'border-status-success-border bg-status-success-surface text-status-success-text',
            information:
                'border-status-information-border bg-status-information-surface text-status-information-text',
        })[props.status],
);
</script>

<template>
    <div
        data-slot="inline-state"
        :data-status="props.status"
        :role="role"
        :aria-live="role === 'status' ? 'polite' : undefined"
        :aria-busy="props.status === 'loading' ? 'true' : undefined"
        :class="
            cn(
                'flex min-h-24 flex-col items-center justify-center gap-2 rounded-xl border px-4 py-6 text-center text-sm',
                statusClass,
                props.class,
            )
        "
    >
        <div
            v-if="$slots.icon"
            data-slot="inline-state-icon"
            aria-hidden="true"
        >
            <slot name="icon" />
        </div>
        <p v-if="props.title" class="font-medium text-foreground">
            {{ props.title }}
        </p>
        <p v-if="props.description" class="max-w-md leading-6">
            {{ props.description }}
        </p>
        <slot />
    </div>
</template>
