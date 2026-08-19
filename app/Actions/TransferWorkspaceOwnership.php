<?php

namespace App\Actions;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferWorkspaceOwnership
{
    public function handle(Workspace $workspace, User $actor, User $newOwner): Workspace
    {
        return DB::transaction(function () use ($workspace, $actor, $newOwner): Workspace {
            if ($actor->is($newOwner)) {
                throw ValidationException::withMessages([
                    'user_id' => [__('validation.different', [
                        'attribute' => __('validation.attributes.user_id'),
                        'other' => __('validation.attributes.owner'),
                    ])],
                ]);
            }

            $workspaceUpdated = Workspace::query()
                ->whereKey($workspace->id)
                ->where('owner_id', $actor->id)
                ->update(['owner_id' => $newOwner->id]);

            if ($workspaceUpdated !== 1) {
                throw ValidationException::withMessages([
                    'user_id' => [__('validation.in', [
                        'attribute' => __('validation.attributes.user_id'),
                    ])],
                ]);
            }

            $currentOwnerUpdated = WorkspaceMember::query()
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $actor->id)
                ->where('role', WorkspaceRole::Owner)
                ->update(['role' => WorkspaceRole::Admin]);
            $newOwnerUpdated = WorkspaceMember::query()
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $newOwner->id)
                ->whereNot('role', WorkspaceRole::Owner)
                ->update(['role' => WorkspaceRole::Owner]);

            if ($currentOwnerUpdated !== 1 || $newOwnerUpdated !== 1) {
                throw ValidationException::withMessages([
                    'user_id' => [__('validation.exists', [
                        'attribute' => __('validation.attributes.user_id'),
                    ])],
                ]);
            }

            return Workspace::query()->findOrFail($workspace->id);
        }, 5);
    }
}
