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

test('production deployment executes the activation script from the tested commit', function () {
    $workflow = File::get(base_path('.github/workflows/deploy-production.yml'));
    $transfer = str($workflow)->between(
        '      - name: Transfer and activate release',
        '      - name: Remove ephemeral SSH material',
    );

    expect($transfer->toString())
        ->toContain('activation_script="$GITHUB_WORKSPACE/deploy/activate-release.sh"')
        ->toContain('activation_checksum="$(sha256sum "$activation_script" | cut -d\' \' -f1)"')
        ->toContain('remote_activation="${DEPLOY_PATH}/shared/incoming/${RELEASE_SHA}.activate-release"')
        ->toContain('"$activation_script" "${ssh_target}:${remote_activation}.uploading"')
        ->toContain('printf \'%s  %s\n\' \'${activation_checksum}\' \'${remote_activation}\' | sha256sum --check --status')
        ->toContain('/usr/bin/bash \'${remote_activation}\' \'${RELEASE_SHA}\' \'${checksum}\'')
        ->not->toContain('\'${DEPLOY_PATH}/shared/bin/activate-release\'');
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

test('production frontend build boots Wayfinder against isolated SQLite', function () {
    $workflow = File::get(base_path('.github/workflows/deploy-production.yml'));
    $buildStep = str($workflow)->between(
        '      - name: Build production frontend',
        '      - name: Package immutable release',
    );

    expect($buildStep->toString())
        ->toContain('APP_ENV: production')
        ->toContain('DB_CONNECTION: sqlite')
        ->toContain("DB_DATABASE: ':memory:'")
        ->toContain('npm run build');
});

test('frontend source contracts use Git case-correct page paths on Linux', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'ts') {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toContain('/Pages/');
    }
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

test('post activation release cleanup failures remain non fatal', function () {
    $script = File::get(base_path('deploy/activate-release.sh'));
    $cleanup = str($script)->between('prune_old_releases() {', "\n}\n\n[[");

    expect($cleanup->toString())
        ->toContain('if ! rm -rf -- "$release_path"; then')
        ->toContain('WARNING: Could not remove expired release ${release_path##*/}; operator cleanup is required.')
        ->toContain('continue')
        ->not->toContain('sudo')
        ->not->toContain('chown');
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
        ->toContain('--max-time=60')
        ->not->toContain('--stop-when-empty');
});
