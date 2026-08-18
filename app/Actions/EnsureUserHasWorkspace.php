<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserLanguage;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class EnsureUserHasWorkspace
{
    public function __construct(private readonly CreateWorkspace $createWorkspace) {}

    public function handle(User $user, UserLanguage $language = UserLanguage::English): Workspace
    {
        return DB::transaction(function () use ($user, $language): Workspace {
            $lockedUser = User::query()
                ->select(['id'])
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $workspace = $lockedUser->workspaces()
                ->select(['workspaces.id'])
                ->reorder()
                ->orderBy('workspaces.created_at')
                ->orderBy('workspaces.id')
                ->first();

            if ($workspace instanceof Workspace) {
                return $workspace;
            }

            return $this->createWorkspace->handle([
                'name' => (string) trans('workspace.defaults.personal_name', locale: $language->value),
            ], $lockedUser);
        }, attempts: 5);
    }
}
