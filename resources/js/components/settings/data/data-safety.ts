export type ExportFormat = 'csv' | 'json' | 'markdown';
export type ImportFormat = 'csv' | 'json';
export type ImportStage = 'importing' | 'previewing' | 'review' | 'select';
export type DataSafetyPluralForm = 'few' | 'many' | 'one' | 'other';

export function dataSafetyPluralForm(
    count: number,
    locale: string,
): DataSafetyPluralForm {
    const category = new Intl.PluralRules(locale).select(count);

    switch (category) {
        case 'few':
        case 'many':
        case 'one':
            return category;
        default:
            return 'other';
    }
}

export function importStage(state: {
    previewing: boolean;
    importing: boolean;
    hasPreview: boolean;
}): ImportStage {
    if (state.importing) {
        return 'importing';
    }

    if (state.previewing) {
        return 'previewing';
    }

    return state.hasPreview ? 'review' : 'select';
}

export function hasSuccessfulHttpResponse<T>(
    response: T | null | undefined,
    hasErrors: boolean,
): response is T {
    return response != null && !hasErrors;
}

export function formatDataSize(
    bytes: number,
    formatNumber: (value: number, options?: Intl.NumberFormatOptions) => string,
): string {
    const units = ['B', 'KB', 'MB', 'GB'] as const;
    let value = Math.max(0, bytes);
    let unitIndex = 0;

    while (value >= 1024 && unitIndex < units.length - 1) {
        value /= 1024;
        unitIndex += 1;
    }

    return `${formatNumber(value, {
        maximumFractionDigits: value < 10 && unitIndex > 0 ? 1 : 0,
    })} ${units[unitIndex]}`;
}
