import assert from 'node:assert/strict';
import test from 'node:test';
import {
    activityPluralForm,
    buildActivityQuery,
    hasActivityFilters,
} from './activity/activity-types.ts';
import type { ActivityFilters } from './activity/activity-types.ts';

test('activity filters omit default values from the URL', () => {
    const filters: ActivityFilters = {
        category: 'all',
        actor: null,
        period: 'all',
    };

    assert.deepEqual(buildActivityQuery(filters), {});
    assert.equal(hasActivityFilters(filters), false);
});

test('activity filters produce a stable shareable URL query', () => {
    const filters: ActivityFilters = {
        category: 'completion',
        actor: '0198db17-d802-7123-97bc-b0d39c53fca0',
        period: '30d',
    };

    assert.deepEqual(buildActivityQuery(filters), {
        category: 'completion',
        actor: '0198db17-d802-7123-97bc-b0d39c53fca0',
        period: '30d',
    });
    assert.equal(hasActivityFilters(filters), true);
});

test('activity result counts select locale-aware plural forms', () => {
    assert.equal(activityPluralForm(1, 'en-US'), 'one');
    assert.equal(activityPluralForm(2, 'lt-LT'), 'few');
    assert.equal(activityPluralForm(10, 'lt-LT'), 'other');
    assert.equal(activityPluralForm(21, 'ru-RU'), 'one');
    assert.equal(activityPluralForm(24, 'ru-RU'), 'few');
    assert.equal(activityPluralForm(25, 'ru-RU'), 'many');
});
