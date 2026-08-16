<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AdvanceOnboarding;
use App\Actions\ChooseOnboardingProject;
use App\Actions\ChooseOnboardingTask;
use App\Actions\ChooseOnboardingWorkspace;
use App\Actions\CompleteOnboarding;
use App\Actions\RestartOnboarding;
use App\Actions\SaveOnboardingPreferences;
use App\Actions\SkipOnboarding;
use App\Http\Requests\AdvanceOnboardingRequest;
use App\Http\Requests\StoreOnboardingProjectRequest;
use App\Http\Requests\StoreOnboardingTaskRequest;
use App\Http\Requests\StoreOnboardingWorkspaceRequest;
use App\Http\Requests\UpdateOnboardingPreferencesRequest;
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
        $isReplay = $this->isReplay($request);

        if (! $preferences instanceof UserPreference
            || (! $preferences->requiresOnboarding() && ! $isReplay)) {
            return to_route(UserPreference::startRoute($preferences?->start_page));
        }

        $copy = __('onboarding');

        return Inertia::render('onboarding/Index', [
            ...$onboardingQuery->forUser($user, $preferences, $isReplay),
            'copy' => is_array($copy) ? $copy : [],
        ]);
    }

    public function progress(
        AdvanceOnboardingRequest $request,
        AdvanceOnboarding $advanceOnboarding,
    ): RedirectResponse {
        $preferences = $this->activePreferences($request);

        $advanceOnboarding->handle($preferences, $request->targetStep());

        return to_route('onboarding.index');
    }

    public function preferences(
        UpdateOnboardingPreferencesRequest $request,
        SaveOnboardingPreferences $saveOnboardingPreferences,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $saveOnboardingPreferences->handle(
            $user,
            $this->activePreferences($request),
            $request->preferenceData(),
        );

        return to_route('onboarding.index');
    }

    public function workspace(
        StoreOnboardingWorkspaceRequest $request,
        ChooseOnboardingWorkspace $chooseOnboardingWorkspace,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $workspace = $chooseOnboardingWorkspace->handle(
            $user,
            $this->activePreferences($request),
            $request->mode(),
            $request->workspaceId(),
            $request->workspaceData(),
            $request->requestKey(),
        );
        $request->session()->put('current_workspace_id', $workspace->id);

        return to_route('onboarding.index');
    }

    public function project(
        StoreOnboardingProjectRequest $request,
        ChooseOnboardingProject $chooseOnboardingProject,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $chooseOnboardingProject->handle(
            $user,
            $this->activePreferences($request),
            $request->mode(),
            $request->projectId(),
            $request->projectData(),
            $request->requestKey(),
        );

        return to_route('onboarding.index');
    }

    public function task(
        StoreOnboardingTaskRequest $request,
        ChooseOnboardingTask $chooseOnboardingTask,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $chooseOnboardingTask->handle(
            $user,
            $this->activePreferences($request),
            $request->mode(),
            $request->taskId(),
            $request->taskData(),
            $request->requestKey(),
        );

        return to_route('onboarding.index');
    }

    public function skip(Request $request, SkipOnboarding $skipOnboarding): RedirectResponse
    {
        $preferences = $this->activePreferences($request);

        $skipOnboarding->handle($preferences, $this->isReplay($request));
        $request->session()->forget('onboarding_replay');

        return to_route(UserPreference::startRoute($preferences->start_page));
    }

    public function complete(Request $request, CompleteOnboarding $completeOnboarding): RedirectResponse
    {
        $preferences = $this->activePreferences($request);

        $completeOnboarding->handle($preferences);
        $request->session()->forget('onboarding_replay');

        return to_route(UserPreference::startRoute($preferences->start_page));
    }

    public function restart(Request $request, RestartOnboarding $restartOnboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $restartOnboarding->handle($user);

        if ($preferences->requiresOnboarding()) {
            $request->session()->forget('onboarding_replay');
        } else {
            $request->session()->put('onboarding_replay', true);
        }

        return to_route('onboarding.index');
    }

    private function activePreferences(Request $request): UserPreference
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $user->preferences()->first();

        abort_unless($preferences instanceof UserPreference, 404);
        abort_unless($preferences->requiresOnboarding() || $this->isReplay($request), 403);

        return $preferences;
    }

    private function isReplay(Request $request): bool
    {
        return $request->session()->get('onboarding_replay') === true;
    }
}
