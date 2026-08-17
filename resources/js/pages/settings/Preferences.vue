<script setup lang="ts">
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import { RotateCcw, Save, Sparkles } from '@lucide/vue';
import { restart as restartOnboarding } from '@/actions/App/Http/Controllers/OnboardingController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/composables/useToast';
import { useUi } from '@/composables/useUi';
import { update } from '@/routes/preferences';
import type { SettingsLayoutProps } from '@/types';
import type { UserPreference } from '@/types/models';

type PreferenceFields = Pick<
    UserPreference,
    | 'date_format'
    | 'default_view'
    | 'language'
    | 'start_page'
    | 'time_format'
    | 'timezone'
    | 'week_start'
>;

const props = defineProps<{
    preferences: PreferenceFields;
    timezones: string[];
    canReplayOnboarding: boolean;
}>();
const toast = useToast();
const { t } = useUi();

setLayoutProps<SettingsLayoutProps>({
    settingsEyebrow: t('account.menu.settings'),
    settingsTitle: t('settings.preferences.title'),
    settingsDescription: t('settings.preferences.description'),
});

const form = useForm({
    timezone: props.preferences.timezone,
    language: props.preferences.language,
    date_format: props.preferences.date_format,
    time_format: props.preferences.time_format,
    default_view: props.preferences.default_view,
    start_page: props.preferences.start_page,
    week_start: props.preferences.week_start,
});
const replayForm = useForm({});

function replayOnboarding(): void {
    replayForm.post(restartOnboarding.url(), {
        preserveScroll: true,
    });
}

function submit() {
    form.put(update.url(), {
        onSuccess: () => toast.success(t('settings.preferences.saved')),
    });
}

const languages = [
    { value: 'en', label: 'settings.preferences.languages.en' },
    { value: 'lt', label: 'settings.preferences.languages.lt' },
    { value: 'ru', label: 'settings.preferences.languages.ru' },
];
const dateFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd.m.Y'];
const timeFormats = ['H:i', 'h:i A'];
const startPages = ['dashboard', 'tasks', 'projects', 'calendar'];
</script>

<template>
    <Head :title="t('settings.preferences.title')" />
    <div class="space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader
                    ><CardTitle>{{
                        t('settings.preferences.display')
                    }}</CardTitle></CardHeader
                >
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="default-view">{{
                                t('settings.preferences.default_view')
                            }}</Label>
                            <Select
                                v-model="form.default_view"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="default-view"
                                    :aria-invalid="
                                        Boolean(form.errors.default_view)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="list">{{
                                        t('settings.preferences.list_view')
                                    }}</SelectItem>
                                    <SelectItem value="board">{{
                                        t('settings.preferences.board_view')
                                    }}</SelectItem>
                                    <SelectItem value="calendar">{{
                                        t('settings.preferences.calendar_view')
                                    }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.default_view" />
                        </div>
                        <div class="space-y-2">
                            <Label for="start-page">{{
                                t('settings.preferences.start_page')
                            }}</Label>
                            <Select
                                v-model="form.start_page"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="start-page"
                                    :aria-invalid="
                                        Boolean(form.errors.start_page)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="page in startPages"
                                        :key="page"
                                        :value="page"
                                    >
                                        {{
                                            t(
                                                `settings.preferences.start_pages.${page}`,
                                            )
                                        }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.start_page" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle>{{
                        t('settings.preferences.locale')
                    }}</CardTitle></CardHeader
                >
                <CardContent class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        {{ t('settings.preferences.locale_description') }}
                    </p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="language">{{
                                t('settings.preferences.language')
                            }}</Label>
                            <Select
                                v-model="form.language"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="language"
                                    :aria-invalid="
                                        Boolean(form.errors.language)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="language in languages"
                                        :key="language.value"
                                        :value="language.value"
                                    >
                                        {{ t(language.label) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.language" />
                        </div>
                        <div class="space-y-2">
                            <Label for="timezone">{{
                                t('settings.preferences.timezone')
                            }}</Label>
                            <Select
                                v-model="form.timezone"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="timezone"
                                    :aria-invalid="
                                        Boolean(form.errors.timezone)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="tz in props.timezones"
                                        :key="tz"
                                        :value="tz"
                                        >{{ tz }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.timezone" />
                        </div>
                        <div class="space-y-2">
                            <Label for="date-format">{{
                                t('settings.preferences.date_format')
                            }}</Label>
                            <Select
                                v-model="form.date_format"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="date-format"
                                    :aria-invalid="
                                        Boolean(form.errors.date_format)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="f in dateFormats"
                                        :key="f"
                                        :value="f"
                                        >{{ f }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.date_format" />
                        </div>
                        <div class="space-y-2">
                            <Label for="time-format">{{
                                t('settings.preferences.time_format')
                            }}</Label>
                            <Select
                                v-model="form.time_format"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="time-format"
                                    :aria-invalid="
                                        Boolean(form.errors.time_format)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="format in timeFormats"
                                        :key="format"
                                        :value="format"
                                        >{{ format }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.time_format" />
                        </div>
                        <div class="space-y-2">
                            <Label for="week-start">{{
                                t('settings.preferences.week_start')
                            }}</Label>
                            <Select
                                v-model="form.week_start"
                                :disabled="form.processing"
                            >
                                <SelectTrigger
                                    id="week-start"
                                    :aria-invalid="
                                        Boolean(form.errors.week_start)
                                    "
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="sunday">{{
                                        t(
                                            'settings.preferences.week_starts.sunday',
                                        )
                                    }}</SelectItem>
                                    <SelectItem value="monday">{{
                                        t(
                                            'settings.preferences.week_starts.monday',
                                        )
                                    }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.week_start" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <div class="flex justify-end">
                <Button type="submit" size="lg" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    <Save v-else class="size-4" aria-hidden="true" />
                    {{ t('settings.preferences.save') }}
                </Button>
            </div>
        </form>

        <Card
            v-if="canReplayOnboarding"
            class="overflow-hidden border-orange-200/80 bg-gradient-to-br from-orange-50 via-background to-amber-50/60"
        >
            <CardHeader class="sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-sm"
                    >
                        <Sparkles class="size-5" aria-hidden="true" />
                    </span>
                    <div class="space-y-1">
                        <p
                            class="text-xs font-semibold tracking-[0.16em] text-orange-700 uppercase"
                        >
                            {{ t('settings.preferences.replay.eyebrow') }}
                        </p>
                        <CardTitle>{{
                            t('settings.preferences.replay.title')
                        }}</CardTitle>
                        <CardDescription class="max-w-2xl leading-6">
                            {{ t('settings.preferences.replay.description') }}
                        </CardDescription>
                    </div>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="mt-4 min-h-11 shrink-0 border-orange-300 bg-background/80 sm:mt-0"
                    :disabled="replayForm.processing"
                    @click="replayOnboarding"
                >
                    <Spinner v-if="replayForm.processing" />
                    <RotateCcw v-else class="size-4" aria-hidden="true" />
                    {{ t('settings.preferences.replay.action') }}
                </Button>
            </CardHeader>
            <CardContent v-if="replayForm.processing || replayForm.hasErrors">
                <p
                    class="text-sm text-muted-foreground"
                    :class="{ 'text-destructive': replayForm.hasErrors }"
                    :role="replayForm.hasErrors ? 'alert' : undefined"
                    aria-live="polite"
                >
                    {{
                        t(
                            replayForm.hasErrors
                                ? 'settings.preferences.replay.error'
                                : 'settings.preferences.replay.processing',
                        )
                    }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
