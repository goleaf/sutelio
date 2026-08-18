<script setup lang="ts">
import { Check } from '@lucide/vue';
import { projectIconOptions } from '@/components/project/project-icons';
import ProjectIcon from '@/components/project/ProjectIcon.vue';
import { useWorkspaceUi } from '@/composables/useWorkspaceUi';

withDefaults(
    defineProps<{
        label: string;
        disabled?: boolean;
        invalid?: boolean;
    }>(),
    {
        disabled: false,
        invalid: false,
    },
);

const icon = defineModel<string>({ required: true });
const { copy } = useWorkspaceUi();
</script>

<template>
    <div
        data-slot="project-icon-picker"
        role="group"
        :aria-label="label"
        :aria-invalid="invalid"
        class="grid grid-cols-2 gap-2 sm:grid-cols-4"
    >
        <button
            v-for="option in projectIconOptions"
            :key="option.value"
            type="button"
            data-slot="project-icon-option"
            :data-project-icon="option.value"
            :class="[
                'relative flex min-h-12 min-w-0 cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-left transition-[background-color,border-color,box-shadow,color] focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60 motion-reduce:transition-none forced-colors:border-[ButtonText] pointer-coarse:min-h-13',
                icon === option.value
                    ? 'border-orange-500/50 bg-orange-500/10 text-orange-800 shadow-sm ring-1 ring-orange-500/20'
                    : 'border-border/80 bg-background text-muted-foreground hover:border-orange-500/25 hover:bg-orange-500/[0.04] hover:text-foreground',
            ]"
            :disabled="disabled"
            :aria-label="copy.projects[option.labelKey]"
            :aria-pressed="icon === option.value"
            :title="copy.projects[option.labelKey]"
            @click="icon = option.value"
        >
            <ProjectIcon :value="option.value" class="size-5 shrink-0" />
            <span
                class="min-w-0 text-[0.9375rem] leading-5 font-medium wrap-anywhere"
            >
                {{ copy.projects[option.labelKey] }}
            </span>
            <Check
                v-if="icon === option.value"
                class="absolute top-1.5 right-1.5 size-3.5 text-orange-700 forced-colors:text-[Highlight]"
                aria-hidden="true"
            />
        </button>
    </div>
</template>
