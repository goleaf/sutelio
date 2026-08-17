<script setup lang="ts">
import { CalendarClock, UsersRound } from '@lucide/vue';
import type {
    ActivityContributor,
    ActivityPeriod,
} from '@/components/activity/activity-types';
import IconTile from '@/components/shared/IconTile.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

const props = withDefaults(
    defineProps<{
        contributors: ActivityContributor[];
        disabled?: boolean;
        mode?: 'desktop' | 'mobile';
    }>(),
    {
        disabled: false,
        mode: 'desktop',
    },
);

const actor = defineModel<string>('actor', { required: true });
const period = defineModel<ActivityPeriod>('period', { required: true });
const { copy } = useWorkspaceUi();
</script>

<template>
    <div
        data-slot="activity-filter-fields"
        :class="props.mode === 'mobile' ? 'grid gap-4' : 'contents'"
    >
        <div
            :class="[
                'grid gap-2 text-sm font-medium',
                props.mode === 'desktop' ? 'px-1' : '',
            ]"
        >
            <span class="flex items-center gap-2">
                <IconTile tone="muted" size="sm">
                    <UsersRound />
                </IconTile>
                {{ copy.activity.contributor_label }}
            </span>
            <Select v-model="actor" :disabled="props.disabled">
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
                        v-for="contributor in props.contributors"
                        :key="contributor.id"
                        :value="contributor.id"
                    >
                        {{ contributor.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div
            :class="[
                'grid gap-2 text-sm font-medium',
                props.mode === 'desktop' ? 'px-1' : '',
            ]"
        >
            <span class="flex items-center gap-2">
                <IconTile tone="muted" size="sm">
                    <CalendarClock />
                </IconTile>
                {{ copy.activity.period_label }}
            </span>
            <Select v-model="period" :disabled="props.disabled">
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
</template>
