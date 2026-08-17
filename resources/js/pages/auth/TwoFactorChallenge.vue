<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { KeyRound, ShieldCheck } from '@lucide/vue';
import { computed, ref, watchEffect } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { useUi } from '@/composables/useUi';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');
const { t } = useUi();

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: t('auth.two_factor.recovery_code'),
            description: t('auth.two_factor.recovery_description'),
            buttonText: t('auth.two_factor.use_authentication_code'),
        };
    }

    return {
        title: t('auth.two_factor.authentication_code'),
        description: t('auth.two_factor.authentication_description'),
        buttonText: t('auth.two_factor.use_recovery_code'),
    };
});

watchEffect(() => {
    setLayoutProps({
        title: authConfigContent.value.title,
        description: authConfigContent.value.description,
    });
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <Head :title="t('auth.two_factor.title')" />

    <div class="space-y-6">
        <template v-if="!showRecoveryInput">
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                disable-while-processing
                @error="code = ''"
                #default="{ errors, processing, clearErrors }"
            >
                <input type="hidden" name="code" :value="code" />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full items-center justify-center">
                        <InputOTP
                            id="otp"
                            v-model="code"
                            :maxlength="6"
                            :disabled="processing"
                            :aria-invalid="Boolean(errors.code)"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="index in 6"
                                    :key="index"
                                    :index="index - 1"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError :message="errors.code" />
                </div>
                <Button
                    type="submit"
                    size="lg"
                    class="w-full"
                    :loading="processing"
                    :loading-label="t('auth.two_factor.continue')"
                >
                    <ShieldCheck class="size-4" aria-hidden="true" />
                    {{ t('auth.two_factor.continue') }}
                </Button>
                <div class="text-center text-sm text-muted-foreground">
                    <span>{{ t('auth.two_factor.or_you_can') }} </span>
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-md font-medium text-orange-700 underline decoration-orange-500/35 underline-offset-4 transition-colors duration-200 hover:text-orange-800 hover:decoration-orange-500 focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        <KeyRound class="size-3.5" aria-hidden="true" />
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </Form>
        </template>

        <template v-else>
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                disable-while-processing
                #default="{ errors, processing, clearErrors }"
            >
                <Input
                    name="recovery_code"
                    type="text"
                    :placeholder="t('auth.two_factor.recovery_placeholder')"
                    :autofocus="showRecoveryInput"
                    required
                    :aria-invalid="Boolean(errors.recovery_code)"
                />
                <InputError :message="errors.recovery_code" />
                <Button
                    type="submit"
                    size="lg"
                    class="w-full"
                    :loading="processing"
                    :loading-label="t('auth.two_factor.continue')"
                >
                    <ShieldCheck class="size-4" aria-hidden="true" />
                    {{ t('auth.two_factor.continue') }}
                </Button>

                <div class="text-center text-sm text-muted-foreground">
                    <span>{{ t('auth.two_factor.or_you_can') }} </span>
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-md font-medium text-orange-700 underline decoration-orange-500/35 underline-offset-4 transition-colors duration-200 hover:text-orange-800 hover:decoration-orange-500 focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        <KeyRound class="size-3.5" aria-hidden="true" />
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </Form>
        </template>
    </div>
</template>
