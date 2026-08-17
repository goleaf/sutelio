<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed, useId } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label: string;
        description?: string;
        error?: string;
        id?: string;
        required?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        description: undefined,
        error: undefined,
        id: undefined,
        required: false,
        class: undefined,
    },
);

const generatedId = useId();
const controlId = computed(() => props.id ?? `field-${generatedId}`);
const descriptionId = computed(() =>
    props.description ? `${controlId.value}-description` : undefined,
);
const errorId = computed(() =>
    props.error ? `${controlId.value}-error` : undefined,
);
const describedBy = computed(
    () =>
        [descriptionId.value, errorId.value].filter(Boolean).join(' ') ||
        undefined,
);
const hasError = computed(() => Boolean(props.error));
</script>

<template>
    <div data-slot="field" :class="cn('grid gap-2', props.class)">
        <Label data-slot="field-label" :for="controlId">
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true">
                *
            </span>
        </Label>

        <p
            v-if="description"
            :id="descriptionId"
            data-slot="field-description"
            class="text-sm leading-5 text-muted-foreground"
        >
            {{ description }}
        </p>

        <slot
            :id="controlId"
            :described-by="describedBy"
            :invalid="hasError"
            :required="required"
        />

        <InputError :id="errorId" :message="error" />
    </div>
</template>
