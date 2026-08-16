<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OnboardingStep;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_step' => ['required', Rule::enum(OnboardingStep::class)],
        ];
    }

    public function targetStep(): OnboardingStep
    {
        return OnboardingStep::from((string) $this->validated('target_step'));
    }
}
