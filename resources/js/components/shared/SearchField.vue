<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { useId } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        id?: string;
        label: string;
        placeholder?: string;
        clearLabel?: string;
        describedBy?: string;
        invalid?: boolean;
        disabled?: boolean;
        pending?: boolean;
        labelHidden?: boolean;
        class?: HTMLAttributes['class'];
        inputClass?: HTMLAttributes['class'];
    }>(),
    {
        modelValue: '',
        id: undefined,
        placeholder: undefined,
        clearLabel: undefined,
        describedBy: undefined,
        invalid: false,
        disabled: false,
        pending: false,
        labelHidden: true,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
    clear: [];
}>();
const generatedId = useId();
const controlId = props.id ?? `search-field-${generatedId}`;
const modelValue = useVModel(props, 'modelValue', emit, { passive: true });

function clear(): void {
    modelValue.value = '';
    emit('clear');
}
</script>

<template>
    <div data-slot="search-field" :class="cn('grid gap-2', props.class)">
        <Label
            :for="controlId"
            :class="props.labelHidden ? 'sr-only' : undefined"
        >
            {{ props.label }}
        </Label>
        <div class="relative">
            <Spinner
                v-if="props.pending"
                class="pointer-events-none absolute top-1/2 left-3.5 z-10 -translate-y-1/2"
            />
            <Search
                v-else
                class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                aria-hidden="true"
            />
            <Input
                :id="controlId"
                v-model="modelValue"
                type="search"
                :placeholder="props.placeholder"
                :disabled="props.disabled"
                :aria-busy="props.pending ? 'true' : undefined"
                :aria-describedby="props.describedBy"
                :aria-invalid="props.invalid"
                :class="
                    cn(
                        'pl-10',
                        modelValue && props.clearLabel && 'pr-11',
                        props.inputClass,
                    )
                "
            />
            <Button
                v-if="!props.pending && modelValue && props.clearLabel"
                type="button"
                variant="ghost"
                size="icon-sm"
                class="absolute top-1 right-1"
                :aria-label="props.clearLabel"
                @click="clear"
            >
                <X aria-hidden="true" />
            </Button>
        </div>
    </div>
</template>
