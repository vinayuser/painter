<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ManagesNotificationInbox
{
    protected function inbox(Request $request, User $user, string $view, string $panel): View
    {
        $query = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest();

        if ($request->get('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $unreadCount = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view($view, [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'panel' => $panel,
            'filter' => $request->get('filter'),
        ]);
    }

    protected function markOneRead(User $user, int $id): RedirectResponse
    {
        $notification = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->firstOrFail();

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    protected function markAllAsRead(User $user): RedirectResponse
    {
        AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
