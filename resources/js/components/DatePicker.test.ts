import assert from 'node:assert/strict';
import test from 'node:test';
import { parseDate, parseDateTime } from '@internationalized/date';
import {
    parseDatePickerValue,
    replaceDatePickerDate,
    serializeDatePickerValue,
} from './ui/date-picker/date-picker-value.ts';

test('date picker values preserve the server date contract', () => {
    const value = parseDatePickerValue('2026-08-18', 'day');

    assert.ok(value);
    assert.equal(serializeDatePickerValue(value, 'day'), '2026-08-18');
});

test('date and time picker values preserve the minute precision contract', () => {
    const value = parseDatePickerValue('2026-08-18T14:30', 'minute');

    assert.ok(value);
    assert.equal(serializeDatePickerValue(value, 'minute'), '2026-08-18T14:30');
});

test('date picker parsing rejects malformed or mismatched values safely', () => {
    assert.equal(parseDatePickerValue('', 'day'), undefined);
    assert.equal(parseDatePickerValue('2026-02-31', 'day'), undefined);
    assert.equal(parseDatePickerValue('2026-08-18T14:30', 'day'), undefined);
    assert.equal(parseDatePickerValue('2026-08-18', 'minute'), undefined);
});

test('today replaces the date while preserving a selected time', () => {
    const selected = parseDateTime('2026-08-18T14:30');
    const currentDate = parseDate('2026-08-21');

    assert.equal(
        serializeDatePickerValue(
            replaceDatePickerDate(selected, currentDate, 'minute'),
            'minute',
        ),
        '2026-08-21T14:30',
    );
    assert.equal(
        serializeDatePickerValue(
            replaceDatePickerDate(undefined, currentDate, 'minute'),
            'minute',
        ),
        '2026-08-21T09:00',
    );
    assert.equal(
        serializeDatePickerValue(
            replaceDatePickerDate(selected, currentDate, 'day'),
            'day',
        ),
        '2026-08-21',
    );
});
