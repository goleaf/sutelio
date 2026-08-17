<?php

declare(strict_types=1);

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Project;
use App\Models\Reminder;
use App\Models\Tag;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Database\Factories\ActivityLogFactory;
use Database\Factories\AttachmentFactory;
use Database\Factories\ChecklistFactory;
use Database\Factories\ChecklistItemFactory;
use Database\Factories\CommentFactory;
use Database\Factories\LabelFactory;
use Database\Factories\ProjectFactory;
use Database\Factories\ReminderFactory;
use Database\Factories\TagFactory;
use Database\Factories\TaskPriorityFactory;
use Database\Factories\TaskStatusFactory;
use Database\Factories\TodoFactory;
use Database\Factories\UserFactory;
use Database\Factories\UserPreferenceFactory;
use Database\Factories\WorkspaceFactory;
use Database\Factories\WorkspaceInvitationFactory;
use Database\Factories\WorkspaceMemberFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('factories resolve their concrete eloquent model', function (string $factory, string $model) {
    expect($factory::new()->modelName())->toBe($model);
})->with([
    'activity log' => [ActivityLogFactory::class, ActivityLog::class],
    'attachment' => [AttachmentFactory::class, Attachment::class],
    'checklist' => [ChecklistFactory::class, Checklist::class],
    'checklist item' => [ChecklistItemFactory::class, ChecklistItem::class],
    'comment' => [CommentFactory::class, Comment::class],
    'label' => [LabelFactory::class, Label::class],
    'project' => [ProjectFactory::class, Project::class],
    'reminder' => [ReminderFactory::class, Reminder::class],
    'tag' => [TagFactory::class, Tag::class],
    'task priority' => [TaskPriorityFactory::class, TaskPriority::class],
    'task status' => [TaskStatusFactory::class, TaskStatus::class],
    'todo' => [TodoFactory::class, Todo::class],
    'user' => [UserFactory::class, User::class],
    'user preference' => [UserPreferenceFactory::class, UserPreference::class],
    'workspace' => [WorkspaceFactory::class, Workspace::class],
    'workspace invitation' => [WorkspaceInvitationFactory::class, WorkspaceInvitation::class],
    'workspace member' => [WorkspaceMemberFactory::class, WorkspaceMember::class],
]);

test('every first party model factory creates a valid default record', function (string $factory) {
    expect($factory::new()->create()->exists)->toBeTrue();
})->with([
    ActivityLogFactory::class,
    AttachmentFactory::class,
    ChecklistFactory::class,
    ChecklistItemFactory::class,
    CommentFactory::class,
    LabelFactory::class,
    ProjectFactory::class,
    ReminderFactory::class,
    TagFactory::class,
    TaskPriorityFactory::class,
    TaskStatusFactory::class,
    TodoFactory::class,
    UserFactory::class,
    UserPreferenceFactory::class,
    WorkspaceFactory::class,
    WorkspaceInvitationFactory::class,
    WorkspaceMemberFactory::class,
]);

test('meaningful factory states create valid records', function () {
    $models = [
        ChecklistItem::factory()->checked()->create(),
        ChecklistItem::factory()->unchecked()->create(),
        Project::factory()->active()->create(),
        Project::factory()->archived()->create(),
        Reminder::factory()->pending()->create(),
        Reminder::factory()->delivered()->create(),
        Reminder::factory()->failed()->create(),
        Reminder::factory()->cancelled()->create(),
        TaskPriority::factory()->asDefault()->create(),
        TaskPriority::factory()->archived()->create(),
        TaskStatus::factory()->asDefault()->create(),
        TaskStatus::factory()->completed()->create(),
        TaskStatus::factory()->archived()->create(),
        Todo::factory()->pending()->create(),
        Todo::factory()->inProgress()->create(),
        Todo::factory()->completed()->create(),
        Todo::factory()->overdue()->create(),
        Todo::factory()->archived()->create(),
        User::factory()->withTwoFactor()->create(),
        UserPreference::factory()->lithuanian()->create(),
        UserPreference::factory()->russian()->create(),
        UserPreference::factory()->withoutNotifications()->create(),
        Workspace::factory()->withOwnerMembership()->create(),
        WorkspaceInvitation::factory()->expired()->create(),
        WorkspaceInvitation::factory()->cancelled()->create(),
        WorkspaceInvitation::factory()->accepted()->create(),
        WorkspaceMember::factory()->owner()->create(),
        WorkspaceMember::factory()->admin()->create(),
        WorkspaceMember::factory()->member()->create(),
    ];

    foreach ($models as $model) {
        expect($model->exists)->toBeTrue();
    }
});

test('supporting factories produce valid typed state', function () {
    $subject = Todo::factory()->create();
    $activity = ActivityLog::factory()
        ->forSubject($subject)
        ->withProperties(['field' => 'title'])
        ->create();
    $preference = UserPreference::factory()->create();

    expect($activity->subject_id)
        ->toBe($subject->id)
        ->and($activity->properties)->toBe(['field' => 'title'])
        ->and(timezone_open($preference->timezone))->not->toBeFalse();
});

test('sanctum stateful domains are normalized to strings', function () {
    expect(config('sanctum.stateful'))
        ->toBeArray()
        ->each->toBeString();
});

test('workspace schema enforces its declared foreign keys', function (string $table, int $expectedCount) {
    expect(Schema::getForeignKeys($table))->toHaveCount($expectedCount);
})->with([
    'workspaces' => ['workspaces', 1],
    'workspace members' => ['workspace_members', 2],
    'projects' => ['projects', 1],
    'todos' => ['todos', 6],
    'checklists' => ['checklists', 1],
    'checklist items' => ['checklist_items', 1],
    'labels' => ['labels', 1],
    'tags' => ['tags', 1],
    'todo labels' => ['todo_label', 2],
    'todo tags' => ['todo_tag', 2],
    'comments' => ['comments', 2],
    'reminders' => ['reminders', 2],
    'attachments' => ['attachments', 2],
    'activity logs' => ['activity_logs', 2],
    'user preferences' => ['user_preferences', 1],
]);

test('email verification column removal is reversible', function () {
    $migration = require database_path('migrations/2026_08_17_103526_drop_email_verified_at_from_users_table.php');

    expect(Schema::hasColumn('users', 'email_verified_at'))->toBeFalse();

    $migration->down();

    expect(Schema::hasColumn('users', 'email_verified_at'))->toBeTrue();

    $migration->up();

    expect(Schema::hasColumn('users', 'email_verified_at'))->toBeFalse();
});

test('database seeders create the complete demo dataset', function () {
    $this->seed();

    expect(User::where('email', 'demo@example.com')->exists())->toBeTrue()
        ->and(Todo::query()->exists())->toBeTrue()
        ->and(Workspace::where('slug', 'acme-projects')->exists())->toBeTrue();
});

test('database seeders are idempotent and cover every supported locale and role', function () {
    $tables = [
        'users', 'user_preferences', 'workspaces', 'workspace_members',
        'task_statuses', 'task_priorities', 'projects', 'labels', 'tags',
        'todos', 'todo_label', 'todo_tag', 'checklists', 'checklist_items',
        'comments', 'reminders', 'activity_logs', 'notifications',
    ];

    $this->seed(DatabaseSeeder::class);
    $firstCounts = collect($tables)->mapWithKeys(
        fn (string $table): array => [$table => DB::table($table)->count()],
    );

    $this->seed(DatabaseSeeder::class);
    $secondCounts = collect($tables)->mapWithKeys(
        fn (string $table): array => [$table => DB::table($table)->count()],
    );

    expect($secondCounts->all())->toBe($firstCounts->all())
        ->and(UserPreference::query()->pluck('language')->sort()->values()->all())
        ->toBe(['en', 'lt', 'ru'])
        ->and(WorkspaceMember::query()->pluck('role')->map->value->sort()->values()->all())
        ->toBe(['admin', 'member', 'owner']);
});

test('database and demo seeders reject production execution before writing demo data', function () {
    $this->app->detectEnvironment(fn (): string => 'production');

    try {
        expect(fn () => $this->app->make(DatabaseSeeder::class)->run())
            ->toThrow(LogicException::class, 'local or testing environments');

        expect(fn () => $this->app->make(DemoSeeder::class)->run())
            ->toThrow(LogicException::class, 'local or testing environments');

        expect(User::query()->count())->toBe(0);
    } finally {
        $this->app->detectEnvironment(fn (): string => 'testing');
    }
});
