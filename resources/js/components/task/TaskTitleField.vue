<script setup lang="ts">
import Field from '@/components/shared/Field.vue';
import { Input } from '@/components/ui/input';

withDefaults(
    defineProps<{
        label: string;
        error?: string;
        placeholder?: string;
        disabled?: boolean;
        autofocus?: boolean;
    }>(),
    {
        error: undefined,
        placeholder: undefined,
        disabled: false,
        autofocus: false,
    },
);

const model = defineModel<string>({ required: true });
const emit = defineEmits<{ input: [] }>();
</script>

<template>
    <Field :label="label" :error="error" required>
        <template #default="{ id, describedBy, invalid, required }">
            <Input
                :id="id"
                v-model="model"
                maxlength="500"
                :placeholder="placeholder"
                :autofocus="autofocus"
                :disabled="disabled"
                :aria-describedby="describedBy"
                :aria-invalid="invalid"
                :required="required"
                @input="emit('input')"
            />
        </template>
    </Field>
</template>
