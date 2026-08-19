<?php

namespace App\Http\Requests;

use App\Models\Todo;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class CreateTodoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && $this->user()?->can('create', [Todo::class, $workspace]) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'uuid'],
        ];
    }

    public function projectId(): ?string
    {
        $projectId = $this->validated('project_id');

        return is_string($projectId) ? $projectId : null;
    }
}
