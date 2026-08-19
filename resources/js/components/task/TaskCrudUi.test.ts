import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const createForm = readFileSync(
    new URL('./TaskCreateForm.vue', import.meta.url),
    'utf8',
);
const overviewPanel = readFileSync(
    new URL('./TaskOverviewPanel.vue', import.meta.url),
    'utf8',
);
const taskList = readFileSync(
    new URL('./TaskList.vue', import.meta.url),
    'utf8',
);
const taskIndex = readFileSync(
    new URL('../../pages/tasks/Index.vue', import.meta.url),
    'utf8',
);

test('todo UI submits title priority and due date when creating a task', () => {
    assert.match(createForm, /title:/);
    assert.match(createForm, /priority:/);
    assert.match(createForm, /due_date:/);
    assert.match(createForm, /form\.submit\(store\(props\.workspaceId\)/);
    assert.match(createForm, /described-by/);
    assert.match(createForm, /id="task-due-date-error"/);
});

test('todo UI supports editing title priority and due date', () => {
    assert.match(overviewPanel, /form\.title/);
    assert.match(overviewPanel, /form\.priority/);
    assert.match(overviewPanel, /form\.due_date/);
    assert.match(overviewPanel, /form\.put/);
});

test('todo UI supports completion and deletion mutations', () => {
    assert.match(taskList, /todo\.is_completed/);
    assert.match(taskList, /emit\('toggleCompletion', todo\)/);
    assert.match(taskIndex, /router\.delete\(destroy\(todo\)\.url/);
});
