<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Todo;
use App\Models\User;
use App\Notifications\ReminderNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationIndexQuery
{
    private const PAYLOAD_KIND_EXPRESSION = "CASE WHEN json_valid(data) THEN json_extract(data, '$.kind') END";

    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    public function forUser(
        User $user,
        string $status = 'all',
        string $kind = 'all',
        int $perPage = 20,
    ): LengthAwarePaginator {
        $notifications = $user->notifications()
            ->select(['id', 'type', 'data', 'read_at', 'created_at'])
            ->when($status === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->when($kind === 'reminders', fn (Builder $query) => $this->reminders($query))
            ->when($kind === 'updates', fn (Builder $query) => $this->updates($query))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $authorizedTodoIds = $this->authorizedTodoIds($user, $notifications);

        foreach ($notifications->items() as $notification) {
            $todoId = $this->todoId($notification);
            $notification->setAttribute(
                'action_url',
                $todoId !== null && in_array($todoId, $authorizedTodoIds, true)
                    ? route('todos.show', ['todo' => $todoId])
                    : null,
            );
        }

        return $notifications;
    }

    /** @return array{total: int, unread: int, read: int} */
    public function statsForUser(User $user): array
    {
        $stats = $user->notifications()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(CASE WHEN read_at IS NULL THEN 1 END) AS unread')
            ->first();
        $total = (int) ($stats?->getAttribute('total') ?? 0);
        $unread = (int) ($stats?->getAttribute('unread') ?? 0);

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
        ];
    }

    /** @param Builder<DatabaseNotification> $query */
    private function reminders(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query
                ->where('type', ReminderNotification::class)
                ->orWhereRaw(self::PAYLOAD_KIND_EXPRESSION.' = ?', ['reminder']);
        });
    }

    /** @param Builder<DatabaseNotification> $query */
    private function updates(Builder $query): void
    {
        $query
            ->where('type', '!=', ReminderNotification::class)
            ->where(function (Builder $query): void {
                $query
                    ->whereRaw(self::PAYLOAD_KIND_EXPRESSION.' IS NULL')
                    ->orWhereRaw(self::PAYLOAD_KIND_EXPRESSION.' != ?', ['reminder']);
            });
    }

    /**
     * @param  LengthAwarePaginator<int, DatabaseNotification>  $notifications
     * @return list<string>
     */
    private function authorizedTodoIds(User $user, LengthAwarePaginator $notifications): array
    {
        $todoIds = collect($notifications->items())
            ->map(fn (DatabaseNotification $notification): ?string => $this->todoId($notification))
            ->filter()
            ->unique()
            ->values();

        if ($todoIds->isEmpty()) {
            return [];
        }

        return array_values(Todo::query()
            ->select('todos.id')
            ->join('workspaces', 'workspaces.id', '=', 'todos.workspace_id')
            ->leftJoin('workspace_members', function (JoinClause $join) use ($user): void {
                $join->on('workspace_members.workspace_id', '=', 'workspaces.id')
                    ->where('workspace_members.user_id', '=', $user->id);
            })
            ->whereIn('todos.id', $todoIds->all())
            ->where(function (Builder $query) use ($user): void {
                $query->where('workspaces.owner_id', $user->id)
                    ->orWhereNotNull('workspace_members.id');
            })
            ->distinct()
            ->get()
            ->map(fn (Todo $todo): string => $todo->id)
            ->values()
            ->all());
    }

    private function todoId(DatabaseNotification $notification): ?string
    {
        $todoId = $notification->data['todo_id'] ?? null;

        return is_string($todoId) && $todoId !== '' ? $todoId : null;
    }
}
