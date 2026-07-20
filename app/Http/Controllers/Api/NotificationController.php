<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id();

        $query = AppNotification::query()
            ->where('user_id', $userId)
            ->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate((int) $request->get('per_page', 20));

        $unreadCount = AppNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return AppNotificationResource::collection($notifications)
            ->additional([
                'meta' => [
                    'unread_count' => $unreadCount,
                ],
            ])
            ->response();
    }

    public function unreadCount(): JsonResponse
    {
        $count = AppNotification::query()
            ->where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => ['unread_count' => $count],
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $notification = AppNotification::query()
            ->where('user_id', auth('api')->id())
            ->whereKey($id)
            ->firstOrFail();

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => new AppNotificationResource($notification->fresh()),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
