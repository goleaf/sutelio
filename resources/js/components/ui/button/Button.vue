<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '.';
import { Primitive } from 'reka-ui';
import { computed } from 'vue';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { buttonVariants } from '.';

interface Props extends PrimitiveProps {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    loading?: boolean;
    loadingLabel?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    loading: false,
    loadingLabel: undefined,
    disabled: false,
});

const isDisabled = computed(() => props.disabled || props.loading);

function preventDisabledActivation(event: MouseEvent): void {
    if (!isDisabled.value) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
}
</script>

<template>
    <Primitive
        data-slot="button"
        :data-variant="props.variant"
        :data-size="props.size"
        :data-loading="props.loading ? '' : undefined"
        :as="props.as"
        :as-child="props.asChild"
        :disabled="props.asChild ? undefined : isDisabled"
        :aria-busy="props.loading ? 'true' : undefined"
        :aria-disabled="isDisabled ? 'true' : undefined"
        :tabindex="props.asChild && isDisabled ? -1 : undefined"
        :class="
            cn(
                buttonVariants({
                    variant: props.variant,
                    size: props.size,
                }),
                props.class,
            )
        "
        @click="preventDisabledActivation"
    >
        <Spinner
            v-if="props.loading && !props.asChild"
            aria-hidden="true"
        />
        <template
            v-if="props.loading && props.loadingLabel && !props.asChild"
        >
            {{ props.loadingLabel }}
        </template>
        <slot v-else />
    </Primitive>
</template>
