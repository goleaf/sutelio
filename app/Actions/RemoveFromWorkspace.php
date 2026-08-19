<?php

namespace App\Actions;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveFromWorkspace
{
    public function handle(WorkspaceMember $membership, User $actor): bool
    {
        return DB::transaction(function () use ($membership, $actor): bool {
            $deleted = WorkspaceMember::query()
                ->whereKey($membership->id)
                ->where('workspace_id', $membership->workspace_id)
                ->where('user_id', '!=', $actor->id)
                ->whereNot('role', WorkspaceRole::Owner)
                ->whereHas('workspace.memberships', fn ($query) => $query
                    ->where('user_id', $actor->id)
                    ->whereIn('role', [WorkspaceRole::Owner, WorkspaceRole::Admin]))
                ->delete();

            if ($deleted !== 1) {
                throw ValidationException::withMessages([
                    'member' => [__('validation.in', ['attribute' => __('validation.attributes.member')])],
                ]);
            }

            return true;
        }, 5);
    }
}
