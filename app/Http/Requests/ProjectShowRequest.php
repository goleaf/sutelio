<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:200'],
            'status' => ['sometimes', 'nullable', 'uuid'],
            'priority' => ['sometimes', 'nullable', 'uuid'],
            'assignee' => ['sometimes', 'nullable', 'uuid'],
            'attention' => ['sometimes', 'string', Rule::in(['all', 'overdue', 'due_soon', 'unassigned'])],
            'sort' => ['sometimes', 'string', Rule::in(['position', 'due_date', 'priority', 'updated'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{search: string|null, status: string|null, priority: string|null, assignee: string|null, attention: string, sort: string}
     *
     * @throws ValidationException
     */
    public function filters(Workspace $workspace): array
    {
        $filters = $this->state();

        $this->assertWorkspaceIdentifier(
            'status',
            $filters['status'],
            fn (string $id): bool => $workspace->taskStatuses()->active()->whereKey($id)->exists(),
            __('validation.exists', ['attribute' => __('validation.attributes.status')]),
        );
        $this->assertWorkspaceIdentifier(
            'priority',
            $filters['priority'],
            fn (string $id): bool => $workspace->taskPriorities()->active()->whereKey($id)->exists(),
            __('validation.exists', ['attribute' => __('validation.attributes.priority')]),
        );
        $this->assertWorkspaceIdentifier(
            'assignee',
            $filters['assignee'],
            fn (string $id): bool => $workspace->members()->whereKey($id)->exists(),
            __('validation.exists', ['attribute' => __('validation.attributes.assignee')]),
        );

        return $filters;
    }

    /** @return array{search: string|null, status: string|null, priority: string|null, assignee: string|null, attention: string, sort: string} */
    public function state(): array
    {
        return [
            'search' => $this->search(),
            'status' => $this->identifier('status'),
            'priority' => $this->identifier('priority'),
            'assignee' => $this->identifier('assignee'),
            'attention' => $this->attention(),
            'sort' => $this->sort(),
        ];
    }

    public function today(): CarbonImmutable
    {
        $user = $this->user();
        $timezone = $user instanceof User ? $user->preferences?->timezone : null;

        $fallbackTimezone = config('app.timezone', 'UTC');
        $resolvedTimezone = is_string($timezone)
            ? $timezone
            : (is_string($fallbackTimezone) ? $fallbackTimezone : 'UTC');

        return CarbonImmutable::now($resolvedTimezone)
            ->startOfDay();
    }

    private function search(): ?string
    {
        $search = $this->validated('search');

        if (! is_string($search)) {
            return null;
        }

        $normalized = Str::squish($search);

        return $normalized !== '' ? $normalized : null;
    }

    private function identifier(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function attention(): string
    {
        $attention = $this->validated('attention');

        return is_string($attention) ? $attention : 'all';
    }

    private function sort(): string
    {
        $sort = $this->validated('sort');

        return is_string($sort) ? $sort : 'position';
    }

    /** @param callable(string): bool $exists */
    private function assertWorkspaceIdentifier(
        string $key,
        ?string $value,
        callable $exists,
        string $message,
    ): void {
        if ($value === null || $exists($value)) {
            return;
        }

        throw ValidationException::withMessages([
            $key => $message,
        ]);
    }
}
