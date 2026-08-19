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
    $fields = File::get(resource_path('js/components/project/ProjectTaskFilterFields.vue'));

    expect($filters)
        ->toContain('FilterSheet')
        ->toContain('ProjectTaskFilterFields')
        ->toContain('type="search"')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('aria-pressed')
        ->toContain('aria-live')
        ->toContain('motion-reduce:transition-none')
        ->and($fields)
        ->toContain('Select')
        ->toContain('mode?: \'desktop\' | \'mobile\'')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('aria-pressed')
        ->toContain('focus-visible:ring');
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
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
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

test('project operations open canonical task pages and preserve merged queue context', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));
    $refresh = Str::betweenFirst(
        $page,
        'function refreshOperations',
        'function selectTodo',
    );

    expect($page)
        ->toContain("import { create as createTodo, show as showTodo } from '@/routes/todos'")
        ->toContain('router.visit(showTodo(task.id).url)')
        ->not->toContain('selectedTodo')
        ->not->toContain('TaskDetail')
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
