<?php

namespace App\Queries;

use App\Models\Project;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use App\Models\Workspace;
use App\Services\TodoFilterService;
use App\Services\TodoSortService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoIndexQuery
{
    public const int PROJECT_OPTION_LIMIT = 100;

    public function __construct(
        private TodoFilterService $filterService,
        private TodoSortService $sortService,
    ) {}

    /**
     * @param  array{search?: string|null, project_id?: string|null, status?: string|null, priority?: string|null, assigned_to?: string|null, label_id?: string|null, tag_id?: string|null, is_pinned?: bool|null, is_favorite?: bool|null, due_date_from?: string|null, due_date_to?: string|null, overdue?: bool|null, completed_today?: bool|null}  $filters
     * @return LengthAwarePaginator<int, Todo>
     */
    public function todos(
        Workspace $workspace,
        array $filters,
        string $timezone,
        ?string $sort,
        ?string $direction,
        int $perPage,
    ): LengthAwarePaginator {
        $query = $this->filtered($workspace, $filters, $timezone)
            ->with(['project', 'assignee', 'labels', 'tags', 'statusDefinition', 'priorityDefinition'])
            ->active();

        $query = $this->sortService->apply($query, $sort, $direction);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{search?: string|null, project_id?: string|null, status?: string|null, priority?: string|null, assigned_to?: string|null, label_id?: string|null, tag_id?: string|null, is_pinned?: bool|null, is_favorite?: bool|null, due_date_from?: string|null, due_date_to?: string|null, overdue?: bool|null, completed_today?: bool|null}  $filters
     * @return array{total: int, pending: int, completed: int}
     */
    public function stats(Workspace $workspace, array $filters, string $timezone): array
    {
        $counts = (array) $this->filtered($workspace, $filters, $timezone)
            ->active()
            ->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(CASE WHEN completed_at IS NULL THEN 1 END) AS pending')
            ->selectRaw('COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) AS completed')
            ->first();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
        ];
    }

    /** @return Collection<int, Project> */
    public function projects(Workspace $workspace): Collection
    {
        return $this->projectQuery($workspace)->get();
    }

    /** @return Collection<int, Project> */
    public function projectOptions(Workspace $workspace, ?string $selectedProjectId): Collection
    {
        $projects = $this->projectQuery($workspace)
            ->orderBy('projects.position')
            ->orderBy('projects.id')
            ->limit(self::PROJECT_OPTION_LIMIT)
            ->get();

        if ($selectedProjectId === null || $projects->contains('id', $selectedProjectId)) {
            return $projects;
        }

        $selectedProject = $this->projectQuery($workspace)
            ->whereKey($selectedProjectId)
            ->first();

        if ($selectedProject !== null) {
            $projects->push($selectedProject);
        }

        return $projects;
    }

    /** @return HasMany<Project, Workspace> */
    private function projectQuery(Workspace $workspace): HasMany
    {
        return $workspace->projects()
            ->select([
                'projects.id',
                'projects.workspace_id',
                'projects.name',
                'projects.description',
                'projects.color',
                'projects.icon',
                'projects.is_archived',
                'projects.position',
                'projects.created_at',
                'projects.updated_at',
            ])
            ->active();
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
     * @param  array{search?: string|null, project_id?: string|null, status?: string|null, priority?: string|null, assigned_to?: string|null, label_id?: string|null, tag_id?: string|null, is_pinned?: bool|null, is_favorite?: bool|null, due_date_from?: string|null, due_date_to?: string|null, overdue?: bool|null, completed_today?: bool|null}  $filters
     * @return Builder<Todo>
     */
    private function filtered(Workspace $workspace, array $filters, string $timezone): Builder
    {
        return $this->filterService->apply(
            $workspace->todos()->getQuery(),
            $filters,
            $timezone,
        );
    }
}
