<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ActivityCategory;
use App\Http\Requests\ActivityIndexRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\User;
use App\Models\Workspace;
use App\Queries\ActivityIndexQuery;
use App\Queries\CurrentWorkspaceQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function current(
        ActivityIndexRequest $request,
        CurrentWorkspaceQuery $currentWorkspaceQuery,
        ActivityIndexQuery $activityIndexQuery,
    ): Response {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $workspace = $currentWorkspaceQuery->forUser(
            $user,
            $request->session()->get('current_workspace_id'),
        );

        return $this->render($request, $workspace, $activityIndexQuery);
    }

    public function index(
        ActivityIndexRequest $request,
        Workspace $workspace,
        ActivityIndexQuery $activityIndexQuery,
    ): Response {
        return $this->render($request, $workspace, $activityIndexQuery);
    }

    private function render(
        ActivityIndexRequest $request,
        ?Workspace $workspace,
        ActivityIndexQuery $activityIndexQuery,
    ): Response {
        if (! $workspace) {
            return Inertia::render('activity/Index', [
                'activities' => Inertia::scroll(ActivityLogResource::collection(
                    new LengthAwarePaginator(
                        [],
                        0,
                        ActivityIndexQuery::PER_PAGE,
                        1,
                        ['path' => $request->url()],
                    ),
                )),
                'metrics' => fn (): array => [
                    'recorded_actions' => 0,
                    'contributors' => 0,
                    'recent_changes' => 0,
                ],
                'filters' => $request->state(),
                'categories' => fn (): array => array_column(ActivityCategory::cases(), 'value'),
                'contributors' => fn (): array => [],
                'workspace' => null,
            ]);
        }

        $this->authorize('view', $workspace);
        $filters = $request->filters($workspace);

        return Inertia::render('activity/Index', [
            'activities' => Inertia::scroll(ActivityLogResource::collection(
                $activityIndexQuery->forWorkspace($workspace, $filters),
            )),
            'metrics' => fn (): array => $activityIndexQuery->metricsForWorkspace($workspace),
            'filters' => $request->state(),
            'categories' => fn (): array => array_column(ActivityCategory::cases(), 'value'),
            'contributors' => fn (): array => $activityIndexQuery->contributorsForWorkspace(
                $workspace,
                $filters['actor'],
            ),
            'workspace' => $workspace->only(['id', 'name']),
        ]);
    }
}
