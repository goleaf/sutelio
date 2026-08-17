<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserLanguage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'language' => ['required', Rule::enum(UserLanguage::class)],
        ];
    }

    public function language(): UserLanguage
    {
        return UserLanguage::from($this->string('language')->toString());
    }
}
