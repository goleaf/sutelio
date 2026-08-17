<?php

namespace App\Http\Responses;

use App\Enums\UserLanguage;
use App\Models\UserPreference;
use App\Services\LocalePreference;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private readonly LocalePreference $localePreference) {}

    public function toResponse($request): Response
    {
        /** @var Request $request */
        $user = $request->user();
        $user?->loadMissing('preferences');
        $route = UserPreference::startRoute($user?->preferences?->start_page);

        $response = $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(route($route, absolute: false));

        $language = UserLanguage::tryFrom((string) $user?->preferences?->language)
            ?? UserLanguage::English;

        return $response->withCookie($this->localePreference->remember($request, $language));
    }
}
