<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('production deployment waits for successful main CI and uses least privilege', function () {
    $workflow = File::get(base_path('.github/workflows/deploy-production.yml'));

    expect($workflow)
        ->toContain('workflow_run:')
        ->toContain("conclusion == 'success'")
        ->toContain("head_branch == 'main'")
        ->toContain('head_repository.full_name == github.repository')
        ->toContain('contents: read')
        ->toContain('cancel-in-progress: false')
        ->toContain('git ls-remote origin refs/heads/main')
        ->toContain('github.event.workflow_run.head_sha')
        ->toContain('composer install --no-dev --classmap-authoritative')
        ->toContain('find "$release_root" -type f')
        ->toContain('StrictHostKeyChecking=yes')
        ->toContain('Remove ephemeral SSH material')
        ->not->toContain('password')
        ->not->toContain('appleboy/')
        ->not->toMatch('/uses: [^#\n]+@(v[0-9]+|main|master)$/m');
});

test('clean CI creates the guarded SQLite file before Composer boots Laravel', function () {
    $workflow = File::get(base_path('.github/workflows/tests.yml'));
    $databasePreparation = strpos($workflow, 'touch database/database.sqlite');
    $composerSetup = strpos($workflow, 'run: composer setup');

    expect($databasePreparation)
        ->toBeInt()
        ->and($composerSetup)
        ->toBeInt()
        ->and($databasePreparation)
        ->toBeLessThan($composerSetup);
});

test('production activation preserves shared state and rolls code back on failed health', function () {
    $script = File::get(base_path('deploy/activate-release.sh'));

    expect($script)
        ->toContain('shared/database/database.sqlite')
        ->toContain('backup:run')
        ->toContain('down --retry=60 --refresh=15')
        ->toContain('migrate --force')
        ->toContain('app:database-health')
        ->toContain('flock')
        ->toContain('exceeds the 512 MiB safety limit')
        ->toContain('only regular files and directories')
        ->toContain('rollback_release')
        ->not->toContain('migrate:fresh')
        ->not->toContain('db:seed');
});

test('aaPanel Nginx contract serves only the current Laravel public release over modern TLS', function () {
    $configuration = File::get(base_path('deploy/nginx/sutelio.miniserver.fun.conf'));

    expect($configuration)
        ->toContain('root /www/wwwroot/sutelio.miniserver.fun/current/public;')
        ->toContain('return 301 https://$host$request_uri;')
        ->toContain('ssl_protocols TLSv1.2 TLSv1.3;')
        ->toContain('try_files $uri $uri/ /index.php?$query_string;')
        ->toContain('include enable-php-85.conf;')
        ->toContain('well-known/sutelio.miniserver.fun.conf')
        ->toContain('Strict-Transport-Security')
        ->toContain('Content-Security-Policy')
        ->toContain('X-Content-Type-Options')
        ->not->toContain('TLSv1.1');
});

test('scheduler and queue worker serialize SQLite work against release migrations', function () {
    $activation = File::get(base_path('deploy/activate-release.sh'));
    $scheduler = File::get(base_path('deploy/run-scheduler.sh'));
    $worker = File::get(base_path('deploy/run-queue-worker.sh'));

    expect($activation)
        ->toContain('shared/runtime.lock')
        ->toContain('flock 8')
        ->and($scheduler)
        ->toContain('flock --shared --nonblock')
        ->toContain('cd "$CURRENT_PATH"')
        ->toContain('schedule:run --no-interaction')
        ->and($worker)
        ->toContain('flock --shared --wait 120')
        ->toContain('cd "$CURRENT_PATH"')
        ->toContain('queue:work database')
        ->toContain('--stop-when-empty-for=5');
});
