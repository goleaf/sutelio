<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\ReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/** @mixin DatabaseNotification */
final class NotificationInboxResource extends JsonResource
{
    /** @var list<string> */
    private const KINDS = ['comment', 'completion', 'general', 'overdue', 'reminder'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = $this->notificationData();

        return [
            'id' => (string) $this->id,
            'kind' => $this->kind($data),
            'title' => $this->safeText(Arr::get($data, 'title'), 180),
            'body' => $this->safeText(
                Arr::get($data, 'body', Arr::get($data, 'message')),
                500,
            ),
            'task_title' => $this->safeText(Arr::get($data, 'todo_title'), 180),
            'browser_delivery' => Arr::get($data, 'channel') === 'browser',
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'created_date' => $this->created_at?->timezone($this->timezone($request))->toDateString(),
            'url' => $this->getAttribute('action_url'),
        ];
    }

    /** @return array<string, mixed> */
    private function notificationData(): array
    {
        $data = $this->resource->getAttribute('data');

        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    private function kind(array $data): string
    {
        if ($this->type === ReminderNotification::class || Arr::get($data, 'kind') === 'reminder') {
            return 'reminder';
        }

        $kind = Arr::get($data, 'kind');

        return is_string($kind) && in_array($kind, self::KINDS, true)
            ? $kind
            : 'general';
    }

    private function safeText(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = Str::squish(strip_tags($value));

        return $text === '' ? null : Str::limit($text, $limit);
    }

    private function timezone(Request $request): string
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->relationLoaded('preferences')) {
            return UserPreference::defaults()['timezone'];
        }

        $preferences = $user->getRelation('preferences');

        return $preferences instanceof UserPreference
            ? $preferences->timezone
            : UserPreference::defaults()['timezone'];
    }
}
