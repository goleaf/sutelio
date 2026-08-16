<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::where('slug', 'acme-projects')->firstOrFail();

        $checklists = [
            ['Finalize product positioning document', 'Review Steps', 0, [
                ['Read through draft positioning', true],
                ['Verify competitor analysis data', true],
                ['Get stakeholder sign-off', false],
                ['Final polish and formatting', false],
            ]],
            ['Design new landing page mockups', 'Design Phases', 0, [
                ['Wireframe complete', true],
                ['High-fidelity mockups', true],
                ['Responsive variants', false],
                ['Prototype interactions', false],
            ]],
            ['Design new landing page mockups', 'Stakeholder Feedback', 1, [
                ['Share with design lead', true],
                ['Incorporate CEO feedback', false],
                ['Final approval', false],
            ]],
            ['Set up Kubernetes cluster', 'Infrastructure Setup', 0, [
                ['Provision EKS cluster', true],
                ['Configure node groups', true],
                ['Set up IAM roles and policies', true],
                ['Configure auto-scaling', false],
                ['Deploy monitoring stack', false],
            ]],
            ['Implement push notification service', 'Platform Setup', 0, [
                ['Register with FCM', true],
                ['Register with APNs', false],
                ['Build notification service API', false],
            ]],
        ];

        foreach ($checklists as [$todoTitle, $name, $position, $items]) {
            $todo = $workspace->todos()->where('title', $todoTitle)->firstOrFail();
            $checklist = Checklist::query()->updateOrCreate(
                ['todo_id' => $todo->id, 'name' => $name],
                ['position' => $position],
            );

            foreach ($items as $itemPosition => [$content, $isChecked]) {
                ChecklistItem::query()->updateOrCreate(
                    ['checklist_id' => $checklist->id, 'content' => $content],
                    ['is_checked' => $isChecked, 'position' => $itemPosition],
                );
            }
        }

        $this->command->info('Created checklists with items.');
    }
}
