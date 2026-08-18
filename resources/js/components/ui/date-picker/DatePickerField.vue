<script setup lang="ts">
import {
    parseDateTime,
    today,
    type DateValue,
} from '@internationalized/date';
import { CalendarDays, Check, ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import {
    CalendarRoot,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerClose,
    DatePickerContent,
    DatePickerField as RekaDatePickerField,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerInput,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useUi } from '@/composables/useUi';
import {
    parseDatePickerValue,
    replaceDatePickerDate,
    serializeDatePickerValue,
    type DatePickerGranularity,
} from './date-picker-value';

const props = withDefaults(
    defineProps<{
        id: string;
        label: string;
        describedBy?: string;
        disabled?: boolean;
        granularity?: DatePickerGranularity;
        invalid?: boolean;
    }>(),
    {
        describedBy: undefined,
        disabled: false,
        granularity: 'day',
        invalid: false,
    },
);

const value = defineModel<string>({ required: true });
const page = usePage();
const { locale, t, timezone } = useUi();
const rootElement = ref<HTMLElement | null>(null);
const portalTarget = ref<HTMLElement | string>('body');

const dateValue = computed(() =>
    parseDatePickerValue(value.value, props.granularity),
);
const defaultPlaceholder = computed<DateValue>(() => {
    const currentDate = today(timezone.value);

    return props.granularity === 'day'
        ? currentDate
        : parseDateTime(`${currentDate.toString()}T09:00`);
});
const weekStartsOn = computed<0 | 1>(() => {
    const defaultWeekStart = page.props.localization.options.find(
        (option) => option.code === page.props.localization.current,
    )?.default_week_start;

    return (page.props.preferences?.week_start ?? defaultWeekStart) === 'sunday'
        ? 0
        : 1;
});
const hourCycle = computed<12 | 24>(() =>
    page.props.preferences?.time_format === 'h:i A' ? 12 : 24,
);

onMounted(() => {
    portalTarget.value =
        rootElement.value?.closest<HTMLElement>('[role="dialog"]') ??
        document.body;
});

function updateValue(nextValue: DateValue | undefined): void {
    value.value = nextValue
        ? serializeDatePickerValue(nextValue, props.granularity)
        : '';
}

function updateCalendarValue(
    nextValue: DateValue | DateValue[] | undefined,
): void {
    updateValue(Array.isArray(nextValue) ? nextValue[0] : nextValue);
}

function selectToday(): void {
    const currentDate = today(timezone.value);
    updateValue(
        replaceDatePickerDate(
            dateValue.value,
            currentDate,
            props.granularity,
        ),
    );
}
</script>

<template>
    <div ref="rootElement" class="min-w-0">
        <DatePickerRoot
            :id="id"
            :model-value="dateValue"
            :default-placeholder="defaultPlaceholder"
            :locale="locale"
            :week-starts-on="weekStartsOn"
            :hour-cycle="hourCycle"
            :granularity="granularity"
            :disabled="disabled"
            :close-on-select="granularity === 'day'"
            fixed-weeks
            @update:model-value="updateValue"
        >
            <RekaDatePickerField v-slot="{ segments }">
                <div
                    data-slot="date-picker-field"
                    role="group"
                    :aria-label="label"
                    :aria-invalid="invalid"
                    :aria-describedby="describedBy"
                    class="flex min-h-12 w-full min-w-0 items-center rounded-xl border border-input bg-background shadow-xs transition-[border-color,box-shadow] focus-within:border-orange-500 focus-within:ring-[3px] focus-within:ring-orange-500/20 has-aria-invalid:border-destructive has-aria-invalid:ring-destructive/20 motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border-[CanvasText]"
                >
                    <div
                        class="flex min-w-0 flex-1 items-center overflow-x-auto px-3.5 py-2 text-base whitespace-nowrap [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        <DatePickerInput
                            v-for="segment in segments"
                            :key="segment.part"
                            :part="segment.part"
                            class="rounded px-0.5 text-foreground outline-none data-[placeholder]:text-muted-foreground focus:bg-orange-100 focus:text-foreground forced-colors:focus:bg-[Highlight] forced-colors:focus:text-[HighlightText]"
                        >
                            {{ segment.value }}
                        </DatePickerInput>
                    </div>

                    <Button
                        v-if="dateValue"
                        type="button"
                        variant="link"
                        size="icon-lg"
                        :aria-label="t('date_picker.clear')"
                        :disabled="disabled"
                        class="shrink-0 rounded-lg text-muted-foreground no-underline hover:bg-muted hover:text-foreground hover:no-underline pointer-coarse:size-12 forced-colors:border forced-colors:border-transparent"
                        @click="updateValue(undefined)"
                    >
                        <X class="size-4" aria-hidden="true" />
                    </Button>

                    <DatePickerTrigger as-child>
                        <Button
                            type="button"
                            variant="link"
                            size="icon-lg"
                            :aria-label="t('date_picker.open')"
                            class="shrink-0 rounded-lg text-orange-700 no-underline hover:bg-orange-50 hover:no-underline pointer-coarse:size-12 forced-colors:border forced-colors:border-transparent"
                        >
                            <CalendarDays class="size-5" aria-hidden="true" />
                        </Button>
                    </DatePickerTrigger>
                </div>
            </RekaDatePickerField>

            <DatePickerContent
                data-slot="date-picker-content"
                :portal="{ to: portalTarget }"
                side="bottom"
                align="start"
                :side-offset="8"
                :collision-padding="8"
                class="z-70 max-h-[min(36rem,var(--reka-popover-content-available-height))] w-[min(22rem,calc(100dvw-0.5rem))] origin-[var(--reka-popover-content-transform-origin)] overflow-y-auto rounded-2xl border border-border/80 bg-popover p-2.5 text-popover-foreground shadow-xl outline-none data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 sm:p-4 motion-reduce:data-[state=closed]:animate-none motion-reduce:data-[state=open]:animate-none forced-colors:border-[CanvasText]"
            >
                <CalendarRoot
                    v-slot="{ grid, weekDays }"
                    :model-value="dateValue"
                    :default-placeholder="defaultPlaceholder"
                    :locale="locale"
                    :week-starts-on="weekStartsOn"
                    :calendar-label="t('date_picker.calendar_label')"
                    fixed-weeks
                    initial-focus
                    @update:model-value="updateCalendarValue"
                >
                    <DatePickerHeader
                        class="flex min-h-12 items-center justify-between gap-2"
                    >
                        <DatePickerPrev as-child>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon-lg"
                                :aria-label="t('date_picker.previous_month')"
                                class="pointer-coarse:size-12 forced-colors:border-[ButtonBorder]"
                            >
                                <ChevronLeft class="size-5" aria-hidden="true" />
                            </Button>
                        </DatePickerPrev>
                        <DatePickerHeading
                            class="text-base font-semibold text-foreground"
                        />
                        <DatePickerNext as-child>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon-lg"
                                :aria-label="t('date_picker.next_month')"
                                class="pointer-coarse:size-12 forced-colors:border-[ButtonBorder]"
                            >
                                <ChevronRight class="size-5" aria-hidden="true" />
                            </Button>
                        </DatePickerNext>
                    </DatePickerHeader>

                    <DatePickerGrid
                        v-for="month in grid"
                        :key="month.value.toString()"
                        class="mt-2 w-full table-fixed border-collapse"
                    >
                        <DatePickerGridHead>
                            <DatePickerGridRow>
                                <DatePickerHeadCell
                                    v-for="weekDay in weekDays"
                                    :key="weekDay"
                                    class="h-9 text-center text-sm font-medium text-muted-foreground"
                                >
                                    {{ weekDay }}
                                </DatePickerHeadCell>
                            </DatePickerGridRow>
                        </DatePickerGridHead>
                        <DatePickerGridBody>
                            <DatePickerGridRow
                                v-for="(weekDates, weekIndex) in month.rows"
                                :key="weekIndex"
                            >
                                <DatePickerCell
                                    v-for="calendarDate in weekDates"
                                    :key="calendarDate.toString()"
                                    :date="calendarDate"
                                    class="p-0.5"
                                >
                                    <DatePickerCellTrigger
                                        v-slot="{
                                            dayValue,
                                            selected,
                                            today: isToday,
                                        }"
                                        as="button"
                                        type="button"
                                        :day="calendarDate"
                                        :month="month.value"
                                        class="relative flex min-h-11 w-full min-w-0 cursor-pointer items-center justify-center rounded-xl text-base font-medium text-foreground outline-none transition-[background-color,color,box-shadow] hover:bg-orange-50 focus-visible:ring-3 focus-visible:ring-orange-500/35 data-[disabled]:pointer-events-none data-[disabled]:opacity-35 data-[outside-view]:opacity-35 data-[selected]:bg-orange-600 data-[selected]:text-white data-[today]:ring-2 data-[today]:ring-orange-500/45 motion-reduce:transition-none pointer-coarse:min-h-12 forced-colors:border forced-colors:border-transparent forced-colors:data-[selected]:bg-[Highlight] forced-colors:data-[selected]:text-[HighlightText]"
                                    >
                                        <span>{{ dayValue }}</span>
                                        <Check
                                            v-if="selected"
                                            class="absolute right-0.5 bottom-0.5 size-3"
                                            aria-hidden="true"
                                        />
                                        <span v-if="isToday" class="sr-only">
                                            {{ t('date_picker.today') }}
                                        </span>
                                    </DatePickerCellTrigger>
                                </DatePickerCell>
                            </DatePickerGridRow>
                        </DatePickerGridBody>
                    </DatePickerGrid>
                </CalendarRoot>

                <div
                    class="mt-3 flex items-center justify-between gap-2 border-t border-border/70 pt-3"
                >
                    <DatePickerClose as-child>
                        <button
                            type="button"
                            class="min-h-12 rounded-xl px-3.5 py-2 text-base font-semibold text-orange-700 outline-none transition-colors hover:bg-orange-50 focus-visible:ring-3 focus-visible:ring-orange-500/30 motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border forced-colors:border-[ButtonBorder]"
                            @click="selectToday"
                        >
                            {{ t('date_picker.today') }}
                        </button>
                    </DatePickerClose>
                    <DatePickerClose
                        v-if="granularity === 'minute'"
                        type="button"
                        class="min-h-12 rounded-xl bg-orange-600 px-4 py-2 text-base font-semibold text-white outline-none transition-colors hover:bg-orange-700 focus-visible:ring-3 focus-visible:ring-orange-500/35 motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border forced-colors:border-[ButtonBorder]"
                    >
                        {{ t('date_picker.done') }}
                    </DatePickerClose>
                </div>
            </DatePickerContent>
        </DatePickerRoot>
    </div>
</template>
