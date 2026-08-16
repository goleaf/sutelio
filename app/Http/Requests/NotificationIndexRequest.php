<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class NotificationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['all', 'unread', 'read'])],
            'kind' => ['sometimes', 'string', Rule::in(['all', 'reminders', 'updates'])],
            'per_page' => ['sometimes', 'integer', Rule::in([20, 50])],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function status(): string
    {
        $status = $this->validated('status');

        return is_string($status) ? $status : 'all';
    }

    public function kind(): string
    {
        $kind = $this->validated('kind');

        return is_string($kind) ? $kind : 'all';
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 20);
    }

    /** @return array{status: string, kind: string, per_page: int} */
    public function state(): array
    {
        return [
            'status' => $this->status(),
            'kind' => $this->kind(),
            'per_page' => $this->perPage(),
        ];
    }

    public function today(): string
    {
        $user = $this->user();
        $timezone = $user instanceof User ? $user->preferences?->timezone : null;
        $fallbackTimezone = config('app.timezone', 'UTC');

        return CarbonImmutable::now(
            is_string($timezone)
                ? $timezone
                : (is_string($fallbackTimezone) ? $fallbackTimezone : 'UTC'),
        )->toDateString();
    }
}
