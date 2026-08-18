<?php

namespace App\Queries;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;

class ProjectIndexQuery
{
    private const COLUMNS = [
        'projects.id',
        'projects.workspace_id',
        'projects.name',
        'projects.description',
        'projects.color',
        'projects.icon',
        'projects.is_archived',
        'projects.updated_at',
    ];

    /** @return Collection<int, Project> */
    public function forWorkspace(Workspace $workspace): Collection
    {
        return $workspace->projects()
            ->select(self::COLUMNS)
            ->withCount('todos')
            ->get();
    }
}
