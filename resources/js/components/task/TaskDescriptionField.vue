<script setup lang="ts">
import Field from '@/components/shared/Field.vue';
import { Textarea } from '@/components/ui/textarea';

withDefaults(
    defineProps<{
        label: string;
        error?: string;
        placeholder?: string;
        disabled?: boolean;
        rows?: number;
    }>(),
    {
        error: undefined,
        placeholder: undefined,
        disabled: false,
        rows: 4,
    },
);

const model = defineModel<string>({ required: true });
const emit = defineEmits<{ input: [] }>();
</script>

<template>
    <Field :label="label" :error="error">
        <template #default="{ id, describedBy, invalid }">
            <Textarea
                :id="id"
                v-model="model"
                :rows="rows"
                :placeholder="placeholder"
                :disabled="disabled"
                :aria-describedby="describedBy"
                :aria-invalid="invalid"
                @input="emit('input')"
            />
        </template>
    </Field>
</template>
