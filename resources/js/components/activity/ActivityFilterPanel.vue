<script setup lang="ts">
import { Filter, RotateCcw } from '@lucide/vue';
import { computed, ref } from 'vue';
import ActivityFilterFields from '@/components/activity/ActivityFilterFields.vue';
import FilterSheet from '@/components/shared/FilterSheet.vue';
import IconTile from '@/components/shared/IconTile.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';
import type {
    ActivityCategory,
    ActivityContributor,
    ActivityFilters,
    ActivityPeriod,
} from './activity-types';
import { hasActivityFilters } from './activity-types';

const props = defineProps<{
    filters: ActivityFilters;
    categories: Exclude<ActivityCategory, 'all'>[];
    contributors: ActivityContributor[];
    processing: boolean;
}>();
const emit = defineEmits<{ update: [filters: ActivityFilters] }>();
const { copy } = useWorkspaceUi();
const mobileOpen = ref(false);

const categoryOptions = computed(() => [
    { value: 'all' as const, label: copy.value.activity.filter_all },
    ...props.categories.map((category) => ({
        value: category,
        label: categoryLabel(category),
    })),
]);

const actorModel = computed({
    get: () => props.filters.actor ?? 'all',
    set: (actor: string) => {
        updateFilters({ actor: actor === 'all' ? null : actor });
    },
});

const periodModel = computed({
    get: () => props.filters.period,
    set: (period: ActivityPeriod) => {
        updateFilters({ period });
    },
});

function categoryLabel(category: Exclude<ActivityCategory, 'all'>): string {
    return {
        automation: copy.value.activity.category_automation,
        changes: copy.value.activity.category_changes,
        completion: copy.value.activity.category_completion,
        creation: copy.value.activity.category_creation,
        organization: copy.value.activity.category_organization,
    }[category];
}

function updateFilters(changes: Partial<ActivityFilters>): void {
    emit('update', { ...props.filters, ...changes });
}

function clearFilters(): void {
    mobileOpen.value = false;
    emit('update', { category: 'all', actor: null, period: 'all' });
}
</script>

<template>
    <div class="contents">
        <div class="lg:hidden">
            <div
                class="-mx-1 mb-3 flex touch-pan-x gap-2 overflow-x-auto overscroll-x-contain px-1 pb-1"
                role="group"
                :aria-label="copy.activity.category_label"
            >
                <button
                    v-for="category in categoryOptions"
                    :key="category.value"
                    type="button"
                    :aria-pressed="filters.category === category.value"
                    :disabled="processing"
                    class="min-h-12 shrink-0 rounded-xl border px-3.5 text-base font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-orange-500/45 disabled:opacity-50 motion-reduce:transition-none pointer-coarse:min-h-13"
                    :class="
                        filters.category === category.value
                            ? 'border-orange-500/30 bg-orange-500/10 text-orange-800'
                            : 'border-border bg-card text-foreground/75 hover:bg-muted/60 hover:text-foreground'
                    "
                    @click="updateFilters({ category: category.value })"
                >
                    {{ category.label }}
                </button>
            </div>

            <FilterSheet
                v-model="mobileOpen"
                :title="copy.activity.filters_title"
                :description="copy.activity.filters_description"
                :clear-label="copy.activity.clear_filters"
                :clear-disabled="processing || !hasActivityFilters(filters)"
                :processing="processing"
                @clear="clearFilters"
            >
                <template #trigger>
                    <Button
                        type="button"
                        variant="outline"
                        class="min-h-12 w-full justify-between rounded-xl text-base motion-reduce:transition-none pointer-coarse:min-h-13"
                        aria-describedby="activity-mobile-filter-status"
                    >
                        <span class="flex items-center gap-2">
                            <IconTile tone="brand" size="sm">
                                <Filter />
                            </IconTile>
                            {{ copy.activity.filters_title }}
                        </span>
                        <span
                            v-if="hasActivityFilters(filters)"
                            class="size-2 rounded-full bg-orange-500"
                            aria-hidden="true"
                        />
                    </Button>
                </template>
                <template #status>
                    <span id="activity-mobile-filter-status" class="sr-only">
                        {{
                            hasActivityFilters(filters)
                                ? copy.activity.active_filters_status
                                : copy.activity.no_active_filters_status
                        }}
                    </span>
                </template>

                <div class="grid gap-6">
                    <ActivityFilterFields
                        v-model:actor="actorModel"
                        v-model:period="periodModel"
                        mode="mobile"
                        :contributors="contributors"
                        :disabled="processing"
                    />
                </div>
            </FilterSheet>
        </div>

        <aside
            class="hidden min-w-0 overflow-hidden rounded-panel border border-border/80 bg-card p-4 shadow-panel lg:sticky lg:top-6 lg:block"
            :aria-label="copy.activity.filters_title"
        >
            <div class="border-b border-border/70 px-1 pb-4">
                <LeadingIconHeading
                    tile
                    tile-tone="brand"
                    tile-size="sm"
                    content-class="gap-1"
                >
                    <template #icon>
                        <Filter />
                    </template>

                    <p class="text-base font-semibold">
                        {{ copy.activity.filters_title }}
                    </p>
                    <p class="text-[0.9375rem] leading-5 text-muted-foreground">
                        {{ copy.activity.filters_description }}
                    </p>
                </LeadingIconHeading>
            </div>

            <div class="space-y-6 pt-5">
                <div class="space-y-2">
                    <p
                        class="px-1 text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                    >
                        {{ copy.activity.category_label }}
                    </p>
                    <div
                        class="space-y-1"
                        role="group"
                        :aria-label="copy.activity.category_label"
                    >
                        <button
                            v-for="category in categoryOptions"
                            :key="category.value"
                            type="button"
                            :aria-pressed="filters.category === category.value"
                            :disabled="processing"
                            class="flex min-h-12 w-full items-center rounded-xl px-3 text-left text-base font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-orange-500/45 disabled:opacity-50 motion-reduce:transition-none pointer-coarse:min-h-13"
                            :class="
                                filters.category === category.value
                                    ? 'bg-orange-500/10 text-orange-800'
                                    : 'text-foreground/75 hover:bg-muted/60 hover:text-foreground'
                            "
                            @click="updateFilters({ category: category.value })"
                        >
                            {{ category.label }}
                        </button>
                    </div>
                </div>

                <ActivityFilterFields
                    v-model:actor="actorModel"
                    v-model:period="periodModel"
                    mode="desktop"
                    :contributors="contributors"
                    :disabled="processing"
                />

                <Button
                    type="button"
                    variant="ghost"
                    class="min-h-12 w-full justify-start motion-reduce:transition-none pointer-coarse:min-h-13"
                    :disabled="processing || !hasActivityFilters(filters)"
                    @click="clearFilters"
                >
                    <RotateCcw class="size-4" aria-hidden="true" />
                    {{ copy.activity.clear_filters }}
                </Button>
            </div>
        </aside>
    </div>
</template>
