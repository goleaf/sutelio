<script setup lang="ts">
import { Check, ChevronDown, Languages, LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import FirstRunLanguageDialog from '@/components/localization/FirstRunLanguageDialog.vue';
import LanguageFlag from '@/components/localization/LanguageFlag.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useLanguagePreference } from '@/composables/useLanguagePreference';
import { useUi } from '@/composables/useUi';

const { form, localization, saveLanguage } = useLanguagePreference();
const { t } = useUi();
const currentOption = computed(
    () =>
        localization.value.options.find(
            (option) => option.code === localization.value.current,
        ) ?? localization.value.options[0],
);

function handleLanguageChange(value: unknown): void {
    if (typeof value === 'string') {
        saveLanguage(value);
    }
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="outline"
                size="sm"
                class="min-h-11 gap-2 bg-background/90 px-3 shadow-sm"
                :aria-label="t('localization.switcher_label')"
                :disabled="form.processing"
            >
                <LoaderCircle
                    v-if="form.processing"
                    class="size-4 animate-spin motion-reduce:animate-none"
                    aria-hidden="true"
                />
                <LanguageFlag
                    v-else-if="currentOption"
                    :src="currentOption.flag_url"
                />
                <Languages v-else class="size-4" aria-hidden="true" />
                <span class="uppercase">{{ localization.current }}</span>
                <ChevronDown
                    class="size-3.5 text-muted-foreground"
                    aria-hidden="true"
                />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-64">
            <DropdownMenuLabel>
                {{ t('localization.choose') }}
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuRadioGroup
                :model-value="localization.current"
                @update:model-value="handleLanguageChange"
            >
                <DropdownMenuRadioItem
                    v-for="option in localization.options"
                    :key="option.code"
                    :value="option.code"
                    class="min-h-11 gap-3 rounded-lg py-2.5 pl-9"
                >
                    <template #indicator-icon>
                        <Check class="size-4" />
                    </template>
                    <LanguageFlag :src="option.flag_url" />
                    <span class="min-w-0">
                        <span class="block font-medium">{{
                            option.native_name
                        }}</span>
                        <span class="block text-xs text-muted-foreground">
                            {{ option.localized_name }}
                        </span>
                    </span>
                </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>

    <FirstRunLanguageDialog />
</template>
