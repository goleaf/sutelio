<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateUserPreferences;
use App\Http\Requests\UpdateLocaleRequest;
use App\Models\User;
use App\Services\LocalePreference;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(
        UpdateLocaleRequest $request,
        UpdateUserPreferences $updateUserPreferences,
        LocalePreference $localePreference,
    ): RedirectResponse {
        $language = $request->language();
        $user = $request->user();

        if ($user instanceof User) {
            $updateUserPreferences->execute($user, [
                'language' => $language->value,
                'week_start' => $language->defaultWeekStart(),
            ]);
        }

        return back()->withCookie($localePreference->remember($request, $language));
    }
}
