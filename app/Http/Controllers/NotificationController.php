<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MarkAllNotificationsRead;
use App\Actions\MarkNotificationRead;
use App\Http\Requests\NotificationIndexRequest;
use App\Http\Resources\NotificationInboxResource;
use App\Models\User;
use App\Queries\NotificationIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(
        NotificationIndexRequest $request,
        NotificationIndexQuery $notificationIndexQuery,
    ): Response {
        $user = $request->user();

        abort_unless($user instanceof User, 403);
        $user->loadMissing('preferences');

        return Inertia::render('notifications/Index', [
            'notifications' => fn () => NotificationInboxResource::collection(
                $notificationIndexQuery->forUser(
                    $user,
                    $request->status(),
                    $request->kind(),
                    $request->perPage(),
                ),
            ),
            'stats' => fn (): array => $notificationIndexQuery->statsForUser($user),
            'filters' => fn (): array => $request->state(),
            'today' => $request->today(),
        ]);
    }

    public function markRead(
        Request $request,
        string $id,
        MarkNotificationRead $markNotificationRead,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $markNotificationRead->handle($user, $id);

        return redirect()->back();
    }

    public function markAllRead(
        Request $request,
        MarkAllNotificationsRead $markAllNotificationsRead,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $markAllNotificationsRead->handle($user);

        return redirect()->back();
    }
}
