<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /notifications — paginated list for the authenticated user */
    public function index(Request $request)
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'notifications'  => $notifications->items(),
            'unread_count'   => AppNotification::where('user_id', $request->user()->id)
                                    ->whereNull('read_at')
                                    ->count(),
            'has_more'       => $notifications->hasMorePages(),
            'current_page'   => $notifications->currentPage(),
        ]);
    }

    /** GET /notifications/unread-count */
    public function unreadCount(Request $request)
    {
        $count = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /** PATCH /notifications/{notification}/read */
    public function markRead(Request $request, AppNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    /** PATCH /notifications/read-all */
    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /** POST /notifications/fcm-token — save / update the device FCM token */
    public function saveFcmToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $request->user()->update(['fcm_token' => $request->token]);

        return response()->json(['message' => 'Token saved']);
    }
}
