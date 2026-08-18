<script setup lang="ts">
import type { CheckboxRootEmits, CheckboxRootProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { Check } from "@lucide/vue"
import { reactiveOmit } from "@vueuse/core"
import { CheckboxIndicator, CheckboxRoot, useForwardPropsEmits } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<CheckboxRootProps & { class?: HTMLAttributes["class"] }>()
const emits = defineEmits<CheckboxRootEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <CheckboxRoot
    v-slot="slotProps"
    data-slot="checkbox"
    v-bind="forwarded"
    :class="
      cn('peer border-input bg-linear-to-br from-background via-orange-50/45 to-orange-100/65 data-[state=checked]:border-orange-700 data-[state=checked]:from-orange-600 data-[state=checked]:via-orange-600 data-[state=checked]:to-orange-700 data-[state=checked]:text-white focus-visible:border-orange-500 focus-visible:ring-orange-500/25 aria-invalid:ring-destructive/20 aria-invalid:border-destructive size-5 shrink-0 cursor-pointer rounded-md border shadow-xs transition-[background-color,border-color,box-shadow] hover:border-orange-400/70 motion-reduce:transition-none outline-none focus-visible:ring-[3px] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 pointer-coarse:size-6',
         props.class)"
  >
    <CheckboxIndicator
      data-slot="checkbox-indicator"
      class="grid place-content-center text-current transition-none"
    >
      <slot v-bind="slotProps">
        <Check class="size-3.5" />
      </slot>
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
