<?php

declare(strict_types=1);

test('well known passkey endpoint publishes the authenticated management route', function () {
    $this->getJson(route('well-known.passkeys'))
        ->assertOk()
        ->assertExactJson([
            'enroll' => route('security.edit'),
            'manage' => route('security.edit'),
        ]);
});

test('guest home requests redirect to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});
