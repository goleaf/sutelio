export type SupportedLanguage = 'en' | 'lt' | 'ru';

export interface LanguageOption {
    code: SupportedLanguage;
    native_name: string;
    localized_name: string;
    flag_url: string;
}

export interface LocalizationCopy {
    choose: string;
    continue: string;
    first_run: {
        description: string;
        title: string;
    };
    saving: string;
    switcher_label: string;
}

export interface LocalizationPreview {
    copy: LocalizationCopy;
    language_names: Record<SupportedLanguage, string>;
}

export interface LocalizationPageProps {
    current: SupportedLanguage;
    requires_selection: boolean;
    options: LanguageOption[];
    previews: Record<SupportedLanguage, LocalizationPreview>;
}
