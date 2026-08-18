<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

const props = withDefaults(
    defineProps<{
        applyDisabled?: boolean;
        applyLabel?: string;
        clearDisabled?: boolean;
        clearLabel?: string;
        description: string;
        processing?: boolean;
        title: string;
    }>(),
    {
        applyDisabled: false,
        applyLabel: undefined,
        clearDisabled: false,
        clearLabel: undefined,
        processing: false,
    },
);

const emit = defineEmits<{
    apply: [];
    clear: [];
}>();

const open = defineModel<boolean>({ required: true });
const hasActions = computed(() => props.clearLabel || props.applyLabel);
</script>

<template>
    <Sheet v-model:open="open">
        <SheetTrigger v-if="$slots.trigger" :as-child="true">
            <slot name="trigger" />
        </SheetTrigger>

        <slot name="status" />

        <SheetContent
            data-slot="filter-sheet"
            side="bottom"
            class="max-h-[calc(100dvh-1rem)] overflow-y-auto rounded-t-feature"
        >
            <SheetHeader>
                <SheetTitle>{{ props.title }}</SheetTitle>
                <SheetDescription>{{ props.description }}</SheetDescription>
            </SheetHeader>

            <div class="grid gap-5 px-4 py-5 sm:px-6">
                <slot />
            </div>

            <SheetFooter
                v-if="hasActions"
                :class="
                    props.clearLabel && props.applyLabel
                        ? 'grid grid-cols-2 gap-2 sm:grid-cols-2'
                        : 'grid grid-cols-1 gap-2'
                "
            >
                <Button
                    v-if="props.clearLabel"
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-12 pointer-coarse:min-h-13"
                    :disabled="props.clearDisabled"
                    :loading="props.processing"
                    :loading-label="props.clearLabel"
                    @click="emit('clear')"
                >
                    {{ props.clearLabel }}
                </Button>
                <Button
                    v-if="props.applyLabel"
                    type="button"
                    size="lg"
                    class="min-h-12 pointer-coarse:min-h-13"
                    :disabled="props.applyDisabled"
                    :loading="props.processing"
                    :loading-label="props.applyLabel"
                    @click="emit('apply')"
                >
                    {{ props.applyLabel }}
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
