<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\Workspace;
use App\Services\LocalePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(private readonly LocalePreference $localePreference) {}

    public function toResponse($request): Response
    {
        /** @var Request $request */
        $user = $request->user();
        $user?->loadMissing(['preferences', 'workspaces']);

        if ($user instanceof User) {
            $workspace = $user->workspaces->first();

            if ($workspace instanceof Workspace) {
                $request->session()->put('current_workspace_id', $workspace->id);
            }
        }

        $language = $user instanceof User
            ? UserLanguage::tryFrom((string) $user->preferences?->language)
            : null;
        $response = $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended(Fortify::redirects('register'));

        return $response->withCookie(
            $this->localePreference->remember(
                $request,
                $language ?? UserLanguage::English,
            ),
        );
    }
}
