<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { computed } from 'vue';
import OnboardingIcon from '@/components/onboarding/OnboardingIcon.vue';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: 'label' | 'legend';
        htmlFor?: string;
        icon: Component;
        class?: HTMLAttributes['class'];
    }>(),
    {
        as: 'label',
        htmlFor: undefined,
    },
);

const element = computed(() => (props.as === 'legend' ? 'legend' : Label));
</script>

<template>
    <component
        :is="element"
        :for="as === 'label' ? htmlFor : undefined"
        data-slot="onboarding-field-label"
        :class="
            cn(
                'flex min-w-0 items-center gap-2 text-base font-medium wrap-anywhere',
                props.class,
            )
        "
    >
        <OnboardingIcon :icon="icon" />
        <span class="min-w-0"><slot /></span>
    </component>
</template>
