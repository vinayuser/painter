<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FcmTopic;
use App\Http\Controllers\Concerns\ManagesNotificationInbox;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\FcmService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use ManagesNotificationInbox;

    public function __construct(
        protected FcmService $fcm,
        protected NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'inbox');
        $user = $request->user();

        $inboxQuery = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest();

        if ($request->get('filter') === 'unread') {
            $inboxQuery->whereNull('read_at');
        }

        $notifications = $inboxQuery->paginate(20)->withQueryString();

        $unreadCount = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $broadcasts = AppNotification::query()
            ->whereNull('user_id')
            ->whereNotNull('channel')
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.notifications.index', [
            'tab' => $tab,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'broadcasts' => $broadcasts,
            'topics' => FcmTopic::cases(),
            'fcmReady' => $this->fcm->isConfigured(),
            'hasBrowserToken' => filled($user->fcm_token),
            'panel' => 'admin',
            'filter' => $request->get('filter'),
        ]);
    }

    public function testSelf(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->fcm_token)) {
            return redirect()
                ->route('admin.notifications.index', ['tab' => 'settings'])
                ->with('error', 'Enable Chrome notifications first, then try the self-test.');
        }

        $this->notifications->notifyUser(
            $user,
            'Test notification',
            'Paint Store push is working. You can switch tabs — this should still appear in Chrome.',
            'admin_self_test',
            ['click_action' => 'HOME', 'title' => 'Test notification', 'body' => 'Paint Store push is working.'],
            queue: false,
        );

        return redirect()
            ->route('admin.notifications.index', ['tab' => 'settings'])
            ->with('success', 'Test push sent to this browser. Switch to another tab — you should still see a Chrome notification.');
    }

    public function markRead(int $id): RedirectResponse
    {
        return $this->markOneRead(request()->user(), $id);
    }

    public function markAllRead(): RedirectResponse
    {
        return $this->markAllAsRead(request()->user());
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(array_column(FcmTopic::cases(), 'value'))],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $topic = FcmTopic::from($data['channel']);

        $this->notifications->notifyTopic(
            $topic,
            $data['title'],
            $data['body'],
            'global_broadcast',
            ['click_action' => 'HOME'],
            queue: false,
        );

        return redirect()
            ->route('admin.notifications.index', ['tab' => 'broadcast'])
            ->with('success', 'Broadcast sent to '.$topic->label().' channel.');
    }
}

