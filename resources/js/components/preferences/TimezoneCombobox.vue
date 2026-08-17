<script setup lang="ts">
import { Check, ChevronsUpDown, Search } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxLabel,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { computed, ref } from 'vue';
import { useUi } from '@/composables/useUi';
import { localizeTimeZoneName } from '@/lib/timezone';
import type { TimeZoneGroup, TimeZoneOption } from '@/types/timezone';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        groups: TimeZoneGroup[];
        id?: string;
        disabled?: boolean;
        invalid?: boolean;
    }>(),
    {
        id: 'timezone',
        disabled: false,
        invalid: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
const { locale, t } = useUi();
const isOpen = ref(false);
const searchTerm = ref('');

const selectedTimezone = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value),
});
const options = computed(() => props.groups.flatMap((group) => group.options));
const localizedGroups = computed<TimeZoneGroup[]>(() =>
    props.groups.map((group) => ({
        ...group,
        options: group.options.map((option) => {
            const label = localizeTimeZoneName(
                option.identifier,
                locale.value,
                option.label,
            );

            return {
                ...option,
                label,
                search_terms: `${option.search_terms} ${label} ${group.label}`,
            };
        }),
    })),
);

function selectedLabel(value: unknown): string {
    if (typeof value !== 'string') {
        return '';
    }

    const option = options.value.find((candidate) => candidate.value === value);

    if (!option) {
        return value;
    }

    const label = localizeTimeZoneName(
        option.identifier,
        locale.value,
        option.label,
    );

    return `${option.offset} · ${label}`;
}

function optionDescription(option: TimeZoneOption): string {
    return `${option.offset} · ${option.identifier}`;
}

function handleOpenChange(value: boolean): void {
    isOpen.value = value;

    if (value) {
        searchTerm.value = '';
    }
}
</script>

<template>
    <ComboboxRoot
        v-model="selectedTimezone"
        :disabled="disabled"
        :open="isOpen"
        open-on-click
        open-on-focus
        @update:open="handleOpenChange"
    >
        <ComboboxAnchor class="relative">
            <Search
                class="pointer-events-none absolute top-1/2 left-3.5 z-10 size-4 -translate-y-1/2 text-muted-foreground"
                aria-hidden="true"
            />
            <ComboboxInput
                v-model="searchTerm"
                :id="id"
                :display-value="selectedLabel"
                :aria-invalid="invalid"
                :placeholder="t('timezones.search_placeholder')"
                class="h-11 w-full min-w-0 rounded-xl border border-input bg-linear-to-br from-background via-orange-50/45 to-orange-100/65 py-2 pr-11 pl-10 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground hover:border-orange-300/70 focus-visible:border-orange-500 focus-visible:ring-[3px] focus-visible:ring-orange-500/20 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 md:text-sm"
            />
            <ComboboxTrigger
                class="absolute top-0 right-0 flex size-11 items-center justify-center rounded-r-xl text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-orange-500/20 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                :aria-label="t('timezones.open')"
            >
                <ChevronsUpDown class="size-4" aria-hidden="true" />
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxPortal>
            <ComboboxContent
                position="popper"
                align="start"
                :side-offset="6"
                class="z-50 flex max-h-[min(22rem,var(--reka-combobox-content-available-height))] w-[var(--reka-combobox-trigger-width)] max-w-[calc(100dvw-1rem)] min-w-[min(22rem,calc(100dvw-1rem))] overflow-hidden rounded-xl border border-border/80 bg-popover text-popover-foreground shadow-[0_18px_50px_-28px_rgba(15,23,42,0.45)] data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0 motion-reduce:data-[state=closed]:animate-none motion-reduce:data-[state=open]:animate-none"
            >
                <ComboboxViewport
                    class="overflow-y-auto overscroll-contain p-1.5"
                >
                    <ComboboxEmpty
                        class="px-4 py-8 text-center text-sm text-muted-foreground"
                    >
                        {{ t('timezones.empty') }}
                    </ComboboxEmpty>

                    <ComboboxGroup
                        v-for="group in localizedGroups"
                        :key="group.key"
                        class="py-1"
                    >
                        <ComboboxLabel
                            class="sticky top-0 z-10 block bg-popover/95 px-2.5 py-2 text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase backdrop-blur-sm"
                        >
                            {{ group.label }}
                        </ComboboxLabel>
                        <ComboboxItem
                            v-for="option in group.options"
                            :key="option.value"
                            :value="option.value"
                            :text-value="option.search_terms"
                            class="relative flex min-h-11 cursor-default scroll-my-1 items-center rounded-lg py-2 pr-9 pl-2.5 text-sm outline-none select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                        >
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-medium">
                                    {{ option.label }}
                                </span>
                                <span
                                    class="block truncate text-xs text-muted-foreground"
                                >
                                    {{ optionDescription(option) }}
                                </span>
                            </span>
                            <ComboboxItemIndicator
                                class="absolute right-2.5 flex size-4 items-center justify-center text-orange-600"
                            >
                                <Check class="size-4" aria-hidden="true" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxViewport>
            </ComboboxContent>
        </ComboboxPortal>
    </ComboboxRoot>
</template>
