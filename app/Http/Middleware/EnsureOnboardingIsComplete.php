<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserPreference;
use App\Services\ApiResponseFactory;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsComplete
{
    /**
     * @var list<string>
     */
    private const array SUPPORT_ROUTE_NAMES = [
        'onboarding.index',
        'onboarding.progress',
        'onboarding.preferences',
        'onboarding.workspace',
        'onboarding.project',
        'onboarding.task',
        'onboarding.complete',
        'onboarding.restart',
        'onboarding.replay.exit',
        'locale.update',
        'logout',
        'workspace-invitations.accept',
        'api.v1.auth.logout',
        'api.legacy.auth.logout',
    ];

    public function __construct(private readonly ApiResponseFactory $apiResponses) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isOnboardingSupportRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $user->load('preferences');
        $preferences = $user->preferences;

        if ($preferences instanceof UserPreference && $preferences->requiresOnboarding()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->apiResponse($request);
            }

            return to_route('onboarding.index');
        }

        return $next($request);
    }

    private function isOnboardingSupportRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return is_string($routeName) && in_array($routeName, self::SUPPORT_ROUTE_NAMES, true);
    }

    private function apiResponse(Request $request): JsonResponse
    {
        $details = ['onboarding_url' => route('onboarding.index')];

        if ($request->is('api/v1') || $request->is('api/v1/*')) {
            return $this->apiResponses->error(
                $request,
                'onboarding_required',
                Response::HTTP_CONFLICT,
                $details,
            );
        }

        $response = response()->json([
            'message' => __('api.errors.onboarding_required'),
            ...$details,
        ], Response::HTTP_CONFLICT);

        $this->apiResponses->decorate($response, $request, 'legacy');

        return $response;
    }
}
