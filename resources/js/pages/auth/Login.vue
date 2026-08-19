<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { BadgeCheck, LogIn } from '@lucide/vue';
import { ref, watchEffect } from 'vue';
import AuthEmailAssistant from '@/components/auth/AuthEmailAssistant.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useUi } from '@/composables/useUi';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const { t } = useUi();

watchEffect(() => {
    setLayoutProps({
        title: t('auth.login.heading'),
        description: t('auth.login.description'),
    });
});

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    deviceEmailPickerAvailable: boolean;
    rememberedEmails: string[];
}>();

const email = ref(props.rememberedEmails[0] ?? '');
</script>

<template>
    <Head :title="t('auth.login.title')" />

    <Alert v-if="status" variant="success" class="mb-4">
        <BadgeCheck aria-hidden="true" />
        <AlertDescription class="font-medium">
            {{ status }}
        </AlertDescription>
    </Alert>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        disable-while-processing
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <AuthEmailAssistant
                v-model="email"
                mode="login"
                autofocus
                :error="errors.email"
                :remembered-emails="props.rememberedEmails"
                :device-email-picker-available="
                    props.deviceEmailPickerAvailable
                "
            />

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">{{
                        t('auth.common.password')
                    }}</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-base"
                    >
                        {{ t('auth.login.forgot_password') }}
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    :placeholder="t('auth.common.password')"
                    :aria-invalid="Boolean(errors.password)"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label
                    for="remember"
                    class="flex min-h-12 cursor-pointer items-center space-x-3"
                >
                    <Checkbox
                        id="remember"
                        name="remember"
                        :aria-label="t('auth.login.remember')"
                    />
                    <span>{{ t('auth.login.remember') }}</span>
                </Label>
            </div>

            <Button
                type="submit"
                size="lg"
                class="mt-4 w-full"
                :loading="processing"
                :loading-label="t('auth.login.submit')"
                data-test="login-button"
            >
                <LogIn class="size-4" aria-hidden="true" />
                {{ t('auth.login.submit') }}
            </Button>
        </div>

        <div class="text-center text-base text-muted-foreground">
            {{ t('auth.login.no_account') }}
            <TextLink :href="register()">{{
                t('auth.login.sign_up')
            }}</TextLink>
        </div>
    </Form>
</template>
