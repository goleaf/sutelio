<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOnboardingPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'language' => ['required', Rule::enum(UserLanguage::class)],
            'timezone' => ['required', 'string', 'timezone:all'],
            'date_format' => ['required', Rule::in(UserPreference::DATE_FORMATS)],
            'time_format' => ['required', Rule::in(UserPreference::TIME_FORMATS)],
            'default_view' => ['required', Rule::in(UserPreference::DEFAULT_VIEWS)],
            'start_page' => ['required', Rule::in(UserPreference::START_PAGES)],
            'week_start' => ['required', Rule::in(UserPreference::WEEK_STARTS)],
        ];
    }

    /** @return array{language: string, timezone: string, date_format: string, time_format: string, default_view: string, start_page: string, week_start: string} */
    public function preferenceData(): array
    {
        return [
            'language' => $this->string('language')->toString(),
            'timezone' => $this->string('timezone')->toString(),
            'date_format' => $this->string('date_format')->toString(),
            'time_format' => $this->string('time_format')->toString(),
            'default_view' => $this->string('default_view')->toString(),
            'start_page' => $this->string('start_page')->toString(),
            'week_start' => $this->string('week_start')->toString(),
        ];
    }
}
