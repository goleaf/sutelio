<script setup lang="ts">
import { CircleDotDashed, Flag, Palette, Tags } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import type { WorkspaceTaxonomySection } from '@/components/workspace/workspace-stewardship';
import { useUi } from '@/composables/useUi';
import { cn } from '@/lib/utils';

defineProps<{
    activeSection: WorkspaceTaxonomySection;
    counts: Record<WorkspaceTaxonomySection, number>;
}>();

const emit = defineEmits<{
    'update:activeSection': [section: WorkspaceTaxonomySection];
}>();

const { formatNumber, t } = useUi();

const items = computed<
    {
        section: WorkspaceTaxonomySection;
        icon: LucideIcon;
        tone: string;
    }[]
>(() => [
    {
        section: 'statuses',
        icon: CircleDotDashed,
        tone: 'text-emerald-700',
    },
    {
        section: 'priorities',
        icon: Flag,
        tone: 'text-amber-700',
    },
    {
        section: 'labels',
        icon: Palette,
        tone: 'text-sky-700',
    },
    {
        section: 'tags',
        icon: Tags,
        tone: 'text-violet-700',
    },
]);
</script>

<template>
    <div
        class="grid grid-cols-2 gap-2 lg:grid-cols-4"
        role="group"
        :aria-label="t('workspaces.management.configuration.categories_label')"
    >
        <button
            v-for="item in items"
            :key="item.section"
            type="button"
            class="group flex min-h-14 items-center gap-3 rounded-xl border border-border/80 bg-card px-3 py-2.5 text-left transition-[border-color,background-color,box-shadow] hover:border-orange-500/25 hover:bg-orange-500/[0.03] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none"
            :class="
                activeSection === item.section
                    ? 'border-orange-500/30 bg-orange-500/[0.05] shadow-sm'
                    : ''
            "
            :aria-pressed="activeSection === item.section"
            @click="emit('update:activeSection', item.section)"
        >
            <span
                :class="
                    cn(
                        'flex size-9 shrink-0 items-center justify-center rounded-xl bg-muted/70',
                        item.tone,
                    )
                "
            >
                <component :is="item.icon" class="size-4" aria-hidden="true" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-medium break-words">
                    {{
                        t(
                            `workspaces.management.configuration.${item.section}.title`,
                        )
                    }}
                </span>
                <span class="mt-0.5 block text-xs text-muted-foreground">
                    {{ formatNumber(counts[item.section]) }}
                </span>
            </span>
        </button>
    </div>
</template>
