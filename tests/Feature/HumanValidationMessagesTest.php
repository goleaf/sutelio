<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Http\Requests\PasskeyRegistrationRequest;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;

dataset('human validation locales', [
    'English' => ['en', [
        'workspace' => 'Choose a valid workspace.',
        'password' => 'Use at least 12 characters for password.',
        'confirmation' => 'Make sure password and its confirmation match.',
        'device' => 'Please provide device name.',
        'position' => 'Enter a whole number for task position.',
        'credential' => 'Please provide passkey identifier.',
        'project' => 'Enter text for project.',
        'label' => 'Enter text for label.',
        'checklist_state' => 'Choose yes or no for checklist item completion state.',
    ]],
    'Lithuanian' => ['lt', [
        'workspace' => 'Pasirinkite tinkamą lauko „darbo erdvė“ reikšmę.',
        'password' => 'Lauke „slaptažodis“ įveskite bent 12 simbolių.',
        'confirmation' => 'Įsitikinkite, kad laukas „slaptažodis“ ir jo patvirtinimas sutampa.',
        'device' => 'Užpildykite lauką „įrenginio pavadinimas“.',
        'position' => 'Lauke „užduoties vieta“ įveskite sveikąjį skaičių.',
        'credential' => 'Užpildykite lauką „prieigos rakto identifikatorius“.',
        'project' => 'Lauke „projektas“ įveskite tekstą.',
        'label' => 'Lauke „etiketė“ įveskite tekstą.',
        'checklist_state' => 'Laukui „kontrolinio sąrašo punkto užbaigimo būsena“ pasirinkite „taip“ arba „ne“.',
    ]],
    'Russian' => ['ru', [
        'workspace' => 'Выберите корректное значение для поля «рабочее пространство».',
        'password' => 'Введите для поля «пароль» не менее 12 символов.',
        'confirmation' => 'Убедитесь, что поле «пароль» и его подтверждение совпадают.',
        'device' => 'Заполните поле «название устройства».',
        'position' => 'Введите целое число в поле «позиция задачи».',
        'credential' => 'Заполните поле «идентификатор ключа доступа».',
        'project' => 'Введите текст в поле «проект».',
        'label' => 'Введите текст в поле «метка».',
        'checklist_state' => 'Выберите «да» или «нет» для поля «состояние пункта контрольного списка».',
    ]],
]);

dataset('localized server authentication messages', [
    'English' => ['en', [
        'invalid_credential' => 'We could not read this passkey. Try again.',
        'registration_expired' => 'Passkey setup took too long. Start again.',
        'verification_expired' => 'Passkey confirmation took too long. Start again.',
    ]],
    'Lithuanian' => ['lt', [
        'invalid_credential' => 'Nepavyko perskaityti šio prieigos rakto. Bandykite dar kartą.',
        'registration_expired' => 'Baigėsi prieigos rakto pridėjimo laikas. Pradėkite iš naujo.',
        'verification_expired' => 'Baigėsi patvirtinimo prieigos raktu laikas. Pradėkite iš naujo.',
    ]],
    'Russian' => ['ru', [
        'invalid_credential' => 'Не удалось распознать этот ключ доступа. Попробуйте ещё раз.',
        'registration_expired' => 'Время добавления ключа доступа истекло. Начните заново.',
        'verification_expired' => 'Время подтверждения ключом доступа истекло. Начните заново.',
    ]],
]);

test('validation messages explain the correction with human field names', function (string $locale, array $expected) {
    app()->setLocale($locale);

    $errors = Validator::make([
        'workspace_id' => 'not-a-workspace',
        'password' => 'short',
        'device_name' => '',
        'items' => [['position' => 'first']],
        'credential' => ['rawId' => null],
    ], [
        'workspace_id' => ['uuid'],
        'password' => ['min:12', 'confirmed'],
        'device_name' => ['required'],
        'items.*.position' => ['integer'],
        'credential.rawId' => ['required'],
    ])->errors();

    expect($errors->first('workspace_id'))->toBe($expected['workspace'])
        ->and($errors->first('password'))->toBe($expected['password'])
        ->and($errors->get('password')[1])->toBe($expected['confirmation'])
        ->and($errors->first('device_name'))->toBe($expected['device'])
        ->and($errors->first('items.0.position'))->toBe($expected['position'])
        ->and($errors->first('credential.rawId'))->toBe($expected['credential'])
        ->and(implode(' ', $errors->all()))
        ->not->toMatch('/\b(?:workspace_id|device_name|items\.0\.position|password_confirmation)\b/i');

    $importErrors = Validator::make([
        'project' => 42,
        'labels' => [42],
        'checklists' => [['items' => [['checked' => 'yes']]]],
    ], [
        'project' => ['string'],
        'labels.*' => ['string'],
        'checklists.*.items.*.checked' => ['boolean'],
    ])->errors();

    expect($importErrors->first('project'))->toBe($expected['project'])
        ->and($importErrors->first('labels.0'))->toBe($expected['label'])
        ->and($importErrors->first('checklists.0.items.0.checked'))->toBe($expected['checklist_state'])
        ->and(implode(' ', $importErrors->all()))
        ->not->toMatch('/\b(?:labels\.0|checklists\.0\.items\.0\.checked)\b/i');
})->with('human validation locales');

test('installed passkey requests return localized server errors', function (string $locale, array $expected) {
    app()->setLocale($locale);

    $registrationRequest = PasskeyRegistrationRequest::create('/user/passkeys', 'POST');
    $registrationRequest->setLaravelSession(app('session')->driver());
    $verificationRequest = PasskeyVerificationRequest::create('/passkeys/login', 'POST');
    $verificationRequest->setLaravelSession(app('session')->driver());

    $registrationError = null;
    $verificationError = null;

    try {
        $registrationRequest->registrationOptions();
    } catch (ValidationException $exception) {
        $registrationError = $exception->errors()['credential'][0] ?? null;
    }

    try {
        $verificationRequest->verificationOptions();
    } catch (ValidationException $exception) {
        $verificationError = $exception->errors()['credential'][0] ?? null;
    }

    expect($registrationError)->toBe($expected['registration_expired'])
        ->and($verificationError)->toBe($expected['verification_expired']);

    $this->withSession(['locale' => $locale])
        ->postJson(route('passkey.login', absolute: false), [
            'credential' => [
                'id' => "invalid-{$locale}",
                'rawId' => "invalid-{$locale}",
                'type' => 'public-key',
                'response' => ['clientDataJSON' => 'invalid'],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.credential.0', $expected['invalid_credential']);
})->with('localized server authentication messages');

test('installed authentication package literals have complete human JSON translations', function () {
    $vendorSources = collect([
        base_path('vendor/laravel/fortify/src/Actions/ConfirmTwoFactorAuthentication.php'),
        base_path('vendor/laravel/fortify/src/Http/Responses/FailedPasswordConfirmationResponse.php'),
        base_path('vendor/laravel/fortify/src/Http/Responses/FailedTwoFactorLoginResponse.php'),
        base_path('vendor/laravel/fortify/src/Rules/Password.php'),
        base_path('vendor/laravel/passkeys/src/Actions/StorePasskey.php'),
        base_path('vendor/laravel/passkeys/src/Actions/VerifyPasskey.php'),
        base_path('vendor/laravel/passkeys/src/Http/Controllers/PasskeyLoginController.php'),
        base_path('vendor/laravel/passkeys/src/Http/Requests/PasskeyRegistrationRequest.php'),
        base_path('vendor/laravel/passkeys/src/Http/Requests/PasskeyVerificationRequest.php'),
    ])->map(fn (string $path): string => File::get($path))->implode("\n");

    preg_match_all('/__\(\s*[\'\"]([^\'\"]+)[\'\"]/', $vendorSources, $matches);
    preg_match_all('/InvalidPasskeyException::make\(\s*[\'\"]([^\'\"]+)[\'\"]/', $vendorSources, $passkeyMatches);
    $vendorKeys = collect($matches[1])
        ->merge($passkeyMatches[1])
        ->unique()
        ->sort()
        ->values()
        ->all();
    $catalogs = collect(['en', 'lt', 'ru'])->mapWithKeys(function (string $locale): array {
        $catalog = json_decode(File::get(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);

        return [$locale => $catalog];
    });
    $englishKeys = array_keys($catalogs['en']);

    foreach ($catalogs as $locale => $catalog) {
        expect(array_keys($catalog), "{$locale} JSON authentication keys")
            ->toBe($englishKeys)
            ->and($catalog, "{$locale} JSON authentication messages")
            ->toHaveKeys($vendorKeys)
            ->and(collect($catalog)->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty();

        foreach ($vendorKeys as $key) {
            preg_match_all('/:[A-Za-z_]+/', $key, $sourcePlaceholders);
            preg_match_all('/:[A-Za-z_]+/', $catalog[$key], $localizedPlaceholders);
            sort($sourcePlaceholders[0]);
            sort($localizedPlaceholders[0]);

            expect($localizedPlaceholders[0], "Placeholder mismatch for {$locale} JSON key {$key}")
                ->toBe($sourcePlaceholders[0]);
        }
    }
});

test('every supported locale owns the complete human validation attribute catalog', function () {
    $expectedAttributes = collect([
        'action',
        'actor',
        'agenda',
        'assigned_to',
        'assignee',
        'attention',
        'avatar',
        'body',
        'category',
        'checklists',
        'checklists.*',
        'checklists.*.items',
        'checklists.*.items.*',
        'checklists.*.items.*.checked',
        'checklists.*.items.*.content',
        'checklists.*.name',
        'code',
        'color',
        'completed_today',
        'content',
        'credential',
        'credential.id',
        'credential.rawId',
        'credential.response',
        'credential.type',
        'csv',
        'current_password',
        'cursor',
        'date',
        'date_format',
        'default_view',
        'description',
        'device_name',
        'direction',
        'due_date',
        'due_date_from',
        'due_date_to',
        'email',
        'estimated_time',
        'file',
        'format',
        'icon',
        'ids',
        'ids.*',
        'is_completed',
        'is_favorite',
        'is_pinned',
        'is_recurring',
        'items',
        'items.*.id',
        'items.*.position',
        'json',
        'kind',
        'label_id',
        'label_ids',
        'label_ids.*',
        'labels',
        'labels.*',
        'language',
        'member',
        'mode',
        'name',
        'notification_browser',
        'notification_email',
        'notification_in_app',
        'operation',
        'owner',
        'overdue',
        'page',
        'parent_id',
        'password',
        'password_confirmation',
        'per_page',
        'period',
        'priorities',
        'priority',
        'priority_id',
        'project',
        'project_id',
        'recovery_code',
        'recurring_rule',
        'reminded_at',
        'replacement_id',
        'request_key',
        'remember',
        'role',
        'search',
        'sort',
        'spent_time',
        'start_date',
        'start_page',
        'status',
        'statuses',
        'status_id',
        'tag_id',
        'tag_ids',
        'tag_ids.*',
        'tags',
        'tags.*',
        'target_step',
        'task_id',
        'time_format',
        'timezone',
        'title',
        'token',
        'type',
        'user_id',
        'user_name',
        'view',
        'week',
        'week_start',
        'workspace_id',
    ]);
    $validationSourcePaths = [
        app_path('Actions/Fortify'),
        app_path('Concerns'),
        app_path('Http/Requests'),
        base_path('vendor/laravel/passkeys/src/Http/Requests'),
    ];
    $manualValidatorFiles = collect(File::allFiles(app_path()))
        ->filter(function (SplFileInfo $file): bool {
            if ($file->getExtension() !== 'php') {
                return false;
            }

            return str_contains(File::get($file->getPathname()), 'Validator::make(');
        });
    $sourceAttributes = collect($validationSourcePaths)
        ->flatMap(fn (string $path): array => File::allFiles($path))
        ->concat($manualValidatorFiles)
        ->unique(fn (SplFileInfo $file): string => $file->getPathname())
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all(
                '/[\'\"]([A-Za-z_][A-Za-z0-9_.*-]*)[\'\"]\s*=>\s*\[/',
                File::get($file->getPathname()),
                $matches,
            );

            return $matches[1];
        });
    $expectedAttributes = $expectedAttributes
        ->merge($sourceAttributes)
        ->unique()
        ->values()
        ->all();

    $catalogs = collect(['en', 'lt', 'ru'])->mapWithKeys(function (string $locale): array {
        $catalog = require lang_path("{$locale}/validation.php");

        return [$locale => Arr::dot($catalog['attributes'])];
    });
    $englishKeys = array_keys($catalogs['en']);

    foreach ($catalogs as $locale => $attributes) {
        expect(array_keys($attributes), "{$locale} validation attribute keys")
            ->toBe($englishKeys)
            ->and($attributes, "{$locale} validation attributes")
            ->toHaveKeys($expectedAttributes)
            ->and(collect($attributes)->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty();
    }
});

test('human validation catalogs replace every Laravel rule template with locale parity', function () {
    $frameworkCatalog = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $frameworkKeys = array_keys($frameworkCatalog);
    sort($frameworkKeys);

    $catalogs = collect(['en', 'lt', 'ru'])->mapWithKeys(function (string $locale): array {
        $catalog = require lang_path("{$locale}/validation.php");
        $rules = Arr::except($catalog, ['attributes', 'custom']);

        return [$locale => Arr::dot($rules)];
    });
    $englishKeys = array_keys($catalogs['en']);

    foreach (['en', 'lt', 'ru'] as $locale) {
        $catalog = require lang_path("{$locale}/validation.php");
        $catalogKeys = array_keys($catalog);
        sort($catalogKeys);

        expect($catalogKeys, "{$locale} framework validation keys")
            ->toBe($frameworkKeys)
            ->and(array_keys($catalogs[$locale]), "{$locale} validation rule keys")
            ->toBe($englishKeys)
            ->and(collect($catalogs[$locale])->filter(fn (mixed $value): bool => ! is_string($value) || blank($value)))
            ->toBeEmpty();

        foreach ($catalogs['en'] as $key => $message) {
            preg_match_all('/:[A-Za-z_]+/', $message, $englishPlaceholders);
            preg_match_all('/:[A-Za-z_]+/', $catalogs[$locale][$key], $localizedPlaceholders);

            sort($englishPlaceholders[0]);
            sort($localizedPlaceholders[0]);

            expect($localizedPlaceholders[0], "Placeholder mismatch for {$locale}.validation.{$key}")
                ->toBe($englishPlaceholders[0]);
        }
    }
});

test('manual validation messages cannot inject raw attribute keys', function () {
    $source = collect(File::allFiles(app_path()))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");

    expect($source)
        ->not->toMatch('/[\'\"](?:attribute|other)[\'\"]\s*=>\s*(?:[\'\"][^\'\"]+[\'\"]|\$[A-Za-z_])/')
        ->not->toMatch('/validation\.attributes\.(?:\{\$|\$)|validation\.attributes[\'\"]\s*\./');
});

test('Fortify registration uses the human profile name in Russian', function () {
    $this->withSession(['locale' => 'ru'])
        ->post(route('register.store'), [])
        ->assertSessionHasErrors([
            'name' => 'Заполните поле «имя».',
            'email' => 'Заполните поле «адрес электронной почты».',
            'password' => 'Заполните поле «пароль».',
        ]);

    app()->setLocale('ru');

    expect(Validator::make([], ['name' => ['required']])->errors()->first('name'))
        ->toBe('Заполните поле «название».');
});

test('the versioned API returns human Russian validation details', function () {
    $this->withHeader('Accept-Language', 'ru')
        ->postJson(route('api.v1.auth.register', absolute: false), [])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'validation_failed')
        ->assertJsonPath('error.details.name.0', 'Заполните поле «имя».')
        ->assertJsonPath('error.details.email.0', 'Заполните поле «адрес электронной почты».')
        ->assertJsonPath('error.details.password.0', 'Заполните поле «пароль».')
        ->assertJsonPath('error.details.password_confirmation.0', 'Заполните поле «подтверждение пароля».')
        ->assertJsonPath('error.details.device_name.0', 'Заполните поле «название устройства».');
});
