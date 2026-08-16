<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPreference;
use App\Queries\OnboardingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(Request $request, OnboardingQuery $onboardingQuery): Response|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $user->preferences()->first();
        $isReplay = $request->session()->get('onboarding_replay') === true;

        if (! $preferences instanceof UserPreference
            || (! $preferences->requiresOnboarding() && ! $isReplay)) {
            return to_route(UserPreference::startRoute($preferences?->start_page));
        }

        $copy = __('onboarding');

        return Inertia::render('onboarding/Index', [
            ...$onboardingQuery->forPreferences($preferences, $isReplay),
            'copy' => is_array($copy) ? $copy : [],
        ]);
    }
}
