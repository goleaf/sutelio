<?php

test('activity intelligence page uses server filters and manual infinite scroll', function () {
    $page = file_get_contents(resource_path('js/pages/activity/Index.vue'));
    $timeline = file_get_contents(resource_path('js/components/activity/ActivityTimeline.vue'));

    expect($page)
        ->toContain('ActivityFilterPanel')
        ->toContain('ActivityTimeline')
        ->toContain('InfiniteScroll')
        ->toContain('manual')
        ->toContain("from '@/routes'")
        ->toContain("from '@/routes/activity'")
        ->toContain("only: ['activities', 'filters']")
        ->toContain("reset: ['activities']")
        ->toContain('router.cancelAll()')
        ->not->toContain('activity.properties')
        ->and($timeline)
        ->toContain('EmptyState');
});

test('activity controls keep desktop and mobile filter experiences accessible', function () {
    $filters = file_get_contents(resource_path('js/components/activity/ActivityFilterPanel.vue'));

    expect($filters)
        ->toContain('aria-pressed')
        ->toContain('Sheet')
        ->toContain('Select')
        ->toContain('min-h-11')
        ->toContain('aria-describedby')
        ->toContain('active_filters_status')
        ->toContain('focus-visible:ring')
        ->toContain('motion-reduce:transition-none');
});

test('activity timeline localizes complete sentences and historical event fallbacks', function () {
    $timeline = file_get_contents(resource_path('js/components/activity/ActivityTimeline.vue'));

    expect($timeline)
        ->toContain('activitySentence')
        ->toContain('sentence_changed')
        ->toContain('event_changed')
        ->toContain('default:');
});

test('activity translations cover the complete filter and event vocabulary', function (string $locale) {
    $translations = require lang_path("{$locale}/workspace.php");

    expect($translations['activity'])
        ->toHaveKeys([
            'category_creation',
            'category_changes',
            'category_completion',
            'category_organization',
            'category_automation',
            'contributor_label',
            'period_label',
            'load_older',
            'active_filters_status',
            'result_summary_one',
            'result_summary_few',
            'result_summary_many',
            'result_summary_other',
            'event_recurrence_generated',
            'sentence_recurrence_generated',
        ]);
})->with(['en', 'lt', 'ru']);

test('lithuanian activity totals use the genitive plural form for ten events', function () {
    $translations = require lang_path('lt/workspace.php');

    expect($translations['activity']['result_summary_other'])
        ->toBe('Užfiksuota :count įvykių');
});
