<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AdvanceOnboarding;
use App\Actions\ChooseOnboardingProject;
use App\Actions\ChooseOnboardingTask;
use App\Actions\ChooseOnboardingWorkspace;
use App\Actions\CompleteOnboarding;
use App\Actions\DismissOnboardingChecklist;
use App\Actions\RestartOnboarding;
use App\Actions\SaveOnboardingPreferences;
use App\Enums\UserLanguage;
use App\Http\Requests\AdvanceOnboardingRequest;
use App\Http\Requests\StoreOnboardingProjectRequest;
use App\Http\Requests\StoreOnboardingTaskRequest;
use App\Http\Requests\StoreOnboardingWorkspaceRequest;
use App\Http\Requests\UpdateOnboardingPreferencesRequest;
use App\Models\User;
use App\Models\UserPreference;
use App\Queries\OnboardingQuery;
use App\Services\LocalePreference;
use App\Services\TimeZoneCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(
        Request $request,
        OnboardingQuery $onboardingQuery,
        TimeZoneCatalog $timeZoneCatalog,
    ): Response|RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $user->preferences()->first();

        if (! $preferences instanceof UserPreference) {
            return to_route(UserPreference::startRoute(null));
        }

        $requiresOnboarding = $preferences->requiresOnboarding();
        $isReplay = ! $requiresOnboarding && $this->isReplay($request);

        if ($requiresOnboarding) {
            $request->session()->forget('onboarding_replay');
        } elseif (! $isReplay) {
            return to_route(UserPreference::startRoute($preferences->start_page));
        }

        $copy = __('onboarding');
        $language = UserLanguage::tryFrom($request->getLocale()) ?? UserLanguage::English;

        return Inertia::render('onboarding/Index', [
            ...$onboardingQuery->forUser($user, $preferences, $isReplay),
            'timezoneGroups' => $timeZoneCatalog->forLanguage($language),
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
        LocalePreference $localePreference,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $saveOnboardingPreferences->handle(
            $user,
            $this->activePreferences($request),
            $request->preferenceData(),
        );
        $request->session()->put('locale', $preferences->language);

        $language = UserLanguage::tryFrom((string) $preferences->language)
            ?? UserLanguage::English;

        return to_route('onboarding.index')
            ->withCookie($localePreference->remember($request, $language));
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

    public function exitReplay(Request $request): RedirectResponse
    {
        $preferences = $this->activePreferences($request);

        abort_unless($this->isReplay($request) && ! $preferences->requiresOnboarding(), 403);

        $request->session()->forget('onboarding_replay');

        return to_route(UserPreference::startRoute($preferences->start_page));
    }

    public function complete(Request $request, CompleteOnboarding $completeOnboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $this->activePreferences($request);

        $workspace = $completeOnboarding->handle($user, $preferences);
        $request->session()->put('current_workspace_id', $workspace->id);
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

    public function dismissChecklist(
        Request $request,
        DismissOnboardingChecklist $dismissOnboardingChecklist,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $dismissOnboardingChecklist->handle($user);

        return back();
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
