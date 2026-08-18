<?php

declare(strict_types=1);

use App\Enums\OnboardingStep;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('pending users are redirected from application pages and settings to onboarding', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('onboarding.index');

    $this->actingAs($user)
        ->get(route('preferences.edit'))
        ->assertRedirectToRoute('onboarding.index');
});

test('onboarding and application routes never require email verification', function () {
    foreach (['onboarding.index', 'dashboard', 'workspace-invitations.accept', 'security.edit'] as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route, $routeName)->not->toBeNull()
            ->and($route?->gatherMiddleware(), $routeName)->not->toContain('verified');
    }
});

test('existing complete users and legacy users without preferences are not redirected', function () {
    $complete = User::factory()->create();
    UserPreference::factory()->for($complete)->create();
    $legacy = User::factory()->create();

    $this->actingAs($complete)->get(route('dashboard'))->assertOk();
    $this->actingAs($legacy)->get(route('dashboard'))->assertOk();
});

test('onboarding requires authentication', function () {
    $this->get(route('onboarding.index'))
        ->assertRedirectToRoute('login');
});

test('pending users can render their resumable onboarding page', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->pendingOnboarding()->create([
        'language' => 'lt',
        'onboarding_state' => ['language' => 'lt'],
    ]);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Index')
            ->where('progress.step', OnboardingStep::Welcome->value)
            ->where('progress.position', 1)
            ->where('progress.total', 8)
            ->where('progress.percent', 13)
            ->where('progress.is_replay', false)
            ->where('preferences.language', $preferences->language)
            ->where('state', [])
            ->has('copy.meta.title')
            ->has('copy.meta.description')
            ->where('timezoneGroups', fn ($groups): bool => $groups->contains(
                fn (array $group): bool => $group['key'] === 'europe'
                    && $group['label'] === 'Europa'
                    && collect($group['options'])->contains(
                        fn (array $option): bool => $option['value'] === 'Europe/Vilnius'
                            && str_contains($option['label'], 'Lietuva'),
                    ),
            ))
            ->missing('timezones'));
});

test('completed users leave onboarding for their selected start page', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['start_page' => 'projects']);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertRedirectToRoute('projects');
});

test('legacy users without preferences leave onboarding for the default start page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertRedirectToRoute('dashboard');
});

test('pending users cannot leave onboarding through authentication recovery routes', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();

    $this->actingAs($user)->get(route('password.request'))
        ->assertRedirectToRoute('onboarding.index');
    $this->get(route('password.confirm'))
        ->assertRedirectToRoute('onboarding.index');
});

test('every authenticated product route is covered by the onboarding gate', function () {
    $logoutRoutes = ['logout', 'api.v1.auth.logout', 'api.legacy.auth.logout'];
    $webMiddleware = app('router')->getMiddlewareGroups()['web'];
    $apiMiddleware = app('router')->getMiddlewareGroups()['api'];
    $gatedGroups = ['web', 'api'];

    expect($webMiddleware)->toContain(EnsureOnboardingIsComplete::class)
        ->and($apiMiddleware)->toContain(EnsureOnboardingIsComplete::class);

    foreach (Route::getRoutes()->getRoutes() as $route) {
        $middleware = $route->gatherMiddleware();
        $isAuthenticated = collect($middleware)->contains(
            fn (string $name): bool => $name === 'auth'
                || str_starts_with($name, 'auth:')
                || str_starts_with($name, Authenticate::class),
        );

        if (! $isAuthenticated
            || in_array($route->getName(), $logoutRoutes, true)
        ) {
            continue;
        }

        expect(
            in_array(EnsureOnboardingIsComplete::class, $middleware, true)
                || collect($middleware)->intersect($gatedGroups)->isNotEmpty(),
            $route->getName() ?? $route->uri(),
        )->toBeTrue();
    }
});

test('the onboarding gate runs before throttling and route model binding', function () {
    $priority = app(HttpKernel::class)->getMiddlewarePriority();
    $gatePosition = array_search(EnsureOnboardingIsComplete::class, $priority, true);

    expect($gatePosition)->toBeInt();

    foreach ([ThrottleRequests::class, ThrottleRequestsWithRedis::class, SubstituteBindings::class] as $middleware) {
        $middlewarePosition = array_search($middleware, $priority, true);

        expect($middlewarePosition, $middleware)->toBeInt()
            ->and($gatePosition)->toBeLessThan($middlewarePosition);
    }
});

test('pending users are contained before protected bindings and rate limits run', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();
    $missingId = (string) Str::uuid();

    $this->actingAs($user)
        ->get(route('todos.show', ['todo' => $missingId]))
        ->assertRedirectToRoute('onboarding.index');

    foreach (range(1, 12) as $attempt) {
        $this->actingAs($user)
            ->post(route('workspaces.invite', ['workspace' => $missingId]), [
                'email' => "pending-{$attempt}@example.test",
            ])
            ->assertRedirectToRoute('onboarding.index');
    }
});

test('pending users cannot dismiss the post-onboarding checklist', function () {
    $user = User::factory()->create();
    $preferences = UserPreference::factory()->for($user)->pendingOnboarding()->create([
        'onboarding_checklist_dismissed_at' => null,
    ]);

    $this->actingAs($user)
        ->delete(route('onboarding.checklist.dismiss'))
        ->assertRedirectToRoute('onboarding.index');

    expect($preferences->fresh()->onboarding_checklist_dismissed_at)->toBeNull();
});

test('versioned api product routes reject pending users with the onboarding contract', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();
    $token = $user->createToken('onboarding-entry-test')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('api.v1.user.show'))
        ->assertConflict()
        ->assertJsonPath('error.code', 'onboarding_required')
        ->assertJsonPath('error.details.onboarding_url', route('onboarding.index'));

    $this->withToken($token)
        ->getJson(route('api.v1.workspaces.index'))
        ->assertConflict()
        ->assertJsonPath('error.code', 'onboarding_required');

    $this->withToken($token)
        ->getJson(route('api.v1.workspaces.show', ['workspace' => (string) Str::uuid()]))
        ->assertConflict()
        ->assertJsonPath('error.code', 'onboarding_required');
});

test('legacy api product routes reject pending users without changing compatibility headers', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();
    $token = $user->createToken('legacy-onboarding-entry-test')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('api.legacy.user.show'))
        ->assertConflict()
        ->assertHeader('X-API-Version', 'legacy')
        ->assertHeader('Deprecation', 'true')
        ->assertJsonPath('message', 'Complete onboarding before using the application.')
        ->assertJsonPath('onboarding_url', route('onboarding.index'));
});

test('pending api users can still sign out', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->pendingOnboarding()->create();
    $token = $user->createToken('onboarding-logout-test')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.v1.auth.logout'))
        ->assertNoContent();
});
