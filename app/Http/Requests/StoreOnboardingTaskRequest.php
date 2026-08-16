<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $workspaceId = $this->selectedStateId('workspace_id');
        $projectId = $this->selectedStateId('project_id');

        return [
            'mode' => ['required', Rule::in(['select', 'create'])],
            'request_key' => ['required', 'uuid'],
            'task_id' => [
                'exclude_unless:mode,select',
                'required',
                'uuid',
                Rule::exists('todos', 'id')
                    ->where('workspace_id', $workspaceId)
                    ->where('project_id', $projectId)
                    ->where('is_archived', 0)
                    ->whereNull('deleted_at'),
            ],
            'title' => ['exclude_unless:mode,create', 'required', 'string', 'max:500'],
            'description' => ['exclude_unless:mode,create', 'nullable', 'string'],
            'status_id' => [
                'exclude_unless:mode,create',
                'required',
                'uuid',
                Rule::exists('task_statuses', 'id')
                    ->where('workspace_id', $workspaceId)
                    ->where('is_archived', 0),
            ],
            'priority_id' => [
                'exclude_unless:mode,create',
                'required',
                'uuid',
                Rule::exists('task_priorities', 'id')
                    ->where('workspace_id', $workspaceId)
                    ->where('is_archived', 0),
            ],
            'assigned_to' => [
                'exclude_unless:mode,create',
                'nullable',
                'uuid',
                Rule::exists('workspace_members', 'user_id')->where('workspace_id', $workspaceId),
            ],
            'due_date' => ['exclude_unless:mode,create', 'nullable', 'date'],
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

    public function taskId(): ?string
    {
        $taskId = $this->validated('task_id');

        return is_string($taskId) ? $taskId : null;
    }

    /** @return array{title: string, project_id: string, assigned_to: string|null, description: string|null, status_id: string, priority_id: string, due_date: string|null} */
    public function taskData(): array
    {
        return [
            'title' => $this->string('title')->toString(),
            'project_id' => $this->selectedStateId('project_id'),
            'assigned_to' => $this->nullableString('assigned_to'),
            'description' => $this->nullableString('description'),
            'status_id' => $this->string('status_id')->toString(),
            'priority_id' => $this->string('priority_id')->toString(),
            'due_date' => $this->nullableString('due_date'),
        ];
    }

    private function selectedStateId(string $key): string
    {
        $state = $this->user()?->preferences()->first()?->getAttribute('onboarding_state');
        $value = is_array($state) ? ($state[$key] ?? null) : null;

        return is_string($value) ? $value : '';
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) ? $value : null;
    }
}
