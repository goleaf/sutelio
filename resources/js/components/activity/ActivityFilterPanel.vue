<script setup lang="ts">
import { CalendarClock, Filter, RotateCcw, UsersRound } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
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
                class="-mx-1 mb-3 flex gap-2 overflow-x-auto px-1 pb-1"
                role="group"
                :aria-label="copy.activity.category_label"
            >
                <button
                    v-for="category in categoryOptions"
                    :key="category.value"
                    type="button"
                    :aria-pressed="filters.category === category.value"
                    :disabled="processing"
                    class="min-h-11 shrink-0 rounded-xl border px-3.5 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-orange-500/45 disabled:opacity-50 motion-reduce:transition-none"
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

            <Sheet v-model:open="mobileOpen">
                <SheetTrigger :as-child="true">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-11 w-full justify-between rounded-xl motion-reduce:transition-none"
                        aria-describedby="activity-mobile-filter-status"
                    >
                        <span class="flex items-center gap-2">
                            <Filter class="size-4" aria-hidden="true" />
                            {{ copy.activity.filters_title }}
                        </span>
                        <span
                            v-if="hasActivityFilters(filters)"
                            class="size-2 rounded-full bg-orange-500"
                            aria-hidden="true"
                        />
                    </Button>
                </SheetTrigger>
                <span id="activity-mobile-filter-status" class="sr-only">
                    {{
                        hasActivityFilters(filters)
                            ? copy.activity.active_filters_status
                            : copy.activity.no_active_filters_status
                    }}
                </span>
                <SheetContent
                    side="bottom"
                    class="max-h-[92vh] overflow-y-auto rounded-t-feature"
                >
                    <SheetHeader>
                        <SheetTitle>{{
                            copy.activity.filters_title
                        }}</SheetTitle>
                        <SheetDescription>
                            {{ copy.activity.filters_description }}
                        </SheetDescription>
                    </SheetHeader>

                    <div class="grid gap-6 px-4 pb-7">
                        <div class="grid gap-4">
                            <div class="grid gap-2 text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <UsersRound
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    {{ copy.activity.contributor_label }}
                                </span>
                                <Select
                                    v-model="actorModel"
                                    :disabled="processing"
                                >
                                    <SelectTrigger
                                        class="w-full"
                                        :aria-label="
                                            copy.activity.contributor_label
                                        "
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{
                                            copy.activity.all_contributors
                                        }}</SelectItem>
                                        <SelectItem
                                            v-for="contributor in contributors"
                                            :key="contributor.id"
                                            :value="contributor.id"
                                        >
                                            {{ contributor.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid gap-2 text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <CalendarClock
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    {{ copy.activity.period_label }}
                                </span>
                                <Select
                                    v-model="periodModel"
                                    :disabled="processing"
                                >
                                    <SelectTrigger
                                        class="w-full"
                                        :aria-label="copy.activity.period_label"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{
                                            copy.activity.period_all
                                        }}</SelectItem>
                                        <SelectItem value="7d">{{
                                            copy.activity.period_7d
                                        }}</SelectItem>
                                        <SelectItem value="30d">{{
                                            copy.activity.period_30d
                                        }}</SelectItem>
                                        <SelectItem value="90d">{{
                                            copy.activity.period_90d
                                        }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            class="motion-reduce:transition-none"
                            :disabled="
                                processing || !hasActivityFilters(filters)
                            "
                            @click="clearFilters"
                        >
                            <RotateCcw class="size-4" aria-hidden="true" />
                            {{ copy.activity.clear_filters }}
                        </Button>
                    </div>
                </SheetContent>
            </Sheet>
        </div>

        <aside
            class="hidden min-w-0 overflow-hidden rounded-panel border border-border/80 bg-card p-4 shadow-panel lg:sticky lg:top-6 lg:block"
            :aria-label="copy.activity.filters_title"
        >
            <div class="border-b border-border/70 px-1 pb-4">
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <Filter class="size-4 text-orange-600" aria-hidden="true" />
                    {{ copy.activity.filters_title }}
                </div>
                <p class="mt-1.5 text-xs leading-5 text-muted-foreground">
                    {{ copy.activity.filters_description }}
                </p>
            </div>

            <div class="space-y-6 pt-5">
                <div class="space-y-2">
                    <p
                        class="px-1 text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase"
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
                            class="flex min-h-11 w-full items-center rounded-xl px-3 text-left text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-orange-500/45 disabled:opacity-50 motion-reduce:transition-none"
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

                <div class="grid gap-2 px-1 text-sm font-medium">
                    <span class="flex items-center gap-2">
                        <UsersRound
                            class="size-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        {{ copy.activity.contributor_label }}
                    </span>
                    <Select v-model="actorModel" :disabled="processing">
                        <SelectTrigger
                            class="w-full"
                            :aria-label="copy.activity.contributor_label"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                copy.activity.all_contributors
                            }}</SelectItem>
                            <SelectItem
                                v-for="contributor in contributors"
                                :key="contributor.id"
                                :value="contributor.id"
                            >
                                {{ contributor.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2 px-1 text-sm font-medium">
                    <span class="flex items-center gap-2">
                        <CalendarClock
                            class="size-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        {{ copy.activity.period_label }}
                    </span>
                    <Select v-model="periodModel" :disabled="processing">
                        <SelectTrigger
                            class="w-full"
                            :aria-label="copy.activity.period_label"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{
                                copy.activity.period_all
                            }}</SelectItem>
                            <SelectItem value="7d">{{
                                copy.activity.period_7d
                            }}</SelectItem>
                            <SelectItem value="30d">{{
                                copy.activity.period_30d
                            }}</SelectItem>
                            <SelectItem value="90d">{{
                                copy.activity.period_90d
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Button
                    type="button"
                    variant="ghost"
                    class="w-full justify-start motion-reduce:transition-none"
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
