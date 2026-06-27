<?php

namespace App\Http\Controllers;

use App\Models\WatchmanNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchmanNotificationController extends Controller
{
    /** GET /watchman/notifications — paginated list for the authenticated watchman */
    public function index(Request $request): JsonResponse
    {
        $watchmanId = $request->user()->id;

        $notifications = WatchmanNotification::where('watchman_id', $watchmanId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count'  => WatchmanNotification::where('watchman_id', $watchmanId)
                                    ->whereNull('read_at')
                                    ->count(),
            'has_more'      => $notifications->hasMorePages(),
            'current_page'  => $notifications->currentPage(),
        ]);
    }

    /** GET /watchman/notifications/unread-count */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = WatchmanNotification::where('watchman_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /** PATCH /watchman/notifications/{notification}/read */
    public function markRead(Request $request, WatchmanNotification $notification): JsonResponse
    {
        if ($notification->watchman_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    /** PATCH /watchman/notifications/read-all */
    public function markAllRead(Request $request): JsonResponse
    {
        WatchmanNotification::where('watchman_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /** POST /watchman/notifications/fcm-token — save / update the device FCM token */
    public function saveFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);

        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'Token saved']);
    }
}
