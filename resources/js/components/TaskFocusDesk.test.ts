import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import type { TodoFilters } from '../types/api.ts';
import {
    activeTaskFilterCount,
    clearTaskFilters,
    restoreTaskFocus,
    toggleTaskFocusFilter,
} from './task/task-focus.ts';

const defaults: TodoFilters = {
    direction: 'asc',
    per_page: 50,
    view: 'list',
};

test('task focus count includes only filters that narrow the result set', () => {
    assert.equal(activeTaskFilterCount(defaults), 0);
    assert.equal(
        activeTaskFilterCount({
            ...defaults,
            search: 'release',
            project_id: 'project-id',
            overdue: true,
            is_favorite: false,
            sort: 'due_date',
        }),
        4,
    );
});

test('task focus toggles return a new filter value without mutating input', () => {
    const filters: TodoFilters = { ...defaults, overdue: true };
    const disabled = toggleTaskFocusFilter(filters, 'overdue');
    const enabled = toggleTaskFocusFilter(filters, 'is_pinned');

    assert.deepEqual(filters, { ...defaults, overdue: true });
    assert.equal(disabled.overdue, undefined);
    assert.equal(enabled.is_pinned, true);
    assert.equal(enabled.overdue, true);
});

test('clearing task filters preserves only presentation defaults', () => {
    assert.deepEqual(
        clearTaskFilters({
            ...defaults,
            view: 'board',
            search: 'release',
            status: 'in_progress',
            is_favorite: true,
        }),
        {
            direction: 'asc',
            per_page: 50,
            view: 'board',
        },
    );
});

test('pagination exits selection mode before visiting another task page', () => {
    const pagination = readFileSync(
        new URL('./task/TaskPagination.vue', import.meta.url),
        'utf8',
    );
    const panel = readFileSync(
        new URL('./task/TaskWorkspacePanel.vue', import.meta.url),
        'utf8',
    );
    const page = readFileSync(
        new URL('../Pages/tasks/Index.vue', import.meta.url),
        'utf8',
    );

    assert.match(pagination, /emit\('navigate'\)/);
    assert.match(panel, /@navigate="emit\('navigate'\)"/);
    assert.match(page, /@navigate="setSelectionMode\(false\)"/);
});

test('task focus restoration prefers a connected origin and falls back safely', () => {
    let focused = '';
    const origin = {
        focus: () => {
            focused = 'origin';
        },
        isConnected: true,
    };
    const fallback = {
        focus: () => {
            focused = 'fallback';
        },
        isConnected: true,
    };

    restoreTaskFocus(origin, fallback);
    assert.equal(focused, 'origin');

    origin.isConnected = false;
    restoreTaskFocus(origin, fallback);
    assert.equal(focused, 'fallback');
});
