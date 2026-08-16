<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('login');
        }

        $user->loadMissing('preferences');

        return to_route(UserPreference::startRoute($user->preferences?->start_page));
    }
}
