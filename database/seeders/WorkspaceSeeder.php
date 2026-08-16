<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\EnsureWorkspaceTaskDefinitions;
use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    public function run(EnsureWorkspaceTaskDefinitions $ensureWorkspaceTaskDefinitions): void
    {
        $owner = User::where('email', 'demo@example.com')->firstOrFail();
        $alice = User::where('email', 'alice@example.com')->firstOrFail();
        $bob = User::where('email', 'bob@example.com')->firstOrFail();

        $workspace = Workspace::query()->updateOrCreate(
            ['slug' => 'acme-projects'],
            [
                'name' => 'Acme Projects',
                'description' => 'Main workspace for all Acme product development and operations.',
                'owner_id' => $owner->id,
            ],
        );

        foreach ([
            $owner->id => WorkspaceRole::Owner,
            $alice->id => WorkspaceRole::Admin,
            $bob->id => WorkspaceRole::Member,
        ] as $userId => $role) {
            WorkspaceMember::query()->updateOrCreate(
                ['workspace_id' => $workspace->id, 'user_id' => $userId],
                ['role' => $role],
            );
        }

        $ensureWorkspaceTaskDefinitions->handle($workspace);

        $this->command->info('Created workspace "Acme Projects" with 3 members.');
    }
}
