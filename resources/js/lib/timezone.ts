type TimezoneResolver = () => string | undefined;

const resolveSystemTimezone: TimezoneResolver = () =>
    Intl.DateTimeFormat().resolvedOptions().timeZone;

export function detectBrowserTimezone(
    resolveTimezone: TimezoneResolver = resolveSystemTimezone,
): string | null {
    try {
        const timezone = resolveTimezone()?.trim();

        if (!timezone) {
            return null;
        }

        new Intl.DateTimeFormat('en-US', { timeZone: timezone }).format();

        return timezone;
    } catch {
        return null;
    }
}

export function localizeTimeZoneName(
    timezone: string,
    locale: string,
    fallback: string,
): string {
    try {
        const name = new Intl.DateTimeFormat(locale, {
            timeZone: timezone,
            timeZoneName: 'longGeneric',
        })
            .formatToParts(new Date())
            .find((part) => part.type === 'timeZoneName')
            ?.value.trim();

        return name || fallback;
    } catch {
        return fallback;
    }
}
