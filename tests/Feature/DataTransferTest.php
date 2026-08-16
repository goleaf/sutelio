<?php

use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createDataTransferWorkspace(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);

    WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    return [$user, $workspace];
}

function jsonImportFile(array $payload): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        'workspace-import.json',
        json_encode($payload, JSON_THROW_ON_ERROR),
    );
}

test('workspace JSON import preview validates counts without writing records', function () {
    [$user, $workspace] = createDataTransferWorkspace();

    $this->actingAs($user)
        ->postJson(route('import.preview', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile([
                'version' => 1,
                'projects' => [
                    ['name' => 'Preview project'],
                ],
                'todos' => [
                    ['title' => 'First preview task', 'project' => 'Preview project'],
                    ['title' => 'Second preview task'],
                ],
            ]),
        ])
        ->assertOk()
        ->assertJsonPath('preview.format', 'json')
        ->assertJsonPath('preview.version', 1)
        ->assertJsonPath('preview.projects', 1)
        ->assertJsonPath('preview.todos', 2);

    expect($workspace->projects()->count())->toBe(0)
        ->and($workspace->todos()->count())->toBe(0);
});

test('workspace CSV import preview validates counts without writing records', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    $csv = implode("\n", [
        'Title,Status,Priority,Due Date,Project,Assigned To,Description',
        'First preview task,pending,none,,,,',
        'Second preview task,pending,none,,,,',
    ]);

    $this->actingAs($user)
        ->postJson(route('import.preview', $workspace), [
            'format' => 'csv',
            'file' => UploadedFile::fake()->createWithContent('workspace-import.csv', $csv),
        ])
        ->assertOk()
        ->assertJsonPath('preview.format', 'csv')
        ->assertJsonPath('preview.version', 1)
        ->assertJsonPath('preview.projects', 0)
        ->assertJsonPath('preview.todos', 2);

    expect($workspace->todos()->count())->toBe(0);
});

test('workspace import execution remains separate from preview', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    $payload = [
        'version' => 1,
        'projects' => [['name' => 'Imported project']],
        'todos' => [['title' => 'Imported task', 'project' => 'Imported project']],
    ];

    $this->actingAs($user)
        ->postJson(route('import.preview', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile($payload),
        ])
        ->assertOk();

    expect($workspace->projects()->count())->toBe(0)
        ->and($workspace->todos()->count())->toBe(0);

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile($payload),
        ])
        ->assertOk()
        ->assertJsonPath('imported.version', 1)
        ->assertJsonPath('imported.projects', 1)
        ->assertJsonPath('imported.todos', 1);

    expect($workspace->projects()->where('name', 'Imported project')->exists())->toBeTrue()
        ->and($workspace->todos()->where('title', 'Imported task')->exists())->toBeTrue();
});

test('workspace import rejects unsupported schema versions without writes', function (string $routeName) {
    [$user, $workspace] = createDataTransferWorkspace();

    $this->actingAs($user)
        ->postJson(route($routeName, $workspace), [
            'format' => 'json',
            'file' => jsonImportFile([
                'version' => 99,
                'projects' => [['name' => 'Unsupported project']],
                'todos' => [],
            ]),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($workspace->projects()->count())->toBe(0)
        ->and($workspace->todos()->count())->toBe(0);
})->with(['import.preview', 'import']);

test('workspace import preview and execution enforce workspace permissions', function (string $routeName) {
    [, $workspace] = createDataTransferWorkspace();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->postJson(route($routeName, $workspace), [
            'format' => 'json',
            'file' => jsonImportFile([
                'version' => 1,
                'projects' => [['name' => 'Forbidden project']],
                'todos' => [],
            ]),
        ])
        ->assertForbidden();

    expect($workspace->projects()->count())->toBe(0);
})->with(['import.preview', 'import']);

test('settings import UI requires a successful preview before execution', function () {
    $page = File::get(resource_path('js/pages/settings/Export.vue'));

    expect($page)
        ->toContain('importPreview')
        ->toContain('previewRequest')
        ->toContain('confirmImport')
        ->toContain("t('settings.export.preview_title')")
        ->toContain("t('settings.export.confirm_import')");
});

test('settings workspace transfer exposes import access only to workspace managers', function () {
    [$owner, $workspace] = createDataTransferWorkspace();
    $member = User::factory()->create();
    WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    $this->actingAs($owner)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('export.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Export')
            ->where('workspace.id', $workspace->id)
            ->where('canImport', true));

    $this->actingAs($member)
        ->withSession(['current_workspace_id' => $workspace->id])
        ->get(route('export.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Export')
            ->where('workspace.id', $workspace->id)
            ->where('canImport', false));
});

test('workspace import rejects files larger than five mebibytes', function () {
    [$user, $workspace] = createDataTransferWorkspace();

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => UploadedFile::fake()->create('workspace-import.json', 5121, 'application/json'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file')
        ->assertJsonPath('errors.file.0', 'Import files may not exceed 5 MiB.');
});

test('workspace import rejects content whose type does not match the selected format', function () {
    [$user, $workspace] = createDataTransferWorkspace();

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => UploadedFile::fake()->create('workspace-import.json', 10, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

test('workspace import rejects batches above the record limit', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    $todos = array_map(
        fn (int $index): array => ['title' => "Imported task {$index}"],
        range(1, 1001),
    );

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile(['projects' => [], 'todos' => $todos]),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($workspace->todos()->count())->toBe(0);
});

test('a partially invalid JSON import rolls back the complete batch', function () {
    [$user, $workspace] = createDataTransferWorkspace();

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile([
                'projects' => [
                    ['name' => 'Imported project'],
                ],
                'todos' => [
                    ['title' => 'Valid imported task', 'status' => 'pending'],
                    ['title' => 'Invalid imported task', 'status' => 'not-a-status'],
                ],
            ]),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($workspace->projects()->count())->toBe(0)
        ->and($workspace->todos()->count())->toBe(0);
});

test('a partially invalid CSV import rolls back the complete batch', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    $csv = implode("\n", [
        'Title,Status,Priority,Due Date,Project,Assigned To,Description',
        'Valid imported task,pending,none,,,,',
        'Invalid imported task,not-a-status,none,,,,',
    ]);

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'csv',
            'file' => UploadedFile::fake()->createWithContent('workspace-import.csv', $csv),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($workspace->todos()->count())->toBe(0);
});

test('workspace import rejects foreign project identifiers atomically', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    $foreignWorkspace = Workspace::factory()->create();
    $foreignProject = Project::factory()->create(['workspace_id' => $foreignWorkspace->id]);

    $this->actingAs($user)
        ->postJson(route('import', $workspace), [
            'format' => 'json',
            'file' => jsonImportFile([
                'todos' => [
                    [
                        'title' => 'Cross-workspace task',
                        'project_id' => $foreignProject->id,
                    ],
                ],
            ]),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($workspace->todos()->count())->toBe(0)
        ->and($foreignWorkspace->todos()->count())->toBe(0);
});

test('web and API attachment uploads reject unsupported content types', function () {
    Storage::fake('attachments');
    config()->set('filesystems.attachment_disk', 'attachments');

    [$user, $workspace] = createDataTransferWorkspace();
    $todo = Todo::factory()->create(['workspace_id' => $workspace->id]);
    $token = $user->createToken('attachment-test')->plainTextToken;

    $this->actingAs($user)
        ->postJson(route('attachments.store', $todo), [
            'file' => UploadedFile::fake()->createWithContent('payload.html', '<script>alert(1)</script>'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    $this->withToken($token)
        ->postJson("/api/tasks/{$todo->id}/attachments", [
            'file' => UploadedFile::fake()->createWithContent('payload.html', '<script>alert(1)</script>'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($todo->attachments()->count())->toBe(0);
});

test('attachment uploads reject files larger than ten mebibytes', function () {
    Storage::fake('attachments');
    config()->set('filesystems.attachment_disk', 'attachments');

    [$user, $workspace] = createDataTransferWorkspace();
    $todo = Todo::factory()->create(['workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->postJson(route('attachments.store', $todo), [
            'file' => UploadedFile::fake()->create('notes.txt', 10241, 'text/plain'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file')
        ->assertJsonPath('errors.file.0', 'Attachments may not exceed 10 MiB.');
});

test('attachment uploads are authorized through the task workspace', function () {
    Storage::fake('attachments');
    config()->set('filesystems.attachment_disk', 'attachments');

    [$user] = createDataTransferWorkspace();
    $foreignWorkspace = Workspace::factory()->create();
    $foreignTodo = Todo::factory()->create(['workspace_id' => $foreignWorkspace->id]);

    $this->actingAs($user)
        ->postJson(route('attachments.store', $foreignTodo), [
            'file' => UploadedFile::fake()->createWithContent('notes.txt', 'private notes'),
        ])
        ->assertForbidden();

    expect($foreignTodo->attachments()->count())->toBe(0);
});

test('every workspace export format streams its response', function (string $format) {
    [$user, $workspace] = createDataTransferWorkspace();
    Todo::factory()->count(3)->create(['workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('export', [$workspace, $format]))
        ->assertOk()
        ->assertStreamed();
})->with(['json', 'csv', 'markdown']);

test('workspace export contains only records from the authorized workspace', function (string $format) {
    [$user, $workspace] = createDataTransferWorkspace();
    $foreignWorkspace = Workspace::factory()->create();
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Authorized workspace project',
    ]);
    $foreignProject = Project::factory()->create([
        'workspace_id' => $foreignWorkspace->id,
        'name' => 'Foreign workspace project',
    ]);

    Todo::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Authorized workspace task',
    ]);
    Todo::factory()->create([
        'workspace_id' => $foreignWorkspace->id,
        'project_id' => $foreignProject->id,
        'title' => 'Foreign workspace task',
    ]);

    $response = $this->actingAs($user)
        ->get(route('export', [$workspace, $format]))
        ->assertOk();

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Authorized workspace task')
        ->toContain('Authorized workspace project')
        ->not->toContain('Foreign workspace task')
        ->not->toContain('Foreign workspace project');

    if ($format === 'json') {
        expect(json_decode($content, true, 512, JSON_THROW_ON_ERROR))
            ->toBeArray()
            ->toHaveKey('version', 1);
    }
})->with(['json', 'csv', 'markdown']);

test('CSV exports neutralize spreadsheet formulas', function () {
    [$user, $workspace] = createDataTransferWorkspace();
    Todo::factory()->create([
        'workspace_id' => $workspace->id,
        'title' => '=HYPERLINK("https://example.test")',
    ]);

    $response = $this->actingAs($user)
        ->get(route('export', [$workspace, 'csv']))
        ->assertOk();

    expect($response->streamedContent())
        ->toContain("'=HYPERLINK");
});
