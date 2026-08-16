<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/** @return array<string, string> */
function firstPartySourceFiles(array $directories, array $extensions): array
{
    $files = [];

    foreach ($directories as $directory) {
        if (! File::isDirectory($directory)) {
            continue;
        }

        foreach (File::allFiles($directory) as $file) {
            if (! in_array($file->getExtension(), $extensions, true)) {
                continue;
            }

            $files[$file->getRelativePathname()] = $file->getContents();
        }
    }

    return $files;
}

test('blade templates remain presentation only', function () {
    $templates = firstPartySourceFiles([resource_path('views')], ['php']);

    expect($templates)->not->toBeEmpty();

    foreach ($templates as $path => $contents) {
        expect($contents, $path)
            ->not->toMatch('/@php\b|@endphp\b|@inject\b/')
            ->not->toMatch('/\{!!/')
            ->not->toMatch('/\b(?:app|resolve)\s*\(/')
            ->not->toMatch('/\b(?:DB|Cache|Gate|Storage|Auth)::/')
            ->not->toMatch('/\b[A-Z][A-Za-z0-9_]*::(?:query|find|where|all|create|update|delete)\s*\(/')
            ->not->toMatch('/<style\b/i')
            ->not->toMatch('/<script\b(?![^>]*\bsrc=)[^>]*>/i');
    }
});

test('the application does not introduce livewire or volt', function () {
    $sources = firstPartySourceFiles(
        [app_path(), base_path('bootstrap'), base_path('routes'), resource_path()],
        ['php', 'ts', 'vue', 'js'],
    );
    $sources['composer.json'] = File::get(base_path('composer.json'));
    $sources['package.json'] = File::get(base_path('package.json'));

    foreach ($sources as $path => $contents) {
        expect($contents, $path)->not->toMatch('/\b(?:livewire|volt)\b/i');
    }
});

test('environment access and debug calls stay outside first party runtime code', function () {
    $sources = firstPartySourceFiles(
        [app_path(), base_path('bootstrap'), base_path('routes'), database_path(), resource_path()],
        ['php', 'ts', 'vue', 'js'],
    );

    foreach ($sources as $path => $contents) {
        expect($contents, $path)
            ->not->toMatch('/\benv\s*\(/')
            ->not->toMatch('/\b(?:dd|dump|var_dump|ray)\s*\(/')
            ->not->toMatch('/\bconsole\.(?:log|debug)\s*\(/');
    }
});

test('application and factory code does not use the service container as a locator', function () {
    $sources = firstPartySourceFiles(
        [app_path(), database_path('factories')],
        ['php'],
    );

    foreach ($sources as $path => $contents) {
        expect($contents, $path)
            ->not->toMatch('/\bapp\s*\(/')
            ->not->toMatch('/(?<!->)\bresolve\s*\(/');
    }
});

test('routes do not contain endpoint action closures', function () {
    $routes = firstPartySourceFiles([base_path('routes')], ['php']);

    foreach ($routes as $path => $contents) {
        expect($contents, $path)
            ->not->toMatch('/Route::(?:get|post|put|patch|delete|any|match)\([^;]*(?:function\s*\(|fn\s*\()/s');
    }
});

test('tailwind utility names are not assembled from runtime interpolation', function () {
    $sources = firstPartySourceFiles(
        [app_path(), resource_path()],
        ['php', 'ts', 'vue', 'js'],
    );

    foreach ($sources as $path => $contents) {
        expect($contents, $path)
            ->not->toMatch('/\b(?:bg|text|border|ring|grid-cols|col-span|row-span)-\$\{/')
            ->not->toMatch('/[\'\"](?:bg|text|border|ring|grid-cols|col-span|row-span)-[\'\"]\s*\+/');
    }
});
