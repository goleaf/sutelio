<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::where('slug', 'acme-projects')->firstOrFail();

        $tags = [
            ['name' => 'frontend'],
            ['name' => 'backend'],
            ['name' => 'design'],
            ['name' => 'urgent'],
            ['name' => 'review'],
        ];

        foreach ($tags as $data) {
            Tag::query()->firstOrCreate([
                'workspace_id' => $workspace->id,
                'name' => $data['name'],
            ]);
        }

        $this->command->info('Created 5 tags.');
    }
}
