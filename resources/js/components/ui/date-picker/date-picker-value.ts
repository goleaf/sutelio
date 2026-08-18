import {
    CalendarDateTime,
    parseDate,
    parseDateTime,
    type CalendarDate,
    type DateValue,
} from '@internationalized/date';

export type DatePickerGranularity = 'day' | 'minute';

const datePattern = /^\d{4}-\d{2}-\d{2}$/;
const dateTimePattern = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;

export function parseDatePickerValue(
    value: string,
    granularity: DatePickerGranularity,
): DateValue | undefined {
    if (
        (granularity === 'day' && !datePattern.test(value)) ||
        (granularity === 'minute' && !dateTimePattern.test(value))
    ) {
        return undefined;
    }

    try {
        return granularity === 'day'
            ? parseDate(value)
            : parseDateTime(value);
    } catch {
        return undefined;
    }
}

export function serializeDatePickerValue(
    value: DateValue,
    granularity: DatePickerGranularity,
): string {
    const serialized = value.toString();

    return granularity === 'day'
        ? serialized.slice(0, 10)
        : serialized.slice(0, 16);
}

export function replaceDatePickerDate(
    value: DateValue | undefined,
    date: CalendarDate,
    granularity: DatePickerGranularity,
): DateValue {
    if (granularity === 'day') {
        return date;
    }

    const hour = value instanceof CalendarDateTime ? value.hour : 9;
    const minute = value instanceof CalendarDateTime ? value.minute : 0;

    return new CalendarDateTime(
        date.year,
        date.month,
        date.day,
        hour,
        minute,
    );
}
