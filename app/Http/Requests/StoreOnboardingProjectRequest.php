<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $workspaceId = $this->selectedStateId('workspace_id');

        return [
            'mode' => ['required', Rule::in(['select', 'create'])],
            'request_key' => ['required', 'uuid'],
            'project_id' => [
                'exclude_unless:mode,select',
                'required',
                'uuid',
                Rule::exists('projects', 'id')
                    ->where('workspace_id', $workspaceId)
                    ->where('is_archived', 0),
            ],
            'name' => ['exclude_unless:mode,create', 'required', 'string', 'max:255'],
            'description' => ['exclude_unless:mode,create', 'nullable', 'string', 'max:1000'],
            'color' => ['exclude_unless:mode,create', 'sometimes', 'string', 'max:7'],
            'icon' => ['exclude_unless:mode,create', 'sometimes', 'string', 'max:50'],
        ];
    }

    public function mode(): string
    {
        return $this->string('mode')->toString();
    }

    public function requestKey(): string
    {
        return $this->string('request_key')->toString();
    }

    public function projectId(): ?string
    {
        $projectId = $this->validated('project_id');

        return is_string($projectId) ? $projectId : null;
    }

    /** @return array{name: string, description?: string|null, color?: string, icon?: string} */
    public function projectData(): array
    {
        $data = ['name' => $this->string('name')->toString()];

        foreach (['description', 'color', 'icon'] as $key) {
            $value = $this->validated($key);

            if (is_string($value) || ($key === 'description' && $value === null)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function selectedStateId(string $key): string
    {
        $state = $this->user()?->preferences()->first()?->getAttribute('onboarding_state');
        $value = is_array($state) ? ($state[$key] ?? null) : null;

        return is_string($value) ? $value : '';
    }
}
