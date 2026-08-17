<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Check, Eye, EyeOff, LockKeyhole, RefreshCw } from '@lucide/vue';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';
import AlertError from '@/components/AlertError.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { useUi } from '@/composables/useUi';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const { t } = useUi();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <LeadingIconHeading tile tile-tone="warning">
                <template #icon>
                    <LockKeyhole />
                </template>

                <CardTitle>
                    {{ t('account.two_factor.recovery_title') }}
                </CardTitle>
                <CardDescription>
                    {{ t('account.two_factor.recovery_description') }}
                </CardDescription>
            </LeadingIconHeading>
        </CardHeader>
        <CardContent>
            <div
                class="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between"
            >
                <Button @click="toggleRecoveryCodesVisibility" class="w-fit">
                    <component
                        :is="isRecoveryCodesVisible ? EyeOff : Eye"
                        class="size-4"
                    />
                    {{
                        isRecoveryCodesVisible
                            ? t('account.two_factor.recovery_hide')
                            : t('account.two_factor.recovery_view')
                    }}
                    {{ t('account.two_factor.recovery_label') }}
                </Button>

                <Form
                    v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                    v-bind="regenerateRecoveryCodes.form()"
                    method="post"
                    :options="{ preserveScroll: true }"
                    @success="fetchRecoveryCodes"
                    #default="{ processing, recentlySuccessful }"
                >
                    <Button
                        variant="secondary"
                        type="submit"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        <Check
                            v-else-if="recentlySuccessful"
                            class="ui-status-pop size-4"
                            aria-hidden="true"
                        />
                        <RefreshCw v-else class="size-4" aria-hidden="true" />
                        {{ t('account.two_factor.recovery_action') }}
                    </Button>
                </Form>
            </div>
            <Transition
                enter-active-class="transition-[opacity,transform] duration-[var(--motion-state)] ease-[var(--ease-emphasized)] motion-reduce:transition-none"
                enter-from-class="translate-y-2 opacity-0 motion-reduce:translate-y-0"
                leave-active-class="transition-[opacity,transform] duration-[var(--motion-feedback)] ease-[var(--ease-exit)] motion-reduce:transition-none"
                leave-to-class="translate-y-2 opacity-0 motion-reduce:translate-y-0"
            >
                <div
                    v-if="isRecoveryCodesVisible"
                    class="relative overflow-hidden"
                >
                    <div v-if="errors?.length" class="mt-6">
                        <AlertError :errors="errors" />
                    </div>
                    <div v-else class="mt-3 space-y-3">
                        <div
                            ref="recoveryCodeSectionRef"
                            class="grid gap-1 rounded-xl border border-border/80 bg-muted/50 p-4 font-mono text-sm"
                        >
                            <div
                                v-if="!recoveryCodesList.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="n in 8"
                                    :key="n"
                                    class="h-4 animate-pulse rounded-lg bg-muted-foreground/20 motion-reduce:animate-none"
                                ></div>
                            </div>
                            <div
                                v-else
                                v-for="(code, index) in recoveryCodesList"
                                :key="index"
                            >
                                {{ code }}
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground select-none">
                            {{ t('account.two_factor.recovery_help_before') }}
                            <span class="font-bold">{{
                                t('account.two_factor.recovery_action')
                            }}</span>
                            {{ t('account.two_factor.recovery_help_after') }}
                        </p>
                    </div>
                </div>
            </Transition>
        </CardContent>
    </Card>
</template>
