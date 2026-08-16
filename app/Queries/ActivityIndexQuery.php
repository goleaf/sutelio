<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ActivityCategory;
use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ActivityIndexQuery
{
    private const ACTOR_FILTER_INDEX = 'activity_logs_workspace_user_created_index';

    private const EVENT_FILTER_INDEX = 'activity_logs_workspace_event_created_index';

    public const PER_PAGE = 20;

    public const CONTRIBUTOR_LIMIT = 100;

    /**
     * @param  array{category: ActivityCategory|null, actor: string|null, period: string}  $filters
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function forWorkspace(Workspace $workspace, array $filters): LengthAwarePaginator
    {
        return $this->filtered($workspace, $filters)
            ->select([
                'id',
                'user_id',
                'subject_type',
                'subject_id',
                'event',
                'properties',
                'created_at',
            ])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /** @return array{recorded_actions: int, contributors: int, recent_changes: int} */
    public function metricsForWorkspace(Workspace $workspace): array
    {
        $recentThreshold = CarbonImmutable::now()->subDays(7);
        $metrics = (array) $workspace->activityLogs()
            ->toBase()
            ->selectRaw('COUNT(*) AS recorded_actions')
            ->selectRaw('COUNT(DISTINCT user_id) AS contributors')
            ->selectRaw(
                'COUNT(CASE WHEN created_at >= ? THEN 1 END) AS recent_changes',
                [$recentThreshold->toDateTimeString()],
            )
            ->first();

        return [
            'recorded_actions' => (int) ($metrics['recorded_actions'] ?? 0),
            'contributors' => (int) ($metrics['contributors'] ?? 0),
            'recent_changes' => (int) ($metrics['recent_changes'] ?? 0),
        ];
    }

    /** @return list<array{id: string, name: string}> */
    public function contributorsForWorkspace(Workspace $workspace, ?string $selectedActor = null): array
    {
        $contributors = $workspace->members()
            ->select(['users.id', 'users.name'])
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->limit(self::CONTRIBUTOR_LIMIT)
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => [
                'id' => $user->id,
                'name' => $user->name,
            ]]);

        if ($selectedActor !== null && ! $contributors->has($selectedActor)) {
            $selectedContributor = $workspace->members()
                ->select(['users.id', 'users.name'])
                ->whereKey($selectedActor)
                ->first();

            if ($selectedContributor instanceof User) {
                $contributors->put($selectedContributor->id, [
                    'id' => $selectedContributor->id,
                    'name' => $selectedContributor->name,
                ]);
            }
        }

        return array_values($contributors->all());
    }

    /**
     * @param  array{category: ActivityCategory|null, actor: string|null, period: string}  $filters
     * @return Builder<ActivityLog>
     */
    private function filtered(Workspace $workspace, array $filters): Builder
    {
        $events = $filters['category'] instanceof ActivityCategory
            ? array_map(
                fn (ActivityEvent $event): string => $event->value,
                $filters['category']->events(),
            )
            : [];
        $days = match ($filters['period']) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => null,
        };
        $query = $workspace->activityLogs()->getQuery();

        if ($filters['actor'] !== null) {
            $query->getQuery()->fromRaw('activity_logs INDEXED BY '.self::ACTOR_FILTER_INDEX);
        } elseif ($events !== []) {
            $query->getQuery()->fromRaw('activity_logs INDEXED BY '.self::EVENT_FILTER_INDEX);
        }

        return $query
            ->when($events !== [], fn (Builder $query) => $query->whereIn('event', $events))
            ->when($filters['actor'] !== null, fn (Builder $query) => $query->where('user_id', $filters['actor']))
            ->when($days !== null, fn (Builder $query) => $query->where(
                'created_at',
                '>=',
                CarbonImmutable::now()->subDays($days),
            ));
    }
}
