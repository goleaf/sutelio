<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { useForwardProps } from "reka-ui"
import { computed } from "vue"
import { useVueOTPContext } from "vue-input-otp"
import { cn } from "@/lib/utils"

const props = defineProps<{ index: number, class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardProps(delegatedProps)

const context = useVueOTPContext()

const slot = computed(() => context?.value.slots[props.index])
</script>

<template>
  <div
    v-bind="forwarded"
    data-slot="input-otp-slot"
    :data-active="slot?.isActive"
    :class="cn('data-[active=true]:border-orange-500 data-[active=true]:ring-orange-500/20 data-[active=true]:aria-invalid:ring-destructive/20 aria-invalid:border-destructive data-[active=true]:aria-invalid:border-destructive border-input relative flex size-11 items-center justify-center border-y border-r bg-linear-to-br from-background via-orange-50/45 to-orange-100/65 text-sm font-medium shadow-xs transition-[color,background-color,border-color,box-shadow] duration-[var(--motion-feedback)] ease-[var(--ease-emphasized)] outline-none first:rounded-l-xl first:border-l last:rounded-r-xl data-[active=true]:z-10 data-[active=true]:ring-[3px]', props.class)"
  >
    {{ slot?.char }}
    <div v-if="slot?.hasFakeCaret" class="pointer-events-none absolute inset-0 flex items-center justify-center">
      <div class="animate-caret-blink bg-foreground h-4 w-px duration-1000 motion-reduce:animate-none" />
    </div>
  </div>
</template>
