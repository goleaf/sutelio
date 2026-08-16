<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\EnsureWorkspaceTaskDefinitions;
use App\Enums\WorkspaceRole;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Workspace $workspace): void {
            (new EnsureWorkspaceTaskDefinitions)->handle($workspace);
        });
    }

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->sentence(),
            'owner_id' => UserFactory::new(),
        ];
    }

    public function withOwnerMembership(): static
    {
        return $this->afterCreating(function (Workspace $workspace): void {
            WorkspaceMember::query()->updateOrCreate(
                ['workspace_id' => $workspace->id, 'user_id' => $workspace->owner_id],
                ['role' => WorkspaceRole::Owner],
            );
        });
    }
}
