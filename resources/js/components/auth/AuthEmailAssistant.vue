<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Check, Mail, Smartphone, Trash2 } from '@lucide/vue';
import type { ComponentPublicInstance } from 'vue';
import { computed, nextTick, ref, watch } from 'vue';
import { BridgeCall } from '#nativephp';
import ForgetRememberedEmailController from '@/actions/App/Http/Controllers/ForgetRememberedEmailController';
import InputError from '@/components/InputError.vue';
import StatusNotice from '@/components/shared/StatusNotice.vue';
import type { StatusNoticeStatus } from '@/components/shared/StatusNotice.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useUi } from '@/composables/useUi';
import {
    DeviceEmailPickerError,
    requestDeviceEmail,
} from '@/lib/deviceEmailPicker';

type AssistantMode = 'login' | 'register';

type ForgetResponse = {
    forgotten: boolean;
    remaining: number;
};

const props = withDefaults(
    defineProps<{
        autofocus?: boolean;
        deviceEmailPickerAvailable?: boolean;
        error?: string;
        mode: AssistantMode;
        modelValue: string;
        rememberedEmails?: string[];
    }>(),
    {
        autofocus: false,
        deviceEmailPickerAvailable: false,
        error: undefined,
        rememberedEmails: () => [],
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useUi();
const inputRef = ref<ComponentPublicInstance | null>(null);
const rememberedEmails = ref([...props.rememberedEmails]);
const choosing = ref(false);
const removingEmail = ref<string | null>(null);
const notice = ref<{
    message: string;
    status: StatusNoticeStatus;
} | null>(null);
const forgetRequest = useHttp<{ email: string }, ForgetResponse>({
    email: '',
});

const email = computed({
    get: () => props.modelValue,
    set: (value: string | number) => emit('update:modelValue', String(value)),
});
const autocomplete = computed(() =>
    props.mode === 'login' ? 'username' : 'email',
);
const showRememberedEmails = computed(
    () => props.mode === 'login' && rememberedEmails.value.length > 0,
);

watch(
    () => props.rememberedEmails,
    (emails) => {
        rememberedEmails.value = [...emails];
    },
);

function emailIdentity(value: string): string {
    return value.trim().toLowerCase();
}

function isSelected(value: string): boolean {
    return emailIdentity(value) === emailIdentity(email.value);
}

async function focusEmail(): Promise<void> {
    await nextTick();
    const element = inputRef.value?.$el;

    if (element instanceof HTMLInputElement) {
        element.focus();
    }
}

function selectRememberedEmail(value: string): void {
    email.value = value;
    notice.value = {
        message: t('auth.email_assistance.selected'),
        status: 'success',
    };
    void focusEmail();
}

async function chooseFromDevice(): Promise<void> {
    if (choosing.value) {
        return;
    }

    choosing.value = true;
    notice.value = {
        message: t('auth.email_assistance.chooser_loading'),
        status: 'loading',
    };

    try {
        const selectedEmail = await requestDeviceEmail(() =>
            BridgeCall('EmailPicker.Choose'),
        );

        if (selectedEmail === null) {
            notice.value = {
                message: t('auth.email_assistance.chooser_cancelled'),
                status: 'information',
            };

            return;
        }

        email.value = selectedEmail;
        notice.value = {
            message: t('auth.email_assistance.chooser_selected'),
            status: 'success',
        };
        await focusEmail();
    } catch (error) {
        notice.value = {
            message:
                error instanceof DeviceEmailPickerError &&
                error.code === 'TIMED_OUT'
                    ? t('auth.email_assistance.chooser_timeout')
                    : t('auth.email_assistance.chooser_error'),
            status: 'error',
        };
    } finally {
        choosing.value = false;
    }
}

async function forgetEmail(value: string): Promise<void> {
    if (removingEmail.value !== null) {
        return;
    }

    removingEmail.value = value;
    forgetRequest.email = value;
    notice.value = null;

    try {
        const result = await forgetRequest.delete(
            ForgetRememberedEmailController.url(),
        );

        if (!result.forgotten) {
            notice.value = {
                message: t('auth.email_assistance.forget_error'),
                status: 'error',
            };

            return;
        }

        rememberedEmails.value = rememberedEmails.value.filter(
            (candidate) => emailIdentity(candidate) !== emailIdentity(value),
        );
        notice.value = {
            message: t('auth.email_assistance.forgotten'),
            status: 'success',
        };
        await focusEmail();
    } catch {
        notice.value = {
            message: t('auth.email_assistance.forget_error'),
            status: 'error',
        };
    } finally {
        removingEmail.value = null;
        forgetRequest.reset();
    }
}
</script>

<template>
    <div class="grid gap-3" data-test="auth-email-assistant">
        <Label for="email">{{ t('auth.common.email') }}</Label>
        <Input
            id="email"
            ref="inputRef"
            v-model="email"
            type="email"
            name="email"
            inputmode="email"
            required
            :autofocus="props.autofocus"
            :autocomplete="autocomplete"
            autocapitalize="none"
            :spellcheck="false"
            :placeholder="t('auth.common.email_placeholder')"
            :aria-invalid="Boolean(props.error)"
        />
        <InputError :message="props.error" />

        <section
            v-if="showRememberedEmails"
            class="rounded-2xl border border-orange-200/80 bg-orange-50/55 p-3 shadow-xs forced-colors:border-[CanvasText] forced-colors:bg-[Canvas]"
            aria-labelledby="remembered-emails-title"
            data-test="remembered-emails"
        >
            <div class="mb-2 flex items-start gap-2.5 px-1">
                <Mail
                    class="mt-0.5 size-5 shrink-0 text-orange-700"
                    aria-hidden="true"
                />
                <div class="min-w-0">
                    <h2
                        id="remembered-emails-title"
                        class="text-sm font-semibold text-foreground"
                    >
                        {{ t('auth.email_assistance.remembered_title') }}
                    </h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        {{ t('auth.email_assistance.remembered_description') }}
                    </p>
                </div>
            </div>

            <ul class="grid gap-2" role="list">
                <li
                    v-for="rememberedEmail in rememberedEmails"
                    :key="rememberedEmail"
                    class="flex min-w-0 items-stretch gap-2"
                >
                    <button
                        type="button"
                        class="flex min-h-12 min-w-0 flex-1 items-center gap-2.5 rounded-xl border border-border bg-background px-3 text-left text-sm font-medium shadow-xs transition-[border-color,background-color,color,box-shadow] hover:border-orange-400 hover:bg-orange-50 focus-visible:border-orange-500 focus-visible:ring-3 focus-visible:ring-orange-500/20 focus-visible:outline-none motion-reduce:transition-none forced-colors:border-[ButtonBorder]"
                        :class="{
                            'border-orange-500 bg-orange-50 text-orange-950':
                                isSelected(rememberedEmail),
                        }"
                        :aria-pressed="isSelected(rememberedEmail)"
                        :aria-label="
                            t('auth.email_assistance.use_remembered', {
                                email: rememberedEmail,
                            })
                        "
                        @click="selectRememberedEmail(rememberedEmail)"
                    >
                        <Mail
                            class="size-4 shrink-0 text-orange-700"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1 truncate">
                            {{ rememberedEmail }}
                        </span>
                        <Check
                            v-if="isSelected(rememberedEmail)"
                            class="size-4 shrink-0"
                            aria-hidden="true"
                        />
                    </button>

                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="min-h-12 min-w-12 shrink-0 text-muted-foreground hover:text-destructive"
                        :loading="removingEmail === rememberedEmail"
                        :disabled="removingEmail !== null"
                        :aria-label="
                            t('auth.email_assistance.forget', {
                                email: rememberedEmail,
                            })
                        "
                        @click="forgetEmail(rememberedEmail)"
                    >
                        <Trash2 class="size-4" aria-hidden="true" />
                    </Button>
                </li>
            </ul>
        </section>

        <div
            v-if="props.deviceEmailPickerAvailable"
            class="grid gap-2 rounded-2xl border border-border bg-muted/30 p-3 forced-colors:border-[CanvasText]"
        >
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="min-h-12 w-full justify-start"
                :loading="choosing"
                :loading-label="t('auth.email_assistance.chooser_loading')"
                @click="chooseFromDevice"
            >
                <Smartphone class="size-5" aria-hidden="true" />
                {{ t('auth.email_assistance.choose_from_device') }}
            </Button>
            <p class="px-1 text-sm leading-5 text-muted-foreground">
                {{ t('auth.email_assistance.chooser_description') }}
            </p>
        </div>

        <StatusNotice
            v-if="notice"
            :message="notice.message"
            :status="notice.status"
        />
    </div>
</template>
