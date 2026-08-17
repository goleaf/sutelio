<script setup lang="ts">
import { Check, Languages, LoaderCircle } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import LanguageFlag from '@/components/localization/LanguageFlag.vue';
import LeadingIconHeading from '@/components/shared/LeadingIconHeading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useLanguagePreference } from '@/composables/useLanguagePreference';
import type { SupportedLanguage } from '@/types';

const { form, localization, saveLanguage } = useLanguagePreference();
const selectedLanguage = ref<SupportedLanguage>(localization.value.current);
const selectedPreview = computed(
    () =>
        localization.value.previews[selectedLanguage.value] ??
        localization.value.previews[localization.value.current],
);
const previewCopy = computed(() => selectedPreview.value.copy);

watch(
    () => localization.value.current,
    (language) => {
        selectedLanguage.value = language;
    },
);

function confirmLanguage(): void {
    saveLanguage(selectedLanguage.value);
}
</script>

<template>
    <Dialog :open="localization.requires_selection">
        <DialogContent
            :show-close-button="false"
            class="overflow-hidden p-0 sm:max-w-xl"
            @escape-key-down.prevent
            @pointer-down-outside.prevent
            @interact-outside.prevent
        >
            <div
                class="relative overflow-hidden border-b border-border/70 bg-orange-500/[0.06] px-6 py-7 sm:px-8"
            >
                <span
                    class="absolute -top-16 -right-10 size-40 rounded-full border-[24px] border-orange-500/10 motion-safe:animate-pulse motion-reduce:animate-none"
                    aria-hidden="true"
                />
                <DialogHeader class="relative gap-3 text-left">
                    <LeadingIconHeading>
                        <template #icon>
                            <span
                                class="flex size-12 items-center justify-center rounded-2xl bg-orange-600 text-white shadow-lg shadow-orange-500/20"
                                aria-hidden="true"
                            >
                                <Languages class="size-6" />
                            </span>
                        </template>

                        <DialogTitle class="text-2xl tracking-[-0.035em]">
                            {{ previewCopy.first_run.title }}
                        </DialogTitle>
                        <DialogDescription class="max-w-md leading-6">
                            {{ previewCopy.first_run.description }}
                        </DialogDescription>
                    </LeadingIconHeading>
                </DialogHeader>
            </div>

            <form
                class="grid gap-5 px-6 pb-6 sm:px-8 sm:pb-8"
                @submit.prevent="confirmLanguage"
            >
                <fieldset class="grid gap-2.5">
                    <legend class="sr-only">
                        {{ previewCopy.choose }}
                    </legend>
                    <button
                        v-for="option in localization.options"
                        :key="option.code"
                        type="button"
                        class="group flex min-h-16 w-full items-center gap-4 rounded-2xl border px-4 py-3 text-left transition-all focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transition-none"
                        :class="
                            selectedLanguage === option.code
                                ? 'border-orange-500 bg-orange-500/[0.07] shadow-sm'
                                : 'border-border/80 bg-background hover:border-orange-500/35 hover:bg-orange-500/[0.03]'
                        "
                        :aria-pressed="selectedLanguage === option.code"
                        :disabled="form.processing"
                        @click="selectedLanguage = option.code"
                    >
                        <LanguageFlag :src="option.flag_url" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold">{{
                                option.native_name
                            }}</span>
                            <span class="block text-sm text-muted-foreground">
                                {{
                                    selectedPreview.language_names[option.code]
                                }}
                            </span>
                        </span>
                        <span
                            class="flex size-7 items-center justify-center rounded-full border transition-colors motion-reduce:transition-none"
                            :class="
                                selectedLanguage === option.code
                                    ? 'border-orange-600 bg-orange-600 text-white'
                                    : 'border-border text-transparent'
                            "
                            aria-hidden="true"
                        >
                            <Check class="size-4" />
                        </span>
                    </button>
                </fieldset>

                <p
                    v-if="form.errors.language"
                    class="text-sm text-destructive"
                    role="alert"
                    aria-live="polite"
                >
                    {{ form.errors.language }}
                </p>

                <Button
                    type="submit"
                    size="lg"
                    class="min-h-11 w-full"
                    :disabled="form.processing"
                >
                    <LoaderCircle
                        v-if="form.processing"
                        class="size-4 animate-spin motion-reduce:animate-none"
                        aria-hidden="true"
                    />
                    <Languages v-else class="size-4" aria-hidden="true" />
                    {{
                        form.processing
                            ? previewCopy.saving
                            : previewCopy.continue
                    }}
                </Button>
            </form>
        </DialogContent>
    </Dialog>
</template>
