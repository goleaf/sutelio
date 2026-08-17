<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DialogActions from '@/components/shared/DialogActions.vue';
import DialogBody from '@/components/shared/DialogBody.vue';
import IconTile from '@/components/shared/IconTile.vue';
import WorkspaceDialogContent from '@/components/shared/WorkspaceDialogContent.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
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
    }>(),
    {
        processing: false,
        destructive: true,
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

const confirmationValue = ref('');
const confirmationMatches = computed(
    () =>
        !props.confirmationText ||
        confirmationValue.value === props.confirmationText,
);

watch(
    () => props.open,
    (open) => {
        if (open) {
            confirmationValue.value = '';
        }
    },
);

function confirm(): void {
    if (confirmationMatches.value) {
        emit('confirm');
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <WorkspaceDialogContent
            :title="title"
            :description="description"
            :close-label="cancelLabel"
            :accent="destructive ? 'red' : 'orange'"
            max-width-class="sm:max-w-md"
        >
            <DialogBody>
                <IconTile
                    :tone="destructive ? 'destructive' : 'brand'"
                    size="md"
                >
                    <slot name="icon">
                        <TriangleAlert />
                    </slot>
                </IconTile>
                <div v-if="confirmationText" class="space-y-2">
                    <Label for="workspace-confirmation-text">
                        {{ confirmationLabel ?? confirmationText }}
                    </Label>
                    <Input
                        id="workspace-confirmation-text"
                        v-model="confirmationValue"
                        :disabled="processing"
                        :autocomplete="'off'"
                    />
                </div>
            </DialogBody>
            <DialogActions>
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
                    type="button"
                    :variant="destructive ? 'destructive' : 'default'"
                    size="lg"
                    :loading="processing"
                    :disabled="processing || !confirmationMatches"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogActions>
        </WorkspaceDialogContent>
    </Dialog>
</template>
