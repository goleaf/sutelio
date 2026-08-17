<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

test('email verification is disabled throughout the authentication contract', function () {
    expect(Features::enabled(Features::emailVerification()))
        ->toBeFalse()
        ->and(is_subclass_of(User::class, MustVerifyEmail::class))
        ->toBeFalse()
        ->and(Route::has('verification.notice'))
        ->toBeFalse()
        ->and(Route::has('verification.send'))
        ->toBeFalse()
        ->and(Route::has('verification.verify'))
        ->toBeFalse();
});
