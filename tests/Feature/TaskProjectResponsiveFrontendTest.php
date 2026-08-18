<?php

use Illuminate\Support\Facades\File;

test('task queues keep titles statuses and actions readable on phones and tablets', function () {
    $taskList = File::get(resource_path('js/components/task/TaskList.vue'));
    $board = File::get(resource_path('js/components/task/BoardView.vue'));
    $projectQueue = File::get(resource_path('js/components/project/ProjectTaskQueue.vue'));
    $results = File::get(resource_path('js/components/task/TaskResultsBar.vue'));
    $pagination = File::get(resource_path('js/components/task/TaskPagination.vue'));

    expect($taskList)
        ->toContain('data-slot="task-row"')
        ->toContain('line-clamp-2 text-base')
        ->not->toContain('line-clamp-2 block')
        ->toContain('data-slot="task-row-metadata"')
        ->toContain('col-span-3 row-start-2')
        ->toContain('data-slot="task-status"')
        ->toContain('data-slot="task-checkbox-target"')
        ->toContain('text-[0.9375rem] leading-6')
        ->toContain('text-[0.9375rem]')
        ->toContain('min-h-12 min-w-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('color: statusColor(todo)')
        ->not->toContain('color: priorityColor(todo)')
        ->not->toContain('sm:truncate')
        ->and($board)
        ->toContain('data-slot="task-board"')
        ->toContain('line-clamp-2 text-base')
        ->toContain('text-[0.9375rem]')
        ->and($projectQueue)
        ->toContain('data-slot="project-task-row"')
        ->toContain('line-clamp-2 text-base')
        ->toContain('data-slot="project-task-metadata"')
        ->toContain('data-slot="project-task-status"')
        ->toContain('data-slot="project-task-priority"')
        ->toContain('data-slot="project-task-checkbox-target"')
        ->toContain('text-[0.9375rem]')
        ->toContain('min-h-12 min-w-12')
        ->not->toContain('block truncate text-sm')
        ->not->toContain('hidden max-w-28 truncate')
        ->and($results)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('text-xs')
        ->and($pagination)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13');
});

test('task and project filters expose comfortable mobile actions and active state', function () {
    $taskBar = File::get(resource_path('js/components/task/TaskFilterBar.vue'));
    $taskFields = File::get(resource_path('js/components/task/TaskFilterFields.vue'));
    $projectFilters = File::get(resource_path('js/components/project/ProjectTaskFilters.vue'));
    $projectFields = File::get(resource_path('js/components/project/ProjectTaskFilterFields.vue'));
    $filterSheet = File::get(resource_path('js/components/shared/FilterSheet.vue'));
    $sheetContent = File::get(resource_path('js/components/ui/sheet/SheetContent.vue'));

    expect($taskBar)
        ->toContain('<FilterSheet')
        ->toContain('activeFilterLabel')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->not->toContain('text-xs')
        ->and($taskFields)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->and($projectFilters)
        ->toContain('<FilterSheet')
        ->toContain('activeFilterCount')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->not->toContain('text-xs')
        ->and($projectFields)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->and($filterSheet)
        ->toContain('max-h-[calc(100dvh-1rem)]')
        ->toContain('overflow-y-auto')
        ->toContain('size="lg"')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->and($sheetContent)
        ->toContain('size-12')
        ->toContain('pointer-coarse:size-13')
        ->not->toContain('size-11');
});

test('task detail collaboration and create flows reflow without dense phone rows', function () {
    $checklists = File::get(resource_path('js/components/task/TaskChecklistPanel.vue'));
    $comments = File::get(resource_path('js/components/task/TaskCommentsPanel.vue'));
    $attachments = File::get(resource_path('js/components/task/TaskAttachmentsPanel.vue'));
    $taskDialog = File::get(resource_path('js/components/task/TaskCreateDialog.vue'));
    $projectDialog = File::get(resource_path('js/components/project/ProjectCreateDialog.vue'));
    $dialogSurface = File::get(resource_path('js/components/shared/WorkspaceDialogContent.vue'));

    expect($checklists)
        ->toContain('data-slot="checklist-item-row"')
        ->toContain('data-slot="checklist-item-actions"')
        ->toContain('data-slot="checklist-item-checkbox-target"')
        ->toContain('flex flex-wrap')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('grid-cols-[auto_minmax(0,1fr)_auto_auto_auto]')
        ->not->toContain('class="h-8 text-sm"')
        ->and($comments)
        ->toContain('wrap-anywhere')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('truncate')
        ->and($attachments)
        ->toContain('wrap-anywhere')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('truncate')
        ->and($taskDialog)
        ->toContain('<DialogActions>')
        ->toContain('size="lg"')
        ->toContain('text-base')
        ->not->toContain('text-sm')
        ->and($projectDialog)
        ->toContain('<DialogActions>')
        ->toContain('size="lg"')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('text-sm')
        ->and($dialogSurface)
        ->toContain('max-h-[calc(100dvh-1rem)]')
        ->toContain('overflow-y-auto overscroll-contain')
        ->toContain('size-12')
        ->toContain('pointer-coarse:size-13')
        ->not->toContain('size-11');
});

test('project cards and operations keep readable copy and comfortable actions', function () {
    $index = File::get(resource_path('js/pages/projects/Index.vue'));
    $header = File::get(resource_path('js/components/project/ProjectOperationsHeader.vue'));
    $pulse = File::get(resource_path('js/components/project/ProjectPulse.vue'));

    expect($index)
        ->toContain('data-slot="project-card"')
        ->toContain('text-base leading-7')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->not->toContain('text-[0.68rem]')
        ->not->toContain('text-[0.65rem]')
        ->and($header)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->not->toContain('min-h-11')
        ->and($pulse)
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm');
});
