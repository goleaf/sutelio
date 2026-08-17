<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DialogFooter } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <DialogFooter
        data-slot="dialog-actions"
        :class="
            cn(
                'gap-2 border-t border-border/70 px-4 py-4 sm:gap-2 sm:px-8',
                props.class,
            )
        "
    >
        <slot />
    </DialogFooter>
</template>
