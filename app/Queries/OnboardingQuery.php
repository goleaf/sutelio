<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\OnboardingStep;
use App\Models\Project;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class OnboardingQuery
{
    public const int OPTION_LIMIT = 100;

    /**
     * @return array{
     *     progress: array{step: string, position: int, total: int, percent: int, is_replay: bool},
     *     preferences: array{language: string, timezone: string, date_format: string, time_format: string, default_view: string, start_page: string, week_start: string},
     *     state: array<string, string>,
     *     recovery: string|null,
     *     options: array{
     *         workspaces: list<array{id: string, name: string, role: string|null}>,
     *         projects: list<array{id: string, name: string, color: string, icon: string}>,
     *         tasks: list<array{id: string, title: string, project_id: string|null, due_date: string|null}>,
     *         members: list<array{id: string, name: string, role: string|null}>,
     *         statuses: list<array{id: string, key: string, name: string, translation_key: string|null, color: string, is_default: bool}>,
     *         priorities: list<array{id: string, key: string, name: string, translation_key: string|null, color: string, is_default: bool}>
     *     }
     * }
     */
    public function forUser(User $user, UserPreference $preferences, bool $isReplay): array
    {
        $storedStep = $preferences->onboardingStep();
        $storedState = $preferences->onboardingState();
        $workspaceId = $this->stateId($storedState, 'workspace_id');
        $projectId = $this->stateId($storedState, 'project_id');
        $taskId = $this->stateId($storedState, 'task_id');

        $workspaces = $this->workspaces($user, $workspaceId);
        $workspace = $workspaceId !== null
            ? $workspaces->firstWhere('id', $workspaceId)
            : null;
        $projects = $workspace instanceof Workspace
            ? $this->projects($workspace, $projectId)
            : collect();
        $project = $projectId !== null
            ? $projects->firstWhere('id', $projectId)
            : null;
        $tasks = $workspace instanceof Workspace && $project instanceof Project
            ? $this->tasks($workspace, $project, $taskId)
            : collect();
        $task = $taskId !== null
            ? $tasks->firstWhere('id', $taskId)
            : null;

        [$effectiveStep, $recovery] = $this->effectiveStep(
            $storedStep,
            $workspaceId,
            $workspace,
            $projectId,
            $project,
            $taskId,
            $task,
        );
        $normalizedState = [];

        if ($workspace instanceof Workspace) {
            $normalizedState['workspace_id'] = $workspace->id;
        }

        if ($project instanceof Project) {
            $normalizedState['project_id'] = $project->id;
        }

        if ($task instanceof Todo) {
            $normalizedState['task_id'] = $task->id;
        }

        return [
            'progress' => $this->progress($effectiveStep, $isReplay),
            'preferences' => [
                'language' => $preferences->language,
                'timezone' => $preferences->timezone,
                'date_format' => $preferences->date_format,
                'time_format' => $preferences->time_format,
                'default_view' => $preferences->default_view,
                'start_page' => $preferences->start_page,
                'week_start' => $preferences->week_start,
            ],
            'state' => $normalizedState,
            'recovery' => $recovery,
            'options' => [
                'workspaces' => array_values($workspaces->map(fn (Workspace $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'role' => $this->pivotRole($item),
                ])->all()),
                'projects' => array_values($projects->map(fn (Project $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'color' => $item->color,
                    'icon' => $item->icon,
                ])->all()),
                'tasks' => array_values($tasks->map(fn (Todo $item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'project_id' => $item->project_id,
                    'due_date' => $item->due_date?->toDateString(),
                ])->all()),
                'members' => $workspace instanceof Workspace
                    ? array_values($this->members($workspace)->map(fn (User $item): array => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'role' => $this->pivotRole($item),
                    ])->all())
                    : [],
                'statuses' => $workspace instanceof Workspace
                    ? array_values($this->statuses($workspace)->map(fn (TaskStatus $item): array => [
                        'id' => $item->id,
                        'key' => $item->key,
                        'name' => is_string($item->translation_key)
                            ? __($item->translation_key)
                            : $item->name,
                        'translation_key' => $item->translation_key,
                        'color' => $item->color,
                        'is_default' => $item->is_default,
                    ])->all())
                    : [],
                'priorities' => $workspace instanceof Workspace
                    ? array_values($this->priorities($workspace)->map(fn (TaskPriority $item): array => [
                        'id' => $item->id,
                        'key' => $item->key,
                        'name' => is_string($item->translation_key)
                            ? __($item->translation_key)
                            : $item->name,
                        'translation_key' => $item->translation_key,
                        'color' => $item->color,
                        'is_default' => $item->is_default,
                    ])->all())
                    : [],
            ],
        ];
    }

    /** @return Collection<int, Workspace> */
    private function workspaces(User $user, ?string $selectedId): Collection
    {
        $query = $user->workspaces()
            ->select(['workspaces.id', 'workspaces.name'])
            ->reorder();

        if ($selectedId !== null) {
            $query->orderByRaw('CASE WHEN workspaces.id = ? THEN 0 ELSE 1 END', [$selectedId]);
        }

        return $query
            ->orderBy('workspaces.name')
            ->orderBy('workspaces.id')
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @return Collection<int, Project> */
    private function projects(Workspace $workspace, ?string $selectedId): Collection
    {
        $query = $workspace->projects()
            ->active()
            ->select(['id', 'workspace_id', 'name', 'color', 'icon', 'position'])
            ->reorder();

        if ($selectedId !== null) {
            $query->orderByRaw('CASE WHEN projects.id = ? THEN 0 ELSE 1 END', [$selectedId]);
        }

        return $query
            ->orderBy('position')
            ->orderBy('id')
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @return Collection<int, Todo> */
    private function tasks(Workspace $workspace, Project $project, ?string $selectedId): Collection
    {
        $query = $workspace->todos()
            ->active()
            ->where('project_id', $project->id)
            ->select(['id', 'workspace_id', 'project_id', 'title', 'due_date', 'position'])
            ->reorder();

        if ($selectedId !== null) {
            $query->orderByRaw('CASE WHEN todos.id = ? THEN 0 ELSE 1 END', [$selectedId]);
        }

        return $query
            ->orderBy('position')
            ->orderBy('id')
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @return Collection<int, User> */
    private function members(Workspace $workspace): Collection
    {
        return $workspace->members()
            ->select(['users.id', 'users.name'])
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @return Collection<int, TaskStatus> */
    private function statuses(Workspace $workspace): Collection
    {
        return $workspace->taskStatuses()
            ->active()
            ->select(['id', 'workspace_id', 'key', 'name', 'translation_key', 'color', 'position', 'is_default'])
            ->ordered()
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @return Collection<int, TaskPriority> */
    private function priorities(Workspace $workspace): Collection
    {
        return $workspace->taskPriorities()
            ->active()
            ->select(['id', 'workspace_id', 'key', 'name', 'translation_key', 'color', 'position', 'is_default'])
            ->ordered()
            ->limit(self::OPTION_LIMIT)
            ->get();
    }

    /** @param array<string, mixed> $state */
    private function stateId(array $state, string $key): ?string
    {
        $value = $state[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array{0: OnboardingStep, 1: string|null}
     */
    private function effectiveStep(
        OnboardingStep $storedStep,
        ?string $workspaceId,
        ?Workspace $workspace,
        ?string $projectId,
        ?Project $project,
        ?string $taskId,
        ?Todo $task,
    ): array {
        if ($workspaceId !== null && ! $workspace instanceof Workspace) {
            return [OnboardingStep::Workspace, 'workspace_unavailable'];
        }

        if ($storedStep->position() > OnboardingStep::Workspace->position()
            && ! $workspace instanceof Workspace) {
            return [OnboardingStep::Workspace, 'workspace_required'];
        }

        if ($projectId !== null && ! $project instanceof Project) {
            return [OnboardingStep::Project, 'project_unavailable'];
        }

        if ($storedStep->position() > OnboardingStep::Project->position()
            && ! $project instanceof Project) {
            return [OnboardingStep::Project, 'project_required'];
        }

        if ($taskId !== null && ! $task instanceof Todo) {
            return [OnboardingStep::Task, 'task_unavailable'];
        }

        if ($storedStep->position() > OnboardingStep::Task->position()
            && ! $task instanceof Todo) {
            return [OnboardingStep::Task, 'task_required'];
        }

        return [$storedStep, null];
    }

    /** @return array{step: string, position: int, total: int, percent: int, is_replay: bool} */
    private function progress(OnboardingStep $step, bool $isReplay): array
    {
        return [
            'step' => $step->value,
            'position' => $step->position(),
            'total' => count(OnboardingStep::ordered()),
            'percent' => $step->percent(),
            'is_replay' => $isReplay,
        ];
    }

    private function pivotRole(Workspace|User $model): ?string
    {
        $pivot = $model->getRelation('pivot');
        $role = $pivot instanceof Pivot ? $pivot->getAttribute('role') : null;

        return is_string($role) ? $role : null;
    }
}
