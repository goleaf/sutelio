<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { usePasskeyVerify } from '@laravel/passkeys/vue';
import { KeyRound } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { useUi } from '@/composables/useUi';
import { localizePasskeyError } from '@/lib/passkeyErrors';
import { dashboard } from '@/routes';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

const props = defineProps<Props>();
const { t } = useUi();

const { verify, isLoading, errorInstance, isSupported } = usePasskeyVerify({
    ...(props.routes
        ? {
              routes: {
                  options: props.routes.options.url,
                  submit: props.routes.submit.url,
              },
          }
        : {}),
    onSuccess: (response) => {
        router.visit(response.redirect ?? dashboard().url);
    },
});
const localizedError = computed(() =>
    localizePasskeyError(errorInstance.value, t),
);
</script>

<template>
    <div v-if="isSupported">
        <div class="grid gap-2">
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="w-full"
                @click="verify"
                :disabled="isLoading"
            >
                <Spinner v-if="isLoading" />
                <KeyRound v-else class="h-4 w-4" />
                {{
                    isLoading
                        ? (props.loadingLabel ??
                          t('account.passkeys.authenticating'))
                        : (props.label ?? t('account.passkeys.sign_in'))
                }}
            </Button>

            <div v-if="localizedError" class="text-center">
                <InputError :message="localizedError" />
            </div>
        </div>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <Separator class="w-full" />
            </div>
            <div class="relative flex justify-center text-[0.9375rem]">
                <span class="bg-background px-2 text-muted-foreground">
                    {{ props.separator ?? t('account.passkeys.separator') }}
                </span>
            </div>
        </div>
    </div>
</template>
