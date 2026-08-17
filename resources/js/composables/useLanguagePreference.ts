import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { update } from '@/routes/locale';
import type { SupportedLanguage } from '@/types';

type LanguageSaveCallbacks = {
    onError?: (language: SupportedLanguage) => void;
};

export function useLanguagePreference() {
    const page = usePage();
    const localization = computed(() => page.props.localization);
    const form = useForm<{ language: SupportedLanguage }>({
        language: localization.value.current,
    });

    function isSupportedLanguage(value: string): value is SupportedLanguage {
        return localization.value.options.some(
            (option) => option.code === value,
        );
    }

    function saveLanguage(
        language: string,
        callbacks: LanguageSaveCallbacks = {},
    ): void {
        if (
            !isSupportedLanguage(language) ||
            form.processing ||
            (!localization.value.requires_selection &&
                language === localization.value.current)
        ) {
            return;
        }

        form.language = language;
        form.submit(update(), {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                form.language = localization.value.current;
                callbacks.onError?.(localization.value.current);
            },
        });
    }

    return {
        form,
        localization,
        saveLanguage,
    };
}
