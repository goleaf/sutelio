<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WorkspaceRole;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkspaceMember>
 */
class WorkspaceMemberFactory extends Factory
{
    protected $model = WorkspaceMember::class;

    public function definition(): array
    {
        return [
            'workspace_id' => WorkspaceFactory::new(),
            'user_id' => UserFactory::new(),
            'role' => WorkspaceRole::Member,
        ];
    }

    public function owner(): static
    {
        return $this
            ->state(fn (): array => ['role' => WorkspaceRole::Owner])
            ->afterCreating(function (WorkspaceMember $membership): void {
                $workspace = $membership->workspace;

                $workspace->memberships()
                    ->whereKeyNot($membership->id)
                    ->where('role', WorkspaceRole::Owner)
                    ->update(['role' => WorkspaceRole::Member]);

                $workspace->update(['owner_id' => $membership->user_id]);
            });
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => WorkspaceRole::Admin]);
    }

    public function member(): static
    {
        return $this->state(fn (): array => ['role' => WorkspaceRole::Member]);
    }
}
