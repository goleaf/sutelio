import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const taskList = readFileSync(
    new URL('./TaskList.vue', import.meta.url),
    'utf8',
);

test('task list displays completion, priority, and due-date state', () => {
    assert.match(taskList, /todo\.is_completed/);
    assert.match(
        taskList,
        /todo\.priority_definition\?\.name \?\? todo\.priority/,
    );
    assert.match(taskList, /todo\.due_date/);
});

test('task list uses the shared overdue rule and localized overdue text', () => {
    assert.match(taskList, /isTodoOverdue/);
    assert.match(taskList, /t\('tasks\.stats\.overdue'\)/);
    assert.doesNotMatch(taskList, /'Overdue · '/);
});
