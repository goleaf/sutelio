<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, CheckCircle2 } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import type { CalendarTodo } from '@/components/calendar/calendar-types';
import ColorSwatch from '@/components/shared/ColorSwatch.vue';
import { cn } from '@/lib/utils';
import { show as todoShow } from '@/routes/todos';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        density?: 'comfortable' | 'compact' | 'dense';
        secondary?: string | null;
        showArrow?: boolean;
        todo: CalendarTodo;
    }>(),
    {
        density: 'comfortable',
        secondary: null,
        showArrow: false,
    },
);

const containerClass = computed(
    () =>
        ({
            comfortable: 'gap-3 rounded-xl px-3 py-2.5',
            compact: 'gap-2 rounded-xl px-2.5 py-2',
            dense: 'gap-2 rounded-lg px-2 py-1.5',
        })[props.density],
);

const titleClass = computed(
    () =>
        ({
            comfortable: 'text-sm font-semibold',
            compact: 'text-xs leading-5 font-semibold',
            dense: 'text-[0.7rem] font-medium',
        })[props.density],
);

const swatchSize = computed<'sm' | 'md'>(() =>
    props.density === 'comfortable' ? 'md' : 'sm',
);
</script>

<template>
    <Link
        data-slot="calendar-task-item"
        :href="todoShow(props.todo)"
        prefetch
        :aria-label="props.todo.title"
        :class="
            cn(
                'group flex min-h-11 max-w-full min-w-0 cursor-pointer items-center overflow-hidden border border-border/80 bg-card transition-colors hover:border-orange-500/30 hover:bg-orange-500/[0.035] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none',
                containerClass,
                props.class,
            )
        "
    >
        <ColorSwatch
            :color="props.todo.priority_definition?.color"
            :size="swatchSize"
            :emphasized="props.density === 'comfortable'"
        />

        <span class="min-w-0 flex-1">
            <span :class="cn('block truncate', titleClass)">
                {{ props.todo.title }}
            </span>
            <span
                v-if="props.secondary"
                class="mt-0.5 block truncate text-xs text-muted-foreground"
            >
                {{ props.secondary }}
            </span>
        </span>

        <CheckCircle2
            v-if="props.todo.is_completed"
            :class="
                props.density === 'comfortable'
                    ? 'size-4 shrink-0 text-emerald-600'
                    : 'size-3.5 shrink-0 text-emerald-600'
            "
            aria-hidden="true"
        />
        <ArrowUpRight
            v-if="props.showArrow"
            class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none"
            aria-hidden="true"
        />
    </Link>
</template>
