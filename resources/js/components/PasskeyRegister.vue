<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { KeyRound, Plus, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useUi } from '@/composables/useUi';
import {
    getDefaultPasskeyName,
    localizePasskeyError,
} from '@/lib/passkeyErrors';

const emit = defineEmits<{
    success: [];
}>();
const { t } = useUi();

const name = ref('');
const showForm = ref(false);

const { register, isLoading, errorInstance, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = '';
        showForm.value = false;
        emit('success');
    },
});
const localizedError = computed(() =>
    localizePasskeyError(errorInstance.value, t),
);

const handleOpen = () => {
    name.value = getDefaultPasskeyName(navigator.userAgent, t);
    showForm.value = true;
};

const handleSubmit = async (event: Event) => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = () => {
    showForm.value = false;
    name.value = '';
};
</script>

<template>
    <div v-if="!isSupported" class="text-sm text-muted-foreground">
        {{ t('account.passkeys.not_supported') }}
    </div>

    <Button v-else-if="!showForm" variant="outline" @click="handleOpen">
        <Plus class="size-4" aria-hidden="true" />
        {{ t('account.passkeys.add') }}
    </Button>

    <form
        v-else
        @submit="handleSubmit"
        class="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
    >
        <div class="grid gap-2">
            <Label for="passkey-name">{{ t('account.passkeys.name') }}</Label>
            <Input
                id="passkey-name"
                type="text"
                v-model="name"
                :placeholder="t('account.passkeys.name_placeholder')"
                class="mt-1 block w-full border-foreground/20"
                autofocus
            />
            <p class="text-xs text-muted-foreground">
                {{ t('account.passkeys.help') }}
            </p>
        </div>

        <InputError v-if="localizedError" :message="localizedError" />

        <div class="flex gap-2">
            <Button type="submit" :disabled="isLoading || !name.trim()">
                <Spinner v-if="isLoading" />
                <KeyRound v-else class="size-4" aria-hidden="true" />
                {{
                    isLoading
                        ? t('account.passkeys.registering')
                        : t('account.passkeys.register')
                }}
            </Button>
            <Button type="button" variant="ghost" @click="handleCancel">
                <X class="size-4" aria-hidden="true" />
                {{ t('common.actions.cancel') }}
            </Button>
        </div>
    </form>
</template>
