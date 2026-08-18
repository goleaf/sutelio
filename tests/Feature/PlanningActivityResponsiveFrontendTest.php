<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('dashboard keeps task and productivity summaries readable and wrap safe', function () {
    $page = File::get(resource_path('js/pages/Dashboard.vue'));
    $queue = File::get(resource_path('js/components/dashboard/DashboardTaskQueue.vue'));
    $productivity = File::get(resource_path('js/components/dashboard/ProductivityChart.vue'));

    expect($page)
        ->toContain('text-base leading-7')
        ->and($queue)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('line-clamp-2 text-base')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('min-h-11')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->not->toContain('truncate')
        ->not->toContain('class="hidden sm:inline-flex"')
        ->and($productivity)
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->not->toContain('text-[0.7rem]')
        ->not->toContain('text-[0.65rem]')
        ->not->toContain('truncate');
});

test('calendar keeps direct task access readable through tablet widths', function () {
    $navigator = File::get(resource_path('js/components/calendar/CalendarPeriodNavigator.vue'));
    $month = File::get(resource_path('js/components/calendar/CalendarMonthGrid.vue'));
    $task = File::get(resource_path('js/components/calendar/CalendarTaskItem.vue'));

    expect($navigator)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('min-h-12 text-base pointer-coarse:min-h-13')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('min-h-11')
        ->not->toContain('text-[0.65rem]')
        ->not->toContain('text-sm')
        ->and($month)
        ->toContain('xl:hidden')
        ->toContain('xl:block')
        ->toContain('xl:grid')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('md:hidden')
        ->not->toContain('md:block')
        ->not->toContain('md:grid')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->not->toContain('text-[0.68rem]')
        ->not->toContain('text-[0.65rem]')
        ->and($task)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('line-clamp-2')
        ->toContain('text-[0.9375rem]')
        ->not->toContain('min-h-11')
        ->not->toContain('text-xs')
        ->not->toContain('text-sm')
        ->not->toContain('text-[0.7rem]')
        ->not->toContain('truncate');
});

test('activity filters and timeline use readable copy and full touch targets', function () {
    $page = File::get(resource_path('js/pages/activity/Index.vue'));
    $panel = File::get(resource_path('js/components/activity/ActivityFilterPanel.vue'));
    $fields = File::get(resource_path('js/components/activity/ActivityFilterFields.vue'));
    $timeline = File::get(resource_path('js/components/activity/ActivityTimeline.vue'));
    $emptyState = File::get(resource_path('js/components/shared/EmptyState.vue'));

    foreach ([$page, $panel, $fields, $timeline] as $source) {
        expect($source)
            ->not->toContain('min-h-11')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm')
            ->not->toContain('text-[0.68rem]')
            ->not->toContain('text-[0.65rem]');
    }

    expect($page)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($panel)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($fields)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->and($timeline)
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($emptyState)
        ->toContain('text-[0.9375rem] leading-6')
        ->not->toContain('text-sm');
});

test('notification filters feed and rows expose readable explicit states', function () {
    $filters = File::get(resource_path('js/components/notification/NotificationFilters.vue'));
    $feed = File::get(resource_path('js/components/notification/NotificationFeed.vue'));
    $row = File::get(resource_path('js/components/notification/NotificationRow.vue'));
    $segmentedButton = File::get(resource_path('js/components/shared/WorkspaceSegmentedButton.vue'));

    foreach ([$filters, $feed, $row] as $source) {
        expect($source)
            ->not->toContain('min-h-11')
            ->not->toContain('text-xs')
            ->not->toContain('text-sm')
            ->not->toContain('text-[0.65rem]');
    }

    expect($filters)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($feed)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->and($row)
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('text-base')
        ->toContain('text-[0.9375rem]')
        ->toContain('unread_status')
        ->toContain('read_status')
        ->and($segmentedButton)
        ->toContain('text-neutral-700')
        ->not->toContain("'text-muted-foreground hover:text-foreground'");
});
