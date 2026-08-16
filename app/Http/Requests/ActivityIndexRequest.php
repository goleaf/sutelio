<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ActivityCategory;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActivityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', Rule::in([
                'all',
                ...array_column(ActivityCategory::cases(), 'value'),
            ])],
            'actor' => ['sometimes', 'nullable', 'uuid'],
            'period' => ['sometimes', 'string', Rule::in(['all', '7d', '30d', '90d'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{category: ActivityCategory|null, actor: string|null, period: string}
     *
     * @throws ValidationException
     */
    public function filters(Workspace $workspace): array
    {
        $actor = $this->actor();

        if ($actor !== null && ! $workspace->members()->whereKey($actor)->exists()) {
            throw ValidationException::withMessages([
                'actor' => __('validation.exists', ['attribute' => 'actor']),
            ]);
        }

        return [
            'category' => $this->category(),
            'actor' => $actor,
            'period' => $this->period(),
        ];
    }

    /** @return array{category: string, actor: string|null, period: string} */
    public function state(): array
    {
        $category = $this->category();

        return [
            'category' => $category instanceof ActivityCategory ? $category->value : 'all',
            'actor' => $this->actor(),
            'period' => $this->period(),
        ];
    }

    public function category(): ?ActivityCategory
    {
        $category = $this->validated('category');

        return is_string($category) && $category !== 'all'
            ? ActivityCategory::tryFrom($category)
            : null;
    }

    public function actor(): ?string
    {
        $actor = $this->validated('actor');

        return is_string($actor) && $actor !== '' ? $actor : null;
    }

    public function period(): string
    {
        $period = $this->validated('period');

        return is_string($period) ? $period : 'all';
    }
}
