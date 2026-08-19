import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const createForm = readFileSync(
    new URL('./task/TaskCreateForm.vue', import.meta.url),
    'utf8',
);
const overviewPanel = readFileSync(
    new URL('./task/TaskOverviewPanel.vue', import.meta.url),
    'utf8',
);
const taskList = readFileSync(
    new URL('./task/TaskList.vue', import.meta.url),
    'utf8',
);
const taskIndex = readFileSync(
    new URL('../pages/tasks/Index.vue', import.meta.url),
    'utf8',
);

test('todo list displays title completion priority and due date', () => {
    assert.match(taskList, /todo\.title/);
    assert.match(taskList, /todo\.is_completed/);
    assert.match(
        taskList,
        /todo\.priority_definition\?\.name \?\? todo\.priority/,
    );
    assert.match(taskList, /todo\.due_date/);
});

test('todo create UI submits title priority and due date', () => {
    assert.match(createForm, /title:/);
    assert.match(createForm, /priority:/);
    assert.match(createForm, /due_date:/);
    assert.match(createForm, /form\.submit\(store\(props\.workspaceId\)/);
});

test('todo edit UI submits title priority and due date', () => {
    assert.match(overviewPanel, /form\.title/);
    assert.match(overviewPanel, /form\.priority/);
    assert.match(overviewPanel, /form\.due_date/);
    assert.match(overviewPanel, /form\.put/);
});

test('todo UI exposes completion and deletion mutations', () => {
    assert.match(taskList, /emit\('toggleCompletion', todo\)/);
    assert.match(taskIndex, /router\.delete\(destroy\(todo\)\.url/);
});

test('todo list uses the shared overdue rule and localized overdue text', () => {
    assert.match(taskList, /isTodoOverdue/);
    assert.match(taskList, /t\('tasks\.index\.due_overdue'/);
    assert.doesNotMatch(taskList, /`\$\{t\([^)]*overdue[^)]*\)\}/);
});
