import assert from 'node:assert/strict';
import test from 'node:test';
import {
    projectIconOptions,
    resolveProjectIcon,
} from './project/project-icons.ts';

test('project icon registry exposes the curated list in a stable order', () => {
    assert.deepEqual(
        projectIconOptions.map((option) => option.value),
        [
            'folder',
            'briefcase',
            'code',
            'palette',
            'book',
            'star',
            'rocket',
            'globe',
        ],
    );
    assert.equal(
        new Set(projectIconOptions.map((option) => option.value)).size,
        projectIconOptions.length,
    );
});

test('project icon resolver returns the selected component and safely falls back', () => {
    const folder = projectIconOptions[0]?.icon;
    const rocket = projectIconOptions.find(
        (option) => option.value === 'rocket',
    )?.icon;

    assert.ok(folder);
    assert.ok(rocket);
    assert.equal(resolveProjectIcon('rocket'), rocket);
    assert.equal(resolveProjectIcon('legacy-custom-value'), folder);
    assert.equal(resolveProjectIcon(undefined), folder);
});
