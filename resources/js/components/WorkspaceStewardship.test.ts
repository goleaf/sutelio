import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

function source(relativePath: string): string {
    try {
        return readFileSync(new URL(relativePath, import.meta.url), 'utf8');
    } catch {
        return '';
    }
}

async function stewardshipModule() {
    try {
        return await import('./workspace/workspace-stewardship.ts');
    } catch {
        assert.fail('workspace stewardship helpers must exist');
    }
}

const workspaces = [
    {
        id: 'beta',
        name: 'Beta Studio',
        description: 'Design systems',
        created_at: '2026-08-15T10:00:00Z',
        members_count: 2,
        projects_count: 3,
        todos_count: 8,
        owner: { name: 'Alice' },
    },
    {
        id: 'alpha',
        name: 'Alpha Operations',
        description: 'Launch operations',
        created_at: '2026-08-16T10:00:00Z',
        members_count: 4,
        projects_count: 5,
        todos_count: 13,
        owner: { name: 'Demo User' },
    },
];

test('workspace portfolio helpers filter, sort, total, and preserve immutable inputs', async () => {
    const helpers = await stewardshipModule();
    const originalOrder = workspaces.map((workspace) => workspace.id);

    const visible = helpers.filterAndSortWorkspacePortfolio(
        workspaces,
        'operations',
        'name_desc',
        'en',
    );

    assert.deepEqual(
        visible.map((workspace) => workspace.id),
        ['alpha'],
    );
    assert.deepEqual(helpers.workspacePortfolioTotals(workspaces), {
        workspaces: 2,
        members: 6,
        projects: 8,
        tasks: 21,
    });
    assert.deepEqual(
        workspaces.map((workspace) => workspace.id),
        originalOrder,
    );
});

test('workspace taxonomy helpers expose only supported sections and complete counts', async () => {
    const helpers = await stewardshipModule();

    assert.equal(helpers.isWorkspaceTaxonomySection('statuses'), true);
    assert.equal(helpers.isWorkspaceTaxonomySection('members'), false);
    assert.deepEqual(
        helpers.workspaceTaxonomyCounts({
            statuses: [{ id: 'open' }, { id: 'done' }],
            priorities: [{ id: 'normal' }],
            labels: [{ id: 'design' }, { id: 'review' }, { id: 'urgent' }],
            tags: [],
        }),
        { statuses: 2, priorities: 1, labels: 3, tags: 0 },
    );
});

test('workspace management uses responsive navigation and progressive disclosure', () => {
    const showPage = source('../pages/workspaces/Show.vue');
    const navigation = source('./workspace/WorkspaceManagementNavigation.vue');
    const responsiveNavigation = source(
        './shared/ResponsiveSectionNavigation.vue',
    );
    const configuration = source('./workspace/WorkspaceConfigurationPanel.vue');

    assert.match(showPage, /WorkspaceManagementNavigation/);
    assert.match(navigation, /ResponsiveSectionNavigation/);
    assert.match(navigation, /prefetch="click"/);
    assert.match(responsiveNavigation, /DropdownMenu/);
    assert.match(responsiveNavigation, /WorkspaceSegmentedControl/);
    assert.match(responsiveNavigation, /aria-current/);
    assert.match(configuration, /activeSection/);
    assert.match(configuration, /WorkspaceTaxonomySwitcher/);
    assert.match(configuration, /nextTick/);
    assert.match(configuration, /activeSectionHeadingId/);
    assert.match(configuration, /focus:ring-2/);
    assert.match(configuration, /activeSection === 'statuses'/);
    assert.match(configuration, /activeSection === 'priorities'/);
    assert.match(configuration, /v-else-if="activeSection === 'labels'"/);
    assert.match(configuration, /v-else-if="activeSection === 'tags'"/);
});

test('workspace portfolio and taxonomy rows hide secondary actions in menus', () => {
    const portfolio = source('../pages/workspaces/Index.vue');
    const definitionCard = source('./workspace/WorkspaceDefinitionCard.vue');
    const configuration = source('./workspace/WorkspaceConfigurationPanel.vue');

    assert.match(portfolio, /workspacePortfolioTotals/);
    assert.match(portfolio, /workspaces\.result_summary/);
    assert.match(portfolio, /workspace\.owner\.name/);
    assert.match(portfolio, /restorePortfolioDialogFocus/);
    assert.match(portfolio, /DropdownMenu/);
    assert.match(definitionCard, /DropdownMenu/);
    assert.match(definitionCard, /workspaces\.actions_label/);
    assert.match(definitionCard, /max-w-\[calc\(100dvw-2rem\)\]/);
    assert.match(definitionCard, /focus:ring-2/);
    assert.match(definitionCard, /break-all/);
    assert.match(configuration, /DropdownMenu/);
    assert.match(configuration, /restoreMetadataDialogFocus/);
});
