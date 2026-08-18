<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Clock3 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LanguageFlag from '@/components/localization/LanguageFlag.vue';
import type {
    OnboardingCopy,
    OnboardingPreferences,
} from '@/components/onboarding/onboarding-types';
import TimezoneCombobox from '@/components/preferences/TimezoneCombobox.vue';
import IconTile from '@/components/shared/IconTile.vue';
import StatusNotice from '@/components/shared/StatusNotice.vue';
import { Label } from '@/components/ui/label';
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

const dateFormats: OnboardingPreferences['date_format'][] = [
    'Y-m-d',
    'd/m/Y',
    'm/d/Y',
    'd.m.Y',
];
const timeFormats: OnboardingPreferences['time_format'][] = ['H:i', 'h:i A'];
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(15rem,0.48fr)]">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="language">{{ copy.language }}</Label>
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
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in page.props.localization.options"
                            :key="option.code"
                            :value="option.code"
                        >
                            <span class="flex items-center gap-2.5">
                                <LanguageFlag :src="option.flag_url" />
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
                <Label for="timezone">{{ copy.timezone }}</Label>
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
                <Label for="date_format">{{ copy.date_format }}</Label>
                <Select v-model="dateFormat" :disabled="processing">
                    <SelectTrigger
                        id="date_format"
                        :aria-invalid="Boolean(errors.date_format)"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="value in dateFormats"
                            :key="value"
                            :value="value"
                            >{{ value }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.date_format" />
            </div>
            <div class="space-y-2">
                <Label for="time_format">{{ copy.time_format }}</Label>
                <Select v-model="timeFormat" :disabled="processing">
                    <SelectTrigger
                        id="time_format"
                        :aria-invalid="Boolean(errors.time_format)"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="value in timeFormats"
                            :key="value"
                            :value="value"
                            >{{ value }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.time_format" />
            </div>
            <div class="space-y-2">
                <Label for="default_view">{{ copy.default_view }}</Label>
                <Select v-model="defaultView" :disabled="processing">
                    <SelectTrigger
                        id="default_view"
                        :aria-invalid="Boolean(errors.default_view)"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="(label, value) in copy.views"
                            :key="value"
                            :value="value"
                            >{{ label }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.default_view" />
            </div>
            <div class="space-y-2">
                <Label for="start_page">{{ copy.start_page }}</Label>
                <Select v-model="startPage" :disabled="processing">
                    <SelectTrigger
                        id="start_page"
                        :aria-invalid="Boolean(errors.start_page)"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="(label, value) in copy.start_pages"
                            :key="value"
                            :value="value"
                            >{{ label }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.start_page" />
            </div>
            <div class="space-y-2 sm:col-span-2">
                <Label for="week_start">{{ copy.week_start }}</Label>
                <Select v-model="weekStart" :disabled="processing">
                    <SelectTrigger
                        id="week_start"
                        :aria-invalid="Boolean(errors.week_start)"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="(label, value) in copy.week_starts"
                            :key="value"
                            :value="value"
                            >{{ label }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <InputError :message="errors.week_start" />
            </div>
        </div>

        <aside
            class="rounded-2xl border border-orange-500/15 bg-orange-500/[0.055] p-5"
        >
            <IconTile tone="brand">
                <Clock3 />
            </IconTile>
            <h2 class="mt-4 font-semibold">{{ copy.preview_title }}</h2>
            <p class="mt-1 text-base leading-7 text-muted-foreground">
                {{ copy.preview_description }}
            </p>
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
