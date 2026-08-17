<?php

namespace App\Http\Controllers;

use App\Actions\UpdateUserPreferences;
use App\Enums\UserLanguage;
use App\Http\Requests\UpdateUserPreferenceRequest;
use App\Services\LocalePreference;
use Illuminate\Http\RedirectResponse;

class UserPreferenceController extends Controller
{
    public function update(
        UpdateUserPreferenceRequest $request,
        UpdateUserPreferences $updateUserPreferences,
        LocalePreference $localePreference,
    ): RedirectResponse {
        $preferences = $updateUserPreferences->execute($request->user(), $request->validated());
        $request->session()->put('locale', $preferences->language);

        $language = UserLanguage::tryFrom((string) $preferences->language)
            ?? UserLanguage::English;

        return back()->withCookie($localePreference->remember($request, $language));
    }
}
