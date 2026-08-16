<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ArchiveProject;
use App\Actions\CreateProject;
use App\Actions\DuplicateProject;
use App\Actions\UpdateProject;
use App\Http\Requests\ProjectShowRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectTaskResource;
use App\Http\Resources\TaskPriorityResource;
use App\Http\Resources\TaskStatusResource;
use App\Models\Project;
use App\Models\Workspace;
use App\Queries\ProjectDetailQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(
        ProjectShowRequest $request,
        Workspace $workspace,
        Project $project,
        ProjectDetailQuery $projectDetailQuery,
    ): Response {
        $this->authorize('view', $project);
        $filters = $request->filters($workspace);
        $today = $request->today();
        $metrics = null;
        $resolveMetrics = function () use ($projectDetailQuery, $workspace, $project, $today, &$metrics): array {
            return $metrics ??= $projectDetailQuery->metrics($workspace, $project->id, $today);
        };

        return Inertia::render('projects/Show', [
            'project' => new ProjectResource($project),
            'todos' => Inertia::scroll(fn () => ProjectTaskResource::collection(
                $projectDetailQuery->todos($workspace, $project->id, $filters, $today),
            ))->matchOn('data.id'),
            'metrics' => $resolveMetrics,
            'filters' => $request->state(),
            'today' => $today->toDateString(),
            'attentionTasks' => fn (): array => [
                'data' => ProjectTaskResource::collection(
                    $projectDetailQuery->attentionTasks($workspace, $project->id, $today),
                )->resolve($request),
                'total' => $resolveMetrics()['attention'],
            ],
            'assignees' => fn (): array => $projectDetailQuery->assignees(
                $workspace,
                $filters['assignee'],
            ),
            'priorityDistribution' => fn (): array => $projectDetailQuery->priorityDistribution(
                $workspace,
                $project->id,
            ),
            'workspace' => ['id' => $workspace->id],
            'taskDefinitions' => fn (): array => [
                'statuses' => TaskStatusResource::collection(
                    $projectDetailQuery->statuses($workspace),
                )->resolve($request),
                'priorities' => TaskPriorityResource::collection(
                    $projectDetailQuery->priorities($workspace),
                )->resolve($request),
            ],
        ]);
    }

    public function store(StoreProjectRequest $request, Workspace $workspace, CreateProject $action): JsonResponse
    {
        $this->authorize('create', [Project::class, $workspace]);
        $project = $action->handle($workspace, $request->projectData());

        return response()->json(['project' => new ProjectResource($project)], 201);
    }

    public function update(UpdateProjectRequest $request, Workspace $workspace, Project $project, UpdateProject $action): JsonResponse
    {
        $project = $action->handle($project, $request->validated());

        return response()->json(['project' => new ProjectResource($project)]);
    }

    public function destroy(Workspace $workspace, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        return response()->json(null, 204);
    }

    public function archive(Workspace $workspace, Project $project, ArchiveProject $action): JsonResponse
    {
        $this->authorize('archive', $project);
        $project = $action->handle($project);

        return response()->json(['project' => new ProjectResource($project)]);
    }

    public function restore(Workspace $workspace, Project $project): JsonResponse
    {
        $this->authorize('archive', $project);
        $project->update(['is_archived' => false]);

        return response()->json(['project' => new ProjectResource($project->fresh())]);
    }

    public function duplicate(Workspace $workspace, Project $project, DuplicateProject $action): JsonResponse
    {
        $this->authorize('create', [Project::class, $workspace]);
        $project = $action->handle($project);

        return response()->json(['project' => new ProjectResource($project)], 201);
    }

    public function reorder(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        foreach ($request->items as $item) {
            $workspace->projects()->where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(null, 204);
    }
}
