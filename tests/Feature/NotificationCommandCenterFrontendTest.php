<?php

declare(strict_types=1);

test('notification command center uses typed component boundaries and narrow visits', function () {
    $page = file_get_contents(resource_path('js/pages/notifications/Index.vue'));

    expect($page)
        ->toContain('NotificationFilters')
        ->toContain('NotificationFeed')
        ->toContain('router.cancelAll()')
        ->toContain("only: ['notifications', 'stats', 'filters', 'today']")
        ->toContain("from '@/routes/notifications'")
        ->not->toContain("searchable.includes('remind')")
        ->not->toContain('notification.data.title');
});

test('notification controls and rows expose complete accessible states', function () {
    $filters = file_get_contents(resource_path('js/components/notification/NotificationFilters.vue'));
    $feed = file_get_contents(resource_path('js/components/notification/NotificationFeed.vue'));
    $row = file_get_contents(resource_path('js/components/notification/NotificationRow.vue'));

    expect($filters)
        ->toContain('role="group"')
        ->toContain('aria-pressed')
        ->toContain('min-h-11')
        ->toContain('aria-live="polite"')
        ->toContain('motion-reduce:transition-none')
        ->and($feed)
        ->toContain('tabindex="-1"')
        ->toContain('NotificationRow')
        ->not->toContain('aria-live')
        ->and($row)
        ->toContain('data-notification-row')
        ->toContain('unread_status')
        ->toContain('focus-visible:ring');
});

test('notification translations cover filters groups states and plurals', function (string $locale) {
    $translations = require lang_path("{$locale}/workspace.php");

    expect($translations['notifications'])->toHaveKeys([
        'status_label',
        'read_tab',
        'kind_label',
        'all_kinds',
        'reminders_kind',
        'updates_kind',
        'today_group',
        'earlier_group',
        'clear_filters',
        'active_filters_status',
        'result_summary_one',
        'result_summary_few',
        'result_summary_many',
        'result_summary_other',
        'empty_read_title',
        'empty_reminders_title',
        'empty_updates_title',
    ]);
})->with(['en', 'lt', 'ru']);
