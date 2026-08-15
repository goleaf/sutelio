import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const createDialog = readFileSync(
    new URL('./TaskCreateDialog.vue', import.meta.url),
    'utf8',
);
const overviewPanel = readFileSync(
    new URL('./TaskOverviewPanel.vue', import.meta.url),
    'utf8',
);
const taskList = readFileSync(new URL('./TaskList.vue', import.meta.url), 'utf8');
const taskIndex = readFileSync(
    new URL('../../Pages/tasks/Index.vue', import.meta.url),
    'utf8',
);

test('todo UI submits title priority and due date when creating a task', () => {
    assert.match(createDialog, /title:/);
    assert.match(createDialog, /priority:/);
    assert.match(createDialog, /due_date:/);
    assert.match(createDialog, /form\.submit\(store\(props\.workspaceId\)/);
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
