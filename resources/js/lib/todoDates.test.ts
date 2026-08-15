import assert from 'node:assert/strict';
import test from 'node:test';
import { isTodoOverdue } from './todoDates.ts';

test('an incomplete todo is overdue after its due date has ended', () => {
    assert.equal(
        isTodoOverdue('2026-08-14', false, new Date('2026-08-15T00:00:00')),
        true,
    );
});

test('an incomplete todo remains current through the end of its due date', () => {
    assert.equal(
        isTodoOverdue('2026-08-15', false, new Date('2026-08-15T23:59:59')),
        false,
    );
});

test('a completed todo is never overdue', () => {
    assert.equal(
        isTodoOverdue('2026-08-14', true, new Date('2026-08-15T00:00:00')),
        false,
    );
});

test('a todo without a due date is never overdue', () => {
    assert.equal(isTodoOverdue(null, false, new Date('2026-08-15')), false);
});
