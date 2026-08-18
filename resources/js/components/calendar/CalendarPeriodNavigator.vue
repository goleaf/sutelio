<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import { shiftCalendarAnchor } from '@/components/calendar/calendar-date';
import type {
    CalendarState,
    CalendarView,
} from '@/components/calendar/calendar-types';
import WorkspaceSegmentedButton from '@/components/shared/WorkspaceSegmentedButton.vue';
import WorkspaceSegmentedControl from '@/components/shared/WorkspaceSegmentedControl.vue';
import { Button } from '@/components/ui/button';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

const props = defineProps<{
    calendar: CalendarState;
    periodLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    navigate: [view: CalendarView, anchorDate: string];
}>();

const { copy } = useWorkspaceUi();
const viewOptions: CalendarView[] = ['month', 'week', 'agenda'];
const previousAnchor = computed(() =>
    shiftCalendarAnchor(props.calendar.view, props.calendar.anchor_date, -1),
);
const nextAnchor = computed(() =>
    shiftCalendarAnchor(props.calendar.view, props.calendar.anchor_date, 1),
);

function changeView(view: CalendarView): void {
    emit('navigate', view, props.calendar.anchor_date);
}
</script>

<template>
    <section
        class="flex flex-col gap-4 border-b border-border/70 px-1 pb-5 lg:flex-row lg:items-center lg:justify-between"
        :aria-busy="processing"
        aria-labelledby="calendar-period-heading"
    >
        <div class="flex flex-wrap items-center gap-2">
            <WorkspaceSegmentedControl :label="copy.common.filters">
                <WorkspaceSegmentedButton
                    v-for="option in viewOptions"
                    :key="option"
                    role="tab"
                    :aria-selected="calendar.view === option"
                    :active="calendar.view === option"
                    class="min-h-12 text-base pointer-coarse:min-h-13"
                    :disabled="processing"
                    @click="changeView(option)"
                >
                    {{ copy.calendar[option] }}
                </WorkspaceSegmentedButton>
            </WorkspaceSegmentedControl>

            <Button
                variant="outline"
                size="lg"
                class="min-h-12 cursor-pointer rounded-xl focus-visible:ring-orange-500 motion-reduce:transition-none pointer-coarse:min-h-13"
                :disabled="processing"
                @click="emit('navigate', calendar.view, calendar.today_date)"
            >
                {{ copy.calendar.go_today }}
            </Button>
        </div>

        <div class="flex items-center justify-between gap-2 sm:justify-end">
            <Button
                variant="ghost"
                size="icon-lg"
                class="min-h-12 min-w-12 cursor-pointer rounded-xl focus-visible:ring-orange-500 motion-reduce:transition-none pointer-coarse:min-h-13 pointer-coarse:min-w-13"
                :aria-label="copy.calendar.previous_period"
                :disabled="processing"
                @click="emit('navigate', calendar.view, previousAnchor)"
            >
                <ChevronLeft class="size-5" aria-hidden="true" />
            </Button>

            <div class="min-w-0 flex-1 text-center sm:min-w-64 sm:flex-none">
                <p
                    class="text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                >
                    {{ copy.calendar.planning_period }}
                </p>
                <h2
                    id="calendar-period-heading"
                    class="mt-1 text-base font-semibold wrap-anywhere capitalize"
                >
                    {{ periodLabel }}
                </h2>
            </div>

            <Button
                variant="ghost"
                size="icon-lg"
                class="min-h-12 min-w-12 cursor-pointer rounded-xl focus-visible:ring-orange-500 motion-reduce:transition-none pointer-coarse:min-h-13 pointer-coarse:min-w-13"
                :aria-label="copy.calendar.next_period"
                :disabled="processing"
                @click="emit('navigate', calendar.view, nextAnchor)"
            >
                <ChevronRight class="size-5" aria-hidden="true" />
            </Button>
        </div>

        <p class="sr-only" role="status" aria-live="polite">
            {{ processing ? copy.calendar.loading_period : '' }}
        </p>
    </section>
</template>
