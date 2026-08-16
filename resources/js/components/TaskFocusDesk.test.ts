import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import type { TodoFilters } from '../types/api.ts';
import {
    activeTaskFilterCount,
    clearTaskFilters,
    mergeTaskFilterState,
    restoreTaskFocus,
    taskPluralForm,
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

test('visible task controls preserve URL-backed filters they do not render', () => {
    assert.deepEqual(
        mergeTaskFilterState(
            {
                ...defaults,
                assigned_to: 'member-id',
                due_date_from: '2026-08-01',
                label_id: 'label-id',
                tag_id: 'tag-id',
            },
            { overdue: true, view: 'board' },
        ),
        {
            ...defaults,
            assigned_to: 'member-id',
            due_date_from: '2026-08-01',
            label_id: 'label-id',
            overdue: true,
            tag_id: 'tag-id',
            view: 'board',
        },
    );
});

test('active task filter counts select locale-aware plural forms', () => {
    assert.equal(taskPluralForm(1, 'en-US'), 'one');
    assert.equal(taskPluralForm(2, 'lt-LT'), 'few');
    assert.equal(taskPluralForm(10, 'lt-LT'), 'other');
    assert.equal(taskPluralForm(21, 'ru-RU'), 'one');
    assert.equal(taskPluralForm(24, 'ru-RU'), 'few');
    assert.equal(taskPluralForm(25, 'ru-RU'), 'many');
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
        new URL('../pages/tasks/Index.vue', import.meta.url),
        'utf8',
    );

    assert.match(pagination, /@before="preventWhileProcessing"/);
    assert.match(pagination, /@start="emit\('navigate', true\)"/);
    assert.match(pagination, /@finish="emit\('navigate', false\)"/);
    assert.match(panel, /@navigate="emit\('navigate', \$event\)"/);
    assert.match(page, /@navigate="handlePagination"/);
    assert.match(page, /filtering\.value = processing/);
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
