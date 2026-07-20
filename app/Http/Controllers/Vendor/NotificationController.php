<?php

namespace App\Http\Controllers\Vendor;

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
        return $this->inbox($request, $request->user(), 'vendor.notifications.index', 'vendor');
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
