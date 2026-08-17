<script setup lang="ts">
import { Building2, Database, ShieldCheck } from '@lucide/vue';
import { computed } from 'vue';

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
              iconClass: 'bg-orange-500/10 text-orange-800 ring-orange-500/15',
              railClass: 'bg-orange-500',
          }
        : {
              icon: Database,
              iconClass: 'bg-amber-500/10 text-amber-800 ring-amber-500/20',
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
        <div class="flex items-start gap-3 pl-1 sm:gap-4">
            <span
                :class="[
                    'flex size-11 shrink-0 items-center justify-center rounded-xl ring-1',
                    presentation.iconClass,
                ]"
            >
                <component
                    :is="presentation.icon"
                    class="size-5"
                    aria-hidden="true"
                />
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p
                        class="text-[11px] font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                    >
                        {{ label }}
                    </p>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-background/80 px-2 py-1 text-[11px] font-medium text-foreground ring-1 ring-border/70"
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
                <p
                    class="mt-1 max-w-3xl text-sm leading-6 text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
        </div>
    </section>
</template>
