<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\NativePhpRuntimeService;
use App\Services\SqliteHealthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(
        SqliteHealthService $sqliteHealthService,
        NativePhpRuntimeService $nativePhpRuntimeService,
    ): void {
        Model::shouldBeStrict(! $this->app->isProduction());

        $nativePhpRuntimeService->configure();
        $sqliteHealthService->assertConfigurationIsSafe();

        Event::listen(DiagnosingHealth::class, function () use ($sqliteHealthService): void {
            $sqliteHealthService->assertHealthy();
        });

        RateLimiter::for('api-login', function (Request $request): array {
            $credential = hash('sha256', Str::lower(trim((string) $request->input('email'))));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(60)->by('api-login-ip:'.$ip),
                Limit::perMinute(5)->by('api-login-credential:'.$credential.':'.$ip),
            ];
        });

        RateLimiter::for('api-registration', function (Request $request): array {
            $credential = hash('sha256', Str::lower(trim((string) $request->input('email'))));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(10)->by('api-registration-ip:'.$ip),
                Limit::perMinute(3)->by('api-registration-credential:'.$credential.':'.$ip),
            ];
        });
    }
}
