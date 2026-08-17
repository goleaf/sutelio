<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LocalePreference;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private readonly LocalePreference $localePreference) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->localePreference->preferredLanguage($request);

        App::setLocale($locale->value);
        $request->setLocale($locale->value);

        return $next($request);
    }
}
