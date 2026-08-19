<?php

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('record creation and workspace editing expose dedicated page routes', function () {
    expect(Route::has('projects.create'))->toBeTrue()
        ->and(Route::has('projects.edit'))->toBeTrue()
        ->and(Route::has('projects.copy'))->toBeTrue()
        ->and(Route::has('todos.create'))->toBeTrue()
        ->and(Route::has('workspaces.create'))->toBeTrue()
        ->and(Route::has('workspaces.copy'))->toBeTrue()
        ->and(Route::has('workspaces.edit'))->toBeTrue();
});

test('dedicated record pages use the shared page frame and no dialog root', function (string $page) {
    $path = resource_path("js/pages/{$page}");

    expect(File::exists($path))->toBeTrue();

    if (! File::exists($path)) {
        return;
    }

    expect(File::get($path))
        ->toContain('WorkspacePageFrame')
        ->toContain('WorkspacePageHeader')
        ->not->toContain('@/components/ui/dialog')
        ->not->toContain('@/components/ui/sheet')
        ->not->toContain('<Dialog')
        ->not->toContain('<Sheet');

    if ($page === 'tasks/Create.vue') {
        expect(File::get($path))
            ->toContain('projectShow')
            ->toContain(':cancel-href="cancelHref"');
    }
})->with([
    'project create' => 'projects/Create.vue',
    'project duplicate' => 'projects/Duplicate.vue',
    'project edit' => 'projects/Edit.vue',
    'task create' => 'tasks/Create.vue',
    'workspace create' => 'workspaces/Create.vue',
    'workspace duplicate' => 'workspaces/Duplicate.vue',
    'workspace edit' => 'workspaces/Edit.vue',
]);

test('record collection and detail pages navigate instead of opening dialogs or sheets', function (string $page) {
    expect(File::get(resource_path("js/pages/{$page}")))
        ->not->toContain('ProjectCreateDialog')
        ->not->toContain('TaskCreateDialog')
        ->not->toContain('TaskDetail')
        ->not->toContain('WorkspaceDialogContent')
        ->not->toContain('@/components/ui/dialog')
        ->not->toContain('@/components/ui/sheet');
})->with([
    'project collection' => 'projects/Index.vue',
    'project detail' => 'projects/Show.vue',
    'task collection' => 'tasks/Index.vue',
    'workspace portfolio' => 'workspaces/Index.vue',
    'account profile' => 'settings/Profile.vue',
]);

test('project edit and duplicate actions navigate to dedicated pages', function () {
    $page = File::get(resource_path('js/pages/projects/Show.vue'));

    expect($page)
        ->toContain('copy as copyProject')
        ->toContain('edit as editProjectPage')
        ->toContain('router.visit(')
        ->not->toContain("submitProjectAction(\n        'duplicate'");
});

test('record confirmations use the non modal page confirmation primitive', function () {
    $confirmation = resource_path('js/components/shared/PageConfirmPanel.vue');

    expect(File::exists($confirmation))->toBeTrue();

    if (File::exists($confirmation)) {
        expect(File::get($confirmation))
            ->toContain('data-slot="page-confirm-panel"')
            ->toContain('tabindex="-1"')
            ->toContain('document.activeElement instanceof HTMLElement')
            ->toContain('focusOrigin.value?.isConnected')
            ->toContain('focusOrigin.value.focus')
            ->toMatch('/type="button"\s+variant="outline"/s')
            ->toContain(':type="confirmType"')
            ->not->toContain('@/components/ui/dialog')
            ->not->toContain('@/components/ui/sheet')
            ->not->toContain('<Dialog')
            ->not->toContain('<Sheet')
            ->not->toContain('<Teleport');
    }

    $consumers = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->reject(fn (SplFileInfo $file): bool => $file->getFilename() === 'WorkspaceConfirmDialog.vue')
        ->filter(fn (SplFileInfo $file): bool => str_contains(
            $file->getContents(),
            'WorkspaceConfirmDialog',
        ))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($consumers)->toBe([]);
});

test('retired project task and confirmation overlays have no source files', function (string $path) {
    expect(File::exists(resource_path("js/components/{$path}")))->toBeFalse();
})->with([
    'project create dialog' => 'project/ProjectCreateDialog.vue',
    'task create dialog' => 'task/TaskCreateDialog.vue',
    'task detail sheet' => 'task/TaskDetail.vue',
    'workspace confirmation dialog' => 'shared/WorkspaceConfirmDialog.vue',
    'unused dialog actions helper' => 'shared/DialogActions.vue',
    'unused dialog body helper' => 'shared/DialogBody.vue',
]);

test('task confirmation consumers retain a single rendered root', function (string $path) {
    expect(File::get(resource_path("js/components/task/{$path}")))
        ->toMatch('/<template>\s*<div[^>]*>/');
})->with([
    'attachments' => 'TaskAttachmentsPanel.vue',
    'checklists' => 'TaskChecklistPanel.vue',
    'comments' => 'TaskCommentsPanel.vue',
    'task detail' => 'TaskDetailContent.vue',
]);

test('protected onboarding and global operation surfaces remain present', function () {
    expect(File::exists(resource_path('js/pages/onboarding/Index.vue')))->toBeTrue()
        ->and(File::exists(resource_path('js/components/shared/GlobalBusyOverlay.vue')))->toBeTrue();
});

test('workspace owner can open every dedicated management page', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()
        ->for($owner, 'owner')
        ->withOwnerMembership()
        ->create();
    $project = Project::factory()->for($workspace)->create();

    $this->actingAs($owner)
        ->get(route('projects.create', $workspace))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Create')
            ->where('workspace.id', $workspace->id));

    $this->actingAs($owner)
        ->get(route('todos.create', [$workspace, 'project_id' => $project->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tasks/Create')
            ->where('workspace.id', $workspace->id)
            ->where('selectedProjectId', $project->id));

    $this->actingAs($owner)
        ->get(route('projects.edit', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Edit')
            ->where('project.id', $project->id));

    $this->actingAs($owner)
        ->get(route('projects.copy', [$workspace, $project]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Duplicate')
            ->where('project.id', $project->id));

    $this->actingAs($owner)
        ->get(route('workspaces.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('workspaces/Create'));

    $this->actingAs($owner)
        ->get(route('workspaces.edit', $workspace))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('workspaces/Edit')
            ->where('workspace.id', $workspace->id));

    $this->actingAs($owner)
        ->get(route('workspaces.copy', $workspace))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('workspaces/Duplicate')
            ->where('workspace.id', $workspace->id));
});

test('task creation page rejects a project from another workspace', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()
        ->for($owner, 'owner')
        ->withOwnerMembership()
        ->create();
    $foreignProject = Project::factory()->create();

    $this->actingAs($owner)
        ->get(route('todos.create', [
            $workspace,
            'project_id' => $foreignProject->id,
        ]))
        ->assertNotFound();
});

test('task creation page keeps bounded project options and query count as projects grow', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()
        ->for($owner, 'owner')
        ->withOwnerMembership()
        ->create();

    $this->actingAs($owner);

    $this->get(route('todos.create', $workspace))->assertOk();

    $measureQueryCount = function () use ($workspace): int {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            $this->get(route('todos.create', $workspace))->assertOk();

            return count($connection->getQueryLog());
        } finally {
            $connection->disableQueryLog();
        }
    };

    $smallWorkspaceCount = $measureQueryCount();
    Project::factory()->count(130)->for($workspace)->create(['position' => 0]);
    $largerWorkspaceCount = $measureQueryCount();

    $this->get(route('todos.create', $workspace))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projects.data', 100));

    $selectedProject = Project::factory()
        ->for($workspace)
        ->create(['position' => 1000]);

    $this->get(route('todos.create', [
        $workspace,
        'project_id' => $selectedProject->id,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projects.data', 101)
            ->where('projects.data.100.id', $selectedProject->id));

    expect($largerWorkspaceCount)
        ->toBe($smallWorkspaceCount)
        ->toBeLessThanOrEqual(15);
});

test('workspace member cannot open owner only duplicate page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = Workspace::factory()
        ->for($owner, 'owner')
        ->withOwnerMembership()
        ->create();
    WorkspaceMember::factory()
        ->for($workspace)
        ->for($member)
        ->create(['role' => WorkspaceRole::Member]);

    $this->actingAs($member)
        ->get(route('workspaces.copy', $workspace))
        ->assertForbidden();
});
