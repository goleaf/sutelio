<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<{
    as?: 'div' | 'h2' | 'h3' | 'h4'
    class?: HTMLAttributes["class"]
  }>(),
  {
    as: 'h3',
  },
)
</script>

<template>
  <component
    :is="props.as"
    data-slot="card-title"
    :class="cn('leading-none font-semibold', props.class)"
  >
    <slot />
  </component>
</template>
