<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Services\RememberedEmailStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterViewResponse implements RegisterViewResponseContract
{
    public function __construct(
        private RememberedEmailStore $rememberedEmailStore,
    ) {}

    public function toResponse($request): Response
    {
        /** @var Request $request */
        return Inertia::render('auth/Register', [
            'deviceEmailPickerAvailable' => $this->rememberedEmailStore->deviceEmailPickerAvailable(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'rememberedEmails' => [],
        ])->toResponse($request);
    }
}
