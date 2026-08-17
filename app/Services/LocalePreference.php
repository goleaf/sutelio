<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserLanguage;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class LocalePreference
{
    public const string COOKIE_NAME = 'sutelio_locale';

    private const int COOKIE_MINUTES = 60 * 24 * 365 * 5;

    public function preferredLanguage(Request $request): UserLanguage
    {
        return $this->authenticatedLanguage($request)
            ?? $this->languageFrom($request->cookie(self::COOKIE_NAME))
            ?? $this->languageFrom($request->hasSession() ? $request->session()->get('locale') : null)
            ?? $this->languageFrom($request->getPreferredLanguage(UserLanguage::values()))
            ?? $this->languageFrom(config('app.fallback_locale'))
            ?? UserLanguage::English;
    }

    public function requiresSelection(Request $request): bool
    {
        if ($request->user() instanceof User) {
            return false;
        }

        return $this->languageFrom($request->cookie(self::COOKIE_NAME)) === null
            && $this->languageFrom($request->hasSession() ? $request->session()->get('locale') : null) === null;
    }

    public function remember(Request $request, UserLanguage $language): Cookie
    {
        if ($request->hasSession()) {
            $request->session()->put('locale', $language->value);
        }

        return cookie(
            name: self::COOKIE_NAME,
            value: $language->value,
            minutes: self::COOKIE_MINUTES,
            secure: $request->isSecure(),
            httpOnly: true,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }

    private function authenticatedLanguage(Request $request): ?UserLanguage
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        $user->loadMissing('preferences');

        return $this->languageFrom($user->preferences?->language);
    }

    private function languageFrom(mixed $value): ?UserLanguage
    {
        return is_string($value) ? UserLanguage::tryFrom($value) : null;
    }
}
