<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserLanguage;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\TimeZoneCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreferencesController extends Controller
{
    public function edit(Request $request, TimeZoneCatalog $timeZoneCatalog): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $preferences = $user->preferences()->first();
        $language = UserLanguage::tryFrom($request->getLocale()) ?? UserLanguage::English;

        return Inertia::render('settings/Preferences', [
            'preferences' => [
                ...UserPreference::defaults(),
                ...($preferences?->only(array_keys(UserPreference::defaults())) ?? []),
            ],
            'timezoneGroups' => $timeZoneCatalog->forLanguage($language),
            'canReplayOnboarding' => true,
        ]);
    }
}
