<script setup lang="ts">
import { Building2, Database, ShieldCheck } from '@lucide/vue';
import { computed } from 'vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';

type DataScope = 'application' | 'workspace';

const props = defineProps<{
    description: string;
    label: string;
    scope: DataScope;
    title: string;
}>();

const presentation = computed(() =>
    props.scope === 'workspace'
        ? {
              icon: Building2,
              tileTone: 'cobalt' as const,
              railClass: 'bg-orange-500',
          }
        : {
              icon: Database,
              tileTone: 'information' as const,
              railClass: 'bg-amber-500',
          },
);
</script>

<template>
    <section
        :aria-labelledby="`${scope}-data-scope-title`"
        class="relative overflow-hidden rounded-2xl border border-border/80 bg-muted/35 p-4 sm:p-5"
    >
        <span
            :class="['absolute inset-y-0 left-0 w-1.5', presentation.railClass]"
            aria-hidden="true"
        />
        <LeadingIconHeading
            tile
            :tile-tone="presentation.tileTone"
            class="pl-1 sm:gap-4"
            content-class="gap-0"
        >
            <template #icon>
                <component :is="presentation.icon" />
            </template>

            <div class="flex flex-wrap items-center gap-2">
                <p
                    class="text-[0.9375rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-background/80 px-2.5 py-1 text-[0.9375rem] font-medium text-foreground ring-1 ring-border/70"
                >
                    <ShieldCheck class="size-3" aria-hidden="true" />
                    <slot name="status" />
                </span>
            </div>
            <h2
                :id="`${scope}-data-scope-title`"
                class="mt-2 text-base font-semibold text-balance sm:text-lg"
            >
                {{ title }}
            </h2>
            <p class="mt-1 max-w-3xl text-base leading-6 text-muted-foreground">
                {{ description }}
            </p>
        </LeadingIconHeading>
    </section>
</template>
