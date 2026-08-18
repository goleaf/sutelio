<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    CalendarRange,
    Clock3,
    Columns3,
    FolderKanban,
    Globe2,
    Languages,
    LayoutDashboard,
    List,
    ListChecks,
    PanelsTopLeft,
    Sun,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LanguageFlag from '@/components/localization/LanguageFlag.vue';
import type {
    OnboardingCopy,
    OnboardingPreferences,
} from '@/components/onboarding/onboarding-types';
import OnboardingFieldLabel from '@/components/onboarding/OnboardingFieldLabel.vue';
import OnboardingIcon from '@/components/onboarding/OnboardingIcon.vue';
import TimezoneCombobox from '@/components/preferences/TimezoneCombobox.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import StatusNotice from '@/components/shared/StatusNotice.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLanguagePreference } from '@/composables/useLanguagePreference';
import { useUi } from '@/composables/useUi';
import { formatDateValue } from '@/lib/formatters';
import { detectBrowserTimezone } from '@/lib/timezone';
import type { SupportedLanguage } from '@/types';
import type { TimeZoneGroup } from '@/types/timezone';

const props = defineProps<{
    copy: OnboardingCopy['preferences'];
    draft: OnboardingPreferences;
    timezoneGroups: TimeZoneGroup[];
    errors: Record<string, string>;
    processing: boolean;
}>();
const page = usePage();
const { form: languageForm, saveLanguage } = useLanguagePreference();
const { t } = useUi();

const emit = defineEmits<{
    'update:draft': [draft: OnboardingPreferences];
}>();

function preferenceModel<K extends keyof OnboardingPreferences>(key: K) {
    return computed<OnboardingPreferences[K]>({
        get: () => props.draft[key],
        set: (value) => emit('update:draft', { ...props.draft, [key]: value }),
    });
}

const language = preferenceModel('language');
const timezone = preferenceModel('timezone');
const dateFormat = preferenceModel('date_format');
const timeFormat = preferenceModel('time_format');
const defaultView = preferenceModel('default_view');
const startPage = preferenceModel('start_page');
const weekStart = preferenceModel('week_start');
const detectedTimezone = ref<string | null>(null);

function handleLanguageChange(value: unknown): void {
    if (typeof value !== 'string') {
        return;
    }

    const previousWeekStart = weekStart.value;
    language.value = value as SupportedLanguage;
    const selectedLanguage = page.props.localization.options.find(
        (option) => option.code === value,
    );

    if (selectedLanguage) {
        weekStart.value = selectedLanguage.default_week_start;
    }

    saveLanguage(value, {
        onError: (currentLanguage) => {
            language.value = currentLanguage;
            weekStart.value = previousWeekStart;
        },
    });
}

onMounted(() => {
    const detected = detectBrowserTimezone();

    if (detected === timezone.value) {
        detectedTimezone.value = detected;
    }
});

const preview = computed(() =>
    formatDateValue(
        new Date(),
        { dateStyle: 'medium', timeStyle: 'short' },
        props.draft,
    ),
);

const selectedLanguage = computed(() =>
    page.props.localization.options.find(
        (option) => option.code === language.value,
    ),
);
const dateFormats: ReadonlyArray<{
    value: OnboardingPreferences['date_format'];
}> = [
    { value: 'Y-m-d' },
    { value: 'd/m/Y' },
    { value: 'm/d/Y' },
    { value: 'd.m.Y' },
];
const timeFormats: ReadonlyArray<{
    value: OnboardingPreferences['time_format'];
}> = [{ value: 'H:i' }, { value: 'h:i A' }];
const viewOptions = computed(() => [
    { value: 'list' as const, label: props.copy.views.list, icon: List },
    {
        value: 'board' as const,
        label: props.copy.views.board,
        icon: Columns3,
    },
    {
        value: 'calendar' as const,
        label: props.copy.views.calendar,
        icon: CalendarDays,
    },
]);
const startPageOptions = computed(() => [
    {
        value: 'dashboard' as const,
        label: props.copy.start_pages.dashboard,
        icon: LayoutDashboard,
    },
    {
        value: 'tasks' as const,
        label: props.copy.start_pages.tasks,
        icon: ListChecks,
    },
    {
        value: 'projects' as const,
        label: props.copy.start_pages.projects,
        icon: FolderKanban,
    },
    {
        value: 'calendar' as const,
        label: props.copy.start_pages.calendar,
        icon: CalendarDays,
    },
]);
const weekStartOptions = computed(() => [
    {
        value: 'sunday' as const,
        label: props.copy.week_starts.sunday,
        icon: Sun,
    },
    {
        value: 'monday' as const,
        label: props.copy.week_starts.monday,
        icon: CalendarRange,
    },
]);
const selectedViewIcon = computed(
    () =>
        viewOptions.value.find((option) => option.value === defaultView.value)
            ?.icon ?? List,
);
const selectedStartPageIcon = computed(
    () =>
        startPageOptions.value.find(
            (option) => option.value === startPage.value,
        )?.icon ?? LayoutDashboard,
);
const selectedWeekStartIcon = computed(
    () =>
        weekStartOptions.value.find(
            (option) => option.value === weekStart.value,
        )?.icon ?? CalendarRange,
);
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(15rem,0.48fr)]">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <OnboardingFieldLabel html-for="language" :icon="Languages">
                    {{ copy.language }}
                </OnboardingFieldLabel>
                <Select
                    :model-value="language"
                    :disabled="processing || languageForm.processing"
                    @update:model-value="handleLanguageChange"
                >
                    <SelectTrigger
                        id="language"
                        :aria-invalid="
                            Boolean(
                                errors.language || languageForm.errors.language,
                            )
                        "
                    >
                        <OnboardingIcon>
                            <LanguageFlag
                                v-if="selectedLanguage"
                                :src="selectedLanguage.flag_url"
                            />
                        </OnboardingIcon>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in page.props.localization.options"
                            :key="option.code"
                            :value="option.code"
                        >
                            <span class="flex items-center gap-2.5">
                                <OnboardingIcon>
                                    <LanguageFlag :src="option.flag_url" />
                                </OnboardingIcon>
                                <span>{{ option.localized_name }}</span>
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    :message="languageForm.errors.language ?? errors.language"
                />
                <StatusNotice
                    v-if="languageForm.processing"
                    :message="t('localization.saving')"
                    status="loading"
                />
            </div>
            <div class="space-y-2">
                <OnboardingFieldLabel html-for="timezone" :icon="Globe2">
                    {{ copy.timezone }}
                </OnboardingFieldLabel>
                <TimezoneCombobox
                    v-model="timezone"
                    :groups="timezoneGroups"
                    :disabled="processing"
                    :invalid="Boolean(errors.timezone)"
                />
                <InputError :message="errors.timezone" />
                <StatusNotice
                    v-if="detectedTimezone"
                    :message="
                        t('timezones.detected', {
                            timezone: detectedTimezone,
                        })
                    "
                    status="information"
                />
            </div>
            <div class="space-y-2">
                <OnboardingFieldLabel
                    html-for="date_format"
                    :icon="CalendarDays"
                >
                    {{ copy.date_format }}
                </OnboardingFieldLabel>
                <Select v-model="dateFormat" :disabled="processing">
                    <SelectTrigger
                        id="date_format"
                        :aria-invalid="Boolean(errors.date_format)"
                    >
                        <OnboardingIcon :icon="CalendarDays" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in dateFormats"
                            :key="option.value"
                            :value="option.value"
                        >
                            <OnboardingIcon :icon="CalendarDays" />
                            {{ option.value }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.date_format" />
            </div>
            <div class="space-y-2">
                <OnboardingFieldLabel html-for="time_format" :icon="Clock3">
                    {{ copy.time_format }}
                </OnboardingFieldLabel>
                <Select v-model="timeFormat" :disabled="processing">
                    <SelectTrigger
                        id="time_format"
                        :aria-invalid="Boolean(errors.time_format)"
                    >
                        <OnboardingIcon :icon="Clock3" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in timeFormats"
                            :key="option.value"
                            :value="option.value"
                        >
                            <OnboardingIcon :icon="Clock3" />
                            {{ option.value }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.time_format" />
            </div>
            <div class="space-y-2">
                <OnboardingFieldLabel
                    html-for="default_view"
                    :icon="PanelsTopLeft"
                >
                    {{ copy.default_view }}
                </OnboardingFieldLabel>
                <Select v-model="defaultView" :disabled="processing">
                    <SelectTrigger
                        id="default_view"
                        :aria-invalid="Boolean(errors.default_view)"
                    >
                        <OnboardingIcon :icon="selectedViewIcon" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in viewOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            <OnboardingIcon :icon="option.icon" />
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.default_view" />
            </div>
            <div class="space-y-2">
                <OnboardingFieldLabel
                    html-for="start_page"
                    :icon="LayoutDashboard"
                >
                    {{ copy.start_page }}
                </OnboardingFieldLabel>
                <Select v-model="startPage" :disabled="processing">
                    <SelectTrigger
                        id="start_page"
                        :aria-invalid="Boolean(errors.start_page)"
                    >
                        <OnboardingIcon :icon="selectedStartPageIcon" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in startPageOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            <OnboardingIcon :icon="option.icon" />
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.start_page" />
            </div>
            <div class="space-y-2 sm:col-span-2">
                <OnboardingFieldLabel
                    html-for="week_start"
                    :icon="CalendarRange"
                >
                    {{ copy.week_start }}
                </OnboardingFieldLabel>
                <Select v-model="weekStart" :disabled="processing">
                    <SelectTrigger
                        id="week_start"
                        :aria-invalid="Boolean(errors.week_start)"
                    >
                        <OnboardingIcon :icon="selectedWeekStartIcon" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in weekStartOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            <OnboardingIcon :icon="option.icon" />
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.week_start" />
            </div>
        </div>

        <aside
            class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
        >
            <LeadingIconHeading tile tile-tone="brand" content-class="gap-1">
                <template #icon>
                    <Clock3 />
                </template>
                <h2 class="font-semibold">{{ copy.preview_title }}</h2>
                <p class="text-base leading-7 text-muted-foreground">
                    {{ copy.preview_description }}
                </p>
            </LeadingIconHeading>
            <p
                class="mt-5 text-xl font-semibold tracking-tight break-words tabular-nums"
            >
                {{ preview }}
            </p>
            <p
                class="mt-2 text-[0.9375rem] leading-6 wrap-anywhere text-muted-foreground"
            >
                {{ draft.timezone }}
            </p>
        </aside>
    </div>
</template>
