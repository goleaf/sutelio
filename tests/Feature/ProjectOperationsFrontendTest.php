<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

test('project operations use scoped Wayfinder visits and manual result loading', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));
    $queue = File::get(resource_path('js/components/project/ProjectTaskQueue.vue'));

    expect($page)
        ->toContain("import ProjectOperationsHeader from '@/components/project/ProjectOperationsHeader.vue'")
        ->toContain("import ProjectPulse from '@/components/project/ProjectPulse.vue'")
        ->toContain("import ProjectTaskFilters from '@/components/project/ProjectTaskFilters.vue'")
        ->toContain("import ProjectTaskQueue from '@/components/project/ProjectTaskQueue.vue'")
        ->toContain("from '@/routes/projects'")
        ->toContain('router.cancelAll()')
        ->toContain("only: ['todos', 'filters']")
        ->toContain("reset: ['todos']")
        ->toContain('hiddenTaskIds')
        ->and($queue)
        ->toContain('InfiniteScroll')
        ->toContain('manual')
        ->toContain('EmptyState');
});

test('project filters expose accessible desktop and mobile controls', function () {
    $filters = File::get(resource_path('js/components/project/ProjectTaskFilters.vue'));

    expect($filters)
        ->toContain('Sheet')
        ->toContain('Select')
        ->toContain('type="search"')
        ->toContain('min-h-11')
        ->toContain('aria-pressed')
        ->toContain('aria-live')
        ->toContain('motion-reduce:transition-none');
});

test('project header and pulse communicate state without color alone', function () {
    $header = File::get(resource_path('js/components/project/ProjectOperationsHeader.vue'));
    $pulse = File::get(resource_path('js/components/project/ProjectPulse.vue'));
    $sharedHeader = File::get(resource_path('js/components/shared/WorkspacePageHeader.vue'));

    expect($header)
        ->toContain('DropdownMenu')
        ->toContain('processingAction')
        ->toContain('project.is_archived')
        ->toContain("t('projects.show.archived')")
        ->toContain('min-h-11')
        ->and($sharedHeader)
        ->toContain('$slots.back')
        ->toContain('$slots.badges')
        ->and($pulse)
        ->toContain('role="progressbar"')
        ->toContain('aria-valuenow')
        ->toContain('priorityDistribution')
        ->toContain('metrics.overdue')
        ->toContain('metrics.due_soon')
        ->toContain('metrics.unassigned')
        ->toContain('motion-reduce:transition-none');
});

test('project queue preserves loaded task context and archived safeguards', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));
    $queue = File::get(resource_path('js/components/project/ProjectTaskQueue.vue'));

    expect($page)
        ->toContain(':archived="project.is_archived"')
        ->toContain('@clear="clearFilters"')
        ->and($queue)
        ->toContain('task.labels.slice(0, 2)')
        ->toContain('archived')
        ->toContain("emit('clear')");
});

test('project operations place pulse context before the queue on small screens', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));

    expect(strpos($page, '<ProjectPulse'))->toBeLessThan(strpos($page, '<ProjectTaskQueue'));
});

test('project operations preserve detail focus origin and merged page context', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));
    $refresh = Str::betweenFirst(
        $page,
        'function refreshOperations',
        'async function selectTodo',
    );

    expect($page)
        ->toContain('if (selectedTodo.value === null)')
        ->toContain('taskDetailTrigger.value?.isConnected')
        ->toContain('queueFallbackRef.value')
        ->toContain('hiddenTaskIds')
        ->toContain('taskOverrides')
        ->toContain('pendingTotalAdjustmentIds')
        ->toContain('() => props.todos')
        ->toContain('projectTaskMatchesFilters')
        ->toContain('sortProjectTasks')
        ->toContain('synchronizeTask(response.data)')
        ->toContain('preserveScroll: true')
        ->and($refresh)
        ->not->toContain('reset:');
});

test('project operations translations cover filters results and project pulse', function (string $locale) {
    $translations = require lang_path("{$locale}/ui.php");
    $copy = data_get($translations, 'projects.show');

    expect($copy)
        ->toBeArray()
        ->toHaveKeys([
            'actions',
            'attention',
            'filters',
            'loading',
            'metrics',
            'pagination',
            'pulse',
            'results',
        ])
        ->and(data_get($copy, 'results.summary_one'))->toBeString()->not->toBeEmpty()
        ->and(data_get($copy, 'results.summary_few'))->toBeString()->not->toBeEmpty()
        ->and(data_get($copy, 'results.summary_many'))->toBeString()->not->toBeEmpty()
        ->and(data_get($copy, 'results.summary_other'))->toBeString()->not->toBeEmpty();
})->with(['en', 'lt', 'ru']);
