import assert from 'node:assert/strict';
import test from 'node:test';
import {
    mergeOnboardingDraft,
    onboardingPercent,
    onboardingPluralForm,
    orderedOnboardingSteps,
} from './onboarding/onboarding-types.ts';

test('guided onboarding keeps the stable eight-step order and honest percentages', () => {
    assert.deepEqual(orderedOnboardingSteps, [
        'welcome',
        'preferences',
        'workspace',
        'project',
        'task',
        'product_map',
        'safety',
        'results',
    ]);
    assert.equal(onboardingPercent('welcome'), 13);
    assert.equal(onboardingPercent('results'), 100);
});

test('guided onboarding merges drafts immutably and clears dependent identities', () => {
    const source = {
        workspace_id: 'workspace-one',
        workspace_name: 'Original',
        project_id: 'project-one',
        task_id: 'task-one',
    };
    const renamed = mergeOnboardingDraft(source, {
        workspace_name: 'Changed',
    });
    const switched = mergeOnboardingDraft(source, {
        workspace_id: 'workspace-two',
    });

    assert.deepEqual(source, {
        workspace_id: 'workspace-one',
        workspace_name: 'Original',
        project_id: 'project-one',
        task_id: 'task-one',
    });
    assert.deepEqual(renamed, {
        ...source,
        workspace_name: 'Changed',
    });
    assert.deepEqual(switched, {
        workspace_id: 'workspace-two',
        workspace_name: 'Original',
    });
});

test('guided onboarding result counts use locale-aware plural categories', () => {
    assert.equal(onboardingPluralForm(1, 'en-US'), 'one');
    assert.equal(onboardingPluralForm(2, 'lt-LT'), 'few');
    assert.equal(onboardingPluralForm(10, 'lt-LT'), 'other');
    assert.equal(onboardingPluralForm(21, 'ru-RU'), 'one');
    assert.equal(onboardingPluralForm(24, 'ru-RU'), 'few');
    assert.equal(onboardingPluralForm(25, 'ru-RU'), 'many');
});
