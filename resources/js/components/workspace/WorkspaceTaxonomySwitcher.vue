<script setup lang="ts">
import { CircleDotDashed, Flag, Palette, Tags } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import type { IconTileTone } from '@/components/shared/IconTile.vue';
import type { WorkspaceTaxonomySection } from '@/components/workspace/workspace-stewardship';
import { useUi } from '@/composables/useUi';

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
        tone: IconTileTone;
    }[]
>(() => [
    {
        section: 'statuses',
        icon: CircleDotDashed,
        tone: 'success',
    },
    {
        section: 'priorities',
        icon: Flag,
        tone: 'warning',
    },
    {
        section: 'labels',
        icon: Palette,
        tone: 'information',
    },
    {
        section: 'tags',
        icon: Tags,
        tone: 'cobalt',
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
            class="group flex min-h-12 items-center gap-3 rounded-xl border border-border/80 bg-card px-3 py-2.5 text-left transition-[border-color,background-color,box-shadow] hover:border-orange-500/25 hover:bg-orange-500/[0.03] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none motion-reduce:transition-none pointer-coarse:min-h-13"
            :class="
                activeSection === item.section
                    ? 'border-orange-500/30 bg-orange-500/[0.05] shadow-sm'
                    : ''
            "
            :aria-pressed="activeSection === item.section"
            @click="emit('update:activeSection', item.section)"
        >
            <IconTile :tone="item.tone" size="sm">
                <component :is="item.icon" />
            </IconTile>
            <span class="min-w-0 flex-1">
                <span class="block text-base leading-6 font-medium break-words">
                    {{
                        t(
                            `workspaces.management.configuration.${item.section}.title`,
                        )
                    }}
                </span>
                <span
                    class="mt-0.5 block text-[0.9375rem] leading-5 text-muted-foreground"
                >
                    {{ formatNumber(counts[item.section]) }}
                </span>
            </span>
        </button>
    </div>
</template>
