<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $userId = $this->user()?->getAuthIdentifier();

        return [
            'mode' => ['required', Rule::in(['select', 'create'])],
            'request_key' => ['required', 'uuid'],
            'workspace_id' => [
                'exclude_unless:mode,select',
                'required',
                'uuid',
                Rule::exists('workspace_members', 'workspace_id')->where('user_id', $userId),
            ],
            'name' => ['exclude_unless:mode,create', 'required', 'string', 'max:255'],
            'description' => ['exclude_unless:mode,create', 'nullable', 'string', 'max:1000'],
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

    public function workspaceId(): ?string
    {
        $workspaceId = $this->validated('workspace_id');

        return is_string($workspaceId) ? $workspaceId : null;
    }

    /** @return array{name: string, description?: string|null} */
    public function workspaceData(): array
    {
        $description = $this->validated('description');

        return [
            'name' => $this->string('name')->toString(),
            'description' => is_string($description) ? $description : null,
        ];
    }
}
