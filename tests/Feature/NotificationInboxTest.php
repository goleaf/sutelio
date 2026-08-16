<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Workspace;
use App\Notifications\ReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function insertInboxNotification(
    User $user,
    array $data = [],
    ?string $readAt = null,
    ?string $createdAt = null,
    string $type = ReminderNotification::class,
): string {
    $id = Str::uuid()->toString();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => $type,
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode($data, JSON_THROW_ON_ERROR),
        'read_at' => $readAt,
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);

    return $id;
}

test('notification inbox is user scoped with global totals and direct task links', function () {
    $user = User::factory()->create();
    $foreign = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();
    $todo = Todo::factory()->for($workspace)->create();
    $reminderNotificationId = insertInboxNotification($user, [
        'kind' => 'reminder',
        'todo_id' => $todo->id,
        'todo_title' => $todo->title,
    ]);
    insertInboxNotification($user, ['title' => 'Read'], now()->toDateTimeString());
    insertInboxNotification($foreign, ['title' => 'Foreign']);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notifications/Index')
            ->has('notifications.data', 2)
            ->where('stats.total', 2)
            ->where('stats.unread', 1)
            ->where('stats.read', 1)
            ->where('filters.status', 'all')
            ->where('filters.kind', 'all')
            ->where('notifications.data', fn ($notifications): bool => $notifications->contains(
                fn (array $notification): bool => $notification['id'] === $reminderNotificationId
                    && $notification['kind'] === 'reminder'
                    && $notification['url'] === route('todos.show', $todo)
                    && $notification['is_read'] === false,
            )));
});

test('notification action links are emitted only for tasks the recipient may access', function () {
    $user = User::factory()->create();
    $foreignOwner = User::factory()->create();
    $foreignWorkspace = Workspace::factory()->for($foreignOwner, 'owner')->create();
    $foreignTodo = Todo::factory()->for($foreignWorkspace)->create();
    $notificationId = insertInboxNotification($user, [
        'kind' => 'reminder',
        'todo_id' => $foreignTodo->id,
        'todo_title' => $foreignTodo->title,
    ]);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.data.0.id', $notificationId)
            ->where('notifications.data.0.url', null));
});

test('unread filter is server backed and pagination remains stable', function () {
    $user = User::factory()->create();
    $createdAt = '2026-07-22 12:00:00';

    foreach (range(1, 21) as $number) {
        insertInboxNotification($user, ['title' => "Notice {$number}"], createdAt: $createdAt);
    }
    insertInboxNotification($user, ['title' => 'Read'], $createdAt, $createdAt);

    $first = $this->actingAs($user)
        ->get(route('notifications.index', ['status' => 'unread', 'per_page' => 20]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.meta.total', 21)
            ->has('notifications.data', 20)
            ->where('filters.status', 'unread'));

    $firstIds = collect($first->viewData('page')['props']['notifications']['data'])->pluck('id');
    $second = $this->get(route('notifications.index', [
        'status' => 'unread',
        'per_page' => 20,
        'page' => 2,
    ]))->assertOk();
    $secondIds = collect($second->viewData('page')['props']['notifications']['data'])->pluck('id');

    expect($firstIds)->toHaveCount(20)
        ->and($secondIds)->toHaveCount(1)
        ->and($firstIds->intersect($secondIds))->toBeEmpty();
});

test('read and semantic kind filters are user scoped and preserve query state', function () {
    $user = User::factory()->create();
    $foreign = User::factory()->create();
    $readReminderId = insertInboxNotification(
        $user,
        ['kind' => 'reminder'],
        now()->toDateTimeString(),
    );
    insertInboxNotification($user, ['kind' => 'reminder']);
    $readUpdateId = insertInboxNotification(
        $user,
        ['kind' => 'comment', 'title' => 'Reviewed update'],
        now()->toDateTimeString(),
        type: 'App\\Notifications\\GenericNotification',
    );
    insertInboxNotification(
        $user,
        ['kind' => 'comment', 'title' => 'Unread update'],
        type: 'App\\Notifications\\GenericNotification',
    );
    insertInboxNotification(
        $foreign,
        ['kind' => 'comment', 'title' => 'Foreign update'],
        now()->toDateTimeString(),
        type: 'App\\Notifications\\GenericNotification',
    );

    $response = $this->actingAs($user)->get(route('notifications.index', [
        'status' => 'read',
        'kind' => 'updates',
        'per_page' => 50,
    ]));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('filters.status', 'read')
        ->where('filters.kind', 'updates')
        ->where('filters.per_page', 50)
        ->where('notifications.meta.total', 1)
        ->where('notifications.data.0.id', $readUpdateId)
        ->where('notifications.data.0.kind', 'comment'));

    $ids = collect($response->viewData('page')['props']['notifications']['data'])->pluck('id');

    expect($ids)->toContain($readUpdateId)
        ->not->toContain($readReminderId);
});

test('explicit reminder payloads and reminder notification types share the reminders filter', function () {
    $user = User::factory()->create();
    $typedReminderId = insertInboxNotification($user, ['title' => 'Typed reminder']);
    $payloadReminderId = insertInboxNotification(
        $user,
        ['kind' => 'reminder', 'title' => 'Payload reminder'],
        type: 'App\\Notifications\\GenericNotification',
    );
    insertInboxNotification(
        $user,
        ['kind' => 'completion', 'title' => 'Completed task'],
        type: 'App\\Notifications\\GenericNotification',
    );

    $response = $this->actingAs($user)->get(route('notifications.index', ['kind' => 'reminders']));
    $notifications = collect($response->viewData('page')['props']['notifications']['data']);

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('filters.kind', 'reminders')
        ->where('notifications.meta.total', 2));

    expect($notifications->pluck('id')->all())
        ->toEqualCanonicalizing([$typedReminderId, $payloadReminderId])
        ->and($notifications->pluck('kind')->unique()->all())
        ->toBe(['reminder']);
});

test('legacy payloads use a safe semantic fallback and user timezone date key', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-16 21:30:00', 'UTC'));
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['timezone' => 'Europe/Vilnius']);
    $legacyId = insertInboxNotification(
        $user,
        ['title' => '  Legacy   update ', 'message' => ' Safe message ', 'todo_id' => 'not-a-uuid'],
        createdAt: '2026-08-16 21:30:00',
        type: 'App\\Notifications\\GenericNotification',
    );

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.data.0.id', $legacyId)
            ->where('notifications.data.0.kind', 'general')
            ->where('notifications.data.0.title', 'Legacy update')
            ->where('notifications.data.0.body', 'Safe message')
            ->where('notifications.data.0.url', null)
            ->where('notifications.data.0.created_date', '2026-08-17')
            ->where('today', '2026-08-17'));
});

test('partial reloads can refresh the user local today boundary after midnight', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create(['timezone' => 'Europe/Vilnius']);
    $headers = [
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'notifications/Index',
        'X-Inertia-Partial-Data' => 'today',
    ];
    $version = app(HandleInertiaRequests::class)->version(request());

    if (is_string($version)) {
        $headers['X-Inertia-Version'] = $version;
    }

    $this->travelTo(CarbonImmutable::parse('2026-08-16 20:59:00', 'UTC'));
    $this->actingAs($user)
        ->get(route('notifications.index'), $headers)
        ->assertOk()
        ->assertJsonPath('props.today', '2026-08-16');

    $this->travelTo(CarbonImmutable::parse('2026-08-16 21:01:00', 'UTC'));
    $this->get(route('notifications.index'), $headers)
        ->assertOk()
        ->assertJsonPath('props.today', '2026-08-17');
});

test('notification stats partial reload does not execute the paginated inbox query', function () {
    $user = User::factory()->create();
    insertInboxNotification($user, ['kind' => 'reminder']);

    $headers = [
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'notifications/Index',
        'X-Inertia-Partial-Data' => 'stats',
    ];
    $version = app(HandleInertiaRequests::class)->version(request());

    if (is_string($version)) {
        $headers['X-Inertia-Version'] = $version;
    }

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->actingAs($user)
        ->get(route('notifications.index'), $headers)
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonStructure(['props' => ['stats']])
        ->assertJsonMissingPath('props.notifications')
        ->assertJsonMissingPath('props.filters');

    $paginatedQueries = collect($queries)->filter(
        fn (string $query): bool => preg_match('/\bfrom\s+["`]?notifications["`]?\b/i', $query) === 1
            && preg_match('/\bselect\b.*["`]?data["`]?/i', $query) === 1,
    )->values()->all();

    expect($paginatedQueries)->toBeEmpty(implode("\n", $paginatedQueries));
});

test('notification task action authorization is batched without per-row queries', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->for($user, 'owner')->create();
    $todos = Todo::factory()->count(20)->for($workspace)->create();

    foreach ($todos as $todo) {
        insertInboxNotification($user, [
            'kind' => 'reminder',
            'todo_id' => $todo->id,
        ]);
    }

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('notifications.data', 20));

    $notificationQueries = collect($queries)->filter(
        fn (string $query): bool => preg_match('/\bfrom\s+["`]?notifications["`]?\b/i', $query) === 1,
    );
    $todoAuthorizationQueries = collect($queries)->filter(
        fn (string $query): bool => preg_match('/\bfrom\s+["`]?todos["`]?\b/i', $query) === 1
            && preg_match('/\bjoin\s+["`]?workspaces["`]?\b/i', $query) === 1,
    );

    expect($notificationQueries)->toHaveCount(3)
        ->and($todoAuthorizationQueries)->toHaveCount(1);
});

test('notification mutations affect only the authenticated users rows', function () {
    $user = User::factory()->create();
    $foreign = User::factory()->create();
    $ownId = insertInboxNotification($user, ['title' => 'Own']);
    $otherOwnId = insertInboxNotification($user, ['title' => 'Own two']);
    $foreignId = insertInboxNotification($foreign, ['title' => 'Foreign']);

    $this->actingAs($user)
        ->post(route('notifications.markRead', ['id' => $foreignId]))
        ->assertRedirect();
    $this->post(route('notifications.markRead', ['id' => $ownId]))->assertRedirect();
    $this->post(route('notifications.markRead', ['id' => $ownId]))->assertRedirect();
    $this->post(route('notifications.markAllRead'))->assertRedirect();
    $this->post(route('notifications.markAllRead'))->assertRedirect();

    expect(DB::table('notifications')->where('id', $ownId)->value('read_at'))->not->toBeNull()
        ->and(DB::table('notifications')->where('id', $otherOwnId)->value('read_at'))->not->toBeNull()
        ->and(DB::table('notifications')->where('id', $foreignId)->value('read_at'))->toBeNull();
});

test('notification inbox rejects unsupported filters', function (array $query, array $errors) {
    $this->actingAs(User::factory()->create())
        ->get(route('notifications.index', $query))
        ->assertSessionHasErrors($errors);
})->with([
    'status' => [['status' => 'foreign'], ['status']],
    'kind' => [['kind' => 'foreign'], ['kind']],
    'page size' => [['per_page' => 500], ['per_page']],
    'page' => [['page' => 0], ['page']],
]);
