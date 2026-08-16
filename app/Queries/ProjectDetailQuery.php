<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectDetailQuery
{
    public const int PER_PAGE = 25;

    public const int ATTENTION_LIMIT = 5;

    public const int ASSIGNEE_LIMIT = 100;

    /**
     * @param  array{search: string|null, status: string|null, priority: string|null, assignee: string|null, attention: string, sort: string}  $filters
     * @return LengthAwarePaginator<int, Todo>
     */
    public function todos(
        Workspace $workspace,
        string $projectId,
        array $filters,
        CarbonImmutable $today,
    ): LengthAwarePaginator {
        $query = $this->filtered($workspace, $projectId, $filters, $today)
            ->with([
                'assignee:id,name',
                'labels:id,name,color',
                'statusDefinition:id,key,name,translation_key,color,position,is_completed',
                'priorityDefinition:id,key,name,translation_key,color,position',
            ]);

        $query = match ($filters['sort']) {
            'due_date' => $query
                ->orderByRaw('due_date ASC NULLS LAST')
                ->orderBy('position')
                ->orderBy('id'),
            'priority' => $query
                ->orderBy($this->priorityPositionSubquery())
                ->orderBy('position')
                ->orderBy('id'),
            'updated' => $query
                ->orderByDesc('updated_at')
                ->orderByDesc('id'),
            default => $query
                ->orderBy('position')
                ->orderBy('id'),
        };

        return $query
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * @return array{total: int, open: int, completed: int, completion_rate: int, attention: int, overdue: int, due_soon: int, unassigned: int}
     */
    public function metrics(Workspace $workspace, string $projectId, CarbonImmutable $today): array
    {
        $todayDate = $today->toDateString();
        $dueSoonDate = $today->addDays(7)->toDateString();
        $metrics = (array) $this->projectTasks($workspace, $projectId)
            ->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(CASE WHEN completed_at IS NULL THEN 1 END) AS open')
            ->selectRaw('COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) AS completed')
            ->selectRaw(
                'COUNT(CASE WHEN completed_at IS NULL AND due_date IS NOT NULL AND due_date < ? THEN 1 END) AS overdue',
                [$todayDate],
            )
            ->selectRaw(
                'COUNT(CASE WHEN completed_at IS NULL AND due_date BETWEEN ? AND ? THEN 1 END) AS due_soon',
                [$todayDate, $dueSoonDate],
            )
            ->selectRaw('COUNT(CASE WHEN completed_at IS NULL AND assigned_to IS NULL THEN 1 END) AS unassigned')
            ->first();

        $total = (int) ($metrics['total'] ?? 0);
        $completed = (int) ($metrics['completed'] ?? 0);
        $overdue = (int) ($metrics['overdue'] ?? 0);
        $dueSoon = (int) ($metrics['due_soon'] ?? 0);

        return [
            'total' => $total,
            'open' => (int) ($metrics['open'] ?? 0),
            'completed' => $completed,
            'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'attention' => $overdue + $dueSoon,
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'unassigned' => (int) ($metrics['unassigned'] ?? 0),
        ];
    }

    /** @return Collection<int, Todo> */
    public function attentionTasks(
        Workspace $workspace,
        string $projectId,
        CarbonImmutable $today,
    ): Collection {
        return $this->projectTasks($workspace, $projectId)
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $today->addDays(7)->toDateString())
            ->with([
                'assignee:id,name',
                'labels:id,name,color',
                'statusDefinition:id,key,name,translation_key,color,position,is_completed',
                'priorityDefinition:id,key,name,translation_key,color,position',
            ])
            ->orderBy('due_date')
            ->orderBy($this->priorityPositionSubquery())
            ->orderBy('position')
            ->orderBy('id')
            ->limit(self::ATTENTION_LIMIT)
            ->get();
    }

    /** @return list<array{id: string, name: string}> */
    public function assignees(Workspace $workspace, ?string $selectedAssignee = null): array
    {
        $assignees = $workspace->members()
            ->select(['users.id', 'users.name'])
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->limit(self::ASSIGNEE_LIMIT)
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => [
                'id' => $user->id,
                'name' => $user->name,
            ]]);

        if ($selectedAssignee !== null && ! $assignees->has($selectedAssignee)) {
            $selectedMember = $workspace->members()
                ->select(['users.id', 'users.name'])
                ->whereKey($selectedAssignee)
                ->first();

            if ($selectedMember instanceof User) {
                $assignees->put($selectedMember->id, [
                    'id' => $selectedMember->id,
                    'name' => $selectedMember->name,
                ]);
            }
        }

        return array_values($assignees->all());
    }

    /** @return list<array{id: string, key: string, name: string, color: string, count: int}> */
    public function priorityDistribution(Workspace $workspace, string $projectId): array
    {
        $counts = $this->projectTasks($workspace, $projectId)
            ->whereNull('completed_at')
            ->selectRaw('priority_id, COUNT(*) AS aggregate')
            ->groupBy('priority_id')
            ->pluck('aggregate', 'priority_id');

        $distribution = $workspace->taskPriorities()
            ->where(function (Builder $query) use ($counts): void {
                $query->where('is_archived', false)
                    ->when(
                        $counts->keys()->filter()->isNotEmpty(),
                        fn (Builder $query) => $query->orWhereIn('id', $counts->keys()->filter()->all()),
                    );
            })
            ->ordered()
            ->get(['id', 'key', 'name', 'translation_key', 'color'])
            ->map(fn (TaskPriority $priority): array => [
                'id' => $priority->id,
                'key' => $priority->key,
                'name' => is_string($priority->translation_key)
                    ? (string) __($priority->translation_key)
                    : $priority->name,
                'color' => $priority->color,
                'count' => (int) ($counts[$priority->id] ?? 0),
            ])
            ->values()
            ->all();

        return array_values($distribution);
    }

    /** @return Collection<int, TaskStatus> */
    public function statuses(Workspace $workspace): Collection
    {
        return $workspace->taskStatuses()->ordered()->get();
    }

    /** @return Collection<int, TaskPriority> */
    public function priorities(Workspace $workspace): Collection
    {
        return $workspace->taskPriorities()->ordered()->get();
    }

    /**
     * @param  array{search: string|null, status: string|null, priority: string|null, assignee: string|null, attention: string, sort: string}  $filters
     * @return Builder<Todo>
     */
    private function filtered(
        Workspace $workspace,
        string $projectId,
        array $filters,
        CarbonImmutable $today,
    ): Builder {
        $todayDate = $today->toDateString();

        return $this->projectTasks($workspace, $projectId)
            ->when($filters['search'] !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $query->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['status'] !== null, fn (Builder $query) => $query->where('status_id', $filters['status']))
            ->when($filters['priority'] !== null, fn (Builder $query) => $query->where('priority_id', $filters['priority']))
            ->when($filters['assignee'] !== null, fn (Builder $query) => $query->where('assigned_to', $filters['assignee']))
            ->when($filters['attention'] === 'overdue', fn (Builder $query) => $query
                ->whereNull('completed_at')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $todayDate))
            ->when($filters['attention'] === 'due_soon', fn (Builder $query) => $query
                ->whereNull('completed_at')
                ->whereBetween('due_date', [$todayDate, $today->addDays(7)->toDateString()]))
            ->when($filters['attention'] === 'unassigned', fn (Builder $query) => $query
                ->whereNull('completed_at')
                ->whereNull('assigned_to'));
    }

    /** @return Builder<Todo> */
    private function projectTasks(Workspace $workspace, string $projectId): Builder
    {
        return $workspace->todos()
            ->getQuery()
            ->where('project_id', $projectId)
            ->active();
    }

    /** @return Builder<TaskPriority> */
    private function priorityPositionSubquery(): Builder
    {
        $priority = new TaskPriority;
        $todo = new Todo;

        return TaskPriority::query()
            ->select('position')
            ->whereColumn(
                $priority->qualifyColumn('id'),
                $todo->qualifyColumn('priority_id'),
            );
    }
}
