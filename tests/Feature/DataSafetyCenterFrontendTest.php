<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

test('settings expose the current section on mobile and retain desktop navigation', function () {
    $layout = File::get(resource_path('js/layouts/settings/Layout.vue'));
    $menuPath = resource_path('js/components/settings/SettingsSectionMenu.vue');

    expect($layout)
        ->toContain("import SettingsSectionMenu from '@/components/settings/SettingsSectionMenu.vue'")
        ->toContain('<SettingsSectionMenu')
        ->toContain('lg:block')
        ->and(File::exists($menuPath))->toBeTrue();

    if (! File::exists($menuPath)) {
        return;
    }

    expect(File::get($menuPath))
        ->toContain('DropdownMenuTrigger')
        ->toContain('DropdownMenuItem')
        ->toContain('<Link')
        ->toContain('aria-current')
        ->toContain('min-h-11')
        ->toContain('whitespace-normal')
        ->toContain('break-words')
        ->toContain('motion-reduce:transition-none')
        ->toContain('class="sr-only">{{ openLabel }}</span>')
        ->not->toContain('truncate')
        ->not->toContain(':aria-label="openLabel"');
});

test('workspace data transfer communicates scope and stages every import', function () {
    $page = File::get(resource_path('js/pages/settings/Export.vue'));

    expect($page)
        ->toContain('DataScopeBanner')
        ->toContain('scope="workspace"')
        ->toContain('importStage')
        ->toContain('formatDataSize')
        ->toContain('hasSuccessfulHttpResponse')
        ->toContain('v-if="canImport"')
        ->toContain("t('settings.export.import_limitations')")
        ->toContain("t('settings.export.import_restricted_title')")
        ->toContain('<a')
        ->toContain('exportMethod([props.workspace.id, format.value]).url')
        ->toContain("t('settings.export.steps.select')")
        ->toContain("t('settings.export.steps.review')")
        ->toContain("t('settings.export.steps.confirm')")
        ->toContain('sm:flex-row')
        ->toContain('primaryImportInput.value?.focus()')
        ->toContain('await nextTick()')
        ->toContain('await clearImportSelection(true)')
        ->toContain('selectedImportFile?.name')
        ->toContain('page.props.capabilities.manageDatabaseBackups')
        ->toContain('editBackup.url()')
        ->not->toContain('flex-col-reverse')
        ->not->toContain('<span class="truncate">{{ step }}</span>');
});

test('workspace data transfer handles resolved validation failures before success effects', function () {
    $page = File::get(resource_path('js/pages/settings/Export.vue'));

    expect($page)
        ->toContain('if (!hasSuccessfulHttpResponse')
        ->toContain('response, previewRequest.hasErrors')
        ->toContain('response, importRequest.hasErrors')
        ->toContain('const response = await importRequest.post')
        ->toContain("toast.success(t('settings.export.import_success'))");

    expect(strpos($page, 'response, importRequest.hasErrors'))
        ->toBeLessThan(strpos($page, "toast.success(t('settings.export.import_success'))"))
        ->toBeLessThan(strpos($page, 'await clearImportSelection(true)'));
});

test('operator backups communicate application scope and destructive restore risk', function () {
    $page = File::get(resource_path('js/pages/settings/Backup.vue'));

    expect($page)
        ->toContain('DataScopeBanner')
        ->toContain('scope="application"')
        ->toContain("t('settings.backup.verified')")
        ->toContain("t('settings.backup.restore_risk')")
        ->toContain('formatDataSize')
        ->toContain('<ol')
        ->toContain('<article')
        ->toContain('min-h-11')
        ->toContain(':href="download(backup.id).url"')
        ->not->toContain('backup.filename')
        ->not->toContain('backup.path');
});

test('data safety copy is complete in every supported locale', function (string $locale) {
    $copy = require lang_path("{$locale}/ui.php");
    $keys = [
        'settings.navigation.current_section',
        'settings.navigation.open_sections',
        'settings.export.scope_label',
        'settings.export.scope_description',
        'settings.export.formats.json_description',
        'settings.export.formats.csv_description',
        'settings.export.formats.markdown_description',
        'settings.export.steps.select',
        'settings.export.steps.review',
        'settings.export.steps.confirm',
        'settings.export.file_name',
        'settings.export.file_size',
        'settings.export.file_format',
        'settings.export.import_limitations',
        'settings.export.import_restricted_title',
        'settings.export.import_restricted_description',
        'settings.export.operator_backup_title',
        'settings.export.operator_backup_description',
        'settings.export.operator_backup_action',
        'settings.backup.scope_label',
        'settings.backup.scope_description',
        'settings.backup.verified',
        'settings.backup.restore_risk',
        'settings.backup.inventory_description',
    ];

    $messages = collect(Arr::only(Arr::dot($copy), $keys));

    expect($messages)->toHaveCount(count($keys));

    $messages->each(fn (mixed $message) => expect($message)
        ->toBeString()
        ->not->toBeEmpty());
})->with(['en', 'lt', 'ru']);

test('data safety copy accurately describes round trip limits and Russian backup counts', function () {
    $english = require lang_path('en/ui.php');
    $lithuanian = require lang_path('lt/ui.php');
    $russian = require lang_path('ru/ui.php');

    expect(data_get($english, 'settings.export.formats.json_description'))
        ->not->toContain('complete')
        ->and(data_get($lithuanian, 'settings.export.formats.json_description'))
        ->not->toContain('visai')
        ->and(data_get($russian, 'settings.export.formats.json_description'))
        ->not->toContain('полного')
        ->and(data_get($russian, 'settings.backup.available_few'))
        ->toBe('Доступны :count проверенные резервные копии');
});
