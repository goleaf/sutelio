<script setup lang="ts">
import {
    AlertTriangle,
    ArrowRight,
    LoaderCircle,
    PackageOpen,
} from '@lucide/vue';
import IconTile from '@/components/shared/IconTile.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

type EmptyStateStatus = 'empty' | 'loading' | 'error';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        actionLabel?: string;
        compact?: boolean;
        status?: EmptyStateStatus;
    }>(),
    {
        description: undefined,
        actionLabel: undefined,
        compact: false,
        status: 'empty',
    },
);

const emit = defineEmits<{ action: [] }>();
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center overflow-hidden px-6 text-center"
        :class="compact ? 'min-h-56 py-10' : 'min-h-80 py-16'"
        :role="
            status === 'error'
                ? 'alert'
                : status === 'loading'
                  ? 'status'
                  : undefined
        "
        :aria-busy="status === 'loading' ? 'true' : undefined"
    >
        <span
            class="absolute -right-12 -bottom-20 size-52 rounded-full border-[28px]"
            :class="
                status === 'error'
                    ? 'border-red-500/[0.035]'
                    : 'border-orange-500/[0.035]'
            "
            aria-hidden="true"
        />
        <IconTile
            size="lg"
            class="relative mb-5"
            :tone="
                status === 'error'
                    ? 'destructive'
                    : status === 'loading'
                      ? 'information'
                      : 'brand'
            "
        >
            <slot name="icon">
                <LoaderCircle
                    v-if="status === 'loading'"
                    class="size-7 animate-spin motion-reduce:animate-none"
                    aria-hidden="true"
                />
                <AlertTriangle
                    v-else-if="status === 'error'"
                    class="size-7"
                    aria-hidden="true"
                />
                <PackageOpen v-else aria-hidden="true" />
            </slot>
        </IconTile>
        <h3 class="relative text-lg font-semibold tracking-tight">
            {{ title }}
        </h3>
        <div
            v-if="status === 'loading'"
            class="relative mt-3 flex w-full max-w-xs flex-col items-center gap-2"
            aria-hidden="true"
        >
            <Skeleton class="h-3 w-4/5 rounded-full" />
            <Skeleton class="h-3 w-3/5 rounded-full" />
        </div>
        <p
            v-if="description"
            class="relative max-w-md text-[0.9375rem] leading-6 text-muted-foreground"
            :class="status === 'loading' ? 'sr-only' : 'mt-2'"
        >
            {{ description }}
        </p>
        <Button
            v-if="actionLabel && status !== 'loading'"
            size="lg"
            class="relative mt-5"
            @click="emit('action')"
        >
            {{ actionLabel }}
            <ArrowRight class="size-4" aria-hidden="true" />
        </Button>
    </div>
</template>
