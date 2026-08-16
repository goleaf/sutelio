<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::where('slug', 'acme-projects')->firstOrFail();
        $demo = User::where('email', 'demo@example.com')->firstOrFail();
        $alice = User::where('email', 'alice@example.com')->firstOrFail();
        $bob = User::where('email', 'bob@example.com')->firstOrFail();
        $projects = $workspace->projects()->pluck('id', 'name');
        $todos = $workspace->todos()->pluck('id', 'title');

        $activities = [
            ['user' => $demo, 'subject_type' => Project::class, 'subject_id' => $projects['Product Launch'], 'event' => ActivityEvent::Created, 'properties' => ['name' => 'Product Launch']],
            ['user' => $alice, 'subject_type' => Project::class, 'subject_id' => $projects['Website Redesign'], 'event' => ActivityEvent::Created, 'properties' => ['name' => 'Website Redesign']],
            ['user' => $demo, 'subject_type' => Todo::class, 'subject_id' => $todos['Finalize product positioning document'], 'event' => ActivityEvent::Created, 'properties' => ['title' => 'Finalize product positioning document']],
            ['user' => $alice, 'subject_type' => Todo::class, 'subject_id' => $todos['Finalize product positioning document'], 'event' => ActivityEvent::Updated, 'properties' => ['field' => 'status', 'old' => 'pending', 'new' => 'in_progress']],
            ['user' => $bob, 'subject_type' => Todo::class, 'subject_id' => $todos['Set up analytics tracking'], 'event' => ActivityEvent::Completed, 'properties' => ['title' => 'Set up analytics tracking']],
            ['user' => $demo, 'subject_type' => Todo::class, 'subject_id' => $todos['Design new landing page mockups'], 'event' => ActivityEvent::Pinned, 'properties' => ['title' => 'Design new landing page mockups']],
            ['user' => $alice, 'subject_type' => Todo::class, 'subject_id' => $todos['Record product demo video'], 'event' => ActivityEvent::Created, 'properties' => ['title' => 'Record product demo video']],
            ['user' => $bob, 'subject_type' => Todo::class, 'subject_id' => $todos['Add biometric authentication'], 'event' => ActivityEvent::Updated, 'properties' => ['field' => 'priority', 'old' => 'medium', 'new' => 'urgent']],
            ['user' => $demo, 'subject_type' => Project::class, 'subject_id' => $projects['Legacy System'], 'event' => ActivityEvent::Archived, 'properties' => ['name' => 'Legacy System']],
            ['user' => $alice, 'subject_type' => Todo::class, 'subject_id' => $todos['Fix crash on Android 12'], 'event' => ActivityEvent::Created, 'properties' => ['title' => 'Fix crash on Android 12']],
        ];

        foreach ($activities as $data) {
            ActivityLog::query()->updateOrCreate([
                'user_id' => $data['user']->id,
                'workspace_id' => $workspace->id,
                'subject_type' => $data['subject_type'],
                'subject_id' => $data['subject_id'],
                'event' => $data['event'],
            ], [
                'properties' => $data['properties'],
            ]);
        }

        $this->command->info('Created 10 activity log entries.');
    }
}
