<?php

declare(strict_types=1);

use App\Enums\RememberedEmailStorageStatus;
use App\Models\User;
use App\Providers\NativeServiceProvider;
use App\Services\EncryptedRememberedEmailStorage;
use App\Services\RememberedEmailStorageResult;
use App\Services\RememberedEmailStore;
use GoLeaf\NativeEmailPicker\EmailPickerServiceProvider;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;

function rememberedEmailStorageResult(
    RememberedEmailStorageStatus $status,
    ?string $value = null,
): RememberedEmailStorageResult {
    return new RememberedEmailStorageResult($status, $value);
}

test('remembered email storage is disabled outside the native runtime', function () {
    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldNotReceive('read', 'set', 'delete');

    $store = new RememberedEmailStore(config(), $storage);

    expect($store->emails())->toBe([])
        ->and($store->remember('person@example.com'))->toBeFalse()
        ->and($store->forget('person@example.com'))->toBeFalse();
});

test('remembered email storage filters bounds and de-duplicates native data', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            json_encode([
                'version' => 1,
                'emails' => [
                    ' Recent@Example.com ',
                    'recent@example.com',
                    'not-an-email',
                    'second@example.com',
                    'third@example.com',
                    'fourth@example.com',
                    'fifth@example.com',
                    'sixth@example.com',
                    ['nested@example.com'],
                ],
            ], JSON_THROW_ON_ERROR),
        ));

    expect((new RememberedEmailStore(config(), $storage))->emails())->toBe([
        'Recent@Example.com',
        'second@example.com',
        'third@example.com',
        'fourth@example.com',
        'fifth@example.com',
    ]);
});

test('corrupt or unsupported remembered email payloads fail closed', function (string $payload) {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            $payload,
        ));

    expect((new RememberedEmailStore(config(), $storage))->emails())->toBe([]);
})->with([
    'invalid json' => '{',
    'unsupported payload version' => '{"version":2,"emails":["person@example.com"]}',
]);

test('successful native authentication moves the authenticated email to the front', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            json_encode([
                'version' => 1,
                'emails' => ['older@example.com', 'PERSON@example.com'],
            ], JSON_THROW_ON_ERROR),
        ));
    $storage->shouldReceive('set')
        ->once()
        ->withArgs(function (string $payload): bool {
            return json_decode($payload, true, flags: JSON_THROW_ON_ERROR) === [
                'version' => 1,
                'emails' => ['person@example.com', 'older@example.com'],
            ];
        })
        ->andReturnTrue();

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $user = User::factory()->create(['email' => 'person@example.com']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

test('failed authentication never changes remembered email storage', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldNotReceive('read', 'set', 'delete');

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ]);

    $this->assertGuest();
});

test('successful native registration remembers only the authenticated model email', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(RememberedEmailStorageStatus::NotFound));
    $storage->shouldReceive('set')
        ->once()
        ->withArgs(function (string $payload): bool {
            return json_decode($payload, true, flags: JSON_THROW_ON_ERROR) === [
                'version' => 1,
                'emails' => ['registered@example.com'],
            ];
        })
        ->andReturnTrue();

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $this->post(route('register.store'), [
        'name' => 'Registered Person',
        'email' => 'registered@example.com',
        'language' => 'en',
        'timezone' => 'Europe/Vilnius',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

test('logout preserves remembered email storage', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldNotReceive('read', 'set', 'delete');

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();

    $this->assertGuest();
});

test('unavailable native storage is never overwritten', function (RememberedEmailStorageStatus $status) {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult($status));
    $storage->shouldNotReceive('set', 'delete');

    $store = new RememberedEmailStore(config(), $storage);

    expect($store->remember('person@example.com'))->toBeFalse()
        ->and($store->forget('person@example.com'))->toBeFalse();
})->with([
    'protected data unavailable' => RememberedEmailStorageStatus::Unavailable,
    'encrypted file failure' => RememberedEmailStorageStatus::Failed,
]);

test('native auth pages expose bounded assistance without reusing login history for registration', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            json_encode([
                'version' => 1,
                'emails' => ['recent@example.com', 'other@example.com'],
            ], JSON_THROW_ON_ERROR),
        ));

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->where('rememberedEmails', ['recent@example.com', 'other@example.com'])
            ->where('deviceEmailPickerAvailable', true));

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('rememberedEmails', [])
            ->where('deviceEmailPickerAvailable', true));
});

test('web auth pages keep manual entry without touching native storage', function () {
    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldNotReceive('read', 'set', 'delete');

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rememberedEmails', [])
            ->where('deviceEmailPickerAvailable', false));

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rememberedEmails', [])
            ->where('deviceEmailPickerAvailable', false));
});

test('a guest can remove one remembered email without placing it in the url', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            json_encode([
                'version' => 1,
                'emails' => ['remove@example.com', 'keep@example.com'],
            ], JSON_THROW_ON_ERROR),
        ));
    $storage->shouldReceive('set')
        ->once()
        ->withArgs(function (string $payload): bool {
            return json_decode($payload, true, flags: JSON_THROW_ON_ERROR) === [
                'version' => 1,
                'emails' => ['keep@example.com'],
            ];
        })
        ->andReturnTrue();

    app()->instance(
        RememberedEmailStore::class,
        new RememberedEmailStore(config(), $storage),
    );

    $this->deleteJson(route('remembered-emails.destroy'), [
        'email' => 'remove@example.com',
    ])->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('forgotten', true)
            ->where('remaining', 1));

    expect(route('remembered-emails.destroy'))->not->toContain('remove@example.com');
});

test('removing the final remembered email deletes the secure storage key', function () {
    config()->set('nativephp-internal.platform', 'android');

    $storage = Mockery::mock(EncryptedRememberedEmailStorage::class);
    $storage->shouldReceive('read')
        ->once()
        ->andReturn(rememberedEmailStorageResult(
            RememberedEmailStorageStatus::Found,
            json_encode([
                'version' => 1,
                'emails' => ['remove@example.com'],
            ], JSON_THROW_ON_ERROR),
        ));
    $storage->shouldReceive('delete')
        ->once()
        ->andReturnTrue();

    $store = new RememberedEmailStore(config(), $storage);

    expect($store->forget('remove@example.com'))->toBeTrue()
        ->and($store->emails())->toBe([]);
});

test('native remembered email payload is encrypted atomically in private app storage', function () {
    $filesystem = new Filesystem;
    $path = storage_path('framework/testing/native-email-'.Str::uuid().'.enc');
    $storage = new EncryptedRememberedEmailStorage(
        new Encrypter(random_bytes(32), 'AES-256-CBC'),
        $filesystem,
        $path,
    );
    $payload = '{"version":1,"emails":["private@example.com"]}';

    try {
        expect($storage->set($payload))->toBeTrue()
            ->and($filesystem->exists($path))->toBeTrue()
            ->and($filesystem->get($path))->not->toContain('private@example.com')
            ->and($filesystem->chmod($path))->toBe('0600')
            ->and($storage->read()->found())->toBeTrue()
            ->and($storage->read()->value)->toBe($payload)
            ->and($storage->delete())->toBeTrue()
            ->and($storage->read()->missing())->toBeTrue();
    } finally {
        if ($filesystem->exists($path)) {
            $filesystem->delete($path);
        }
    }
});

test('tampered encrypted remembered email payload is unavailable and never exposed', function () {
    $filesystem = new Filesystem;
    $path = storage_path('framework/testing/native-email-'.Str::uuid().'.enc');
    $storage = new EncryptedRememberedEmailStorage(
        new Encrypter(random_bytes(32), 'AES-256-CBC'),
        $filesystem,
        $path,
    );

    try {
        $filesystem->put($path, 'tampered-ciphertext', true);

        expect($storage->read()->status)->toBe(RememberedEmailStorageStatus::Unavailable)
            ->and($storage->read()->value)->toBeNull();
    } finally {
        if ($filesystem->exists($path)) {
            $filesystem->delete($path);
        }
    }
});

test('the first party native picker is allowlisted and permission free', function () {
    $rootComposer = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $manifest = json_decode(
        file_get_contents(base_path('packages/goleaf/nativephp-email-picker/nativephp.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $androidSource = file_get_contents(
        base_path('packages/goleaf/nativephp-email-picker/resources/android/EmailPickerFunctions.kt'),
    );

    expect($rootComposer['require']['goleaf/nativephp-email-picker'])->toBe('^1.0')
        ->and($rootComposer['repositories'])->toContain([
            'type' => 'path',
            'url' => 'packages/goleaf/nativephp-email-picker',
            'options' => ['symlink' => false],
        ])
        ->and((new NativeServiceProvider(app()))->plugins())->toContain(
            EmailPickerServiceProvider::class,
        )
        ->and($manifest['android']['permissions'])->toBe([])
        ->and($manifest['bridge_functions'][0]['name'])->toBe('EmailPicker.Choose')
        ->and($androidSource)->toContain(
            'ActivityResultContracts.StartActivityForResult',
            'AccountManager.newChooseAccountIntent',
            'AccountManager.KEY_ACCOUNT_NAME',
            'getWebViewOrNull',
        )
        ->not->toContain('true,')
        ->not->toContain(
            'GET_ACCOUNTS',
            'READ_CONTACTS',
            'getAccounts(',
            'Log.',
        );
});

test('the auth interface uses one shared inline assistant with complete translations', function () {
    $login = file_get_contents(resource_path('js/pages/auth/Login.vue'));
    $register = file_get_contents(resource_path('js/pages/auth/Register.vue'));
    $component = file_get_contents(resource_path('js/components/auth/AuthEmailAssistant.vue'));

    expect($login)->toContain('AuthEmailAssistant')
        ->and($register)->toContain('AuthEmailAssistant')
        ->and($component)->toContain(
            'requestDeviceEmail',
            'remembered-emails',
            'min-h-12',
            'StatusNotice',
        );

    $english = require lang_path('en/ui.php');
    $lithuanian = require lang_path('lt/ui.php');
    $russian = require lang_path('ru/ui.php');

    expect(array_keys($english['auth']['email_assistance']))
        ->toBe(array_keys($lithuanian['auth']['email_assistance']))
        ->toBe(array_keys($russian['auth']['email_assistance']));
});
