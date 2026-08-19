<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Services\RememberedEmailStore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;
use Laravel\Fortify\Features;
use Symfony\Component\HttpFoundation\Response;

class LoginViewResponse implements LoginViewResponseContract
{
    public function __construct(
        private RememberedEmailStore $rememberedEmailStore,
    ) {}

    public function toResponse($request): Response
    {
        /** @var Request $request */
        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'deviceEmailPickerAvailable' => $this->rememberedEmailStore->deviceEmailPickerAvailable(),
            'rememberedEmails' => $this->rememberedEmailStore->emails(),
            'status' => $request->session()->get('status'),
        ])->toResponse($request);
    }
}
