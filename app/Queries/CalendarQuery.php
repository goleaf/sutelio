<?php

namespace App\Queries;

use App\Models\Todo;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarQuery
{
    /** @return Collection<int, Todo> */
    public function forRange(Workspace $workspace, string $startDate, string $endDate): Collection
    {
        return $this->baseQuery($workspace)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, Todo> */
    public function overduePreview(Workspace $workspace, string $today, int $limit = 6): Collection
    {
        return $this->baseQuery($workspace)
            ->where('due_date', '<', $today)
            ->whereNull('completed_at')
            ->orderBy('due_date')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function overdueCount(Workspace $workspace, string $today): int
    {
        return $this->baseQuery($workspace)
            ->where('due_date', '<', $today)
            ->whereNull('completed_at')
            ->count();
    }

    /** @return HasMany<Todo, Workspace> */
    private function baseQuery(Workspace $workspace): HasMany
    {
        return $workspace->todos()
            ->select([
                'id', 'project_id', 'workspace_id', 'title', 'status', 'status_id',
                'priority', 'priority_id', 'due_date', 'completed_at',
            ])
            ->where('is_archived', false)
            ->whereNotNull('due_date')
            ->with([
                'project:id,name,color',
                'statusDefinition:id,workspace_id,key,name,translation_key,color,is_completed',
                'priorityDefinition:id,workspace_id,key,name,translation_key,color',
            ]);
    }
}
