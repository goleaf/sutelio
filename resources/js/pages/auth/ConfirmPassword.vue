<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { watchEffect } from 'vue';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useUi } from '@/composables/useUi';
import { store } from '@/routes/password/confirm';

const { t } = useUi();

watchEffect(() => {
    setLayoutProps({
        title: t('auth.confirm_password.heading'),
        description: t('auth.confirm_password.description'),
    });
});
</script>

<template>
    <Head :title="t('auth.confirm_password.title')" />

    <PasskeyVerify
        :routes="{
            options: confirmOptions(),
            submit: confirmStore(),
        }"
        :label="t('auth.confirm_password.confirm_passkey')"
        :loading-label="t('auth.confirm_password.confirming')"
        :separator="t('auth.confirm_password.separator')"
    />

    <Form
        v-bind="store.form()"
        reset-on-success
        disable-while-processing
        v-slot="{ errors, processing }"
    >
        <div class="space-y-6">
            <div class="grid gap-2">
                <Label htmlFor="password">{{
                    t('auth.common.password')
                }}</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                    :aria-invalid="Boolean(errors.password)"
                />

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center">
                <Button
                    size="lg"
                    class="w-full"
                    :loading="processing"
                    :loading-label="t('auth.confirm_password.submit')"
                    data-test="confirm-password-button"
                >
                    <ShieldCheck class="size-4" aria-hidden="true" />
                    {{ t('auth.confirm_password.submit') }}
                </Button>
            </div>
        </div>
    </Form>
</template>
