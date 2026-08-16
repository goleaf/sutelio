import assert from 'node:assert/strict';
import test from 'node:test';
import {
    dataSafetyPluralForm,
    formatDataSize,
    hasSuccessfulHttpResponse,
    importStage,
} from './settings/data/data-safety.ts';

test('data safety import state follows select preview review confirm order', () => {
    assert.equal(
        importStage({
            previewing: false,
            importing: false,
            hasPreview: false,
        }),
        'select',
    );
    assert.equal(
        importStage({
            previewing: true,
            importing: false,
            hasPreview: false,
        }),
        'previewing',
    );
    assert.equal(
        importStage({
            previewing: false,
            importing: false,
            hasPreview: true,
        }),
        'review',
    );
    assert.equal(
        importStage({
            previewing: false,
            importing: true,
            hasPreview: true,
        }),
        'importing',
    );
});

test('data safety sizes use binary units and injected locale formatting', () => {
    const formatNumber = (
        value: number,
        options: Intl.NumberFormatOptions = {},
    ) => new Intl.NumberFormat('en-US', options).format(value);

    assert.equal(formatDataSize(0, formatNumber), '0 B');
    assert.equal(formatDataSize(1536, formatNumber), '1.5 KB');
    assert.equal(formatDataSize(10 * 1024 * 1024, formatNumber), '10 MB');
    assert.equal(formatDataSize(-1, formatNumber), '0 B');
});

test('data safety counts select locale-aware plural forms', () => {
    assert.equal(dataSafetyPluralForm(1, 'en-US'), 'one');
    assert.equal(dataSafetyPluralForm(2, 'lt-LT'), 'few');
    assert.equal(dataSafetyPluralForm(10, 'lt-LT'), 'other');
    assert.equal(dataSafetyPluralForm(21, 'ru-RU'), 'one');
    assert.equal(dataSafetyPluralForm(24, 'ru-RU'), 'few');
    assert.equal(dataSafetyPluralForm(25, 'ru-RU'), 'many');
});

test('data safety accepts standalone HTTP results only when validation succeeded', () => {
    const response = { preview: { projects: 1, todos: 2 } };

    assert.equal(hasSuccessfulHttpResponse(response, false), true);
    assert.equal(hasSuccessfulHttpResponse(response, true), false);
    assert.equal(hasSuccessfulHttpResponse(undefined, false), false);
    assert.equal(hasSuccessfulHttpResponse(null, false), false);
});
