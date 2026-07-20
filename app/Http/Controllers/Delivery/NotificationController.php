<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Concerns\ManagesNotificationInbox;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use ManagesNotificationInbox;

    public function index(Request $request): View
    {
        return $this->inbox($request, $request->user(), 'delivery.notifications.index', 'delivery');
    }

    public function markRead(int $id): RedirectResponse
    {
        return $this->markOneRead(request()->user(), $id);
    }

    public function markAllRead(): RedirectResponse
    {
        return $this->markAllAsRead(request()->user());
    }
}
