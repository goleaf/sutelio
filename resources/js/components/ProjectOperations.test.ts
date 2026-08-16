import assert from 'node:assert/strict';
import test from 'node:test';
import {
    buildProjectQuery,
    countProjectFilters,
    hasProjectFilters,
    isProjectTaskOverdue,
    projectAttentionContinuation,
    projectResultPluralForm,
    projectTaskMatchesFilters,
    sortProjectTasks,
} from './project/project-operations.ts';
import type { ProjectFilters } from './project/project-operations.ts';

test('project filters omit default values from the URL', () => {
    const filters: ProjectFilters = {
        search: '',
        status: null,
        priority: null,
        assignee: null,
        attention: 'all',
        sort: 'position',
    };

    assert.deepEqual(buildProjectQuery(filters), {});
    assert.equal(hasProjectFilters(filters), false);
    assert.equal(countProjectFilters(filters), 0);
});

test('project filters produce a stable shareable URL query', () => {
    const filters: ProjectFilters = {
        search: 'launch review',
        status: '0198db17-d802-7123-97bc-b0d39c53fca0',
        priority: '0198db17-d802-7123-97bc-b0d39c53fca1',
        assignee: '0198db17-d802-7123-97bc-b0d39c53fca2',
        attention: 'due_soon',
        sort: 'due_date',
    };

    assert.deepEqual(buildProjectQuery(filters), {
        search: 'launch review',
        status: '0198db17-d802-7123-97bc-b0d39c53fca0',
        priority: '0198db17-d802-7123-97bc-b0d39c53fca1',
        assignee: '0198db17-d802-7123-97bc-b0d39c53fca2',
        attention: 'due_soon',
        sort: 'due_date',
    });
    assert.equal(hasProjectFilters(filters), true);
    assert.equal(countProjectFilters(filters), 6);
});

test('project result counts select locale-aware plural forms', () => {
    assert.equal(projectResultPluralForm(1, 'en-US'), 'one');
    assert.equal(projectResultPluralForm(2, 'lt-LT'), 'few');
    assert.equal(projectResultPluralForm(10, 'lt-LT'), 'other');
    assert.equal(projectResultPluralForm(21, 'ru-RU'), 'one');
    assert.equal(projectResultPluralForm(24, 'ru-RU'), 'few');
    assert.equal(projectResultPluralForm(25, 'ru-RU'), 'many');
});

test('project overdue state uses the server preference date boundary', () => {
    assert.equal(isProjectTaskOverdue('2026-08-15', false, '2026-08-16'), true);
    assert.equal(
        isProjectTaskOverdue('2026-08-16', false, '2026-08-16'),
        false,
    );
    assert.equal(isProjectTaskOverdue('2026-08-15', true, '2026-08-16'), false);
    assert.equal(isProjectTaskOverdue(null, false, '2026-08-16'), false);
});

test('attention continuation opens the category that still has hidden work', () => {
    const overdueTasks = Array.from({ length: 5 }, () => ({
        due_date: '2026-08-15',
        is_completed: false,
    }));

    assert.equal(
        projectAttentionContinuation(
            { overdue: 6 },
            overdueTasks,
            '2026-08-16',
        ),
        'overdue',
    );
    assert.equal(
        projectAttentionContinuation(
            { overdue: 5 },
            overdueTasks,
            '2026-08-16',
        ),
        'due_soon',
    );
});

test('updated tasks leave queues whose active filters they no longer match', () => {
    const task = {
        id: 'task-1',
        title: 'Launch review',
        description: 'Confirm release readiness',
        status_id: 'status-done',
        priority_id: 'priority-high',
        assigned_to: 'user-1',
        completed_at: '2026-08-16T08:00:00.000000Z',
        due_date: '2026-08-15',
    };
    const filters: ProjectFilters = {
        search: 'release',
        status: null,
        priority: 'priority-high',
        assignee: 'user-1',
        attention: 'overdue',
        sort: 'position',
    };

    assert.equal(projectTaskMatchesFilters(task, filters, '2026-08-16'), false);
    assert.equal(
        projectTaskMatchesFilters(
            { ...task, completed_at: null },
            filters,
            '2026-08-16',
        ),
        true,
    );
    assert.equal(
        projectTaskMatchesFilters(
            { ...task, completed_at: null },
            { ...filters, search: 'review confirm', attention: 'all' },
            '2026-08-16',
        ),
        false,
    );
    assert.equal(
        projectTaskMatchesFilters(
            { ...task, completed_at: null },
            { ...filters, search: '%', attention: 'all' },
            '2026-08-16',
        ),
        true,
    );
});

test('updated tasks are re-sorted within the loaded queue', () => {
    const tasks = [
        {
            id: 'task-later',
            due_date: '2026-08-20',
            position: 1,
            updated_at: '2026-08-15T09:00:00Z',
            priority_definition: { position: 2 },
        },
        {
            id: 'task-sooner',
            due_date: '2026-08-17',
            position: 2,
            updated_at: '2026-08-16T09:00:00Z',
            priority_definition: { position: 1 },
        },
    ];

    assert.deepEqual(
        sortProjectTasks(tasks, 'due_date').map((task) => task.id),
        ['task-sooner', 'task-later'],
    );
    assert.deepEqual(
        sortProjectTasks(tasks, 'updated').map((task) => task.id),
        ['task-sooner', 'task-later'],
    );
    assert.deepEqual(
        sortProjectTasks(tasks, 'priority').map((task) => task.id),
        ['task-sooner', 'task-later'],
    );
});
