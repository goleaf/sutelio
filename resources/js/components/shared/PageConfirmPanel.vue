<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import { computed, nextTick, ref, useId, watch } from 'vue';
import IconTile from '@/components/shared/IconTile.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmLabel: string;
        cancelLabel: string;
        processing?: boolean;
        destructive?: boolean;
        confirmationText?: string;
        confirmationLabel?: string;
        confirmType?: 'button' | 'submit';
    }>(),
    {
        processing: false,
        destructive: true,
        confirmType: 'button',
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

const componentId = useId();
const panel = ref<HTMLElement | null>(null);
const heading = ref<HTMLElement | null>(null);
const focusOrigin = ref<HTMLElement | null>(null);
const confirmationValue = ref('');
const confirmationMatches = computed(
    () =>
        !props.confirmationText ||
        confirmationValue.value === props.confirmationText,
);

watch(
    () => props.open,
    async (open, wasOpen) => {
        if (!open) {
            if (wasOpen) {
                await nextTick();

                if (focusOrigin.value?.isConnected) {
                    focusOrigin.value.focus({ preventScroll: true });
                }

                focusOrigin.value = null;
            }

            return;
        }

        focusOrigin.value =
            document.activeElement instanceof HTMLElement
                ? document.activeElement
                : null;
        confirmationValue.value = '';
        await nextTick();
        panel.value?.scrollIntoView({ behavior: 'auto', block: 'center' });
        heading.value?.focus({ preventScroll: true });
    },
);

function confirm(): void {
    if (confirmationMatches.value) {
        emit('confirm');
    }
}
</script>

<template>
    <section
        v-if="open"
        ref="panel"
        data-slot="page-confirm-panel"
        role="region"
        :aria-labelledby="`${componentId}-title`"
        :aria-describedby="`${componentId}-description`"
        :aria-busy="processing"
        :class="[
            'ui-reveal my-6 overflow-hidden rounded-panel border bg-card shadow-panel forced-colors:border-[CanvasText]',
            destructive
                ? 'border-status-destructive-border'
                : 'border-orange-500/35',
        ]"
    >
        <div class="space-y-5 p-4 sm:p-6">
            <div class="flex min-w-0 items-center gap-4">
                <IconTile
                    :tone="destructive ? 'destructive' : 'brand'"
                    size="md"
                >
                    <slot name="icon">
                        <TriangleAlert aria-hidden="true" />
                    </slot>
                </IconTile>
                <div class="min-w-0 flex-1">
                    <h2
                        :id="`${componentId}-title`"
                        ref="heading"
                        tabindex="-1"
                        class="text-xl font-semibold tracking-tight wrap-anywhere focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {{ title }}
                    </h2>
                    <p
                        :id="`${componentId}-description`"
                        class="mt-1.5 text-base leading-6 wrap-anywhere text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
            </div>

            <div v-if="confirmationText" class="max-w-xl space-y-2">
                <Label :for="`${componentId}-confirmation`">
                    {{ confirmationLabel ?? confirmationText }}
                </Label>
                <Input
                    :id="`${componentId}-confirmation`"
                    v-model="confirmationValue"
                    :disabled="processing"
                    autocomplete="off"
                />
            </div>

            <slot />
        </div>

        <div
            class="flex flex-col-reverse gap-2 border-t border-border/70 bg-muted/20 p-4 min-[30rem]:flex-row min-[30rem]:justify-end sm:px-6"
        >
            <Button
                type="button"
                variant="outline"
                size="lg"
                :disabled="processing"
                @click="emit('update:open', false)"
            >
                {{ cancelLabel }}
            </Button>
            <Button
                :type="confirmType"
                :variant="destructive ? 'destructive' : 'default'"
                size="lg"
                :loading="processing"
                :disabled="processing || !confirmationMatches"
                @click="confirm"
            >
                {{ confirmLabel }}
            </Button>
        </div>
    </section>
</template>
