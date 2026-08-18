<?php

namespace App\Actions\Fortify;

use App\Actions\EnsureUserHasWorkspace;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private readonly EnsureUserHasWorkspace $ensureUserHasWorkspace) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'language' => ['sometimes', Rule::enum(UserLanguage::class)],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
            'password' => $this->passwordRules(),
        ])->validate();

        $language = UserLanguage::tryFrom($input['language'] ?? App::currentLocale())
            ?? UserLanguage::English;

        return DB::transaction(function () use ($input, $language): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $user->preferences()->create([
                ...UserPreference::defaults(),
                ...UserPreference::pendingOnboardingDefaults(),
                'language' => $language->value,
                'timezone' => $input['timezone'] ?? UserPreference::defaults()['timezone'],
                'week_start' => $language->defaultWeekStart(),
            ]);

            $workspace = $this->ensureUserHasWorkspace->handle($user, $language);
            $user->setRelation('workspaces', collect([$workspace]));

            return $user;
        });
    }
}
