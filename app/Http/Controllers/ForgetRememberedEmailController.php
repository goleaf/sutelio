<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgetRememberedEmailRequest;
use App\Services\RememberedEmailStore;
use Illuminate\Http\JsonResponse;

class ForgetRememberedEmailController extends Controller
{
    public function __invoke(
        ForgetRememberedEmailRequest $request,
        RememberedEmailStore $rememberedEmailStore,
    ): JsonResponse {
        $forgotten = $rememberedEmailStore->forget($request->email());

        return response()->json([
            'forgotten' => $forgotten,
            'remaining' => count($rememberedEmailStore->emails()),
        ]);
    }
}
