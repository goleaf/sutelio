<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\RememberedEmailStore;
use Illuminate\Auth\Events\Login;

class RememberAuthenticatedEmail
{
    public function __construct(
        private RememberedEmailStore $rememberedEmailStore,
    ) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->rememberedEmailStore->remember($event->user->email);
    }
}
