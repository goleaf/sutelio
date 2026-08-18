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
        ->toContain('focusActivityResults')
        ->toContain('data-activity-results-heading')
        ->toContain('requestAnimationFrame(focusActivityResults)')
        ->not->toContain('activity.properties')
        ->and($timeline)
        ->toContain('EmptyState');
});

test('activity controls keep desktop and mobile filter experiences accessible', function () {
    $filters = file_get_contents(resource_path('js/components/activity/ActivityFilterPanel.vue'));
    $fields = file_get_contents(resource_path('js/components/activity/ActivityFilterFields.vue'));

    expect($filters)
        ->toContain('aria-pressed')
        ->toContain('FilterSheet')
        ->toContain('ActivityFilterFields')
        ->toContain('min-h-12')
        ->toContain('pointer-coarse:min-h-13')
        ->toContain('aria-describedby')
        ->toContain('active_filters_status')
        ->toContain('focus-visible:ring')
        ->toContain('motion-reduce:transition-none')
        ->and($fields)
        ->toContain('Select')
        ->toContain('mode?: \'desktop\' | \'mobile\'')
        ->toContain(':aria-label="copy.activity.contributor_label"')
        ->toContain(':aria-label="copy.activity.period_label"');
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
